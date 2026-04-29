# Neogen Store Blueprint
## neogen.store - Smart Home & Electronics | Saudi Arabia

---

## 1. Store Identity

### Brand
- **Name**: Neogen (نيوجين)
- **Tagline AR**: حوّل بيتك إلى بيت ذكي
- **Tagline EN**: Transform Your Home Into a Smart Home
- **Domain**: neogen.store

### Positioning
Neogen is the **only Arabic-first, Home Assistant-specialized** smart home store in Saudi Arabia. We serve the DIY-capable homeowner who wants smart home tech without hiring an expensive integrator.

### What Makes Neogen Different
| Factor | Competitors | Neogen |
|--------|------------|--------|
| Language | English-first or generic Arabic | Arabic-first, technical Arabic content |
| Focus | Generic smart products or premium integration | Home Assistant + DIY smart home |
| Support | Email/ticket | 24/7 WhatsApp in Arabic |
| Price | SAR 2,400+ (integrators) or cheap generics | SAR 30-1,800 curated mid-range |
| Content | None | Arabic tutorials, YouTube guides, blog |

### Language Strategy
- **Primary**: Arabic (RTL) - all UI, product descriptions, support
- **Secondary**: English toggle available - product specs, brand names kept in English
- **Technical terms**: Keep in English with Arabic explanation (e.g., "Home Assistant - نظام البيت الذكي")

---

## 2. Homepage Structure

The homepage must convert a visitor into a buyer in one scroll. Every section has a job.

### Section 1: Hero Banner
```
Full-width, dark background with smart home lifestyle image (Saudi villa interior)

Headline (AR): حوّل بيتك إلى بيت ذكي - بدون فني وبدون تعقيد
Subtext: أجهزة ذكية مُختارة بعناية | دعم عربي 24/7 | شحن مجاني فوق 300 ريال

[CTA: تسوّق الآن]  [CTA Secondary: شاهد الدليل]
```

### Section 2: Trust Bar (immediately below hero)
Four icons in a row:
- شحن مجاني فوق 300 ر.س (Free shipping over 300 SAR)
- ضمان سنة كاملة (1-year warranty)
- دعم عربي عبر واتساب (Arabic WhatsApp support)
- إرجاع خلال 15 يوم (15-day returns)

### Section 3: Shop by Category (6 cards)
Visual category cards with icons/images:

| Category | Arabic | Icon Suggestion |
|----------|--------|----------------|
| Security & Cameras | الأمان والكاميرات | Shield + Camera |
| Smart Lighting | الإضاءة الذكية | Lightbulb |
| Sensors & Hubs | المستشعرات والمراكز | Radar/Hub |
| Switches & Electrical | المفاتيح والكهربائيات | Switch |
| Climate & Energy | التحكم بالمناخ والطاقة | Thermometer |
| Networking | الشبكات | WiFi |

### Section 4: Best Sellers (Product Carousel)
- 8-12 top products, auto-scrolling
- Show: image, name (AR), price, rating, "Add to Cart"
- Pull from WooCommerce "featured" tag

### Section 5: Why Neogen? (Value Proposition)
Three columns:

**متخصصون في Home Assistant**
نحن الوحيدون في السعودية المتخصصون في أنظمة Home Assistant. نختار أجهزة متوافقة ومجربة.

**دعم عربي حقيقي**
مش بوت - فريق تقني يرد عليك عبر واتساب بالعربي، يساعدك من الاختيار للتركيب.

**محتوى تعليمي مجاني**
فيديوهات يوتيوب ومقالات بالعربي تشرح لك كل خطوة. حوّل بيتك بنفسك.

### Section 6: Featured Bundle / Offer
Highlight a starter kit or seasonal bundle:
```
باقة البيت الذكي للمبتدئين - Smart Home Starter Kit
Hub + 2 Sensors + Smart Bulb + Smart Plug
SAR XXX (save XX%)
[اطلب الآن]
```

### Section 7: From Our Blog (3 latest posts)
- Arabic blog post cards with thumbnail, title, excerpt
- Topics: "كيف تبدأ بيتك الذكي" / "أفضل 5 كاميرات" / "Home Assistant للمبتدئين"

