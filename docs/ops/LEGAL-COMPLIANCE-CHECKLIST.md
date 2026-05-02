# NeoGen Store — Legal & Technical Compliance Checklist
**Owner:** فهد سعد فهد المنصور  
**CR:** 7053130576 | **VAT:** 3145127947  
**Last Updated:** 2026-05-01

---

## 1. Commercial Registration & Identity ✅
| Item | Status | Action |
|------|--------|--------|
| CR 7053130576 | ✅ Active | Renew annually via Markaz |
| VAT 3145127947 | ✅ Registered (15%) | File quarterly returns via ZATCA |
| Chamber of Commerce 1238532 | ✅ Active | — |
| National address on site | ✅ Added to contact page | 8102 Al Khaboub, Al Malqa, Riyadh 13521 — RRMB8102 (correspondence only) |
| Owner name displayed | ✅ Shown | — |

**Action Required:** Add physical/national address to site footer immediately (Article 14 e-commerce law).

---

## 2. ZATCA E-Invoicing (Phase 2) ⚠️
| Item | Status | Action |
|------|--------|--------|
| Phase 1 (generation) | ✅ Done | — |
| Phase 2 (integration) | ⚠️ Pending | Integrate with ZATCA Fatoora API |
| QR Code on invoices | ❌ Missing | Required on all B2C invoices |
| 15% VAT shown on invoice | ⚠️ Verify | Check WooCommerce invoice plugin |

**Action Required:** Install WooCommerce PDF Invoices Pro with QR code support, connect to ZATCA Fatoora portal.

---

## 3. Maroof Platform ❌
| Item | Status | Action |
|------|--------|--------|
| Registered on Maroof | ❌ Not confirmed | Register at maroof.sa |
| Maroof badge on site | ❌ Missing | Add to header after registration |

**Action Required:** Register at `https://maroof.sa` using CR number, then embed trust badge in site header. This is the #1 trust signal for Saudi consumers.

---

## 4. Page Availability ✅ (Fixed 2026-05-01)
| Page | URL | Status |
|------|-----|--------|
| Privacy Policy | /privacy-policy/ | ✅ 200 |
| Terms & Conditions | /terms/ | ✅ 200 |
| Refund Policy | /refund-policy/ | ✅ 200 |
| Contact Us | /contact-us/ | ✅ 200 |
| Cart | /cart/ | ✅ 200 |
| Checkout | /checkout/ | ✅ 200 |
| My Account | /my-account/ | ✅ 200 |

---

## 5. Product Pricing ✅ (Fixed 2026-05-01)
- PS Plus 1M US (ID 567): 38 SAR ✅
- PS Plus 3M US (ID 568): 94 SAR ✅
- PS Plus 12M US (ID 569): 226 SAR ✅
- PS Plus 1M KSA (ID 570): 29 SAR ✅
- PS Plus 3M KSA (ID 571): 74 SAR ✅
- PS Plus 12M KSA (ID 572): 249 SAR ✅

**Ongoing:** Set up price alert — no product should be published with price = 0.

---

## 6. SSL & Security ✅
| Item | Status | Expiry |
|------|--------|--------|
| SSL Certificate (Sectigo) | ✅ Valid | Jul 16 2026 — renew by Jun 2026 |
| HSTS | ✅ Enabled | — |
| X-Frame-Options | ✅ SAMEORIGIN | — |
| X-Content-Type | ✅ nosniff | — |
| CSP | ⚠️ Report-Only | Change to enforced mode |

**Action Required:** Change `content-security-policy-report-only` to `content-security-policy` in server config.  
**Reminder:** Renew SSL by June 2026.

---

## 7. Brand Identity — Neogen Name Conflict ⚠️
Entities with similar names causing SEO/trust confusion:
- **Neogen Lab** (Korea) — skincare brand, registered globally
- **NeoGen Plasma** — aesthetic clinics, fraud complaints online
- **Beelive/Neogen** — mall sales, FDA seal complaints

**Actions Required:**
- Add clear statement on homepage: "نيوجين ستور — متجر تقني سعودي متخصص في الشبكات والمنزل الذكي. لا علاقة له بأي علامة تجارية أخرى تحمل اسماً مشابهاً."
- Strengthen branded SEO: use "نيوجين ستور" + "الرياض" + "تقنية" consistently
- Consider trademark registration for "NeoGen Store" with SAIP (هيئة الملكية الفكرية)

