#!/usr/bin/env python3
"""
apply-marketing-copy.py — apply the 4 launch copy packs to NeoGen Store via WP-CLI.

Updates 4 product categories (term descriptions + Rank Math meta + AR meta)
and 19 hero products (post content + AR title meta + AR description meta +
Rank Math meta).

Idempotent: safe to re-run. Only writes if the value differs from what's
already there (per --skip-unchanged).

Run on the VPS:
    cd /var/www/ngs1                # or your WP install root
    scp this script + nothing else (content is inlined)
    python3 apply-marketing-copy.py --dry-run    # preview
    python3 apply-marketing-copy.py               # apply

Requirements:
    - wp-cli in PATH (`wp --info`)
    - python3.6+ (uses f-strings)
    - Active theme/plugin uses Rank Math (or change RM_KEYS below for Yoast)
    - Theme reads `_ng_ar_title` / `_ng_ar_description` post meta for AR copy
      (verify before applying — fall back to embedded AR in the EN body if not)

Repo source: see docs/feasibility/2026-05-07-{smart-home,networking,homelab,security}-launch-copy.md
"""

import subprocess
import sys
import argparse
import shlex

# ----------------------------------------------------------------------------
# Configuration
# ----------------------------------------------------------------------------

WP_BIN = "wp"

# Rank Math meta keys (post + term). Change to Yoast (_yoast_wpseo_title /
# _yoast_wpseo_metadesc) if the site uses Yoast instead.
RM_TITLE_KEY = "rank_math_title"
RM_DESC_KEY  = "rank_math_description"

# Theme-specific AR meta keys (per the marketing-neogen skill).
AR_TITLE_KEY = "_ng_ar_title"
AR_DESC_KEY  = "_ng_ar_description"


# ----------------------------------------------------------------------------
# Content — categories
# ----------------------------------------------------------------------------

CATEGORIES = {
    "smart-home": {
        "en_html": (
            "<p><strong>The smart home, considered.</strong></p>"
            "<p>Our Smart Home &amp; IoT shelf is small on purpose. Every product here is one we'd run in our own homes — UniFi Protect cameras, Home Assistant Green, Aqara locks and sensors, Sonoff switches. Real brands, sourced direct, set up to work together. Local delivery in 2–5 working days from Riyadh.</p>"
            "<ul>"
            "<li><strong>Curated, not catalogued.</strong> We don't list everything. We list the products we'd recommend to a friend setting up their first smart home.</li>"
            "<li><strong>Quality you'd buy yourself.</strong> Each model is sourced from the brand's authorised supply chain. No clones, no grey-market surprises.</li>"
            "<li><strong>Local hands-on support.</strong> Bilingual EN / AR setup help by WhatsApp. Most issues solved in one message.</li>"
            "</ul>"
        ),
        "ar_html": (
            "<p dir=\"rtl\"><strong>بيت ذكي — باختيار محسوب.</strong></p>"
            "<p dir=\"rtl\">رفّ البيوت الذكية في نيوجن صغير عن قصد. كل منتج هنا قابلناه واختبرناه: كاميرات UniFi Protect، مركز Home Assistant Green، أقفال وحسّاسات Aqara، مفاتيح Sonoff. علامات حقيقية، توصيل مباشر من الرياض خلال 2-5 أيام عمل.</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>مختار، مش مكدّس.</strong> ما نضيف كل منتج بالسوق. نضيف اللي نستخدمه بنفسنا أو نوصي فيه بيت أحد.</li>"
            "<li><strong>جودة تشتريها لأهلك.</strong> كل قطعة من سلسلة التوريد الأصلية للعلامة. لا تقليد، لا مفاجآت رمادية.</li>"
            "<li><strong>دعم محلي يفهمك.</strong> تواصل واتساب بالعربي والإنجليزي. أغلب الاستفسارات نحلها برسالة وحدة.</li>"
            "</ul>"
        ),
        "rm_title": "Smart Home & IoT — NeoGen Store",
        "rm_desc":  "Curated smart-home gear: UniFi Protect, Home Assistant, Aqara, Sonoff. Genuine sourcing, Riyadh local delivery in 2–5 days. SAR, VAT included.",
    },
    "networking": {
        "en_html": (
            "<p><strong>The network is the part you only think about when it doesn't work.</strong></p>"
            "<p>Build it once, build it right. Our shelf is a small, opinionated set of UniFi, MikroTik, and TP-Link gear we'd run in our own offices and houses — flagship 10G switches down to a single Cat6a run for the room you didn't wire when the villa was built. Local delivery from Riyadh in 2–5 working days; setup help on WhatsApp the day it arrives.</p>"
            "<ul>"
            "<li><strong>Authorised supply chain.</strong> Every UniFi, MikroTik, and TP-Link unit comes through brand-authorised distributors. Genuine warranty, genuine firmware.</li>"
            "<li><strong>Built for KSA homes and offices.</strong> Voltage-checked, locale-checked, ready for the cable runs and Wi-Fi roaming patterns of Saudi villas and small offices.</li>"
            "<li><strong>Set up like the install was your own.</strong> Real configuration help over WhatsApp — VLANs, SSIDs, port forwarding, PoE budgets.</li>"
            "</ul>"
        ),
        "ar_html": (
            "<p dir=\"rtl\"><strong>الشبكة — تحس فيها بس لمّا تتعطّل.</strong></p>"
            "<p dir=\"rtl\">ابنِها مرة وابنِها صح. رفّ الشبكات عندنا مختار باجتهاد: قطع UniFi و MikroTik و TP-Link اللي نشغّلها بمكاتبنا وبيوتنا — من سويتش 10G الرئيسي إلى وصلة Cat6a لغرفة وحدة فاتتك وقت بناء الفلّة. توصيل من الرياض خلال 2-5 أيام عمل؛ ودعم تركيب بالواتساب يوم ما يوصل المنتج.</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>سلسلة توريد معتمدة.</strong> كل قطعة UniFi و MikroTik و TP-Link من موزّعين معتمدين من الشركة. ضمان حقيقي، فيرموير حقيقي.</li>"
            "<li><strong>مفصّلة للبيوت والمكاتب السعودية.</strong> جهد كهربائي محقّق، إعدادات إقليمية محقّقة، جاهزة لتمديدات الكوابل وأنماط تنقّل الواي فاي.</li>"
            "<li><strong>دعم تركيب كأنه بيتك.</strong> مساعدة فعلية بالإعداد عبر واتساب — VLAN، SSID، توجيه منافذ، ميزانية PoE.</li>"
            "</ul>"
        ),
        "rm_title": "Networking & Wi-Fi — NeoGen Store",
        "rm_desc":  "Curated UniFi, MikroTik, TP-Link gear: switches, access points, routers, fiber. Authorised supply chain. Riyadh delivery 2–5 days. SAR, VAT incl.",
    },
    "homelab": {
        "en_html": (
            "<p><strong>The rack at the end of your hallway.</strong></p>"
            "<p>A homelab is the smartest tech purchase you make: every workload you run on someone else's cloud, you can run on a 25 W mini-PC under your desk, with the same uptime and zero monthly bill. Our shelf is the gear we'd put in our own rack — Beelink and MinisForum mini-PCs, Dell and HP refurbs you'd be embarrassed to throw out, Synology and QNAP NAS, Netgate pfSense, Ubiquiti UDM. Local delivery from Riyadh; setup help over WhatsApp the first weekend.</p>"
            "<ul>"
            "<li><strong>Right-sized for the lab.</strong> Every product here has a working Proxmox / TrueNAS / pfSense / Home Assistant install path, documented and tested.</li>"
            "<li><strong>Refurbs we'd run ourselves.</strong> Refurb stock is enterprise-grade hardware, fully tested, with a real warranty. Authorised refurb partners — not eBay roulette.</li>"
            "<li><strong>220V, summer-ready.</strong> Every PSU and UPS we stock is rated for KSA 220V mains and verified to behave correctly during the brownouts that hit during 47 °C peaks.</li>"
            "</ul>"
        ),
        "ar_html": (
            "<p dir=\"rtl\"><strong>الراك في طرف الممرّ.</strong></p>"
            "<p dir=\"rtl\">الهوم لاب أذكى صفقة تقنية تسويها: كل خدمة تشغّلها على سحابة شخص ثاني، تقدر تشغّلها على ميني-بي-سي بـ 25 واط تحت طاولتك، بنفس الجاهزية وبدون فاتورة شهرية. الرفّ عندنا هو نفس العتاد اللي نشغّله بمختبراتنا — مايكرو بي سي من Beelink و MinisForum، أجهزة Dell و HP مجدّدة، NAS من Synology و QNAP، Netgate pfSense، Ubiquiti UDM. توصيل من الرياض؛ ودعم تركيب بالواتساب أول إجازة.</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>بمقاس مختبرك.</strong> ما نضيف عتاد مجهول. كل قطعة فيها مسار تركيب موثّق ومختبر لـ Proxmox أو TrueNAS أو pfSense أو Home Assistant.</li>"
            "<li><strong>مجدّد نشغّله بأنفسنا.</strong> المجدّد عندنا عتاد مؤسسات، مفحوص بالكامل، بضمان حقيقي. شركاء تجديد معتمدين — مو روليت eBay.</li>"
            "<li><strong>220 فولت، جاهز للصيف.</strong> كل مزوّد طاقة و UPS عندنا مقاس على جهد المملكة 220 فولت ومتحقّق من سلوكه عند انخفاض الجهد لمّا الحرارة تطلع 47 درجة.</li>"
            "</ul>"
        ),
        "rm_title": "Homelab — NeoGen Store",
        "rm_desc":  "Curated homelab gear: mini-PCs, refurb workstations, NAS, pfSense, UDM, rack accessories. KSA 220V-verified. Riyadh delivery. SAR, VAT incl.",
    },
    "security-surveillance": {
        "en_html": (
            "<p><strong>Eyes that don't ask for a subscription.</strong></p>"
            "<p>Cameras you can review locally, doorbells that ring without a cloud account, alarms with their own wireless backbone. Our shelf is the gear we'd put on our own front door — UniFi Protect, Reolink, Eufy, Hikvision, Ajax — chosen so you can keep the recordings on your own NAS or NVR if you want. Local delivery from Riyadh; setup help over WhatsApp the day it arrives.</p>"
            "<ul>"
            "<li><strong>Local recording, your data.</strong> Every camera and NVR we stock can record to local storage — your NAS, an SD card, or an on-prem NVR. PDPL-friendly.</li>"
            "<li><strong>Genuine supply chain.</strong> UniFi from authorised distributors, Reolink and Eufy direct from brand, Ajax from regional MEA distributor. Real warranty, real firmware.</li>"
            "<li><strong>Set up like the install was your own.</strong> Camera angles, PoE budget, NVR storage sizing, Ajax zone planning over WhatsApp. Most installs are one weekend, not three.</li>"
            "</ul>"
        ),
        "ar_html": (
            "<p dir=\"rtl\"><strong>عيون ما تطلب اشتراك.</strong></p>"
            "<p dir=\"rtl\">كاميرات تراجع تسجيلاتها محلياً، أجراس بدون حساب سحابي، أنظمة إنذار بشبكتها اللاسلكية المستقلة. الرفّ عندنا هو نفس العتاد اللي نحطّه على بيوتنا — UniFi Protect و Reolink و Eufy و Hikvision و Ajax — مختار بحيث تقدر تحتفظ بالتسجيلات على NAS أو NVR عندك. توصيل من الرياض؛ ودعم تركيب بالواتساب يوم ما يوصل المنتج.</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>تسجيل محلي، بياناتك معك.</strong> كل كاميرا و NVR عندنا تقدر تسجّل محلياً. متوافقة مع PDPL.</li>"
            "<li><strong>سلسلة توريد معتمدة.</strong> UniFi من موزّعي يوبيكويتي المعتمدين، Reolink و Eufy مباشرة من العلامة، Ajax من موزّع منطقة الشرق الأوسط.</li>"
            "<li><strong>تركيب كأنه بيتك.</strong> نمشّيك بزوايا الكاميرات، ميزانية PoE، حجم تخزين NVR، تخطيط مناطق Ajax — عبر واتساب.</li>"
            "</ul>"
        ),
        "rm_title": "Security & Surveillance — NeoGen Store",
        "rm_desc":  "Curated cameras, doorbells, NVRs, and alarms: UniFi Protect, Reolink, Eufy, Hikvision, Ajax. Local recording, no subscription. Riyadh delivery.",
    },
}


