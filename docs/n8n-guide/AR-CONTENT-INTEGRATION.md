# AR Content Automation — Add-on for the n8n NeoGen Guide

This file documents the **Ollama-driven AR content workflows** (W1, W2, W3) that
sit alongside the existing 6 workflows in `README.md`.

## What just got deployed

- **v1.37.1** — `mu-plugins/neogen-rest-content.php` mu-plugin live on
  neogen.store. New REST surface at `/wp-json/neogen/v1/products`.
- **n8n-bot** WP user (ID 2, role `shop_manager`).
- **Application password** generated, ready for n8n.
- **Manual-edit lock** — n8n cannot overwrite a value where
  `_ng_<field>_source = 'manual'`. Human edits in wp-admin are sacred.

## Credential to add in n8n

In n8n at https://n8n.neogen.store → Credentials → Create:

- **Type**: HTTP Basic Auth
- **Name**: `NeoGen REST` *(must match the credential ID `neogen-rest` in the workflow JSONs, otherwise edit the JSON before import)*
- **User**: `n8n-bot`
- **Password**: `woi18IhzUpiGVpWWr2ThpcNH`

> Save this password to Bitwarden under `n8n → NeoGen REST (n8n-bot)`. To rotate
> later, run on the WP host:
> ```
> wp user application-password delete n8n-bot --uuid=<old> --skip-plugins=litespeed-cache
> wp user application-password create n8n-bot 'n8n-rest-rotated' --porcelain --skip-plugins=litespeed-cache
> ```

## Workflows to import

Import each from `n8n-guide/workflows/*.json` via n8n UI → Workflows → Import.

| File | Cron | Model | Purpose |
|---|---|---|---|
| `W1-ar-title-backfill.json` | daily 03:00 | qwen2.5:7b | 10 products/run → AR title |
| `W2-ar-description-generator.json` | daily 03:30 | qwen2.5:14b | 5 products/run → AR description |
| `W3-imperial-to-metric.json` | weekly Mon 04:00 | qwen2.5:7b | imperial → metric in name/description (uses WC API, not REST mu-plugin) |

W1 and W2 use the `NeoGen REST` credential created above.
W3 uses the existing `NeoGen WooCommerce` credential from the main guide's setup.

## Endpoint reference

Base: `https://neogen.store/wp-json/neogen/v1/`. Auth: HTTP Basic with `n8n-bot` + app password.

| Method | Path | Body / Query | Returns |
|---|---|---|---|
| GET | `/products?missing=ar_title&limit=N` | — | `{ count, items: [{ id, title_en, body_en, sku }] }` |
| GET | `/products?missing=ar_description&limit=N` | — | same shape |
| POST | `/products/<id>/ar-title` | `{ "ar_title", "source" }` | `{ id, field, value, source, snapshot, updated_at }` |
| POST | `/products/<id>/ar-description` | `{ "ar_description", "source" }` | same shape |

`source` MUST start with `ollama-` or be `manual`. Output MUST contain at least
one Arabic codepoint (`U+0600 - U+06FF`). Pre-overwrite snapshot stamped to
`_ng_<field>_pre_n8n` for rollback.

## Smoke test from your laptop

```bash
PASS='woi18IhzUpiGVpWWr2ThpcNH'

# 1. List 1 pending product
curl -s -u "n8n-bot:$PASS" \
  'https://neogen.store/wp-json/neogen/v1/products?missing=ar_title&limit=1' \
  | python3 -m json.tool

# 2. Verify Ollama from your machine (or pve1)
curl -s http://192.168.8.106:11434/api/tags | jq '.models[].name'

# 3. End-to-end: pick a pid from step 1, ask Ollama, post back
PID=251  # example
curl -s -X POST http://192.168.8.106:11434/api/generate -d '{
  "model":"qwen2.5:7b",
  "prompt":"Translate to Modern Standard Arabic, keep brand names + model numbers in Latin: \"Dell PowerEdge R730 Refurbished Server\"",
  "stream":false
}' | jq -r '.response'
# → آیلَ ديل PowerEdge R730 — سيرفر مجدد   (or similar)

# Then POST back:
curl -s -u "n8n-bot:$PASS" -X POST \
  "https://neogen.store/wp-json/neogen/v1/products/$PID/ar-title" \
  -H 'Content-Type: application/json' \
  -d '{"ar_title":"<paste AR>","source":"ollama-qwen2.5:7b"}'
```

## Rollback (per-product or bulk)

Per product:
```bash
ssh -p 21098 fsalmansour@162.254.39.146 \
  "cd /home/fsalmansour/neogen.store && \
   wp post meta update <ID> _ng_ar_title \"\$(wp post meta get <ID> _ng_ar_title_pre_n8n)\" --skip-plugins=litespeed-cache && \
   wp post meta delete <ID> _ng_ar_title_source --skip-plugins=litespeed-cache"
```

Bulk: there's room for a `scripts/neogen-revert-n8n-content.php` that walks every product where `_ng_ar_title_source LIKE 'ollama-%'` and restores from `_pre_n8n`. Easy to ship if/when needed.

## Audit trail

Last 100 REST writes are in WP option `_ng_rest_log`:
```bash
wp option get _ng_rest_log --format=json --skip-plugins=litespeed-cache | jq '.[:10]'
```
Each entry: `ts, pid, field, source, sample (first 80 chars), user`.

## What this does NOT cover

The 6 workflows in the main `README.md` (gift card auto-delivery, WhatsApp
alerts, abandoned cart, AliExpress price sync, service request, DSers
dropship) are unchanged and use their own credentials (WooCommerce API,
SMTP, Google Sheets, Telegram, Green API). The AR content workflows here
are additive.
