# LiteSpeed Cache — activation runbook (2026-05-08)

**Why this exists:** the 2026-05-08 readiness audit (`~/.claude/reports/neogen/readiness-2026-05-08.md`) flagged BLOCKER #2 — TTFB 2.5–2.7s with no cache headers on prod responses. Investigation showed:

- `litespeed-cache 7.8.1` plugin is **installed but inactive** on `/home/fsalmansour/neogen.store/`.
- Origin is LiteSpeed Web Server (header `server: LiteSpeed`), so LSCache plugin can talk to the server-level cache directly — no extra config on the host.
- Cloudflare DNS is grey-cloud (proxy off), so origin is exposed and there is no edge cache either.
- `Set-Cookie: ng_recent` was emitting on every product-page cold GET. The companion code commit (v1.42.0) defers that cookie to client-side JS so anonymous responses no longer carry `Set-Cookie` and become cache-eligible.

This runbook walks through the WP-admin clicks and the LSCache config knobs that actually matter for an anonymous-cache-by-default WooCommerce store. It does NOT enable Cloudflare orange-cloud — that's a separate runbook (DNS-side change).

## Prerequisites

- ✅ `apps/neogen-custom` deployed at v1.42.0 or later (companion `ng_recent` cookie defer landed; without it, product pages bypass cache anyway).
- WP-admin login as the merchant account.
- ~10 minutes of low-traffic time. After flipping cache on, every previously-cached anonymous response gets re-generated once, so expect a small 30–60s TTFB spike on the first cold visitor per URL.

## Step 1 — Activate the plugin

1. WP admin → **Plugins → Installed Plugins**.
2. Find **LiteSpeed Cache 7.8.1** in the inactive list.
3. Click **Activate**.

A new top-level menu item **LiteSpeed Cache** appears in the admin sidebar.

Verification: `ssh vps 'cd /home/fsalmansour/neogen.store && wp plugin list --status=active --format=csv | grep litespeed'` — should show `litespeed-cache,active`.

## Step 2 — Run the on-boarding wizard (or skip)

LSCache shows an onboarding modal on first activation. Either complete the wizard with the recommended defaults or click **Skip** — the runbook below covers every knob that matters.

If the wizard prompts for a **QUIC.cloud** account: skip for now. We don't need their CDN; LSCache works fine without it (and our backbone is the LiteSpeed origin anyway).

## Step 3 — Cache → General

WP admin → **LiteSpeed Cache → Cache → General**.

| Setting | Value | Why |
|---|---|---|
| Enable Cache | **ON** | The point of all of this. |
| Default Public Cache TTL | **3600** (1 h) | Long enough to amortise PHP renders, short enough to pick up product/price edits. WooCommerce purge hooks will invalidate stale entries on save anyway. |
| Default Private Cache TTL | **1800** (30 min) | For logged-in users (cart, my-account). |
| Default Front Page TTL | **3600** | Same as public. |
| Default Feed TTL | **3600** | Low traffic, fine to cache. |
| Default REST TTL | **300** | Short TTL — REST is used by JS callers expecting fresh data. |
| Default HTTP Status Code Page TTL | **3600 200, 60 404** (defaults) | Don't cache 5xx. |

Click **Save Changes**.

## Step 4 — Cache → Excludes (critical for Woo)

WP admin → **LiteSpeed Cache → Cache → Excludes**.

LSCache's defaults already exclude WooCommerce's cart / checkout / my-account / order-received / wishlist endpoints automatically. Verify the auto-detected exclusions are present:

- **Do Not Cache URIs** should include or auto-detect:
  - `/cart`
  - `/checkout`
  - `/my-account`
  - `/order-received`
  - `/wp-json/wc/`
  - `/wp-admin`

- **Do Not Cache Roles**: tick **Administrator**, **Shop Manager** (so you and the merchant always see fresh edits without manual purge).

- **Do Not Cache Cookies**: the audit recommended adding `ng_recent` here as a defence-in-depth, but with the v1.42.0 client-side cookie defer landed, the server response never sets `ng_recent`, so this exclusion isn't needed on the response side. **Leave the default list (which already excludes `woocommerce_cart_hash`, `woocommerce_items_in_cart`, `wp_woocommerce_session_*`).**

- **Do Not Cache Query Strings**: add `lang` here. The new `?lang=ar` toggle (mu-plugins/neogen-i18n.php in v1.41.0) needs to vary the `<html lang>` attribute. Treating `?lang=ar` as a separate cache entry is the correct behaviour — without this exclusion, the AR variant would pollute the EN cache or vice versa.

  Actually — **the better setup** is to keep `?lang=ar` cached but as a separate cache entry, not excluded. LSCache → Cache → Browser tab → **Cache Vary by Query String** → add `lang`. This caches both `?lang=ar` and the bare URL independently and serves each from cache.

Click **Save Changes**.

## Step 5 — Cache → Browser

WP admin → **LiteSpeed Cache → Cache → Browser**.

| Setting | Value |
|---|---|
| Browser Cache | **ON** |
| Browser Cache TTL | **31557600** (1 year) — for static assets only; LSCache attaches a content hash to file URLs so this never serves stale CSS/JS. |

## Step 6 — Cache → Object

WP admin → **LiteSpeed Cache → Cache → Object**.

- Object Cache: **ON** (uses Redis or Memcached if available; falls back to file-based).
- Method: **Memcached** if available, else **Redis**, else leave file-based default.
- Verification: `ssh vps 'cd /home/fsalmansour/neogen.store && wp redis status 2>&1 || wp eval "echo extension_loaded(\"memcached\") ? \"memcached_ext_loaded\" : \"no_memcached\";"'`. If neither is available, file-based is fine for this size of catalog.

