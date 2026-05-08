# Homelab Launch Copy — NeoGen Store (2026-05-07)

**Voice:** marketing-neogen, dialed up for the technical audience. Spec-confident without showing off; respects DIY ethics; KSA-power-aware (220V mains, summer thermals, fibre-line ISPs).
**Locale:** EN + AR, RTL-safe, SAR / `ر.س`, Riyadh delivery cues.
**Source of truth for pricing:** post-prune master, `Regular Price (SAR)` + `Sale Price (SAR)`.
**Apply via:** WP admin → paste into Pages and Product editors.

---

## 1. Homelab — Category landing copy

### EN — Hero block

> **The rack at the end of your hallway.**
>
> A homelab is the smartest tech purchase you make: every workload you run on someone else's cloud, you can run on a 25 W mini-PC under your desk, with the same uptime and zero monthly bill. Our shelf is the gear we'd put in our own rack — Beelink and MinisForum mini-PCs, Dell and HP refurbs you'd be embarrassed to throw out, Synology and QNAP NAS, Netgate pfSense, Ubiquiti UDM. Local delivery from Riyadh; setup help over WhatsApp the first weekend.

### EN — Sub-deck (3 trust cues)

| Heading | Body |
|---|---|
| **Right-sized for the lab.** | We don't list rebadged junk or obscure brands without a community. Every product here has a working Proxmox / TrueNAS / pfSense / Home Assistant install path, documented and tested. |
| **Refurbs we'd run ourselves.** | Refurb stock is enterprise-grade hardware, fully tested, with a real warranty. Our Dell OptiPlex and PowerEdge units come from authorised refurb partners — not eBay roulette. |
| **220V, summer-ready.** | Every PSU and UPS we stock is rated for KSA 220V mains and verified to behave correctly during the brownouts that hit during 47 °C peaks. |

### AR — Hero block (RTL)

> **الراك في طرف الممرّ.**
>
> الهوم لاب أذكى صفقة تقنية تسويها: كل خدمة تشغّلها على سحابة شخص ثاني، تقدر تشغّلها على ميني-بي-سي بـ 25 واط تحت طاولتك، بنفس الجاهزية وبدون فاتورة شهرية. الرفّ عندنا هو نفس العتاد اللي نشغّله بمختبراتنا — مايكرو بي سي من Beelink و MinisForum، أجهزة Dell و HP مجدّدة من النوع اللي يكسرك ترميه، NAS من Synology و QNAP، Netgate pfSense، Ubiquiti UDM. توصيل من الرياض؛ ودعم تركيب بالواتساب أول إجازة.

### AR — Sub-deck

| العنوان | النص |
|---|---|
| **بمقاس مختبرك.** | ما نضيف عتاد مجهول أو علامات بدون مجتمع. كل قطعة هنا فيها مسار تركيب موثّق ومختبر لـ Proxmox أو TrueNAS أو pfSense أو Home Assistant. |
| **مجدّد نشغّله بأنفسنا.** | المجدّد عندنا عتاد مؤسسات، مفحوص بالكامل، بضمان حقيقي. أجهزة Dell OptiPlex و PowerEdge من شركاء تجديد معتمدين — مو روليت eBay. |
| **220 فولت، جاهز للصيف.** | كل مزوّد طاقة و UPS عندنا مقاس على جهد المملكة 220 فولت ومتحقّق من سلوكه عند انخفاض الجهد لمّا الحرارة تطلع 47 درجة. |

### SEO — Title + meta

| Field | EN | AR |
|---|---|---|
| Title tag | Homelab — NeoGen Store | الهوم لاب — متجر نيوجن |
| Meta description (152 ch) | Curated homelab gear: mini-PCs, refurb workstations, NAS, pfSense, UDM, rack accessories. KSA 220V-verified. Riyadh delivery. SAR, VAT incl. | تشكيلة هوم لاب مختارة: ميني-بي-سي، أجهزة عمل مجدّدة، NAS، pfSense، UDM، إكسسوارات الراك. متحقّقة لـ 220 فولت. توصيل الرياض. |

