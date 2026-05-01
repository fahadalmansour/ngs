#!/usr/bin/env python3.11
"""
Neogen Store Homepage Builder
Programmatically rebuilds the homepage (Post ID 2745) with all 9 blueprint sections
using Elementor's internal save mechanism via Playwright.

Usage:
    python3.11 build_homepage.py          # Build and deploy
    python3.11 build_homepage.py --dry-run # Show JSON without deploying
    python3.11 build_homepage.py --backup-only  # Just backup current data
"""

import asyncio
import argparse
import json
import os
import random
import sys

try:
    from playwright.async_api import async_playwright
except ImportError:
    print("ERROR: playwright required. Install: pip3.11 install playwright && python3.11 -m playwright install chromium")
    sys.exit(1)


# ============================================================
# CONFIGURATION
# ============================================================

def _load_env():
    for env_path in [
        os.path.join(os.path.dirname(__file__), ".env"),
        os.path.join(os.path.dirname(__file__), "..", ".env"),
    ]:
        if os.path.exists(env_path):
            with open(env_path) as f:
                for line in f:
                    line = line.strip()
                    if line and not line.startswith("#") and "=" in line:
                        key, _, value = line.partition("=")
                        os.environ.setdefault(key.strip(), value.strip())

_load_env()

WP_URL = os.environ.get("WP_URL", "https://neogen.store")
USERNAME = os.environ.get("WP_ADMIN_USER", "")
PASSWORD = os.environ.get("WP_ADMIN_PASSWORD", "")
POST_ID = 2745  # Homepage

SCREENSHOTS_DIR = os.path.join(os.path.dirname(__file__), "..", "screenshots")
BACKUP_FILE = os.path.join(os.path.dirname(__file__), "homepage_backup.json")
os.makedirs(SCREENSHOTS_DIR, exist_ok=True)

# Design System
COLORS = {
    "primary": "#1A3A5C",
    "primary_dark": "#0D2137",
    "secondary": "#00BFA6",
    "accent": "#FF6B35",
    "bg": "#F8F9FA",
    "white": "#FFFFFF",
    "text": "#1A1A2E",
    "text_light": "#6B7280",
    "success": "#10B981",
}

FONT_AR = "IBM Plex Sans Arabic"
FONT_EN = "Inter"


# ============================================================
# ELEMENTOR JSON BUILDERS
# ============================================================

def gen_id():
    """Generate Elementor-compatible element ID (7-8 hex chars)."""
    return format(random.randint(0x1000000, 0x7FFFFFFF), "x")


def make_section(columns, settings=None, is_inner=False):
    """Create an Elementor section element."""
    base_settings = {}
    if settings:
        base_settings.update(settings)
    return {
        "id": gen_id(),
        "elType": "section",
        "settings": base_settings,
        "elements": columns,
        "isInner": is_inner,
    }


def make_column(widgets, size=100, settings=None):
    """Create an Elementor column element."""
    base_settings = {"_column_size": size, "_inline_size": None}
    if settings:
        base_settings.update(settings)
    return {
        "id": gen_id(),
        "elType": "column",
        "settings": base_settings,
        "elements": widgets,
        "isInner": False,
    }


def make_widget(widget_type, settings):
    """Create an Elementor widget element."""
    return {
        "id": gen_id(),
        "elType": "widget",
        "widgetType": widget_type,
        "settings": settings,
        "elements": [],
    }


# ============================================================
# SECTION BUILDERS
# ============================================================

