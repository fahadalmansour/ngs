# Flagged SKUs — Manual Review Log

**Date:** 2026-05-07
**Source audit:** `2026-05-07-market-sync-applied.json` (10 SKUs flagged or rejected by sanity-check thresholds)
**Method:** WebSearch + targeted WebFetch on KSA retailers (Amazon.sa, Microless, IKEA.sa, Noon).

## Outcome

| # | SKU | Product | NK list | Scraped | Verified KSA | Decision |
|---|---|---|---|---|---|---|
| 1 | NT-KVM-ATN-001 | ATEN CS1924M 4-Port DP KVM | 2,399 | 129 | _not surfaced_ | Leave empty — KSA distributor (futures-vision.com) does not show prices in search snippets; Amazon.sa not surfaced |
| 2 | NT-KVM-TES-001 | TESmart HKS0401A1U 4-Port HDMI KVM | 689 | 65 | _not surfaced_ | Leave empty — only US$129.99 Newegg/Walmart found; no KSA listing surfaced |
| 3 | NT-SRV-CBM-001 | Cable Matters 24-Port Cat6 Patch Panel | 239 | 45 | _not surfaced_ | Leave empty — only US listings surface; no KSA retailer with displayed price |
| 4 | NT-UPS-APC-002 | APC Back-UPS Pro BX1500M | 1,839 | 198 | _not surfaced_ | Amazon.sa product page exists (`B06VY6FXMM`) but returned 503 to WebFetch; price not visible. Leave empty |
| 5 | NT-UPS-APC-001 | APC Smart-UPS SMT1500RM2UC | 4,899 | 969 | _not surfaced_ | Saudi distributor mejdaf.com listed but no displayed price; CDW US$773.78 ≈ 2,902 SAR FX-only (not retail-comparable). Leave empty |
| 6 | SH-BLD-IKEA-001 | IKEA FYRTUR Blackout Smart Roller Blind | 1,099 | 96 | _not in KSA IKEA_ | KSA IKEA shows other smart blinds (FRIDANS, FÖNSTERBLAD) but FYRTUR is not currently listed on ikea.com/sa. Product genuinely may be unavailable in KSA — leave empty |
| 7 | SH-LGT-LIFX-001 | LIFX Color A19 1100lm Bulb | 329 | 60 | _not surfaced_ | Amazon.sa 2-pack listing exists (`B08FWH238Y`) but price not in search snippet and direct fetch blocked. Leave empty |
| 8 | NG-ENT-010 | UniFi Switch Pro 24 PoE (USW-Pro-24-PoE) | 4,199 | 369 | **3,064.74** ✓ | **Applied** — Microless, in stock, 8% off SAR 3,323.04. Source: `https://saudi.microless.com/product/unifi-switch-pro-24-poe/` |
| 9 | SH-LWN-MAM-001 | Mammotion LUBA 2 AWD 5000H | 14,999 | 307 | _not surfaced_ | Only US$2,750 retail found; no KSA distributor surfaced. Robot mowers are a thin KSA market. Leave empty |
| 10 | SH-LWN-WRX-001 | Worx Landroid Vision M600 | 6,999 | 267 | _not surfaced_ | Only UK / SA / AU pricing found; no KSA listing surfaced. Leave empty |

## Summary

- **Applied:** 1 SKU (NG-ENT-010 at 3,064.74 SAR — direct verification via Microless KSA)
- **Left empty (deferred to manual sourcing):** 9 SKUs
- **Net effect on price-floor audit:** PASS 97 → 98 (+1)

## Why so few hit

KSA marketplace retailers (Amazon.sa, Noon, Microless, Jarir, eXtra) frequently:
1. Don't expose prices in search-result snippets (snippets show product titles only)
2. Block direct WebFetch with 503 / anti-bot pages
3. Show prices only after JS hydration — require Playwright with a real product-page selector

The first scrape pass hit Amazon.sa search (`.a-price-whole`) with Playwright successfully because the search UI does emit price elements server-side. **Direct product pages on Amazon.sa block plain WebFetch.** Microless and IKEA.sa work via WebFetch (no anti-bot), but only one of these tail-end SKUs surfaces on Microless with a live price.

## Recommended next step for the 9 unverified

Either:
1. **Add Microless / Jarir / eXtra search to the Playwright scraper** as fallback supplier sites — they're more permissive than Amazon.sa product pages.
2. **Hand-source manually** — open `output/spreadsheet/supplier_price_work_queue.csv`, sort by these 9 SKUs, click the supplier search URLs, paste verified prices.
3. **Accept they may not be valid for KSA launch** — robot mowers, IKEA-specific smart blinds, and US-only KVMs may be products that genuinely don't have KSA market depth. Drop them from the catalog rather than carry a NEEDS_REFERENCE row indefinitely.
