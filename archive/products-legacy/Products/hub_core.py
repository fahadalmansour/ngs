#!/usr/bin/env python3
"""Shared data and DB utilities for omnichannel sync."""

from __future__ import annotations

import csv
import datetime as dt
import hashlib
import json
import math
import os
import re
import sqlite3
from dataclasses import dataclass
from pathlib import Path
from typing import Any, Dict, Iterable, List, Optional, Sequence
from urllib.parse import urlparse

BASE_DIR = Path(__file__).resolve().parent
SCHEMA_PATH = BASE_DIR / "sql" / "001_hub_schema.sql"
DEFAULT_SQLITE_PATH = BASE_DIR / "omnichannel_hub.db"

CHANNELS = ("woo", "zid", "salla", "shopify")
SCOPE_TO_FILE = {
    "top50": BASE_DIR / "woocommerce-top-sell-balanced-50.csv",
    "top100": BASE_DIR / "woocommerce-top-sell-balanced-100.csv",
    "top200": BASE_DIR / "woocommerce-top-sell-balanced-200.csv",
}
SCOPE_LIMIT = {"top50": 50, "top100": 100, "top200": 200}
STAGE_TO_SCOPE = {"wave50": "top50", "wave100": "top100", "wave200": "top200"}

DEFAULT_PRICE_RULES = {
    "woo": {"fee_pct": 2.50, "payment_pct": 2.00, "ops_buffer_sar": 2.0, "round_rule": "nearest_9", "active": True},
    "zid": {"fee_pct": 3.20, "payment_pct": 1.80, "ops_buffer_sar": 3.0, "round_rule": "nearest_9", "active": True},
    "salla": {"fee_pct": 3.00, "payment_pct": 1.90, "ops_buffer_sar": 3.0, "round_rule": "nearest_9", "active": True},
    "shopify": {"fee_pct": 2.90, "payment_pct": 1.70, "ops_buffer_sar": 4.0, "round_rule": "nearest_9", "active": True},
}


class HubConfigError(RuntimeError):
    """Raised when required omnichannel configuration is missing."""


@dataclass
class ProductRow:
    sku: str
    name_ar: str
    name_en: str
    desc_ar: str
    desc_en: str
    brand: str
    status: str
    category_key: str
    weight: float
    barcode: str
    images: List[str]
    source_scope: str
    stock_on_hand: int
    reserved_qty: int
    safety_stock: int
    base_cost_sar: float
    target_margin_pct: float
    vat_included_bool: bool


def load_local_env(env_path: Optional[Path] = None) -> None:
    """Load .env values into process environment if missing."""
    path = env_path or (BASE_DIR / ".env")
    if not path.exists():
        return
    for raw_line in path.read_text(encoding="utf-8").splitlines():
        line = raw_line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, value = line.split("=", 1)
        os.environ.setdefault(key.strip(), value.strip())


def now_utc() -> dt.datetime:
    return dt.datetime.now(dt.timezone.utc)


def slugify(value: str) -> str:
    cleaned = re.sub(r"[^a-zA-Z0-9]+", "-", (value or "").strip().lower())
    cleaned = re.sub(r"-+", "-", cleaned).strip("-")
    return cleaned or "uncategorized"


def _to_float(value: Any, default: float = 0.0) -> float:
    try:
        if value in (None, ""):
            return default
        return float(value)
    except (TypeError, ValueError):
        return default


def _to_int(value: Any, default: int = 0) -> int:
    try:
        if value in (None, ""):
            return default
        return int(float(value))
    except (TypeError, ValueError):
        return default


def _to_bool(value: Any, default: bool = True) -> bool:
    if isinstance(value, bool):
        return value
    if value is None:
        return default
    text = str(value).strip().lower()
    if text in {"1", "true", "yes", "y"}:
        return True
    if text in {"0", "false", "no", "n"}:
        return False
    return default


def hash_payload(payload: Dict[str, Any]) -> str:
    encoded = json.dumps(payload, sort_keys=True, ensure_ascii=False).encode("utf-8")
    return hashlib.sha256(encoded).hexdigest()


def round_nearest_9(value: float) -> float:
    if value <= 0:
        return 9.0
    candidate = math.ceil(value / 10.0) * 10.0 - 1.0
    while candidate < value:
        candidate += 10.0
    return round(candidate, 2)