def build_hero_section():
    """Section 1: Hero Banner - full width, gradient background."""
    heading = make_widget("heading", {
        "title": "حوّل بيتك إلى بيت ذكي",
        "header_size": "h1",
        "align": "center",
        "title_color": COLORS["white"],
        "typography_typography": "custom",
        "typography_font_family": FONT_AR,
        "typography_font_size": {"size": 44, "unit": "px"},
        "typography_font_size_mobile": {"size": 28, "unit": "px"},
        "typography_font_weight": "700",
        "typography_line_height": {"size": 1.4, "unit": "em"},
    })

    subtitle = make_widget("heading", {
        "title": "بدون فني وبدون تعقيد - أجهزة ذكية مُختارة بعناية | دعم عربي 24/7 | شحن مجاني فوق 300 ريال",
        "header_size": "h3",
        "align": "center",
        "title_color": "#CBD5E1",
        "typography_typography": "custom",
        "typography_font_family": FONT_AR,
        "typography_font_size": {"size": 18, "unit": "px"},
        "typography_font_size_mobile": {"size": 14, "unit": "px"},
        "typography_font_weight": "400",
        "typography_line_height": {"size": 1.8, "unit": "em"},
    })

    spacer1 = make_widget("spacer", {"space": {"size": 20, "unit": "px"}})

    cta_primary = make_widget("button", {
        "text": "تسوّق الآن",
        "link": {"url": "/shop/", "is_external": False, "nofollow": False},
        "align": "center",
        "size": "lg",
        "button_type": "",
        "background_color": COLORS["secondary"],
        "button_text_color": COLORS["white"],
        "border_radius": {"size": 8, "unit": "px"},
        "typography_typography": "custom",
        "typography_font_family": FONT_AR,
        "typography_font_size": {"size": 18, "unit": "px"},
        "typography_font_weight": "600",
        "button_background_color": COLORS["secondary"],
    })

    cta_secondary = make_widget("button", {
        "text": "شاهد الدليل",
        "link": {"url": "/blog/", "is_external": False, "nofollow": False},
        "align": "center",
        "size": "lg",
        "button_type": "",
        "background_color": "transparent",
        "button_text_color": COLORS["white"],
        "border_border": "solid",
        "border_width": {"top": "2", "right": "2", "bottom": "2", "left": "2", "unit": "px"},
        "border_color": COLORS["white"],
        "border_radius": {"size": 8, "unit": "px"},
        "typography_typography": "custom",
        "typography_font_family": FONT_AR,
        "typography_font_size": {"size": 18, "unit": "px"},
        "typography_font_weight": "600",
    })

    # Buttons in inner section for side-by-side layout
    btn_section = make_section(
        [
            make_column([cta_primary], size=50),
            make_column([cta_secondary], size=50),
        ],
        settings={
            "layout": "full_width",
            "content_width": {"size": 500, "unit": "px"},
            "gap": "default",
        },
        is_inner=True,
    )

    col = make_column([heading, subtitle, spacer1, btn_section])

    return make_section([col], settings={
        "layout": "full_width",
        "content_width": {"size": 1140, "unit": "px"},
        "min_height": {"size": 500, "unit": "px"},
        "height": "min-height",
        "background_background": "gradient",
        "background_color": COLORS["primary"],
        "background_color_b": COLORS["primary_dark"],
        "background_gradient_angle": {"size": 135, "unit": "deg"},
        "padding": {
            "top": "80", "right": "20", "bottom": "80", "left": "20", "unit": "px",
        },
        "padding_mobile": {
            "top": "50", "right": "15", "bottom": "50", "left": "15", "unit": "px",
        },
    })


def build_trust_bar_section():
    """Section 2: Trust Bar - 4 icon columns."""
    trust_items = [
        {"icon": "fas fa-truck", "title": "شحن مجاني فوق 300 ر.س", "desc": "توصيل لباب بيتك خلال 7-14 يوم عمل"},
        {"icon": "fas fa-shield-alt", "title": "ضمان جودة المنتج", "desc": "منتجات أصلية من علامات تجارية موثوقة"},
        {"icon": "fab fa-whatsapp", "title": "دعم عربي عبر واتساب", "desc": "فريق تقني متخصص 24/7"},
        {"icon": "fas fa-undo-alt", "title": "إرجاع خلال 15 يوم", "desc": "سياسة إرجاع مرنة"},
    ]

    columns = []
    for item in trust_items:
        widget = make_widget("icon-box", {
            "selected_icon": {"value": item["icon"], "library": "fa-solid" if item["icon"].startswith("fas") else "fa-brands"},
            "title_text": item["title"],
            "description_text": item["desc"],
            "position": "top",
            "text_align": "center",
            "primary_color": COLORS["secondary"],
            "title_size": "h5",
            "title_color": COLORS["text"],
            "description_color": COLORS["text_light"],
            "icon_size": {"size": 36, "unit": "px"},
            "title_typography_typography": "custom",
            "title_typography_font_family": FONT_AR,
            "title_typography_font_size": {"size": 16, "unit": "px"},
            "title_typography_font_weight": "600",
            "description_typography_typography": "custom",
            "description_typography_font_family": FONT_AR,
            "description_typography_font_size": {"size": 13, "unit": "px"},
        })
        columns.append(make_column([widget], size=25))

    return make_section(columns, settings={
        "background_background": "classic",
        "background_color": COLORS["white"],
        "padding": {"top": "30", "right": "0", "bottom": "30", "left": "0", "unit": "px"},
        "border_border": "solid",
        "border_width": {"top": "0", "right": "0", "bottom": "1", "left": "0", "unit": "px"},
        "border_color": "#E5E7EB",
    })


