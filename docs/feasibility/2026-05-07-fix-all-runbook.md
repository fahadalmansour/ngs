# NeoGen Store — "Fix All" WP-Admin Runbook

**Generated:** 2026-05-07 (companion to commit `apps/neogen-custom@v1.40.0` — security mu-plugin)
**Audience:** the merchant. These are the 6 admin-only items that close the post-deploy probe's remaining warnings.
**Time budget:** ~45 minutes total if you do them in order.

After all 6 are done, run `./scripts/post-deploy-probe.sh` from the repo root. Expected: **0 critical fails, ≤4 soft warns** (down from the current 2 critical + 7 soft).

Two probe items are **deliberately not in this runbook** — see §7 below for why.

---

## 0. Pull Latest first (if you haven't)

The security mu-plugin in v1.40.0 closes the XML-RPC and wp-login warnings without any admin clicks. Get it deployed before doing anything else.

```
https://neogen.store/wp-admin/tools.php?page=neogen-deploy
→ click "Pull Latest"
→ wait for the version badge to read "NG 1.40.0"
```

After the deploy, run the probe — the soft-warn count should drop from 7 to 5 just from this step (X-Pingback header gone, generator stripped).

---

## 1. Reconcile live catalog (5 minutes — biggest single win)

**Why it matters:** Live site has 199 products; master catalog has 163. The 36-product delta is orphans from categories you deleted (Gift Cards & Software Keys, Gaming) plus 3 dropped specialty items. Both `/product-category/gift-cards/` and `/product-category/gaming/` still return HTTP 200 — the only **critical** fails the probe still flags.

### Path A — WP admin (point and click)

```
WP admin → Products → All Products
  Filter by category: "Gift Cards & Software Keys"
  ☐ → ☑ Select all   → Bulk action → Move to Trash → Apply

  Filter by category: "Gaming"
  ☐ → ☑ Select all   → Bulk action → Move to Trash → Apply

  Search: "Mammotion LUBA"   → Trash
  Search: "Worx Landroid"    → Trash
  Search: "FYRTUR"           → Trash

WP admin → Products → Categories
  Delete the now-empty parent terms: "Gift Cards & Software Keys", "Gaming"
```

### Path B — WP-CLI over SSH (faster)

```bash
ssh blazr-vps   # or whatever your VPS alias is
cd /var/www/ngs1   # confirm path; adjust if your install root differs

# Trash all products in the deleted categories (uses the term slugs)
wp post list --post_type=product \
  --tax_query='[{"taxonomy":"product_cat","field":"slug","terms":["gift-cards","gaming","gift-cards-software-keys"]}]' \
  --format=ids \
  | xargs -r wp post delete --force

# Trash the 3 individually-dropped products
wp post list --post_type=product --s="Mammotion LUBA"  --format=ids | xargs -r wp post delete --force
wp post list --post_type=product --s="Worx Landroid"   --format=ids | xargs -r wp post delete --force
wp post list --post_type=product --s="FYRTUR"          --format=ids | xargs -r wp post delete --force

# Delete the now-empty parent category terms
wp term delete product_cat gift-cards gaming gift-cards-software-keys 2>/dev/null
```

### Verification

```bash
./scripts/post-deploy-probe.sh
```

Expect:
- `Sitemap product count: 163` (was 199) ✓
- `/product-category/gift-cards/ → 404` ✓ (was 200, critical fail)
- `/product-category/gaming/ → 404` ✓ (was 200, critical fail)

**Both critical fails turn green after this step.**

---

## 2. Enable LiteSpeed full-page cache (3 minutes)

**Why it matters:** TTFB is currently 3-6 seconds. The expected TTFB for a cached LiteSpeed install is 0.3-0.8s. Every paid-acquisition click loses on conversion at this TTFB.

```
WP admin → LiteSpeed Cache → Cache → tab "1) Cache"
  Enable Cache:                    ☑ ON
  Cache Logged-in Users:           ☐ OFF (good — keeps admins on uncached responses)
  Cache Commenters:                ☑ ON
  Cache REST API:                  ☑ ON
  Cache Login Page:                ☐ OFF (your security mu-plugin's rate-limiter needs to see live POSTs)

  → Save Changes

WP admin → LiteSpeed Cache → Cache → tab "5) Excludes"
  Add to "Do Not Cache URIs":
    /wp-admin/
    /wp-login.php
    /xmlrpc.php
    /cart/
    /checkout/
    /my-account/

  → Save Changes

WP admin → LiteSpeed Cache → Toolbox → Purge → "Purge All"
```

