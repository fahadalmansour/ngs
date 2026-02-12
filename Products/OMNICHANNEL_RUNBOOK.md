# Omnichannel Runbook (Woo + Zid -> Salla -> Shopify)

## 1) Configure environment

1. Copy `.env.omnichannel.example` values into `/Volumes/Fahadmega/NGS_Business/Products/.env`.
2. Set `HUB_DB_URL` to Postgres in production.

## 2) Foundation bootstrap

```bash
python3 /Volumes/Fahadmega/NGS_Business/Products/hub_sync.py --channel woo --mode catalog --scope top50 --dry-run --report-json /Volumes/Fahadmega/NGS_Business/Products/output/wave50_woo_catalog_dry.json
python3 /Volumes/Fahadmega/NGS_Business/Products/hub_sync.py --channel zid --mode catalog --scope top50 --dry-run --report-json /Volumes/Fahadmega/NGS_Business/Products/output/wave50_zid_catalog_dry.json
```

## 3) Contracted worker commands

```bash
python3 /Volumes/Fahadmega/NGS_Business/Products/hub_sync.py --channel woo --mode catalog --scope top50
python3 /Volumes/Fahadmega/NGS_Business/Products/hub_sync.py --channel zid --mode inventory --scope active
python3 /Volumes/Fahadmega/NGS_Business/Products/hub_sync.py --channel salla --mode catalog --scope top50
python3 /Volumes/Fahadmega/NGS_Business/Products/hub_sync.py --channel shopify --mode catalog --scope top50
python3 /Volumes/Fahadmega/NGS_Business/Products/hub_validate.py --stage wave50 --strict
```

## 4) Webhook server

```bash
python3 /Volumes/Fahadmega/NGS_Business/Products/hub_webhooks.py --host 0.0.0.0 --port 8788
```

Supported endpoints:

- `POST /webhooks/{channel}/orders`
- `POST /webhooks/{channel}/cancellations`
- `POST /webhooks/{channel}/returns`

## 5) Wave gates

- Gate A: `wave50` strict pass before Salla activation.
- Gate B: `wave100` strict pass before Shopify activation.
- Gate C: `wave200` strict pass before broad scale.

## 6) n8n workflow

Import `/Volumes/Fahadmega/NGS_Business/Website/n8n-workflows/omnichannel-sync.json`.

Set execution host permissions for these scripts and ensure Python path is available.
