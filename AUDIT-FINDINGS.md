# NeoGen Store — Visual/UX Audit

Last run: **2026-05-08** · Run ID: **2026-05-08-1500**
Captured: `~/.claude/reports/neogen-store/screenshots/2026-05-08/` (24 shots: home + shop + cart + account × 3 viewports × 2 locales)
HEAD: `c8ea0b4`
Notion: https://www.notion.so/e38bdfd54e3343109402b1def5e8c693

> Constraint: targeted component-level fixes only. No theme rewrites. Findings that would require a rewrite live under **OUT-OF-SCOPE**.

**Tally:** 3 BLOCKER · 5 HIGH · 6 MEDIUM · 2 LOW · 1 OUT-OF-SCOPE = **17 findings**

---

## BLOCKER

### B1. Shop archive renders ONLY blue "Compare" placeholder bars — no product cards anywhere
- Viewport / page / locale: **all viewports / shop / en + ar**
- Screenshot: `~/.claude/reports/neogen-store/screenshots/2026-05-08/shop-1280-en.png`, `shop-360-en.png`, `shop-1280-ar.png`
- Evidence: 16 solid `#2D7CF6` vertical bars labeled "Compare" — no images, SKUs, prices, or add-to-cart anywhere in the product loop. **Primary commerce path completely broken.**

**Root-cause diagnosis (2026-05-08 follow-up):**

The `.ng-product` override at `apps/neogen-custom/mu-plugins/neogen-theme-assets/templates/woocommerce/content-product.php` is **structurally correct** — it emits image + AR/EN title + specs + price + branded CTA. The routing filter at `mu-plugins/neogen-theme.php:1433-1446` (`wc_get_template_part`) maps `content|product` → that override.

So the most likely live cause is **the filter never fires on `/shop/`** — meaning Blocksy parent theme (or a Site Editor toggle, or a WC blocks update) has switched the shop archive to the **block-based** product loop (`woocommerce/product-collection` block instead of the classic `wc_get_template_part('content','product')` path). The `.ng-product` markup never gets rendered; what's left is Blocksy's default product card with most of its CSS suppressed by `neogen.css` overrides targeting `.ng-product` selectors. The lone visible element is the Compare button because its styles aren't suppressed.

**Diagnostic steps (require WP admin):**

1. Visit `/wp-admin/site-editor.php` — check whether a block template exists for `archive-product` or `shop`. If yes, that's the override bypassing the classic filter.
2. View page source at `https://neogen.store/shop/` and look for `.ng-product` in the HTML. If absent, the override truly isn't firing.
3. Check Settings → WooCommerce → Advanced for any block-template feature toggles.
4. Confirm the active theme is `blocksy-child` and not a Block-FSE variant.

**Component-level fix paths** (without rewriting):

- **Path A** (cleanest): Add a Site Editor template at `archive-product.html` that uses the legacy WC product-loop block (`woocommerce/legacy-template`) so `wc_get_template_part` fires again.
- **Path B**: Replace the block-based product loop with `<ul class="products"><?php woocommerce_product_loop_start(); ... ?></ul>` in a custom shop template.
- **Path C** (quickest if confirmed): Disable WC Blocks' `woocommerce/product-collection` overrides via `add_filter('woocommerce_blocks_register_script_dependencies', '__return_empty_array')` or the equivalent feature flag.

This finding stays BLOCKER until operator confirms which template is rendering the live `/shop/` and which fix path applies.

---

## HIGH

### H1. `<html lang>` stays `en-US` on AR pages — RTL/SEO/screen-reader parity broken
- Source: `apps/neogen-custom/mu-plugins/neogen-theme-assets/templates/front-page.php`
- Fix: Add a language-switch hook (TranslatePress / Polylang filter on `language_attributes`) so `<html lang>` and `dir` flip with locale.
- Evidence: AR pages mix English header chrome ("Cart", "Account", "Search") with Arabic body copy; cookie banner stays English-only despite Arabic locale; layout direction LTR-anchored on AR pages.

### H2. Cookie consent modal blocks the entire above-the-fold area on every page (including login form)
- Source: third-party CookieAdmin plugin (admin-side configuration)
- Fix: Switch to a non-modal bottom-bar variant or reduce its width on 360 so the LOG IN form is reachable without dismissing.
- Evidence: On `account-360-en.png` the cookie modal sits directly over the username/password inputs — visitor cannot SEE the login form let alone tap into it without first dismissing the modal.

### H3. Home hero component is wrong fitness — tiny product strip + oversized headline reads as broken/empty
- Viewport / page / locale: 360 / home / en + ar
- Source: `apps/neogen-custom/mu-plugins/neogen-theme-assets/templates/front-page.php`
- Fix: Swap to a single-product showcase or a 2×1 hero grid; current 1×4 thumbnail strip on 360 has icons under 40 px.
- Evidence: Hero has a huge "NEOGEN جيل التقنية القادم" headline above a row of 4 micro-thumbnails (router/dot/keyboard/backpack); thumbnails too small to identify the products.

### H4. Home page has massive empty-whitespace gap below hero — no category tiles, no value props, no social proof
- Viewport / page / locale: 1280 / home / en
- Source: `apps/neogen-custom/mu-plugins/neogen-theme-assets/templates/front-page.php`
- Fix: Either populate the front-page.php sections (the 5 `wc_get_products` calls likely return empty arrays at the moment) OR hide empty section wrappers via `if ( ! empty( $products ) )` guards.
- Evidence: ~3 viewport heights of pure white space between hero and the 4-icon trust strip near the footer — strongly suggests rendered-but-empty WooCommerce product blocks.