### Section 8: Brands We Carry (Logo Strip)
Logo carousel: Aqara, Sonoff, Philips Hue, Reolink, Shelly, SwitchBot, Ubiquiti, Nanoleaf, Google Nest, Apple, IKEA, Eufy

### Section 9: Footer
- Quick links: Shop, About, FAQ, Blog, Contact, Privacy Policy
- Contact: WhatsApp number, email, social media icons
- Payment icons: Mada, Visa, Mastercard, Apple Pay, STC Pay, Tabby
- "Powered by Neogen" + copyright

---

## 3. Navigation & Categories

### Main Menu (Right-to-Left)
```
الرئيسية | المتجر ▼ | العروض | المدونة | من نحن | تواصل معنا
```

### Shop Mega Menu (dropdown)
```
┌─────────────────────────────────────────────────────┐
│  الأمان والكاميرات    │  الإضاءة الذكية    │  المستشعرات والمراكز  │
│  - كاميرات داخلية     │  - لمبات ذكية       │  - مستشعر حركة       │
│  - كاميرات خارجية     │  - شرائط LED        │  - مستشعر باب/نافذة  │
│  - أقفال ذكية         │  - لوحات إضاءة      │  - مستشعر حرارة/رطوبة │
│  - أجراس ذكية         │  - إضاءة Hue        │  - هبات ومراكز       │
│  - حساسات دخان        │                     │  - مستشعر تسريب مياه │
│                       │                     │                      │
│  المفاتيح والكهربائيات │  التحكم بالمناخ     │  الشبكات والصوت      │
│  - مفاتيح ذكية        │  - تحكم مكيف        │  - راوترات           │
│  - بلاقات ذكية        │  - ثرموستات         │  - أكسس بوينت       │
│  - ريلاي وقواطع       │  - ستائر ذكية       │  - مكبرات صوت ذكية  │
│  - دمرات إضاءة       │  - مراوح ذكية       │  - سويتشات شبكة     │
└─────────────────────────────────────────────────────┘
```

### Category Hierarchy (WooCommerce)
```
Smart Home (بيت ذكي)
├── Security & Cameras (الأمان والكاميرات)
│   ├── Indoor Cameras (كاميرات داخلية)
│   ├── Outdoor Cameras (كاميرات خارجية)
│   ├── Smart Locks (أقفال ذكية)
│   ├── Video Doorbells (أجراس ذكية)
│   └── Smoke & Safety (حساسات دخان وأمان)
├── Smart Lighting (الإضاءة الذكية)
│   ├── Smart Bulbs (لمبات ذكية)
│   ├── LED Strips (شرائط LED)
│   ├── Light Panels (لوحات إضاءة)
│   └── Philips Hue
├── Sensors & Hubs (المستشعرات والمراكز)
│   ├── Motion Sensors (مستشعر حركة)
│   ├── Door/Window Sensors (مستشعر باب/نافذة)
│   ├── Temperature/Humidity (حرارة ورطوبة)
│   ├── Water Leak Sensors (مستشعر تسريب)
│   └── Hubs & Controllers (هبات ومراكز)
├── Switches & Electrical (المفاتيح والكهربائيات)
│   ├── Smart Switches (مفاتيح ذكية)
│   ├── Smart Plugs (بلاقات ذكية)
│   ├── Relays & Breakers (ريلاي وقواطع)
│   └── Dimmers (دمرات)
├── Climate & Energy (التحكم بالمناخ والطاقة)
│   ├── AC Controllers (تحكم مكيف)
│   ├── Thermostats (ثرموستات)
│   ├── Smart Curtains (ستائر ذكية)
│   └── Smart Fans (مراوح ذكية)
├── Networking & Audio (الشبكات والصوت)
│   ├── Routers & Mesh (راوترات)
│   ├── Access Points (أكسس بوينت)
│   ├── Smart Speakers (مكبرات صوت ذكية)
│   └── Network Switches (سويتشات)
└── Bundles & Kits (باقات وأطقم)
    ├── Starter Kits (باقات مبتدئين)
    ├── Security Kits (باقات أمان)
    └── Room Kits (باقات غرف)
```