## Step 7 — Cache → Mobile

WP admin → **LiteSpeed Cache → Cache → Mobile**.

| Setting | Value | Why |
|---|---|---|
| Cache Mobile | **ON** | KSA traffic is heavily mobile; serving mobile users from a separate cache prevents desktop layouts (e.g. the 6-up product grid) leaking into mobile responses. |
| List of Mobile User Agents | **leave the default** (the LSCache regex covers iPhone, Android, mobile, tablet). |

## Step 8 — Page Optimisation (light-touch only)

WP admin → **LiteSpeed Cache → Page Optimization**.

NeoGen already has 28× external `<script src>` and 55× inline `<script>` on the homepage (audit HIGH). LSCache's optimisation can defer/combine some of these but **also has a high regression rate** — minify-and-combine has broken Elementor, Tabby, and Checkout.com integrations on KSA stores in past incidents.

**Recommended minimal config** (low-risk wins only):

| Setting | Value | Notes |
|---|---|---|
| **CSS → CSS Minify** | OFF | Defer to a future commit; needs A/B verification per page |
| **CSS → CSS Combine** | OFF | Almost always breaks something on a Blocksy + Woo + Tabby stack |
| **CSS → Generate Critical CSS** | **ON** | Inlines above-the-fold CSS, defers the rest. ~200 ms LCP win typical. Test the homepage hero strip after enable. |
| **JS → JS Minify** | OFF | High regression risk |
| **JS → JS Combine** | OFF | High regression risk |
| **JS → JS Defer** | **ON, "Delayed"** | Defers third-party scripts (GTM/GA/Tabby/Checkout) until first user interaction. Big TBT win. Do test the Tabby payment widget after this — if broken, switch to "Deferred" (less aggressive). |
| **JS → Localize Resources** | OFF | Privacy upside, but breaks Cloudflare Turnstile and some Recaptcha flows |
| **HTML → HTML Minify** | **ON** | Safe; ~5–10 KB per page |
| **HTML → DNS Prefetch** | **ON** + add: `//www.googletagmanager.com`, `//www.google-analytics.com`, `//*.tabby.ai`, `//*.checkout.com`, `//*.stcpay.com.sa` | Saves ~100ms per first-paint per third-party host |
| **Media → Lazy Load Images** | **ON** (already enabled in theme) | Set viewport buffer to 200 |
| **Media → LQIP Cloud** | OFF | Requires QUIC.cloud account |

**After every Page Optimization toggle:** test the homepage, a product page, and the cart in a clean incognito window. Anything broken → toggle the offending knob OFF and report.

Click **Save Changes**.

## Step 9 — Toolbox → Purge

WP admin → **LiteSpeed Cache → Toolbox → Purge**.

- **Purge All – LSCache** once after the config above is saved. The next 5–10 anonymous visitors per URL will MISS, then HIT.

## Step 10 — Verify

```bash
# Cold visit — first request after Purge All. Expect MISS.
curl -sI 'https://neogen.store/' | grep -i 'x-litespeed-cache'
# Expect:  x-litespeed-cache: miss

# Immediate second visit. Expect HIT.
curl -sI 'https://neogen.store/' | grep -i 'x-litespeed-cache'
# Expect:  x-litespeed-cache: hit

# Product page — should HIT after the v1.42.0 cookie defer.
curl -sI 'https://neogen.store/product/home-assistant-green/' | grep -i 'x-litespeed-cache\|set-cookie'
# Expect:  x-litespeed-cache: hit
# Expect:  no set-cookie line (was set-cookie: ng_recent=...)

# AR variant cached separately.
curl -sI 'https://neogen.store/?lang=ar' | grep -i 'x-litespeed-cache'
# Expect:  x-litespeed-cache: hit (after second hit)

# TTFB — repeat the homepage curl 5 times.
for i in 1 2 3 4 5; do curl -s -o /dev/null -w '%{time_starttransfer}\n' 'https://neogen.store/'; done
# Expect after warmup: <0.5s on every request (was 2.5-2.7s)
```

## Rollback

If any of Step 8's optimisations break something:

- WP admin → **LiteSpeed Cache → Page Optimization → [tab] → toggle the bad knob OFF → Save Changes → Toolbox → Purge → Purge All**.
- If the entire cache layer needs to come down: **LiteSpeed Cache → Toolbox → [Disable Cache button]**, OR Plugins → LiteSpeed Cache → **Deactivate**. Both restore the pre-2026-05-08 baseline within seconds.

## What this runbook does NOT do

These are deliberately separate runbooks:

- **Cloudflare orange-cloud + CAA + IPv6** — DNS-side, separate session.
- **DMARC `p=quarantine` → `p=reject`** — separate runbook after `rua` monitoring window.
- **Automated host-level backup cadence** — separate (BLOCKER from the readiness audit, not this commit).
- **ZATCA Phase-2 verification** — needs invoice runtime probe.

## Verification checklist

- [ ] LSCache plugin shows `active` in `wp plugin list`.
- [ ] `curl -sI https://neogen.store/` returns `x-litespeed-cache: hit` on second hit.
- [ ] Homepage TTFB <0.5s after warmup.
- [ ] AR variant (`?lang=ar`) returns its own cache hit.
- [ ] Cart, checkout, my-account return `x-litespeed-cache: no-cache` (excluded — by design).
- [ ] Tabby and Checkout.com payment widgets render correctly on a product page (Step 8 regression check).
- [ ] One real test order goes through end-to-end via Mada / Apple Pay / STC Pay (the three KSA payment surfaces) without errors.

When all six boxes are green, BLOCKER #2 is closed.
