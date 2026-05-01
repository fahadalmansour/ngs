#!/usr/bin/env python3.11
"""
Neogen Store Layout Overhaul - Master Deployment Script

Deploys the rewritten neogen-theme-customizer plugin to the live site,
rebuilds the homepage, creates missing pages (About, FAQ, Contact).

Usage:
    python3.11 overhaul_store.py --plugin     # Deploy plugin only
    python3.11 overhaul_store.py --homepage   # Redeploy homepage
    python3.11 overhaul_store.py --footer     # Rebuild footer
    python3.11 overhaul_store.py --pages      # Create About/FAQ/Contact
    python3.11 overhaul_store.py --all        # Everything
    python3.11 overhaul_store.py --verify     # Just take screenshots
"""

import asyncio
import argparse
import json
import os
import random
import sys
import time

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

PLUGIN_FILE = os.path.join(
    os.path.dirname(__file__), "..",
    "Website", "wp-content", "plugins",
    "neogen-theme-customizer", "neogen-theme-customizer.php"
)
SCREENSHOTS_DIR = os.path.join(os.path.dirname(__file__), "..", "screenshots")
os.makedirs(SCREENSHOTS_DIR, exist_ok=True)

HOMEPAGE_ID = 2745
FOOTER_ID = 2681


# ============================================================
# SHARED: Login + Elementor helpers
# ============================================================

async def login(page):
    """Login to WordPress admin."""
    print("  Logging in...")
    await page.goto(f"{WP_URL}/wp-login.php", wait_until="domcontentloaded")
    await page.wait_for_selector("#user_login", timeout=30000)
    await page.fill("#user_login", USERNAME)
    await page.fill("#user_pass", PASSWORD)
    await page.click("#wp-submit")
    try:
        await page.wait_for_url("**wp-admin**", timeout=30000)
    except Exception:
        if "wp-admin" not in page.url:
            print(f"  Login FAILED. URL: {page.url}")
            return False
    print("  Login OK")
    return True


async def wait_for_elementor(page):
    """Wait for Elementor editor to fully initialize."""
    print("  Waiting for Elementor...")
    for attempt in range(30):
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
            print(f"    Attempt {attempt + 1}: {check}")
        if check == "ready":
            print("  Elementor ready")
            return True
    print("  Elementor failed to load")
    return False


