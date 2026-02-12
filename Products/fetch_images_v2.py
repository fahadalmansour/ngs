#!/usr/bin/env python3
"""
NGS Product Image Fetcher v2
=============================
Fetches product images from multiple sources and uploads them to WooCommerce.

Sources (priority order):
1. Real URLs already in woocommerce-final.csv (non-placeholder)
2. Direct manufacturer website URL patterns (brand-specific)
3. Bing Image Search (fallback for unknown brands)

Upload:
- Downloads images locally, validates with Pillow, resizes to 800x800 max
- Uploads to WooCommerce via REST API (PUT /wp-json/wc/v3/products/{id})

Usage:
    python3 fetch_images_v2.py
    python3 fetch_images_v2.py --limit 50
    python3 fetch_images_v2.py --brand Aqara --dry-run
    python3 fetch_images_v2.py --skip-upload --limit 10
    python3 fetch_images_v2.py --start 100 --limit 50
"""

import os
import sys
import csv
import html as html_mod
import io
import json
import time
import logging
import argparse
import re
from datetime import datetime
from urllib.parse import quote_plus, urljoin, urlparse

import requests

try:
    from PIL import Image
except ImportError:
    print("ERROR: 'Pillow' library required. Install with: pip3 install Pillow")
    sys.exit(1)

# ---------------------------------------------------------------------------
# Configuration
# ---------------------------------------------------------------------------

# Load credentials from .env file or environment
def _load_env():
    env_path = os.path.join(os.path.dirname(__file__), ".env")
    if os.path.exists(env_path):
        with open(env_path) as f:
            for line in f:
                line = line.strip()
                if line and not line.startswith("#") and "=" in line:
                    key, _, value = line.partition("=")
                    os.environ.setdefault(key.strip(), value.strip())

_load_env()

STORE_URL = os.environ.get("STORE_URL", "")
CONSUMER_KEY = os.environ.get("WC_CONSUMER_KEY", "")
CONSUMER_SECRET = os.environ.get("WC_CONSUMER_SECRET", "")

if not all([STORE_URL, CONSUMER_KEY, CONSUMER_SECRET]):
    print("ERROR: Missing credentials. Set STORE_URL, WC_CONSUMER_KEY, WC_CONSUMER_SECRET")
    print("       in environment or in Products/.env")
    sys.exit(1)

BASE_DIR = os.path.dirname(os.path.abspath(__file__))
CSV_PATH = os.path.join(BASE_DIR, "woocommerce-final.csv")
IMAGE_LIST_PATH = os.path.join(BASE_DIR, "image-fetch-list.csv")
DEFAULT_IMAGES_DIR = os.path.join(BASE_DIR, "images")
PROGRESS_PATH = os.path.join(BASE_DIR, "fetch_images_v2_progress.json")
LOG_PATH = os.path.join(BASE_DIR, "fetch_images_v2.log")

MAX_IMAGE_SIZE = (800, 800)
JPEG_QUALITY = 85
WEB_REQUEST_DELAY = 2.0   # seconds between web scrape requests
API_REQUEST_DELAY = 1.0   # seconds between WooCommerce API calls
MAX_RETRIES = 3

HEADERS = {
    "User-Agent": (
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) "
        "AppleWebKit/537.36 (KHTML, like Gecko) "
        "Chrome/122.0.0.0 Safari/537.36"
    ),
    "Accept": (
        "text/html,application/xhtml+xml,application/xml;q=0.9,"
        "image/avif,image/webp,image/apng,*/*;q=0.8"
    ),
    "Accept-Language": "en-US,en;q=0.9",
    # Note: Do NOT include 'br' (Brotli) -- requests can't decompress it
    # without the brotli package. gzip/deflate are handled natively.
    "Accept-Encoding": "gzip, deflate",
}

# ---------------------------------------------------------------------------
# Brand -> manufacturer image URL patterns
# ---------------------------------------------------------------------------
# Each entry can have:
#   "url_fn":  callable(product_name, brand) -> candidate URL string
#   "search_page_fn": callable(product_name) -> page URL to scrape for <img>
#
# The helpers below build these dynamically.

def _synology_model(name):
    """Extract Synology model from product name, e.g. 'Synology DS224+' -> 'DS224plus'."""
    m = re.search(r"(DS|RS|SA|FS|HD|UC)\d{3,4}\+?", name)
    if m:
        return m.group(0).replace("+", "plus")
    return None

def _qnap_model(name):
    m = re.search(r"(TS|TVS|TBS|TL|TR|QSW)-\w+", name)
    if m:
        return m.group(0)
    return None

def _home_assistant_model(name):
    low = name.lower()
    if "green" in low:
        return "green"
    if "yellow" in low:
        return "yellow"
    if "skyconnect" in low or "sky connect" in low:
        return "skyconnect"
    if "voice" in low:
        return "voice-pe"
    return None

def _sonoff_slug(name):
    """Turn 'SONOFF ZBDongle-P' into a search-friendly slug."""
    parts = name.replace("SONOFF", "").strip()
    return parts.lower().replace(" ", "-")

def _aqara_slug(name):
    """Turn 'Aqara FP2 Presence Sensor' -> 'fp2-presence-sensor'."""
    parts = name.replace("Aqara", "").strip()
    return re.sub(r"[^a-z0-9]+", "-", parts.lower()).strip("-")

def _raspberry_pi_slug(name):
    low = name.lower()
    if "pi 5" in low:
        return "raspberry-pi-5"
    if "pi 4" in low:
        return "raspberry-pi-4-model-b"
    if "pi 400" in low:
        return "raspberry-pi-400"
    if "pi zero 2" in low:
        return "raspberry-pi-zero-2-w"
    if "pi zero" in low:
        return "raspberry-pi-zero-w"
    if "pico" in low:
        return "raspberry-pi-pico"
    return None