def build_category_section():
    """Section 3: Shop by Category - 6 cards in 2 rows of 3."""
    categories = [
        {"icon": "fas fa-shield-alt", "name": "الأمان والكاميرات", "slug": "security-cameras", "desc": "كاميرات مراقبة، أقفال ذكية، أجراس"},
        {"icon": "fas fa-lightbulb", "name": "الإضاءة الذكية", "slug": "smart-lighting", "desc": "لمبات ذكية، شرائط LED، لوحات إضاءة"},
        {"icon": "fas fa-broadcast-tower", "name": "المستشعرات والمراكز", "slug": "sensors-hubs", "desc": "مستشعرات حركة، أبواب، حرارة، هبات"},
        {"icon": "fas fa-toggle-on", "name": "المفاتيح والكهربائيات", "slug": "switches-electrical", "desc": "مفاتيح ذكية، بلاقات، ريلاي، قواطع"},
        {"icon": "fas fa-thermometer-half", "name": "التحكم بالمناخ والطاقة", "slug": "climate-energy", "desc": "تحكم مكيف، ثرموستات، ستائر ذكية"},
        {"icon": "fas fa-wifi", "name": "الشبكات والصوت", "slug": "networking-audio", "desc": "راوترات، أكسس بوينت، مكبرات صوت"},
    ]

    # Section heading
    section_heading = make_widget("heading", {
        "title": "تسوّق حسب الفئة",
        "header_size": "h2",
        "align": "center",
        "title_color": COLORS["text"],
        "typography_typography": "custom",
        "typography_font_family": FONT_AR,
        "typography_font_size": {"size": 32, "unit": "px"},
        "typography_font_size_mobile": {"size": 24, "unit": "px"},
        "typography_font_weight": "700",
    })

    spacer = make_widget("spacer", {"space": {"size": 10, "unit": "px"}})

    heading_col = make_column([section_heading, spacer])
    heading_section = make_section([heading_col], settings={
        "background_background": "classic",
        "background_color": COLORS["bg"],
        "padding": {"top": "50", "right": "0", "bottom": "0", "left": "0", "unit": "px"},
    })

    # Row 1: first 3 categories
    row1_cols = []
    for cat in categories[:3]:
        widget = make_widget("icon-box", {
            "selected_icon": {"value": cat["icon"], "library": "fa-solid"},
            "title_text": cat["name"],
            "description_text": cat["desc"],
            "link": {"url": f"/product-category/{cat['slug']}/", "is_external": False},
            "position": "top",
            "text_align": "center",
            "primary_color": COLORS["primary"],
            "title_size": "h4",
            "title_color": COLORS["text"],
            "description_color": COLORS["text_light"],
            "icon_size": {"size": 48, "unit": "px"},
            "title_typography_typography": "custom",
            "title_typography_font_family": FONT_AR,
            "title_typography_font_size": {"size": 20, "unit": "px"},
            "title_typography_font_weight": "600",
            "description_typography_typography": "custom",
            "description_typography_font_family": FONT_AR,
            "description_typography_font_size": {"size": 14, "unit": "px"},
        })
        col_settings = {
            "background_background": "classic",
            "background_color": COLORS["white"],
            "border_radius": {"size": 12, "unit": "px"},
            "box_shadow_box_shadow_type": "yes",
            "box_shadow_box_shadow": {
                "horizontal": 0, "vertical": 2, "blur": 8, "spread": 0, "color": "rgba(0,0,0,0.08)",
            },
            "padding": {"top": "30", "right": "20", "bottom": "30", "left": "20", "unit": "px"},
            "margin": {"top": "0", "right": "10", "bottom": "20", "left": "10", "unit": "px"},
        }
        row1_cols.append(make_column([widget], size=33, settings=col_settings))

    row1 = make_section(row1_cols, settings={
        "background_background": "classic",
        "background_color": COLORS["bg"],
        "padding": {"top": "20", "right": "20", "bottom": "0", "left": "20", "unit": "px"},
    })

    # Row 2: last 3 categories
    row2_cols = []
    for cat in categories[3:]:
        widget = make_widget("icon-box", {
            "selected_icon": {"value": cat["icon"], "library": "fa-solid"},
            "title_text": cat["name"],
            "description_text": cat["desc"],
            "link": {"url": f"/product-category/{cat['slug']}/", "is_external": False},
            "position": "top",
            "text_align": "center",
            "primary_color": COLORS["primary"],
            "title_size": "h4",
            "title_color": COLORS["text"],
            "description_color": COLORS["text_light"],
            "icon_size": {"size": 48, "unit": "px"},
            "title_typography_typography": "custom",
            "title_typography_font_family": FONT_AR,
            "title_typography_font_size": {"size": 20, "unit": "px"},
            "title_typography_font_weight": "600",
            "description_typography_typography": "custom",
            "description_typography_font_family": FONT_AR,
            "description_typography_font_size": {"size": 14, "unit": "px"},
        })
        col_settings = {
            "background_background": "classic",
            "background_color": COLORS["white"],
            "border_radius": {"size": 12, "unit": "px"},
            "box_shadow_box_shadow_type": "yes",
            "box_shadow_box_shadow": {
                "horizontal": 0, "vertical": 2, "blur": 8, "spread": 0, "color": "rgba(0,0,0,0.08)",
            },
            "padding": {"top": "30", "right": "20", "bottom": "30", "left": "20", "unit": "px"},
            "margin": {"top": "0", "right": "10", "bottom": "20", "left": "10", "unit": "px"},
        }
        row2_cols.append(make_column([widget], size=33, settings=col_settings))

    row2 = make_section(row2_cols, settings={
        "background_background": "classic",
        "background_color": COLORS["bg"],
        "padding": {"top": "0", "right": "20", "bottom": "50", "left": "20", "unit": "px"},
    })

    return [heading_section, row1, row2]


