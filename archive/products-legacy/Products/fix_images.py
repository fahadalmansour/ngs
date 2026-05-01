#!/usr/bin/env python3.11
"""
Fix missing product images for 70 Neogen products.
- Service products: Generate branded Neogen service images
- Hardware products: Generate branded hardware-style images
- Upload via WordPress REST API using Playwright cookie auth
- Assign to products via WooCommerce REST API
"""

import asyncio
import json
import os
import sys
import time
import requests
from requests.auth import HTTPBasicAuth
from PIL import Image, ImageDraw, ImageFont
from io import BytesIO
import textwrap
import math

try:
    from playwright.async_api import async_playwright
except ImportError:
    sys.exit("playwright required: pip install playwright && playwright install chromium")


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

STORE = os.environ.get("STORE_URL", "")
WP_URL = os.environ.get("WP_URL", STORE or "https://neogen.store")
USERNAME = os.environ.get("WP_ADMIN_USER", "")
PASSWORD = os.environ.get("WP_ADMIN_PASSWORD", "")
CK = os.environ.get("WC_CONSUMER_KEY", "")
CS = os.environ.get("WC_CONSUMER_SECRET", "")
WC_AUTH = HTTPBasicAuth(CK, CS)

IMAGES_DIR = os.path.join(os.path.dirname(__file__), "images", "generated")
os.makedirs(IMAGES_DIR, exist_ok=True)

# Neogen brand colors
BRAND_PRIMARY = (26, 58, 92)       # #1A3A5C - dark blue
BRAND_SECONDARY = (0, 191, 166)    # #00BFA6 - teal/green
BRAND_ACCENT = (255, 107, 53)      # #FF6B35 - orange
BRAND_LIGHT = (240, 244, 248)      # #F0F4F8 - light gray-blue
BRAND_WHITE = (255, 255, 255)

# Service category icons (text-based since we can't use icon fonts easily)
SERVICE_ICONS = {
    "Installation": "⚙",
    "Dashboard": "📊",
    "Automation": "🔄",
    "Setup": "🔧",
    "Zigbee": "📡",
    "Matter": "🔗",
    "Camera": "📹",
    "Frigate": "📹",
    "Network": "🌐",
    "VLAN": "🌐",
    "UniFi": "🌐",
    "Support": "💬",
    "Training": "🎓",
    "Remote": "🖥",
    "Warranty": "🛡",
    "Migration": "🔀",
    "Custom": "🎨",
    "Security": "🔒",
    "Maintenance": "🔩",
    "Firmware": "⬆",
    "Energy": "⚡",
    "Design": "📐",
    "Blueprint": "📐",
    "Assessment": "📋",
    "Backup": "💾",
    "Health": "❤",
    "Login": "🔑",
    "Kit": "📦",
    "Starter": "📦",
    "SD Card": "💾",
}

# Hardware product image search terms
HARDWARE_PRODUCTS = {
    "NGS-RIN-CAM-0038": "Ring Video Doorbell 4",
    "NGS-TAD-CLI-0045": "Tado Smart AC Control V3+",
    "NGS-RAS-RAS-0129": "Raspberry Pi 5 8GB",
    "NGS-MER-PLU-0249": "Meross MSS310 smart plug",
    "NGS-SKY-ZIG-0336": "Home Assistant SkyConnect USB",
    "NGS-MAR-VOI-0370": "Marshall Uxbridge Voice speaker",
    "NGS-GMK-MIN-0390": "GMKtec NucBox G3 mini PC",
    "NGS-NVI-GPU-0412": "NVIDIA Quadro P2000 GPU",
    "NGS-HIK-OUT-0464": "Hikvision DS-2CD2087G2 camera",
    "NGS-UNI-PTZ-0469": "UniFi G4 PTZ camera",
    "NGS-HIK-NVR-0477": "Hikvision DS-7608NXI NVR",
    "NGS-POE-ACC-0483": "PoE Extender network",
    "NGS-GEN-MIS-0545": "Logic Level Converter module",
    "NGS-CHA-LEV-0631": "ChargePoint Home Flex EV charger",
    "NGS-FAC-VID-0753": "Facebook Portal Plus",
    "NGS-PHI-MED-0757": "Philips automatic medication dispenser",
    "NGS-TUY-ENE-0765": "Tuya 3 phase energy meter",
}


