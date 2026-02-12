#!/usr/bin/env python3
"""Omnichannel sync entrypoint for Woo, Zid, Salla, and Shopify."""

from __future__ import annotations

import argparse
import json
import os
import sys
from pathlib import Path
from typing import Any, Dict, List

from connectors import CONNECTOR_MAP
from hub_core import (
    CHANNELS,
    HubConfigError,
    HubDB,
    build_channel_payload,
    compute_channel_price,
    load_local_env,
    parse_stage_to_scope,
)


def parse_args() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description="Omnichannel catalog/inventory/pricing sync")
    parser.add_argument("--channel", required=True, choices=CHANNELS)
    parser.add_argument("--mode", required=True, choices=["catalog", "inventory", "pricing", "reconcile"])
    parser.add_argument("--scope", default="top50", help="top50|top100|top200|active|wave50|wave100|wave200")
    parser.add_argument("--csv-file", default="", help="Optional override CSV path for scope bootstrap")
    parser.add_argument("--dry-run", action="store_true")
    parser.add_argument("--report-json", default="", help="Optional output path for job report")
    parser.add_argument("--strict", action="store_true", help="Fail if any item fails")
    return parser.parse_args()


def _build_items(db: HubDB, channel: str, scope: str) -> List[Dict[str, Any]]:
    products = db.get_products_for_scope(scope, channel)
    rule = db.get_price_rule(channel)
    items: List[Dict[str, Any]] = []
    for product in products:
        category_external_id = db.get_category_map(channel, product.get("category_key", "uncategorized"))
        price = compute_channel_price(
            base_cost_sar=float(product.get("base_cost_sar") or 0.0),
            target_margin_pct=float(product.get("target_margin_pct") or 0.0),
            vat_included_bool=bool(product.get("vat_included_bool")),
            fee_pct=float(rule.get("fee_pct") or 0.0),
            payment_pct=float(rule.get("payment_pct") or 0.0),
            ops_buffer_sar=float(rule.get("ops_buffer_sar") or 0.0),
            round_rule=str(rule.get("round_rule") or "nearest_9"),
        )
        item = build_channel_payload(product, category_external_id, price)
        item["publish_state"] = product.get("publish_state") or ("publish" if product.get("status") == "publish" else "draft")
        items.append(item)
    return items


def _connector(channel: str, dry_run: bool):
    klass = CONNECTOR_MAP[channel]
    return klass(dry_run=dry_run)


def _sync_mode(connector, mode: str, items: List[Dict[str, Any]]) -> Dict[str, Any]:
    if mode == "catalog":
        return connector.sync_catalog(items)
    if mode == "inventory":
        return connector.sync_inventory(items)
    if mode == "pricing":
        return connector.sync_pricing(items)

    # reconcile mode
    out_catalog = connector.sync_catalog(items)
    out_inventory = connector.sync_inventory(items)
    out_pricing = connector.sync_pricing(items)

    merged_items = out_catalog["items"] + out_inventory["items"] + out_pricing["items"]
    succeeded = out_catalog["succeeded"] + out_inventory["succeeded"] + out_pricing["succeeded"]
    failed = out_catalog["failed"] + out_inventory["failed"] + out_pricing["failed"]
    return {
        "processed": len(merged_items),
        "succeeded": succeeded,
        "failed": failed,
        "items": merged_items,
        "segments": {
            "catalog": out_catalog,
            "inventory": out_inventory,
            "pricing": out_pricing,
        },
    }


def main() -> int:
    load_local_env()
    args = parse_args()

    try:
        scope = parse_stage_to_scope(args.scope)
        db = HubDB(os.environ.get("HUB_DB_URL"))
        db.ensure_schema()
        db.seed_default_price_rules()

        csv_path = Path(args.csv_file).resolve() if args.csv_file else None
        if scope != "active":
            db.ensure_scope_loaded(scope, csv_path)

        connector = _connector(args.channel, args.dry_run)
        job_id = db.start_sync_job(args.channel, args.mode, scope, args.dry_run)

        items = _build_items(db, args.channel, scope)
        result = _sync_mode(connector, args.mode, items)

        for item_result in result.get("items", []):
            sku = item_result.get("sku", "")
            payload = item_result.get("payload") or {}
            response = item_result.get("response") or {}
            error = item_result.get("error") or None
            db.upsert_channel_listing(
                channel=args.channel,
                sku=sku,
                external_product_id=item_result.get("external_product_id"),
                external_variant_id=item_result.get("external_variant_id"),
                publish_state=item_result.get("publish_state") or "draft",
                payload=payload,
                response=response,
                error=error,
            )
            if not item_result.get("success"):
                db.queue_dead_letter(
                    channel=args.channel,
                    mode=args.mode,
                    sku=sku,
                    payload=payload,
                    error=str(error or "unknown error"),
                )

        status = "success" if result.get("failed", 0) == 0 else "partial_failure"
        db.finish_sync_job(
            job_id=job_id,
            status=status,
            processed_count=result.get("processed", 0),
            success_count=result.get("succeeded", 0),
            failed_count=result.get("failed", 0),
            error_summary="" if result.get("failed", 0) == 0 else f"{result.get('failed', 0)} failed",
        )

        report = {
            "channel": args.channel,
            "mode": args.mode,
            "scope": scope,
            "dry_run": args.dry_run,
            "processed": result.get("processed", 0),
            "succeeded": result.get("succeeded", 0),
            "failed": result.get("failed", 0),
            "job_id": job_id,
            "items": result.get("items", []),
            "segments": result.get("segments", {}),
        }

        if args.report_json:
            out_path = Path(args.report_json).resolve()
            out_path.parent.mkdir(parents=True, exist_ok=True)
            out_path.write_text(json.dumps(report, ensure_ascii=False, indent=2), encoding="utf-8")

        print(json.dumps({k: v for k, v in report.items() if k != "items"}, ensure_ascii=False, indent=2))

        if args.strict and report["failed"] > 0:
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