def compute_channel_price(
    base_cost_sar: float,
    target_margin_pct: float,
    vat_included_bool: bool,
    fee_pct: float,
    payment_pct: float,
    ops_buffer_sar: float,
    round_rule: str,
) -> float:
    margin_factor = 1.0 + max(target_margin_pct, 0.0) / 100.0
    base_price = max(base_cost_sar, 0.0) * margin_factor
    fee_factor = 1.0 + (max(fee_pct, 0.0) + max(payment_pct, 0.0)) / 100.0
    channel_price = base_price * fee_factor + max(ops_buffer_sar, 0.0)
    if not vat_included_bool:
        channel_price *= 1.15
    if round_rule == "nearest_9":
        return round_nearest_9(channel_price)
    return round(channel_price, 2)


class HubDB:
    """DB abstraction for Postgres (preferred) and SQLite (local fallback)."""

    def __init__(self, db_url: Optional[str], schema_path: Path = SCHEMA_PATH):
        self.db_url = (db_url or "").strip()
        if not self.db_url:
            self.db_url = f"sqlite:///{DEFAULT_SQLITE_PATH}"
        parsed = urlparse(self.db_url)
        scheme = parsed.scheme.lower()
        if scheme in {"postgres", "postgresql"}:
            self.backend = "postgres"
        elif scheme == "sqlite":
            self.backend = "sqlite"
        elif not scheme and self.db_url.endswith(".db"):
            self.backend = "sqlite"
            self.db_url = f"sqlite:///{self.db_url}"
        else:
            raise HubConfigError(
                "Unsupported HUB_DB_URL. Use postgresql://... or sqlite:///..."
            )
        self.schema_path = schema_path

    def _pg_connect(self):
        try:
            import psycopg
            from psycopg.rows import dict_row
        except ImportError as exc:
            raise HubConfigError(
                "psycopg is required for Postgres HUB_DB_URL. Install with: pip install psycopg[binary]"
            ) from exc
        conn = psycopg.connect(self.db_url, row_factory=dict_row)
        conn.autocommit = False
        return conn

    def _sqlite_path(self) -> str:
        if self.db_url.startswith("sqlite:///"):
            return self.db_url.replace("sqlite:///", "", 1)
        return str(DEFAULT_SQLITE_PATH)

    def _sqlite_connect(self):
        path = self._sqlite_path()
        conn = sqlite3.connect(path)
        conn.row_factory = sqlite3.Row
        return conn

    def _connect(self):
        if self.backend == "postgres":
            return self._pg_connect()
        return self._sqlite_connect()

    def ensure_schema(self) -> None:
        if self.backend == "postgres":
            self._ensure_schema_postgres()
        else:
            self._ensure_schema_sqlite()

    def _ensure_schema_postgres(self) -> None:
        sql = self.schema_path.read_text(encoding="utf-8")
        with self._connect() as conn:
            with conn.cursor() as cur:
                cur.execute(sql)
            conn.commit()

    def _ensure_schema_sqlite(self) -> None:
        statements = [
            """
            CREATE TABLE IF NOT EXISTS catalog_products (
                sku TEXT PRIMARY KEY,
                name_ar TEXT NOT NULL DEFAULT '',
                name_en TEXT NOT NULL,
                desc_ar TEXT NOT NULL DEFAULT '',
                desc_en TEXT NOT NULL DEFAULT '',
                brand TEXT NOT NULL DEFAULT '',
                status TEXT NOT NULL DEFAULT 'draft',
                category_key TEXT NOT NULL DEFAULT 'uncategorized',
                weight REAL NOT NULL DEFAULT 0,
                barcode TEXT,
                images_json TEXT NOT NULL DEFAULT '[]',
                source_scope TEXT NOT NULL DEFAULT 'top50',
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
            """,
            """
            CREATE TABLE IF NOT EXISTS catalog_inventory (
                sku TEXT PRIMARY KEY REFERENCES catalog_products(sku) ON DELETE CASCADE,
                stock_on_hand INTEGER NOT NULL DEFAULT 0,
                reserved_qty INTEGER NOT NULL DEFAULT 0,
                safety_stock INTEGER NOT NULL DEFAULT 0,
                sellable_qty INTEGER NOT NULL DEFAULT 0,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
            """,
            """
            CREATE TABLE IF NOT EXISTS catalog_pricing (
                sku TEXT PRIMARY KEY REFERENCES catalog_products(sku) ON DELETE CASCADE,
                base_cost_sar REAL NOT NULL DEFAULT 0,
                target_margin_pct REAL NOT NULL DEFAULT 25,
                vat_included_bool INTEGER NOT NULL DEFAULT 1,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
            """,
            """
            CREATE TABLE IF NOT EXISTS channel_listing (
                channel TEXT NOT NULL,
                sku TEXT NOT NULL REFERENCES catalog_products(sku) ON DELETE CASCADE,
                external_product_id TEXT,
                external_variant_id TEXT,
                publish_state TEXT NOT NULL DEFAULT 'draft',
                last_payload_hash TEXT,
                last_payload TEXT,
                last_response TEXT,
                last_sync_at TEXT,
                last_error TEXT,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (channel, sku)
            )
            """,
            """
            CREATE TABLE IF NOT EXISTS channel_price_rules (
                channel TEXT PRIMARY KEY,
                fee_pct REAL NOT NULL DEFAULT 0,
                payment_pct REAL NOT NULL DEFAULT 0,
                ops_buffer_sar REAL NOT NULL DEFAULT 0,
                round_rule TEXT NOT NULL DEFAULT 'nearest_9',
                active INTEGER NOT NULL DEFAULT 1,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
            """,
            """
            CREATE TABLE IF NOT EXISTS channel_category_map (
                channel TEXT NOT NULL,
                category_key TEXT NOT NULL,
                external_category_id TEXT NOT NULL,
                active INTEGER NOT NULL DEFAULT 1,
                updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (channel, category_key)
            )
            """,
            """
            CREATE TABLE IF NOT EXISTS order_events (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                channel TEXT NOT NULL,
                external_order_id TEXT NOT NULL,
                event_type TEXT NOT NULL,
                event_ts TEXT NOT NULL,
                payload_json TEXT NOT NULL,
                idempotency_key TEXT NOT NULL UNIQUE,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
            """,
            """
            CREATE TABLE IF NOT EXISTS sync_jobs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                channel TEXT NOT NULL,
                mode TEXT NOT NULL,
                scope TEXT NOT NULL,
                started_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                ended_at TEXT,
                status TEXT NOT NULL DEFAULT 'running',
                dry_run INTEGER NOT NULL DEFAULT 0,
                processed_count INTEGER NOT NULL DEFAULT 0,
                success_count INTEGER NOT NULL DEFAULT 0,
                failed_count INTEGER NOT NULL DEFAULT 0,
                error_summary TEXT
            )
            """,
            """
            CREATE TABLE IF NOT EXISTS dead_letter_queue (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                channel TEXT NOT NULL,
                mode TEXT NOT NULL,
                sku TEXT,
                payload_json TEXT,
                error TEXT NOT NULL,
                retries INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                resolved_at TEXT
            )
            """,
        ]
        with self._connect() as conn:
            cur = conn.cursor()
            for stmt in statements:
                cur.execute(stmt)
            conn.commit()

    def fetch_all(self, query_pg: str, query_sqlite: str, params: Sequence[Any] = ()) -> List[Dict[str, Any]]:
        with self._connect() as conn:
            if self.backend == "postgres":
                cur = conn.cursor()
                cur.execute(query_pg, params)
                rows = cur.fetchall()
                conn.commit()
                return list(rows)
            cur = conn.cursor()
            cur.execute(query_sqlite, params)
            rows = [dict(r) for r in cur.fetchall()]
            conn.commit()
            return rows

    def execute(self, query_pg: str, query_sqlite: str, params: Sequence[Any] = ()) -> None:
        with self._connect() as conn:
            cur = conn.cursor()
            if self.backend == "postgres":
                cur.execute(query_pg, params)
            else:
                cur.execute(query_sqlite, params)
            conn.commit()

    def seed_default_price_rules(self) -> None:
        for channel, rule in DEFAULT_PRICE_RULES.items():
            self.execute(
                """
                INSERT INTO channel_price_rules
                (channel, fee_pct, payment_pct, ops_buffer_sar, round_rule, active)
                VALUES (%s, %s, %s, %s, %s, %s)
                ON CONFLICT (channel) DO UPDATE SET
                    fee_pct = EXCLUDED.fee_pct,
                    payment_pct = EXCLUDED.payment_pct,
                    ops_buffer_sar = EXCLUDED.ops_buffer_sar,
                    round_rule = EXCLUDED.round_rule,
                    active = EXCLUDED.active
                """,
                """
                INSERT INTO channel_price_rules
                (channel, fee_pct, payment_pct, ops_buffer_sar, round_rule, active)
                VALUES (?, ?, ?, ?, ?, ?)
                ON CONFLICT(channel) DO UPDATE SET
                    fee_pct = excluded.fee_pct,
                    payment_pct = excluded.payment_pct,
                    ops_buffer_sar = excluded.ops_buffer_sar,
                    round_rule = excluded.round_rule,
                    active = excluded.active
                """,
                (
                    channel,
                    rule["fee_pct"],
                    rule["payment_pct"],
                    rule["ops_buffer_sar"],
                    rule["round_rule"],
                    bool(rule["active"]),
                ),
            )

    def upsert_product_row(self, row: ProductRow) -> None:
        images_json = json.dumps(row.images, ensure_ascii=False)
        self.execute(
            """
            INSERT INTO catalog_products
            (sku, name_ar, name_en, desc_ar, desc_en, brand, status, category_key, weight, barcode, images_json, source_scope)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s::jsonb, %s)
            ON CONFLICT (sku) DO UPDATE SET
                name_ar = EXCLUDED.name_ar,
                name_en = EXCLUDED.name_en,
                desc_ar = EXCLUDED.desc_ar,
                desc_en = EXCLUDED.desc_en,
                brand = EXCLUDED.brand,
                status = EXCLUDED.status,
                category_key = EXCLUDED.category_key,
                weight = EXCLUDED.weight,
                barcode = EXCLUDED.barcode,
                images_json = EXCLUDED.images_json,
                source_scope = EXCLUDED.source_scope
            """,
            """
            INSERT INTO catalog_products
            (sku, name_ar, name_en, desc_ar, desc_en, brand, status, category_key, weight, barcode, images_json, source_scope)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON CONFLICT(sku) DO UPDATE SET
                name_ar = excluded.name_ar,
                name_en = excluded.name_en,
                desc_ar = excluded.desc_ar,
                desc_en = excluded.desc_en,
                brand = excluded.brand,
                status = excluded.status,
                category_key = excluded.category_key,
                weight = excluded.weight,
                barcode = excluded.barcode,
                images_json = excluded.images_json,
                source_scope = excluded.source_scope,
                updated_at = CURRENT_TIMESTAMP
            """,
            (
                row.sku,
                row.name_ar,
                row.name_en,
                row.desc_ar,
                row.desc_en,
                row.brand,
                row.status,
                row.category_key,
                row.weight,
                row.barcode,
                images_json,
                row.source_scope,
            ),
        )

        if self.backend == "postgres":
            self.execute(
                """
                INSERT INTO catalog_inventory
                (sku, stock_on_hand, reserved_qty, safety_stock)
                VALUES (%s, %s, %s, %s)
                ON CONFLICT (sku) DO UPDATE SET
                    stock_on_hand = EXCLUDED.stock_on_hand,
                    reserved_qty = EXCLUDED.reserved_qty,
                    safety_stock = EXCLUDED.safety_stock
                """,
                """
                INSERT INTO catalog_inventory
                (sku, stock_on_hand, reserved_qty, safety_stock)
                VALUES (?, ?, ?, ?)
                ON CONFLICT(sku) DO UPDATE SET
                    stock_on_hand = excluded.stock_on_hand,
                    reserved_qty = excluded.reserved_qty,
                    safety_stock = excluded.safety_stock
                """,
                (row.sku, row.stock_on_hand, row.reserved_qty, row.safety_stock),
            )
        else:
            self.execute(
                """
                INSERT INTO catalog_inventory
                (sku, stock_on_hand, reserved_qty, safety_stock, sellable_qty)
                VALUES (%s, %s, %s, %s, %s)
                ON CONFLICT (sku) DO UPDATE SET
                    stock_on_hand = EXCLUDED.stock_on_hand,
                    reserved_qty = EXCLUDED.reserved_qty,
                    safety_stock = EXCLUDED.safety_stock,
                    sellable_qty = EXCLUDED.sellable_qty
                """,
                """
                INSERT INTO catalog_inventory
                (sku, stock_on_hand, reserved_qty, safety_stock, sellable_qty)
                VALUES (?, ?, ?, ?, ?)
                ON CONFLICT(sku) DO UPDATE SET
                    stock_on_hand = excluded.stock_on_hand,
                    reserved_qty = excluded.reserved_qty,
                    safety_stock = excluded.safety_stock,
                    sellable_qty = excluded.sellable_qty,
                    updated_at = CURRENT_TIMESTAMP
                """,
                (
                    row.sku,
                    row.stock_on_hand,
                    row.reserved_qty,
                    row.safety_stock,
                    max(row.stock_on_hand - row.reserved_qty - row.safety_stock, 0),
                ),
            )

        self.execute(
            """
            INSERT INTO catalog_pricing
            (sku, base_cost_sar, target_margin_pct, vat_included_bool)
            VALUES (%s, %s, %s, %s)
            ON CONFLICT (sku) DO UPDATE SET
                base_cost_sar = EXCLUDED.base_cost_sar,
                target_margin_pct = EXCLUDED.target_margin_pct,
                vat_included_bool = EXCLUDED.vat_included_bool
            """,
            """
            INSERT INTO catalog_pricing
            (sku, base_cost_sar, target_margin_pct, vat_included_bool)
            VALUES (?, ?, ?, ?)
            ON CONFLICT(sku) DO UPDATE SET
                base_cost_sar = excluded.base_cost_sar,
                target_margin_pct = excluded.target_margin_pct,
                vat_included_bool = excluded.vat_included_bool,
                updated_at = CURRENT_TIMESTAMP
            """,
            (
                row.sku,
                row.base_cost_sar,
                row.target_margin_pct,
                int(row.vat_included_bool),
            ),
        )

    def seed_category_map_from_scope(self, scope: str) -> None:
        rows = self.fetch_all(
            "SELECT DISTINCT category_key FROM catalog_products WHERE source_scope = %s",
            "SELECT DISTINCT category_key FROM catalog_products WHERE source_scope = ?",
            (scope,),
        )
        categories = [r["category_key"] for r in rows if r.get("category_key")]
        for channel in CHANNELS:
            for category in categories:
                self.execute(
                    """
                    INSERT INTO channel_category_map(channel, category_key, external_category_id, active)
                    VALUES (%s, %s, %s, %s)
                    ON CONFLICT (channel, category_key) DO NOTHING
                    """,
                    """
                    INSERT INTO channel_category_map(channel, category_key, external_category_id, active)
                    VALUES (?, ?, ?, ?)
                    ON CONFLICT(channel, category_key) DO NOTHING
                    """,
                    (channel, category, slugify(category), True),
                )

    def load_products_from_csv(self, scope: str, csv_path: Optional[Path] = None) -> int:
        if scope not in SCOPE_TO_FILE and scope != "active":
            raise HubConfigError(f"Unsupported scope: {scope}")
        if scope == "active":
            raise HubConfigError("Active scope is read-only and cannot be loaded from CSV")
        path = csv_path or SCOPE_TO_FILE[scope]
        if not Path(path).exists():
            raise FileNotFoundError(f"Scope CSV not found: {path}")

        count = 0
        with open(path, "r", encoding="utf-8") as f:
            reader = csv.DictReader(f)
            for row in reader:
                sku = (row.get("SKU") or "").strip()
                if not sku:
                    continue
                name_en = (row.get("Name") or "").strip() or sku
                description = (row.get("Description") or "").strip()
                price = _to_float(row.get("Regular price"), 0.0)
                margin = _to_float(row.get("Meta: _margin"), 25.0)
                base_cost = _to_float(row.get("Meta: _cost"), 0.0)
                if base_cost <= 0 and price > 0:
                    base_cost = price / (1 + max(margin, 0.0) / 100.0)
                stock = _to_int(row.get("Stock"), 0)
                images_field = (row.get("Images") or "").strip()
                images = [x.strip() for x in images_field.split(",") if x.strip()]
                status = "publish" if str(row.get("Published", "0")).strip() == "1" else "draft"
                product = ProductRow(
                    sku=sku,
                    name_ar=name_en,
                    name_en=name_en,
                    desc_ar=description,
                    desc_en=description,
                    brand=(row.get("Brands") or "").strip(),
                    status=status,
                    category_key=(row.get("Categories") or "uncategorized").strip() or "uncategorized",
                    weight=_to_float(row.get("Weight (kg)"), 0.0),
                    barcode=(row.get("barcode") or row.get("Barcode") or "").strip(),
                    images=images,
                    source_scope=scope,
                    stock_on_hand=max(stock, 0),
                    reserved_qty=0,
                    safety_stock=1,
                    base_cost_sar=max(base_cost, 0.0),
                    target_margin_pct=max(margin, 0.0),
                    vat_included_bool=True,
                )
                self.upsert_product_row(product)
                count += 1

        self.seed_category_map_from_scope(scope)
        return count

    def ensure_scope_loaded(self, scope: str, csv_path: Optional[Path] = None) -> int:
        if scope == "active":
            return 0
        rows = self.fetch_all(
            "SELECT COUNT(*) AS c FROM catalog_products WHERE source_scope = %s",
            "SELECT COUNT(*) AS c FROM catalog_products WHERE source_scope = ?",
            (scope,),
        )
        existing = int(rows[0]["c"] if rows else 0)
        if existing >= SCOPE_LIMIT.get(scope, 0):
            return existing
        return self.load_products_from_csv(scope, csv_path)

    def get_price_rule(self, channel: str) -> Dict[str, Any]:
        rows = self.fetch_all(
            "SELECT channel, fee_pct, payment_pct, ops_buffer_sar, round_rule, active FROM channel_price_rules WHERE channel = %s",
            "SELECT channel, fee_pct, payment_pct, ops_buffer_sar, round_rule, active FROM channel_price_rules WHERE channel = ?",
            (channel,),
        )
        if not rows:
            raise HubConfigError(f"Missing channel_price_rules entry for channel={channel}")
        row = rows[0]
        row["active"] = _to_bool(row.get("active"), True)
        return row

    def get_products_for_scope(self, scope: str, channel: str) -> List[Dict[str, Any]]:
        if scope == "active":
            rows = self.fetch_all(
                """
                SELECT p.*, i.stock_on_hand, i.reserved_qty, i.safety_stock, i.sellable_qty,
                       r.base_cost_sar, r.target_margin_pct, r.vat_included_bool,
                       l.external_product_id, l.external_variant_id, l.publish_state
                FROM catalog_products p
                JOIN channel_listing l ON l.sku = p.sku AND l.channel = %s
                LEFT JOIN catalog_inventory i ON i.sku = p.sku
                LEFT JOIN catalog_pricing r ON r.sku = p.sku
                WHERE l.publish_state IN ('publish', 'active', 'draft')
                ORDER BY p.sku ASC
                """,
                """
                SELECT p.*, i.stock_on_hand, i.reserved_qty, i.safety_stock, i.sellable_qty,
                       r.base_cost_sar, r.target_margin_pct, r.vat_included_bool,
                       l.external_product_id, l.external_variant_id, l.publish_state
                FROM catalog_products p
                JOIN channel_listing l ON l.sku = p.sku AND l.channel = ?
                LEFT JOIN catalog_inventory i ON i.sku = p.sku
                LEFT JOIN catalog_pricing r ON r.sku = p.sku
                WHERE l.publish_state IN ('publish', 'active', 'draft')
                ORDER BY p.sku ASC
                """,
                (channel,),
            )
            return [self._hydrate_row(r) for r in rows]

        rows = self.fetch_all(
            """
            SELECT p.*, i.stock_on_hand, i.reserved_qty, i.safety_stock, i.sellable_qty,
                   r.base_cost_sar, r.target_margin_pct, r.vat_included_bool,
                   l.external_product_id, l.external_variant_id, l.publish_state
            FROM catalog_products p
            LEFT JOIN catalog_inventory i ON i.sku = p.sku
            LEFT JOIN catalog_pricing r ON r.sku = p.sku
            LEFT JOIN channel_listing l ON l.sku = p.sku AND l.channel = %s
            WHERE p.source_scope = %s
            ORDER BY p.sku ASC
            LIMIT %s
            """,
            """
            SELECT p.*, i.stock_on_hand, i.reserved_qty, i.safety_stock, i.sellable_qty,
                   r.base_cost_sar, r.target_margin_pct, r.vat_included_bool,
                   l.external_product_id, l.external_variant_id, l.publish_state
            FROM catalog_products p
            LEFT JOIN catalog_inventory i ON i.sku = p.sku
            LEFT JOIN catalog_pricing r ON r.sku = p.sku
            LEFT JOIN channel_listing l ON l.sku = p.sku AND l.channel = ?
            WHERE p.source_scope = ?
            ORDER BY p.sku ASC
            LIMIT ?
            """,
            (channel, scope, SCOPE_LIMIT.get(scope, 200)),
        )
        return [self._hydrate_row(r) for r in rows]

    def _hydrate_row(self, row: Dict[str, Any]) -> Dict[str, Any]:
        out = dict(row)
        images_raw = out.get("images_json") or "[]"
        if isinstance(images_raw, str):
            try:
                out["images"] = json.loads(images_raw)
            except json.JSONDecodeError:
                out["images"] = []
        elif isinstance(images_raw, list):
            out["images"] = images_raw
        else:
            out["images"] = []
        out["sellable_qty"] = max(
            _to_int(out.get("stock_on_hand"), 0)
            - _to_int(out.get("reserved_qty"), 0)
            - _to_int(out.get("safety_stock"), 0),
            0,
        )
        out["vat_included_bool"] = _to_bool(out.get("vat_included_bool"), True)
        return out

    def get_category_map(self, channel: str, category_key: str) -> Optional[str]:
        rows = self.fetch_all(
            "SELECT external_category_id FROM channel_category_map WHERE channel = %s AND category_key = %s AND active = TRUE",
            "SELECT external_category_id FROM channel_category_map WHERE channel = ? AND category_key = ? AND active = 1",
            (channel, category_key),
        )
        if not rows:
            return None
        return rows[0]["external_category_id"]

    def upsert_channel_listing(
        self,
        channel: str,
        sku: str,
        external_product_id: Optional[str],
        external_variant_id: Optional[str],
        publish_state: str,
        payload: Dict[str, Any],
        response: Dict[str, Any],
        error: Optional[str],
    ) -> None:
        payload_hash = hash_payload(payload)
        payload_json = json.dumps(payload, ensure_ascii=False)
        response_json = json.dumps(response, ensure_ascii=False)
        self.execute(
            """
            INSERT INTO channel_listing
            (channel, sku, external_product_id, external_variant_id, publish_state, last_payload_hash, last_payload, last_response, last_sync_at, last_error)
            VALUES (%s, %s, %s, %s, %s, %s, %s::jsonb, %s::jsonb, NOW(), %s)
            ON CONFLICT (channel, sku) DO UPDATE SET
                external_product_id = EXCLUDED.external_product_id,
                external_variant_id = EXCLUDED.external_variant_id,
                publish_state = EXCLUDED.publish_state,
                last_payload_hash = EXCLUDED.last_payload_hash,
                last_payload = EXCLUDED.last_payload,
                last_response = EXCLUDED.last_response,
                last_sync_at = EXCLUDED.last_sync_at,
                last_error = EXCLUDED.last_error
            """,
            """
            INSERT INTO channel_listing
            (channel, sku, external_product_id, external_variant_id, publish_state, last_payload_hash, last_payload, last_response, last_sync_at, last_error)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, ?)
            ON CONFLICT(channel, sku) DO UPDATE SET
                external_product_id = excluded.external_product_id,
                external_variant_id = excluded.external_variant_id,
                publish_state = excluded.publish_state,
                last_payload_hash = excluded.last_payload_hash,
                last_payload = excluded.last_payload,
                last_response = excluded.last_response,
                last_sync_at = excluded.last_sync_at,
                last_error = excluded.last_error,
                updated_at = CURRENT_TIMESTAMP
            """,
            (
                channel,
                sku,
                external_product_id,
                external_variant_id,
                publish_state,
                payload_hash,
                payload_json,
                response_json,
                error,
            ),
        )

    def start_sync_job(self, channel: str, mode: str, scope: str, dry_run: bool) -> int:
        if self.backend == "postgres":
            with self._connect() as conn:
                cur = conn.cursor()
                cur.execute(
                    """
                    INSERT INTO sync_jobs(channel, mode, scope, dry_run)
                    VALUES (%s, %s, %s, %s)
                    RETURNING id
                    """,
                    (channel, mode, scope, dry_run),
                )
                row = cur.fetchone()
                conn.commit()
                return int(row["id"])

        with self._connect() as conn:
            cur = conn.cursor()
            cur.execute(
                "INSERT INTO sync_jobs(channel, mode, scope, dry_run) VALUES (?, ?, ?, ?)",
                (channel, mode, scope, int(dry_run)),
            )
            conn.commit()
            return int(cur.lastrowid)

    def finish_sync_job(
        self,
        job_id: int,
        status: str,
        processed_count: int,
        success_count: int,
        failed_count: int,
        error_summary: str,
    ) -> None:
        self.execute(
            """
            UPDATE sync_jobs
            SET ended_at = NOW(),
                status = %s,
                processed_count = %s,
                success_count = %s,
                failed_count = %s,
                error_summary = %s
            WHERE id = %s
            """,
            """
            UPDATE sync_jobs
            SET ended_at = CURRENT_TIMESTAMP,
                status = ?,
                processed_count = ?,
                success_count = ?,
                failed_count = ?,
                error_summary = ?
            WHERE id = ?
            """,
            (status, processed_count, success_count, failed_count, error_summary, job_id),
        )

    def queue_dead_letter(
        self,
        channel: str,
        mode: str,
        sku: str,
        payload: Dict[str, Any],
        error: str,
    ) -> None:
        payload_json = json.dumps(payload, ensure_ascii=False)
        self.execute(
            """
            INSERT INTO dead_letter_queue(channel, mode, sku, payload_json, error)
            VALUES (%s, %s, %s, %s::jsonb, %s)
            """,
            """
            INSERT INTO dead_letter_queue(channel, mode, sku, payload_json, error)
            VALUES (?, ?, ?, ?, ?)
            """,
            (channel, mode, sku, payload_json, error),
        )

    def insert_order_event(
        self,
        channel: str,
        external_order_id: str,
        event_type: str,
        event_ts: dt.datetime,
        payload: Dict[str, Any],
        idempotency_key: str,
    ) -> bool:
        payload_json = json.dumps(payload, ensure_ascii=False)
        try:
            self.execute(
                """
                INSERT INTO order_events
                (channel, external_order_id, event_type, event_ts, payload_json, idempotency_key)
                VALUES (%s, %s, %s, %s, %s::jsonb, %s)
                """,
                """
                INSERT INTO order_events
                (channel, external_order_id, event_type, event_ts, payload_json, idempotency_key)
                VALUES (?, ?, ?, ?, ?, ?)
                """,
                (
                    channel,
                    external_order_id,
                    event_type,
                    event_ts.isoformat(),
                    payload_json,
                    idempotency_key,
                ),
            )
            return True
        except Exception:
            return False

    def apply_order_event(self, event_type: str, payload: Dict[str, Any]) -> None:
        items = payload.get("items") or payload.get("line_items") or []
        delta = 0
        if event_type == "orders":
            delta = 1
        elif event_type in {"cancellations", "returns"}:
            delta = -1
        if delta == 0:
            return

        for item in items:
            sku = (item.get("sku") or "").strip()
            qty = max(_to_int(item.get("qty") or item.get("quantity"), 0), 0)
            if not sku or qty <= 0:
                continue
            if delta > 0:
                self.execute(
                    """
                    UPDATE catalog_inventory
                    SET reserved_qty = GREATEST(reserved_qty + %s, 0)
                    WHERE sku = %s
                    """,
                    """
                    UPDATE catalog_inventory
                    SET reserved_qty = MAX(reserved_qty + ?, 0)
                    WHERE sku = ?
                    """,
                    (qty, sku),
                )
            else:
                self.execute(
                    """
                    UPDATE catalog_inventory
                    SET reserved_qty = GREATEST(reserved_qty - %s, 0)
                    WHERE sku = %s
                    """,
                    """
                    UPDATE catalog_inventory
                    SET reserved_qty = MAX(reserved_qty - ?, 0)
                    WHERE sku = ?
                    """,
                    (qty, sku),
                )
            if self.backend == "sqlite":
                self.execute(
                    "UPDATE catalog_inventory SET sellable_qty = MAX(stock_on_hand - reserved_qty - safety_stock, 0) WHERE sku = ?",
                    "UPDATE catalog_inventory SET sellable_qty = MAX(stock_on_hand - reserved_qty - safety_stock, 0) WHERE sku = ?",
                    (sku,),
                )

    def get_sync_lag_minutes(self, channel: str) -> Optional[float]:
        rows = self.fetch_all(
            "SELECT MAX(last_sync_at) AS last_sync_at FROM channel_listing WHERE channel = %s",
            "SELECT MAX(last_sync_at) AS last_sync_at FROM channel_listing WHERE channel = ?",
            (channel,),
        )
        if not rows or not rows[0].get("last_sync_at"):
            return None
        raw = rows[0]["last_sync_at"]
        if isinstance(raw, str):
            last_sync = dt.datetime.fromisoformat(raw.replace("Z", "+00:00"))
        else:
            last_sync = raw
        if last_sync.tzinfo is None:
            last_sync = last_sync.replace(tzinfo=dt.timezone.utc)
        return (now_utc() - last_sync).total_seconds() / 60.0