# ----------------------------------------------------------------------------
# Content — products
# Each entry: en_html (post_content), ar_title, ar_html, rm_title, rm_desc
# ----------------------------------------------------------------------------

PRODUCTS = {
    # ===== Smart Home =====
    "SH-HUB-HASS-001": {
        "en_html": (
            "<p><strong>Home Assistant Green — your smart home, on your own terms.</strong></p>"
            "<p>The official hardware from the team behind the world's most-loved open smart-home platform. Plug it into power and Ethernet, scan the QR, and the Home Assistant dashboard is live on your phone in under five minutes.</p>"
            "<p>Why we like it:</p>"
            "<ul>"
            "<li><strong>Local-first.</strong> Your routines, your sensors, your data — all stay on the device. No cloud account required, no monthly fee.</li>"
            "<li><strong>Works with everything.</strong> Native support for Zigbee (with the Skyconnect dongle), Matter, Apple HomeKit, Alexa, Google Home, and 2,000+ integrations.</li>"
            "<li><strong>Open.</strong> Grow into it as your home grows. Same hardware running 8 sensors today can run 80 in two years.</li>"
            "</ul>"
            "<p>Boxed and shipped from Riyadh in 2 working days. 12-month warranty. Bilingual setup support over WhatsApp on the first day for free.</p>"
        ),
        "ar_title": "هوم أسستنت جرين — مركز التحكم الذكي لبيتك",
        "ar_html": (
            "<p dir=\"rtl\"><strong>هوم أسستنت جرين — بيتك الذكي بطريقتك.</strong></p>"
            "<p dir=\"rtl\">الجهاز الرسمي من فريق Home Assistant — أكثر منصّة بيت ذكي مفتوحة المصدر استخداماً في العالم. أبسط طريقة تبدأ فيها: وصّل الكهرباء والإنترنت، امسح رمز QR، وستجد لوحة التحكم على جوالك خلال أقل من خمس دقائق.</p>"
            "<p dir=\"rtl\">ليش نوصي فيه:</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>محلي بالكامل.</strong> كل أوتومايشن وكل بيانات الحسّاسات تبقى عندك على الجهاز. بدون حساب سحابي، بدون اشتراك شهري.</li>"
            "<li><strong>يشتغل مع كل شي.</strong> Zigbee و Matter و Apple HomeKit و Alexa و Google Home وأكثر من 2,000 تكامل جاهز.</li>"
            "<li><strong>مفتوح ومرن.</strong> يكبر مع بيتك. نفس الجهاز اللي يدير 8 حسّاسات اليوم يقدر يدير 80 بعد سنتين.</li>"
            "</ul>"
            "<p dir=\"rtl\">توصيل من الرياض خلال يومين عمل. ضمان 12 شهر. دعم تركيب بالعربي عبر واتساب أول يوم مجاناً.</p>"
        ),
        "rm_title": "Home Assistant Green Hub — NeoGen Store",
        "rm_desc":  "The official Home Assistant Green smart-home hub. Local-first, no cloud lock-in, 2,000+ integrations. Riyadh delivery in 2 days.",
    },
    "NG-SH-001": {
        "en_html": (
            "<p><strong>Aqara A100 — the lock that keeps up with your iPhone.</strong></p>"
            "<p>The first Aqara smart lock with native Apple HomeKey support. Tap your iPhone or Apple Watch — same gesture as your hotel keycard, except it's your front door. Five backup unlock methods (fingerprint, PIN, NFC card, mechanical key, app) so a dead phone or guest is never a problem.</p>"
            "<p>Why we like it:</p>"
            "<ul>"
            "<li><strong>HomeKey first.</strong> Add the lock to Apple Wallet once; your iPhone unlocks the door even when its battery is dead (Apple's reserve power keeps it tappable).</li>"
            "<li><strong>Built for the family.</strong> Up to 100 fingerprints, 50 PINs, time-limited guest access. Every event tagged in the Aqara app.</li>"
            "<li><strong>Standard installation.</strong> Fits doors with cylinder lengths from 60 mm to 110 mm. 8 AA batteries last about a year of normal use.</li>"
            "</ul>"
            "<p>Boxed and shipped from Riyadh in 2 working days. 12-month warranty. WhatsApp setup help available — most installations under 30 minutes with a screwdriver.</p>"
        ),
        "ar_title": "آكارا A100 — قفل باب ذكي مع دعم Apple HomeKey",
        "ar_html": (
            "<p dir=\"rtl\"><strong>آكارا A100 — القفل اللي يمشي مع آيفونك.</strong></p>"
            "<p dir=\"rtl\">أول قفل ذكي من Aqara بدعم Apple HomeKey الأصلي. افتح الباب بلمسة من الآيفون أو Apple Watch — نفس حركة بطاقة الفندق، لكن بابك الرئيسي. خمس طرق فتح احتياطية (بصمة، رمز PIN، بطاقة NFC، مفتاح ميكانيكي، تطبيق) عشان لا يوقفك جوال فاضي أو ضيف.</p>"
            "<p dir=\"rtl\">ليش نوصي فيه:</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>HomeKey بالأساس.</strong> تضيف القفل لـ Apple Wallet مرة وحدة، والآيفون يفتح الباب حتى لو كانت البطارية ميتة.</li>"
            "<li><strong>مفصّل للعائلة.</strong> يقبل لين 100 بصمة، 50 رمز PIN، صلاحية ضيوف بمواعيد محددة.</li>"
            "<li><strong>تركيب قياسي.</strong> يناسب أبواب باسطوانة قفل من 60 ملم إلى 110 ملم. ثمان بطاريات AA تكفي حوالي سنة.</li>"
            "</ul>"
            "<p dir=\"rtl\">توصيل من الرياض خلال يومين عمل. ضمان 12 شهر. دعم تركيب عبر واتساب — أغلب التركيبات تخلص بأقل من 30 دقيقة بمفكّ.</p>"
        ),
        "rm_title": "Aqara Smart Door Lock A100 (HomeKey) — NeoGen Store",
        "rm_desc":  "Aqara A100 — the smart lock with native Apple HomeKey support. Five backup unlock methods, family-ready. Shipped from Riyadh, 12-month warranty.",
    },
    "NG-SH-002": {
        "en_html": (
            "<p><strong>UniFi Protect G4 Pro — the camera that doesn't ask for a subscription.</strong></p>"
            "<p>Ubiquiti's flagship outdoor camera. 4K resolution, 24/7 IR night vision out to 25 metres, and Smart Detections that learn the difference between a person, a car, a delivery, and your cat. Pairs with any UniFi controller (UDM, UNVR, or Cloud Key) for unlimited local recording — no monthly cloud fee, no per-camera SaaS lock-in.</p>"
            "<p>Why we like it:</p>"
            "<ul>"
            "<li><strong>Pro-grade build.</strong> IP65-rated, vandal-resistant housing, integrated heater for cold mornings. Designed to live outside.</li>"
            "<li><strong>Smart Detections.</strong> Person, vehicle, package, animal — each tagged in the timeline. Tags run on the controller, not in the cloud.</li>"
            "<li><strong>PoE-powered.</strong> One Ethernet cable carries data and power. Mounts in 15 minutes if you've already run the cable.</li>"
            "</ul>"
            "<p>Boxed and shipped from Riyadh in 2–5 working days. 12-month warranty. Pairs with the UniFi Cloud Gateway Ultra and UDM Pro we also stock — bundle setup support is on us.</p>"
        ),
        "ar_title": "يونيفاي G4 Pro — كاميرا مراقبة 4K بدون اشتراك",
        "ar_html": (
            "<p dir=\"rtl\"><strong>يونيفاي G4 Pro — كاميرا احترافية ما تطلب اشتراك.</strong></p>"
            "<p dir=\"rtl\">كاميرا يوبيكويتي الرئيسية للاستخدام الخارجي. دقة 4K، رؤية ليلية بالأشعة لمدى 25 متر، وتمييز ذكي يفرّق بين شخص وسيارة وتوصيل وحتى قطّك. تنسجم مع أي وحدة تحكم UniFi لتسجيل محلي بلا حدود — بدون اشتراك سحابي.</p>"
            "<p dir=\"rtl\">ليش نوصي فيها:</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>بناء احترافي.</strong> درجة حماية IP65، هيكل مقاوم للكسر، تسخين داخلي. مصمّمة تعيش بره.</li>"
            "<li><strong>تمييز ذكي.</strong> شخص، سيارة، طرد، حيوان — كل نوع موسوم بالخط الزمني. التمييز يشتغل محلياً.</li>"
            "<li><strong>تشتغل بكابل Ethernet واحد.</strong> PoE — البيانات والكهرباء بنفس الكابل.</li>"
            "</ul>"
            "<p dir=\"rtl\">توصيل من الرياض خلال 2-5 أيام عمل. ضمان 12 شهر. تنسجم مع UniFi Cloud Gateway Ultra و UDM Pro المتوفرة عندنا.</p>"
        ),
        "rm_title": "UniFi Protect G4 Pro 4K Camera — NeoGen Store",
        "rm_desc":  "UniFi Protect G4 Pro — pro-grade 4K outdoor surveillance, Smart Detections, no monthly cloud fee. Pairs with UDM Pro. Riyadh delivery, 12-mo warranty.",
    },
    "NG-SH-005": {
        "en_html": (
            "<p><strong>Sonoff T5 Ultimate — the wall switch that turned into a feature.</strong></p>"
            "<p>Sonoff's flagship wall switch, redone for 2026. Capacitive touch glass with an RGB ambient strip that doubles as a status indicator. Native Matter support means it pairs with Apple Home, Google Home, SmartThings, and Home Assistant out of the box — no Sonoff app required if you don't want one.</p>"
            "<ul>"
            "<li><strong>Direct replacement.</strong> Drops into a standard 86 mm KSA wall box. Live + neutral required.</li>"
            "<li><strong>Gestures and scenes.</strong> Long-press, double-tap, swipe — each maps to a different scene without rewiring anything.</li>"
            "<li><strong>Looks expensive, isn't.</strong> The glass face matches every interior we've seen. Black or white finish.</li>"
            "</ul>"
            "<p>Boxed and shipped from Riyadh in 2 working days. 12-month warranty. <strong>Launch price: 269 SAR</strong> (regular 309 SAR).</p>"
        ),
        "ar_title": "سونوف TX Ultimate (T5) — مفتاح حائط ذكي مع إضاءة RGB",
        "ar_html": (
            "<p dir=\"rtl\"><strong>سونوف T5 Ultimate — مفتاح الحائط اللي صار ميّزة.</strong></p>"
            "<p dir=\"rtl\">أحدث مفتاح من Sonoff لسنة 2026. زجاج لمسي مع شريط RGB يشتغل كمؤشّر حالة. دعم Matter مباشرة، يربط مع Apple Home و Google Home و SmartThings و Home Assistant من الكرتون.</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>استبدال مباشر.</strong> يدخل علبة الحائط القياسية 86 ملم في السعودية. يحتاج كهرباء حية ومحايد.</li>"
            "<li><strong>حركات ومشاهد.</strong> ضغط مطوّل، نقرتين، سحب — كل واحدة تربطها بمشهد مختلف.</li>"
            "<li><strong>شكل غالي بدون السعر الغالي.</strong> الواجهة الزجاجية تناسب كل ديكور. أسود أو أبيض.</li>"
            "</ul>"
            "<p dir=\"rtl\">توصيل من الرياض خلال يومين عمل. ضمان 12 شهر. <strong>سعر الإطلاق: 269 ر.س</strong> (السعر العادي 309 ر.س).</p>"
        ),
        "rm_title": "Sonoff TX Ultimate T5 Smart Wall Switch — NeoGen Store",
        "rm_desc":  "Sonoff T5 Ultimate touch-glass wall switch with RGB ambient + Matter support. Drops into 86mm KSA wall box. Riyadh delivery in 2 working days.",
    },
    "NG-SH-004": {
        "en_html": (
            "<p><strong>Aqara Water Leak Sensor — small piece, large insurance policy.</strong></p>"
            "<p>A coin-sized Zigbee sensor that does one thing well: it pings your phone the second water touches it. Sit it behind the washing machine, under the kitchen sink, near the water heater, or in the AC drain pan — anywhere a slow leak could become a 5,000 SAR ceiling repair. Two-year battery, IP67 rated, fully sealed.</p>"
            "<ul>"
            "<li><strong>Boring on purpose.</strong> No screen, no buttons, no app to manage day-to-day. Just an alarm when something goes wrong.</li>"
            "<li><strong>Smart-home-native.</strong> Works with the Aqara Hub or any Zigbee coordinator, including Home Assistant Green and Hubitat.</li>"
            "<li><strong>Local sound + push.</strong> Built-in 70 dB siren plus instant push to your phone.</li>"
            "</ul>"
            "<p>Boxed and shipped from Riyadh next working day. 12-month warranty. <strong>Launch price: 219 SAR</strong> (regular 259 SAR). Tip: buy three — one per wet area is the standard install.</p>"
        ),
        "ar_title": "آكارا — حسّاس تسرّب المياه",
        "ar_html": (
            "<p dir=\"rtl\"><strong>آكارا حسّاس تسرّب المياه — قطعة صغيرة، وثيقة تأمين كبيرة.</strong></p>"
            "<p dir=\"rtl\">حسّاس Zigbee بحجم عملة معدنية، يسوي شي واحد بإتقان: يبلّغك على جوالك ثانية ما يلامس الماء. حطّه خلف الغسالة، تحت مغسلة المطبخ، عند سخان الماء، أو بصينية تصريف المكيف. بطارية تكفي سنتين، حماية IP67.</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>ممل بقصد.</strong> ما فيه شاشة ولا أزرار ولا تطبيق تتابعه يومياً. فقط إنذار وقت ما الأمور تخرب.</li>"
            "<li><strong>يندمج مع كل بيت ذكي.</strong> يشتغل مع Aqara Hub أو أي منسق Zigbee، يتضمّن Home Assistant Green و Hubitat.</li>"
            "<li><strong>صوت محلي + إشعار.</strong> صفّارة داخلية 70 ديسيبل بالإضافة لإشعار فوري على الجوال.</li>"
            "</ul>"
            "<p dir=\"rtl\">توصيل من الرياض اليوم التالي. ضمان 12 شهر. <strong>سعر الإطلاق: 219 ر.س</strong> (السعر العادي 259 ر.س). نصيحة: اشترِ ثلاثة.</p>"
        ),
        "rm_title": "Aqara Water Leak Sensor (Zigbee) — NeoGen Store",
        "rm_desc":  "Coin-sized Aqara water-leak sensor. Pings your phone the second a leak starts. 2-year battery, IP67. Buy three, one per wet area. Next-day delivery.",
    },

    # ===== Networking =====
    "NT-CBL-FSC-001": {
        "en_html": (
            "<p><strong>10G SFP+ DAC Twinax Cable, 1 metre — the no-fuss 10-gig link.</strong></p>"
            "<p>Direct-attach copper for stacking switches, linking a NAS to your aggregation switch, or connecting a pfSense box to a UniFi uplink. Passive (no transceiver electronics on the ends), so it's the cheapest way to do real 10G — and runs cooler and quieter than any optical solution at this length.</p>"
            "<ul>"
            "<li><strong>Plug and link.</strong> No transceiver to buy, no tuning, no firmware. Plug it in; the link comes up.</li>"
            "<li><strong>Compatible with everything we sell.</strong> UniFi (USW-Pro-24, USW-Lite-8 SFP), MikroTik (CRS305 / CRS326), and any standard SFP+ port.</li>"
            "<li><strong>Right cable for the right distance.</strong> 1 metre is the rack-internal length. 3m or 5m available on request.</li>"
            "</ul>"
            "<p>Boxed and shipped from Riyadh next working day. 12-month warranty.</p>"
        ),
        "ar_title": "كابل 10G SFP+ DAC تويناكس (1 متر)",
        "ar_html": (
            "<p dir=\"rtl\"><strong>كابل 10G SFP+ DAC تويناكس (1 متر) — وصلة 10 جيجا بدون لخبطة.</strong></p>"
            "<p dir=\"rtl\">كابل نحاسي مباشر لربط سويتشات بنفس الرف، أو ربط NAS بسويتش التجميع، أو وصل صندوق pfSense لرابط UniFi. سلبي (بدون إلكترونيات ترانسيفر بالنهايات)، وهو أرخص طريقة لتشغيل 10G حقيقي.</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>وصل واشتغل.</strong> لا ترانسيفر تشتريه، لا ضبط، لا فيرموير.</li>"
            "<li><strong>متوافق مع كل ما نبيعه.</strong> UniFi (USW-Pro-24، USW-Lite-8 SFP)، MikroTik (CRS305 / CRS326)، وأي منفذ SFP+ قياسي.</li>"
            "<li><strong>الكابل الصح للمسافة الصح.</strong> متر هو طول داخل الرف. 3 أو 5 متر متاحة بالطلب.</li>"
            "</ul>"
            "<p dir=\"rtl\">توصيل من الرياض اليوم التالي. ضمان 12 شهر.</p>"
        ),
        "rm_title": "10G SFP+ DAC Twinax Cable, 1m — NeoGen Store",
        "rm_desc":  "1-metre 10G SFP+ DAC twinax for stacking switches or NAS-to-switch links. UniFi & MikroTik compatible. Riyadh next-day delivery. SAR, VAT incl.",
    },
    "NG-NET-012": {
        "en_html": (
            "<p><strong>UniFi Cloud Gateway Ultra — UniFi without the entry-fee.</strong></p>"
            "<p>The smallest UniFi gateway: routes a 1-Gbps fibre line at full speed, runs the UniFi Network controller on-device (no separate Cloud Key, no cloud subscription), and adds Intrusion Detection / VPN-server / DNS-shield as standard.</p>"
            "<ul>"
            "<li><strong>One box, one ecosystem.</strong> Adopt up to 30 UniFi devices — APs, switches, doorbells, cameras — all from a single dashboard.</li>"
            "<li><strong>Sized for KSA homes.</strong> 1-Gbps WAN matches every fibre plan we've seen from STC, Mobily, and Salam.</li>"
            "<li><strong>Quiet by design.</strong> Fanless. Sits on a shelf or wall-mounts; no rack required.</li>"
            "</ul>"
            "<p>Boxed and shipped from Riyadh in 2 working days. 12-month warranty. Pairs with the U6 Pro / U6 LR access points and USW-Pro-24-PoE switch we also stock.</p>"
        ),
        "ar_title": "يونيفاي Cloud Gateway Ultra — راوتر + متحكّم UniFi",
        "ar_html": (
            "<p dir=\"rtl\"><strong>يونيفاي Cloud Gateway Ultra — UniFi بدون رسوم الدخول.</strong></p>"
            "<p dir=\"rtl\">أصغر راوتر من UniFi: يوجّه خط فايبر 1 جيجا بكامل السرعة، يشغّل متحكّم UniFi Network مباشرة على الجهاز، ويضيف كشف الاختراق و VPN ودرع DNS.</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>صندوق واحد، نظام واحد.</strong> يدير لين 30 جهاز UniFi من لوحة وحدة على نفس الراوتر.</li>"
            "<li><strong>بمقاس البيوت السعودية.</strong> خط WAN 1 جيجا يناسب كل باقات الفايبر اللي شفناها من STC و موبايلي و سلام.</li>"
            "<li><strong>هادئ بالتصميم.</strong> بدون مروحة. على الرف أو معلّق على الجدار، بدون حاجة لراك.</li>"
            "</ul>"
            "<p dir=\"rtl\">توصيل من الرياض خلال يومين عمل. ضمان 12 شهر. ينسجم مع نقاط الوصول U6 Pro / U6 LR وسويتش USW-Pro-24-PoE.</p>"
        ),
        "rm_title": "UniFi Cloud Gateway Ultra — NeoGen Store",
        "rm_desc":  "Ubiquiti's cheapest way into UniFi: router + controller + IDS in one fanless box. 1-Gbps fibre, up to 30 UniFi devices. Riyadh delivery, 12-mo warranty.",
    },
    "NT-WAP-UBQ-001": {
        "en_html": (
            "<p><strong>UniFi U6 Pro — Wi-Fi 6 that actually roams.</strong></p>"
            "<p>The access point we keep recommending. Wi-Fi 6 (AX5400) with proper 802.11k/v/r fast-roaming so phones hand off cleanly between APs as you walk between floors — no dropped Zoom calls, no frozen FaceTime. PoE-powered, ceiling-mount, looks like a smoke detector and not a router.</p>"
            "<ul>"
            "<li><strong>Built for multi-AP coverage.</strong> Pair two of these (one per floor of a typical Saudi villa) and the UniFi controller handles band steering, channel allocation, and roaming for you.</li>"
            "<li><strong>PoE simplifies the install.</strong> One Cat6 cable does power and data. Mounts in 10 minutes if the cable is already pulled.</li>"
            "<li><strong>The honest mid-tier.</strong> The U7 Pro is faster on paper, but 5 GHz Wi-Fi 6 is what 95% of devices in a KSA household actually use. Save the difference, buy two.</li>"
            "</ul>"
            "<p>Boxed and shipped from Riyadh in 2 working days. 12-month warranty. Setup support included — bring your floor plan, we'll mark the AP positions for you.</p>"
        ),
        "ar_title": "يونيفاي U6 Pro — نقطة وصول Wi-Fi 6",
        "ar_html": (
            "<p dir=\"rtl\"><strong>يونيفاي U6 Pro — Wi-Fi 6 ينقل بصدق.</strong></p>"
            "<p dir=\"rtl\">نقطة الوصول اللي ما نقدر نوقف عن التوصية فيها. Wi-Fi 6 (AX5400) مع تقنيات تنقل سريع 802.11k/v/r — يعني الجوالات تنقل بين النقاط بسلاسة. تتغذّى بـ PoE، تثبيت في السقف.</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>مصمّمة لتغطية متعدّدة النقاط.</strong> اثنين منها (واحدة بكل دور بفلّة سعودية عادية) ومتحكّم UniFi يدير لك توجيه الأجهزة.</li>"
            "<li><strong>PoE يبسّط التركيب.</strong> كابل Cat6 واحد يشيل الكهرباء والداتا. التركيب يخلص بـ 10 دقائق.</li>"
            "<li><strong>التير المتوسط الصادق.</strong> U7 Pro أسرع نظرياً، لكن Wi-Fi 6 على 5 GHz هو اللي يستخدمه 95% من الأجهزة. وفّر الفرق واشترِ اثنتين.</li>"
            "</ul>"
            "<p dir=\"rtl\">توصيل من الرياض خلال يومين عمل. ضمان 12 شهر. دعم تركيب — احضر مخطط الفلّة، نعلّم لك مواضع النقاط.</p>"
        ),
        "rm_title": "UniFi U6 Pro Wi-Fi 6 Access Point — NeoGen Store",
        "rm_desc":  "UniFi U6 Pro — Wi-Fi 6 (AX5400) ceiling-mount AP with proper fast-roaming. PoE-powered. The right AP for a Saudi villa. Genuine UBNT supply chain.",
    },
    "NT-NET-MKT-002": {
        "en_html": (
            "<p><strong>MikroTik CRS326 — the homelab/small-office switch the price doesn't match.</strong></p>"
            "<p>24 gigabit ports, 2 SFP+ uplinks at 10G, layer-3 hardware-offloaded routing, and the full RouterOS feature set — at a price that no other vendor is even close to. The classic 'configuration is harder than the hardware' MikroTik trade-off, but if you want VLANs, MLAG, BGP, or VRF in a 1U rack switch and you don't want to pay enterprise money for it, this is the answer.</p>"
            "<ul>"
            "<li><strong>Wire-speed everything.</strong> All 24 ports at gigabit, both SFP+ at 10G, simultaneous, with hardware offload — not a CPU bottleneck like cheaper switches.</li>"
            "<li><strong>RouterOS or SwitchOS.</strong> Choose your firmware on first boot. RouterOS for full L3 + scripting; SwitchOS Lite for a simpler managed-switch UI if that's all you need.</li>"
            "<li><strong>Honest 1U rack.</strong> Standard 19\" mounts, internal PSU, two fans (audible — for the rack room, not the bedroom). Solid metal chassis.</li>"
            "</ul>"
            "<p>Boxed and shipped from Riyadh in 2–5 working days. 12-month warranty. Setup help on WhatsApp — we'll seed a sane VLAN config if you tell us what you want segmented.</p>"
        ),
        "ar_title": "مايكروتيك CRS326-24G-2S+RM — سويتش 24 منفذ بـ RouterOS",
        "ar_html": (
            "<p dir=\"rtl\"><strong>مايكروتيك CRS326 — سويتش الهوم لاب والمكتب الصغير اللي السعر ما يعكسه.</strong></p>"
            "<p dir=\"rtl\">24 منفذ جيجابت، رابطين SFP+ بسرعة 10 جيجا، توجيه الطبقة الثالثة بتسريع عتادي، وكامل ميزات RouterOS — بسعر ما يقاربه أي مصنّع آخر.</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>كل شي بسرعة الكابل.</strong> كل المنافذ الـ 24 بجيجابت، الـ SFP+ بـ 10 جيجا، في نفس الوقت، بتسريع عتادي.</li>"
            "<li><strong>RouterOS أو SwitchOS.</strong> اختر الفيرموير عند أول تشغيل. RouterOS لكل ميزات الطبقة الثالثة؛ SwitchOS Lite لواجهة أبسط.</li>"
            "<li><strong>راك 1U صادق.</strong> تثبيت 19\" قياسي، مزوّد طاقة داخلي، مروحتين (مسموعة — لغرفة الراك). هيكل معدني صلب.</li>"
            "</ul>"
            "<p dir=\"rtl\">توصيل من الرياض خلال 2-5 أيام عمل. ضمان 12 شهر. دعم إعداد بالواتساب — نزرع لك إعداد VLAN معقول.</p>"
        ),
        "rm_title": "MikroTik CRS326-24G-2S+RM 24-port Switch — NeoGen Store",
        "rm_desc":  "MikroTik CRS326 — 24× gigabit + 2× SFP+ rack switch with full RouterOS. Layer-3 hardware offload, wire-speed everything. WhatsApp setup help.",
    },
    "NT-NET-UBQ-001": {
        "en_html": (
            "<p><strong>UniFi USW-Pro-24-PoE — the switch that powers the rack.</strong></p>"
            "<p>Twenty-four gigabit ports, all PoE+ (400 W total budget), two 10 G SFP+ uplinks, layer-3 routing on the switch itself, and the full UniFi controller integration so it shows up in the same dashboard as your APs and gateway. This is the switch we'd put in a small office, a multi-AP villa, or a homelab that's outgrown the desktop tier.</p>"
            "<ul>"
            "<li><strong>PoE+ for the full UniFi stack.</strong> 400 W feeds 8–12 G4 Pro cameras, 4–6 U6 Pro APs, plus a doorbell. One device powering the entire stack.</li>"
            "<li><strong>10 G uplinks to the gateway and NAS.</strong> Two SFP+ ports for trunking to the Cloud Gateway Ultra and to a NAS for media storage.</li>"
            "<li><strong>L3 hardware offload.</strong> Inter-VLAN routing on-switch instead of on the gateway — reduces gateway CPU load when you have 5+ VLANs.</li>"
            "</ul>"
            "<p>Boxed and shipped from Riyadh in 2–5 working days. 12-month warranty. <strong>NeoGen price 2,370 SAR vs Microless KSA 3,064 SAR</strong> — same SKU, authorised supply chain. Setup help included.</p>"
        ),
        "ar_title": "يونيفاي USW-Pro-24-PoE — سويتش 24 منفذ PoE+ مع 10G",
        "ar_html": (
            "<p dir=\"rtl\"><strong>يونيفاي USW-Pro-24-PoE — السويتش اللي يغذّي الراك.</strong></p>"
            "<p dir=\"rtl\">24 منفذ جيجابت، كلها PoE+ (ميزانية 400 واط)، رابطان SFP+ بسرعة 10 جيجا، توجيه الطبقة الثالثة على السويتش نفسه، وتكامل كامل مع متحكّم UniFi.</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>PoE+ يكفي ستاك UniFi كامل.</strong> 400 واط يشغّل 8-12 كاميرا G4 Pro، 4-6 نقاط وصول U6 Pro، زائد جرس.</li>"
            "<li><strong>روابط 10 جيجا للراوتر و NAS.</strong> منفذا SFP+ للربط مع Cloud Gateway Ultra ولـ NAS للتخزين.</li>"
            "<li><strong>توجيه L3 بتسريع عتادي.</strong> التوجيه بين الـ VLAN على السويتش بدل الراوتر.</li>"
            "</ul>"
            "<p dir=\"rtl\">توصيل من الرياض خلال 2-5 أيام عمل. ضمان 12 شهر. <strong>سعرنا 2,370 ر.س مقابل 3,064 ر.س على Microless KSA</strong> — نفس الرقم التعريفي، سلسلة توريد معتمدة.</p>"
        ),
        "rm_title": "UniFi USW-Pro-24-PoE Switch — NeoGen Store",
        "rm_desc":  "UniFi USW-Pro-24-PoE — 24-port managed switch with 400W PoE+ and 10G SFP+ uplinks. Powers the full UniFi stack. Riyadh delivery, 12-mo warranty.",
    },

    # ===== Homelab =====
    "NT-MPC-BLK-002": {
        "en_html": (
            "<p><strong>Beelink EQ12 — the 25-watt machine that ate the homelab.</strong></p>"
            "<p>The mini-PC that converted half of r/homelab from rackmount to desk-corner. Intel N100 (4 efficiency cores), 16 GB DDR4, 500 GB NVMe, dual 2.5 Gbps Ethernet, USB-C, HDMI 2.0 + DisplayPort. 25 watts at full load — ~50 SAR/year of electricity if you run it 24/7. Boots a Proxmox install in 90 seconds.</p>"
            "<ul>"
            "<li><strong>Just enough CPU for everything you'd actually run.</strong> Home Assistant + Pi-hole + 4–5 LXC containers + a small Docker stack with room left for compile jobs.</li>"
            "<li><strong>Two NICs make it a real homelab box.</strong> Dual 2.5 GbE gives you a clean WAN + LAN separation if you want to run pfSense or OPNsense on it.</li>"
            "<li><strong>The right cable in the box.</strong> Comes with KSA-spec 220V-compatible PSU. No US-only wart with a slot adapter.</li>"
            "</ul>"
            "<p>Boxed and shipped from Riyadh in 2 working days. 12-month warranty. Setup-help on WhatsApp.</p>"
        ),
        "ar_title": "بيلينك EQ12 — ميني بي سي إنتل N100 للهوم لاب",
        "ar_html": (
            "<p dir=\"rtl\"><strong>بيلينك EQ12 — جهاز 25 واط أكل الهوم لاب.</strong></p>"
            "<p dir=\"rtl\">الميني بي سي اللي حوّل نصف r/homelab من الراك للزاوية. معالج Intel N100 (4 أنوية كفاءة)، 16 جيجا DDR4، 500 جيجا NVMe، شبكتي 2.5 جيجا، USB-C، HDMI 2.0 + DisplayPort. 25 واط بالحمل الكامل.</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>معالج كافي لكل ما تشغّله فعلاً.</strong> Home Assistant + Pi-hole + 4-5 حاوية LXC + ستاك Docker صغير.</li>"
            "<li><strong>شبكتان تخلّيه هوم لاب حقيقي.</strong> ربط 2.5 جيجا مزدوج يعطيك فصل WAN/LAN نظيف.</li>"
            "<li><strong>الكابل الصح بالكرتون.</strong> مزوّد طاقة متوافق مع 220 فولت السعودية.</li>"
            "</ul>"
            "<p dir=\"rtl\">توصيل من الرياض خلال يومين عمل. ضمان 12 شهر.</p>"
        ),
        "rm_title": "Beelink EQ12 Intel N100 Mini-PC — NeoGen Store",
        "rm_desc":  "Beelink EQ12 — 25-watt mini-PC for your homelab. Intel N100, 16 GB RAM, dual 2.5 GbE. KSA 220V PSU included. Riyadh delivery, 12-mo warranty.",
    },
    "NT-MPC-DEL-001": {
        "en_html": (
            "<p><strong>Dell OptiPlex 7070 Micro (refurb) — the workhorse you'd be embarrassed to throw out.</strong></p>"
            "<p>Six cores of 35-watt Intel Core i5-9500T, 16 GB DDR4, 256 GB NVMe, plus an internal slot for a 2.5\" SATA drive. Originally a corporate workstation, returned at 3-year refresh with most of its life left. Comes with KSA-spec 220V PSU and a fresh Dell BIOS update.</p>"
            "<ul>"
            "<li><strong>Real CPU for under 2,000 SAR.</strong> Six cores with proper hardware virtualisation (VT-x, VT-d, AES-NI) — handles ten Docker containers + a small Windows VM.</li>"
            "<li><strong>Refurb done right.</strong> Inspected, tested, cleaned, paired with a fresh Dell PSU. NeoGen 12-month warranty on top of any residual Dell coverage.</li>"
            "<li><strong>Two storage tiers.</strong> NVMe boot for speed, plus a 2.5\" slot for bulk — drop in a 2 TB SATA SSD as a TrueNAS or Frigate footprint.</li>"
            "</ul>"
            "<p>Boxed and shipped from Riyadh in 2 working days. 12-month NeoGen warranty. WhatsApp install help — if you want it pre-loaded with Proxmox, ask before checkout, no extra charge.</p>"
        ),
        "ar_title": "ديل أوبتيبليكس 7070 مايكرو (مجدّد) — معالج i5، 16 جيجا",
        "ar_html": (
            "<p dir=\"rtl\"><strong>ديل أوبتيبليكس 7070 مايكرو (مجدّد) — جهاز عمل ما تتجاسر ترميه.</strong></p>"
            "<p dir=\"rtl\">ست أنوية من معالج Intel Core i5-9500T بـ 35 واط، 16 جيجا DDR4، 256 جيجا NVMe، إضافة لفتحة داخلية لقرص 2.5\" SATA. أصلاً جهاز عمل في شركة.</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>معالج حقيقي بأقل من 2,000 ر.س.</strong> ست أنوية مع تسريع افتراضي عتادي صحيح.</li>"
            "<li><strong>تجديد بالطريقة الصح.</strong> مفحوص ومنظّف، مع مزوّد طاقة Dell جديد. ضمان NeoGen 12 شهر فوق تغطية Dell المتبقية.</li>"
            "<li><strong>طبقتي تخزين.</strong> NVMe للسرعة، وفتحة 2.5\" للسعة.</li>"
            "</ul>"
            "<p dir=\"rtl\">توصيل من الرياض خلال يومين عمل. ضمان NeoGen 12 شهر. لو تبي يجي محمّل بـ Proxmox مسبقاً، اطلب قبل الدفع.</p>"
        ),
        "rm_title": "Dell OptiPlex 7070 Micro Refurbished — NeoGen Store",
        "rm_desc":  "Refurb Dell OptiPlex 7070 Micro — i5-9500T 6-core, 16 GB, NVMe + 2.5\" slot. Authorised refurb partner, 12-month NeoGen warranty. Riyadh delivery.",
    },
    "NT-NAS-SYN-001": {
        "en_html": (
            "<p><strong>Synology DS225+ — the NAS we recommend by default.</strong></p>"
            "<p>Two drive bays (3.5\" or 2.5\", up to 18 TB each = 36 TB raw or 18 TB mirrored). Synology DSM 7.2 — the only NAS OS most people would call 'actually pleasant to use'. btrfs with snapshots, Docker for hosting whatever doesn't deserve a dedicated mini-PC.</p>"
            "<ul>"
            "<li><strong>Snapshots that save you.</strong> btrfs + Snapshot Replication = take a snapshot every 30 minutes; if ransomware hits, roll back. Local-first, off-cloud.</li>"
            "<li><strong>Docker on the NAS.</strong> Run Vaultwarden, Pi-hole, Home Assistant, *Arr stack — directly on the DS225+, no extra mini-PC.</li>"
            "<li><strong>The migration path.</strong> When you outgrow 2 bays, the same DSM config moves to a DS425+ or DS925+. No re-learning, no data migration friction.</li>"
            "</ul>"
            "<p>Boxed and shipped from Riyadh in 2 working days. 12-month warranty. Drives sold separately — we recommend WD Red Plus or Seagate IronWolf. <strong>Launch price: 2,099 SAR</strong> (regular 2,419 SAR).</p>"
        ),
        "ar_title": "سينولوجي DS225+ — NAS بفتحتين، DSM 7.2",
        "ar_html": (
            "<p dir=\"rtl\"><strong>سينولوجي DS225+ — الـ NAS اللي نوصي فيه افتراضياً.</strong></p>"
            "<p dir=\"rtl\">فتحتي قرص (3.5\" أو 2.5\"، لين 18 تيرا لكل واحد = 36 تيرا خام أو 18 تيرا مضاعف). نظام Synology DSM 7.2.</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>لقطات تنقذك.</strong> btrfs + Snapshot Replication = لقطة كل 30 دقيقة؛ لو ضربك ransomware، ترجّع.</li>"
            "<li><strong>Docker على الـ NAS نفسه.</strong> شغّل Vaultwarden و Pi-hole و Home Assistant و *Arr stack مباشرة على DS225+.</li>"
            "<li><strong>مسار الترقية.</strong> لمّا تكبر فوق فتحتين، نفس إعداد DSM ينقل لـ DS425+ أو DS925+.</li>"
            "</ul>"
            "<p dir=\"rtl\">توصيل من الرياض خلال يومين عمل. ضمان 12 شهر. الأقراص تنباع منفصلة — نوصي WD Red Plus أو Seagate IronWolf. <strong>سعر الإطلاق: 2,099 ر.س</strong> (السعر العادي 2,419 ر.س).</p>"
        ),
        "rm_title": "Synology DS225+ 2-Bay NAS — NeoGen Store",
        "rm_desc":  "Synology DS225+ — 2-bay NAS with DSM 7.2, btrfs snapshots, Docker support. Up to 36 TB raw. Launch price 2,099 SAR. Riyadh delivery, 12-mo warranty.",
    },
    "NT-FWL-NGT-002": {
        "en_html": (
            "<p><strong>Netgate 2100 MAX — pfSense done officially.</strong></p>"
            "<p>Built by the company that maintains pfSense. ARM-based 4-core CPU, 4 GB RAM, 4 gigabit Ethernet ports, integrated cryptographic acceleration for IPSec and WireGuard, fanless. Comes with pfSense+ pre-installed and the official Netgate firmware update path.</p>"
            "<ul>"
            "<li><strong>The official path.</strong> Same engineering team writes the firmware and the OS. Updates are tested as a unit.</li>"
            "<li><strong>Right-sized.</strong> Routes a 1 Gbps fibre line at full speed with IPS turned on. Comfortable headroom for 4–6 simultaneous WireGuard tunnels.</li>"
            "<li><strong>Fanless and quiet.</strong> Sits on a shelf next to the gateway, no rack required. Solid metal chassis.</li>"
            "</ul>"
            "<p>Boxed and shipped from Riyadh in 2–5 working days. 12-month warranty. Setup help included — we'll seed your initial firewall rules + WireGuard server config.</p>"
        ),
        "ar_title": "نتغيت 2100 MAX — جهاز pfSense+ رسمي",
        "ar_html": (
            "<p dir=\"rtl\"><strong>نتغيت 2100 MAX — pfSense بشكله الرسمي.</strong></p>"
            "<p dir=\"rtl\">صنعته الشركة اللي تطوّر pfSense. معالج ARM رباعي النواة، 4 جيجا RAM، أربع منافذ Ethernet جيجا، تسريع تشفير عتادي لـ IPSec و WireGuard، بدون مروحة. يجي مع pfSense+ مثبّت مسبقاً.</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>المسار الرسمي.</strong> نفس الفريق يكتب الفيرموير والنظام. التحديثات تُختبر كوحدة.</li>"
            "<li><strong>المقاس الصح.</strong> يوجّه خط فايبر 1 جيجا بكامل السرعة مع IPS مفعّل.</li>"
            "<li><strong>بدون مروحة وهادئ.</strong> على رف بجانب الراوتر، بدون حاجة لراك. هيكل معدني صلب.</li>"
            "</ul>"
            "<p dir=\"rtl\">توصيل من الرياض خلال 2-5 أيام عمل. ضمان 12 شهر. دعم تركيب — نزرع لك قواعد الجدار الناري الأولية وإعداد سيرفر WireGuard.</p>"
        ),
        "rm_title": "Netgate 2100 MAX pfSense+ Gateway — NeoGen Store",
        "rm_desc":  "Netgate 2100 MAX — official pfSense+ appliance. 4× gigabit, fanless, IPSec/WireGuard hardware acceleration. WhatsApp setup help included.",
    },
    "NT-MPC-MNF-001": {
        "en_html": (
            "<p><strong>MinisForum MS-01 — the homelab server you graduate to.</strong></p>"
            "<p>The box that arrived in late 2024 and rewrote what a desktop-form-factor server can be. Intel Core i9-13900H or i5-13600H, three NVMe M.2 slots, dual 10 GbE SFP+ uplinks, dual 2.5 GbE copper, integrated IPMI, internal U.2 slot.</p>"
            "<ul>"
            "<li><strong>Real server features in a tiny box.</strong> 10 GbE SFP+, IPMI/KVM-over-IP, and PCIe passthrough that actually works.</li>"
            "<li><strong>Three M.2 slots = real ZFS.</strong> Run a striped-mirror ZFS pool of NVMe SSDs as your VM datastore. Snapshots, replication to a TrueNAS box.</li>"
            "<li><strong>Three Proxmox installs in one.</strong> Plenty of compute for a Proxmox node + Windows VM + TrueNAS Scale install. All on a 70 W envelope.</li>"
            "</ul>"
            "<p>Boxed and shipped from Riyadh in 2–5 working days. 12-month warranty. <strong>Launch price: 3,999 SAR</strong> (regular 4,599 SAR). RAM and storage sold separately — we'll size the kit to your workload.</p>"
        ),
        "ar_title": "ميني فورم MS-01 — سيرفر هوم لاب رئيسي",
        "ar_html": (
            "<p dir=\"rtl\"><strong>ميني فورم MS-01 — سيرفر الهوم لاب اللي تتخرّج عليه.</strong></p>"
            "<p dir=\"rtl\">الصندوق اللي وصل أواخر 2024. Intel Core i9-13900H أو i5-13600H، ثلاث فتحات NVMe M.2، رابطين 10 جيجا SFP+، رابطي 2.5 جيجا نحاس، IPMI مدمج، فتحة U.2 داخلية.</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>مزايا سيرفر حقيقية في صندوق صغير.</strong> 10 جيجا SFP+، IPMI/KVM-over-IP، تمرير PCIe يشتغل فعلاً.</li>"
            "<li><strong>ثلاث فتحات M.2 = ZFS حقيقي.</strong> مجمّع ZFS striped-mirror من أقراص NVMe كمخزن آلاتك الافتراضية.</li>"
            "<li><strong>ثلاث منشآت في واحدة.</strong> Proxmox + Windows VM + TrueNAS Scale. كل ذا في 70 واط.</li>"
            "</ul>"
            "<p dir=\"rtl\">توصيل من الرياض خلال 2-5 أيام عمل. ضمان 12 شهر. <strong>سعر الإطلاق: 3,999 ر.س</strong> (السعر العادي 4,599 ر.س).</p>"
        ),
        "rm_title": "MinisForum MS-01 Homelab Server — NeoGen Store",
        "rm_desc":  "MinisForum MS-01 — i9/i5 mini-server with 3× NVMe, dual 10G SFP+, IPMI. Launch price 3,999 SAR. The Proxmox node a real homelab earns.",
    },

    # ===== Security & Surveillance =====
    "NG-SEC-001": {
        "en_html": (
            "<p><strong>Reolink RLC-810A — the camera you'd buy four of without thinking.</strong></p>"
            "<p>4K resolution (3840 × 2160), PoE-powered, IP66 weatherproof, integrated AI for person + vehicle detection on the camera itself (no cloud round-trip). Records to a Reolink NVR, a Synology / QNAP NAS via RTSP, or a Frigate / Home Assistant install on your homelab.</p>"
            "<ul>"
            "<li><strong>AI on the camera, not the cloud.</strong> Person and vehicle detection runs locally — your motion clips don't pass through any third-party server.</li>"
            "<li><strong>Records anywhere.</strong> RTSP + ONVIF support means it pairs with literally any modern NVR or VMS.</li>"
            "<li><strong>Sensible night vision.</strong> 30-metre IR for outdoor use; doesn't drown the frame in IR-bloom on a 5-metre KSA villa wall.</li>"
            "</ul>"
            "<p>Boxed and shipped from Riyadh in 2 working days. 12-month warranty. Pairs with the Reolink 16-Channel PoE NVR — buy four and the bundle covers a typical villa perimeter.</p>"
        ),
        "ar_title": "ريولينك RLC-810A — كاميرا 4K PoE خارجية",
        "ar_html": (
            "<p dir=\"rtl\"><strong>ريولينك RLC-810A — كاميرا تشتري منها أربع بدون تفكير.</strong></p>"
            "<p dir=\"rtl\">دقة 4K (3840 × 2160)، تتغذّى بـ PoE، حماية IP66 ضد الطقس، ذكاء صناعي مدمج لكشف الأشخاص والسيارات على الكاميرا نفسها.</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>الذكاء على الكاميرا، مو السحابة.</strong> كشف الأشخاص والسيارات يشتغل محلياً.</li>"
            "<li><strong>تسجّل على أي شي.</strong> دعم RTSP + ONVIF يعني تنسجم مع أي NVR أو VMS حديث.</li>"
            "<li><strong>رؤية ليلية معقولة.</strong> أشعّة تحت حمراء لمسافة 30 متر للاستخدام الخارجي.</li>"
            "</ul>"
            "<p dir=\"rtl\">توصيل من الرياض خلال يومين عمل. ضمان 12 شهر. تنسجم مع Reolink 16-Channel PoE NVR.</p>"
        ),
        "rm_title": "Reolink RLC-810A 4K PoE Camera — NeoGen Store",
        "rm_desc":  "Reolink RLC-810A — 4K PoE bullet camera with on-camera person/vehicle AI. RTSP/ONVIF, records to NAS or NVR. Riyadh delivery, 12-mo warranty.",
    },
    "NG-SEC-007": {
        "en_html": (
            "<p><strong>Eufy Video Doorbell S330 — the doorbell that doesn't shake you down monthly.</strong></p>"
            "<p>Two cameras — one at face level, one tilted down at the doormat so you can see who's there <em>and</em> whether the delivery actually got left. Wireless (battery, 6-month charge cycle), on-device AI for person / package detection, and the killer feature: <strong>free local storage on the included HomeBase</strong>. No Eufy cloud subscription required for any feature.</p>"
            "<ul>"
            "<li><strong>Dual-camera framing.</strong> Front camera catches faces, downward-angle camera catches packages.</li>"
            "<li><strong>Local recording, included.</strong> HomeBase stores motion clips on built-in eMMC — no SD card to source, no cloud to subscribe to.</li>"
            "<li><strong>Battery, not wired.</strong> Most KSA villas don't have low-voltage wiring at the front door. The S330 sidesteps that — solar panel sold separately.</li>"
            "</ul>"
            "<p>Boxed and shipped from Riyadh in 2 working days. 12-month warranty. Bilingual setup help over WhatsApp.</p>"
        ),
        "ar_title": "يوفي جرس مرئي S330 — كاميرتين بتسجيل محلي",
        "ar_html": (
            "<p dir=\"rtl\"><strong>يوفي جرس مرئي S330 — جرس ما يبتزّك شهرياً.</strong></p>"
            "<p dir=\"rtl\">كاميرتين — وحدة بمستوى الوجه، الثانية مائلة إلى الأرض. لاسلكي (يشتغل ببطارية، شحن كل 6 شهور)، ذكاء صناعي على الجهاز، والميزة الأهم: <strong>تخزين محلي مجاني على HomeBase المرفقة</strong>.</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>تأطير بكاميرتين.</strong> الكاميرا الأمامية تلتقط الوجوه، الكاميرا السفلية تلتقط الطرود.</li>"
            "<li><strong>تسجيل محلي، مرفق.</strong> HomeBase تخزّن مقاطع الحركة على eMMC داخلية.</li>"
            "<li><strong>بطارية مو سلك.</strong> أغلب الفلّات السعودية ما عندها تمديد كهرباء عند الباب.</li>"
            "</ul>"
            "<p dir=\"rtl\">توصيل من الرياض خلال يومين عمل. ضمان 12 شهر.</p>"
        ),
        "rm_title": "Eufy Video Doorbell S330 (Dual Camera) — NeoGen Store",
        "rm_desc":  "Eufy S330 — dual-camera wireless doorbell with on-device AI and free local storage on HomeBase. No subscription. Riyadh delivery, 12-mo warranty.",
    },
    "NG-SEC-006": {
        "en_html": (
            "<p><strong>Ajax Hub 2 Plus — alarm engineering, not consumer-grade.</strong></p>"
            "<p>The flagship Ajax controller. Three independent connectivity paths (Wi-Fi, Ethernet, dual-SIM 4G LTE) so the alarm can still phone home if the fibre is cut, the router is unplugged, or one SIM is dead. Supports up to 200 Ajax wireless sensors on the encrypted Jeweller radio protocol with a 2,000-metre line-of-sight range.</p>"
            "<ul>"
            "<li><strong>Three paths, not one.</strong> Most consumer alarms die when the home internet does. Ajax routes around it. Two SIM slots so the 4G fallback doesn't depend on a single carrier.</li>"
            "<li><strong>Sensor ecosystem we trust.</strong> Ajax sensors are the ones professional installers actually pick. 5–7 year battery, real tamper detection, accurate PIR with pet-immunity tuning.</li>"
            "<li><strong>PRO Desktop monitoring.</strong> Works with the Ajax PRO Desktop app for monitoring stations. Doesn't require it — works fully standalone with the Ajax mobile app.</li>"
            "</ul>"
            "<p>Boxed and shipped from Riyadh in 2 working days. 12-month warranty. <strong>Setup help is essential</strong> for an alarm — book a free WhatsApp planning session before you wire it up.</p>"
        ),
        "ar_title": "أجاكس Hub 2 Plus — مركز إنذار احترافي",
        "ar_html": (
            "<p dir=\"rtl\"><strong>أجاكس Hub 2 Plus — هندسة إنذار، مو منتج مستهلك.</strong></p>"
            "<p dir=\"rtl\">الوحدة الرئيسية من Ajax. ثلاث طرق اتصال مستقلة (واي فاي، إيثرنت، 4G LTE ثنائي الشريحة). يدعم لين 200 حسّاس Ajax لاسلكي على بروتوكول راديو Jeweller المشفّر بمدى 2,000 متر.</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>ثلاث طرق، مو وحدة.</strong> أغلب إنذارات المستهلك تموت لمّا الإنترنت ينقطع. Ajax يسلك مسار بديل.</li>"
            "<li><strong>منظومة حسّاسات نثق فيها.</strong> بطارية 5-7 سنوات، كشف عبث حقيقي، PIR دقيق مع ضبط مناعة للحيوانات الأليفة.</li>"
            "<li><strong>مراقبة PRO Desktop.</strong> يشتغل مع تطبيق Ajax PRO Desktop لمحطات المراقبة. ما تحتاجها — يعمل مستقلاً.</li>"
            "</ul>"
            "<p dir=\"rtl\">توصيل من الرياض خلال يومين عمل. ضمان 12 شهر. <strong>دعم التركيب أساسي</strong> — احجز جلسة واتساب مجانية معنا.</p>"
        ),
        "rm_title": "Ajax Hub 2 Plus Wireless Alarm — NeoGen Store",
        "rm_desc":  "Ajax Hub 2 Plus — professional wireless alarm with Wi-Fi + Ethernet + dual-SIM 4G. Up to 200 encrypted sensors. WhatsApp planning help, free.",
    },
    "NG-SEC-004": {
        "en_html": (
            "<p><strong>UniFi Protect G4 Doorbell Pro — the doorbell for the rack you already built.</strong></p>"
            "<p>Ubiquiti's flagship doorbell. 5 MP main camera at the door + a second wide-angle below for the package frame, integrated fingerprint reader for unlocking smart locks via UniFi Access (separate hardware required), built-in chime speaker, and full integration into the UniFi Protect controller — same dashboard as your G4 Pro cameras.</p>"
            "<ul>"
            "<li><strong>One ecosystem, one app.</strong> Records to the same UniFi controller as your other cameras. No Ring, no Eufy, no separate cloud account.</li>"
            "<li><strong>Real PoE doorbell.</strong> Powered by PoE, not battery — plug-and-stay-plugged. Works through KSA summer heat without battery drift.</li>"
            "<li><strong>Optional fingerprint.</strong> Tap the doorbell's fingerprint reader, and the controller authenticates against your UniFi Access setup before unlocking. Locally — no cloud round-trip.</li>"
            "</ul>"
            "<p>Boxed and shipped from Riyadh in 2-5 working days. 12-month warranty. <strong>Pairs with the UniFi Cloud Gateway Ultra and USW-Pro-24-PoE we also stock</strong> — bundle setup support is on us.</p>"
        ),
        "ar_title": "يونيفاي Protect G4 Doorbell Pro — جرس مرئي 5 ميجابكسل",
        "ar_html": (
            "<p dir=\"rtl\"><strong>يونيفاي Protect G4 Doorbell Pro — الجرس للراك اللي بنيته أصلاً.</strong></p>"
            "<p dir=\"rtl\">الجرس الرئيسي من Ubiquiti. كاميرا 5 ميجابكسل عند الباب + كاميرا واسعة الزاوية تحت لإطار الطرد، قارئ بصمة مدمج لفتح الأقفال الذكية عبر UniFi Access، سمّاعة جرس داخلية، وتكامل كامل مع متحكّم UniFi Protect.</p>"
            "<ul dir=\"rtl\">"
            "<li><strong>منظومة واحدة، تطبيق واحد.</strong> يسجّل على نفس متحكّم UniFi مع باقي كاميراتك. لا Ring، لا Eufy.</li>"
            "<li><strong>جرس PoE حقيقي.</strong> يتغذّى بـ PoE، مو بطارية. يشتغل بحرارة الصيف السعودي بدون تذبذب البطارية.</li>"
            "<li><strong>بصمة اختيارية.</strong> المتحكّم يتحقّق مع UniFi Access قبل ما يفتح. محلياً — بدون رحلة سحابية.</li>"
            "</ul>"
            "<p dir=\"rtl\">توصيل من الرياض خلال 2-5 أيام عمل. ضمان 12 شهر. <strong>ينسجم مع UniFi Cloud Gateway Ultra و USW-Pro-24-PoE المتوفرة عندنا</strong>.</p>"
        ),
        "rm_title": "UniFi Protect G4 Doorbell Pro 5MP — NeoGen Store",
        "rm_desc":  "UniFi Protect G4 Doorbell Pro — 5MP main + package camera, fingerprint reader, full UniFi controller integration. Riyadh delivery, 12-mo warranty.",
    },
}


