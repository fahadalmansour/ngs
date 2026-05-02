# NovaKeys.store — Launch Checklist

Status as of 2026-05-02: WordPress installed via Softaculous, domain live.

---

## Phase 1 — Infrastructure (DONE)

- [x] Register novakeys.store domain
- [x] WordPress 6.9.4 installed via Softaculous on cPanel (162.254.39.146)
- [x] DB: `fsalmansour_wp63`, prefix: `wpfj_`
- [x] Site URL set to `https://novakeys.store`
- [x] Site title: "NovaKeys Store", tagline set in Arabic
- [x] WooCommerce installed and activated
- [x] Permalinks set to `/%postname%/`
- [x] .htaccess created

---

## Phase 2 — DNS & SSL

- [ ] Point novakeys.store A record → 31.220.42.110 (blazr.net VPS — same as neogen.store)
  - OR confirm current hosting IP for novakeys.store
- [ ] Verify SSL certificate is active (Let's Encrypt via cPanel or Sectigo)
- [ ] Test: `curl -I https://novakeys.store` → should return 200 with HTTPS
- [ ] Set up www redirect: `www.novakeys.store` → `novakeys.store`

---

## Phase 3 — WooCommerce Setup

- [ ] Run WooCommerce setup wizard (admin > WooCommerce > Setup Wizard)
- [ ] Set store country: Saudi Arabia
- [ ] Set currency: Saudi Riyal (SAR)
- [ ] Enable payment gateways:
  - [ ] Tabby (BNPL)
  - [ ] Tamara (BNPL)
  - [ ] STC Pay
  - [ ] Moyasar (Mada/Visa gateway — same as neogen.store)
- [ ] Configure tax: 15% VAT (ZATCA compliant)
- [ ] Set up WooCommerce REST API keys (for n8n integration)

---

## Phase 4 — Product Import

- [ ] Export products from neogen.store:
  ```bash
  wp --allow-root export --dir=/tmp/export --post_type=product
  ```
- [ ] Import into novakeys.store:
  ```bash
  wp --allow-root import /tmp/export/file.xml --authors=create
  ```
- [ ] Alternatively: use WooCommerce CSV importer for products only
- [ ] Verify all digital products have correct prices (no zero-price items)
- [ ] Set product type to "Simple" with downloadable/virtual checkbox for digital goods

---

## Phase 5 — Theme & Design

- [ ] Install Electro theme (same as neogen.store) OR deploy NovaKeys React frontend
- [ ] If using WordPress: import Electro demo content and customize
- [ ] Upload NovaKeys logo (SVG preferred)
- [ ] Set up homepage with category grid: PS, Xbox, Steam, Apple, Google Play, Games, Software
- [ ] Configure header: logo, search, cart, account links
- [ ] Configure footer: CR, VAT, links (Terms, Privacy, Refund, Contact)

---

## Phase 6 — Legal Pages

- [ ] Create Privacy Policy page (slug: `/privacy-policy/`)
- [ ] Create Terms & Conditions page (slug: `/terms/`) — use `docs/ops/terms-and-conditions.html`
- [ ] Create Refund Policy page (slug: `/refund-policy/`)
- [ ] Create Contact Us page (slug: `/contact/`)
- [ ] Add national address to footer: 8102 Al Khaboub, Al Malqa, Riyadh 13521
- [ ] Register on Maroof.sa: https://maroof.sa (link account to novakeys.store)

---

## Phase 7 — n8n Integration

- [ ] Update W4–W13 workflows: add novakeys.store WooCommerce API keys as secondary
- [ ] Update W-BOT-CATALOG: add novakeys.store endpoint alongside neogen.store
- [ ] Register Telegram bot webhook:
  ```
  POST https://api.telegram.org/bot8768777836:AAF_2g_r8FbDll_B9m_h4gqkp2C7GcoXqsg/setWebhook
  Body: {"url": "https://n8n.yourdomain.com/webhook/telegram-bot"}
  ```
- [ ] Test: send "ps plus" to bot → should return products from novakeys.store

---

## Phase 8 — SEO & Analytics

- [ ] Install Rank Math SEO plugin
- [ ] Submit sitemap to Google Search Console: `https://novakeys.store/sitemap_index.xml`
- [ ] Set up Google Analytics 4 (GA4) — add tracking ID to site
- [ ] Set up Google Merchant Center account for Shopping ads (digital goods)
- [ ] Create `robots.txt` and `llms.txt`
- [ ] Add meta tags: Open Graph, Twitter Card

---

## Phase 9 — Pre-Launch Testing

- [ ] Test full checkout flow: add to cart → checkout → payment → receive code
- [ ] Test on mobile (iOS Safari + Chrome)
- [ ] Test Arabic RTL layout
- [ ] Test all legal page links load correctly
- [ ] Run GTmetrix or PageSpeed Insights (target score >80)
- [ ] Check SSL rating: ssllabs.com/ssltest → target A

---

## Phase 10 — Launch

- [ ] Announce on social media (Twitter/X, Instagram)
- [ ] Send email to existing neogen.store customers (if permission)
- [ ] Set up Uptime Kuma monitoring for novakeys.store
- [ ] Enable WordPress automatic updates for minor versions
- [ ] Schedule first backup (cPanel daily backup)

---

## Key Credentials

| Item | Value |
|------|-------|
| VPS SSH | `ssh -p 21098 fsalmansour@162.254.39.146` |
| WP Admin | https://novakeys.store/wp-admin/ |
| DB name | `fsalmansour_wp63` |
| DB user | `fsalmansour_wp63` |
| Table prefix | `wpfj_` |
| Telegram Bot | @NeoGenstoreAlertsBot |
| Channel | -1003973109225 |