def build_bestsellers_section():
    """Section 4: Best Sellers - product carousel via WooCommerce shortcode."""
    heading = make_widget("heading", {
        "title": "الأكثر مبيعاً",
        "header_size": "h2",
        "align": "center",
        "title_color": COLORS["text"],
        "typography_typography": "custom",
        "typography_font_family": FONT_AR,
        "typography_font_size": {"size": 32, "unit": "px"},
        "typography_font_size_mobile": {"size": 24, "unit": "px"},
        "typography_font_weight": "700",
    })

    spacer = make_widget("spacer", {"space": {"size": 10, "unit": "px"}})

    products = make_widget("shortcode", {
        "shortcode": '[products limit="8" columns="4" best_selling="true" orderby="popularity"]',
    })

    col = make_column([heading, spacer, products])

    return make_section([col], settings={
        "background_background": "classic",
        "background_color": COLORS["white"],
        "padding": {"top": "50", "right": "20", "bottom": "50", "left": "20", "unit": "px"},
    })


def build_why_neogen_section():
    """Section 5: Why Neogen - 3 value proposition columns."""
    items = [
        {
            "icon": "fas fa-home",
            "title": "متخصصون في Home Assistant",
            "desc": "نحن الوحيدون في السعودية المتخصصون في أنظمة Home Assistant. نختار أجهزة متوافقة ومجربة تعمل بشكل مثالي مع بيتك الذكي.",
        },
        {
            "icon": "fas fa-headset",
            "title": "دعم عربي حقيقي",
            "desc": "مش بوت - فريق تقني يرد عليك عبر واتساب بالعربي، يساعدك من اختيار الجهاز المناسب إلى التركيب والإعداد.",
        },
        {
            "icon": "fas fa-graduation-cap",
            "title": "محتوى تعليمي مجاني",
            "desc": "فيديوهات يوتيوب ومقالات بالعربي تشرح لك كل خطوة. حوّل بيتك بنفسك بدون ما تحتاج فني.",
        },
    ]

    heading = make_widget("heading", {
        "title": "لماذا نيوجين؟",
        "header_size": "h2",
        "align": "center",
        "title_color": COLORS["white"],
        "typography_typography": "custom",
        "typography_font_family": FONT_AR,
        "typography_font_size": {"size": 32, "unit": "px"},
        "typography_font_size_mobile": {"size": 24, "unit": "px"},
        "typography_font_weight": "700",
    })

    spacer = make_widget("spacer", {"space": {"size": 20, "unit": "px"}})

    heading_col = make_column([heading, spacer])
    heading_section = make_section([heading_col], settings={
        "background_background": "gradient",
        "background_color": COLORS["primary"],
        "background_color_b": COLORS["primary_dark"],
        "background_gradient_angle": {"size": 135, "unit": "deg"},
        "padding": {"top": "50", "right": "0", "bottom": "0", "left": "0", "unit": "px"},
    })

    columns = []
    for item in items:
        widget = make_widget("icon-box", {
            "selected_icon": {"value": item["icon"], "library": "fa-solid"},
            "title_text": item["title"],
            "description_text": item["desc"],
            "position": "top",
            "text_align": "center",
            "primary_color": COLORS["secondary"],
            "title_size": "h4",
            "title_color": COLORS["white"],
            "description_color": "#CBD5E1",
            "icon_size": {"size": 44, "unit": "px"},
            "title_typography_typography": "custom",
            "title_typography_font_family": FONT_AR,
            "title_typography_font_size": {"size": 22, "unit": "px"},
            "title_typography_font_weight": "600",
            "description_typography_typography": "custom",
            "description_typography_font_family": FONT_AR,
            "description_typography_font_size": {"size": 15, "unit": "px"},
            "description_typography_line_height": {"size": 1.8, "unit": "em"},
        })
        columns.append(make_column([widget], size=33))

    values_section = make_section(columns, settings={
        "background_background": "gradient",
        "background_color": COLORS["primary"],
        "background_color_b": COLORS["primary_dark"],
        "background_gradient_angle": {"size": 135, "unit": "deg"},
        "padding": {"top": "10", "right": "20", "bottom": "60", "left": "20", "unit": "px"},
    })

    return [heading_section, values_section]


