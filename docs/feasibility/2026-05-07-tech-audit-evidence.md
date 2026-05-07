# NeoGen Store — Tech Audit Evidence Appendix

**Probe date:** 2026-05-07
**Probe host:** local macOS (read-only, no auth)
**Raw probe log:** `evidence/live-probe-2026-05-07.txt`

Every claim in `2026-05-07-feasibility-study.md § Tech Audit` traces back to a row below. Re-running the listed command should reproduce the same value (small variance allowed for TTFB).

## A. URL probe results

| URL | Status | TTFB | Size | Note |
|---|---|---|---|---|
| `/` | 200 | 3.07s | 253 KB | Homepage |
| `/shop/` | 200 | 4.91s | 197 KB | Shop archive |
| `/cart/` | 200 | 2.79s | 430 KB | Cart loads even when empty |
| `/wp-json/` | 200 | 4.03s | **1,690 KB** | REST root publicly indexed |
| `/wp-login.php` | 200 | 3.02s | 12.7 KB | **No IP restriction** |
| `/robots.txt` | 200 | 2.38s | 525 B | Allows ChatGPT/Perplexity, blocks anthropic-ai |
| `/wp-sitemap.xml` | 200 | 3.23s | 870 B | XML sitemap index |
| `/wp-sitemap-posts-product-1.xml` | 200 | 2.57s | 28 KB | **199 product entries** |
| `/feed/` | 200 | 2.10s | 1.7 KB | RSS active |
| `/product/arduino-mega-2560-…/` | 200 | 5.23s | 286 KB | Sample product |
| `/ar/` | **301 → /product/arduino-…** | 4.48s | — | **Arabic locale broken — redirects to a single product** |
| `/return-policy/` | **404** | 3.16s | 165 KB | **Missing legal page** |
| `/refund_returns/` | **404** | 2.79s | 165 KB | **Missing legal page** |
| `/terms-and-conditions/` | **404** | 2.58s | 165 KB | **Missing legal page** |
| `/privacy-policy/` | 200 | 2.41s | 158 KB | Present |

**TTFB summary:** 2.1 s – 5.2 s across all paths. p50 ≈ 3 s. Industry expectation for cached WP+LiteSpeed is < 500 ms. Cause is `unknown` from this read-only probe (could be PHP-FPM tuning, plugin overhead, or no full-page cache active — the `x-litespeed-cache` header is **not** in any response).

## B. Server / TLS

```
server: LiteSpeed
strict-transport-security: max-age=31536000; includeSubDomains
x-content-type-options: nosniff
x-frame-options: SAMEORIGIN
referrer-policy: strict-origin-when-cross-origin
content-security-policy-report-only: default-src 'self'; ... (long policy)
permissions-policy: interest-cohort=(), browsing-topics=()
x-pingback: https://neogen.store/xmlrpc.php   ← XML-RPC enabled (legacy attack surface)
```