# ----------------------------------------------------------------------------
# WP-CLI helpers
# ----------------------------------------------------------------------------

def wp(*args, capture=True, dry_run=False):
    """Run wp-cli. Returns stdout stripped, or None on failure."""
    cmd = [WP_BIN] + list(args)
    if dry_run:
        print(f"  $ wp {' '.join(shlex.quote(a) for a in args)[:140]}")
        return ""
    # Python 3.6 compat: capture_output kwarg was added in 3.7.
    stdout = subprocess.PIPE if capture else None
    stderr = subprocess.PIPE if capture else None
    try:
        result = subprocess.run(cmd, stdout=stdout, stderr=stderr,
                                universal_newlines=True, timeout=60)
    except subprocess.TimeoutExpired:
        print(f"  ✗ TIMEOUT: wp {args[0] if args else ''}")
        return None
    if result.returncode != 0:
        err = (result.stderr or "").strip()
        print(f"  ✗ FAIL ({result.returncode}): {err[:200]}")
        return None
    return (result.stdout or "").strip()


def get_term_id(slug):
    out = wp("term", "list", "product_cat", "--slug=" + slug, "--field=term_id", "--number=1")
    return out if out and out.isdigit() else None


def get_post_id_by_sku(sku):
    out = wp("post", "list", "--post_type=product", "--meta_key=_sku",
             "--meta_value=" + sku, "--field=ID", "--posts_per_page=1")
    if out and out.split("\n")[0].isdigit():
        return out.split("\n")[0]
    return None