def build_bundle_section():
    """Section 6: Featured Bundle / Starter Kit."""
    heading = make_widget("heading", {
        "title": "باقة البيت الذكي للمبتدئين",
        "header_size": "h2",
        "align": "center",
        "title_color": COLORS["white"],
        "typography_typography": "custom",
        "typography_font_family": FONT_AR,
        "typography_font_size": {"size": 32, "unit": "px"},
        "typography_font_size_mobile": {"size": 24, "unit": "px"},
        "typography_font_weight": "700",
    })

    subtitle = make_widget("heading", {
        "title": "Smart Home Starter Kit",
        "header_size": "h4",
        "align": "center",
        "title_color": COLORS["secondary"],
        "typography_typography": "custom",
        "typography_font_family": FONT_EN,
        "typography_font_size": {"size": 18, "unit": "px"},
        "typography_font_weight": "500",
    })

    desc = make_widget("text-editor", {
        "editor": """<p style="text-align: center; color: #CBD5E1; font-size: 16px; line-height: 1.8;">
Hub + 2 مستشعرات + لمبة ذكية + بلاقة ذكية<br>
ابدأ رحلتك في عالم البيت الذكي بأفضل الأجهزة المتوافقة مع Home Assistant
</p>""",
    })

    spacer = make_widget("spacer", {"space": {"size": 10, "unit": "px"}})

    cta = make_widget("button", {
        "text": "تصفّح الباقات",
        "link": {"url": "/product-category/pre-configured-bundles/", "is_external": False},
        "align": "center",
        "size": "lg",
        "background_color": COLORS["accent"],
        "button_text_color": COLORS["white"],
        "border_radius": {"size": 8, "unit": "px"},
        "typography_typography": "custom",
        "typography_font_family": FONT_AR,
        "typography_font_size": {"size": 18, "unit": "px"},
        "typography_font_weight": "600",
        "button_background_color": COLORS["accent"],
    })

    col = make_column([heading, subtitle, desc, spacer, cta])

    return make_section([col], settings={
        "layout": "full_width",
        "background_background": "gradient",
        "background_color": COLORS["primary"],
        "background_color_b": "#0A1628",
        "background_gradient_angle": {"size": 180, "unit": "deg"},
        "padding": {"top": "60", "right": "20", "bottom": "60", "left": "20", "unit": "px"},
    })


def build_blog_section():
    """Section 7: Latest Blog Posts."""
    heading = make_widget("heading", {
        "title": "من مدونتنا",
        "header_size": "h2",
        "align": "center",
        "title_color": COLORS["text"],
        "typography_typography": "custom",
        "typography_font_family": FONT_AR,
        "typography_font_size": {"size": 32, "unit": "px"},
        "typography_font_size_mobile": {"size": 24, "unit": "px"},
        "typography_font_weight": "700",
    })

    spacer = make_widget("spacer", {"space": {"size": 10, "unit": "px"}})

    # Use HTML widget with WP shortcode for latest posts
    blog_html = make_widget("shortcode", {
        "shortcode": '[recent_posts posts_per_page="3"]',
    })

    # Fallback: use raw HTML with custom CSS to show latest posts
    blog_fallback = make_widget("html", {
        "html": """<style>
.neo-blog-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; max-width: 1140px; margin: 0 auto; }
.neo-blog-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); transition: transform 0.2s; }
.neo-blog-card:hover { transform: translateY(-4px); }
.neo-blog-card img { width: 100%; height: 200px; object-fit: cover; }
.neo-blog-card-body { padding: 20px; }
.neo-blog-card h3 { font-family: 'IBM Plex Sans Arabic', sans-serif; font-size: 18px; color: #1A1A2E; margin: 0 0 8px; }
.neo-blog-card p { font-family: 'IBM Plex Sans Arabic', sans-serif; font-size: 14px; color: #6B7280; line-height: 1.6; }
.neo-blog-card a { color: #1A3A5C; font-family: 'IBM Plex Sans Arabic', sans-serif; font-weight: 600; text-decoration: none; }
@media (max-width: 768px) { .neo-blog-grid { grid-template-columns: 1fr; } }
</style>
<div class="neo-blog-grid">
  <div class="neo-blog-card">
    <div class="neo-blog-card-body">
      <h3>كيف تبدأ بيتك الذكي من الصفر</h3>
      <p>دليل شامل للمبتدئين في عالم البيت الذكي - من اختيار الأجهزة إلى الإعداد</p>
      <a href="/blog/">اقرأ المزيد ←</a>
    </div>
  </div>
  <div class="neo-blog-card">
    <div class="neo-blog-card-body">
      <h3>ما هو Home Assistant وليه تحتاجه</h3>
      <p>تعرّف على أقوى نظام تحكم بالبيت الذكي وكيف يجمع كل أجهزتك في مكان واحد</p>
      <a href="/blog/">اقرأ المزيد ←</a>
    </div>
  </div>
  <div class="neo-blog-card">
    <div class="neo-blog-card-body">
      <h3>أفضل 5 كاميرات مراقبة للبيت</h3>
      <p>مقارنة شاملة لأفضل كاميرات المراقبة المتوافقة مع Home Assistant</p>
      <a href="/blog/">اقرأ المزيد ←</a>
    </div>
  </div>
</div>""",
    })

    col = make_column([heading, spacer, blog_fallback])

    return make_section([col], settings={
        "background_background": "classic",
        "background_color": COLORS["bg"],
        "padding": {"top": "50", "right": "20", "bottom": "50", "left": "20", "unit": "px"},
    })