### Product Filters (sidebar on shop/category pages)
- **Price Range**: Slider (SAR 0 - 2,000)
- **Brand**: Checkbox list (Aqara, Sonoff, Philips Hue, etc.)
- **Compatibility**: Home Assistant, Apple HomeKit, Google Home, Alexa
- **Connectivity**: Zigbee, WiFi, Z-Wave, Bluetooth, Thread/Matter
- **Rating**: Star filter

---

## 4. Product Pages

### Layout (Single Product)
```
┌──────────────────────────────────────────────┐
│  Breadcrumb: الرئيسية > المستشعرات > مستشعر حركة  │
├──────────────┬───────────────────────────────┤
│              │  Product Name (AR)             │
│   Product    │  Product Name (EN) - gray      │
│   Image      │  ★★★★☆ (4.2) - 15 تقييم       │
│   Gallery    │  SAR 149  (SAR 199 strikethrough)│
│   (zoom +    │                               │
│    thumbnails)│  Short Description (AR)        │
│              │                               │
│              │  Compatibility Icons:          │
│              │  [HA] [HomeKit] [Alexa]        │
│              │                               │
│              │  الكمية: [1] [أضف للسلة]       │
│              │  [♡ أضف للمفضلة]              │
│              │                               │
│              │  ✓ متوفر - شحن خلال 3-7 أيام  │
│              │  🚚 شحن مجاني فوق 300 ر.س     │
├──────────────┴───────────────────────────────┤
│  Tabs:                                       │
│  [الوصف] [المواصفات] [التوافق] [التقييمات]    │
│                                              │
│  الوصف: Full Arabic product description      │
│  with features, use cases, installation tips  │
│                                              │
│  المواصفات (table):                           │
│  البروتوكول    │ Zigbee 3.0                   │
│  المدى        │ 10 متر                        │
│  البطارية     │ CR2450 (سنتين تقريبًا)         │
│  التوافق      │ Home Assistant, HomeKit, Alexa│
│  الأبعاد      │ 30 × 30 × 33 مم              │
│  الضمان       │ سنة                           │
├──────────────────────────────────────────────┤
│  منتجات ذات صلة (Related Products - 4 items) │
│  يُشترى معه عادةً (Frequently bought together)│
└──────────────────────────────────────────────┘
```

### Product Description Template
Every product description should follow this structure:
1. **Opening hook** (1 sentence) - What problem does this solve?
2. **Key features** (3-5 bullets) - What does it do?
3. **Compatibility note** - Works with Home Assistant / HomeKit / etc.
4. **Installation note** - Easy setup / No wiring / Battery powered
5. **In the box** - What's included

### Cross-sell Strategy
- **Related Products**: Same category, different brand
- **Frequently Bought Together**: Hub + Sensors + Plug (bundles)
- **Upsell**: Basic model -> Pro model

---

## 5. Key Pages

### About Us (من نحن)
```
Hero: Photo/illustration of Saudi smart home

Story:
- Founded by smart home enthusiasts in Saudi Arabia
- 5+ years experience with Home Assistant
- 500+ satisfied customers
- Mission: Make smart homes accessible to every Saudi household

Values:
- Arabic-first support
- Curated, tested products only
- Education before sales
- No-BS pricing
```

### FAQ (الأسئلة الشائعة)
Organized in accordion sections:

**الطلب والشحن:**
- كم مدة التوصيل؟ (3-7 أيام عمل داخل السعودية)
- هل يوجد شحن مجاني؟ (نعم، للطلبات فوق 300 ر.س)
- هل تشحنون خارج السعودية؟ (دول الخليج - تواصل معنا)

**المنتجات:**
- هل المنتجات أصلية؟ (نعم، وكيل معتمد + ضمان سنة)
- هل تعمل مع Home Assistant؟ (نعم، كل منتجاتنا مختبرة)
- هل تعمل بدون هب/مركز؟ (يعتمد على المنتج - WiFi مباشر أو يحتاج هب)
- هل تحتاج نيوترال؟ (نوضح لكل منتج - عندنا خيارات بدون نيوترال)