async def get_nonces(page):
    """Extract WordPress and Elementor nonces."""
    return await page.evaluate("""
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


async def save_elementor(page, post_id, elementor_data, nonces):
    """Save Elementor data via AJAX."""
    data_json = json.dumps(elementor_data, ensure_ascii=False)
    result = await page.evaluate("""
        async (params) => {
            const { postId, nonces, dataJson } = params;
            const ajaxNonce = nonces.commonNonce || nonces.elementorNonce;
            if (!ajaxNonce) return { error: 'no elementor nonce' };

            const formData = new FormData();
            formData.append('action', 'elementor_ajax');
            formData.append('editor_post_id', postId);
            formData.append('_nonce', ajaxNonce);
            formData.append('actions', JSON.stringify({
                save_builder: {
                    action: 'save_builder',
                    data: { status: 'publish', elements: dataJson }
                }
            }));

            const resp = await fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                body: formData,
            });
            return { status: resp.status, ok: resp.ok, response: (await resp.text()).substring(0, 500) };
        }
    """, {"postId": post_id, "nonces": nonces, "dataJson": data_json})
    return result


# ============================================================
# PHASE 1: Deploy Plugin
# ============================================================

async def deploy_plugin():
    """Deploy the rewritten neogen-theme-customizer plugin via WP Plugin Editor."""
    print("\n=== PHASE 1: Deploy Theme Customizer Plugin ===")

    # Read the local plugin file
    if not os.path.exists(PLUGIN_FILE):
        print(f"  ERROR: Plugin file not found: {PLUGIN_FILE}")
        return False

    with open(PLUGIN_FILE, "r", encoding="utf-8") as f:
        plugin_content = f.read()

    print(f"  Plugin file: {len(plugin_content):,} chars")

    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        ctx = await browser.new_context(
            viewport={"width": 1920, "height": 1080},
            ignore_https_errors=True,
        )
        page = await ctx.new_page()
        page.set_default_timeout(60000)

        if not await login(page):
            await browser.close()
            return False

        # Navigate to plugin editor
        print("  Opening Plugin Editor...")
        editor_url = f"{WP_URL}/wp-admin/plugin-editor.php?file=neogen-theme-customizer%2Fneogen-theme-customizer.php&plugin=neogen-theme-customizer%2Fneogen-theme-customizer.php"
        await page.goto(editor_url, wait_until="domcontentloaded")
        await page.wait_for_timeout(3000)

        # Check if CodeMirror editor is available
        has_editor = await page.evaluate("""
            () => {
                if (typeof wp !== 'undefined' && wp.codeEditor) return 'codemirror';
                var textarea = document.getElementById('newcontent');
                if (textarea) return 'textarea';
                return 'none';
            }
        """)
        print(f"  Editor type: {has_editor}")

        if has_editor == 'codemirror':
            # Use CodeMirror API to set content
            await page.evaluate("""
                (content) => {
                    var editors = document.querySelectorAll('.CodeMirror');
                    if (editors.length > 0) {
                        var cm = editors[0].CodeMirror;
                        cm.setValue(content);
                        cm.save();
                    }
                }
            """, plugin_content)
        elif has_editor == 'textarea':
            # Direct textarea
            await page.evaluate("""
                (content) => {
                    var ta = document.getElementById('newcontent');
                    if (ta) {
                        ta.value = content;
                    }
                }
            """, plugin_content)
        else:
            print("  ERROR: Could not find editor on page")
            await page.screenshot(path=os.path.join(SCREENSHOTS_DIR, "plugin_editor_error.png"))
            await browser.close()
            return False

        # Dismiss the "file editor warning" dialog if present
        warning_btn = await page.query_selector('#file-editor-warning .file-editor-warning-dismiss')
        if not warning_btn:
            warning_btn = await page.query_selector('.file-editor-warning-dismiss')
        if not warning_btn:
            warning_btn = await page.query_selector('#file-editor-warning button')
        if warning_btn:
            print("  Dismissing file editor warning...")
            await warning_btn.click()
            await page.wait_for_timeout(1000)
        else:
            # Try clicking the dialog background to dismiss
            await page.evaluate("""
                () => {
                    var warning = document.getElementById('file-editor-warning');
                    if (warning) {
                        // Find any button inside the warning
                        var btns = warning.querySelectorAll('button, .button, a.button');
                        for (var b of btns) {
                            b.click();
                            return 'clicked ' + b.textContent;
                        }
                        // Or just hide it
                        warning.style.display = 'none';
                        var bg = warning.querySelector('.notification-dialog-background');
                        if (bg) bg.style.display = 'none';
                        return 'hidden';
                    }
                    return 'not found';
                }
            """)
            await page.wait_for_timeout(500)

        # Click "Update File" button
        print("  Saving plugin...")
        submit_btn = await page.query_selector('#submit')
        if not submit_btn:
            submit_btn = await page.query_selector('input[name="submit"]')
        if not submit_btn:
            submit_btn = await page.query_selector('button[type="submit"]')

        if submit_btn:
            await submit_btn.click()
            await page.wait_for_timeout(5000)

            # Check for errors
            error = await page.query_selector('.error, .notice-error')
            if error:
                error_text = await error.text_content()
                print(f"  ERROR after save: {error_text[:200]}")
                await page.screenshot(path=os.path.join(SCREENSHOTS_DIR, "plugin_save_error.png"))
                await browser.close()
                return False

            print("  Plugin saved successfully!")
        else:
            print("  ERROR: Could not find submit button")
            await page.screenshot(path=os.path.join(SCREENSHOTS_DIR, "plugin_no_submit.png"))
            await browser.close()
            return False

        # Verify on frontend
        print("  Verifying on frontend...")
        vp = await ctx.new_page()
        await vp.goto(f"{WP_URL}/?nocache={random.randint(1000,9999)}", wait_until="domcontentloaded")
        await vp.wait_for_timeout(3000)

        # Check that new CSS variables are present
        check = await vp.evaluate("""
            () => {
                var style = getComputedStyle(document.documentElement);
                return {
                    primary: style.getPropertyValue('--neo-primary').trim(),
                    secondary: style.getPropertyValue('--neo-secondary').trim(),
                    hasMobileNav: !!document.getElementById('neogen-mobile-nav'),
                    hasPromoBar: !!document.getElementById('neogen-promo-bar'),
                    hasMegaMenu: !!document.getElementById('neogen-mega-menu'),
                };
            }
        """)
        print(f"  Verification: {json.dumps(check, indent=2)}")

        await vp.screenshot(
            path=os.path.join(SCREENSHOTS_DIR, "plugin_deployed.png"),
            full_page=True,
        )
        print(f"  Screenshot: {SCREENSHOTS_DIR}/plugin_deployed.png")

        await browser.close()
        return check.get('primary') == '#1A3A5C' or check.get('hasMobileNav')


# ============================================================
# PHASE 2: Redeploy Homepage
# ============================================================

async def deploy_homepage():
    """Redeploy the homepage via build_homepage.py."""
    print("\n=== PHASE 2: Redeploy Homepage ===")

    # Import build_homepage functions
    sys.path.insert(0, os.path.dirname(__file__))
    try:
        from build_homepage import build_all_sections, deploy
        elementor_data = build_all_sections()
        print(f"  Generated {len(elementor_data)} sections")
        success = await deploy(elementor_data)
        return success
    except Exception as e:
        print(f"  ERROR: {e}")
        return False


# ============================================================
# PHASE 3: Rebuild Footer
# ============================================================

def gen_id():
    return format(random.randint(0x1000000, 0x7FFFFFFF), "x")

def make_section(columns, settings=None, is_inner=False):
    base = {}
    if settings:
        base.update(settings)
    return {"id": gen_id(), "elType": "section", "settings": base, "elements": columns, "isInner": is_inner}

def make_column(widgets, size=100, settings=None):
    base = {"_column_size": size, "_inline_size": None}
    if settings:
        base.update(settings)
    return {"id": gen_id(), "elType": "column", "settings": base, "elements": widgets, "isInner": False}

def make_widget(widget_type, settings):
    return {"id": gen_id(), "elType": "widget", "widgetType": widget_type, "settings": settings, "elements": []}


def build_footer_sections():
    """Build a 4-column footer + bottom bar."""

    # Column 1: Quick Links
    quick_links = make_widget("text-editor", {
        "editor": """<h4 style="color:#fff;font-size:16px;font-weight:600;margin-bottom:16px;font-family:'IBM Plex Sans Arabic',sans-serif;">روابط سريعة</h4>
