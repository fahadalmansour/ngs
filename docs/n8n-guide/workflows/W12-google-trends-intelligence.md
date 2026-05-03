# W12 — Google Trends Product Intelligence

**Trigger:** Weekly schedule (Sunday 06:00 Riyadh)
**What it does:** Queries Google Trends for all 211 published products (Saudi Arabia, last 3 months), writes results to Google Sheet, sends Telegram summary with rising/declining demand signals.

**Architecture:**
- `neogen-trends` Docker container (port 5050) wraps the `pytrends` Python library
- n8n calls it via HTTP — no external API key needed
- Google Trends is scraped from Google's unofficial widget API (geo=SA)

---

## Infrastructure

### neogen-trends service (already deployed on CT 119)
- Container: `neogen-trends` on `n8n_n8nnet` network
- Internal URL from n8n: `http://neogen-trends:5050`
- Health check: `GET http://neogen-trends:5050/health`
- Source: `/opt/trends-service/trends_service.py`

### Endpoints

**POST /trends** — Get trend scores for up to 5 keywords
```json
Request:  { "keywords": ["Ubiquiti UniFi", "Synology NAS"], "geo": "SA", "timeframe": "today 3-m" }
Response: { "results": { "Ubiquiti UniFi": { "avg": 0.9, "peak": 65, "latest": 0, "trend": "down" } } }
```

**GET /trending?geo=SA** — Get Saudi Arabia's top 20 trending searches right now

---

## Workflow ID: A95BR8Jphz9z3ECT

### Flow
```
Weekly Sunday 06:00
    → Fetch Products Page 1 ─┐
    → Fetch Products Page 2 ─┤
                              → Merge Pages
                              → Build Batches of 5 (43 batches × 5 products)
                              → [Loop] Query Google Trends (neogen-trends:5050)
                              → Flatten Results
                              → Write to Google Sheet  ─┐
                              → Build Telegram Summary ──→ Send Telegram
```

---

## Google Sheet Setup

1. Create a new Google Sheet: **NeoGen Product Trends**
2. Add sheet tab named `Trends`
3. Row 1 headers (exact):
   ```
   sku | name | category | price_sar | store_url | brand | brand_url | trend_keyword | trend_avg_3m | trend_peak_3m | trend_latest | trend_direction | last_checked
   ```
4. Copy the Sheet URL and paste it into the **Write to Google Sheet** node → Document ID field
5. The workflow runs weekly and **replaces** (not appends) existing rows — change `useAppend: false` → `true` if you want history

---

## Master Product CSV

Pre-generated at: `/home/node/.n8n/docs/product-trends-master.csv` (inside CT 119)
Also at: `output/sync/product-trends-master.csv` in this repo

Columns: `sku, name, category, price_sar, status, store_url, brand, brand_url, trend_keyword`

---

## Reading the Results

| trend_direction | Meaning |
|---|---|
| `up` | Latest score > 3-month average — demand growing |
| `down` | Latest score < 3-month average — demand softening |
| `no_data` | Google returned no data (very niche product, or keyword too specific) |

**trend_avg_3m**: 0–100 score, relative to all searches in Saudi Arabia over 3 months. Score of 0 means the product is searched less than 1% of peak volume — not necessarily zero.

---

## Rate Limiting

Google Trends rate-limits aggressive scrapers. The `neogen-trends` service has no built-in delay between requests. If you get 429 errors:

1. Open `/opt/trends-service/trends_service.py` on CT 119
2. Add `time.sleep(2)` before `pytrends.build_payload(...)` in the `/trends` endpoint
3. Rebuild: `docker build -t neogen-trends /opt/trends-service/ && docker restart neogen-trends`

At 2s delay × 43 batches = ~86 seconds total runtime per weekly scan. Fine for a background job.

---

## Realtime Saudi Trending (Bonus)

Test in n8n with a manual HTTP Request node:
```
GET http://neogen-trends:5050/trending?geo=SA
```
Returns top 20 trending searches in Saudi Arabia right now — useful for spotting product opportunities.
