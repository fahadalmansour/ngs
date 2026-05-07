# NeoGen Store — Soft-Launch Runbook (2026-05-07)

**Goal:** ship the post-prune 163-SKU catalog (114 launch-ready in scope) with the gift-cards/gaming purge applied to production.

**Pre-flight (verified before this runbook was generated):**
- ✅ All 80 PHP files in `apps/neogen-custom/` pass `php -l`
- ✅ Master catalog: 163 products, 10 categories (no Gift Cards / Gaming / Mammotion / Worx / IKEA FYRTUR)
- ✅ Downstream artifacts (`woocommerce_ready_import.csv`, `price_floor_audit.csv`) at 163 rows, consistent with master
- ✅ `apps/neogen-custom/.backup-gift-cards-purge-*` dir cleaned up after move to `~/sites/novakeys/`
- ✅ Master xlsx + csv backed up at `…backup-20260507-065041` and `…backup-20260507-075900` for rollback

---

## Step 1 — Push code (gift-cards purge) to GitHub

**What:** commit the 24 deletes + 4 inline-disable edits in `apps/neogen-custom/`. The `apps/neogen-deploy` plugin's PHP-lint gate runs before any reset, so this push is safe to retry.

**Done by Claude after your `yes` confirmation** (separate message). The commit will land on `apps/neogen-custom@main` → `origin/main`.

---

## Step 2 — Trigger production deploy

After Step 1 push completes, open WP admin:

```
https://neogen.store/wp-admin/tools.php?page=neogen-deploy
```

1. Click **Pull Latest** button.
2. Watch the log panel — the deploy plugin will:
   - `git fetch --depth 50 origin main`
   - PHP-lint every changed `.php` file
   - Reset working tree to `origin/main`
   - Reinstall the auto-loader
   - Update `wp-content/mu-plugins/neogen-custom-loader.php`
3. Verify the admin-bar badge updates to `🚀 NG <new-version>` on a public page.

**If lint fails:** the deploy will refuse to reset. Read the lint error in the panel; fix locally; re-push; retry. The site stays on the previous good version.

**Rollback:** click **Rollback −1 commit** in the same admin page if anything goes wrong post-deploy.

---

## Step 3 — Reconcile live catalog to the post-prune master

Live `neogen.store` still has **199 products** (per the 2026-05-07 sitemap probe) including all the deleted Gift Cards & Software Keys + Gaming categories. The new 163-SKU master is in the repo but the live products aren't trashed yet.

**Option A — WP admin (manual):**

1. WP admin → **Products → All Products**
2. Filter by category: `Gift Cards & Software Keys`. Bulk-select. Bulk action → **Move to Trash**. Apply.
3. Repeat for `Gaming` category.
4. Search and trash the 3 dropped products: `Mammotion LUBA`, `Worx Landroid`, `IKEA FYRTUR`.
5. Empty trash after 24h sanity period (or leave; trash auto-purges after 30 days).

**Option B — WP-CLI (faster, requires SSH access):**

```bash
ssh blazr-vps  # or wherever you SSH into the VPS
cd /var/www/ngs1
wp post list --post_type=product --tax_query='[{"taxonomy":"product_cat","field":"slug","terms":["gift-cards-software-keys","gaming"]}]' --format=ids \
  | xargs wp post delete --force
# Plus the 3 individually:
wp post list --post_type=product --s="Mammotion LUBA" --format=ids | xargs -r wp post delete --force
wp post list --post_type=product --s="Worx Landroid" --format=ids | xargs -r wp post delete --force
wp post list --post_type=product --s="FYRTUR" --format=ids | xargs -r wp post delete --force
```

**Option C — Re-import from master:**

Use `output/spreadsheet/woocommerce_ready_import.csv` (163 rows, regenerated post-prune) as the canonical product source via WP admin → **WooCommerce → Products → Import**. Choose **"Update existing products"** and **"Skip"** for unmatched. This won't auto-delete the orphans but will at least sync prices and metadata for the 163 surviving SKUs.

For a clean soft-launch, **Option A or B** is recommended. Option C alone leaves the deleted SKUs visible.

---

## Step 4 — Apply the new pricing data

The 93 newly-scraped Amazon SA reference prices are in the master xlsx and the regenerated `woocommerce_ready_import.csv`. To push them to live:

1. WP admin → **WooCommerce → Products → Import**
2. Upload `output/spreadsheet/woocommerce_ready_import_safe_verified_only.csv` (the price-floor-passing strict subset)
3. Map columns (WP usually auto-detects from the header row)
4. Choose **"Update existing products"** → **Run the importer**
5. Spot-check 3-5 products on the live site to confirm `Regular Price` / `Sale Price` / metadata updated