TLS cert: `CN=neogen.store`, issuer `Sectigo Public Server Authentication CA DV R36`, valid `2026-04-29 → 2026-07-16` (DV cert, 88-day Let's-Encrypt-style window). Auto-renewal status `unknown`.

CSP is **report-only** (not enforced). Allowed payment domains in `form-action`: `*.mada.com.sa`, `*.checkout.com`, `*.tabby.ai`, `*.stcpay.com.sa`, `*.paypal.com`. Allowed in `frame-src`/`script-src`: `*.tabby.ai`, `*.checkout.com`, `*.stcpay.com.sa`, `*.applepay.cdn-apple.com`. **Tamara is not in the CSP** despite being mentioned in `docs/ops/novakeys-migration-checklist.md:35-41`.

## C. Application stack (parsed from `/`)

```
<meta name="generator" content="WordPress 6.9.4" />
<meta name="generator" content="WooCommerce 10.7.0" />
<meta name="generator" content="Site Kit by Google 1.178.0" />
<meta name="generator" content="Elementor 4.0.6; …" />
```

Plugins/themes loaded (from `/wp-content/.../` asset paths in homepage HTML):

```
mu-plugins:  neogen-custom
plugins:     blocksy-companion, cookieadmin, cookieadmin-pro, jetpack,
             reddit-for-woocommerce, snapchat-for-woocommerce,
             woocommerce, woocommerce-gateway-stripe, woocommerce-payments,
             yith-essential-kit-for-woocommerce-1, yith-woocommerce-compare,
             yith-woocommerce-product-add-ons, yith-woocommerce-subscription
themes:      blocksy, blocksy-child
```

13 active third-party plugins **plus** the in-house `neogen-custom` MU bundle. Notes:

- **Two cookie plugins active** (`cookieadmin` + `cookieadmin-pro`) — likely conflict; one should go.
- **Two Stripe gateways active** (`woocommerce-gateway-stripe` + `woocommerce-payments`) — overlapping; one should go.
- `jetpack` is present despite the project rule "Minimize WP plugins; prefer MU-plugins" (`/Users/fahadalmansour/CLAUDE.md`). Jetpack is heavy and not needed if Site Kit, MailPoet, etc. cover the function.
- Marketing pixels from `reddit-for-woocommerce` and `snapchat-for-woocommerce` are active but the master CSV has no sale data populated to feed them.

## D. SEO

- **0 `application/ld+json` blocks** on the sample product page → Google's Product / Offer rich result eligibility = none. WooCommerce 10.7 emits product schema by default; this is being suppressed somewhere (theme override or plugin conflict).
- **`<html lang="en-US">`** on homepage — no Arabic alternate; no `<link rel="alternate" hreflang>` present.
- Sitemap counts (from `wp-sitemap-*.xml`):
  - Products: **199** (master catalog has 288 → 89 missing)
  - Pages: 18
  - Categories: 11
  - Posts (blog): 1
  - MailPoet pages: 2
- `robots.txt` explicitly **blocks anthropic-ai** while allowing ChatGPT/Perplexity/Facebook. Asymmetric AI policy — fine if intentional, worth confirming.

## E. WP REST surface

`/wp-json/` returns 1.69 MB of JSON (route list). Unauthenticated REST is enabled — fine for public content but the response includes `routes`, `_links`, plugin descriptors, and authenticated route stubs. Not a vulnerability, but a footprint.

## F. Catalog reality vs master

| Source | Count | Note |
|---|---|---|
| Master CSV rows | 288 | `data/catalogs/master/Neogen_Master_Catalog_Blueprint.csv` |
| Live sitemap products | 199 | `wp-sitemap-posts-product-1.xml` |
| WC import CSV | 288 (all `Published=1`, all `Type=simple`) | `output/spreadsheet/woocommerce_ready_import.csv` |
| Price-floor PASS | 91 | `output/spreadsheet/price_floor_audit.csv` |
| Price-floor exceptions | 197 (173 medium + 24 high) | same |
| Supplier prices filled | 39 SKUs / 1,440 sourcing rows | `output/spreadsheet/supplier_sourcing_matrix.csv` |
| Master columns 100% empty | Brand, Country of Origin, Cost Price FOB, Landed Cost, MSRP, Cost Currency, Lifecycle Status (all 288 = `Mainstream`) | direct CSV inspection |

Discrepancy of 89 between master and live is `unknown` from read-only data — could be drafts, trashed duplicates, or unimported items. The `apps/neogen-custom/mu-plugins/neogen-launch-cleanup.php` audit on 2026-04-28 flagged 6 duplicate pairs.

## G. Performance budget gaps

- **TTFB ~3 s** vs target ≤ 0.5 s for cached WP — `confirmed_from_live_site`.
- **Homepage 253 KB**, product 286 KB, **cart 430 KB** — large for an e-commerce mobile audience on KSA mobile networks.
- No `x-litespeed-cache` header observed → LiteSpeed full-page cache likely **not active** for the request paths probed (or tagged off). Worth checking `LiteSpeed > Cache > General Settings` in WP admin.

## H. Security observations

- `wp-login.php` reachable without IP allowlist or rate-limit header.
- `xmlrpc.php` advertised via `x-pingback`.
- CSP **report-only**, not enforced → content injection risk is detected but not blocked.
- TLS cert lifetime ~ 88 days; auto-renewal not verifiable from outside.
- Two cookie plugins active → consent banner state may be inconsistent (PII / PDPL implication).

## I. Reproducing this evidence

```bash
cd /Users/fahadalmansour/sites/neogen-store
# Status + headers for one URL
curl -sS -o /dev/null -w "STATUS=%{http_code} TTFB=%{time_starttransfer}s\n" \
  --max-time 15 -L https://neogen.store/

# Full probe set (already saved to evidence/live-probe-2026-05-07.txt)
ls docs/feasibility/evidence/

# Catalog stats
python3 -c "import csv; r=list(csv.DictReader(open('data/catalogs/master/Neogen_Master_Catalog_Blueprint.csv'))); print(len(r))"
```

## J. Open items (cannot confirm read-only)

- Whether Cart/Checkout actually completes a real test order
- Whether ZATCA Phase 2 e-invoicing is wired up in production (workflow doc exists at `docs/n8n-guide/W9-zatca-invoice-notify.md` but live status unknown)
- VPS plan tier and SAR cost (blazr.net account)
- UpdraftPlus backup destination (S3? Drive? local-only?)
- Whether sale prices and Amazon SA reference prices in `price_floor_audit.csv` reflect *current* market or stale snapshots
- Whether the 89-product gap (master 288 vs live 199) is intentional or a publishing failure
