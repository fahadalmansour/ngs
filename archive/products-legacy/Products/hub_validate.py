#!/usr/bin/env python3
"""Strict validator for omnichannel rollout gates."""

from __future__ import annotations

import argparse
import json
import os
import random
import sys
from pathlib import Path
from typing import Any, Dict, List

from hub_core import (
    HubConfigError,
    HubDB,
    compute_channel_price,
    load_local_env,
    parse_stage_to_scope,
    stage_channels,
)


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Validate omnichannel wave quality gates")
    parser.add_argument("--stage", required=True, help="wave50|wave100|wave200|top50|top100|top200")
    parser.add_argument("--strict", action="store_true")
    parser.add_argument("--report-file", default="")
    parser.add_argument("--sample-size", type=int, default=30)
    return parser.parse_args()


def finding(severity: str, code: str, message: str, details: Dict[str, Any] | None = None) -> Dict[str, Any]:
    return {
        "severity": severity,
        "code": code,
        "message": message,
        "details": details or {},
    }


def main() -> int:
    load_local_env()
    args = parse_args()

    try:
        scope = parse_stage_to_scope(args.stage)
        stage_key = args.stage.lower()
        channels = stage_channels(stage_key) if stage_key.startswith("wave") else ["woo", "zid"]

        db = HubDB(os.environ.get("HUB_DB_URL"))
        db.ensure_schema()
        db.seed_default_price_rules()
        if scope != "active":
            db.ensure_scope_loaded(scope)

        findings: List[Dict[str, Any]] = []

        # 1) SKU uniqueness in catalog table
        dupes = db.fetch_all(
            "SELECT sku, COUNT(*) AS c FROM catalog_products GROUP BY sku HAVING COUNT(*) > 1",
            "SELECT sku, COUNT(*) AS c FROM catalog_products GROUP BY sku HAVING COUNT(*) > 1",
            (),
        )
        if dupes:
            findings.append(finding("critical", "sku_duplicate", "Duplicate SKUs found in catalog_products", {"count": len(dupes)}))

        # 2) Scope population
        scope_rows = db.fetch_all(
            "SELECT sku, name_ar, name_en, category_key, source_scope FROM catalog_products WHERE source_scope = %s ORDER BY sku",
            "SELECT sku, name_ar, name_en, category_key, source_scope FROM catalog_products WHERE source_scope = ? ORDER BY sku",
            (scope,),
        )
        if not scope_rows:
            findings.append(finding("critical", "scope_empty", f"No products loaded for scope={scope}"))
            scope_products: List[Dict[str, Any]] = []
        else:
            scope_products = scope_rows

        # 3) Arabic/English required fields
        missing_names = [p["sku"] for p in scope_products if not (p.get("name_ar") and p.get("name_en"))]
        if missing_names:
            findings.append(
                finding(
                    "critical",
                    "missing_names",
                    "Products missing Arabic or English names",
                    {"count": len(missing_names), "sample": missing_names[:10]},
                )
            )

        # 4) Category mapping coverage per active channel
        for channel in channels:
            missing_map = db.fetch_all(
                """
                SELECT p.category_key, COUNT(*) AS c
                FROM catalog_products p
                LEFT JOIN channel_category_map m
                  ON m.channel = %s AND m.category_key = p.category_key AND m.active = TRUE
                WHERE p.source_scope = %s AND m.external_category_id IS NULL
                GROUP BY p.category_key
                ORDER BY c DESC
                """,
                """
                SELECT p.category_key, COUNT(*) AS c
                FROM catalog_products p
                LEFT JOIN channel_category_map m
                  ON m.channel = ? AND m.category_key = p.category_key AND m.active = 1
                WHERE p.source_scope = ? AND m.external_category_id IS NULL
                GROUP BY p.category_key
                ORDER BY c DESC
                """,
                (channel, scope),
            )
            if missing_map:
                findings.append(
                    finding(
                        "critical",
                        "missing_category_map",
                        f"Missing category mapping for channel={channel}",
                        {"count": len(missing_map), "sample": missing_map[:10]},
                    )
                )

        # 5) Price rule existence and correctness sample
        for channel in channels:
            try:
                rule = db.get_price_rule(channel)
            except HubConfigError as exc:
                findings.append(finding("critical", "missing_price_rule", str(exc), {"channel": channel}))
                continue

            products = db.get_products_for_scope(scope, channel)
            if not products:
                findings.append(
                    finding(
                        "critical",
                        "scope_no_products",
                        f"No scope products available for channel={channel}",
                    )
                )
                continue

            sample = products[:]
            random.shuffle(sample)
            sample = sample[: max(1, min(args.sample_size, len(sample)))]

            bad_price = []
            for p in sample:
                price = compute_channel_price(
                    base_cost_sar=float(p.get("base_cost_sar") or 0),
                    target_margin_pct=float(p.get("target_margin_pct") or 0),
                    vat_included_bool=bool(p.get("vat_included_bool")),
                    fee_pct=float(rule.get("fee_pct") or 0),
                    payment_pct=float(rule.get("payment_pct") or 0),
                    ops_buffer_sar=float(rule.get("ops_buffer_sar") or 0),
                    round_rule=str(rule.get("round_rule") or "nearest_9"),
                )
                if price <= 0:
                    bad_price.append({"sku": p.get("sku"), "price": price})
                if str(rule.get("round_rule")) == "nearest_9":
                    int_part = int(round(price))
                    if int_part % 10 != 9:
                        bad_price.append({"sku": p.get("sku"), "price": price, "rule": "nearest_9"})

            if bad_price:
                findings.append(
                    finding(
                        "critical",
                        "price_rule_violation",
                        f"Price rule validation failed for channel={channel}",
                        {"count": len(bad_price), "sample": bad_price[:10]},
                    )
                )

        # 6) Inventory non-negative / oversell protection
        inv_issues = db.fetch_all(
            """
            SELECT sku, stock_on_hand, reserved_qty, safety_stock
            FROM catalog_inventory
            WHERE stock_on_hand < 0 OR reserved_qty < 0 OR safety_stock < 0 OR (stock_on_hand - reserved_qty - safety_stock) < 0
            """,
            """
            SELECT sku, stock_on_hand, reserved_qty, safety_stock
            FROM catalog_inventory
            WHERE stock_on_hand < 0 OR reserved_qty < 0 OR safety_stock < 0 OR (stock_on_hand - reserved_qty - safety_stock) < 0
            """,
            (),
        )
        if inv_issues:
            findings.append(
                finding(
                    "critical",
                    "inventory_negative",
                    "Inventory protection check failed: sellable stock below zero",
                    {"count": len(inv_issues), "sample": inv_issues[:10]},
                )
            )

        # 7) Sync lag threshold checks for active channels
        lag_breaches = []
        for channel in channels:
            lag = db.get_sync_lag_minutes(channel)
            if lag is None:
                findings.append(
                    finding(
                        "warning",
                        "sync_lag_missing",
                        f"No sync history yet for channel={channel}",
                    )
                )
                continue
            if lag > 15:
                lag_breaches.append({"channel": channel, "lag_minutes": round(lag, 2)})
        if lag_breaches:
            findings.append(
                finding(
                    "warning",
                    "sync_lag_threshold",
                    "Sync lag exceeded 15 minutes",
                    {"breaches": lag_breaches},
                )
            )

        critical_count = sum(1 for f in findings if f["severity"] == "critical")
        warning_count = sum(1 for f in findings if f["severity"] == "warning")

        report = {
            "stage": args.stage,
            "scope": scope,
            "channels": channels,
            "strict": args.strict,
            "critical_count": critical_count,
            "warning_count": warning_count,
            "status": "pass" if critical_count == 0 else "fail",
            "findings": findings,
        }

        if args.report_file:
            out = Path(args.report_file).resolve()
            out.parent.mkdir(parents=True, exist_ok=True)
            out.write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding="utf-8")

        print(json.dumps(report, ensure_ascii=False, indent=2))

        if args.strict and critical_count > 0:
            return 2
        return 0

    except HubConfigError as exc:
        print(f"ERROR: {exc}", file=sys.stderr)
        return 2
    except Exception as exc:
        print(f"ERROR: {type(exc).__name__}: {exc}", file=sys.stderr)
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
