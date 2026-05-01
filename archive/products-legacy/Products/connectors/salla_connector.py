#!/usr/bin/env python3
"""Salla connector adapter."""

from __future__ import annotations

import os
from typing import Any, Dict, List

from .base_connector import BaseConnector, ConnectorItemResult


class SallaConnector(BaseConnector):
    name = "salla"

    def __init__(self, dry_run: bool = False):
        super().__init__(dry_run=dry_run)
        self.access_token = os.environ.get("SALLA_ACCESS_TOKEN", "")
        self.api_base = os.environ.get("SALLA_API_BASE", "https://api.salla.dev/admin/v2").rstrip("/")
        if not dry_run and not self.access_token:
            raise RuntimeError("Missing SALLA_ACCESS_TOKEN for Salla connector")

    def _headers(self) -> Dict[str, str]:
        return {
            "Authorization": f"Bearer {self.access_token}",
            "Content-Type": "application/json",
            "Accept": "application/json",
        }

    def _catalog_payload(self, item: Dict[str, Any]) -> Dict[str, Any]:
        return {
            "name": item.get("name_ar") or item.get("name_en"),
            "description": item.get("desc_ar") or item.get("desc_en") or "",
            "sku": item.get("sku"),
            "price": item.get("price_sar", 0),
            "quantity": item.get("sellable_qty", 0),
            "status": "published" if item.get("status") == "publish" else "draft",
            "category_id": item.get("external_category_id"),
            "weight": item.get("weight", 0),
            "barcode": item.get("barcode") or "",
            "images": item.get("images", []),
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
                        publish_state="publish" if item.get("status") == "publish" else "draft",
                        payload=payload,
                        response={"dry_run": True},
                    )
                )
                continue

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
                pid = data.get("id") or ext_id or sku
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=True,
                        external_product_id=str(pid),
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
                        error=str(result.get("error") or "Salla catalog sync failed"),
                        payload=payload,
                        response=result,
                    )
                )

        return self._summarize(results)

    def sync_inventory(self, items: List[Dict[str, Any]]) -> Dict[str, Any]:
        results: List[ConnectorItemResult] = []
        for item in items:
            sku = str(item.get("sku") or "")
            ext_id = item.get("external_product_id")
            payload = {"quantity": int(item.get("sellable_qty", 0))}
            if self.dry_run:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=True,
                        external_product_id=ext_id or sku,
                        publish_state=item.get("publish_state", "draft"),
                        payload=payload,
                        response={"dry_run": True},
                    )
                )
                continue
            if not ext_id:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=False,
                        error="Missing Salla external_product_id for inventory update",
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
                        error=str(result.get("error") or "Salla inventory sync failed"),
                        payload=payload,
                        response=result,
                    )
                )
        return self._summarize(results)

    def sync_pricing(self, items: List[Dict[str, Any]]) -> Dict[str, Any]:
        results: List[ConnectorItemResult] = []
        for item in items:
            sku = str(item.get("sku") or "")
            ext_id = item.get("external_product_id")
            payload = {"price": float(item.get("price_sar", 0))}
            if self.dry_run:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=True,
                        external_product_id=ext_id or sku,
                        publish_state=item.get("publish_state", "draft"),
                        payload=payload,
                        response={"dry_run": True},
                    )
                )
                continue
            if not ext_id:
                results.append(
                    ConnectorItemResult(
                        sku=sku,
                        success=False,
                        error="Missing Salla external_product_id for pricing update",
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
                        error=str(result.get("error") or "Salla pricing sync failed"),
                        payload=payload,
                        response=result,
                    )
                )
        return self._summarize(results)
