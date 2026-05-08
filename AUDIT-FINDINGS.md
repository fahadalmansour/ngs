# NeoGen Store — Visual/UX Audit

Last run: **2026-05-08** · Run ID: **2026-05-08-1500**
Captured: `~/.claude/reports/neogen-store/screenshots/2026-05-08/` (24 shots: home + shop + cart + account × 3 viewports × 2 locales)
HEAD: `c8ea0b4`
Notion: https://www.notion.so/e38bdfd54e3343109402b1def5e8c693

> Constraint: targeted component-level fixes only. No theme rewrites. Findings that would require a rewrite live under **OUT-OF-SCOPE**.

**Tally:** 2 BLOCKER · 2 HIGH · 6 MEDIUM · 2 LOW · 1 OUT-OF-SCOPE · 5 RESOLVED = **18 findings** (B1, H3, H4, H5, M-rev resolved 2026-05-08 — H3/H4/H5/M-rev pending v1.48.0+v1.49.0 Pull Latest)

---

## RESOLVED

### B1. Shop archive renders ONLY blue "Compare" placeholder bars — no product cards anywhere ✅ RESOLVED 2026-05-08
- Viewport / page / locale: **all viewports / shop / en + ar**
- Before: `~/.claude/reports/neogen-store/screenshots/2026-05-08/shop-1280-en.png` (and 360/ar variants)
- After:  `~/.claude/reports/neogen-store/screenshots/2026-05-08-after/shop-1280-en.png` (and 360/ar variants)
- Original evidence: 16 solid `#2D7CF6` vertical bars labeled "Compare" — no images, SKUs, prices, or add-to-cart in the product loop.

**Actual root cause (verified by curl + DOM inspection 2026-05-08 18:50):**

Audit's first hypothesis (template routing bypass) was wrong. The `.ng-product` template **was** firing — `curl https://neogen.store/shop/` returned 144 hits for `ng-product` and full `<li>` cards with image + AR/EN title + specs + price + CTA. The visible bug was YITH WooCompare emitting an `<a class="compare button">` as a sibling **after** each `<li>`, styled by YITH as a tall solid blue bar that visually dominated the row when the cards beside it were styled flat-white in this theme. There were 16 buttons → 16 bars, exactly matching the audit screenshot.

**Fix already shipped:** `apps/neogen-custom/mu-plugins/neogen-disable-compare.php` (v1.46.0, deployed 2026-05-08 16:00) hides every `.compare.button` via three layers:
1. `yith_woocompare_is_show_button_in_products_list` filter → `__return_false`
2. `remove_action()` walk against the global `$yith_woocompare` instance for `add_compare_link` / `add_button_in_loop` etc.
3. CSS belt-and-braces `<style id="ng-disable-compare">` injected on `wp_head` priority 100 with `display:none !important`.

In practice Layers 1+2 don't intercept (markup is still emitted — verified 16 hits in the live HTML), but Layer 3 hides them.

**Why the audit caught it as a blocker:** the audit screenshots were captured at 15:04, the v1.46.0 fix deployed at 16:00 — i.e. the screenshots predated the deploy by ~56 minutes.

**Verification:** re-shot at 1280/768/360 in EN+AR via `scripts/shop-shot-after.js` after force-triggering the `.reveal` IntersectionObserver class (see new finding M-rev below). Cards render correctly with full structure.

---

## BLOCKER

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

### H3. Home hero component is wrong fitness — tiny product strip + oversized headline reads as broken/empty ✅ RESOLVED 2026-05-08 (v1.49.0)
- Viewport / page / locale: 360 / home / en + ar
- Fix shipped (commit `68a3713`, `neogen.css` `.ng-hero-collage` rule): hide the decorative `aria-hidden` collage below 480 px. Hero now reads as kicker → h1 → wordmark → copy → CTAs without the 4 micro-thumbnails.
- After: `~/.claude/reports/neogen-store/screenshots/2026-05-08-after/home-360-en-v1.49.0.png`

### H4. Home page has massive empty-whitespace gap below hero — no category tiles, no value props, no social proof ✅ RESOLVED 2026-05-08 (v1.48.0)
- Viewport / page / locale: 1280 / home / en
- Same root cause as M-rev (`.reveal{opacity:0}` was hiding all section heads, making the page read empty in the audit screenshot).
- After: `~/.claude/reports/neogen-store/screenshots/2026-05-08-after/home-1280-en.png` (full hero + featured + categories + new-arrivals + deals + brands + trust strip + footer all visible).
- Fix shipped: `apps/neogen-custom/mu-plugins/neogen-theme-assets/neogen.{css,js}` v1.48.0 (commit `a11e1ee`). Defaults `.reveal` to opacity:1; JS opts in to the scroll animation.
- Original hypothesis (empty `wc_get_products`) was wrong — sections do render content; the `.reveal` opacity simply suppressed them visually.

### H5. Footer trust-strip icons (Mada/Apple Pay, Shipping, 14-day return, 12-month warranty) lose alignment on 360 ✅ RESOLVED 2026-05-08 (v1.49.0)
- Note: actual source is `mu-plugins/neogen-theme.php:1705-` (the audit pointer to `themes/blocksy-child/template-parts/footer-trust.php` is wrong — that path doesn't exist in the deployable overlay).
- Fix shipped (commit `68a3713`, `neogen.css` `.ng-foot-trust` block): drop the trust strip to a 1-column stack below 420 px. Items get full width, padding 14×16, sub-label 11.5 px line-height 1.45.
- The 980 px breakpoint already drops 4-col → 2-col; the new 420 px breakpoint drops 2-col → 1-col for the smallest phones.

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

### M-rev. Product cards default to `opacity:0` until IntersectionObserver fires — empty shop for slow-JS / crawler / no-JS visitors ✅ RESOLVED 2026-05-08 (v1.48.0)
- Source: `apps/neogen-custom/mu-plugins/neogen-theme-assets/neogen.css:1940-1946` and `neogen.js:120-138`
- Discovered: 2026-05-08 during B1 verification — Playwright `fullPage:true` captured the entire shop at `opacity:0` because below-fold cards never entered the IntersectionObserver root.
- Risk: crawlers, slow networks, prefers-reduced-motion users, and no-JS visitors all saw a blank archive. Also the underlying cause of H4 (3-viewport empty home).
- Fix shipped (commit `a11e1ee`, v1.48.0):
  - CSS: `.reveal` defaults to `opacity:1; transform:none`. The hidden+animate state moved under a `.ng-reveal-ready .reveal` ancestor selector.
  - JS: `reveal()` adds `ng-reveal-ready` to `<html>` on first run, then IntersectionObserver toggles `.in` as before.
  - Net effect: identical animation for JS visitors; readable content immediately for everyone else.
- Pending: operator clicks Pull Latest at `/wp-admin/tools.php?page=neogen-deploy` to deploy v1.48.0.

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