def stage_channels(stage: str) -> List[str]:
    if stage == "wave50":
        return ["woo", "zid"]
    if stage == "wave100":
        return ["woo", "zid", "salla"]
    if stage == "wave200":
        return ["woo", "zid", "salla", "shopify"]
    return ["woo", "zid"]


def parse_stage_to_scope(stage_or_scope: str) -> str:
    text = stage_or_scope.strip().lower()
    if text in STAGE_TO_SCOPE:
        return STAGE_TO_SCOPE[text]
    if text in SCOPE_TO_FILE or text == "active":
        return text
    raise HubConfigError(f"Unsupported stage/scope: {stage_or_scope}")


def build_channel_payload(product: Dict[str, Any], category_external_id: Optional[str], price: float) -> Dict[str, Any]:
    return {
        "sku": product.get("sku"),
        "name_ar": product.get("name_ar", ""),
        "name_en": product.get("name_en", ""),
        "desc_ar": product.get("desc_ar", ""),
        "desc_en": product.get("desc_en", ""),
        "brand": product.get("brand", ""),
        "status": product.get("status", "draft"),
        "category_key": product.get("category_key", "uncategorized"),
        "external_category_id": category_external_id or slugify(product.get("category_key", "uncategorized")),
        "weight": _to_float(product.get("weight"), 0.0),
        "barcode": product.get("barcode", ""),
        "images": product.get("images", []),
        "sellable_qty": _to_int(product.get("sellable_qty"), 0),
        "price_sar": float(price),
        "base_cost_sar": _to_float(product.get("base_cost_sar"), 0.0),
        "target_margin_pct": _to_float(product.get("target_margin_pct"), 0.0),
        "external_product_id": product.get("external_product_id"),
        "external_variant_id": product.get("external_variant_id"),
    }
