BEGIN;

CREATE TABLE IF NOT EXISTS catalog_products (
    sku TEXT PRIMARY KEY,
    name_ar TEXT NOT NULL DEFAULT '',
    name_en TEXT NOT NULL,
    desc_ar TEXT NOT NULL DEFAULT '',
    desc_en TEXT NOT NULL DEFAULT '',
    brand TEXT NOT NULL DEFAULT '',
    status TEXT NOT NULL DEFAULT 'draft',
    category_key TEXT NOT NULL DEFAULT 'uncategorized',
    weight NUMERIC(12,3) NOT NULL DEFAULT 0,
    barcode TEXT,
    images_json JSONB NOT NULL DEFAULT '[]'::jsonb,
    source_scope TEXT NOT NULL DEFAULT 'top50',
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS catalog_inventory (
    sku TEXT PRIMARY KEY REFERENCES catalog_products(sku) ON DELETE CASCADE,
    stock_on_hand INTEGER NOT NULL DEFAULT 0,
    reserved_qty INTEGER NOT NULL DEFAULT 0,
    safety_stock INTEGER NOT NULL DEFAULT 0,
    sellable_qty INTEGER GENERATED ALWAYS AS (GREATEST(stock_on_hand - reserved_qty - safety_stock, 0)) STORED,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS catalog_pricing (
    sku TEXT PRIMARY KEY REFERENCES catalog_products(sku) ON DELETE CASCADE,
    base_cost_sar NUMERIC(12,2) NOT NULL DEFAULT 0,
    target_margin_pct NUMERIC(8,2) NOT NULL DEFAULT 25,
    vat_included_bool BOOLEAN NOT NULL DEFAULT TRUE,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS channel_listing (
    channel TEXT NOT NULL,
    sku TEXT NOT NULL REFERENCES catalog_products(sku) ON DELETE CASCADE,
    external_product_id TEXT,
    external_variant_id TEXT,
    publish_state TEXT NOT NULL DEFAULT 'draft',
    last_payload_hash TEXT,
    last_payload JSONB,
    last_response JSONB,
    last_sync_at TIMESTAMPTZ,
    last_error TEXT,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    PRIMARY KEY (channel, sku)
);

CREATE TABLE IF NOT EXISTS channel_price_rules (
    channel TEXT PRIMARY KEY,
    fee_pct NUMERIC(8,4) NOT NULL DEFAULT 0,
    payment_pct NUMERIC(8,4) NOT NULL DEFAULT 0,
    ops_buffer_sar NUMERIC(12,2) NOT NULL DEFAULT 0,
    round_rule TEXT NOT NULL DEFAULT 'nearest_9',
    active BOOLEAN NOT NULL DEFAULT TRUE,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS channel_category_map (
    channel TEXT NOT NULL,
    category_key TEXT NOT NULL,
    external_category_id TEXT NOT NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    PRIMARY KEY (channel, category_key)
);

CREATE TABLE IF NOT EXISTS order_events (
    id BIGSERIAL PRIMARY KEY,
    channel TEXT NOT NULL,
    external_order_id TEXT NOT NULL,
    event_type TEXT NOT NULL,
    event_ts TIMESTAMPTZ NOT NULL,
    payload_json JSONB NOT NULL,
    idempotency_key TEXT NOT NULL UNIQUE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS sync_jobs (
    id BIGSERIAL PRIMARY KEY,
    channel TEXT NOT NULL,
    mode TEXT NOT NULL,
    scope TEXT NOT NULL,
    started_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    ended_at TIMESTAMPTZ,
    status TEXT NOT NULL DEFAULT 'running',
    dry_run BOOLEAN NOT NULL DEFAULT FALSE,
    processed_count INTEGER NOT NULL DEFAULT 0,
    success_count INTEGER NOT NULL DEFAULT 0,
    failed_count INTEGER NOT NULL DEFAULT 0,
    error_summary TEXT
);

CREATE TABLE IF NOT EXISTS dead_letter_queue (
    id BIGSERIAL PRIMARY KEY,
    channel TEXT NOT NULL,
    mode TEXT NOT NULL,
    sku TEXT,
    payload_json JSONB,
    error TEXT NOT NULL,
    retries INTEGER NOT NULL DEFAULT 0,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    resolved_at TIMESTAMPTZ
);

CREATE INDEX IF NOT EXISTS idx_catalog_products_scope ON catalog_products (source_scope);
CREATE INDEX IF NOT EXISTS idx_catalog_products_category ON catalog_products (category_key);
CREATE INDEX IF NOT EXISTS idx_channel_listing_channel_state ON channel_listing (channel, publish_state);
CREATE INDEX IF NOT EXISTS idx_channel_listing_last_sync ON channel_listing (last_sync_at);
CREATE INDEX IF NOT EXISTS idx_order_events_lookup ON order_events (channel, external_order_id, event_type);
CREATE INDEX IF NOT EXISTS idx_sync_jobs_started_at ON sync_jobs (started_at);
CREATE INDEX IF NOT EXISTS idx_dead_letter_queue_open ON dead_letter_queue (channel, resolved_at);

CREATE OR REPLACE FUNCTION touch_updated_at()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

DROP TRIGGER IF EXISTS trg_catalog_products_updated_at ON catalog_products;
CREATE TRIGGER trg_catalog_products_updated_at
BEFORE UPDATE ON catalog_products
FOR EACH ROW EXECUTE FUNCTION touch_updated_at();

DROP TRIGGER IF EXISTS trg_catalog_inventory_updated_at ON catalog_inventory;
CREATE TRIGGER trg_catalog_inventory_updated_at
BEFORE UPDATE ON catalog_inventory
FOR EACH ROW EXECUTE FUNCTION touch_updated_at();

DROP TRIGGER IF EXISTS trg_catalog_pricing_updated_at ON catalog_pricing;
CREATE TRIGGER trg_catalog_pricing_updated_at
BEFORE UPDATE ON catalog_pricing
FOR EACH ROW EXECUTE FUNCTION touch_updated_at();

DROP TRIGGER IF EXISTS trg_channel_listing_updated_at ON channel_listing;
CREATE TRIGGER trg_channel_listing_updated_at
BEFORE UPDATE ON channel_listing
FOR EACH ROW EXECUTE FUNCTION touch_updated_at();

DROP TRIGGER IF EXISTS trg_channel_price_rules_updated_at ON channel_price_rules;
CREATE TRIGGER trg_channel_price_rules_updated_at
BEFORE UPDATE ON channel_price_rules
FOR EACH ROW EXECUTE FUNCTION touch_updated_at();

DROP TRIGGER IF EXISTS trg_channel_category_map_updated_at ON channel_category_map;
CREATE TRIGGER trg_channel_category_map_updated_at
BEFORE UPDATE ON channel_category_map
FOR EACH ROW EXECUTE FUNCTION touch_updated_at();

COMMIT;