def build_brands_section():
    """Section 8: Brand Logos marquee."""
    heading = make_widget("heading", {
        "title": "العلامات التجارية",
        "header_size": "h2",
        "align": "center",
        "title_color": COLORS["text"],
        "typography_typography": "custom",
        "typography_font_family": FONT_AR,
        "typography_font_size": {"size": 32, "unit": "px"},
        "typography_font_size_mobile": {"size": 24, "unit": "px"},
        "typography_font_weight": "700",
    })

    spacer = make_widget("spacer", {"space": {"size": 20, "unit": "px"}})

    brands = [
        "Aqara", "Sonoff", "Philips Hue", "Reolink", "Shelly",
        "SwitchBot", "Ubiquiti", "Nanoleaf", "Google Nest",
        "Apple", "IKEA", "Eufy",
    ]
    brand_spans = " &nbsp;&bull;&nbsp; ".join(
        f'<span style="font-family: Inter, sans-serif; font-size: 20px; font-weight: 600; color: #6B7280;">{b}</span>'
        for b in brands
    )
    # Double the content for seamless loop
    brand_html = make_widget("html", {
        "html": f"""<style>
@keyframes neo-scroll {{ 0% {{ transform: translateX(0); }} 100% {{ transform: translateX(-50%); }} }}
.neo-brands-marquee {{ overflow: hidden; white-space: nowrap; padding: 20px 0; }}
.neo-brands-track {{ display: inline-block; animation: neo-scroll 30s linear infinite; }}
.neo-brands-track:hover {{ animation-play-state: paused; }}
</style>
<div class="neo-brands-marquee">
  <div class="neo-brands-track">
    {brand_spans} &nbsp;&bull;&nbsp; {brand_spans}
  </div>
</div>""",
    })

    col = make_column([heading, spacer, brand_html])

    return make_section([col], settings={
        "background_background": "classic",
        "background_color": COLORS["white"],
        "padding": {"top": "40", "right": "0", "bottom": "40", "left": "0", "unit": "px"},
        "border_border": "solid",
        "border_width": {"top": "1", "right": "0", "bottom": "0", "left": "0", "unit": "px"},
        "border_color": "#E5E7EB",
    })


def build_all_sections():
    """Assemble all homepage sections into the Elementor data array."""
    sections = []

    # Section 1: Hero
    sections.append(build_hero_section())

    # Section 2: Trust Bar
    sections.append(build_trust_bar_section())

    # Section 3: Categories (returns list of 3 sections)
    sections.extend(build_category_section())

    # Section 4: Best Sellers
    sections.append(build_bestsellers_section())

    # Section 5: Why Neogen (returns list of 2 sections)
    sections.extend(build_why_neogen_section())

    # Section 6: Featured Bundle
    sections.append(build_bundle_section())

    # Section 7: Blog Posts
    sections.append(build_blog_section())

    # Section 8: Brand Logos
    sections.append(build_brands_section())

    return sections


# ============================================================
# PLAYWRIGHT DEPLOY
# ============================================================