**الدفع والإرجاع:**
- ما هي طرق الدفع؟ (مدى، فيزا، ماستر، Apple Pay، STC Pay، تابي/تمارا)
- هل يوجد تقسيط؟ (نعم عبر تابي وتمارا)
- ما سياسة الإرجاع؟ (15 يوم من الاستلام)

**التركيب:**
- هل أقدر أركبها بنفسي؟ (نعم! عندنا شروحات يوتيوب بالعربي)
- هل توفرون خدمة تركيب؟ (نوفر إرشاد عن بعد + نرشح فنيين موثوقين)

### Contact (تواصل معنا)
- **WhatsApp**: Primary contact (click-to-chat button)
- **Email**: support@neogen.store
- **Social**: Twitter/X, Instagram, YouTube, TikTok
- **Contact form**: Name, Email, Subject, Message
- **Business hours**: 9 AM - 11 PM (Saudi time), WhatsApp 24/7

### Blog (المدونة)
See Content Strategy section below.

### Privacy Policy & Terms
Standard e-commerce legal pages in Arabic, covering:
- Data collection and usage
- Payment processing
- Shipping terms
- Return/refund policy
- Warranty terms

---

## 6. Design System

### Colors
| Role | Color | Hex | Usage |
|------|-------|-----|-------|
| Primary | Deep Blue | #1A3A5C | Headers, CTA buttons, links |
| Secondary | Electric Teal | #00BFA6 | Accents, badges, hover states |
| Accent | Warm Orange | #FF6B35 | Sale tags, alerts, notifications |
| Background | Off-White | #F8F9FA | Page background |
| Surface | White | #FFFFFF | Cards, product tiles |
| Text Primary | Dark Gray | #1A1A2E | Body text |
| Text Secondary | Medium Gray | #6B7280 | Descriptions, metadata |
| Success | Green | #10B981 | In stock, success messages |
| Error | Red | #EF4444 | Errors, out of stock |

### Typography
- **Arabic**: Noto Kufi Arabic or IBM Plex Arabic (modern, clean)
- **English**: Inter or IBM Plex Sans
- **Headings**: Bold, 24-36px
- **Body**: Regular, 16px, line-height 1.7 (Arabic needs more)
- **Price**: Bold, 20px, primary color

### RTL Layout
- All layouts flow right-to-left
- Navigation starts from right
- Breadcrumbs flow right-to-left
- Currency symbol (ر.س) appears after number
- Icons mirror where directionally relevant (arrows, etc.)

### Mobile-First
- 70%+ traffic will be mobile (Saudi market is mobile-dominant)
- Sticky bottom bar: Home, Categories, Cart, Account
- WhatsApp floating button (bottom-left for RTL)
- Touch-friendly: minimum 44px tap targets
- Lazy-load images for performance
- AMP-compatible blog posts

### Component Patterns
- **Product Cards**: Image (1:1 ratio), name, price, rating, add-to-cart
- **Category Cards**: Icon/image, name, product count
- **CTA Buttons**: Rounded corners (8px), primary color, bold text
- **Badges**: "جديد" (New), "خصم XX%" (Discount), "الأكثر مبيعًا" (Best Seller)

---

## 7. Trust & Conversion

### Payment Methods
| Method | Provider | Priority |
|--------|----------|----------|
| Mada | Local debit | Primary (most Saudis use Mada) |
| Visa/Mastercard | Credit/debit | Standard |
| Apple Pay | Mobile | High (large iPhone user base in KSA) |
| STC Pay | Mobile wallet | Medium |
| Tabby | Buy now, pay later | High (increases conversion 20-30%) |
| Tamara | Buy now, pay later | High |
| Bank transfer | Manual | Fallback |

### Shipping
- **Provider**: SMSA, Aramex, or J&T
- **Free shipping**: Orders over SAR 300
- **Standard**: 3-7 business days, SAR 25-35
- **Express**: 1-2 business days, SAR 50-60
- **Tracking**: Automated SMS/WhatsApp updates
- **Coverage**: All Saudi cities + GCC (on request)