### Verification

```bash
./scripts/post-deploy-probe.sh
```

Expect:
- `Homepage HTTP 200 (TTFB 0.3-0.8s, ...)` ✓ (was 3-6s)
- `x-litespeed-cache header present` ✓ (was missing, soft warn)

---

## 3. Deactivate one cookie plugin (1 minute)

**Why it matters:** Both `cookieadmin` and `cookieadmin-pro` are active. They likely conflict on the consent banner state — PDPL implications + slower JS. Pick one and deactivate the other.

**Recommendation:** Keep `cookieadmin-pro` (the paid version is newer, supports KSA-PDPL banner copy out of the box). Deactivate `cookieadmin`.

```
WP admin → Plugins
  Find "Cookie Admin"          → Deactivate
  (Leave "Cookie Admin Pro" active)
```

After deactivation:
- Open https://neogen.store/ in an incognito window
- Confirm the consent banner appears once and dismisses correctly
- Check that the WC checkout still loads (not blocked by a missing-cookie state)

If anything looks wrong, reactivate `cookieadmin` and reverse the choice (deactivate `cookieadmin-pro` instead).

---

## 4. Deactivate one Stripe gateway (1 minute)

**Why it matters:** Both `woocommerce-payments` and `woocommerce-gateway-stripe` are active. They register overlapping payment methods and can fight over checkout button rendering.

**Recommendation:** Keep `woocommerce-payments` (newer, Stripe-built, better Mada support, supports Apple Pay + Google Pay button placement out of the box). Deactivate `woocommerce-gateway-stripe`.

```
WP admin → Plugins
  Find "WooCommerce Stripe Gateway"   → Deactivate

WP admin → WooCommerce → Settings → Payments
  Confirm these methods are still enabled and configured:
    ☑ WooPayments (Visa / Mastercard / Mada)
    ☑ Tabby
    ☑ STC Pay
    ☑ Apple Pay (under WooPayments → Settings → Express checkouts)
```

**Test the checkout in an incognito window:** add a product to cart, go to checkout, confirm the payment-method radio buttons render correctly. If anything's missing, reactivate `woocommerce-gateway-stripe` and reverse — but you'll then want to disable the duplicate methods inside the gateway's own settings to stop the rendering conflict.

---

## 5. Import tax rates (2 minutes)

**Why it matters:** Without VAT configured in WooCommerce, your invoices won't show 15% VAT line items — ZATCA will not be happy.

**File:** `/Users/fahadalmansour/Downloads/tax_rates.csv` (already prepared, 14 rows: KSA + GCC + WY US)

```
WP admin → WooCommerce → Settings → General
  Currency:                       Saudi riyal (ر.س)
  Currency position:              Right
  Decimal separator:              .
  Thousand separator:              ,
  Number of decimals:              2

  Selling location(s):             Sell to specific countries
  Sell to specific countries:      Saudi Arabia (only — for soft launch)
                                   (add UAE/BH/OM later only after registering for VAT there)

WP admin → WooCommerce → Settings → Tax
  Prices entered with tax:         Yes, I will enter prices inclusive of tax
  Calculate tax based on:          Customer billing address
  Shipping tax class:              Shipping
  Display prices in the shop:      Including tax
  Display prices during cart and checkout: Including tax
  Display tax totals:              As a single total
  → Save changes

WP admin → WooCommerce → Settings → Tax → "Standard rates" tab
  → Click "Import CSV"
  → Choose file: /Users/fahadalmansour/Downloads/tax_rates.csv
  → Click "Upload file and import"

  Verify the table shows:
    SA — — — 15.0000 — VAT — Pri 1 — Compound 0 — Shipping ✓
    AE — — — 5.0000 — VAT — ...
    BH — — — 10.0000 — VAT — ...
    OM — — — 5.0000 — VAT — ...
    QA — — — 0.0000 — ...
    KW — — — 0.0000 — ...
    US, WY — — 4.0000 — WY Sales Tax — ...
    + 7 zero-rate variants
```

### Verification

Add a 100 SAR test product to your cart in an incognito window. At checkout, the tax line should read **"VAT (15%): 13.04 SAR"** (because prices are entered inclusive: 100 SAR includes 13.04 SAR of VAT). Total stays at 100 SAR.

---

## 6. Apply marketing copy (~25 minutes)