async def deploy(elementor_data):
    """Login to WordPress and save homepage via Elementor AJAX."""
    print("=== Neogen Homepage Builder ===")
    print(f"Target: {WP_URL} (Post #{POST_ID})")
    print(f"Sections: {len(elementor_data)}")
    print()

    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        ctx = await browser.new_context(
            viewport={"width": 1920, "height": 1080},
            ignore_https_errors=True,
        )
        page = await ctx.new_page()
        page.set_default_timeout(60000)

        # Step 1: Login
        print("1. Logging in to WordPress...")
        await page.goto(f"{WP_URL}/wp-login.php", wait_until="domcontentloaded")
        await page.wait_for_selector("#user_login")
        await page.fill("#user_login", USERNAME)
        await page.fill("#user_pass", PASSWORD)
        await page.click("#wp-submit")
        try:
            await page.wait_for_url(f"**{WP_URL}/wp-admin/**", timeout=30000)
        except Exception:
            if "/wp-admin" not in page.url:
                print(f"   Login failed. URL: {page.url}")
                await browser.close()
                return False
        print("   OK")

        # Step 2: Open Elementor editor
        print("2. Opening Elementor editor for homepage...")
        url = f"{WP_URL}/wp-admin/post.php?post={POST_ID}&action=elementor"
        await page.goto(url, wait_until="domcontentloaded")

        print("   Waiting for Elementor to initialize...")
        loaded = False
        for attempt in range(25):
            await page.wait_for_timeout(2000)
            check = await page.evaluate("""
                () => {
                    if (typeof elementor === 'undefined') return 'no_elementor';
                    if (!elementor.documents) return 'no_documents';
                    const doc = elementor.documents.getCurrent();
                    if (!doc) return 'no_current_doc';
                    return 'ready';
                }
            """)
            if attempt % 5 == 0:
                print(f"   Attempt {attempt + 1}: {check}")
            if check == "ready":
                loaded = True
                break

        if not loaded:
            print("   Elementor did not fully load. Taking screenshot...")
            await page.screenshot(path=os.path.join(SCREENSHOTS_DIR, "elementor_load_fail.png"))
            await browser.close()
            return False
        print("   Elementor ready")

        # Step 3: Get nonces
        print("3. Extracting nonces...")
        nonces = await page.evaluate("""
            () => {
                const result = {};
                if (typeof wpApiSettings !== 'undefined') result.wpNonce = wpApiSettings.nonce;
                if (typeof elementor !== 'undefined') {
                    result.elementorNonce = elementor.config?.nonce;
                    result.ajaxNonce = elementor.config?.document?.nonce;
                }
                if (typeof elementorCommon !== 'undefined') result.commonNonce = elementorCommon.config?.ajax?.nonce;
                return result;
            }
        """)
        print(f"   Got {len(nonces)} nonces")

        # Step 4: Backup current data
        print("4. Backing up current homepage data...")
        backup = await page.evaluate("""
            async (params) => {
                const resp = await fetch(
                    `/wp-json/wp/v2/pages/${params.postId}?context=edit`,
                    { headers: { 'X-WP-Nonce': params.nonce } }
                );
                if (!resp.ok) return { error: resp.status };
                const post = await resp.json();
                return post.meta?._elementor_data || '[]';
            }
        """, {"postId": POST_ID, "nonce": nonces.get("wpNonce", "")})

        if isinstance(backup, str):
            with open(BACKUP_FILE, "w", encoding="utf-8") as f:
                f.write(backup)
            print(f"   Saved to {BACKUP_FILE}")
        else:
            print(f"   Backup warning: {backup}")

        # Step 5: Save new data via Elementor AJAX
        print("5. Saving new homepage design...")
        data_json = json.dumps(elementor_data, ensure_ascii=False)

        save_result = await page.evaluate("""
            async (params) => {
                const { postId, nonces, dataJson } = params;
                const ajaxNonce = nonces.commonNonce || nonces.elementorNonce;
                if (!ajaxNonce) return { error: 'no elementor nonce available' };

                const formData = new FormData();
                formData.append('action', 'elementor_ajax');
                formData.append('editor_post_id', postId);
                formData.append('_nonce', ajaxNonce);

                const actions = {
                    save_builder: {
                        action: 'save_builder',
                        data: {
                            status: 'publish',
                            elements: dataJson
                        }
                    }
                };
                formData.append('actions', JSON.stringify(actions));

                const resp = await fetch('/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData,
                });

                const text = await resp.text();
                return {
                    status: resp.status,
                    ok: resp.ok,
                    response: text.substring(0, 500),
                };
            }
        """, {"postId": POST_ID, "nonces": nonces, "dataJson": data_json})

        print(f"   Save result: status={save_result.get('status')}, ok={save_result.get('ok')}")
        if not save_result.get("ok"):
            print(f"   Response: {save_result.get('response', 'N/A')}")

        await page.wait_for_timeout(3000)

        # Step 6: Verify on frontend
        print("6. Verifying homepage...")
        vp = await ctx.new_page()
        await vp.goto(f"{WP_URL}/?nocache={random.randint(1000,9999)}", wait_until="domcontentloaded")
        await vp.wait_for_timeout(3000)

        html = await vp.content()

        # Take full-page screenshot
        await vp.screenshot(
            path=os.path.join(SCREENSHOTS_DIR, "homepage_redesign.png"),
            full_page=True,
        )
        print(f"   Screenshot: {SCREENSHOTS_DIR}/homepage_redesign.png")

        # Check for key Arabic content
        checks = {
            "Hero": "حوّل بيتك" in html,
            "Trust Bar": "ضمان سنة" in html,
            "Categories": "تسوّق حسب الفئة" in html,
            "Best Sellers": "الأكثر مبيعاً" in html,
            "Why Neogen": "لماذا نيوجين" in html,
            "Bundle": "باقة البيت الذكي" in html,
            "Blog": "من مدونتنا" in html,
            "Brands": "العلامات التجارية" in html,
        }

        print("\n   Section verification:")
        all_ok = True
        for section, found in checks.items():
            status = "OK" if found else "MISSING"
            print(f"     {section}: {status}")
            if not found:
                all_ok = False

        if all_ok:
            print("\n   ALL SECTIONS VERIFIED SUCCESSFULLY")
        else:
            print("\n   WARNING: Some sections may not have rendered yet (check screenshot)")

        await browser.close()
        return all_ok


