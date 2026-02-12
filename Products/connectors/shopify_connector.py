#!/usr/bin/env python3
"""Shopify connector adapter."""

from __future__ import annotations

import os
from typing import Any, Dict, List

from .base_connector import BaseConnector, ConnectorItemResult


class ShopifyConnector(BaseConnector):
    name = "shopify"

    def __init__(self, dry_run: bool = False):
        super().__init__(dry_run=dry_run)
        self.store = (os.environ.get("SHOPIFY_STORE") or "").strip()
        self.token = os.environ.get("SHOPIFY_ADMIN_TOKEN", "")
        self.version = os.environ.get("SHOPIFY_API_VERSION", "2024-10")
        if not self.store and dry_run:
            self.store = "dry-run-store.myshopify.com"
        if not self.store:
            raise RuntimeError("Missing SHOPIFY_STORE for Shopify connector")
        if not dry_run and not self.token:
            raise RuntimeError("Missing SHOPIFY_ADMIN_TOKEN for Shopify connector")
        self.base_url = f"https://{self.store}/admin/api/{self.version}"

    def _headers(self) -> Dict[str, str]:
        return {
            "X-Shopify-Access-Token": self.token,
            "Content-Type": "application/json",
            "Accept": "application/json",
        }

    def _catalog_payload(self, item: Dict[str, Any]) -> Dict[str, Any]:
        status = "active" if item.get("status") == "publish" else "draft"
        return {
            "product": {
                "title": item.get("name_en") or item.get("name_ar") or item.get("sku"),
                "body_html": item.get("desc_en") or item.get("desc_ar") or "",
                "vendor": item.get("brand") or "NGS",
                "status": status,
                "product_type": item.get("category_key") or "Smart Home",
                "variants": [
                    {
                        "sku": item.get("sku"),
                        "price": str(item.get("price_sar", 0)),
                        "inventory_management": "shopify",
                        "inventory_quantity": int(item.get("sellable_qty", 0)),
                        "barcode": item.get("barcode") or None,
                        "weight": float(item.get("weight", 0)),
                        "weight_unit": "kg",
                    }
                ],
                "images": [{"src": url} for url in (item.get("images") or [])[:8]],
                "tags": ",".join([item.get("category_key", ""), item.get("brand", "")]).strip(","),
            }
        }

    def sync_catalog(self, items: List[Dict[str, Any]]) -> Dict[str, Any]:
        results: List[ConnectorItemResult] = []
        for item in items:
            sku = str(item.get("sku") or "")
            payload = self._catalog_payload(item)
            if self.dry_run:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=True,
                        external_product_id=item.get("external_product_id") or sku,
                        external_variant_id=item.get("external_variant_id"),
                        publish_state="publish" if item.get("status") == "publish" else "draft",
                        payload=payload,
                        response={"dry_run": True},
                    )
                )
                continue

            ext_product_id = item.get("external_product_id")
            if ext_product_id:
                result = self._request(
                    "PUT",
                    f"{self.base_url}/products/{ext_product_id}.json",
                    headers=self._headers(),
                    json=payload,
                )
            else:
                result = self._request(
                    "POST",
                    f"{self.base_url}/products.json",
                    headers=self._headers(),
                    json=payload,
                )

            if result.get("ok"):
                data = (result.get("data") or {}).get("product") or {}
                pid = data.get("id") or ext_product_id or sku
                vid = None
                variants = data.get("variants") or []
                if variants:
                    vid = variants[0].get("id")
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=True,
                        external_product_id=str(pid),
                        external_variant_id=str(vid) if vid else None,
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
                        error=str(result.get("error") or "Shopify catalog sync failed"),
                        payload=payload,
                        response=result,
                    )
                )
        return self._summarize(results)

    def sync_inventory(self, items: List[Dict[str, Any]]) -> Dict[str, Any]:
        # Inventory in Shopify often requires location + inventory_item_id.
        # This implementation updates variant quantity where variant id is known.
        results: List[ConnectorItemResult] = []
        for item in items:
            sku = str(item.get("sku") or "")
            variant_id = item.get("external_variant_id")
            payload = {"variant": {"id": variant_id, "inventory_quantity": int(item.get("sellable_qty", 0))}}
            if self.dry_run:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=True,
                        external_product_id=item.get("external_product_id") or sku,
                        external_variant_id=str(variant_id) if variant_id else None,
                        publish_state=item.get("publish_state", "draft"),
                        payload=payload,
                        response={"dry_run": True},
                    )
                )
                continue

            if not variant_id:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=False,
                        error="Missing Shopify external_variant_id for inventory update",
                        payload=payload,
                    )
                )
                continue

            result = self._request(
                "PUT",
                f"{self.base_url}/variants/{variant_id}.json",
                headers=self._headers(),
                json=payload,
            )
            if result.get("ok"):
                data = (result.get("data") or {}).get("variant") or {}
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=True,
                        external_product_id=str(item.get("external_product_id") or ""),
                        external_variant_id=str(data.get("id") or variant_id),
                        publish_state=item.get("publish_state", "draft"),
                        payload=payload,
                        response=data,
                    )
                )
            else:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=False,
                        external_product_id=str(item.get("external_product_id") or ""),
                        external_variant_id=str(variant_id),
                        publish_state=item.get("publish_state", "draft"),
                        error=str(result.get("error") or "Shopify inventory sync failed"),
                        payload=payload,
                        response=result,
                    )
                )
        return self._summarize(results)

    def sync_pricing(self, items: List[Dict[str, Any]]) -> Dict[str, Any]:
        results: List[ConnectorItemResult] = []
        for item in items:
            sku = str(item.get("sku") or "")
            variant_id = item.get("external_variant_id")
            payload = {"variant": {"id": variant_id, "price": str(item.get("price_sar", 0))}}
            if self.dry_run:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=True,
                        external_product_id=item.get("external_product_id") or sku,
                        external_variant_id=str(variant_id) if variant_id else None,
                        publish_state=item.get("publish_state", "draft"),
                        payload=payload,
                        response={"dry_run": True},
                    )
                )
                continue

            if not variant_id:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=False,
                        error="Missing Shopify external_variant_id for pricing update",
                        payload=payload,
                    )
                )
                continue

            result = self._request(
                "PUT",
                f"{self.base_url}/variants/{variant_id}.json",
                headers=self._headers(),
                json=payload,
            )
            if result.get("ok"):
                data = (result.get("data") or {}).get("variant") or {}
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=True,
                        external_product_id=str(item.get("external_product_id") or ""),
                        external_variant_id=str(data.get("id") or variant_id),
                        publish_state=item.get("publish_state", "draft"),
                        payload=payload,
                        response=data,
                    )
                )
            else:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=False,
                        external_product_id=str(item.get("external_product_id") or ""),
                        external_variant_id=str(variant_id),
                        publish_state=item.get("publish_state", "draft"),
                        error=str(result.get("error") or "Shopify pricing sync failed"),
                        payload=payload,
                        response=result,
                    )
                )
        return self._summarize(results)