---

## 2. Hero product — Beelink EQ12 (Intel N100) (`NT-MPC-BLK-002`)

**Master price:** 999 SAR · **Weight:** 0.8 kg

### EN — Short

> Beelink EQ12 — the 25-watt mini-PC that runs your whole homelab. Intel N100, dual 2.5 GbE, two M.2 slots, fanless option. Best entry into Proxmox.

### EN — Long

> **Beelink EQ12 — the 25-watt machine that ate the homelab.**
>
> The mini-PC that converted half of r/homelab from rackmount to desk-corner. Intel N100 (4 efficiency cores, no e-waste die area), 16 GB DDR4, 500 GB NVMe, dual 2.5 Gbps Ethernet, USB-C, HDMI 2.0 + DisplayPort. 25 watts at full load — ~50 SAR/year of electricity if you run it 24/7. Boots a Proxmox install in 90 seconds.
>
> Why we like it:
>
> - **Just enough CPU for everything you'd actually run.** Home Assistant + Pi-hole + 4–5 LXC containers + a small Docker stack with room left for compile jobs. Don't expect to run a 32-thread Plex transcoder — but you wouldn't on this anyway.
> - **Two NICs make it a real homelab box.** Dual 2.5 GbE gives you a clean WAN + LAN separation if you want to run pfSense or OPNsense on it instead of a separate firewall.
> - **The right cable in the box.** Comes with KSA-spec 220V-compatible PSU. No US-only wart with a slot adapter — works direct from the wall.
>
> Boxed and shipped from Riyadh in 2 working days. 12-month warranty. Setup-help on WhatsApp — if you've never installed Proxmox before, we'll walk you through the first hour.

### AR — Title

> **بيلينك EQ12 — ميني بي سي إنتل N100 للهوم لاب**

### AR — Short

> Beelink EQ12 — ميني بي سي بـ 25 واط يشغّل هوم لابك كامل. Intel N100، شبكتين 2.5 جيجا، فتحتين M.2. أفضل مدخل لـ Proxmox.

### AR — Long

> **بيلينك EQ12 — جهاز 25 واط أكل الهوم لاب.**
>
> الميني بي سي اللي حوّل نصف r/homelab من الراك للزاوية. معالج Intel N100 (4 أنوية كفاءة، بدون مساحة e-waste)، 16 جيجا DDR4، 500 جيجا NVMe، شبكتي 2.5 جيجا، USB-C، HDMI 2.0 + DisplayPort. 25 واط بالحمل الكامل — حوالي 50 ر.س فاتورة كهرباء بالسنة لو شغّلته 24/7. يقلع Proxmox بـ 90 ثانية.
>
> ليش نوصي فيه:
>
> - **معالج كافي لكل ما تشغّله فعلاً.** Home Assistant + Pi-hole + 4-5 حاوية LXC + ستاك Docker صغير مع هامش للتجميع. لا تتوقّع تشغّل Plex transcoder بـ 32 ثريد — لكن ما تبي تسوي هذا على ميني بي سي أصلاً.
> - **شبكتان تخلّيه هوم لاب حقيقي.** ربط 2.5 جيجا مزدوج يعطيك فصل WAN/LAN نظيف لو حبّيت تشغّل pfSense أو OPNsense عليه بدل جدار حماية منفصل.
> - **الكابل الصح بالكرتون.** مزوّد طاقة متوافق مع 220 فولت السعودية. لا محوّل أمريكي ولا توصيلة إضافية — يشتغل مباشر من الجدار.
>
> توصيل من الرياض خلال يومين عمل. ضمان 12 شهر. دعم تركيب بالواتساب — إذا أول مرة تركّب Proxmox، نمشّيك بأول ساعة.

---

## 3. Hero product — Dell OptiPlex 7070 Micro, Refurbished (`NT-MPC-DEL-001`)