def _ubiquiti_slug(name):
    """Turn 'UniFi Dream Machine Pro' -> 'udm-pro', 'U6 Pro' -> 'u6-pro'."""
    low = name.lower()
    # Known model mappings
    mappings = {
        "dream machine pro max": "udm-pro-max",
        "dream machine pro": "udm-pro",
        "dream machine se": "udm-se",
        "dream machine": "udm",
        "dream router": "udr",
        "dream wall": "udw",
        "cloud key gen2 plus": "uck-g2-plus",
        "cloud key gen2+": "uck-g2-plus",
        "cloudkey gen2": "uck-g2-plus",
        "u7 pro": "u7-pro",
        "u6 pro": "u6-pro",
        "u6 lite": "u6-lite",
        "u6 enterprise": "u6-enterprise",
        "usw lite 8 poe": "usw-lite-8-poe",
        "usw lite 16 poe": "usw-lite-16-poe",
        "usw pro 24 poe": "usw-pro-24-poe",
        "usw pro 48 poe": "usw-pro-48-poe",
        "g4 instant": "camera-g4-instant",
        "g4 bullet": "camera-g4-bullet",
        "g4 ptz": "camera-g4-ptz",
        "g4 doorbell pro": "camera-g4-doorbell-pro",
        "g5 pro": "camera-g5-pro",
        "g5 bullet": "camera-g5-bullet",
        "unas pro": "unas-pro",
        "unvr": "unvr",
        "unvr pro": "unvr-pro",
    }
    for key, slug in mappings.items():
        if key in low:
            return slug
    # Fallback: strip brand prefix and slugify
    cleaned = re.sub(r"(?i)^(ubiquiti|unifi)\s+", "", name).strip()
    return re.sub(r"[^a-z0-9]+", "-", cleaned.lower()).strip("-")


def _reolink_slug(name):
    """Turn 'RLC-810A' -> 'rlc-810a', 'E1 Zoom' -> 'e1-zoom'."""
    cleaned = re.sub(r"(?i)^reolink\s+", "", name).strip()
    return re.sub(r"[^a-z0-9]+", "-", cleaned.lower()).strip("-")


def _sonos_slug(name):
    """Turn 'Sonos Beam Gen 2' -> 'beam-gen-2', 'Era 100' -> 'era-100'."""
    cleaned = re.sub(r"(?i)^sonos\s+", "", name).strip()
    return re.sub(r"[^a-z0-9]+", "-", cleaned.lower()).strip("-")


def _switchbot_slug(name):
    """Turn 'SwitchBot Hub 2' -> 'switchbot-hub-2'."""
    return re.sub(r"[^a-z0-9]+", "-", name.lower()).strip("-")


def _shelly_slug(name):
    """Turn 'Shelly Plus 1' -> 'shelly-plus-1'."""
    return re.sub(r"[^a-z0-9]+", "-", name.lower()).strip("-")


def _tp_link_model(name):
    """Extract TP-Link model from name, e.g. 'TP-Link Deco X50' -> 'deco-x50'."""
    cleaned = re.sub(r"(?i)^(tp-?link|tapo|kasa)\s+", "", name).strip()
    return re.sub(r"[^a-z0-9]+", "-", cleaned.lower()).strip("-")


def _mikrotik_slug(name):
    """Turn 'hAP ax3' -> 'hap_ax3', 'RB5009UG+S+IN' -> 'RB5009UG+S+IN'."""
    cleaned = re.sub(r"(?i)^mikrotik\s+", "", name).strip()
    # MikroTik uses the raw model name in URLs
    return cleaned


def _google_nest_slug(name):
    """Turn 'Google Nest Hub 2nd Gen' -> 'google_nest_hub_2nd_gen'."""
    cleaned = re.sub(r"(?i)^google\s+", "", name).strip()
    return re.sub(r"[^a-z0-9]+", "_", cleaned.lower()).strip("_")


def _eufy_slug(name):
    """Turn 'eufy Indoor Cam 2K' -> 'eufy-indoor-cam-2k'."""
    return re.sub(r"[^a-z0-9]+", "-", name.lower()).strip("-")


def _nanoleaf_slug(name):
    """Turn 'Nanoleaf Essentials A19' -> 'nanoleaf-essentials-a19-bulb'."""
    return re.sub(r"[^a-z0-9]+", "-", name.lower()).strip("-")


def _eve_slug(name):
    """Turn 'Eve Aqua' -> 'eve-aqua'."""
    return re.sub(r"[^a-z0-9]+", "-", name.lower()).strip("-")


def _roborock_slug(name):
    """Turn 'Roborock S8 Pro Ultra' -> 's8-pro-ultra'."""
    cleaned = re.sub(r"(?i)^roborock\s+", "", name).strip()
    return re.sub(r"[^a-z0-9]+", "-", cleaned.lower()).strip("-")


def _beelink_slug(name):
    """Turn 'Beelink EQ12' -> 'beelink-eq12'."""
    return re.sub(r"[^a-z0-9]+", "-", name.lower()).strip("-")


def _glinet_model(name):
    """Extract GL.iNet model, e.g. 'Beryl AX (GL-MT3000)' -> 'gl-mt3000'."""
    m = re.search(r"(GL-\w+)", name, re.IGNORECASE)
    if m:
        return m.group(1).lower()
    cleaned = re.sub(r"(?i)^gl\.?i?net\s+", "", name).strip()
    return re.sub(r"[^a-z0-9]+", "-", cleaned.lower()).strip("-")


def _argon_slug(name):
    """Turn 'Argon ONE M.2' -> 'argon-one-m-2'."""
    return re.sub(r"[^a-z0-9]+", "-", name.lower()).strip("-")


# Apple product image CDN URLs (manually curated for known products)
APPLE_IMAGE_URLS = {
    "homepod mini": "https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/homepod-mini-select-202110?wid=800&hei=800&fmt=jpeg&qlt=90",
    "homepod": "https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/homepod-select-202301?wid=800&hei=800&fmt=jpeg&qlt=90",
    "apple tv 4k": "https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/apple-tv-4k-hero-select-202210?wid=800&hei=800&fmt=jpeg&qlt=90",
    "apple tv hd": "https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/apple-tv-hero-select-201709?wid=800&hei=800&fmt=jpeg&qlt=90",
    "airtag": "https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/airtag-single-select-202104?wid=800&hei=800&fmt=jpeg&qlt=90",
    "ipad": "https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/ipad-10th-gen-finish-select-202212-blue-wifi?wid=800&hei=800&fmt=jpeg&qlt=90",
    "apple watch se": "https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MQDY3ref_VW_34FR+watch-case-44-aluminum-midnight-nc-se_VW_34FR?wid=800&hei=800&fmt=jpeg&qlt=90",
    "apple watch series 9": "https://store.storeimages.cdn-apple.com/4982/as-images.apple.com/is/MR933ref_VW_34FR+watch-case-45-aluminum-midnight-nc-s9_VW_34FR?wid=800&hei=800&fmt=jpeg&qlt=90",
}