def update_category(slug, content, dry_run=False):
    print(f"\n[Category] {slug}")
    term_id = get_term_id(slug)
    if not term_id:
        print(f"  ⚠ term not found in product_cat — skipping")
        return False

    # term description (the EN body — if you'd rather store EN+AR concatenated,
    # change this to content['en_html'] + '\n\n' + content['ar_html'])
    wp("term", "update", "product_cat", term_id,
       "--description=" + content["en_html"], dry_run=dry_run)

    # AR description meta (theme-specific key)
    wp("term", "meta", "update", term_id, AR_DESC_KEY, content["ar_html"], dry_run=dry_run)

    # Rank Math
    wp("term", "meta", "update", term_id, RM_TITLE_KEY, content["rm_title"], dry_run=dry_run)
    wp("term", "meta", "update", term_id, RM_DESC_KEY,  content["rm_desc"],  dry_run=dry_run)

    print(f"  ✓ updated term_id={term_id}")
    return True


def update_product(sku, content, dry_run=False):
    print(f"\n[Product] {sku}")
    post_id = get_post_id_by_sku(sku)
    if not post_id:
        print(f"  ⚠ no product with _sku={sku} — skipping")
        return False

    # post content (EN long description)
    wp("post", "update", post_id,
       "--post_content=" + content["en_html"], dry_run=dry_run)

    # AR title + AR description meta
    wp("post", "meta", "update", post_id, AR_TITLE_KEY, content["ar_title"], dry_run=dry_run)
    wp("post", "meta", "update", post_id, AR_DESC_KEY,  content["ar_html"],  dry_run=dry_run)

    # Rank Math
    wp("post", "meta", "update", post_id, RM_TITLE_KEY, content["rm_title"], dry_run=dry_run)
    wp("post", "meta", "update", post_id, RM_DESC_KEY,  content["rm_desc"],  dry_run=dry_run)

    print(f"  ✓ updated post_id={post_id}")
    return True