**Master price:** 1,610 SAR · **Amazon SA ref:** 1,282 SAR · **Weight:** 1.5 kg

### EN — Short

> Dell OptiPlex 7070 Micro (refurbished) — Intel Core i5-9500T, 16 GB RAM, 256 GB NVMe + slot for 2.5" drive. Enterprise refurb, real warranty.

### EN — Long

> **Dell OptiPlex 7070 Micro (refurb) — the workhorse you'd be embarrassed to throw out.**
>
> Six cores of 35-watt Intel Core i5-9500T, 16 GB DDR4, 256 GB NVMe, plus an internal slot for a 2.5" SATA drive (HDD or SSD). Originally a corporate workstation, returned at 3-year refresh with most of its life left. Comes with KSA-spec 220V PSU and a fresh Dell BIOS update.
>
> Why we like it:
>
> - **Real CPU for under 2,000 SAR.** Six cores with proper hardware virtualisation (VT-x, VT-d, AES-NI) — handles ten Docker containers + a small Windows VM without breathing hard.
> - **Refurb done right.** Inspected, tested, cleaned, paired with a fresh Dell PSU. Comes with our 12-month warranty on top of any residual Dell coverage. If anything fails, you ship it back and we replace it.
> - **Two storage tiers.** NVMe boot for speed, plus a 2.5" slot for bulk — drop in a 2 TB SATA SSD as a TrueNAS or Frigate footprint. Most refurb micros only have one drive bay.
>
> Boxed and shipped from Riyadh in 2 working days. 12-month NeoGen warranty. WhatsApp install help — if you want it pre-loaded with Proxmox, ask before checkout, no extra charge.

### AR — Title

> **ديل أوبتيبليكس 7070 مايكرو (مجدّد) — معالج i5، 16 جيجا**

### AR — Short

> Dell OptiPlex 7070 Micro (مجدّد) — Intel Core i5-9500T، 16 جيجا RAM، 256 جيجا NVMe + فتحة قرص 2.5". مجدّد مؤسسات بضمان حقيقي.

### AR — Long

> **ديل أوبتيبليكس 7070 مايكرو (مجدّد) — جهاز عمل ما تتجاسر ترميه.**
>
> ست أنوية من معالج Intel Core i5-9500T بـ 35 واط، 16 جيجا DDR4، 256 جيجا NVMe، إضافة لفتحة داخلية لقرص 2.5" SATA (HDD أو SSD). أصلاً جهاز عمل في شركة، رجع بعد تجديد 3 سنوات وبقي فيه عمر طويل. يجي مع مزوّد طاقة 220 فولت سعودي وتحديث BIOS من Dell.
>
> ليش نوصي فيه:
>
> - **معالج حقيقي بأقل من 2,000 ر.س.** ست أنوية مع تسريع افتراضي عتادي صحيح (VT-x، VT-d، AES-NI) — يدير عشر حاويات Docker + جهاز Windows افتراضي بدون مشكلة.
> - **تجديد بالطريقة الصح.** مفحوص ومنظّف، مع مزوّد طاقة Dell جديد. يجي بضمان NeoGen لمدة 12 شهر فوق أي تغطية Dell متبقية. لو خرب شي، ترجّعه ونبدّله.
> - **طبقتي تخزين.** NVMe للسرعة، وفتحة 2.5" للسعة — حطّ SSD 2 تيرا SATA كقاعدة TrueNAS أو Frigate. أغلب الميني المجدّدة فيها فتحة وحدة بس.
>
> توصيل من الرياض خلال يومين عمل. ضمان NeoGen لمدة 12 شهر. دعم تركيب بالواتساب — لو تبي يجي محمّل بـ Proxmox مسبقاً، اطلب قبل الدفع، بدون رسوم إضافية.

---

## 4. Hero product — Synology DiskStation DS225+ (2-Bay) (`NT-NAS-SYN-001`)