<ul style="list-style:none;padding:0;margin:0;">
<li style="margin-bottom:8px;"><a href="/shop/" style="color:rgba(255,255,255,0.7);text-decoration:none;font-size:14px;">المتجر</a></li>
<li style="margin-bottom:8px;"><a href="/about/" style="color:rgba(255,255,255,0.7);text-decoration:none;font-size:14px;">من نحن</a></li>
<li style="margin-bottom:8px;"><a href="/faq/" style="color:rgba(255,255,255,0.7);text-decoration:none;font-size:14px;">الأسئلة الشائعة</a></li>
<li style="margin-bottom:8px;"><a href="/blog/" style="color:rgba(255,255,255,0.7);text-decoration:none;font-size:14px;">المدونة</a></li>
</ul>""",
    })

    # Column 2: Customer Service
    service_links = make_widget("text-editor", {
        "editor": """<h4 style="color:#fff;font-size:16px;font-weight:600;margin-bottom:16px;font-family:'IBM Plex Sans Arabic',sans-serif;">خدمة العملاء</h4>
<ul style="list-style:none;padding:0;margin:0;">
<li style="margin-bottom:8px;"><a href="/contact/" style="color:rgba(255,255,255,0.7);text-decoration:none;font-size:14px;">تواصل معنا</a></li>
<li style="margin-bottom:8px;"><a href="/shipping-policy/" style="color:rgba(255,255,255,0.7);text-decoration:none;font-size:14px;">سياسة الشحن</a></li>
<li style="margin-bottom:8px;"><a href="/return-policy/" style="color:rgba(255,255,255,0.7);text-decoration:none;font-size:14px;">سياسة الإرجاع</a></li>
<li style="margin-bottom:8px;"><a href="/privacy-policy/" style="color:rgba(255,255,255,0.7);text-decoration:none;font-size:14px;">سياسة الخصوصية</a></li>
</ul>""",
    })

    # Column 3: Contact Info
    contact_info = make_widget("text-editor", {
        "editor": """<h4 style="color:#fff;font-size:16px;font-weight:600;margin-bottom:16px;font-family:'IBM Plex Sans Arabic',sans-serif;">تواصل معنا</h4>
<p style="color:rgba(255,255,255,0.7);font-size:14px;margin-bottom:10px;">📱 واتساب: <a href="https://wa.me/966500000000" style="color:#00BFA6;text-decoration:none;">تحدث معنا</a></p>
<p style="color:rgba(255,255,255,0.7);font-size:14px;margin-bottom:10px;">📧 support@neogen.store</p>
<p style="color:rgba(255,255,255,0.7);font-size:14px;">⏰ 9 صباحاً - 11 مساءً (توقيت السعودية)</p>""",
    })

    # Column 4: About
    about_col = make_widget("text-editor", {
        "editor": """<h4 style="color:#fff;font-size:16px;font-weight:600;margin-bottom:16px;font-family:'IBM Plex Sans Arabic',sans-serif;">نيوجين</h4>