# ============================================================
# MAIN
# ============================================================

def main():
    parser = argparse.ArgumentParser(description="Neogen Homepage Builder")
    parser.add_argument("--dry-run", action="store_true", help="Show JSON without deploying")
    parser.add_argument("--backup-only", action="store_true", help="Only backup current data")
    args = parser.parse_args()

    if not USERNAME or not PASSWORD:
        print("ERROR: Missing WP_ADMIN_USER or WP_ADMIN_PASSWORD in .env")
        sys.exit(1)

    elementor_data = build_all_sections()

    if args.dry_run:
        print(f"Generated {len(elementor_data)} sections")
        output = json.dumps(elementor_data, ensure_ascii=False, indent=2)
        preview_file = os.path.join(os.path.dirname(__file__), "homepage_preview.json")
        with open(preview_file, "w", encoding="utf-8") as f:
            f.write(output)
        print(f"Preview saved to: {preview_file}")
        print(f"Total JSON size: {len(output):,} chars")
        return

    if args.backup_only:
        print("Backup-only mode - fetching current data...")
        asyncio.run(deploy.__wrapped__(elementor_data) if hasattr(deploy, '__wrapped__') else _backup_only())
        return

    success = asyncio.run(deploy(elementor_data))
    if success:
        print("\nHomepage redesign deployed successfully!")
    else:
        print("\nDeployment had issues. Check screenshots for details.")


async def _backup_only():
    """Just backup the current homepage data."""
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        ctx = await browser.new_context(ignore_https_errors=True)
        page = await ctx.new_page()
        page.set_default_timeout(60000)

        await page.goto(f"{WP_URL}/wp-login.php", wait_until="domcontentloaded")
        await page.wait_for_selector("#user_login")
        await page.fill("#user_login", USERNAME)
        await page.fill("#user_pass", PASSWORD)
        await page.click("#wp-submit")
        await page.wait_for_url(f"**{WP_URL}/wp-admin/**", timeout=30000)

        url = f"{WP_URL}/wp-admin/post.php?post={POST_ID}&action=elementor"
        await page.goto(url, wait_until="domcontentloaded")

        for _ in range(25):
            await page.wait_for_timeout(2000)
            check = await page.evaluate("() => typeof wpApiSettings !== 'undefined' ? 'ready' : 'waiting'")
            if check == "ready":
                break

        nonce = await page.evaluate("() => wpApiSettings?.nonce || ''")
        backup = await page.evaluate("""
            async (params) => {
                const resp = await fetch(
                    `/wp-json/wp/v2/pages/${params.postId}?context=edit`,
                    { headers: { 'X-WP-Nonce': params.nonce } }
                );
                const post = await resp.json();
                return post.meta?._elementor_data || '[]';
            }
        """, {"postId": POST_ID, "nonce": nonce})

        with open(BACKUP_FILE, "w", encoding="utf-8") as f:
            f.write(backup if isinstance(backup, str) else json.dumps(backup, ensure_ascii=False))
        print(f"Backup saved to: {BACKUP_FILE}")

        await browser.close()


if __name__ == "__main__":
    main()