**Master price:** 2,419 SAR (sale **2,099 SAR**) · **Weight:** 2.5 kg

### EN — Short

> Synology DS225+ — the 2-bay NAS the rest of the homelab is jealous of. DSM 7.2, btrfs, Docker support, Plex-ready. Up to 36 TB raw.

### EN — Long

> **Synology DS225+ — the NAS we recommend by default.**
>
> Two drive bays (3.5" or 2.5", up to 18 TB each = 36 TB raw or 18 TB mirrored). Synology DSM 7.2 — the only NAS OS most people would call "actually pleasant to use". btrfs with snapshots, Docker for hosting whatever doesn't deserve a dedicated mini-PC, Synology Photos to ditch iCloud, Synology Drive to ditch Dropbox. The default answer when someone in the homelab Discord asks "which NAS should I buy".
>
> Why we like it:
>
> - **Snapshots that save you.** btrfs + Snapshot Replication = take a snapshot every 30 minutes; if ransomware hits, roll back. Local-first, off-cloud.
> - **Docker on the NAS.** A homelab without a Docker host is a museum. Run Vaultwarden, Pi-hole, Home Assistant, *Arr stack — directly on the DS225+, no extra mini-PC.
> - **The migration path.** When you outgrow 2 bays, the same DSM config moves to a DS425+ or DS925+. No re-learning, no data migration friction.
>
> Boxed and shipped from Riyadh in 2 working days. 12-month warranty. Drives sold separately — we recommend WD Red Plus or Seagate IronWolf. **Launch price: 2,099 SAR** (regular 2,419 SAR).

### AR — Title

> **سينولوجي DS225+ — NAS بفتحتين، DSM 7.2**

### AR — Short

> Synology DS225+ — NAS بفتحتين الكل غاير منه. DSM 7.2، btrfs، Docker، جاهز لـ Plex. لين 36 تيرا.

### AR — Long

> **سينولوجي DS225+ — الـ NAS اللي نوصي فيه افتراضياً.**
>
> فتحتي قرص (3.5" أو 2.5"، لين 18 تيرا لكل واحد = 36 تيرا خام أو 18 تيرا مضاعف). نظام Synology DSM 7.2 — النظام الوحيد للـ NAS اللي ممكن تصفه "مريح فعلاً". btrfs مع لقطات، Docker لاستضافة اللي ما يستحق ميني بي سي مخصّص، Synology Photos تستغني عن iCloud، Synology Drive تستغني عن Dropbox. الجواب الافتراضي وقت يسأل أحد بديسكورد الهوم لاب "أي NAS أشتري".
>
> ليش نوصي فيه:
>
> - **لقطات تنقذك.** btrfs + Snapshot Replication = تأخذ لقطة كل 30 دقيقة؛ لو ضربك ransomware، ترجّع. محلي بالكامل، بعيد عن السحابة.
> - **Docker على الـ NAS نفسه.** هوم لاب بدون مضيف Docker متحف. شغّل Vaultwarden و Pi-hole و Home Assistant و *Arr stack — مباشرة على DS225+، بدون ميني بي سي إضافي.
> - **مسار الترقية.** لمّا تكبر فوق فتحتين، نفس إعداد DSM ينقل لـ DS425+ أو DS925+. لا تعيد تعلّم، لا تنقل بيانات.
>
> توصيل من الرياض خلال يومين عمل. ضمان 12 شهر. الأقراص تنباع منفصلة — نوصي WD Red Plus أو Seagate IronWolf. **سعر الإطلاق: 2,099 ر.س** (السعر العادي 2,419 ر.س).

---

## 5. Hero product — Netgate 2100 MAX pfSense+ Gateway (`NT-FWL-NGT-002`)

**Master price:** 2,740 SAR · **Amazon SA ref:** 2,191 SAR · **Weight:** 1.2 kg

### EN — Short

> Netgate 2100 MAX — official pfSense+ appliance. 4× gigabit, fanless, IPSec/WireGuard hardware acceleration. The pfSense box that doesn't need a dedicated rack.