def _apple_product_url(name):
    """Return Apple CDN image URL for known products."""
    low = name.lower()
    for key, url in APPLE_IMAGE_URLS.items():
        if key in low:
            return url
    return None


# IKEA product article number lookup (needed for IKEA image URLs)
IKEA_PRODUCTS = {
    "dirigera": "10503406",
    "tradfri led": "00486783",
    "tradfri motion": "70429913",
    "styrbar": "30488370",
    "somrig": "90563826",
    "tretakt": "50534458",
    "fyrtur": "90417462",
    "starkvind": "00460474",
    "rodret": "80520054",
    "parasoll": "60504602",
    "vindstyrka": "70498243",
    "vallhorn": "20504601",
}


def _ikea_product_url(name):
    """Return IKEA image URL for known products."""
    low = name.lower()
    for key, article in IKEA_PRODUCTS.items():
        if key in low:
            return f"https://www.ikea.com/us/en/images/products/{key}-smart-home-{article}_0_PE-S5.JPG"
    return None


BRAND_URL_BUILDERS = {
    # Synology -- direct image URL
    "synology": lambda name, brand: (
        "https://www.synology.com/img/products/detail/{model}/heading.png".format(
            model=_synology_model(name)
        )
        if _synology_model(name) else None
    ),
    # Home Assistant -- direct image URL (verified 2026-02)
    "home assistant": lambda name, brand: (
        {
            "green": "https://www.home-assistant.io/images/blog/2023-09-ha10/green-intro.png",
            "yellow": "https://www.home-assistant.io/images/yellow/yellow_hero.jpg",
            "skyconnect": "https://www.home-assistant.io/images/connectzbt1/connectzbt1-cover.jpg",
            "voice-pe": "https://www.home-assistant.io/images/voice-pe/og.jpg",
        }.get(_home_assistant_model(name))
        if _home_assistant_model(name) else None
    ),
    # Raspberry Pi -- direct image URL
    "raspberry pi": lambda name, brand: (
        "https://www.raspberrypi.com/app/uploads/2024/03/{slug}.png".format(
            slug=_raspberry_pi_slug(name)
        )
        if _raspberry_pi_slug(name) else None
    ),
    # Apple -- CDN direct URLs for known products
    "apple": lambda name, brand: _apple_product_url(name),
    # IKEA -- lookup table for known smart home products
    "ikea": lambda name, brand: _ikea_product_url(name),
}

# Pages to scrape (we look for og:image or product image tags)
BRAND_SEARCH_PAGES = {
    "aqara": lambda name, brand: (
        "https://www.aqara.com/en/product/{slug}/".format(slug=_aqara_slug(name))
    ),
    "sonoff": lambda name, brand: (
        "https://sonoff.tech/product/{slug}/".format(slug=_sonoff_slug(name))
    ),
    # Google Nest products
    "google": lambda name, brand: (
        "https://store.google.com/us/product/{slug}".format(slug=_google_nest_slug(name))
    ),
    # Ubiquiti / UniFi
    "ubiquiti": lambda name, brand: (
        "https://store.ui.com/us/en/pro/category/all-unifi-cloud-gateways/products/{slug}".format(
            slug=_ubiquiti_slug(name))
    ),
    "unifi": lambda name, brand: (
        "https://store.ui.com/us/en/pro/category/all-unifi-cloud-gateways/products/{slug}".format(
            slug=_ubiquiti_slug(name))
    ),
    # TP-Link (use search page since category paths vary)
    "tp-link": lambda name, brand: (
        "https://www.tp-link.com/us/search/?q={model}".format(
            model=_tp_link_model(name))
    ),
    "tapo": lambda name, brand: (
        "https://www.tp-link.com/us/search/?q={model}".format(
            model=_tp_link_model(name))
    ),
    "kasa": lambda name, brand: (
        "https://www.tp-link.com/us/search/?q={model}".format(
            model=_tp_link_model(name))
    ),
    # Reolink
    "reolink": lambda name, brand: (
        "https://reolink.com/product/{slug}/".format(slug=_reolink_slug(name))
    ),
    # Sonos
    "sonos": lambda name, brand: (
        "https://www.sonos.com/en-us/shop/{slug}".format(slug=_sonos_slug(name))
    ),
    # SwitchBot (Shopify)
    "switchbot": lambda name, brand: (
        "https://www.switch-bot.com/products/{slug}".format(slug=_switchbot_slug(name))
    ),
    # Shelly (Shopify)
    "shelly": lambda name, brand: (
        "https://us.shelly.com/products/{slug}".format(slug=_shelly_slug(name))
    ),
    # QNAP
    "qnap": lambda name, brand: (
        "https://www.qnap.com/en/product/{model}".format(model=_qnap_model(name))
        if _qnap_model(name) else None
    ),
    # Eufy (Shopify)
    "eufy": lambda name, brand: (
        "https://www.eufy.com/products/{slug}".format(slug=_eufy_slug(name))
    ),
    # Nanoleaf (Shopify)
    "nanoleaf": lambda name, brand: (
        "https://us-shop.nanoleaf.me/products/{slug}".format(slug=_nanoleaf_slug(name))
    ),
    # Meross (Shopify)
    "meross": lambda name, brand: (
        "https://shop.meross.com/products/{slug}".format(slug=re.sub(r"[^a-z0-9]+", "-", name.lower()).strip("-"))
    ),
    # Eve HomeKit
    "eve": lambda name, brand: (
        "https://www.evehome.com/en-us/{slug}".format(slug=_eve_slug(name))
    ),
    # Roborock
    "roborock": lambda name, brand: (
        "https://us.roborock.com/pages/roborock-{slug}".format(slug=_roborock_slug(name))
    ),
    # MikroTik
    "mikrotik": lambda name, brand: (
        "https://mikrotik.com/product/{slug}".format(slug=_mikrotik_slug(name))
    ),
    # GL.iNet
    "gl.inet": lambda name, brand: (
        "https://www.gl-inet.com/products/{model}/".format(model=_glinet_model(name))
    ),
    # Beelink (Shopify)
    "beelink": lambda name, brand: (
        "https://www.bee-link.com/products/{slug}".format(slug=_beelink_slug(name))
    ),
    # Argon (Shopify)
    "argon": lambda name, brand: (
        "https://argon40.com/products/{slug}".format(slug=_argon_slug(name))
    ),
    # Samsung SmartThings
    "samsung": lambda name, brand: (
        "https://www.samsung.com/us/search/searchMain/?searchTerm={q}".format(
            q=quote_plus(name))
    ),
}