<p style="color:rgba(255,255,255,0.6);font-size:14px;line-height:1.8;">متجر متخصص في أجهزة البيت الذكي. نختار لك أفضل المنتجات من أشهر العلامات التجارية العالمية مع دعم عربي متكامل.</p>""",
    })

    # Main footer row
    footer_row = make_section(
        [
            make_column([quick_links], size=25),
            make_column([service_links], size=25),
            make_column([contact_info], size=25),
            make_column([about_col], size=25),
        ],
        settings={
            "layout": "full_width",
            "background_background": "classic",
            "background_color": "#0D2137",
            "padding": {"top": "60", "right": "30", "bottom": "40", "left": "30", "unit": "px"},
        }
    )

    # Bottom bar with copyright
    copyright_widget = make_widget("text-editor", {
        "editor": """<div style="text-align:center;padding:20px 0;border-top:1px solid rgba(255,255,255,0.1);">
<p style="color:rgba(255,255,255,0.5);font-size:13px;margin:0;font-family:'IBM Plex Sans Arabic',sans-serif;">
© 2026 Neogen - نيوجين | جميع الحقوق محفوظة
</p>
</div>""",
    })

    bottom_bar = make_section(
        [make_column([copyright_widget])],
        settings={
            "layout": "full_width",
            "background_background": "classic",
            "background_color": "#0D2137",
            "padding": {"top": "0", "right": "20", "bottom": "20", "left": "20", "unit": "px"},
        }
    )

    return [footer_row, bottom_bar]


async def deploy_footer():
    """Rebuild and deploy footer template (Post ID 2681)."""
    print("\n=== PHASE 3: Rebuild Footer ===")

    footer_data = build_footer_sections()
    print(f"  Generated {len(footer_data)} footer sections")

    async with async_playwright() as pw:
        browser = await pw.chromium.launch(headless=True)
        ctx = await browser.new_context(
            viewport={"width": 1920, "height": 1080},
            ignore_https_errors=True,
        )
        page = await ctx.new_page()
        page.set_default_timeout(60000)

        if not await login(page):
            await browser.close()
            return False

        # Open Elementor for footer template
        print(f"  Opening Elementor for footer (Post #{FOOTER_ID})...")
        await page.goto(
            f"{WP_URL}/wp-admin/post.php?post={FOOTER_ID}&action=elementor",
            wait_until="domcontentloaded",
        )

        if not await wait_for_elementor(page):
            await page.screenshot(path=os.path.join(SCREENSHOTS_DIR, "footer_elementor_fail.png"))
            await browser.close()
            return False

        nonces = await get_nonces(page)
        print(f"  Got {len(nonces)} nonces")

        result = await save_elementor(page, FOOTER_ID, footer_data, nonces)
        print(f"  Save result: status={result.get('status')}, ok={result.get('ok')}")

        await page.wait_for_timeout(3000)

        # Verify
        vp = await ctx.new_page()
        await vp.goto(f"{WP_URL}/?nocache={random.randint(1000,9999)}", wait_until="domcontentloaded")
        await vp.wait_for_timeout(3000)
        html = await vp.content()
        has_footer = "روابط سريعة" in html or "خدمة العملاء" in html
        print(f"  Footer content visible: {has_footer}")

        await vp.screenshot(
            path=os.path.join(SCREENSHOTS_DIR, "footer_rebuilt.png"),
            full_page=True,
        )

        await browser.close()
        return result.get("ok", False)


# ============================================================
# PHASE 4: Create Missing Pages
# ============================================================

def build_about_page():
    """Elementor sections for About page."""
    # Hero
    hero_heading = make_widget("heading", {
        "title": "من نحن",
        "header_size": "h1",
        "align": "center",
        "title_color": "#FFFFFF",
        "typography_typography": "custom",
        "typography_font_family": "IBM Plex Sans Arabic",
        "typography_font_size": {"size": 40, "unit": "px"},
        "typography_font_weight": "700",
    })
    hero_sub = make_widget("heading", {
        "title": "متجر متخصص في أجهزة البيت الذكي | دعم عربي متكامل",
        "header_size": "h3",
        "align": "center",
        "title_color": "#CBD5E1",
        "typography_typography": "custom",
        "typography_font_family": "IBM Plex Sans Arabic",
        "typography_font_size": {"size": 18, "unit": "px"},
        "typography_font_weight": "400",
    })
    hero = make_section([make_column([hero_heading, hero_sub])], settings={
        "layout": "full_width",
        "background_background": "gradient",
        "background_color": "#1A3A5C",
        "background_color_b": "#0D2137",
        "background_gradient_angle": {"size": 135, "unit": "deg"},
        "min_height": {"size": 300, "unit": "px"},
        "height": "min-height",
        "padding": {"top": "80", "right": "20", "bottom": "80", "left": "20", "unit": "px"},
    })

    # Stats
    stats = [
        {"num": "+775", "label": "منتج ذكي"},
        {"num": "24/7", "label": "دعم عربي"},
        {"num": "+12", "label": "علامة تجارية"},
    ]
    stat_cols = []
    for s in stats:
        w = make_widget("counter", {
            "starting_number": 0,
            "ending_number": 0,
            "prefix": "",
            "suffix": "",
            "title": f"<span style='font-size:36px;font-weight:700;color:#1A3A5C;display:block;'>{s['num']}</span><span style='font-size:16px;color:#6B7280;'>{s['label']}</span>",
        })
        # Use text widget instead for simpler rendering
        w2 = make_widget("text-editor", {
            "editor": f"<div style='text-align:center;padding:30px 20px;'><div style='font-size:40px;font-weight:700;color:#1A3A5C;font-family:IBM Plex Sans Arabic,sans-serif;'>{s['num']}</div><div style='font-size:16px;color:#6B7280;margin-top:8px;font-family:IBM Plex Sans Arabic,sans-serif;'>{s['label']}</div></div>",
        })
        stat_cols.append(make_column([w2], size=33))
    stats_section = make_section(stat_cols, settings={
        "background_background": "classic",
        "background_color": "#FFFFFF",
        "padding": {"top": "20", "right": "20", "bottom": "20", "left": "20", "unit": "px"},
    })

    # Story
    story = make_widget("text-editor", {
        "editor": """<div style="max-width:800px;margin:0 auto;text-align:center;">