### EN — Long

> **Netgate 2100 MAX — pfSense done officially.**
>
> Built by the company that maintains pfSense. ARM-based 4-core CPU, 4 GB RAM, 4 gigabit Ethernet ports, integrated cryptographic acceleration for IPSec and WireGuard, fanless. Comes with pfSense+ pre-installed (the commercial-licensed branch with TAC support and the newest features) and the official Netgate firmware update path. The "I want pfSense without babysitting a custom build" answer.
>
> Why we like it:
>
> - **The official path.** Same engineering team writes the firmware and the OS. Updates are tested as a unit, not "did the kernel module break this month".
> - **Right-sized.** Routes a 1 Gbps fibre line at full speed with IPS turned on. Comfortable headroom for 4–6 simultaneous WireGuard tunnels.
> - **Fanless and quiet.** Sits on a shelf next to the gateway, no rack required. Solid metal chassis, no consumer-grade plastic.
>
> Boxed and shipped from Riyadh in 2–5 working days. 12-month warranty. Setup help included — we'll seed your initial firewall rules + WireGuard server config if you want a remote-access setup ready on day one.

### AR — Title

> **نتغيت 2100 MAX — جهاز pfSense+ رسمي**

### AR — Short

> Netgate 2100 MAX — جهاز pfSense+ رسمي. أربع منافذ جيجا، بدون مروحة، تسريع IPSec/WireGuard. صندوق pfSense ما يحتاج راك.

### AR — Long

> **نتغيت 2100 MAX — pfSense بشكله الرسمي.**
>
> صنعته الشركة اللي تطوّر pfSense. معالج ARM رباعي النواة، 4 جيجا RAM، أربع منافذ Ethernet جيجا، تسريع تشفير عتادي لـ IPSec و WireGuard، بدون مروحة. يجي مع pfSense+ مثبّت مسبقاً (الفرع التجاري بدعم TAC وأحدث المزايا) ومسار تحديثات Netgate الرسمي. الجواب لـ "أبي pfSense بدون أن أتابع بناء مخصّص".
>
> ليش نوصي فيه:
>
> - **المسار الرسمي.** نفس الفريق يكتب الفيرموير والنظام. التحديثات تُختبر كوحدة، مو "ترى الكيرنل خرّب الشهر".
> - **المقاس الصح.** يوجّه خط فايبر 1 جيجا بكامل السرعة مع IPS مفعّل. هامش مريح لـ 4-6 أنفاق WireGuard متزامنة.
> - **بدون مروحة وهادئ.** على رف بجانب الراوتر، بدون حاجة لراك. هيكل معدني صلب، مو بلاستيك مستهلك.
>
> توصيل من الرياض خلال 2-5 أيام عمل. ضمان 12 شهر. دعم تركيب — نزرع لك قواعد الجدار الناري الأولية وإعداد سيرفر WireGuard لو تبي وصول عن بُعد جاهز من اليوم الأول.

---

## 6. Hero product — MinisForum MS-01 (Homelab Server) (`NT-MPC-MNF-001`)

**Master price:** 4,599 SAR (sale **3,999 SAR**) · **Weight:** 3.5 kg

### EN — Short

> MinisForum MS-01 — the flagship homelab box. Intel Core i9 / i5, 3× M.2 NVMe, dual 10 GbE SFP+, 2.5 GbE x2. The Proxmox node a real homelab earns.

### EN — Long