# ---------------------------------------------------------------------------
# Logging setup
# ---------------------------------------------------------------------------

def setup_logging():
    logger = logging.getLogger("fetch_images_v2")
    logger.setLevel(logging.DEBUG)

    # File handler -- detailed
    fh = logging.FileHandler(LOG_PATH, mode="a", encoding="utf-8")
    fh.setLevel(logging.DEBUG)
    fh.setFormatter(logging.Formatter(
        "%(asctime)s  %(levelname)-8s  %(message)s", datefmt="%Y-%m-%d %H:%M:%S"
    ))
    logger.addHandler(fh)

    # Console handler -- info+
    ch = logging.StreamHandler(sys.stdout)
    ch.setLevel(logging.INFO)
    ch.setFormatter(logging.Formatter("%(message)s"))
    logger.addHandler(ch)

    return logger


log = setup_logging()

# ---------------------------------------------------------------------------
# Progress tracker  (JSON-based, resumable)
# ---------------------------------------------------------------------------

class ProgressTracker:
    """Persist per-SKU results so the script can resume after interruption."""

    def __init__(self, path):
        self.path = path
        self.data = self._load()

    def _load(self):
        if os.path.exists(self.path):
            try:
                with open(self.path, "r") as f:
                    return json.load(f)
            except (json.JSONDecodeError, IOError):
                log.warning("Corrupt progress file -- starting fresh")
        return {}

    def save(self):
        tmp = self.path + ".tmp"
        with open(tmp, "w") as f:
            json.dump(self.data, f, indent=2)
        os.replace(tmp, self.path)

    def is_done(self, sku):
        entry = self.data.get(sku)
        if not entry:
            return False
        return entry.get("status") in ("uploaded", "downloaded", "skipped_service")

    def record(self, sku, status, source=None, image_path=None, woo_id=None, error=None):
        self.data[sku] = {
            "status": status,
            "source": source,
            "image_path": image_path,
            "woo_id": woo_id,
            "error": str(error) if error else None,
            "timestamp": datetime.now().isoformat(),
        }

    def summary(self):
        counts = {}
        for entry in self.data.values():
            s = entry.get("status", "unknown")
            counts[s] = counts.get(s, 0) + 1
        return counts


# ---------------------------------------------------------------------------
# WooCommerce API helper
# ---------------------------------------------------------------------------

class WooCommerceAPI:
    """Thin wrapper around the WooCommerce REST API v3 using requests."""

    def __init__(self, store_url, ck, cs):
        self.base = store_url.rstrip("/") + "/wp-json/wc/v3"
        self.auth = (ck, cs)
        self.session = requests.Session()
        self.session.auth = self.auth
        self.session.headers.update({"Content-Type": "application/json"})

    # -- products ----------------------------------------------------------

    def get_all_products(self):
        """Yield all products (id, sku) from the store, paginated."""
        page = 1
        while True:
            url = f"{self.base}/products"
            params = {"per_page": 100, "page": page}
            resp = self.session.get(url, params=params, timeout=30)
            resp.raise_for_status()
            products = resp.json()
            if not products:
                break
            for p in products:
                yield p
            if len(products) < 100:
                break
            page += 1
            time.sleep(API_REQUEST_DELAY)

    def get_product_by_sku(self, sku):
        """Return the first product matching this SKU, or None."""
        url = f"{self.base}/products"
        params = {"sku": sku, "per_page": 1}
        resp = self.session.get(url, params=params, timeout=30)
        resp.raise_for_status()
        products = resp.json()
        if products:
            return products[0]
        return None

    def update_product_image(self, product_id, image_url):
        """Set the featured image of a product by providing a public URL.

        WooCommerce will sideload the image from the URL.
        """
        url = f"{self.base}/products/{product_id}"
        data = {"images": [{"src": image_url}]}
        resp = self.session.put(url, json=data, timeout=60)
        resp.raise_for_status()
        return resp.json()

    def update_product_image_local(self, product_id, image_path):
        """Upload a local image file as the product's featured image.

        Steps:
        1. Upload to WP media library via wp/v2/media
        2. Attach as product featured image via wc/v3/products/{id}
        """
        media_url = self.base.replace("/wc/v3", "") + "/wp/v2/media"

        filename = os.path.basename(image_path)
        with open(image_path, "rb") as f:
            file_data = f.read()

        headers = {
            "Content-Disposition": f'attachment; filename="{filename}"',
            "Content-Type": "image/jpeg",
        }
        resp = self.session.post(
            media_url, data=file_data, headers=headers, timeout=60
        )
        if resp.status_code in (201, 200):
            media = resp.json()
            media_id = media.get("id")
            media_src = media.get("source_url", "")
            # Now attach to product
            prod_url = f"{self.base}/products/{product_id}"
            data = {"images": [{"id": media_id, "src": media_src}]}
            resp2 = self.session.put(prod_url, json=data, timeout=60)
            resp2.raise_for_status()
            return resp2.json()
        else:
            # Fallback: try the src-based method with a publicly reachable path
            raise RuntimeError(
                f"Media upload failed ({resp.status_code}): {resp.text[:300]}"
            )

    def build_sku_to_id_map(self):
        """Return dict {sku: product_id} for every product in the store."""
        mapping = {}
        log.info("Building SKU -> WooCommerce product ID map ...")
        for p in self.get_all_products():
            sku = p.get("sku", "")
            pid = p.get("id")
            if sku and pid:
                mapping[sku] = {
                    "id": pid,
                    "has_image": bool(p.get("images")),
                    "image_src": (
                        p["images"][0].get("src", "") if p.get("images") else ""
                    ),
                }
        log.info(f"  Found {len(mapping)} products with SKUs in the store")
        return mapping


# ---------------------------------------------------------------------------
# Image fetcher engine
# ---------------------------------------------------------------------------