### Returns & Warranty
- **Return window**: 15 days from delivery
- **Condition**: Unopened or defective
- **Warranty**: 1 year on all products
- **Process**: WhatsApp support -> return label -> refund in 3-5 days

### Trust Signals (display throughout site)
- SSL badge + secure checkout
- "500+ عميل سعيد" (500+ happy customers)
- Real customer reviews (with photos)
- Brand logos (authorized reseller badges where applicable)
- SAR pricing (no currency confusion)
- "مخزون محلي - شحن من السعودية" (Local stock - ships from KSA)
- Maroof registration badge (Saudi e-commerce registry)

### Conversion Boosters
- **Exit-intent popup**: "احصل على 10% خصم" (Get 10% off first order)
- **Cart abandonment**: WhatsApp reminder after 1 hour
- **Low stock urgency**: "باقي 3 فقط!" (Only 3 left!)
- **Recently viewed**: Sticky bar showing last viewed products
- **Wishlist**: Save for later with email/WhatsApp reminders

---

## 8. SEO Strategy

### Arabic Keywords (Primary)
| Keyword | Monthly Volume | Priority |
|---------|---------------|----------|
| بيت ذكي | 8,100 | Critical |
| كاميرات مراقبة | 12,100 | Critical |
| قفل ذكي | 2,900 | High |
| مفتاح ذكي | 1,600 | High |
| اضاءة ذكية | 1,300 | High |
| Home Assistant | 880 | Critical (niche) |
| كيف احول بيتي لبيت ذكي | 720 | Critical (intent) |
| جهاز تحكم مكيف | 590 | Medium |
| مستشعر حركة | 480 | Medium |
| ستائر ذكية | 390 | Medium |

### English Keywords (Secondary)
| Keyword | Monthly Volume | Priority |
|---------|---------------|----------|
| smart home saudi | 2,400 | High |
| home assistant saudi | 320 | Critical (zero competition) |
| home automation riyadh | 880 | Medium |
| aqara saudi arabia | 210 | Medium |

### On-Page SEO Structure
- **Homepage title**: نيوجين | متجر البيت الذكي في السعودية - Neogen Smart Home Store
- **Category titles**: [Category AR] | أجهزة البيت الذكي - نيوجين
- **Product titles**: [Product AR] | [Brand] - نيوجين
- **Meta descriptions**: Arabic, 150 chars, include price range and key feature
- **URL structure**: neogen.store/product-category/[slug] (English slugs)
- **Hreflang**: ar-SA primary, en secondary
- **Schema markup**: Product, BreadcrumbList, Organization, FAQPage

### Technical SEO
- Sitemap: XML sitemap submitted to Google Search Console
- Robots.txt: Allow all product/category pages
- Page speed: Target <3s LCP (Largest Contentful Paint)
- Core Web Vitals: Green on all metrics
- Canonical URLs: Prevent duplicate content
- Image alt text: Arabic descriptions for all product images

---

## 9. Content Strategy

### Blog Topics (المدونة)

**Pillar 1: Smart Home Guide (دليل البيت الذكي)**
- كيف تبدأ بيتك الذكي من الصفر (How to start your smart home from scratch)
- أفضل أجهزة البيت الذكي في 2025 (Best smart home devices 2025)
- Zigbee vs WiFi vs Z-Wave: أيهم أفضل؟ (Which is best?)
- هل تحتاج هب أم لا؟ دليل شامل (Do you need a hub?)
- أجهزة ذكية بدون نيوترال للبيوت القديمة (No-neutral devices for old homes)

**Pillar 2: Home Assistant بالعربي**
- ما هو Home Assistant وليه تحتاجه (What is HA and why you need it)
- تثبيت Home Assistant خطوة بخطوة (Installation guide)
- أفضل أجهزة متوافقة مع Home Assistant (Best compatible devices)
- أتمتة بيتك: سيناريوهات ذكية (Home automations: smart scenarios)