---

## 8. E-Commerce Law Compliance (نظام التجارة الإلكترونية)
| Article | Requirement | Status | Action |
|---------|------------|--------|--------|
| Art. 6 | Display CR, VAT, owner name | ✅ Done | — |
| Art. 14 | Physical address | ❌ Missing | Add national address |
| Art. 13 | Return policy (14 days) | ✅ In Terms | Verify page accessible |
| Art. 13 | Digital product exception stated | ✅ Added to Terms | — |
| Art. 15 | Privacy policy available | ✅ Fixed | — |
| Art. 7 | Electronic contract clarity | ✅ Wa'd clause added | — |
| PDPL | Data breach notification (3 days) | ⚠️ No process | Define internal breach response |
| PDPL | Cookie consent with accessible policy | ⚠️ Partial | Verify cookie banner links work |

---

## 9. Customer Reviews Monitoring
Monitor these channels weekly:
- Google Maps: search "neogen store riyadh"
- Twitter/X: @NeoGenStore mentions
- Complaint portals: `https://mc.gov.sa` (وزارة التجارة)
- Maroof reviews (after registration)

---

## 10. Recommended Plugin Purchases
| Plugin | Purpose | Priority | Cost |
|--------|---------|----------|------|
| YITH WooCommerce Digital Delivery | Auto-deliver gift card codes by email | 🔴 High | €99/yr |
| WooCommerce PDF Invoices Pro | Arabic invoices with QR code (ZATCA) | 🔴 High | €59/yr |
| WooCommerce Subscriptions | Recurring billing for PS Plus/Spotify | 🟡 Medium | $199/yr |
| Rank Math Pro | SEO schema fix, local SEO Riyadh | 🟡 Medium | $59/yr |
| WP Rocket | Performance/Core Web Vitals | 🟡 Medium | $59/yr |

---

---

## NovaKeys.store — New Domain Compliance

**Domain registered and WordPress live as of 2026-05-02.**

| Item | Status | Action |
|------|--------|--------|
| Domain: novakeys.store | ✅ Registered | Active |
| WordPress 6.9.4 | ✅ Installed | VPS: 162.254.39.146, Dir: ~/novakeys.store/ |
| WooCommerce 10.7.0 | ✅ Active | SAR currency, 15% VAT, Saudi Arabia |
| Permalinks | ✅ /%postname%/ | Set |
| Git repo (VPS) | ✅ Init | ~/novakeys.store/.git (tracks wp-config + wp-content) |
| Git repo (local) | ✅ Init | Google Drive: novakeys.store/ (React prototype) |
| CR 7053130576 on site | ❌ Pending | Add to footer and About page |
| VAT 3145127947 on site | ❌ Pending | Add to invoice/checkout |
| Maroof registration | ❌ Pending | Register at maroof.sa — different listing from neogen.store |
| Privacy Policy page | ❌ Pending | Create at /privacy-policy/ |
| Terms page | ❌ Pending | Use docs/ops/terms-and-conditions.html content |
| Refund Policy page | ❌ Pending | Create at /refund-policy/ |
| Contact Us page | ❌ Pending | Create at /contact/ |
| National address in footer | ❌ Pending | 8102 Al Khaboub, Al Malqa, Riyadh 13521 |
| SSL certificate | ⚠️ Verify | Check cPanel > SSL/TLS for novakeys.store |
| WooCommerce API keys | ❌ Pending | Create in WP admin for n8n integration |

**Next steps for novakeys.store launch:** see `docs/ops/novakeys-migration-checklist.md`

---

## Summary Action List (By Priority)
1. 🔴 Register on **Maroof** platform
2. 🔴 Add **national address** to site footer
3. 🔴 Install **YITH Digital Delivery** — stop manual WhatsApp delivery
4. 🔴 Install **PDF Invoices Pro** with ZATCA QR code
5. 🟡 Enforce **CSP header** (remove report-only)
6. 🟡 Renew **SSL by June 2026**
7. 🟡 Add brand **disclaimer** on homepage about Neogen name
8. 🟡 Consider **trademark registration** with SAIP
9. 🟢 Define internal **data breach response** process
10. 🟢 Set up **price monitoring alert** for zero-price products