### H5. Footer trust-strip icons (Mada/Apple Pay, Shipping, 14-day return, 12-month warranty) lose alignment on 360
- Source: `apps/neogen-custom/themes/blocksy-child/template-parts/footer-trust.php`
- Fix: Drop trust strip to a 2×2 grid below 420 px; current 4-up forces cards to <80 px wide and labels truncate.
- Evidence: 4 trust cards squeeze into one row on 360, payment-method labels wrap onto 3+ lines and overlap their icons.

---

## MEDIUM

### M1. Cart "New in store" recommendation cards have inconsistent alt text / image fallback for 5 SKUs
- SKUs: `NG-ENT-004`, `NG-SH-003`, `NG-MKR-002`, `NG-ACC-003`, `NG-MKR-005`
- Source: `apps/neogen-custom/mu-plugins/neogen-recommendations.php`
- Fix: Normalize the product-image alt to use `$product->get_name()` with a SKU fallback; ensure no card shows the generic broken-image placeholder.
- Evidence: Far-left recommendation card on `cart-1280-en.png` shows the broken-image icon while siblings render correctly.

### M2. Header on 360 has no visible search affordance — search hidden behind hamburger
- Source: `apps/neogen-custom/themes/blocksy-child/header.php`
- Fix: Surface the search icon next to the cart icon below 768 px — e-commerce primary-action discovery requires it.
- Evidence: 360 header shows only the NEOGEN logo on the left and a single hamburger on the right; cart and search icons are hidden inside the drawer.

### M3. Tap targets on bottom mobile quick-actions row under 44 px (HIG / WCAG 2.5.5)
- Source: `apps/neogen-custom/themes/blocksy-child/assets/scss/components/_mobile-bar.scss`
- Fix: Increase icon hit-area to min 44×44 px with 8 px padding around each.
- Evidence: 4-up icon row (cart/wishlist/account/menu) icons measure ~28-32 px square in `home-360-en.png`.

### M4. Cart empty-state on 360 forces horizontal product scroll with truncated SKU titles
- Source: `apps/neogen-custom/themes/blocksy-child/woocommerce/cart/cart-empty.php`
- Fix: Stack "New in store" cards vertically (1-up) below 480 px; current 3-up squeezes titles to ~12 chars before ellipsis.
- Evidence: 3 Ubiquiti recommendation cards render at ~28% viewport width each; `Ubiquiti UniFi Dream Machine Pro…` clips to `Ubiquiti UniFi…`.

### M5. Cookie banner copy is English-only on AR locale pages
- Source: third-party CookieAdmin (admin settings, not a code path)
- Fix: Add an AR translation set in the CookieAdmin plugin settings; tie display to `pll_current_language()` or equivalent.
- Evidence: AR home shows banner text "We respect your privacy / Customize / Reject All / Accept All" with no Arabic equivalent.

### M6. Footer information block on AR has misaligned column wrap — phone / CR numbers display LTR inside an RTL column
- Source: `apps/neogen-custom/themes/blocksy-child/template-parts/footer.php`
- Fix: Wrap commercial-registration / VAT / phone numerals in `<bdi>` (or explicit `dir="ltr"`) so digits keep grouping inside an RTL paragraph.
- Evidence: AR footer shows "سجل تجاري:" followed by digits that mix bidi orientation — ragged left edges and inconsistent column alignment vs. the EN counterpart.

---

## LOW

### L1. Hero headline weight too heavy vs. body — type scale jump is ~5× with no intermediate tier
- Source: `apps/neogen-custom/themes/blocksy-child/assets/scss/typography.scss`
- Fix: Introduce a subhead/eyebrow tier between H1 and body (e.g. 18 px medium) so the eye has a stepping stone.
- Evidence: ~64 px Arabic headline directly above ~14 px body line with nothing between; visually unbalanced.

### L2. NEOGEN logo lockup is inconsistent — header vs footer use different weights
- Source: `apps/neogen-custom/themes/blocksy-child/assets/img/logo*.svg`
- Fix: Standardize on a single SVG lockup; "NEO" vs "GEN" weight split changes between header (light/bold) and footer (uniform).
- Evidence: Same brand mark rendered two different ways on one page.

---

## OUT-OF-SCOPE

### OOS1. front-page.php makes 5+ uncached `wc_get_products` calls — needs transient/object cache
- Source: `apps/neogen-custom/mu-plugins/neogen-theme-assets/templates/front-page.php`
- Why out of scope: Wrapping each query in a transient is a template-level refactor, beyond component-level scope. (This was already flagged in the `/study-site` performance audit; see `~/.claude/reports/neogen/readiness-2026-05-08.md`.)
- Evidence: Empty whitespace zones on the home page strongly suggest the queries run + return empty/slow.

---

## Summary

The single highest-impact finding is **B1**: the shop archive is completely broken across all viewports + locales, rendering only blue "Compare" placeholder bars where product cards should be. This blocks the primary commerce path. Everything else is secondary until that template regression is fixed — investigate the blocksy-child WooCommerce overrides at `apps/neogen-custom/themes/blocksy-child/woocommerce/content-product.php` (or whichever file currently overrides the product card markup).

Second pattern is **AR locale parity**: H1 (`<html lang>` doesn't switch), M5 (cookie banner EN-only on AR), M6 (LTR digits in RTL column) plus the existing `/study-site` finding all share root cause — the localization layer isn't fully threaded through chrome and third-party plugins.

Third pattern is **mobile reflow**: H3 (tiny hero strip), H5 (trust strip), M2 (no search on 360), M3 (sub-44 px taps), M4 (cart truncation) — desktop-first design didn't shrink past 768 cleanly.

The home page's empty whitespace (H4) is likely the same root cause as B1 — empty/broken WooCommerce queries — and would resolve itself once the product-card template is restored.

No findings here genuinely require a theme rewrite; B1 is a template-edit, not a redesign.