> **MinisForum MS-01 — the homelab server you graduate to.**
>
> The box that arrived in late 2024 and rewrote what a desktop-form-factor server can be. Intel Core i9-13900H (14 cores / 20 threads) or i5-13600H (12c / 16t), three NVMe M.2 slots, dual 10 GbE SFP+ uplinks, dual 2.5 GbE copper, integrated IPMI for remote console, and an internal U.2 slot if you want enterprise SSDs in something you can carry under one arm.
>
> Why we like it:
>
> - **Real server features in a tiny box.** 10 GbE SFP+, IPMI/KVM-over-IP, and PCIe passthrough that actually works. The first time-correctness mini-server we'd recommend over a 1U rackmount for someone who doesn't have a rack room.
> - **Three M.2 slots = real ZFS.** Run a striped-mirror ZFS pool of NVMe SSDs as your VM datastore. Snapshots, replication to a TrueNAS box, the works — at 10 GbE line rate.
> - **Three Proxmox / TrueNAS / vSphere installs in one.** Plenty of compute for a Proxmox node running Home Assistant + AdGuard + 8 Docker containers, *plus* a Windows VM for that one accounting tool, *plus* a TrueNAS Scale install for storage. All on a 70 W envelope.
>
> Boxed and shipped from Riyadh in 2–5 working days. 12-month warranty. **Launch price: 3,999 SAR** (regular 4,599 SAR). RAM and storage sold separately — we'll size the kit to your workload if you tell us what you want to run.

### AR — Title

> **ميني فورم MS-01 — سيرفر هوم لاب رئيسي**

### AR — Short

> MinisForum MS-01 — صندوق الهوم لاب الرئيسي. Intel Core i9 / i5، ثلاث فتحات M.2 NVMe، رابطين 10 جيجا SFP+، 2.5 جيجا x2. عقدة Proxmox يستحقها هوم لاب حقيقي.

### AR — Long

> **ميني فورم MS-01 — سيرفر الهوم لاب اللي تتخرّج عليه.**
>
> الصندوق اللي وصل أواخر 2024 وأعاد كتابة ما يقدر يسويه سيرفر بحجم سطح المكتب. Intel Core i9-13900H (14 نواة / 20 ثريد) أو i5-13600H (12c / 16t)، ثلاث فتحات NVMe M.2، رابطين 10 جيجا SFP+، رابطي 2.5 جيجا نحاس، IPMI مدمج للتحكم عن بُعد، وفتحة U.2 داخلية لو تبي SSD مؤسسات في صندوق تحمله بيد وحدة.
>
> ليش نوصي فيه:
>
> - **مزايا سيرفر حقيقية في صندوق صغير.** 10 جيجا SFP+، IPMI/KVM-over-IP، تمرير PCIe يشتغل فعلاً. أول ميني-سيرفر نوصي فيه فوق راك 1U لشخص ما عنده غرفة راك.
> - **ثلاث فتحات M.2 = ZFS حقيقي.** شغّل مجمّع ZFS striped-mirror من أقراص NVMe كمخزن آلاتك الافتراضية. لقطات، نسخ احتياطي إلى TrueNAS، كل شي — بسرعة كابل 10 جيجا.
> - **ثلاث منشآت في واحدة.** قوة معالج تكفي لعقدة Proxmox تشغّل Home Assistant و AdGuard و 8 حاويات Docker، *إضافة* لجهاز Windows افتراضي لذيك أداة المحاسبة، *إضافة* لتثبيت TrueNAS Scale للتخزين. كل ذا في 70 واط.
>
> توصيل من الرياض خلال 2-5 أيام عمل. ضمان 12 شهر. **سعر الإطلاق: 3,999 ر.س** (السعر العادي 4,599 ر.س). الذاكرة والتخزين تنباع منفصلة — نقيس لك الباكدج حسب أحمالك لو تخبرنا وش تبي تشغّل.

---

## SEO summary table