**Risk:** the import will overwrite any live-only edits. If you've manually adjusted pricing on the live site in the last few days outside this repo's catalog, those changes will be reverted.

---

## Step 5 — Soft-launch hardening (Week 1 from feasibility study)

Before opening paid acquisition, complete the launch-blocker checklist from `2026-05-07-feasibility-study.md` § 8 Roadmap:

- [ ] Publish `/return-policy/`, `/terms-and-conditions/`, `/refund-policy/` (currently 404)
- [ ] Enable LiteSpeed full-page cache (no `x-litespeed-cache` header observed)
- [ ] Decide between `woocommerce-payments` and `woocommerce-gateway-stripe` — deactivate one
- [ ] Decide between `cookieadmin` and `cookieadmin-pro` — deactivate one
- [ ] Re-enable WooCommerce default JSON-LD product schema (currently 0 blocks on product pages)
- [ ] Repair `/ar/` locale (currently 301-redirects to a single product page)
- [ ] IP-allowlist `wp-login.php`, disable `xmlrpc.php`

---

## Step 6 — Post-deploy verification

Run the same probe as the 2026-05-07 audit to confirm the deploy didn't regress:

```bash
cd /Users/fahadalmansour/sites/neogen-store
# Quick health check
curl -sS -o /dev/null -w "HTTP %{http_code} | TTFB %{time_starttransfer}s\n" -L https://neogen.store/

# Confirm sitemap reflects the prune
curl -sS https://neogen.store/wp-sitemap-posts-product-1.xml | grep -oE '<loc>' | wc -l
# Should drop from 199 → 163 (or close to it once Step 3 completes)

# Confirm /return-policy/ now exists (after Step 5)
curl -sS -o /dev/null -w "%{http_code}\n" https://neogen.store/return-policy/
# Expect 200, not 404
```

Then a manual smoke test:
1. Open https://neogen.store/ in an incognito window
2. Navigate Shop → pick a Tier-A product (e.g., `NT-WAP-UBQ-001` Ubiquiti UniFi U6 Pro)
3. Add to cart → checkout → confirm payment gateways load (Tabby, Mada via Checkout.com, STC Pay, Apple Pay)
4. Stop short of completing payment unless you want to make a real test purchase

---

## Step 7 — Track ZATCA Wave-24 threshold

Wave 24 deadline: **2026-06-30**. Threshold: **SAR 375,000/yr taxable revenue** (≈ 31,250 SAR/mo). Above this, Phase-2 e-invoicing (`docs/n8n-guide/W9-zatca-invoice-notify.md`) must be live in production.

Set a calendar reminder for **2026-06-15** to review revenue trend and either:
- Stay under threshold consciously, or
- Implement W9 workflow before deadline

---

## Quick reference

| Asset | Path |
|---|---|
| Pull Latest (production deploy) | `https://neogen.store/wp-admin/tools.php?page=neogen-deploy` |
| Master catalog | `data/catalogs/master/Neogen_Master_Catalog_Blueprint.xlsx` |
| Deploy-ready import CSV | `output/spreadsheet/woocommerce_ready_import.csv` (163 rows) |
| Strict (price-floor PASS only) import | `output/spreadsheet/woocommerce_ready_import_safe_verified_only.csv` |
| Tier list per SKU | `docs/feasibility/2026-05-07-smart-home-fill-backlog.csv` |
| Dropship-fit subset | `docs/feasibility/2026-05-07-dropship-shortlist.csv` (9 SKUs) |
| Full feasibility study | `docs/feasibility/2026-05-07-feasibility-study.md` |
| Notion mirror | `https://www.notion.so/35995fc54e03814a91b9c612decf8846` |

## Rollback if needed

| If… | Run… |
|---|---|
| Deploy makes the site unstable | WP admin → **Rollback −1 commit** button on the deploy page |
| Master catalog needs revert | `cp data/catalogs/master/Neogen_Master_Catalog_Blueprint.xlsx.backup-20260507-065041 data/catalogs/master/Neogen_Master_Catalog_Blueprint.xlsx` then re-run `npm run woo:generate && sourcing:generate && price:guard` |
| Gift-cards mu-plugins need to come back | Move them back from `~/sites/novakeys/mu-plugins/` to `apps/neogen-custom/mu-plugins/` and revert the inline-comment-out edits in `neogen-theme.php`, `neogen-redesign.php`, `neogen-deploy-tools.php`, `class-module-redesign.php` |