**Why it matters:** The 4 category landing pages and 19 hero product editors currently have generic / machine-translated content. Applying the launch copy packs lifts conversion rate and replaces broken AR titles like "ساحر حساس تسرب المياه" (literally "magician water sensor") with proper Saudi-register copy.

**Files (all in `docs/feasibility/`):**

- `2026-05-07-smart-home-launch-copy.md` — 5 SKUs (Home Assistant Green, Aqara Door Lock A100, UniFi G4 Pro Camera, Sonoff TX T5, Aqara Water Leak Sensor)
- `2026-05-07-networking-launch-copy.md` — 5 SKUs (10G SFP+ DAC, Cloud Gateway Ultra, U6 Pro, MikroTik CRS326, USW-Pro-24-PoE)
- `2026-05-07-homelab-launch-copy.md` — 5 SKUs (Beelink EQ12, Dell OptiPlex 7070 Refurb, Synology DS225+, Netgate 2100 MAX, MinisForum MS-01)
- `2026-05-07-security-launch-copy.md` — 4 SKUs (Reolink RLC-810A, Eufy S330, Ajax Hub 2 Plus, UniFi G4 Doorbell Pro)

**Per category (~6 minutes each):**

```
WP admin → Pages → find the category-archive page → paste §1 EN+AR hero block + sub-deck
  Set Title tag + Meta description in Yoast/Rank Math from the SEO summary table

WP admin → Products → for each hero SKU in that pack:
  Replace "Product name (AR)" field with the improved AR title from §X
  Replace EN long description in the main editor with the §X long EN block
  Paste the §X long AR block into the Arabic-description meta field
  Set Yoast/Rank Math Title + Meta from the SEO summary table
  → Update
```

**Round-trip note:** the next time you regenerate `output/spreadsheet/woocommerce_ready_import.csv` from `master.xlsx`, copy these descriptions back into the master xlsx so they don't get clobbered by the next pipeline run. (Per the marketing-neogen skill rule.)

---

## 7. Skipped from this runbook — with rationale

### CSP flip from `report-only` to enforced

Two pre-existing edits in `apps/neogen-custom/mu-plugins/neogen-seo.php` and `plugins/neogen-pro/includes/modules/class-module-seo.php` already remove `'unsafe-eval'` from `script-src`. They've been intentionally kept out of every commit so far.

Why: removing `unsafe-eval` and flipping the header from `Content-Security-Policy-Report-Only` to `Content-Security-Policy` can break Elementor's admin editor, the Site Kit by Google integration, and any inline script that uses `eval`. Before flipping:

1. Check the CSP report endpoint logs for the last 7 days
2. Whitelist any reported violations that are legitimate
3. Flip in a low-traffic window (not Friday evening Saudi peak)
4. Have the rollback path tested (revert the 1-line change, click Pull Latest)

This is a deliberate decision, not a missed item. When you're ready, this is a separate, focused commit with its own browser-test cycle.

### `/ar/` locale repair

The probe has been showing for 5+ runs that `/ar/` returns a 301 to a single product page (Arduino Mega 2560). This isn't a routing bug we can fix without seeing:

- The site's `.htaccess` and any nginx redirect rules
- Active multilingual plugin (Polylang? Weglot? Elementor's locale switcher?)
- Custom redirect rules in `wp-content/plugins/` and `mu-plugins/`

Until that investigation, attempting a "fix" via mu-plugin would be guessing. **Better as: investigation pass first, fix second.** When you're ready to dig in, send a `wp option get blogname`, the active plugins list (`wp plugin list --status=active`), and a head of `/var/www/ngs1/.htaccess` and we can plan the fix.

---

## Final verification

After steps 0-6 are complete:

```bash
cd ~/sites/neogen-store
./scripts/post-deploy-probe.sh
```

Expected output:
- **Critical fails: 0** (was 2 — both gift-cards/gaming category pages now 404)
- **Soft warns: 3-4** (was 7):
  - `/ar/` redirects (deferred, see §7) — soft warn stays
  - CSP report-only (deferred, see §7) — soft warn stays
  - `wp-login.php → 200` — soft warn stays (rate-limit doesn't change the surface response, but the security mu-plugin DOES rate-limit underneath)
  - TTFB might still be ~1s if LiteSpeed cache wasn't fully tuned — keep iterating

If anything goes red after a step, the most useful command is:

```bash
./scripts/post-deploy-probe.sh --verbose
```

Plus a Pull Latest rollback option if a code-side change ever needs reverting:
```
WP admin → tools.php?page=neogen-deploy → "Rollback −1 commit"
```