**Pillar 3: Security & Cameras (الأمان)**
- أفضل 5 كاميرات مراقبة للبيت (Top 5 home cameras)
- مقارنة: أقفال ذكية تناسب السعودية (Smart lock comparison for KSA)
- كيف تأمن بيتك بأقل من 500 ريال (Secure your home under 500 SAR)

**Pillar 4: Product Reviews (مراجعات)**
- مراجعة [Product] بالعربي (Arabic review)
- مقارنة [Product A] vs [Product B] (Comparison)
- تجربتي مع [Product] لمدة شهر (1-month experience)

### YouTube Integration
- Embed YouTube videos on product pages
- "شاهد الشرح" (Watch tutorial) button on products
- Blog posts link to corresponding videos
- Playlist pages: "دورة البيت الذكي" (Smart Home Course)

### Social Media
| Platform | Content Type | Frequency |
|----------|-------------|-----------|
| Twitter/X | Tips, deals, new products | Daily |
| Instagram | Product photos, reels, stories | 3-4x/week |
| TikTok | Quick tutorials, unboxing | 2-3x/week |
| YouTube | Full tutorials, reviews | 1-2x/week |
| WhatsApp Status | Flash deals, new arrivals | Daily |

---

## 10. Technical Checklist

### Current Plugins (24 installed)
Keep and optimize:
- Elementor Pro 3.34.4 (page builder)
- WooCommerce (e-commerce)
- WPForms (contact forms)
- WP Mail SMTP (email delivery)
- Bit File Manager (file access)
- Ally (accessibility)
- Stackable (blocks)

### Recommended Additional Plugins
| Plugin | Purpose | Priority |
|--------|---------|----------|
| RankMath/Yoast | SEO management | Critical |
| WP Rocket / LiteSpeed Cache | Performance | Critical |
| WPML or TranslatePress | Bilingual AR/EN | High |
| Tabby/Tamara for WooCommerce | BNPL payments | High |
| WooCommerce Product Filter | Shop filtering | High |
| Schema Pro | Structured data | Medium |
| UpdraftPlus | Backups | Critical |
| Wordfence | Security | Critical |
| WooCommerce WhatsApp | Order notifications | High |
| Smush/ShortPixel | Image optimization | High |

### Performance Targets
- **LCP**: < 2.5 seconds
- **FID**: < 100ms
- **CLS**: < 0.1
- **TTFB**: < 600ms
- **Mobile PageSpeed**: > 80
- **Desktop PageSpeed**: > 90

### Security
- SSL/TLS (already active via Blazr)
- Wordfence or Sucuri WAF
- Two-factor auth on wp-admin
- Regular automated backups
- Hide wp-login.php (rename login URL)
- Limit login attempts
- Disable XML-RPC (unless needed)

---

## 11. Launch Checklist

### Pre-Launch
- [ ] Replace all placeholder content on homepage
- [ ] Publish 559 draft products (review descriptions first)
- [ ] Set up proper category hierarchy as defined above
- [ ] Create all key pages (About, FAQ, Contact, Privacy, Terms)
- [ ] Configure payment gateways (Mada, Visa, Apple Pay, Tabby)
- [ ] Set up shipping zones and rates
- [ ] Install and configure SEO plugin
- [ ] Set up Google Analytics 4 + Search Console
- [ ] Test full purchase flow (add to cart -> checkout -> payment -> confirmation)
- [ ] Test mobile responsiveness
- [ ] Set up WhatsApp business integration
- [ ] Create email templates (order confirmation, shipping, etc.)
- [ ] Register on Maroof (Saudi e-commerce registry)

### Post-Launch (Week 1)
- [ ] Submit sitemap to Google
- [ ] Publish first 3 blog posts
- [ ] Set up social media accounts
- [ ] Send announcement to existing contacts
- [ ] Monitor for errors in WooCommerce logs
- [ ] Test page speed and optimize

### Ongoing
- [ ] Publish 2 blog posts per week
- [ ] Upload 1 YouTube video per week
- [ ] Monitor and respond to product reviews
- [ ] Analyze Google Analytics for top pages/products
- [ ] Update inventory and pricing monthly
- [ ] Run monthly promotional campaign