class ImageFetcher:
    """Core engine: resolves an image URL for each product, downloads, validates."""

    def __init__(self, images_dir):
        self.images_dir = images_dir
        os.makedirs(self.images_dir, exist_ok=True)
        self.session = requests.Session()
        self.session.headers.update(HEADERS)

    # -- Source 1: CSV real URLs -------------------------------------------

    @staticmethod
    def load_csv_image_urls(csv_path):
        """Return dict {sku: image_url} for rows with non-placeholder URLs."""
        mapping = {}
        with open(csv_path, "r", encoding="utf-8") as f:
            reader = csv.DictReader(f)
            for row in reader:
                sku = row.get("SKU", "").strip()
                url = row.get("Images", "").strip()
                if sku and url and "via.placeholder.com" not in url:
                    mapping[sku] = url
        return mapping

    # -- Source 2: brand-specific direct URL --------------------------------

    @staticmethod
    def get_brand_direct_url(name, brand):
        """Try to build a direct image URL from brand patterns."""
        key = brand.lower().strip()
        builder = BRAND_URL_BUILDERS.get(key)
        if builder:
            try:
                return builder(name, brand)
            except Exception:
                pass
        return None

    # -- Source 2b: brand search page (scrape og:image / product img) ------

    def scrape_brand_page_image(self, name, brand):
        """Fetch a brand product page and extract the main product image."""
        key = brand.lower().strip()
        page_fn = BRAND_SEARCH_PAGES.get(key)
        if not page_fn:
            return None
        page_url = page_fn(name, brand)
        if not page_url:
            return None

        log.debug(f"  Scraping brand page: {page_url}")
        try:
            resp = self.session.get(page_url, timeout=15, allow_redirects=True)
            if resp.status_code >= 300:
                log.debug(f"  Brand page returned {resp.status_code}")
                return None
            html = resp.text

            # Strategy A: Open Graph image (check both attribute orders)
            og_match = re.search(
                r'<meta\s+(?:property=["\']og:image["\']\s+content=["\']([^"\']+)["\']'
                r'|content=["\']([^"\']+)["\']\s+property=["\']og:image["\'])',
                html, re.IGNORECASE,
            )
            if og_match:
                img_url = og_match.group(1) or og_match.group(2)
                if img_url.startswith("//"):
                    img_url = "https:" + img_url
                if self._looks_like_image_url(img_url):
                    return img_url

            # Strategy B: First large product image in HTML
            img_tags = re.findall(
                r'<img[^>]+src=["\']([^"\']+)["\']', html, re.IGNORECASE
            )
            for src in img_tags:
                if src.startswith("//"):
                    src = "https:" + src
                if not src.startswith("http"):
                    src = urljoin(page_url, src)
                if self._is_product_image_candidate(src, name):
                    return src

        except requests.RequestException as e:
            log.debug(f"  Brand page scrape error: {e}")
        return None

    # -- Source 3: Bing Image Search ---------------------------------------

    def search_bing_images(self, name, brand):
        """Search Bing Images and return the first usable image URL."""
        query = f"{brand} {name} product photo official"
        url = "https://www.bing.com/images/search"
        params = {"q": query, "form": "HDRSC2", "first": 1}

        log.debug(f"  Bing search: {query}")
        try:
            # Use a fresh session to avoid cookie contamination from brand pages
            bing_session = requests.Session()
            bing_session.headers.update(HEADERS)
            resp = bing_session.get(url, params=params, timeout=15)
            if resp.status_code != 200:
                log.debug(f"  Bing returned {resp.status_code}")
                return None

            html = resp.text

            # Bing encodes JSON data with &quot; HTML entities in data attributes
            # Decode them first so the murl regex can match
            decoded = html_mod.unescape(html)

            # Bing stores image metadata in "murl" parameters inside data attributes
            # Pattern: murl":"https://...jpg"
            murl_matches = re.findall(r'"murl":"(https?://[^"]+)"', decoded)
            for candidate in murl_matches[:8]:
                if self._looks_like_image_url(candidate):
                    return candidate

            # Fallback: look for thumbnail images (turl)
            turl_matches = re.findall(r'"turl":"(https?://[^"]+)"', decoded)
            for candidate in turl_matches[:5]:
                if self._looks_like_image_url(candidate):
                    return candidate

        except requests.RequestException as e:
            log.debug(f"  Bing search error: {e}")
        return None

    # -- Source 4: DuckDuckGo Image Search -----------------------------------

    def search_duckduckgo_images(self, name, brand):
        """Search DuckDuckGo Images and return the first usable image URL."""
        query = f"{brand} {name} product photo"

        log.debug(f"  DuckDuckGo search: {query}")
        try:
            # Use a fresh session to avoid cookie contamination from brand pages
            ddg_session = requests.Session()
            ddg_session.headers.update(HEADERS)

            # Step 1: Get vqd token from DDG search page
            ddg_url = "https://duckduckgo.com/"
            params = {"q": query, "ia": "images", "iax": "images"}
            resp = ddg_session.get(ddg_url, params=params, timeout=15)
            if resp.status_code != 200:
                log.debug(f"  DDG returned {resp.status_code}")
                return None

            # Extract vqd token
            vqd_match = re.search(r'vqd=["\']([^"\']+)["\']', resp.text)
            if not vqd_match:
                vqd_match = re.search(r"vqd=(\d+-\d+)", resp.text)
            if not vqd_match:
                log.debug("  DDG: couldn't extract vqd token")
                return None

            vqd = vqd_match.group(1)

            # Step 2: Query the DDG images API
            api_url = "https://duckduckgo.com/i.js"
            api_params = {
                "l": "us-en",
                "o": "json",
                "q": query,
                "vqd": vqd,
                "f": ",,,,,",
                "p": "1",
            }
            api_headers = {**HEADERS, "Referer": "https://duckduckgo.com/"}
            resp2 = ddg_session.get(
                api_url, params=api_params, headers=api_headers, timeout=15
            )
            if resp2.status_code != 200:
                log.debug(f"  DDG API returned {resp2.status_code}")
                return None

            data = resp2.json()
            results = data.get("results", [])
            for result in results[:8]:
                img_url = result.get("image", "")
                if img_url and self._looks_like_image_url(img_url):
                    return img_url
                # Also try thumbnail
                thumb_url = result.get("thumbnail", "")
                if thumb_url and self._looks_like_image_url(thumb_url):
                    return thumb_url

        except (requests.RequestException, json.JSONDecodeError, KeyError) as e:
            log.debug(f"  DDG search error: {e}")
        return None

    # -- Source 5: AliExpress search ----------------------------------------

    def search_aliexpress(self, name, brand):
        """Search AliExpress for product image."""
        query = f"{brand} {name}"
        url = f"https://www.aliexpress.com/w/wholesale-{quote_plus(query)}.html"

        log.debug(f"  AliExpress search: {query}")
        try:
            resp = self.session.get(url, timeout=15, allow_redirects=True)
            if resp.status_code != 200:
                log.debug(f"  AliExpress returned {resp.status_code}")
                return None

            html = resp.text

            # AliExpress product images often in img tags with ae-image URLs
            img_matches = re.findall(
                r'(https?://[a-z0-9.-]*alicdn\.com/[^"\'>\s]+\.(?:jpg|png|webp))',
                html, re.IGNORECASE,
            )
            for candidate in img_matches[:10]:
                # Skip tiny thumbnails (patterns like _50x50, /44x32.png, etc.)
                if re.search(r"[_/]\d{1,3}x\d{1,3}", candidate):
                    continue
                # Skip known tracking/placeholder patterns
                if "/kf/" in candidate and candidate.endswith(".png") and "44x32" in candidate:
                    continue
                return candidate

        except requests.RequestException as e:
            log.debug(f"  AliExpress search error: {e}")
        return None

    # -- Download & validate -----------------------------------------------

    def download_and_validate(self, url, sku, retries=MAX_RETRIES):
        """Download image from URL, validate it, resize, save as {sku}.jpg.

        Returns the local file path on success, None on failure.
        """
        save_path = os.path.join(self.images_dir, f"{sku}.jpg")

        # If already downloaded and valid, skip
        if os.path.exists(save_path) and os.path.getsize(save_path) > 1000:
            log.debug(f"  Already downloaded: {save_path}")
            return save_path

        for attempt in range(1, retries + 1):
            try:
                log.debug(f"  Download attempt {attempt}: {url}")
                resp = self.session.get(url, timeout=30, stream=True)

                if resp.status_code != 200:
                    log.debug(f"  HTTP {resp.status_code} for {url}")
                    if attempt < retries:
                        time.sleep(1)
                    continue

                content_type = resp.headers.get("Content-Type", "")
                # Reject obvious non-image responses
                if "text/html" in content_type and "image" not in content_type:
                    # Could be a redirect page; read a small amount to check
                    data = resp.content[:5000]
                    if b"<html" in data.lower() or b"<!doctype" in data.lower():
                        log.debug(f"  Got HTML instead of image from {url}")
                        return None
                    data += resp.content[5000:]
                else:
                    data = resp.content

                if len(data) < 500:
                    log.debug(f"  Response too small ({len(data)} bytes)")
                    if attempt < retries:
                        time.sleep(1)
                    continue

                # Validate with Pillow
                img = Image.open(io.BytesIO(data))
                img.verify()  # verify integrity

                # Re-open after verify (verify closes internal state)
                img = Image.open(io.BytesIO(data))

                # Check minimum dimensions (reject tiny icons)
                if img.width < 50 or img.height < 50:
                    log.debug(f"  Image too small ({img.width}x{img.height})")
                    return None

                # Convert to RGB for JPEG
                if img.mode in ("RGBA", "P", "LA", "PA"):
                    background = Image.new("RGB", img.size, (255, 255, 255))
                    if img.mode == "P":
                        img = img.convert("RGBA")
                    if "A" in img.mode:
                        background.paste(img, mask=img.split()[-1])
                        img = background
                    else:
                        img = img.convert("RGB")
                elif img.mode != "RGB":
                    img = img.convert("RGB")

                # Resize preserving aspect ratio
                img.thumbnail(MAX_IMAGE_SIZE, Image.Resampling.LANCZOS)

                # Save
                img.save(save_path, "JPEG", quality=JPEG_QUALITY, optimize=True)
                log.debug(f"  Saved: {save_path} ({os.path.getsize(save_path)} bytes)")
                return save_path

            except (IOError, OSError, SyntaxError) as e:
                log.debug(f"  Image validation error: {e}")
                if attempt < retries:
                    time.sleep(1)
            except requests.RequestException as e:
                log.debug(f"  Download error: {e}")
                if attempt < retries:
                    time.sleep(1)

        return None

    # -- Helpers -----------------------------------------------------------

    @staticmethod
    def _looks_like_image_url(url):
        """Quick heuristic: does this URL look like it points to an image?"""
        if not url or not url.startswith("http"):
            return False
        parsed = urlparse(url)
        path_lower = parsed.path.lower()
        # Common image extensions
        if any(path_lower.endswith(ext) for ext in
               (".jpg", ".jpeg", ".png", ".webp", ".gif", ".bmp", ".avif")):
            return True
        # Some CDN URLs don't have extensions but contain image hints
        if any(kw in url.lower() for kw in
               ("image", "img", "photo", "product", "upload", "media", "cdn")):
            return True
        return False

    @staticmethod
    def _is_product_image_candidate(url, product_name):
        """Heuristic filter: is this <img> src likely a product photo?"""
        low = url.lower()
        # Reject common non-product images
        reject_patterns = [
            "logo", "icon", "favicon", "sprite", "banner", "arrow",
            "button", "bg-", "background", "avatar", "social",
            "facebook", "twitter", "instagram", "youtube", "pinterest",
            "flag", "payment", "badge", "star", "rating",
            "1x1", "pixel", "tracking", "analytics",
        ]
        for pat in reject_patterns:
            if pat in low:
                return False

        # Must look like an image URL
        if not ImageFetcher._looks_like_image_url(url):
            return False

        # Prefer URLs with product-related keywords
        good_patterns = ["product", "upload", "media", "img", "image", "cdn"]
        if any(p in low for p in good_patterns):
            return True

        # Accept if it has a recognized image extension
        parsed = urlparse(url)
        if any(parsed.path.lower().endswith(ext) for ext in
               (".jpg", ".jpeg", ".png", ".webp")):
            return True

        return False