<h2 style="font-size:28px;font-weight:700;color:#1A1A2E;margin-bottom:20px;font-family:'IBM Plex Sans Arabic',sans-serif;">قصتنا</h2>
<p style="font-size:16px;line-height:2;color:#4B5563;font-family:'IBM Plex Sans Arabic',sans-serif;">
نيوجين هو متجر سعودي متخصص في أجهزة البيت الذكي. نؤمن بأن التقنية الذكية يجب أن تكون متاحة للجميع وسهلة الاستخدام.
</p>
<p style="font-size:16px;line-height:2;color:#4B5563;font-family:'IBM Plex Sans Arabic',sans-serif;">
نختار بعناية أفضل المنتجات من أشهر العلامات التجارية العالمية مثل Aqara و Sonoff و Philips Hue و SwitchBot، ونقدمها لك مع دعم عربي متكامل يساعدك من اختيار الجهاز المناسب إلى إعداده في بيتك.
</p>
<p style="font-size:16px;line-height:2;color:#4B5563;font-family:'IBM Plex Sans Arabic',sans-serif;">
هدفنا: نحوّل كل بيت سعودي إلى بيت ذكي - بدون فني وبدون تعقيد.
</p>
</div>""",
    })
    story_section = make_section([make_column([story])], settings={
        "background_background": "classic",
        "background_color": "#F8F9FA",
        "padding": {"top": "60", "right": "20", "bottom": "60", "left": "20", "unit": "px"},
    })

    # Values
    values = [
        {"icon": "fas fa-headset", "title": "دعم عربي حقيقي", "desc": "فريق تقني يرد عليك بالعربي عبر واتساب - من اختيار الجهاز المناسب إلى الإعداد والتركيب."},
        {"icon": "fas fa-check-circle", "title": "منتجات مُختارة بعناية", "desc": "نختار فقط المنتجات من علامات تجارية موثوقة ومتوافقة مع أنظمة البيت الذكي الشائعة."},
        {"icon": "fas fa-graduation-cap", "title": "محتوى تعليمي مجاني", "desc": "شروحات ومقالات بالعربي تساعدك تحوّل بيتك بنفسك بدون ما تحتاج فني."},
    ]
    val_cols = []
    for v in values:
        w = make_widget("icon-box", {
            "selected_icon": {"value": v["icon"], "library": "fa-solid"},
            "title_text": v["title"],
            "description_text": v["desc"],
            "position": "top",
            "text_align": "center",
            "primary_color": "#00BFA6",
            "title_size": "h4",
            "title_color": "#1A1A2E",
            "description_color": "#6B7280",
            "icon_size": {"size": 44, "unit": "px"},
            "title_typography_typography": "custom",
            "title_typography_font_family": "IBM Plex Sans Arabic",
            "title_typography_font_size": {"size": 20, "unit": "px"},
            "title_typography_font_weight": "600",
        })
        val_cols.append(make_column([w], size=33))
    values_section = make_section(val_cols, settings={
        "background_background": "classic",
        "background_color": "#FFFFFF",
        "padding": {"top": "60", "right": "20", "bottom": "60", "left": "20", "unit": "px"},
    })

    return [hero, stats_section, story_section, values_section]


def build_faq_page():
    """Elementor sections for FAQ page."""
    hero = make_section([make_column([
        make_widget("heading", {
            "title": "الأسئلة الشائعة",
            "header_size": "h1",
            "align": "center",
            "title_color": "#FFFFFF",
            "typography_typography": "custom",
            "typography_font_family": "IBM Plex Sans Arabic",
            "typography_font_size": {"size": 36, "unit": "px"},
            "typography_font_weight": "700",
        })
    ])], settings={
        "layout": "full_width",
        "background_background": "gradient",
        "background_color": "#1A3A5C",
        "background_color_b": "#0D2137",
        "min_height": {"size": 200, "unit": "px"},
        "height": "min-height",
        "padding": {"top": "60", "right": "20", "bottom": "60", "left": "20", "unit": "px"},
    })

    faq_items = [
        ("كم مدة التوصيل؟", "مدة التوصيل من 7 إلى 14 يوم عمل حسب المنتج والمدينة."),
        ("هل يوجد شحن مجاني؟", "نعم، الشحن مجاني لجميع الطلبات فوق 300 ر.س."),
        ("هل المنتجات أصلية؟", "نعم، جميع منتجاتنا أصلية من العلامات التجارية المعتمدة مثل Aqara و Sonoff و Philips Hue."),
        ("هل تعمل مع Home Assistant؟", "نعم، نختار منتجات متوافقة مع Home Assistant وأنظمة البيت الذكي الأخرى."),
        ("ما هي طرق الدفع؟", "نقبل مدى، فيزا، ماستركارد، والتحويل البنكي."),
        ("ما سياسة الإرجاع؟", "يمكنك إرجاع المنتج خلال 15 يوم من الاستلام بشرط أن يكون بحالته الأصلية."),
        ("هل أقدر أركّب الأجهزة بنفسي؟", "نعم! معظم أجهزتنا سهلة التركيب. نوفر شروحات بالعربي ودعم عبر واتساب يساعدك خطوة بخطوة."),
        ("هل تشحنون خارج السعودية؟", "حالياً نشحن داخل السعودية فقط. للطلبات من دول الخليج، تواصل معنا عبر واتساب."),
    ]

    tabs = []
    for q, a in faq_items:
        tabs.append({"tab_title": q, "tab_content": f"<p style='font-size:15px;line-height:1.8;color:#4B5563;font-family:IBM Plex Sans Arabic,sans-serif;'>{a}</p>"})

    accordion = make_widget("accordion", {
        "tabs": tabs,
        "title_color": "#1A1A2E",
        "title_background": "#FFFFFF",
        "tab_active_color": "#1A3A5C",
        "title_typography_typography": "custom",
        "title_typography_font_family": "IBM Plex Sans Arabic",
        "title_typography_font_size": {"size": 16, "unit": "px"},
        "title_typography_font_weight": "600",
    })

    faq_section = make_section([make_column([accordion])], settings={
        "background_background": "classic",
        "background_color": "#F8F9FA",
        "padding": {"top": "50", "right": "20", "bottom": "50", "left": "20", "unit": "px"},
        "content_width": {"size": 800, "unit": "px"},
    })

    return [hero, faq_section]


def build_contact_page():
    """Elementor sections for Contact page."""
    hero = make_section([make_column([
        make_widget("heading", {
            "title": "تواصل معنا",
            "header_size": "h1",
            "align": "center",
            "title_color": "#FFFFFF",
            "typography_typography": "custom",
            "typography_font_family": "IBM Plex Sans Arabic",
            "typography_font_size": {"size": 36, "unit": "px"},
            "typography_font_weight": "700",
        }),
        make_widget("heading", {
            "title": "نحب نسمع منك! تواصل معنا بأي طريقة تناسبك",
            "header_size": "h4",
            "align": "center",
            "title_color": "#CBD5E1",
            "typography_typography": "custom",
            "typography_font_family": "IBM Plex Sans Arabic",
            "typography_font_size": {"size": 16, "unit": "px"},
            "typography_font_weight": "400",
        }),
    ])], settings={
        "layout": "full_width",
        "background_background": "gradient",
        "background_color": "#1A3A5C",
        "background_color_b": "#0D2137",
        "min_height": {"size": 250, "unit": "px"},
        "height": "min-height",
        "padding": {"top": "60", "right": "20", "bottom": "60", "left": "20", "unit": "px"},
    })

    # WhatsApp CTA
    wa_btn = make_widget("button", {
        "text": "تحدث معنا عبر واتساب",
        "link": {"url": "https://wa.me/966500000000", "is_external": True},
        "align": "center",
        "size": "lg",
        "background_color": "#25D366",
        "button_text_color": "#FFFFFF",
        "border_radius": {"size": 12, "unit": "px"},
        "typography_typography": "custom",
        "typography_font_family": "IBM Plex Sans Arabic",
        "typography_font_size": {"size": 20, "unit": "px"},
        "typography_font_weight": "600",
        "button_background_color": "#25D366",
        "icon": {"value": "fab fa-whatsapp", "library": "fa-brands"},
    })

    # Contact info
    info = make_widget("text-editor", {
        "editor": """<div style="text-align:center;max-width:600px;margin:0 auto;">
