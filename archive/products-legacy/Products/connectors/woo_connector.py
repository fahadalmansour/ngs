#!/usr/bin/env python3
"""WooCommerce channel connector."""

from __future__ import annotations

import os
from typing import Any, Dict, List, Optional

from requests.auth import HTTPBasicAuth

from .base_connector import BaseConnector, ConnectorItemResult


class WooConnector(BaseConnector):
    name = "woo"

    def __init__(self, dry_run: bool = False):
        super().__init__(dry_run=dry_run)
        self.store_url = (os.environ.get("STORE_URL") or "").rstrip("/")
        self.ck = os.environ.get("WC_CONSUMER_KEY", "")
        self.cs = os.environ.get("WC_CONSUMER_SECRET", "")
        if not self.store_url:
            raise RuntimeError("Missing STORE_URL for Woo connector")
        if not dry_run and (not self.ck or not self.cs):
            raise RuntimeError("Missing WC_CONSUMER_KEY/WC_CONSUMER_SECRET for Woo connector")

    def _auth(self) -> Optional[HTTPBasicAuth]:
        if self.dry_run:
            return None
        return HTTPBasicAuth(self.ck, self.cs)

    def _find_by_sku(self, sku: str) -> Optional[Dict[str, Any]]:
        result = self._request(
            "GET",
            f"{self.store_url}/wp-json/wc/v3/products",
            auth=self._auth(),
            params={"sku": sku, "per_page": 1},
        )
        if not result.get("ok"):
            return None
        data = result.get("data") or []
        if data:
            return data[0]
        return None

    def _product_payload(self, item: Dict[str, Any]) -> Dict[str, Any]:
        tags = []
        if item.get("brand"):
            tags.append({"name": str(item["brand"])})
        payload = {
            "name": item.get("name_en") or item.get("sku"),
            "type": "simple",
            "sku": item.get("sku"),
            "description": item.get("desc_en") or item.get("desc_ar") or "",
            "short_description": item.get("desc_en") or item.get("desc_ar") or "",
            "regular_price": str(item.get("price_sar", 0)),
            "manage_stock": True,
            "stock_quantity": int(item.get("sellable_qty", 0)),
            "status": "publish" if item.get("status") == "publish" else "draft",
            "categories": [{"name": item.get("category_key") or "Uncategorized"}],
            "tags": tags,
            "images": [{"src": img} for img in (item.get("images") or [])[:8]],
        }
        return payload

    def sync_catalog(self, items: List[Dict[str, Any]]) -> Dict[str, Any]:
        results: List[ConnectorItemResult] = []
        for item in items:
            sku = str(item.get("sku") or "").strip()
            payload = self._product_payload(item)
            if not sku:
                results.append(
                    ConnectorItemResult(
                        sku="",
                        success=False,
                        error="Missing SKU",
                        payload=payload,
                    )
                )
                continue
            if self.dry_run:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=True,
                        external_product_id=item.get("external_product_id"),
                        external_variant_id=item.get("external_variant_id"),
                        publish_state=payload["status"],
                        payload=payload,
                        response={"dry_run": True},
                    )
                )
                continue

            existing = self._find_by_sku(sku)
            if existing and existing.get("id"):
                result = self._request(
                    "PUT",
                    f"{self.store_url}/wp-json/wc/v3/products/{existing['id']}",
                    auth=self._auth(),
                    json=payload,
                )
            else:
                result = self._request(
                    "POST",
                    f"{self.store_url}/wp-json/wc/v3/products",
                    auth=self._auth(),
                    json=payload,
                )

            if result.get("ok"):
                data = result.get("data") or {}
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=True,
                        external_product_id=str(data.get("id") or ""),
                        publish_state=data.get("status", payload["status"]),
                        payload=payload,
                        response=data,
                    )
                )
            else:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=False,
                        publish_state=payload["status"],
                        error=str(result.get("error") or "Woo sync failed"),
                        payload=payload,
                        response=result,
                    )
                )

        return self._summarize(results)

    def sync_inventory(self, items: List[Dict[str, Any]]) -> Dict[str, Any]:
        results: List[ConnectorItemResult] = []
        for item in items:
            sku = str(item.get("sku") or "").strip()
            qty = int(item.get("sellable_qty", 0))
            payload = {"manage_stock": True, "stock_quantity": max(qty, 0)}
            if self.dry_run:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=True,
                        external_product_id=item.get("external_product_id"),
                        publish_state=item.get("publish_state", "draft"),
                        payload=payload,
                        response={"dry_run": True},
                    )
                )
                continue

            product_id = item.get("external_product_id")
            if not product_id:
                existing = self._find_by_sku(sku)
                product_id = str(existing.get("id")) if existing else ""

            if not product_id:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=False,
                        error="Woo product_id not found for inventory update",
                        payload=payload,
                    )
                )
                continue

            result = self._request(
                "PUT",
                f"{self.store_url}/wp-json/wc/v3/products/{product_id}",
                auth=self._auth(),
                json=payload,
            )
            if result.get("ok"):
                data = result.get("data") or {}
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=True,
                        external_product_id=str(data.get("id") or product_id),
                        publish_state=data.get("status", item.get("publish_state", "draft")),
                        payload=payload,
                        response=data,
                    )
                )
            else:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=False,
                        external_product_id=str(product_id),
                        error=str(result.get("error") or "Woo inventory sync failed"),
                        payload=payload,
                        response=result,
                    )
                )

        return self._summarize(results)

    def sync_pricing(self, items: List[Dict[str, Any]]) -> Dict[str, Any]:
        results: List[ConnectorItemResult] = []
        for item in items:
            sku = str(item.get("sku") or "").strip()
            payload = {"regular_price": str(item.get("price_sar", 0))}
            if self.dry_run:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=True,
                        external_product_id=item.get("external_product_id"),
                        publish_state=item.get("publish_state", "draft"),
                        payload=payload,
                        response={"dry_run": True},
                    )
                )
                continue

            product_id = item.get("external_product_id")
            if not product_id:
                existing = self._find_by_sku(sku)
                product_id = str(existing.get("id")) if existing else ""

            if not product_id:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=False,
                        error="Woo product_id not found for price update",
                        payload=payload,
                    )
                )
                continue

            result = self._request(
                "PUT",
                f"{self.store_url}/wp-json/wc/v3/products/{product_id}",
                auth=self._auth(),
                json=payload,
            )
            if result.get("ok"):
                data = result.get("data") or {}
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=True,
                        external_product_id=str(data.get("id") or product_id),
                        publish_state=data.get("status", item.get("publish_state", "draft")),
                        payload=payload,
                        response=data,
                    )
                )
            else:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=False,
                        external_product_id=str(product_id),
                        error=str(result.get("error") or "Woo pricing sync failed"),
                        payload=payload,
                        response=result,
                    )
                )

        return self._summarize(results)