# ---------------------------------------------------------------------------
# Main orchestrator
# ---------------------------------------------------------------------------

class FetchImagesV2:
    """Top-level orchestrator: reads product lists, resolves images, uploads."""

    def __init__(self, args):
        self.args = args
        self.images_dir = args.images_dir
        self.fetcher = ImageFetcher(self.images_dir)
        self.progress = ProgressTracker(PROGRESS_PATH)
        self.woo = WooCommerceAPI(STORE_URL, CONSUMER_KEY, CONSUMER_SECRET)
        self.sku_to_woo = {}  # populated lazily
        self.csv_urls = {}    # populated from CSV

        # Stats for this run
        self.stats = {
            "processed": 0,
            "skipped_done": 0,
            "skipped_service": 0,
            "csv_url_success": 0,
            "brand_direct_success": 0,
            "brand_scrape_success": 0,
            "bing_success": 0,
            "duckduckgo_success": 0,
            "aliexpress_success": 0,
            "uploaded": 0,
            "upload_failed": 0,
            "download_failed": 0,
            "no_source_found": 0,
        }

    def load_products(self):
        """Load the product list from image-fetch-list.csv."""
        if not os.path.exists(IMAGE_LIST_PATH):
            log.error(f"Product list not found: {IMAGE_LIST_PATH}")
            log.error("Create a CSV with columns: sku, name, brand, category")
            sys.exit(1)
        products = []
        with open(IMAGE_LIST_PATH, "r", encoding="utf-8") as f:
            reader = csv.DictReader(f)
            for row in reader:
                products.append({
                    "sku": row.get("sku", "").strip(),
                    "name": row.get("name", "").strip(),
                    "brand": row.get("brand", "").strip(),
                    "category": row.get("category", "").strip(),
                })
        return products

    def resolve_image(self, sku, name, brand, category):
        """Try each source in priority order. Return (image_path, source_name, source_url) or (None, None, None)."""

        # Source 1: Real URL from CSV
        csv_url = self.csv_urls.get(sku)
        if csv_url:
            log.debug(f"  Trying CSV URL: {csv_url}")
            path = self.fetcher.download_and_validate(csv_url, sku)
            if path:
                self.stats["csv_url_success"] += 1
                return path, "csv_url", csv_url
            time.sleep(WEB_REQUEST_DELAY)

        # Source 2a: Brand direct URL (computed pattern)
        direct_url = ImageFetcher.get_brand_direct_url(name, brand)
        if direct_url:
            log.debug(f"  Trying brand direct URL: {direct_url}")
            path = self.fetcher.download_and_validate(direct_url, sku)
            if path:
                self.stats["brand_direct_success"] += 1
                return path, "brand_direct", direct_url
            time.sleep(WEB_REQUEST_DELAY)

        # Source 2b: Brand search page scrape
        scraped_url = self.fetcher.scrape_brand_page_image(name, brand)
        if scraped_url:
            log.debug(f"  Trying scraped brand image: {scraped_url}")
            path = self.fetcher.download_and_validate(scraped_url, sku)
            if path:
                self.stats["brand_scrape_success"] += 1
                return path, "brand_scrape", scraped_url
            time.sleep(WEB_REQUEST_DELAY)

        # Source 3: AliExpress search
        ali_url = self.fetcher.search_aliexpress(name, brand)
        if ali_url:
            log.debug(f"  Trying AliExpress image: {ali_url}")
            path = self.fetcher.download_and_validate(ali_url, sku)
            if path:
                self.stats["aliexpress_success"] += 1
                return path, "aliexpress", ali_url
            time.sleep(WEB_REQUEST_DELAY)

        # Source 4: DuckDuckGo Image Search
        ddg_url = self.fetcher.search_duckduckgo_images(name, brand)
        if ddg_url:
            log.debug(f"  Trying DuckDuckGo image: {ddg_url}")
            path = self.fetcher.download_and_validate(ddg_url, sku)
            if path:
                self.stats["duckduckgo_success"] += 1
                return path, "duckduckgo", ddg_url
            time.sleep(WEB_REQUEST_DELAY)

        # Source 5: Bing Image Search
        bing_url = self.fetcher.search_bing_images(name, brand)
        if bing_url:
            log.debug(f"  Trying Bing image: {bing_url}")
            path = self.fetcher.download_and_validate(bing_url, sku)
            if path:
                self.stats["bing_success"] += 1
                return path, "bing_search", bing_url
            time.sleep(WEB_REQUEST_DELAY)

        return None, None, None

    def upload_to_woocommerce(self, sku, image_path, source_url=None):
        """Set the product image on WooCommerce using URL sideloading.

        WooCommerce will download the image from source_url and attach it
        to the product. This uses the WC REST API (authenticated via
        consumer key/secret) rather than the WP REST API media endpoint.
        """
        woo_info = self.sku_to_woo.get(sku)
        if not woo_info:
            log.warning(f"  SKU {sku} not found in WooCommerce store -- cannot upload")
            return False

        product_id = woo_info["id"]
        if not source_url:
            log.warning(f"  No source URL for {sku} -- cannot sideload to WooCommerce")
            return False

        try:
            log.debug(f"  Sideloading {source_url} to product {product_id} ...")
            self.woo.update_product_image(product_id, source_url)
            log.info(f"  Uploaded to WooCommerce product {product_id}")
            self.stats["uploaded"] += 1
            return True
        except Exception as e:
            log.warning(f"  WooCommerce upload failed for {sku}: {e}")
            self.stats["upload_failed"] += 1
            return False

    def run(self):
        """Main entry point."""
        start_time = datetime.now()
        log.info("=" * 70)
        log.info("NGS Product Image Fetcher v2")
        log.info(f"Started: {start_time.strftime('%Y-%m-%d %H:%M:%S')}")
        log.info("=" * 70)

        # Load CSV image URLs
        log.info(f"Loading CSV image URLs from {CSV_PATH} ...")
        self.csv_urls = ImageFetcher.load_csv_image_urls(CSV_PATH)
        log.info(f"  Found {len(self.csv_urls)} products with real (non-placeholder) image URLs")

        # Load product list
        products = self.load_products()
        log.info(f"Loaded {len(products)} products from {IMAGE_LIST_PATH}")

        # Apply filters
        if self.args.brand:
            brand_filter = self.args.brand.lower()
            products = [p for p in products if p["brand"].lower() == brand_filter]
            log.info(f"Filtered to {len(products)} products for brand: {self.args.brand}")

        # Apply start/limit
        if self.args.start:
            products = products[self.args.start:]
            log.info(f"Starting from product index {self.args.start}")
        if self.args.limit:
            products = products[:self.args.limit]
            log.info(f"Limited to {self.args.limit} products")

        if not products:
            log.info("No products to process. Exiting.")
            return

        # Build WooCommerce SKU map (unless dry-run or skip-upload)
        if not self.args.dry_run and not self.args.skip_upload:
            self.sku_to_woo = self.woo.build_sku_to_id_map()

        log.info(f"Images directory: {self.images_dir}")
        if self.args.dry_run:
            log.info("DRY RUN MODE -- no downloads or uploads will happen")
        if self.args.skip_upload:
            log.info("SKIP UPLOAD MODE -- images downloaded locally only")
        log.info("-" * 70)

        # Process each product
        total = len(products)
        for idx, product in enumerate(products):
            sku = product["sku"]
            name = product["name"]
            brand = product["brand"]
            category = product["category"]

            self.stats["processed"] += 1

            # Skip NGS service products (no physical product image)
            if brand.upper() == "NGS" and category.lower() in ("services",):
                log.debug(f"[{idx+1}/{total}] {sku} -- skipping service product")
                self.stats["skipped_service"] += 1
                self.progress.record(sku, "skipped_service")
                continue

            # Skip if already completed in a previous run
            if self.progress.is_done(sku):
                log.debug(f"[{idx+1}/{total}] {sku} -- already done (previous run)")
                self.stats["skipped_done"] += 1
                continue

            log.info(f"[{idx+1}/{total}] {sku}: {brand} {name}")

            if self.args.dry_run:
                # Show what sources would be tried
                csv_url = self.csv_urls.get(sku)
                direct_url = ImageFetcher.get_brand_direct_url(name, brand)
                log.info(f"  CSV URL: {csv_url or '(none)'}")
                log.info(f"  Brand direct URL: {direct_url or '(none)'}")
                log.info(f"  Would also try: brand scrape, AliExpress, DuckDuckGo, Bing")
                continue

            # Resolve image
            image_path, source, source_url = self.resolve_image(sku, name, brand, category)

            if image_path:
                log.info(f"  Image found via: {source}")

                # Upload to WooCommerce
                if not self.args.skip_upload:
                    uploaded = self.upload_to_woocommerce(sku, image_path, source_url=source_url)
                    self.progress.record(
                        sku,
                        "uploaded" if uploaded else "download_only",
                        source=source,
                        image_path=image_path,
                        woo_id=self.sku_to_woo.get(sku, {}).get("id"),
                    )
                    time.sleep(API_REQUEST_DELAY)
                else:
                    log.info(f"  Saved locally: {image_path}")
                    self.progress.record(
                        sku, "downloaded", source=source, image_path=image_path
                    )
            else:
                log.info(f"  No image found from any source")
                self.stats["no_source_found"] += 1
                self.progress.record(sku, "no_image_found")

            # Save progress periodically
            if (idx + 1) % 5 == 0:
                self.progress.save()

            # Print progress every 10 products
            if (idx + 1) % 10 == 0:
                elapsed = (datetime.now() - start_time).total_seconds()
                rate = (idx + 1) / elapsed if elapsed > 0 else 0
                eta_seconds = (total - idx - 1) / rate if rate > 0 else 0
                eta_min = int(eta_seconds / 60)
                log.info(f"--- Progress: {idx+1}/{total} | "
                         f"Rate: {rate:.1f} prod/s | "
                         f"ETA: ~{eta_min} min ---")

        # Final save
        self.progress.save()

        # Print summary
        elapsed = (datetime.now() - start_time).total_seconds()
        log.info("")
        log.info("=" * 70)
        log.info("FETCH IMAGES v2 -- SUMMARY")
        log.info("=" * 70)
        log.info(f"Total processed:       {self.stats['processed']}")
        log.info(f"Skipped (prev done):   {self.stats['skipped_done']}")
        log.info(f"Skipped (services):    {self.stats['skipped_service']}")
        log.info(f"")
        log.info(f"Image sources:")
        log.info(f"  CSV real URL:        {self.stats['csv_url_success']}")
        log.info(f"  Brand direct URL:    {self.stats['brand_direct_success']}")
        log.info(f"  Brand page scrape:   {self.stats['brand_scrape_success']}")
        log.info(f"  AliExpress:          {self.stats['aliexpress_success']}")
        log.info(f"  DuckDuckGo:          {self.stats['duckduckgo_success']}")
        log.info(f"  Bing search:         {self.stats['bing_success']}")
        log.info(f"")
        log.info(f"Uploads to WooCommerce: {self.stats['uploaded']}")
        log.info(f"Upload failures:        {self.stats['upload_failed']}")
        log.info(f"Download failures:      {self.stats['download_failed']}")
        log.info(f"No source found:        {self.stats['no_source_found']}")
        log.info(f"")
        log.info(f"Elapsed time:          {elapsed:.0f}s ({elapsed/60:.1f} min)")
        log.info(f"Progress file:         {PROGRESS_PATH}")
        log.info(f"Log file:              {LOG_PATH}")
        log.info(f"Images directory:       {self.images_dir}")
        log.info("=" * 70)

        # Also print overall progress from the full progress file
        overall = self.progress.summary()
        log.info("")
        log.info("Overall progress (all runs combined):")
        for status, count in sorted(overall.items(), key=lambda x: -x[1]):
            log.info(f"  {status:25s}: {count}")


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------