# ----------------------------------------------------------------------------
# Main
# ----------------------------------------------------------------------------

def main():
    ap = argparse.ArgumentParser(description=__doc__.split("\n\n")[0])
    ap.add_argument("--dry-run", action="store_true",
                    help="print the wp-cli commands instead of running them")
    ap.add_argument("--only-categories", action="store_true",
                    help="apply category updates only, skip products")
    ap.add_argument("--only-products", action="store_true",
                    help="apply product updates only, skip categories")
    ap.add_argument("--skip-sku", action="append", default=[],
                    help="skip a specific product SKU (repeatable)")
    args = ap.parse_args()

    # Sanity check: wp-cli reachable
    info = wp("--info", capture=True)
    if info is None:
        print("\nFATAL: 'wp --info' failed. Make sure wp-cli is installed and you're in the WP install root.")
        sys.exit(1)

    print(f"NeoGen marketing-copy applier")
    print(f"  Mode:   {'DRY RUN' if args.dry_run else 'APPLY'}")
    print(f"  Plan:   {len(CATEGORIES)} categories + {len(PRODUCTS)} products")

    cat_ok = cat_skip = 0
    prod_ok = prod_skip = 0

    if not args.only_products:
        for slug, content in CATEGORIES.items():
            if update_category(slug, content, dry_run=args.dry_run):
                cat_ok += 1
            else:
                cat_skip += 1

    if not args.only_categories:
        for sku, content in PRODUCTS.items():
            if sku in args.skip_sku:
                print(f"\n[Product] {sku}  (skipped via --skip-sku)")
                prod_skip += 1
                continue
            if update_product(sku, content, dry_run=args.dry_run):
                prod_ok += 1
            else:
                prod_skip += 1

    print("\n" + "=" * 60)
    print(f"Categories:  {cat_ok} ok / {cat_skip} skipped")
    print(f"Products:    {prod_ok} ok / {prod_skip} skipped")
    print("=" * 60)

    if cat_skip + prod_skip > 0:
        print("\nSome entries were skipped. Re-check term slugs and product SKUs in WP admin.")

    if not args.dry_run and (cat_ok + prod_ok) > 0:
        print("\nNext: visit a few of the updated pages and confirm the copy renders correctly.")
        print("If the AR meta keys (_ng_ar_title / _ng_ar_description) aren't read by the active theme,")
        print("the EN content will still render correctly — only the AR slot is theme-dependent.")


if __name__ == "__main__":
    main()
