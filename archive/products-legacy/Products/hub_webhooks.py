#!/usr/bin/env python3
"""Minimal webhook receiver for order/cancel/return events.

Endpoints:
- POST /webhooks/{channel}/orders
- POST /webhooks/{channel}/cancellations
- POST /webhooks/{channel}/returns
"""

from __future__ import annotations

import argparse
import datetime as dt
import hashlib
import json
import os
from http import HTTPStatus
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from typing import Dict
from urllib.parse import urlparse

from hub_core import CHANNELS, HubDB, load_local_env


class WebhookHandler(BaseHTTPRequestHandler):
    db: HubDB = None  # type: ignore

    def _json_response(self, status: int, payload: Dict) -> None:
        raw = json.dumps(payload, ensure_ascii=False).encode("utf-8")
        self.send_response(status)
        self.send_header("Content-Type", "application/json")
        self.send_header("Content-Length", str(len(raw)))
        self.end_headers()
        self.wfile.write(raw)

    def do_POST(self):  # noqa: N802
        parsed = urlparse(self.path)
        parts = [p for p in parsed.path.split("/") if p]
        if len(parts) != 3 or parts[0] != "webhooks":
            self._json_response(HTTPStatus.NOT_FOUND, {"error": "Unsupported path"})
            return

        channel, event_type = parts[1], parts[2]
        if channel not in CHANNELS:
            self._json_response(HTTPStatus.BAD_REQUEST, {"error": f"Unsupported channel={channel}"})
            return
        if event_type not in {"orders", "cancellations", "returns"}:
            self._json_response(HTTPStatus.BAD_REQUEST, {"error": f"Unsupported event_type={event_type}"})
            return

        try:
            length = int(self.headers.get("Content-Length", "0"))
            body = self.rfile.read(length).decode("utf-8") if length > 0 else "{}"
            payload = json.loads(body or "{}")
        except Exception as exc:
            self._json_response(HTTPStatus.BAD_REQUEST, {"error": f"Invalid JSON body: {exc}"})
            return

        external_order_id = str(payload.get("order_id") or payload.get("id") or "unknown")
        event_ts = payload.get("event_ts") or payload.get("created_at") or dt.datetime.now(dt.timezone.utc).isoformat()
        try:
            parsed_ts = dt.datetime.fromisoformat(str(event_ts).replace("Z", "+00:00"))
        except ValueError:
            parsed_ts = dt.datetime.now(dt.timezone.utc)

        idem = self.headers.get("X-Idempotency-Key")
        if not idem:
            idem_seed = f"{channel}:{event_type}:{external_order_id}:{json.dumps(payload, sort_keys=True)}"
            idem = hashlib.sha256(idem_seed.encode("utf-8")).hexdigest()

        inserted = self.db.insert_order_event(
            channel=channel,
            external_order_id=external_order_id,
            event_type=event_type,
            event_ts=parsed_ts,
            payload=payload,
            idempotency_key=idem,
        )
        if not inserted:
            self._json_response(
                HTTPStatus.OK,
                {"status": "duplicate", "channel": channel, "event_type": event_type, "idempotency_key": idem},
            )
            return

        self.db.apply_order_event(event_type, payload)
        self._json_response(
            HTTPStatus.OK,
            {
                "status": "accepted",
                "channel": channel,
                "event_type": event_type,
                "external_order_id": external_order_id,
                "idempotency_key": idem,
            },
        )

    def log_message(self, fmt, *args):
        # Keep output concise in automation contexts.
        return


def main() -> int:
    parser = argparse.ArgumentParser(description="Run omnichannel webhook receiver")
    parser.add_argument("--host", default="0.0.0.0")
    parser.add_argument("--port", type=int, default=8788)
    args = parser.parse_args()

    load_local_env()
    db = HubDB(os.environ.get("HUB_DB_URL"))
    db.ensure_schema()
    db.seed_default_price_rules()

    WebhookHandler.db = db
    server = ThreadingHTTPServer((args.host, args.port), WebhookHandler)
    print(f"Listening on http://{args.host}:{args.port}")
    server.serve_forever()
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
