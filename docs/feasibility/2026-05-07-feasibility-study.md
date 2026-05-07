# NeoGen Store — Feasibility Study & Full Audit

**Date:** 2026-05-07
**Owner:** Fahad Almansour (CR #7053130576)
**Live site:** https://neogen.store (blazr.net VPS, 31.220.42.110, LiteSpeed)
**Notion mirror:** https://www.notion.so/35995fc54e03814a91b9c612decf8846
**Companion files:**
- `2026-05-07-financial-model.csv` — every numeric input tagged
- `2026-05-07-tech-audit-evidence.md` — raw evidence appendix
- `2026-05-07-dropship-shortlist.csv` — the 9 dropship-fit SKUs (Smart Home + Security + Networking + Homelab scope)
- `2026-05-07-smart-home-fill-backlog.csv` — 119 in-scope SKUs tiered A/B/C/D by what's missing
- `2026-05-07-removed-skus.json` — audit log of the 122 SKUs removed from master on 2026-05-07
- Migrated to `~/sites/novakeys/data/migrated-from-neogen-20260507-072457/` — 83 Gift Cards & Software Keys SKUs + supporting code/assets

> **Catalog-revision note (2026-05-07).** Master reduced from **288 to 166 products** by deleting Gift Cards & Software Keys (83 → migrated to `~/sites/novakeys/`) and Gaming (39 → deleted). Backup at `Neogen_Master_Catalog_Blueprint.{csv,xlsx}.backup-20260507-065041`. Audit log at `2026-05-07-removed-skus.json`.

> **Market-sync note (2026-05-07).** Playwright scrape of Amazon.sa via `scripts/live-market-sync-inscope.js` filled **93 new Amazon SA Ref Prices** into the master xlsx (8 flagged for manual review, 2 rejected as wrong listings — see `2026-05-07-market-sync-applied.json`). After `npm run woo:generate && sourcing:generate && price:guard`, the in-scope PASS count jumped **14 → 98 (+ 16 auto-FIXED)**. B&H Photo source-price selector failed across all 119 SKUs (bot-blocked at render). AliExpress pivot also failed: succeeded on a one-shot probe, then their fingerprint scoring escalated and served `/_____tmd_____/punish` on every subsequent request. Wholesale cost data therefore remains 0% populated — listings are **retail-price-defensible** (Amazon-SA-anchored) but not **margin-defensible** (no FOB cost). Full postmortem: `2026-05-07-supplier-source-scrape-postmortem.md`. Pre-scrape master backup at `Neogen_Master_Catalog_Blueprint.xlsx.backup-20260507-075900`. Three further SKUs dropped (Mammotion LUBA 2, Worx Landroid M600, IKEA FYRTUR — see `2026-05-07-removed-skus.json`); master is now **163 products** total, **116 in scope**.

> **Truth-model contract.** Every figure carries one of: `confirmed_from_repo` (with `file:line`), `confirmed_from_live_site` (with curl evidence in the appendix), `confirmed_from_web` (with URL + 2026-05-07 access date), `default_office_assumption` (labelled "Verify"), or `unknown`. Mixing tags is forbidden. Anything that resists tagging is parked in **§9 Needs verification** rather than guessed.

---

## 1. Executive summary

NeoGen Store is a KSA-registered (CR #7053130576) WooCommerce store on a single LiteSpeed VPS. The technology stack is operational and the security posture is reasonable (HSTS, CSP-report-only, modern TLS), but the **commercial readiness gap is the biggest constraint to "go and grow":** **after the 2026-05-07 category prune the master catalog holds 166 physical-only products**, of which **20 pass the internal price-floor audit** and all 20 also have an Amazon SA reference price. The remaining 146 fail the audit because supplier prices, FOB cost, and landed cost are not yet filled in (`confirmed_from_repo`, `output/spreadsheet/price_floor_audit.csv` — note this audit is now stale until re-run). Recommendation: **launch with the 20-product physical Tier-2 set, narrow paid marketing to the 11-SKU dropship-fit subset, and treat supplier-price coverage as the highest-leverage backlog over the next 30 days.**

KSA macro is favourable — 99% internet penetration (GASTAT 2025), 33.9 M users, e-commerce on Mada cards alone hit **SAR 23.27 bn in April 2025 (+57% YoY)**, and electronic payments are now **85% of all retail payments** (SAMA, 2025). These are tailwinds, not entitlements. The store's largest near-term risks are **operational**, not market-driven: missing legal pages (Returns / T&Cs return 404), a broken `/ar/` locale, slow TTFB (~3 s vs ≤ 0.5 s target), plugin overlap (two cookie plugins, two Stripe-family gateways, no Tamara despite documentation listing it), and a 100%-empty cost-data column set in the master catalog that prevents the pricing pipeline from producing accurate margin output.

ZATCA Wave 24 (e-invoicing, threshold **SAR 375K** annual taxable revenue) takes effect **2026-06-30** — about 8 weeks from the date of this study. Below 375K rolling-12-month revenue, NeoGen has runway. Above it, mandatory Phase-2 integration kicks in and the work in `docs/n8n-guide/W9-zatca-invoice-notify.md` must reach production.

---

## 2. Recommended starting product count (post-deletion, 2026-05-07)

| Tier | Count | Definition | Risk |
|---|---|---|---|
| **Tier 1 / Tier 2 — Confident launch** | **20** | All `Status=PASS` in `price_floor_audit.csv`, all also have an Amazon SA reference price | Low |
| Tier 3 — Full activation | 166 | Entire post-prune master catalog | High — 146 still fail the price-floor audit |

After deleting Gift Cards & Software Keys and Gaming, **Tier 1 and Tier 2 collapse to the same 20 SKUs** — every PASS product happens to also have an Amazon SA reference. That is structurally a stronger position than before the prune (no "evidence-light" subset to caveat).

Tier-2 composition (`confirmed_from_repo`, post-prune): **Homelab 6, Networking 5, 3D Printing & CNC 3, Smart Home & IoT 2, Enthusiasts & Gamers 2, Security & Surveillance 1, Accessories & Lifestyle 1**.

## 2.1 Smart-Home launch focus (recommended)

User decision: **start with smart home products.** Scope = **Smart Home + Security + Networking + Homelab** ("connected home / prosumer infra"). One buyer persona; UniFi gear, pfSense, Home Assistant Green already sourced and Amazon-priced.

| Metric | Pre-scrape | **Post-scrape (2026-05-07 17:59 UTC)** |
|---|---|---|
| In-scope SKUs in master | 119 | **119** |
| Tier A — Launch now (`PASS`) | 14 | **91** |
| Tier A (auto-`FIXED` by price-guard) | 0 | **16** |
| Tier D — Both supplier price + Amazon ref missing | 105 | **12** |
| Dropship-fit subset (PASS ∩ ≤2 kg ∩ Standard class ∩ supplier-priced) | 9 | **67** |

Tier A breakdown by category (post-scrape): Smart Home & IoT **48**, Networking **24**, Homelab **14**, Security & Surveillance **5** = 91 PASS. Plus 16 auto-FIXED across the four categories (review `output/spreadsheet/woocommerce_ready_import_price_floor_checked.csv` before push).

**Caveat on the 67 dropship-fit count:** the supplier matrix now treats Amazon.sa retail price as the "supplier" price for most of these (per `scripts/generate_supplier_sourcing.py:166`). That is a *retail-price reference*, not a *wholesale source cost*. A genuine dropship workflow still needs an actual wholesale supplier (AliExpress, Alibaba, B&H, or a KSA distributor) per SKU before margin can be guaranteed.

Full prioritised list at `docs/feasibility/2026-05-07-smart-home-fill-backlog.csv` (119 rows, sorted by tier then category). The 9 dropship-fit subset:

| # | SKU | Category | Weight | Price (SAR) | Product |
|---|---|---|---|---|---|
| 1 | NT-CBL-FSC-001 | Networking | 0.15 kg | 80 | 10G SFP+ DAC Twinax Cable (1m) |
| 2 | NG-NET-012 | Networking | 0.60 kg | 680 | Ubiquiti UniFi Cloud Gateway Ultra |
| 3 | SH-HUB-HASS-001 | Smart Home & IoT | 0.30 kg | 890 | Home Assistant Green |
| 4 | NT-WAP-UBQ-001 | Networking | 0.50 kg | 910 | Ubiquiti UniFi U6 Pro |
| 5 | NG-ENT-004 | Homelab | 0.50 kg | 1,180 | UniFi 6 Long-Range (U6-LR) |
| 6 | NT-MPC-DEL-001 | Homelab | 1.50 kg | 1,610 | Dell OptiPlex 7070 Micro (Refurb) |
| 7 | NG-SH-002 | Smart Home & IoT | 0.80 kg | 2,520 | UniFi Protect G4 Pro Camera |
| 8 | NT-FWL-NGT-002 | Homelab | 1.20 kg | 2,740 | Netgate 2100 MAX pfSense+ Gateway |
| 9 | NG-SEC-004 | Security & Surveillance | 0.80 kg | 3,140 | UniFi Protect G4 Doorbell Pro |

**Recommendation:**
- **Launch the 14 Tier-A SKUs** as the live catalogue.
- **Lead paid acquisition with the 9-SKU dropship subset** (no warehousing risk).
- **Sub-1,000 SAR cluster (4 SKUs: NT-CBL-FSC-001, NG-NET-012, SH-HUB-HASS-001, NT-WAP-UBQ-001)** = highest-velocity impulse-purchase tier.
- **30-day data-fill sprint** for the 105 Tier-D SKUs: walk down `output/spreadsheet/supplier_price_work_queue.csv` (730 rows) — 50% supplier-price coverage by end-of-month is the goal. Each unblocked SKU lifts straight to Tier A on the next `npm run price:guard`.

---

## 3. Market & demand (KSA)

All figures `confirmed_from_web` with citation + access date 2026-05-07.

| Indicator | Value | Source |
|---|---|---|
| Internet penetration (15-74 yrs) | **99.0%** | GASTAT, ICT Access & Usage 2025 — `https://www.stats.gov.sa/documents/20117/2435267/ICT+Access+and+Usage+2025-EN.pdf` |
| Internet users in KSA, 2025 | **33.9 million** | DataReportal Digital 2025 — `https://datareportal.com/reports/digital-2025-saudi-arabia` |
| E-payments share of retail, 2025 | **85%** | SAMA via SPA — `https://www.spa.gov.sa/en/N2558262` |
| Mada e-commerce volume, Apr 2025 | **SAR 23.27 bn** (USD 6.2 bn) | SAMA via Arab News — `https://www.arabnews.com/node/2604150/business-economy` |
| Mada e-commerce growth YoY | **+57%** | same |
| Mada online txn count, Apr 2025 | **132 million** (+40.75% YoY) | same |
| In-store NFC card share | **94%** | SAMA |
| KSA e-commerce market size, 2025 | **USD 15.2 bn (Statista)** to **USD 28 bn (Mordor)** — wide analyst range | `https://www.statista.com/outlook/emo/ecommerce/saudi-arabia` ; `https://www.mordorintelligence.com/industry-reports/saudi-arabia-ecommerce-market` |

**Read.** The infrastructure tailwind for a KSA-registered, Mada-accepting WooCommerce store is real and current. The competitive ceiling is set by Amazon.sa, Noon, Jarir, and eXtra (all priced and indexed by Mada/STC Pay/Tabby). NeoGen's master catalog has Amazon SA reference prices on **only 39 of 288 products** (`confirmed_from_repo`) — i.e., for 86% of the catalog there is no current evidence of where Amazon prices the same SKU. That is the single most actionable market-research gap.

Internal market context already in repo (cite, do not re-derive): `docs/marketing/Market Trends 2025.md`, `docs/marketing/Competitor analysis.md`. Recommend reading these alongside this section.

---

## 4. Financial viability

### 4.1 Pricing model constants (all `confirmed_from_repo`)

| Constant | Value | Source |
|---|---|---|
| SAR / USD peg | 3.75 | `scripts/live-market-sync.js:10` |
| Target margin | 15% over landed cost | `scripts/price-governor.js:11` |
| Market variance threshold | 5% | `scripts/price-governor.js:12` |
| International shipping | 60 SAR / kg | `scripts/live-market-sync.js:11` |
| Customs + VAT multiplier | 1.2075× (5% + 15%) | `scripts/live-market-sync.js:12` |
| Local delivery | 30 SAR / order | `scripts/price_sync.py:35` |

### 4.2 Worked unit economics

Physical, 1 kg, $50 FOB sample (`default_office_assumption` for the FOB number — Cost Price FOB column is 100% empty in the master CSV):

```
$50 FOB × 3.75            = 187.50 SAR
+ shipping 1 kg × 60      =  60.00 SAR
                           ─────────
subtotal                  = 247.50 SAR
× 1.2075 (customs + VAT)  = 298.86 SAR
+ local delivery          =  30.00 SAR
                           ─────────
landed cost               = 328.86 SAR
× 1.15 (target margin)    = 378.19 SAR  ← sale price
gross margin / unit       =  49.33 SAR (15%)
```

Digital sample (no shipping, no customs duty):

```
$80 wholesale × 3.75      = 300.00 SAR
× 1.15 (VAT 15%)          = 345.00 SAR  landed
× 1.15 (target margin)    = 396.75 SAR  sale
gross margin / unit       =  51.75 SAR (15%)
```

Both worked examples appear in `2026-05-07-financial-model.csv` § 2-3 with each input tagged.

### 4.3 Break-even illustration

Using a placeholder fixed-cost base of **2,000 SAR/mo** (`default_office_assumption` — replace with confirmed VPS + domain + email + tools), a blended payment fee of 4% (mid of cited Tabby BNPL range 2.79%-5.99%, `https://paymentproviders.io/compare/tamara-vs-tabby`), and 15% gross margin:

| AOV | Net margin / order | Break-even orders / mo |
|---|---|---|
| 200 SAR | ~18 SAR | ~111 |
| 700 SAR | ~77 SAR | ~26 |
| 1,500 SAR | ~165 SAR | ~12 |

These are illustrative — not a forecast. The math model is in the financial-model CSV and re-runs cleanly when you replace the `default_office_assumption` rows with confirmed numbers.

### 4.4 Sensitivity

A 10% adverse move on SAR/USD (would require a peg revision — historically very unlikely but cited as a `default_office_assumption`) compresses the worked-physical sale price from 378 SAR to ~410 SAR; if held flat to compete, margin goes negative. A 2pp increase in payment fees (BNPL high end 5.99%) reduces AOV-700 net margin from ~77 to ~63 SAR/order — break-even rises from 26 to 32 orders/mo at the same fixed-cost base.

### 4.5 Revenue ceiling for ZATCA Wave-24 deferral

Wave 24 catches taxable turnover **> SAR 375,000** in any of CY 2022 / 2023 / 2024 (`confirmed_from_web`, EY tax alert + ZATCA news 1426). Rolling-12-month revenue ≥ ~31,250 SAR/mo qualifies. At AOV 700 SAR and ~26 orders/mo (the break-even illustration), monthly revenue is ~18,200 SAR — well below the threshold. Above ~45 orders/mo at AOV 700 you are within Wave-24 scope and Phase-2 integration must be live by **2026-06-30**.

---

## 5. Operations & supply chain

### 5.1 Supplier readiness (all `confirmed_from_repo`)

| Metric | Value | Source |
|---|---|---|
| Supplier sourcing matrix rows | 1,440 | `output/spreadsheet/supplier_sourcing_matrix.csv` |
| Distinct SKUs in matrix | 288 | same |
| Rows with supplier price filled | 39 | same |
| Coverage % | **2.7%** | derived |
| Work queue (rows requiring attention) | 985 | `output/spreadsheet/supplier_price_work_queue.csv` |
| Work queue distinct SKUs | 197 | same |
| Risk distribution | 865 Medium, 120 High | same |
| Master CSV cost columns populated | 0 / 288 | `Cost Price FOB`, `Landed Cost`, `MSRP`, `Cost Currency`, `Brand`, `Country of Origin` all empty |

**Implication.** The pricing pipeline is well-engineered (`scripts/price-governor.js`, `scripts/live-market-sync.js`, `scripts/price_sync.py`, `scripts/price-validator.js`) but is being fed an empty cost column. The 15% target margin in `price-governor.js:11` cannot be enforced if landed cost = `null`. Until `Cost Price FOB` (or `Landed Cost (SAR)`) is populated, every published sale price relies on the price-floor audit's `Real price floor SAR` heuristic, which is precisely why 197 SKUs sit in `NO_REAL_PRICE_REFERENCE` / `NEEDS_REFERENCE`.

### 5.2 Inventory model

`unknown` from repo. The presence of `INTL_SHIPPING_KG=60` and `live-market-sync.js` scraping AliExpress / Alibaba / Amazon SA suggests a dropship / on-demand sourcing model rather than warehoused inventory, but no purchase orders, stock files, 3PL contracts, or warehouse addresses were found in the repo. Parked in §9.

### 5.3 Returns & consumer protection

KSA Consumer Protection Law gives consumers a typical 14-day right of return for non-personalised goods. Live-site audit (`§G in evidence appendix`) shows **`/return-policy/`, `/refund_returns/`, and `/terms-and-conditions/` all return HTTP 404**. Only `/privacy-policy/` is live. This is a launch-blocker for paid acquisition — payment gateways and ad platforms typically refuse merchants without published terms and a returns policy.

### 5.4 ZATCA e-invoicing readiness

Workflow doc exists at `docs/n8n-guide/W9-zatca-invoice-notify.md` describing Phase-2 invoice generation with QR code via n8n. Production status `unknown` — no fingerprint of a Fatoora integration in the live site response. VAT registration number `3145127947` appears in `W9-zatca-invoice-notify.md:112`. If rolling-12-month revenue stays under 375K SAR, the deadline is `unknown` (next wave). If above, **2026-06-30**.

### 5.5 Migration risk (neogen → novakeys)

`migration/README.md` confirms Phase 1 done (289 products exported, 112 digital identified), Phases 2-5 pending. Target store `https://www.novakeys.store` is hosted on Namecheap cPanel (`162.254.39.146`, separate from the neogen VPS). No imports have started. This is an opportunity, not a threat — the migration scope intersects directly with the digital / gift-card subset that is also Tier-1/2 of the launch plan.

---

## 6. Tech / site-health audit

Detailed evidence in `2026-05-07-tech-audit-evidence.md`. Headlines:

| Finding | Severity | Source |
|---|---|---|
| `/return-policy/`, `/refund_returns/`, `/terms-and-conditions/` → 404 | **High** | live probe § A |
| `/ar/` redirects (301) to a single product page; `<html lang="en-US">` only; no `hreflang` | **High** (RTL bilingual claim is a launch promise) | live probe § A, § D |
| TTFB ~3 s across all paths; **no `x-litespeed-cache` header** in any response | High (UX + SEO) | live probe § B, § G |
| 0 JSON-LD product schema blocks on sample product page | High (SEO rich results unavailable) | evidence § D |
| Two cookie plugins active (`cookieadmin` + `cookieadmin-pro`) | Medium | evidence § C |
| Two Stripe-family gateways active (`woocommerce-gateway-stripe` + `woocommerce-payments`) | Medium | evidence § C |
| `wp-login.php` reachable, no IP allowlist, `xmlrpc.php` advertised | Medium | evidence § H |
| CSP **report-only** (not enforced) | Medium | evidence § B |
| **Tamara** absent from CSP despite roadmap mentioning it; Tabby + Mada-via-Checkout.com + STC Pay + Apple Pay confirmed | Medium | evidence § B |
| 89-product delta between master (288) and live (199) | Medium | evidence § F |
| Plugin sprawl beyond CLAUDE.md guidance ("minimize WP plugins"): Jetpack, 4× YITH, Reddit, Snapchat | Medium | evidence § C |
| TLS DV cert ~88-day window, auto-renewal status unverified externally | Low | evidence § B |
| Single test in CI (`tests/example.spec.js`) — no real coverage gate | Low (governance) | repo `.github/workflows/playwright.yml` |
| No staging environment; deploys go straight to production via "Pull Latest" | Architectural | `apps/neogen-deploy/neogen-deploy.php:345-395` |
| Single VPS — no failover | Architectural | `CLAUDE.md` |

The deploy plugin itself is solid: PHP-lints changed files, reset-rollback prepared, encrypted PAT, 20/hr rate-limit. That is **not** a risk — it is a strength worth keeping.

---

## 7. Risks (consolidated)

1. **Pricing-data void** — Cost Price FOB / Landed Cost / Brand / Country of Origin all 100% empty in master CSV. Pricing pipeline cannot enforce margin. *Mitigation: dedicate the next 30 days to filling the work queue (197 SKUs, 985 rows).*
2. **Legal-page 404s** — paid-acquisition blocker. *Mitigation: publish `/return-policy/`, `/terms-and-conditions/`, `/refund-policy/` this week.*
3. **Broken Arabic locale** — `/ar/` redirects to a product page. The site claims bilingual EN/AR per `CLAUDE.md`, but homepage HTML is `lang="en-US"` and there is no `hreflang`. *Mitigation: install/repair multilingual plugin or remove the bilingual claim until ready.*
4. **TTFB 3 s** — every paid click loses on conversion at this TTFB. LiteSpeed Cache appears inactive (no `x-litespeed-cache` header). *Mitigation: enable full-page cache, or move to a CDN.*
5. **Single VPS, no staging** — every deploy is production. The deploy plugin's lint+rollback is the only safety net. *Mitigation: add a one-machine "staging" subdomain, or at least a Playwright smoke-test gate in the deploy plugin.*
6. **Plugin sprawl & overlap** — two cookie plugins, two Stripe gateways, four YITH addons, Jetpack. Each is a security/perf surface. *Mitigation: deprecate one of each overlapping pair before opening Tier 3.*
7. **ZATCA Wave-24 deadline 2026-06-30** — if rolling-12-mo revenue clears 375K SAR, Phase-2 integration is mandatory. *Mitigation: track revenue weekly; either implement n8n-W9 in production or stay under threshold consciously.*
8. **No JSON-LD product schema** — Google rich results unreachable. *Mitigation: re-enable WC default schema or add a small mu-plugin emitting it.*
9. **Public `wp-login.php` + advertised `xmlrpc.php`** — credential-stuffing and pingback-DDoS surface. *Mitigation: IP-allowlist `wp-login.php` and disable XML-RPC if unused.*
10. **Migration to NovaKeys is paused at Phase 1** — risk of stale data drift between source and target. *Mitigation: define an explicit "do or freeze" decision before moving Tier 2 into production marketing.*

---

## 8. Roadmap

### Week 1 (this week)
- Publish `/return-policy/`, `/terms-and-conditions/`, `/refund-policy/` (non-negotiable for paid acquisition).
- Enable LiteSpeed full-page cache; re-probe TTFB.
- Decide Stripe gateway: keep `woocommerce-payments` OR `woocommerce-gateway-stripe`, deactivate the other.
- Decide cookie plugin: keep one, deactivate the other.
- Re-enable WooCommerce default JSON-LD product schema (or remove the override).
- IP-allowlist `wp-login.php`; disable `xmlrpc.php` if unused.

### Weeks 2–4
- Fill `Cost Price FOB`, `Landed Cost (SAR)`, `Brand`, `Country of Origin` in the master CSV for Tier-2 (91 SKUs first).
- Walk down the supplier work queue (197 SKUs); aim for 50% supplier-price coverage by end of week 4.
- Repair `/ar/` (multilingual plugin or static AR landing) and add `hreflang` markers.
- Add JSON-LD `Product` + `Offer` + `BreadcrumbList` to product pages.
- Monitor rolling-12-mo revenue against the 375K SAR ZATCA Wave-24 threshold.

### Weeks 5–8
- Decide neogen → novakeys migration: ship Phases 2–5 or formally freeze.
- Implement ZATCA Phase-2 e-invoicing (`docs/n8n-guide/W9-zatca-invoice-notify.md`) **before 2026-06-30** if revenue trend ≥ 375K SAR / yr equivalent.
- Add a Playwright smoke-test gate to `apps/neogen-deploy` so every "Pull Latest" runs cart-checkout-payment-gateway-load test before reset.
- Open Tier-3 SKUs in waves of 25, gated on price-floor audit going green.

---

## 9. Needs verification (parked, not guessed)

- VPS plan tier and SAR cost (blazr.net account)
- UpdraftPlus backup destination and rotation policy
- Whether ZATCA Phase-2 is currently live in production (the workflow doc exists; no live fingerprint observed)
- Whether the 89-SKU delta between master (288) and live (199) is intentional drafts or a publishing failure
- Inventory model (dropship vs warehoused) — no PO data in repo
- Email service for transactional WooCommerce mail (WP Mail SMTP is in the NGS snapshot but live config unknown from outside)
- Marketing budget and current monthly revenue — neither is in the repo
- Whether sale prices and Amazon SA reference prices in `price_floor_audit.csv` are current or stale
- Whether the Reddit / Snapchat WC plugins are tracking conversions or just installed

---

## 10. Appendices

- **A.** `2026-05-07-financial-model.csv` — every numeric input tagged `confirmed_from_repo` / `confirmed_from_web` / `default_office_assumption` / `unknown`.
- **B.** `2026-05-07-tech-audit-evidence.md` — raw URL probes, TLS, application stack, plugin/theme inventory, sitemap counts, security observations, reproducer commands.
- **C.** `evidence/live-probe-2026-05-07.txt` — full curl/header capture (raw).
- **D.** `evidence/tls-cert-2026-05-07.txt` — TLS cert subject/issuer/dates/fingerprint.
- **E.** `evidence/homepage.html`, `evidence/product-sample.html`, `evidence/sitemap-index.xml`, `evidence/robots.txt` — captured artifacts.

**Sources (web, all access date 2026-05-07):**
- ZATCA Wave 24 — `https://zatca.gov.sa/en/Pages/news_1426.aspx`
- EY Wave 24 alert — `https://www.ey.com/en_gl/technical/tax-alerts/saudi-arabia-announces-24th-wave-of-phase-2-e-invoicing-integration`
- KSA VAT 15% — `https://www.cleartax.com/sa/vat-rates-saudi-arabia`
- GASTAT internet usage — `https://techafricanews.com/2026/01/07/saudi-arabia-hits-99-internet-usage-among-15-74-year-olds-gastat-reports/`
- DataReportal Digital 2025 — `https://datareportal.com/reports/digital-2025-saudi-arabia`
- SAMA 85% e-payments — `https://www.spa.gov.sa/en/N2558262`
- Mada April 2025 — `https://www.arabnews.com/node/2604150/business-economy`
- Tabby fee range — `https://paymentproviders.io/compare/tamara-vs-tabby`
- Statista KSA e-commerce — `https://www.statista.com/outlook/emo/ecommerce/saudi-arabia`
- Mordor KSA e-commerce — `https://www.mordorintelligence.com/industry-reports/saudi-arabia-ecommerce-market`
