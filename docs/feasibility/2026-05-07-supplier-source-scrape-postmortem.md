# Supplier-Source Scrape — Postmortem (2026-05-07)

## Goal

Capture wholesale / source-supplier prices for the 116 in-scope SKUs (Smart Home + Security + Networking + Homelab) so the price-pipeline could compute true gross margin (`Sale Price − Landed Cost`). The original `scripts/live-market-sync-inscope.js` used B&H Photo for source pricing on `NT-*` / `NG-*` prefixes and AliExpress for `SH-*`.

## Result

**B&H Photo: 0/119 hits** in the original run. Probe revealed B&H serves a JS-rendered page where the price elements never populate the DOM under headless Playwright. No `data-selenium="*"`, no `[class*="price"]`, no `$` in the visible body text. They're bot-blocking us at render-time.

**AliExpress: 0/119 hits** in the pivoted run. After my hand-test probe successfully returned 8 SAR prices for "Ubiquiti UniFi U6 Pro" via the Arabic mirror, I built a script to do the same in batch for 119 SKUs. After ~5 sequential requests AliExpress flagged the fingerprint and started serving its bot-detection challenge page (`/_____tmd_____/punish?x5step=1`) with empty body text. Three follow-up debug requests confirmed every URL pattern (long form, slugified, parens-stripped) lands on the same punish page. Fingerprint is now globally blocked from this IP/UA combo.

## What I tried and why none worked

| Attempt | Outcome |
|---|---|
| `[data-selenium="pricing-price"]` selector on B&H | 0 elements in DOM. Selector deprecated or rendered after async hydration we never see. |
| `[class*="price"]` on B&H | 0 elements. Body text contains 0 `$` signs. JS payload either never runs or detects headless. |
| `networkidle` + 5s wait on B&H | Same. Page shell loads but price data never injected. |
| AliExpress `aliexpress.com/wholesale?SearchText=…` | First few requests OK, then 100% redirect to `/_____tmd_____/punish` |
| AliExpress `aliexpress.com/w/wholesale-SLUG.html` (clean slug) | Same punish-page redirect |
| AliExpress `aliexpress.us/w/...` (US mirror) | Same |
| Different `userAgent` + `locale: 'en-SA'` | Same — fingerprint is sticky |

The "0 results across the board" pattern across two unrelated marketplaces (B&H + AliExpress) is consistent with **commercial-grade anti-bot defenses, not a selector bug**.

## Why my one-shot probe earlier worked

The earlier hand-probe (`scripts/.ae-probe.js`, since deleted) returned 8 SAR prices because it ran a single context with a fresh fingerprint and only 3 sequential requests. AliExpress's risk-scoring model didn't escalate. Once the batch script ran 5+ requests the score crossed their threshold and they served the punish page on every subsequent request — including, as of this writing, fresh debug runs from the same machine.

This is the standard "honeypot" / "rate-limit-by-fingerprint" model major marketplaces use. There is no robust selector or wait-strategy fix.

## What this does NOT block

The Smart Home launch is **not** blocked by this. As of 2026-05-07 17:59 UTC, after the Amazon.sa scrape:

- **114 of 116 in-scope SKUs are price-floor-defensible** (98 PASS + 16 auto-FIXED)
- Sale prices clear the `Real price floor SAR` from the Amazon SA reference — i.e., NeoGen is selling at or above the lowest Saudi-market reference for each of these SKUs
- This is **retail-price-defensible**: regulators, payment-gateway underwriters, and ad platforms can verify pricing is consistent with the local market.

What we do NOT have is **margin-defensible** pricing — i.e., a verified wholesale FOB cost so the pipeline can prove `Sale Price - Landed Cost > 0`. Without it, the 15% margin in `scripts/price-governor.js:11` is aspirational, not enforced.

## Real options for getting wholesale costs

| Option | Cost | Lift | Quality |
|---|---|---|---|
| **Paid scraping API** (ScraperAPI, ScrapingBee, Bright Data) | ~$30-100/mo for 5-10k requests | Low — drop-in replacement for `page.goto`. They handle anti-bot, captchas, residential proxies. | High — designed for exactly this |
| **Headed browser on real desktop** | $0 | Med — open Chrome, navigate manually for each SKU, paste prices into work queue | Highest — but very slow (~30 sec/SKU = ~50 min for 116) |
| **Hire a VA via Upwork/Fiverr** | ~$5-20/hr | Med — share the work queue CSV, brief them on filtering for plausible-range prices | High |
| **Direct supplier outreach** | $0 | High — email Ubiquiti / TP-Link / Home Assistant distributors in KSA for wholesale price lists | Highest, but slowest (days-weeks) |
| **Accept retail-defensible state** | $0 | Zero | Sufficient for soft launch; revisit when a SKU starts generating real volume |

## Recommendation

For now, **accept the retail-defensible state and ship.** The 114-SKU launch set has Amazon SA reference prices for every product, so:
- NeoGen is not undercutting itself accidentally
- Customers can see comparable Saudi-market pricing
- Payment processors and ad platforms have evidence-backed listings

When monthly revenue clears the ZATCA Wave-24 threshold (375K SAR/yr ≈ 31K SAR/mo), invest in either a paid scraping API or direct supplier-distributor outreach for the top-velocity SKUs to lock in real margin data. Until then, hand-fill supplier prices for the 5-10 SKUs that drive the most revenue.

## Artifacts

- `scripts/live-market-sync-aliexpress.js` — kept for future use (works against fresh fingerprints; consider a paid proxy)
- `scripts/live-market-sync-inscope.js` — original Amazon.sa + B&H scraper (Amazon.sa works, B&H doesn't)
- `scripts/live-market-sync-ksa-retailers.js` — Microless/Jarir/eXtra fallback (selector tweaks needed; Jarir `.price` works partially)
- No new scrape data was applied to master xlsx — pre-AliExpress backup retained.