def main():
    parser = argparse.ArgumentParser(
        description="NGS Product Image Fetcher v2 -- fetch images and upload to WooCommerce",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog=__doc__,
    )
    parser.add_argument(
        "--limit", type=int, default=None,
        help="Process only N products",
    )
    parser.add_argument(
        "--start", type=int, default=None,
        help="Start from the Nth product (0-indexed)",
    )
    parser.add_argument(
        "--brand", type=str, default=None,
        help="Only process products from a specific brand (case-insensitive)",
    )
    parser.add_argument(
        "--dry-run", action="store_true",
        help="Show what would be done without downloading or uploading",
    )
    parser.add_argument(
        "--skip-upload", action="store_true",
        help="Download images locally but do not upload to WooCommerce",
    )
    parser.add_argument(
        "--images-dir", type=str, default=DEFAULT_IMAGES_DIR,
        help=f"Directory to save downloaded images (default: {DEFAULT_IMAGES_DIR})",
    )
    parser.add_argument(
        "--reset-progress", action="store_true",
        help="Delete progress file and start fresh",
    )

    args = parser.parse_args()

    if args.reset_progress and os.path.exists(PROGRESS_PATH):
        os.remove(PROGRESS_PATH)
        log.info(f"Deleted progress file: {PROGRESS_PATH}")

    runner = FetchImagesV2(args)
    try:
        runner.run()
    except KeyboardInterrupt:
        log.info("\nInterrupted by user. Progress saved.")
        runner.progress.save()
        sys.exit(1)
    except Exception as e:
        log.error(f"Fatal error: {e}", exc_info=True)
        runner.progress.save()
        sys.exit(2)


if __name__ == "__main__":
    main()