<div style="margin-bottom:24px;">
<h3 style="font-size:20px;color:#1A1A2E;font-family:'IBM Plex Sans Arabic',sans-serif;">📧 البريد الإلكتروني</h3>
<p style="color:#6B7280;font-size:16px;">support@neogen.store</p>
</div>
<div style="margin-bottom:24px;">
<h3 style="font-size:20px;color:#1A1A2E;font-family:'IBM Plex Sans Arabic',sans-serif;">⏰ ساعات العمل</h3>
<p style="color:#6B7280;font-size:16px;">9 صباحاً - 11 مساءً (توقيت السعودية)<br>واتساب متاح 24/7</p>
</div>
</div>""",
    })

    contact_section = make_section([make_column([wa_btn, make_widget("spacer", {"space": {"size": 30, "unit": "px"}}), info])], settings={
        "background_background": "classic",
        "background_color": "#F8F9FA",
        "padding": {"top": "50", "right": "20", "bottom": "50", "left": "20", "unit": "px"},
    })

    return [hero, contact_section]


async def create_pages():
    """Create About, FAQ, Contact pages."""
    print("\n=== PHASE 4: Create Missing Pages ===")

    pages_config = [
        {"title": "من نحن", "slug": "about", "builder": build_about_page},
        {"title": "الأسئلة الشائعة", "slug": "faq", "builder": build_faq_page},
        {"title": "تواصل معنا", "slug": "contact", "builder": build_contact_page},
    ]

    async with async_playwright() as pw:
        browser = await pw.chromium.launch(headless=True)
        ctx = await browser.new_context(
            viewport={"width": 1920, "height": 1080},
            ignore_https_errors=True,
        )
        page = await ctx.new_page()
        page.set_default_timeout(60000)

        if not await login(page):
            await browser.close()
            return False

        # Get WP nonce for REST API
        await page.goto(f"{WP_URL}/wp-admin/", wait_until="domcontentloaded")
        await page.wait_for_timeout(2000)
        wp_nonce = await page.evaluate("() => typeof wpApiSettings !== 'undefined' ? wpApiSettings.nonce : ''")

        if not wp_nonce:
            # Try getting nonce from edit page
            await page.goto(f"{WP_URL}/wp-admin/edit.php?post_type=page", wait_until="domcontentloaded")
            await page.wait_for_timeout(2000)
            wp_nonce = await page.evaluate("() => typeof wpApiSettings !== 'undefined' ? wpApiSettings.nonce : ''")

        if not wp_nonce:
            print("  Could not get WP nonce")
            await browser.close()
            return False

        print(f"  Got WP nonce")

        for pc in pages_config:
            print(f"\n  Creating page: {pc['title']} (/{pc['slug']}/)...")

            # Check if page already exists
            check = await page.evaluate("""
                async (params) => {
                    const resp = await fetch(`/wp-json/wp/v2/pages?slug=${params.slug}`, {
                        headers: { 'X-WP-Nonce': params.nonce }
                    });
                    const pages = await resp.json();
                    if (pages.length > 0) return pages[0].id;
                    return null;
                }
            """, {"slug": pc["slug"], "nonce": wp_nonce})

            if check:
                print(f"    Page already exists (ID: {check})")
                page_id = check
            else:
                # Create page
                page_id = await page.evaluate("""
                    async (params) => {
                        const resp = await fetch('/wp-json/wp/v2/pages', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-WP-Nonce': params.nonce
                            },
                            body: JSON.stringify({
                                title: params.title,
                                slug: params.slug,
                                status: 'publish',
                                template: 'elementor_header_footer',
                            })
                        });
                        if (!resp.ok) return { error: resp.status };
                        const data = await resp.json();
                        return data.id;
                    }
                """, {"title": pc["title"], "slug": pc["slug"], "nonce": wp_nonce})

                if isinstance(page_id, dict):
                    print(f"    ERROR creating page: {page_id}")
                    continue

                print(f"    Created page ID: {page_id}")

            # Deploy Elementor content
            print(f"    Opening Elementor editor...")
            await page.goto(
                f"{WP_URL}/wp-admin/post.php?post={page_id}&action=elementor",
                wait_until="domcontentloaded",
            )

            if not await wait_for_elementor(page):
                print(f"    Elementor failed for {pc['title']}")
                await page.screenshot(path=os.path.join(SCREENSHOTS_DIR, f"page_{pc['slug']}_fail.png"))
                continue

            nonces = await get_nonces(page)
            elementor_data = pc["builder"]()
            result = await save_elementor(page, page_id, elementor_data, nonces)
            print(f"    Save result: ok={result.get('ok')}")

            await page.wait_for_timeout(3000)

        # Take screenshots
        for pc in pages_config:
            vp = await ctx.new_page()
            await vp.goto(f"{WP_URL}/{pc['slug']}/", wait_until="domcontentloaded")
            await vp.wait_for_timeout(3000)
            await vp.screenshot(
                path=os.path.join(SCREENSHOTS_DIR, f"page_{pc['slug']}.png"),
                full_page=True,
            )
            print(f"  Screenshot: page_{pc['slug']}.png")
            await vp.close()

        await browser.close()
        return True


# ============================================================
# VERIFY
# ============================================================

async def verify_all():
    """Take screenshots of all key pages."""
    print("\n=== VERIFICATION ===")

    pages = [
        ("homepage", "/"),
        ("shop", "/shop/"),
        ("cart", "/cart/"),
        ("about", "/about/"),
        ("faq", "/faq/"),
        ("contact", "/contact/"),
    ]

    async with async_playwright() as pw:
        browser = await pw.chromium.launch(headless=True)

        # Desktop
        ctx_desktop = await browser.new_context(
            viewport={"width": 1440, "height": 900},
            ignore_https_errors=True,
        )
        for name, path in pages:
            pg = await ctx_desktop.new_page()
            await pg.goto(f"{WP_URL}{path}?nocache={random.randint(1000,9999)}", wait_until="domcontentloaded")
            await pg.wait_for_timeout(3000)
            await pg.screenshot(
                path=os.path.join(SCREENSHOTS_DIR, f"verify_{name}_desktop.png"),
                full_page=True,
            )
            print(f"  Desktop: {name}")
            await pg.close()

        # Mobile
        ctx_mobile = await browser.new_context(
            viewport={"width": 375, "height": 812},
            user_agent="Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X)",
            ignore_https_errors=True,
        )
        for name, path in pages:
            pg = await ctx_mobile.new_page()
            await pg.goto(f"{WP_URL}{path}?nocache={random.randint(1000,9999)}", wait_until="domcontentloaded")
            await pg.wait_for_timeout(3000)
            await pg.screenshot(
                path=os.path.join(SCREENSHOTS_DIR, f"verify_{name}_mobile.png"),
                full_page=True,
            )
            print(f"  Mobile: {name}")
            await pg.close()

        await browser.close()
        print(f"\n  All screenshots saved to: {SCREENSHOTS_DIR}/")
        return True


# ============================================================
# MAIN
# ============================================================

def main():
    parser = argparse.ArgumentParser(description="Neogen Store Layout Overhaul")
    parser.add_argument("--plugin", action="store_true", help="Deploy theme customizer plugin")
    parser.add_argument("--homepage", action="store_true", help="Redeploy homepage")
    parser.add_argument("--footer", action="store_true", help="Rebuild footer")
    parser.add_argument("--pages", action="store_true", help="Create About/FAQ/Contact pages")
    parser.add_argument("--verify", action="store_true", help="Take verification screenshots")
    parser.add_argument("--all", action="store_true", help="Run everything")
    args = parser.parse_args()

    if not USERNAME or not PASSWORD:
        print("ERROR: Missing WP_ADMIN_USER or WP_ADMIN_PASSWORD in .env")
        sys.exit(1)

    if not any([args.plugin, args.homepage, args.footer, args.pages, args.verify, args.all]):
        parser.print_help()
        print("\nPlease specify what to deploy.")
        sys.exit(1)

    results = {}

    if args.all or args.plugin:
        results["plugin"] = asyncio.run(deploy_plugin())

    if args.all or args.homepage:
        results["homepage"] = asyncio.run(deploy_homepage())

    if args.all or args.footer:
        results["footer"] = asyncio.run(deploy_footer())

    if args.all or args.pages:
        results["pages"] = asyncio.run(create_pages())

    if args.all or args.verify:
        results["verify"] = asyncio.run(verify_all())

    # Summary
    print("\n" + "=" * 50)
    print("DEPLOYMENT SUMMARY")
    print("=" * 50)
    for phase, success in results.items():
        status = "OK" if success else "FAILED"
        print(f"  {phase}: {status}")
    print()

    if all(results.values()):
        print("All phases completed successfully!")
    else:
        print("Some phases had issues. Check screenshots for details.")


if __name__ == "__main__":
    main()
