#!/usr/bin/env python3
"""Shared connector behavior: retries, reporting, and dry-run support."""

from __future__ import annotations

import time
from dataclasses import dataclass
from typing import Any, Dict, List, Optional

import requests


@dataclass
class ConnectorItemResult:
    sku: str
    success: bool
    external_product_id: Optional[str] = None
    external_variant_id: Optional[str] = None
    publish_state: str = "draft"
    error: str = ""
    payload: Optional[Dict[str, Any]] = None
    response: Optional[Dict[str, Any]] = None

    def as_dict(self) -> Dict[str, Any]:
        return {
            "sku": self.sku,
            "success": self.success,
            "external_product_id": self.external_product_id,
            "external_variant_id": self.external_variant_id,
            "publish_state": self.publish_state,
            "error": self.error,
            "payload": self.payload or {},
            "response": self.response or {},
        }


class BaseConnector:
    name = "base"

    def __init__(self, dry_run: bool = False, max_retries: int = 3, timeout: int = 45):
        self.dry_run = dry_run
        self.max_retries = max_retries
        self.timeout = timeout
        self.session = requests.Session()

    def _request(self, method: str, url: str, **kwargs) -> Dict[str, Any]:
        if self.dry_run:
            return {"dry_run": True, "url": url, "method": method}

        error_messages: List[str] = []
        for attempt in range(self.max_retries):
            try:
                response = self.session.request(method, url, timeout=self.timeout, **kwargs)
                status = response.status_code
                if status == 429 or status >= 500:
                    error_messages.append(f"{status}:{response.text[:180]}")
                    time.sleep(2 ** attempt)
                    continue
                if status >= 400:
                    return {
                        "ok": False,
                        "status_code": status,
                        "error": response.text[:2000],
                    }
                data = None
                content_type = response.headers.get("Content-Type", "")
                if "application/json" in content_type:
                    data = response.json()
                return {"ok": True, "status_code": status, "data": data, "text": response.text}
            except requests.RequestException as exc:
                error_messages.append(str(exc))
                time.sleep(2 ** attempt)
        return {"ok": False, "error": " | ".join(error_messages)}

    def _summarize(self, items: List[ConnectorItemResult]) -> Dict[str, Any]:
        processed = len(items)
        success = sum(1 for i in items if i.success)
        failed = processed - success
        return {
            "processed": processed,
            "succeeded": success,
            "failed": failed,
            "items": [i.as_dict() for i in items],
        }