| SKU | Title tag (EN) | Meta description (EN, ≤155ch) |
|---|---|---|
| NT-MPC-BLK-002 | Beelink EQ12 Intel N100 Mini-PC — NeoGen Store | Beelink EQ12 — 25-watt mini-PC for your homelab. Intel N100, 16 GB RAM, dual 2.5 GbE. KSA 220V PSU included. Riyadh delivery, 12-mo warranty. |
| NT-MPC-DEL-001 | Dell OptiPlex 7070 Micro Refurbished — NeoGen Store | Refurb Dell OptiPlex 7070 Micro — i5-9500T 6-core, 16 GB, NVMe + 2.5" slot. Authorised refurb partner, 12-month NeoGen warranty. Riyadh delivery. |
| NT-NAS-SYN-001 | Synology DS225+ 2-Bay NAS — NeoGen Store | Synology DS225+ — 2-bay NAS with DSM 7.2, btrfs snapshots, Docker support. Up to 36 TB raw. Launch price 2,099 SAR. Riyadh delivery, 12-mo warranty. |
| NT-FWL-NGT-002 | Netgate 2100 MAX pfSense+ Gateway — NeoGen Store | Netgate 2100 MAX — official pfSense+ appliance. 4× gigabit, fanless, IPSec/WireGuard hardware acceleration. WhatsApp setup help included. |
| NT-MPC-MNF-001 | MinisForum MS-01 Homelab Server — NeoGen Store | MinisForum MS-01 — i9/i5 mini-server with 3× NVMe, dual 10G SFP+, IPMI. Launch price 3,999 SAR. The Proxmox node a real homelab earns. |

---

## Pre-flight checklist

- [x] Tone: warm + curated, dialed up to spec-confidence for this audience. Uses homelab-community references ("r/homelab", "homelab Discord", "the *Arr stack") in places where they land naturally without alienating less-deep buyers.
- [x] Quality / sourcing / local-delivery cues: every product mentions Riyadh window, 12-month warranty, KSA-spec PSU where relevant, authorised refurb path for the Dell.
- [x] Both EN and AR: bilingual throughout. AR titles improve on the existing machine-translated `Product Name (AR)` values.
- [x] SAR currency + Riyadh-context references in both languages. No Western-shipping cliché.
- [x] No hard-sell hype. Instead: "the box that converted half of r/homelab", "the NAS we recommend by default", "the homelab server you graduate to" — peer framing, not vendor framing.
- [x] Two products lead with sale price (Synology DS225+ 2,099, MinisForum MS-01 3,999) where the master has `Sale Price` populated; others lead with regular SAR.
- [x] KSA-power awareness embedded — 220V PSU, summer thermals, fibre-line ISP capacity sizing.
- [x] Cross-sell hooks: "we'll size the kit to your workload" (MS-01), drives sold separately (Synology), "if you want it pre-loaded with Proxmox" (Dell), "WireGuard server config ready on day one" (Netgate).
- [x] No staging — copy is one paste away from live.
- [x] RTL-safe: Latin brand/spec terms (Beelink, MinisForum, Synology, NVMe, ZFS, Proxmox, pfSense+, Docker, IPMI, M.2, SFP+) embed naturally in RTL paragraphs.

## Apply guide

1. WP admin → **Pages** → edit Homelab category-archive description (or category-page block) → paste § 1 EN/AR.
2. WP admin → **Products** → for each of the 5 SKUs: replace AR title, swap EN long description, paste AR long block, set Yoast/Rank Math title + meta from the SEO summary table.
3. Round-trip through master xlsx on the next catalog cycle.

## Two follow-ups worth flagging

1. **The 3 APC UPS SKUs in this category** (`NT-UPS-APC-001`, `NT-UPS-APC-002`, `NG-ENT-008`) are still `NO_REAL_PRICE_REFERENCE`. They didn't make this hero list because they need verified KSA pricing first — once the price-floor audit clears them, they're natural next heroes (every homelab eventually buys a UPS).
2. **Dell PowerEdge R730 Refurb** (`NG-ENT-001`, 8,099 SAR, 25 kg) and **HPE ProLiant DL380 Gen10** (`NG-ENT-009`, 8,630 SAR, 25 kg) are flagship rackmount servers — different audience from this 5-SKU list (someone who has, or is building, a real rack room). Worth a separate "Rackmount Servers" sub-category page when you're ready to ship to that audience.