def generate_service_image(product_name, sku, category_hint=""):
    """Generate a branded service image."""
    width, height = 800, 800
    img = Image.new("RGB", (width, height), BRAND_PRIMARY)
    draw = ImageDraw.Draw(img)

    # Gradient background - top to bottom
    for y in range(height):
        ratio = y / height
        r = int(BRAND_PRIMARY[0] * (1 - ratio * 0.3))
        g = int(BRAND_PRIMARY[1] * (1 - ratio * 0.3))
        b = int(BRAND_PRIMARY[2] * (1 - ratio * 0.1))
        draw.line([(0, y), (width, y)], fill=(r, g, b))

    # Decorative circle (top-right)
    draw.ellipse(
        [width - 250, -100, width + 50, 200],
        fill=(*BRAND_SECONDARY, 40),
        outline=None,
    )

    # Decorative circle (bottom-left)
    draw.ellipse(
        [-80, height - 250, 220, height + 50],
        fill=(*BRAND_ACCENT, 30),
        outline=None,
    )

    # Try to find an icon
    icon_char = "🔧"  # default
    for keyword, icon in SERVICE_ICONS.items():
        if keyword.lower() in product_name.lower():
            icon_char = icon
            break

    # Draw icon circle
    circle_x, circle_y = width // 2, 240
    circle_r = 90
    draw.ellipse(
        [circle_x - circle_r, circle_y - circle_r,
         circle_x + circle_r, circle_y + circle_r],
        fill=BRAND_SECONDARY,
    )

    # Icon text (emoji) - use default font at large size
    try:
        icon_font = ImageFont.truetype("/System/Library/Fonts/Apple Color Emoji.ttc", 60)
    except (OSError, IOError):
        icon_font = ImageFont.load_default()

    try:
        bbox = draw.textbbox((0, 0), icon_char, font=icon_font)
        iw, ih = bbox[2] - bbox[0], bbox[3] - bbox[1]
        draw.text(
            (circle_x - iw // 2, circle_y - ih // 2 - 5),
            icon_char,
            fill=BRAND_WHITE,
            font=icon_font,
        )
    except Exception:
        pass

    # "NEOGEN" brand text at top
    try:
        brand_font = ImageFont.truetype("/System/Library/Fonts/Helvetica.ttc", 28)
    except (OSError, IOError):
        brand_font = ImageFont.load_default()

    draw.text((30, 30), "NEOGEN", fill=BRAND_SECONDARY, font=brand_font)

    # Thin accent line under brand
    draw.rectangle([30, 65, 130, 68], fill=BRAND_ACCENT)

    # Product name - wrap text
    try:
        title_font = ImageFont.truetype("/System/Library/Fonts/Helvetica.ttc", 36)
        small_font = ImageFont.truetype("/System/Library/Fonts/Helvetica.ttc", 20)
    except (OSError, IOError):
        title_font = ImageFont.load_default()
        small_font = ImageFont.load_default()

    # Wrap the product name
    lines = textwrap.wrap(product_name, width=22)
    y_text = 380
    for line in lines[:3]:
        bbox = draw.textbbox((0, 0), line, font=title_font)
        tw = bbox[2] - bbox[0]
        draw.text(
            ((width - tw) // 2, y_text),
            line,
            fill=BRAND_WHITE,
            font=title_font,
        )
        y_text += 48

    # Category badge
    # Determine category from SKU
    if "SER" in sku:
        category = "خدمة | SERVICE"
    elif "PRE" in sku:
        category = "حزمة | BUNDLE"
    else:
        category = "منتج | PRODUCT"

    badge_y = y_text + 30
    bbox = draw.textbbox((0, 0), category, font=small_font)
    bw = bbox[2] - bbox[0]
    badge_pad = 15
    draw.rounded_rectangle(
        [(width // 2 - bw // 2 - badge_pad, badge_y - 5),
         (width // 2 + bw // 2 + badge_pad, badge_y + 30)],
        radius=15,
        fill=BRAND_ACCENT,
    )
    draw.text(
        ((width - bw) // 2, badge_y),
        category,
        fill=BRAND_WHITE,
        font=small_font,
    )

    # Bottom bar
    draw.rectangle([0, height - 60, width, height], fill=(*BRAND_SECONDARY, 200))
    draw.text(
        (30, height - 45),
        "neogen.store",
        fill=BRAND_WHITE,
        font=small_font,
    )

    # SKU in bottom right
    bbox = draw.textbbox((0, 0), sku, font=small_font)
    sw = bbox[2] - bbox[0]
    draw.text(
        (width - sw - 30, height - 45),
        sku,
        fill=BRAND_WHITE,
        font=small_font,
    )

    return img


def search_product_image(product_name):
    """Try to find a real product image via web search."""
    # Try common product image sources
    search_term = product_name.replace(" ", "+")

    # Attempt 1: Use a simple image search approach
    headers = {
        "User-Agent": "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) "
                       "AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    }

    # Try manufacturer websites and common sources
    urls_to_try = []

    # Map common brands to their image CDNs
    brand_urls = {
        "Ring": f"https://images-na.ssl-images-amazon.com/images/I/{search_term}.jpg",
        "Raspberry Pi": "https://www.raspberrypi.com/app/uploads/2023/12/RPi5-768x543.jpg",
        "Hikvision": None,
        "UniFi": None,
    }

    # For now, generate a product-style image since web scraping is unreliable
    return None


def generate_hardware_image(product_name, sku):
    """Generate a product-style image for hardware items."""
    width, height = 800, 800
    img = Image.new("RGB", (width, height), BRAND_LIGHT)
    draw = ImageDraw.Draw(img)

    # Light product-style background with subtle gradient
    for y in range(height):
        ratio = y / height
        val = int(240 + ratio * 15)
        draw.line([(0, y), (width, y)], fill=(val, val + 2, val + 4))

    # Product silhouette circle (center)
    cx, cy = width // 2, 320
    radius = 160
    # Soft shadow
    for i in range(20, 0, -1):
        alpha = int(10 + i * 2)
        draw.ellipse(
            [cx - radius - i, cy - radius - i + 5,
             cx + radius + i, cy + radius + i + 5],
            fill=(200, 200, 210),
        )
    # White circle
    draw.ellipse(
        [cx - radius, cy - radius, cx + radius, cy + radius],
        fill=BRAND_WHITE,
    )

    # Brand icon in circle
    try:
        icon_font = ImageFont.truetype("/System/Library/Fonts/Apple Color Emoji.ttc", 80)
    except (OSError, IOError):
        icon_font = ImageFont.load_default()

    # Choose icon based on product type
    icon = "📦"
    if any(w in product_name.lower() for w in ["camera", "doorbell", "nvr"]):
        icon = "📹"
    elif any(w in product_name.lower() for w in ["gpu", "nvidia", "quadro"]):
        icon = "🖥"
    elif any(w in product_name.lower() for w in ["pi", "raspberry", "mini pc", "nucbox"]):
        icon = "💻"
    elif any(w in product_name.lower() for w in ["plug", "smart plug"]):
        icon = "🔌"
    elif any(w in product_name.lower() for w in ["charger", "ev", "charge"]):
        icon = "⚡"
    elif any(w in product_name.lower() for w in ["speaker", "voice", "marshall"]):
        icon = "🔊"
    elif any(w in product_name.lower() for w in ["zigbee", "skyconnect", "usb"]):
        icon = "📡"
    elif any(w in product_name.lower() for w in ["meter", "energy"]):
        icon = "⚡"
    elif any(w in product_name.lower() for w in ["dispenser", "medication"]):
        icon = "💊"
    elif any(w in product_name.lower() for w in ["poe", "extender", "converter"]):
        icon = "🔌"
    elif any(w in product_name.lower() for w in ["portal", "facebook", "display"]):
        icon = "📱"
    elif any(w in product_name.lower() for w in ["ac", "climate", "tado"]):
        icon = "❄"

    try:
        bbox = draw.textbbox((0, 0), icon, font=icon_font)
        iw, ih = bbox[2] - bbox[0], bbox[3] - bbox[1]
        draw.text(
            (cx - iw // 2, cy - ih // 2),
            icon,
            fill=BRAND_PRIMARY,
            font=icon_font,
        )
    except Exception:
        pass

    # Brand header
    try:
        brand_font = ImageFont.truetype("/System/Library/Fonts/Helvetica.ttc", 22)
        title_font = ImageFont.truetype("/System/Library/Fonts/Helvetica.ttc", 32)
        small_font = ImageFont.truetype("/System/Library/Fonts/Helvetica.ttc", 18)
    except (OSError, IOError):
        brand_font = title_font = small_font = ImageFont.load_default()

    # NEOGEN logo top-left
    draw.text((30, 25), "NEOGEN", fill=BRAND_PRIMARY, font=brand_font)
    draw.rectangle([30, 52, 110, 55], fill=BRAND_SECONDARY)

    # Product name
    lines = textwrap.wrap(product_name, width=28)
    y_text = 540
    for line in lines[:3]:
        bbox = draw.textbbox((0, 0), line, font=title_font)
        tw = bbox[2] - bbox[0]
        draw.text(
            ((width - tw) // 2, y_text),
            line,
            fill=BRAND_PRIMARY,
            font=title_font,
        )
        y_text += 42

    # Extract brand from product name for badge
    brand_name = product_name.split()[0] if product_name else ""
    badge_text = brand_name.upper()
    if len(badge_text) > 12:
        badge_text = badge_text[:12]

    badge_y = y_text + 20
    bbox = draw.textbbox((0, 0), badge_text, font=small_font)
    bw = bbox[2] - bbox[0]
    draw.rounded_rectangle(
        [(width // 2 - bw // 2 - 12, badge_y - 4),
         (width // 2 + bw // 2 + 12, badge_y + 26)],
        radius=13,
        fill=BRAND_PRIMARY,
    )
    draw.text(
        ((width - bw) // 2, badge_y),
        badge_text,
        fill=BRAND_WHITE,
        font=small_font,
    )

    # Bottom strip
    draw.rectangle([0, height - 50, width, height], fill=BRAND_PRIMARY)
    draw.text((30, height - 38), "neogen.store", fill=BRAND_SECONDARY, font=small_font)

    bbox = draw.textbbox((0, 0), sku, font=small_font)
    sw = bbox[2] - bbox[0]
    draw.text((width - sw - 30, height - 38), sku, fill=BRAND_LIGHT, font=small_font)

    return img


async def get_wp_auth_session():
    """Login to WordPress via Playwright and return cookies + nonce for requests."""
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        ctx = await browser.new_context(
            viewport={"width": 1920, "height": 1080},
            ignore_https_errors=True,
        )
        page = await ctx.new_page()
        page.set_default_timeout(30000)

        print("  Logging in to WordPress...")
        await page.goto(f"{WP_URL}/wp-login.php", wait_until="domcontentloaded")
        await page.wait_for_selector("#user_login")
        await page.fill("#user_login", USERNAME)
        await page.fill("#user_pass", PASSWORD)
        await page.click("#wp-submit")

        try:
            await page.wait_for_url(f"**{WP_URL}/wp-admin/**", timeout=30000)
        except Exception:
            if "/wp-admin" not in page.url:
                print(f"  Login failed: {page.url}")
                await browser.close()
                return None, None

        # Get REST API nonce
        await page.goto(f"{WP_URL}/wp-admin/index.php", wait_until="domcontentloaded")
        nonce = await page.evaluate(
            "() => typeof wpApiSettings !== 'undefined' ? wpApiSettings.nonce : null"
        )

        if not nonce:
            print("  Failed to get WP REST nonce")
            await browser.close()
            return None, None

        # Extract cookies from browser context
        cookies = await ctx.cookies()
        await browser.close()

        # Build cookie dict for requests
        cookie_dict = {}
        for c in cookies:
            cookie_dict[c["name"]] = c["value"]

        print(f"  Auth OK (nonce: {nonce[:10]}..., cookies: {len(cookie_dict)})")
        return cookie_dict, nonce


def upload_image_to_wp(session, nonce, image_path, product_name):
    """Upload an image to WordPress media library using cookie auth."""
    with open(image_path, "rb") as f:
        image_data = f.read()

    filename = os.path.basename(image_path)
    headers = {
        "X-WP-Nonce": nonce,
        "Content-Disposition": f'attachment; filename="{filename}"',
        "Content-Type": "image/png",
    }

    resp = session.post(
        f"{WP_URL}/wp-json/wp/v2/media",
        headers=headers,
        data=image_data,
        timeout=60,
    )

    if resp.status_code in (200, 201):
        data = resp.json()
        return {"id": data.get("id"), "src": data.get("source_url")}
    return {"error": f"HTTP {resp.status_code}: {resp.text[:200]}"}


def assign_image_to_product(product_id, media_id, media_url, product_name):
    """Assign an uploaded media image to a WooCommerce product."""
    resp = requests.put(
        f"{STORE}/wp-json/wc/v3/products/{product_id}",
        auth=WC_AUTH,
        json={"images": [{"id": media_id, "src": media_url, "name": product_name}]},
        timeout=30,
    )
    if resp.status_code == 200:
        return True
    return False


async def main():
    print("=== Fix Missing Product Images ===")
    print(f"Store: {WP_URL}")
    print()

    # Load missing products
    missing_path = os.path.join(os.path.dirname(__file__), "missing_images.json")
    with open(missing_path) as f:
        missing = json.load(f)

    print(f"Products to fix: {len(missing)}")

    # Categorize products
    services = []
    hardware = []
    for p in missing:
        sku = p["sku"]
        if sku in HARDWARE_PRODUCTS:
            hardware.append(p)
        else:
            services.append(p)

    print(f"  Services/bundles: {len(services)}")
    print(f"  Hardware: {len(hardware)}")
    print()

    # Step 1: Generate all images first (if not already generated)
    print("--- Step 1: Generating Images ---")
    all_products = services + hardware
    generated = 0
    for p in all_products:
        sku = p["sku"]
        name = p["name"]
        img_path = os.path.join(IMAGES_DIR, f"{sku}.png")

        if os.path.exists(img_path) and os.path.getsize(img_path) > 1000:
            continue  # Already generated

        if sku in HARDWARE_PRODUCTS:
            img = generate_hardware_image(name, sku)
        else:
            img = generate_service_image(name, sku)
        img.save(img_path, "PNG", quality=95)
        generated += 1

    existing = len(all_products) - generated
    print(f"  Generated: {generated} new, {existing} already existed")
    print()

    # Step 2: Authenticate via Playwright
    print("--- Step 2: WordPress Authentication ---")
    cookies, nonce = await get_wp_auth_session()
    if not cookies or not nonce:
        print("ERROR: Authentication failed. Cannot upload images.")
        return

    # Create a requests session with the WP cookies
    session = requests.Session()
    for name_key, value in cookies.items():
        session.cookies.set(name_key, value)

    print()

    # Step 3: Upload and assign images
    print("--- Step 3: Uploading Images ---")
    success = 0
    failed = 0
    results = []

    for i, p in enumerate(all_products, 1):
        sku = p["sku"]
        name = p["name"]
        product_id = p["id"]
        img_path = os.path.join(IMAGES_DIR, f"{sku}.png")

        if not os.path.exists(img_path):
            print(f"  [{i}/{len(all_products)}] SKIP: {sku} - no image file")
            failed += 1
            continue

        # Upload to WP media library
        media = upload_image_to_wp(session, nonce, img_path, name)
        if "error" in media:
            print(f"  [{i}/{len(all_products)}] FAIL upload: {sku} - {media['error'][:80]}")
            failed += 1
            continue

        media_id = media["id"]
        media_url = media["src"]

        # Assign to WooCommerce product
        assigned = assign_image_to_product(product_id, media_id, media_url, name)
        if assigned:
            success += 1
            results.append({"sku": sku, "product_id": product_id, "media_id": media_id})
            print(f"  [{i}/{len(all_products)}] OK: {sku} - {name} (media #{media_id})")
        else:
            failed += 1
            print(f"  [{i}/{len(all_products)}] FAIL assign: {sku} - media #{media_id}")

        # Small delay to avoid overwhelming the server
        if i % 5 == 0:
            time.sleep(1)
        else:
            time.sleep(0.3)

    print()
    print(f"=== Results ===")
    print(f"  Success: {success}")
    print(f"  Failed:  {failed}")
    print(f"  Total:   {success + failed}/{len(all_products)}")

    # Save results
    if results:
        results_path = os.path.join(os.path.dirname(__file__), "image_upload_results.json")
        with open(results_path, "w") as f:
            json.dump(results, f, indent=2)
        print(f"  Results saved to: {results_path}")


if __name__ == "__main__":
    asyncio.run(main())
