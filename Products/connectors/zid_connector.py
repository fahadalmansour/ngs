#!/usr/bin/env python3
"""Zid connector with API mode (token) and CSV fallback mode."""

from __future__ import annotations

import csv
import os
from datetime import datetime, timezone
from pathlib import Path
from typing import Any, Dict, List

from .base_connector import BaseConnector, ConnectorItemResult


class ZidConnector(BaseConnector):
    name = "zid"

    def __init__(self, dry_run: bool = False):
        super().__init__(dry_run=dry_run)
        self.token = os.environ.get("ZID_ACCESS_TOKEN", "")
        self.api_base = os.environ.get("ZID_API_BASE", "https://api.zid.sa/v1").rstrip("/")
        self.output_dir = Path(os.environ.get("ZID_EXPORT_DIR", "") or Path(__file__).resolve().parents[1] / "output")
        self.output_dir.mkdir(parents=True, exist_ok=True)

    def _headers(self) -> Dict[str, str]:
        return {
            "Authorization": f"Bearer {self.token}",
            "Content-Type": "application/json",
            "Accept": "application/json",
        }

    def _export_csv(self, items: List[Dict[str, Any]], suffix: str) -> str:
        ts = datetime.now(timezone.utc).strftime("%Y%m%dT%H%M%SZ")
        path = self.output_dir / f"zid_{suffix}_{ts}.csv"
        fields = [
            "sku",
            "name_ar",
            "name_en",
            "price_sar",
            "sellable_qty",
            "category_key",
            "external_category_id",
            "status",
            "brand",
            "barcode",
            "images",
        ]
        with open(path, "w", encoding="utf-8", newline="") as f:
            writer = csv.DictWriter(f, fieldnames=fields)
            writer.writeheader()
            for item in items:
                writer.writerow(
                    {
                        "sku": item.get("sku", ""),
                        "name_ar": item.get("name_ar", ""),
                        "name_en": item.get("name_en", ""),
                        "price_sar": item.get("price_sar", 0),
                        "sellable_qty": item.get("sellable_qty", 0),
                        "category_key": item.get("category_key", ""),
                        "external_category_id": item.get("external_category_id", ""),
                        "status": item.get("status", "draft"),
                        "brand": item.get("brand", ""),
                        "barcode": item.get("barcode", ""),
                        "images": ",".join(item.get("images", [])),
                    }
                )
        return str(path)

    def _catalog_payload(self, item: Dict[str, Any]) -> Dict[str, Any]:
        return {
            "sku": item.get("sku"),
            "name": item.get("name_ar") or item.get("name_en"),
            "description": item.get("desc_ar") or item.get("desc_en") or "",
            "price": item.get("price_sar", 0),
            "quantity": item.get("sellable_qty", 0),
            "category_id": item.get("external_category_id"),
            "images": item.get("images", []),
            "is_draft": item.get("status") != "publish",
            "barcode": item.get("barcode") or "",
            "weight": item.get("weight", 0),
        }

    def sync_catalog(self, items: List[Dict[str, Any]]) -> Dict[str, Any]:
        if self.dry_run or not self.token:
            export_file = self._export_csv(items, "catalog")
            out = []
            for item in items:
                out.append(
                    ConnectorItemResult(
                        sku=item.get("sku", ""),
                        success=True,
                        external_product_id=item.get("external_product_id") or item.get("sku"),
                        publish_state="publish" if item.get("status") == "publish" else "draft",
                        payload=self._catalog_payload(item),
                        response={"mode": "csv", "file": export_file, "dry_run": self.dry_run},
                    )
                )
            return self._summarize(out)

        results: List[ConnectorItemResult] = []
        for item in items:
            sku = str(item.get("sku") or "")
            payload = self._catalog_payload(item)
            ext_id = item.get("external_product_id")
            if ext_id:
                result = self._request(
                    "PUT",
                    f"{self.api_base}/products/{ext_id}",
                    headers=self._headers(),
                    json=payload,
                )
            else:
                result = self._request(
                    "POST",
                    f"{self.api_base}/products",
                    headers=self._headers(),
                    json=payload,
                )

            if result.get("ok"):
                data = result.get("data") or {}
                pid = str(data.get("id") or ext_id or sku)
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=True,
                        external_product_id=pid,
                        publish_state="publish" if item.get("status") == "publish" else "draft",
                        payload=payload,
                        response=data,
                    )
                )
            else:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=False,
                        external_product_id=str(ext_id or ""),
                        publish_state="draft",
                        error=str(result.get("error") or "Zid catalog sync failed"),
                        payload=payload,
                        response=result,
                    )
                )
        return self._summarize(results)

    def sync_inventory(self, items: List[Dict[str, Any]]) -> Dict[str, Any]:
        if self.dry_run or not self.token:
            export_file = self._export_csv(items, "inventory")
            out = []
            for item in items:
                out.append(
                    ConnectorItemResult(
                        sku=item.get("sku", ""),
                        success=True,
                        external_product_id=item.get("external_product_id") or item.get("sku"),
                        publish_state=item.get("publish_state", "draft"),
                        payload={"quantity": item.get("sellable_qty", 0)},
                        response={"mode": "csv", "file": export_file, "dry_run": self.dry_run},
                    )
                )
            return self._summarize(out)

        results: List[ConnectorItemResult] = []
        for item in items:
            sku = str(item.get("sku") or "")
            ext_id = item.get("external_product_id")
            payload = {"quantity": int(item.get("sellable_qty", 0))}
            if not ext_id:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=False,
                        error="Missing Zid external_product_id for inventory update",
                        payload=payload,
                    )
                )
                continue
            result = self._request(
                "PUT",
                f"{self.api_base}/products/{ext_id}/quantity",
                headers=self._headers(),
                json=payload,
            )
            if result.get("ok"):
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=True,
                        external_product_id=str(ext_id),
                        publish_state=item.get("publish_state", "draft"),
                        payload=payload,
                        response=result.get("data") or {},
                    )
                )
            else:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=False,
                        external_product_id=str(ext_id),
                        publish_state=item.get("publish_state", "draft"),
                        error=str(result.get("error") or "Zid inventory sync failed"),
                        payload=payload,
                        response=result,
                    )
                )
        return self._summarize(results)

    def sync_pricing(self, items: List[Dict[str, Any]]) -> Dict[str, Any]:
        if self.dry_run or not self.token:
            export_file = self._export_csv(items, "pricing")
            out = []
            for item in items:
                out.append(
                    ConnectorItemResult(
                        sku=item.get("sku", ""),
                        success=True,
                        external_product_id=item.get("external_product_id") or item.get("sku"),
                        publish_state=item.get("publish_state", "draft"),
                        payload={"price": item.get("price_sar", 0)},
                        response={"mode": "csv", "file": export_file, "dry_run": self.dry_run},
                    )
                )
            return self._summarize(out)

        results: List[ConnectorItemResult] = []
        for item in items:
            sku = str(item.get("sku") or "")
            ext_id = item.get("external_product_id")
            payload = {"price": float(item.get("price_sar", 0))}
            if not ext_id:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=False,
                        error="Missing Zid external_product_id for pricing update",
                        payload=payload,
                    )
                )
                continue
            result = self._request(
                "PUT",
                f"{self.api_base}/products/{ext_id}/price",
                headers=self._headers(),
                json=payload,
            )
            if result.get("ok"):
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=True,
                        external_product_id=str(ext_id),
                        publish_state=item.get("publish_state", "draft"),
                        payload=payload,
                        response=result.get("data") or {},
                    )
                )
            else:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=False,
                        external_product_id=str(ext_id),
                        publish_state=item.get("publish_state", "draft"),
                        error=str(result.get("error") or "Zid pricing sync failed"),
                        payload=payload,
                        response=result,
                    )
                )
        return self._summarize(results)
