#!/usr/bin/env python3
"""
WooCommerce Store Builder for Neogen Smart Home Store
Usage:
    python3 woo_builder.py setup                  # Test API connection
    python3 woo_builder.py categories [--dry-run]  # Create category hierarchy
    python3 woo_builder.py attributes [--dry-run]  # Create product attributes
    python3 woo_builder.py products [--dry-run] [--limit N] [--start N]
    python3 woo_builder.py images [--dry-run] [--limit N] [--start N]
    python3 woo_builder.py configure [--dry-run]   # Store settings
    python3 woo_builder.py all [--dry-run]         # Run everything in order
"""

import argparse
import csv
import json
import os
import sys
import time
from pathlib import Path

try:
    import requests
    from requests.auth import HTTPBasicAuth
except ImportError:
    print("ERROR: 'requests' library required. Install with: pip3 install requests")
    sys.exit(1)

# ============================================================
# CONFIGURATION
# ============================================================

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

CSV_FILE = os.path.join(os.path.dirname(__file__), "woocommerce-final.csv")
IMAGES_DIR = os.path.join(os.path.dirname(__file__), "images")
PROGRESS_FILE = os.path.join(os.path.dirname(__file__), "woo_builder_progress.json")

BATCH_SIZE = 10
API_DELAY = 1.0  # seconds between batch calls


def _safe_int(value, default=0):
    """Convert value to int, returning default on failure."""
    try:
        return int(float(value))
    except (ValueError, TypeError):
        return default

# ============================================================
# CATEGORY HIERARCHY MAPPING
# ============================================================
CATEGORY_HIERARCHY = {
    "Security & Cameras": {
        "name_ar": "الأمان والكاميرات",
        "slug": "security-cameras",
        "children": {
            "Cameras & Security": {"name_ar": "كاميرات وأمان", "slug": "cameras-security"},
            "Indoor Cam": {"name_ar": "كاميرات داخلية", "slug": "indoor-cameras"},
            "Outdoor Cam": {"name_ar": "كاميرات خارجية", "slug": "outdoor-cameras"},
            "PTZ Cam": {"name_ar": "كاميرات PTZ", "slug": "ptz-cameras"},
            "Doorbell": {"name_ar": "أجراس ذكية", "slug": "smart-doorbells"},
            "NVR/DVR": {"name_ar": "أجهزة تسجيل", "slug": "nvr-dvr"},
            "Locks & Access": {"name_ar": "أقفال ذكية", "slug": "smart-locks"},
            "Security": {"name_ar": "أنظمة أمان", "slug": "security-systems"},
            "Remote Monitoring": {"name_ar": "مراقبة عن بعد", "slug": "remote-monitoring"},
            "Emergency Alert": {"name_ar": "تنبيهات طوارئ", "slug": "emergency-alert"},
        }
    },
    "Smart Lighting": {
        "name_ar": "الإضاءة الذكية",
        "slug": "smart-lighting",
        "children": {
            "Lighting": {"name_ar": "إضاءة", "slug": "lighting"},
            "Motion Lighting": {"name_ar": "إضاءة حركة", "slug": "motion-lighting"},
            "In-Wall/Ceiling": {"name_ar": "إضاءة مدمجة", "slug": "in-wall-ceiling"},
            "Covers": {"name_ar": "أغطية وستائر", "slug": "covers"},
        }
    },
    "Sensors & Hubs": {
        "name_ar": "المستشعرات والمراكز",
        "slug": "sensors-hubs",
        "children": {
            "Sensors": {"name_ar": "مستشعرات", "slug": "sensors"},
            "Sensor Module": {"name_ar": "وحدات استشعار", "slug": "sensor-modules"},
            "Leak System": {"name_ar": "كشف تسريب", "slug": "leak-detection"},
            "Hubs": {"name_ar": "مراكز تحكم", "slug": "hubs"},
            "Hubs & Bridges": {"name_ar": "هبات وجسور", "slug": "hubs-bridges"},
            "Bridge/Gateway": {"name_ar": "بوابات", "slug": "gateways"},
            "Smart Hub": {"name_ar": "مركز ذكي", "slug": "smart-hub"},
            "Coordinator": {"name_ar": "منسق", "slug": "coordinator"},
            "Zigbee Coordinator": {"name_ar": "منسق Zigbee", "slug": "zigbee-coordinator"},
            "Thread Border Router": {"name_ar": "راوتر Thread", "slug": "thread-border-router"},
        }
    },
    "Switches & Electrical": {
        "name_ar": "المفاتيح والكهربائيات",
        "slug": "switches-electrical",
        "children": {
            "Switches & Plugs": {"name_ar": "مفاتيح وبلاقات", "slug": "switches-plugs"},
            "Plugs": {"name_ar": "بلاقات ذكية", "slug": "smart-plugs"},
            "Smart Breaker": {"name_ar": "قواطع ذكية", "slug": "smart-breakers"},
            "Relay Module": {"name_ar": "ريلاي", "slug": "relay-modules"},
            "Smart Valve": {"name_ar": "صمامات ذكية", "slug": "smart-valves"},
            "Buttons": {"name_ar": "أزرار ذكية", "slug": "smart-buttons"},
        }
    },
    "Climate & Energy": {
        "name_ar": "التحكم بالمناخ والطاقة",
        "slug": "climate-energy",
        "children": {
            "Climate": {"name_ar": "تحكم بالمناخ", "slug": "climate-control"},
            "Fan": {"name_ar": "مراوح ذكية", "slug": "smart-fans"},
            "Ventilation": {"name_ar": "تهوية", "slug": "ventilation"},
            "Mini Split": {"name_ar": "مكيفات", "slug": "mini-split"},
            "Air Purifier": {"name_ar": "تنقية هواء", "slug": "air-purifiers"},
            "Humidifier": {"name_ar": "مرطبات", "slug": "humidifiers"},
            "Dehumidifier": {"name_ar": "مزيلات رطوبة", "slug": "dehumidifiers"},
            "Softener": {"name_ar": "ملطفات مياه", "slug": "water-softeners"},
            "Energy": {"name_ar": "إدارة طاقة", "slug": "energy-management"},
            "Energy Monitor": {"name_ar": "مراقبة طاقة", "slug": "energy-monitors"},
            "Smart EVSE": {"name_ar": "شحن سيارات", "slug": "ev-charging"},
            "Level 2 Charger": {"name_ar": "شاحن مستوى 2", "slug": "level-2-charger"},
            "Water Heater": {"name_ar": "سخانات ذكية", "slug": "smart-water-heaters"},
            "Water Quality": {"name_ar": "جودة المياه", "slug": "water-quality"},
        }
    },
    "Networking & Audio": {
        "name_ar": "الشبكات والصوت",
        "slug": "networking-audio",
        "children": {
            "Network": {"name_ar": "شبكات", "slug": "network"},
            "Router": {"name_ar": "راوترات", "slug": "routers"},
            "Router/Firewall": {"name_ar": "راوتر وجدار ناري", "slug": "router-firewall"},
            "Mesh": {"name_ar": "شبكة Mesh", "slug": "mesh-wifi"},
            "AP": {"name_ar": "أكسس بوينت", "slug": "access-points"},
            "Switch": {"name_ar": "سويتشات", "slug": "network-switches"},
            "Firewall": {"name_ar": "جدار ناري", "slug": "firewalls"},
            "Network Appliance": {"name_ar": "أجهزة شبكة", "slug": "network-appliances"},
            "Network Card": {"name_ar": "كروت شبكة", "slug": "network-cards"},
            "Audio": {"name_ar": "صوتيات", "slug": "audio"},
            "Wireless Speaker": {"name_ar": "مكبرات لاسلكية", "slug": "wireless-speakers"},
            "Soundbar": {"name_ar": "ساوندبار", "slug": "soundbars"},
            "Subwoofer": {"name_ar": "سبووفر", "slug": "subwoofers"},
            "Amplifier": {"name_ar": "مكبرات صوت", "slug": "amplifiers"},
            "Audio Streamer": {"name_ar": "بث صوتي", "slug": "audio-streamers"},
        }
    },
    "Voice & Smart Displays": {
        "name_ar": "المساعد الصوتي",
        "slug": "voice-smart-displays",
        "children": {
            "Voice Assistants": {"name_ar": "مساعدات صوتية", "slug": "voice-assistants"},
            "Voice": {"name_ar": "أجهزة صوتية", "slug": "voice-devices"},
            "Simple Voice": {"name_ar": "مكبرات ذكية بسيطة", "slug": "simple-voice"},
            "Smart Display": {"name_ar": "شاشات ذكية", "slug": "smart-displays"},
            "Video Calling": {"name_ar": "مكالمات فيديو", "slug": "video-calling"},
        }
    },
    "Home Assistant Hardware": {
        "name_ar": "أجهزة Home Assistant",
        "slug": "home-assistant-hardware",
        "children": {
            "HA Hardware": {"name_ar": "عتاد HA", "slug": "ha-hardware"},
            "Official HA": {"name_ar": "أجهزة HA الرسمية", "slug": "official-ha"},
            "Mini PC": {"name_ar": "أجهزة Mini PC", "slug": "mini-pc"},
            "SBC": {"name_ar": "حواسيب مصغرة", "slug": "sbc"},
            "Raspberry Pi": {"name_ar": "Raspberry Pi", "slug": "raspberry-pi"},
            "Pi Case": {"name_ar": "علب Pi", "slug": "pi-cases"},
            "Thin Client": {"name_ar": "Thin Client", "slug": "thin-clients"},
            "AI Accelerator": {"name_ar": "مسرعات AI", "slug": "ai-accelerators"},
            "GPU": {"name_ar": "كروت رسومية", "slug": "gpus"},
            "Microcontroller": {"name_ar": "متحكمات", "slug": "microcontrollers"},
        }
    },
    "NAS & Server": {
        "name_ar": "التخزين والسيرفرات",
        "slug": "nas-server",
        "children": {
            "NAS & Storage": {"name_ar": "NAS وتخزين", "slug": "nas-storage"},
            "NAS Servers": {"name_ar": "سيرفرات NAS", "slug": "nas-servers"},
            "HDD": {"name_ar": "أقراص HDD", "slug": "hdd"},
            "NVMe SSD": {"name_ar": "أقراص NVMe", "slug": "nvme-ssd"},
            "SATA SSD": {"name_ar": "أقراص SATA SSD", "slug": "sata-ssd"},
            "Server Parts": {"name_ar": "قطع سيرفر", "slug": "server-parts"},
            "Server CPU": {"name_ar": "معالجات سيرفر", "slug": "server-cpu"},
            "Server RAM": {"name_ar": "ذاكرة سيرفر", "slug": "server-ram"},
            "Server PSU": {"name_ar": "مزودات طاقة سيرفر", "slug": "server-psu"},
            "RAID Controller": {"name_ar": "متحكمات RAID", "slug": "raid-controllers"},
            "HBA Controller": {"name_ar": "متحكمات HBA", "slug": "hba-controllers"},
            "M.2 Adapter": {"name_ar": "محولات M.2", "slug": "m2-adapters"},
            "PCIe Riser": {"name_ar": "رايزر PCIe", "slug": "pcie-risers"},
            "Panel PC": {"name_ar": "حواسيب صناعية", "slug": "panel-pc"},
            "Power Supply": {"name_ar": "مزودات طاقة", "slug": "power-supply"},
            "UPS": {"name_ar": "UPS", "slug": "ups"},
            "UPS/Backup": {"name_ar": "UPS واحتياطي", "slug": "ups-backup"},
        }
    },
    "Smart Life": {
        "name_ar": "الحياة الذكية",
        "slug": "smart-life",
        "children": {
            "Health": {"name_ar": "صحة", "slug": "health"},
            "Fitness": {"name_ar": "لياقة", "slug": "fitness"},
            "Sleep Tech": {"name_ar": "تقنية النوم", "slug": "sleep-tech"},
            "Wellness Monitor": {"name_ar": "مراقبة صحية", "slug": "wellness-monitors"},
            "Smart Scale": {"name_ar": "ميزان ذكي", "slug": "smart-scales"},
            "Baby Tech": {"name_ar": "تقنية أطفال", "slug": "baby-tech"},
            "Baby Monitor": {"name_ar": "مراقبة أطفال", "slug": "baby-monitors"},
            "Smart Nursery": {"name_ar": "غرفة أطفال ذكية", "slug": "smart-nursery"},
            "Pet Tech": {"name_ar": "تقنية حيوانات", "slug": "pet-tech"},
            "Pet Camera": {"name_ar": "كاميرا حيوانات", "slug": "pet-cameras"},
            "Pet Door": {"name_ar": "باب حيوانات ذكي", "slug": "pet-doors"},
            "Smart Feeder": {"name_ar": "مطعم ذكي", "slug": "smart-feeders"},
            "Water Fountain": {"name_ar": "نافورة مياه", "slug": "water-fountains"},
            "Feeding": {"name_ar": "تغذية", "slug": "feeding"},
            "Elder Care": {"name_ar": "رعاية كبار السن", "slug": "elder-care"},
            "Fall Detection": {"name_ar": "كشف السقوط", "slug": "fall-detection"},
            "Medication": {"name_ar": "تذكير أدوية", "slug": "medication"},
            "GPS Tracker": {"name_ar": "تتبع GPS", "slug": "gps-trackers"},
            "Kitchen": {"name_ar": "مطبخ ذكي", "slug": "smart-kitchen"},
            "Coffee Maker": {"name_ar": "آلة قهوة", "slug": "coffee-makers"},
            "Cooking": {"name_ar": "طبخ ذكي", "slug": "smart-cooking"},
            "Refrigerator": {"name_ar": "ثلاجة ذكية", "slug": "smart-refrigerators"},
            "Small Appliances": {"name_ar": "أجهزة صغيرة", "slug": "small-appliances"},
            "Vacuums": {"name_ar": "مكانس ذكية", "slug": "robot-vacuums"},
            "Irrigation": {"name_ar": "ري ذكي", "slug": "smart-irrigation"},
            "Outdoor": {"name_ar": "خارجي", "slug": "outdoor"},
            "Tablet": {"name_ar": "أجهزة لوحية", "slug": "tablets"},
            "Gaming/Hybrid": {"name_ar": "ألعاب", "slug": "gaming"},
            "Travel": {"name_ar": "سفر", "slug": "travel"},
        }
    },
    "Media & Entertainment": {
        "name_ar": "الترفيه والوسائط",
        "slug": "media-entertainment",
        "children": {
            "Media": {"name_ar": "وسائط", "slug": "media"},
            "Streaming Box": {"name_ar": "أجهزة بث", "slug": "streaming-boxes"},
            "Streaming Stick": {"name_ar": "أعواد بث", "slug": "streaming-sticks"},
            "Portable": {"name_ar": "محمول", "slug": "portable"},
        }
    },
    "DIY & Maker": {
        "name_ar": "مشاريع DIY",
        "slug": "diy-maker",
        "children": {
            "DIY": {"name_ar": "DIY", "slug": "diy"},
            "DIY Display": {"name_ar": "شاشات DIY", "slug": "diy-displays"},
            "Display Module": {"name_ar": "وحدات عرض", "slug": "display-modules"},
            "Tools": {"name_ar": "أدوات", "slug": "tools"},
            "Pre-Configured Bundles": {"name_ar": "باقات جاهزة", "slug": "pre-configured-bundles"},
            "Services": {"name_ar": "خدمات", "slug": "services"},
        }
    },
    "Accessories": {
        "name_ar": "ملحقات",
        "slug": "accessories",
        "children": {
            "Accessories": {"name_ar": "ملحقات عامة", "slug": "general-accessories"},
            "Adapters": {"name_ar": "محولات", "slug": "adapters"},
            "Ethernet Cable": {"name_ar": "كيبلات إيثرنت", "slug": "ethernet-cables"},
            "USB Cable": {"name_ar": "كيبلات USB", "slug": "usb-cables"},
            "Mounts & Brackets": {"name_ar": "حوامل وقواعد", "slug": "mounts-brackets"},
            "Misc": {"name_ar": "متنوعات", "slug": "misc"},
            "Misc Components": {"name_ar": "قطع متنوعة", "slug": "misc-components"},
        }
    },
}

# Build reverse lookup: flat category name -> (parent_slug, child_slug)
CATEGORY_MAP = {}
for parent_name, parent_data in CATEGORY_HIERARCHY.items():
    for child_csv_name, child_data in parent_data["children"].items():
        CATEGORY_MAP[child_csv_name] = {
            "parent_name": parent_name,
            "parent_slug": parent_data["slug"],
            "child_slug": child_data["slug"],
            "child_name_ar": child_data["name_ar"],
        }


# ============================================================
# API CLIENT
# ============================================================
class WooAPI:
    def __init__(self, store_url, ck, cs):
        self.base_url = f"{store_url}/wp-json/wc/v3"
        self.wp_url = f"{store_url}/wp-json/wp/v2"
        self.auth = HTTPBasicAuth(ck, cs)
        self.session = requests.Session()
        self.session.auth = self.auth
        self.session.headers.update({"Content-Type": "application/json"})

    def get(self, endpoint, params=None):
        url = f"{self.base_url}/{endpoint}"
        resp = self.session.get(url, params=params, timeout=30)
        resp.raise_for_status()
        return resp.json()

    def post(self, endpoint, data):
        url = f"{self.base_url}/{endpoint}"
        resp = self.session.post(url, json=data, timeout=60)
        resp.raise_for_status()
        return resp.json()

    def put(self, endpoint, data):
        url = f"{self.base_url}/{endpoint}"
        resp = self.session.put(url, json=data, timeout=60)
        resp.raise_for_status()
        return resp.json()

    def get_all(self, endpoint, params=None):
        """Paginate through all results."""
        if params is None:
            params = {}
        params["per_page"] = 100
        page = 1
        all_items = []
        while True:
            params["page"] = page
            items = self.get(endpoint, params)
            if not items:
                break
            all_items.extend(items)
            if len(items) < 100:
                break
            page += 1
        return all_items

    def upload_media(self, filepath, product_name=""):
        """Upload image via WordPress REST API."""
        url = f"{self.wp_url}/media"
        filename = os.path.basename(filepath)
        with open(filepath, "rb") as f:
            headers = {
                "Content-Disposition": f'attachment; filename="{filename}"',
                "Content-Type": "image/jpeg",
            }
            resp = self.session.post(
                url,
                data=f.read(),
                headers=headers,
                timeout=60,
            )
        resp.raise_for_status()
        return resp.json()


# ============================================================
# PROGRESS TRACKING
# ============================================================
class Progress:
    def __init__(self, filepath):
        self.filepath = filepath
        self.data = self._load()

    def _load(self):
        if os.path.exists(self.filepath):
            with open(self.filepath, "r") as f:
                return json.load(f)
        return {
            "categories_created": False,
            "attributes_created": False,
            "category_ids": {},
            "attribute_ids": {},
            "products_imported": {},
            "images_uploaded": {},
            "store_configured": False,
        }

    def save(self):
        with open(self.filepath, "w") as f:
            json.dump(self.data, f, indent=2, ensure_ascii=False)

    def is_product_done(self, sku):
        return sku in self.data["products_imported"]

    def mark_product(self, sku, product_id):
        self.data["products_imported"][sku] = product_id
        self.save()

    def is_image_done(self, sku):
        return sku in self.data["images_uploaded"]

    def mark_image(self, sku, media_id):
        self.data["images_uploaded"][sku] = media_id
        self.save()


# ============================================================
# COMMANDS
# ============================================================
def cmd_setup(api, args):
    """Test API connection and show store info."""
    print("Testing WooCommerce API connection...")
    try:
        info = api.get("system_status")
        env = info.get("environment", {})
        print(f"  Store URL: {STORE_URL}")
        print(f"  WP Version: {env.get('wp_version', '?')}")
        print(f"  WC Version: {env.get('version', '?')}")
        print(f"  PHP Version: {env.get('php_version', '?')}")
        print(f"  Theme: {info.get('theme', {}).get('name', '?')}")

        # Count existing products
        products = api.get("reports/products/totals")
        if products:
            total = sum(p.get("total", 0) for p in products)
            print(f"  Products: {total}")

        # Count categories
        cats = api.get_all("products/categories")
        print(f"  Categories: {len(cats)}")

        print("\n  API connection successful!")
        return True
    except requests.exceptions.ConnectionError:
        print(f"  ERROR: Cannot connect to {STORE_URL}")
        print("  Check that the store is accessible and the URL is correct.")
        return False
    except requests.exceptions.HTTPError as e:
        print(f"  ERROR: API returned {e.response.status_code}")
        if e.response.status_code == 401:
            print("  Check consumer key and secret.")
        return False
    except Exception as e:
        print(f"  ERROR: {e}")
        return False


def cmd_categories(api, args, progress):
    """Create category hierarchy."""
    dry_run = args.dry_run
    print(f"{'[DRY RUN] ' if dry_run else ''}Creating category hierarchy...")

    # Get existing categories
    try:
        existing = api.get_all("products/categories")
    except requests.exceptions.RequestException as e:
        print(f"  ERROR: Failed to fetch existing categories: {e}")
        return
    existing_by_slug = {c["slug"]: c for c in existing}
    print(f"  Existing categories: {len(existing)}")

    created = 0
    skipped = 0
    errors = 0

    for parent_name, parent_data in CATEGORY_HIERARCHY.items():
        parent_slug = parent_data["slug"]
        parent_ar = parent_data["name_ar"]

        # Create parent category
        if parent_slug in existing_by_slug:
            parent_id = existing_by_slug[parent_slug]["id"]
            print(f"  [SKIP] Parent: {parent_name} ({parent_ar}) - already exists (ID: {parent_id})")
            skipped += 1
        else:
            if dry_run:
                print(f"  [CREATE] Parent: {parent_name} ({parent_ar}) slug={parent_slug}")
                parent_id = None
            else:
                result = api.post("products/categories", {
                    "name": parent_ar,
                    "slug": parent_slug,
                    "description": parent_name,
                })
                parent_id = result["id"]
                existing_by_slug[parent_slug] = result
                print(f"  [CREATED] Parent: {parent_ar} (ID: {parent_id})")
                created += 1
                time.sleep(0.3)

        # Create child categories
        for child_csv_name, child_data in parent_data["children"].items():
            child_slug = child_data["slug"]
            child_ar = child_data["name_ar"]

            if child_slug in existing_by_slug:
                child_id = existing_by_slug[child_slug]["id"]
                progress.data["category_ids"][child_csv_name] = child_id
                skipped += 1
            else:
                if dry_run:
                    print(f"    [CREATE] Child: {child_csv_name} -> {child_ar} slug={child_slug}")
                else:
                    result = api.post("products/categories", {
                        "name": child_ar,
                        "slug": child_slug,
                        "description": child_csv_name,
                        "parent": parent_id,
                    })
                    progress.data["category_ids"][child_csv_name] = result["id"]
                    existing_by_slug[child_slug] = result
                    print(f"    [CREATED] {child_ar} (ID: {result['id']})")
                    created += 1
                    time.sleep(0.3)

    if not dry_run:
        progress.data["categories_created"] = True
        progress.save()

    print(f"\n  Created: {created}, Skipped: {skipped}")


def cmd_attributes(api, args, progress):
    """Create product attributes (Brand, Connectivity, Compatibility)."""
    dry_run = args.dry_run
    print(f"{'[DRY RUN] ' if dry_run else ''}Creating product attributes...")

    # Get existing attributes
    existing = api.get_all("products/attributes")
    existing_by_slug = {a["slug"]: a for a in existing}

    attributes_to_create = [
        {
            "name": "Brand",
            "slug": "pa_brand",
            "type": "select",
            "order_by": "name",
            "has_archives": True,
        },
        {
            "name": "Connectivity",
            "slug": "pa_connectivity",
            "type": "select",
            "order_by": "name",
            "has_archives": True,
        },
        {
            "name": "Compatibility",
            "slug": "pa_compatibility",
            "type": "select",
            "order_by": "name",
            "has_archives": True,
        },
    ]

    for attr in attributes_to_create:
        slug = attr["slug"]
        if slug in existing_by_slug:
            attr_id = existing_by_slug[slug]["id"]
            progress.data["attribute_ids"][slug] = attr_id
            print(f"  [SKIP] {attr['name']} already exists (ID: {attr_id})")
        else:
            if dry_run:
                print(f"  [CREATE] Attribute: {attr['name']} ({slug})")
            else:
                result = api.post("products/attributes", attr)
                progress.data["attribute_ids"][slug] = result["id"]
                print(f"  [CREATED] {attr['name']} (ID: {result['id']})")
                time.sleep(0.3)

    if not dry_run:
        progress.data["attributes_created"] = True
        progress.save()

    print("  Attributes done.")


def _read_csv():
    """Read the product CSV and return list of dicts."""
    with open(CSV_FILE, "r", encoding="utf-8") as f:
        reader = csv.DictReader(f)
        return list(reader)


def _map_category(csv_category):
    """Map a flat CSV category to the hierarchical category IDs."""
    if csv_category in CATEGORY_MAP:
        return CATEGORY_MAP[csv_category]
    return None


def _infer_connectivity(tags, name):
    """Infer connectivity type from tags and product name."""
    text = f"{tags} {name}".lower()
    conn = []
    if "zigbee" in text:
        conn.append("Zigbee")
    if "z-wave" in text or "zwave" in text:
        conn.append("Z-Wave")
    if "wifi" in text or "wi-fi" in text:
        conn.append("WiFi")
    if "bluetooth" in text or "ble" in text:
        conn.append("Bluetooth")
    if "thread" in text:
        conn.append("Thread")
    if "matter" in text:
        conn.append("Matter")
    if "ethernet" in text or "poe" in text:
        conn.append("Ethernet")
    return conn


def _infer_compatibility(tags, name):
    """Infer platform compatibility from tags and product name."""
    text = f"{tags} {name}".lower()
    compat = []
    if "home-assistant" in text or "home assistant" in text or "ha-hardware" in text:
        compat.append("Home Assistant")
    if "homekit" in text or "apple" in text:
        compat.append("Apple HomeKit")
    if "alexa" in text or "amazon" in text or "echo" in text:
        compat.append("Amazon Alexa")
    if "google" in text or "nest" in text:
        compat.append("Google Home")
    return compat


def cmd_products(api, args, progress):
    """Import/update products with proper categories and attributes."""
    dry_run = args.dry_run
    limit = args.limit
    start = args.start
    print(f"{'[DRY RUN] ' if dry_run else ''}Importing products...")

    rows = _read_csv()
    print(f"  CSV products: {len(rows)}")

    # Refresh category IDs if not in progress
    if not progress.data["category_ids"]:
        cats = api.get_all("products/categories")
        for c in cats:
            # Match by slug -> find the CSV name from our mapping
            for csv_name, mapping in CATEGORY_MAP.items():
                if mapping["child_slug"] == c["slug"]:
                    progress.data["category_ids"][csv_name] = c["id"]
                    break
        progress.save()

    # Get existing products by SKU
    print("  Fetching existing products...")
    existing_products = api.get_all("products")
    existing_by_sku = {p["sku"]: p["id"] for p in existing_products if p.get("sku")}
    print(f"  Existing products: {len(existing_by_sku)}")

    # Slice if needed
    products_to_process = rows[start:]
    if limit:
        products_to_process = products_to_process[:limit]

    imported = 0
    updated = 0
    skipped = 0
    errors = 0
    batch = []

    for i, row in enumerate(products_to_process):
        sku = row.get("SKU", "").strip()
        if not sku:
            skipped += 1
            continue

        if progress.is_product_done(sku) and not args.force:
            skipped += 1
            continue

        # Build product data
        csv_cat = row.get("Categories", "").strip()
        cat_mapping = _map_category(csv_cat)

        categories = []
        if cat_mapping:
            cat_id = progress.data["category_ids"].get(csv_cat)
            if cat_id:
                categories.append({"id": cat_id})
            else:
                categories.append({"name": csv_cat})
        else:
            categories.append({"name": csv_cat or "Uncategorized"})

        tags = row.get("Tags", "")
        name = row.get("Name", "")
        brand = row.get("Brands", "").strip()

        # Build attributes
        attributes = []
        if brand and brand != "nan":
            attributes.append({
                "name": "Brand",
                "options": [brand],
                "visible": True,
            })

        connectivity = _infer_connectivity(tags, name)
        if connectivity:
            attributes.append({
                "name": "Connectivity",
                "options": connectivity,
                "visible": True,
            })

        compatibility = _infer_compatibility(tags, name)
        if compatibility:
            attributes.append({
                "name": "Compatibility",
                "options": compatibility,
                "visible": True,
            })

        product_data = {
            "name": name,
            "type": "simple",
            "regular_price": str(row.get("Regular price", "")),
            "sale_price": str(row.get("Sale price", "")) if row.get("Sale price") else "",
            "description": row.get("Description", ""),
            "short_description": row.get("Short description", ""),
            "sku": sku,
            "manage_stock": True,
            "stock_quantity": _safe_int(row.get("Stock"), 10),
            "categories": categories,
            "tags": [{"name": t.strip()} for t in tags.split(",") if t.strip()],
            "attributes": attributes,
            "status": "publish" if row.get("Published") == "1" else "draft",
            "featured": row.get("Is featured?") == "1",
            "catalog_visibility": row.get("Visibility in catalog", "visible"),
        }

        # Add image URL if available and not placeholder
        img_url = row.get("Images", "").strip()
        if img_url and "placeholder" not in img_url:
            product_data["images"] = [{"src": img_url}]

        # Add cost/margin as meta
        cost = row.get("Meta: _cost", "")
        margin = row.get("Meta: _margin", "")
        meta_data = []
        if cost:
            meta_data.append({"key": "_cost", "value": str(cost)})
        if margin:
            meta_data.append({"key": "_margin", "value": str(margin)})
        if meta_data:
            product_data["meta_data"] = meta_data

        if dry_run:
            cat_label = cat_mapping["parent_name"] if cat_mapping else csv_cat
            print(f"  [{i+1}] {sku} -> {name} | {cat_label} > {csv_cat}")
            imported += 1
            continue

        # Check if update or create
        if sku in existing_by_sku:
            batch.append({"id": existing_by_sku[sku], **product_data})
        else:
            batch.append(product_data)

        # Send batch when full
        if len(batch) >= BATCH_SIZE:
            try:
                creates = [p for p in batch if "id" not in p]
                updates = [p for p in batch if "id" in p]
                payload = {}
                if creates:
                    payload["create"] = creates
                if updates:
                    payload["update"] = updates

                result = api.post("products/batch", payload)

                for p in result.get("create", []):
                    if p.get("id"):
                        progress.mark_product(p["sku"], p["id"])
                        existing_by_sku[p["sku"]] = p["id"]
                        imported += 1
                    else:
                        errors += 1

                for p in result.get("update", []):
                    if p.get("id"):
                        progress.mark_product(p["sku"], p["id"])
                        updated += 1
                    else:
                        errors += 1

                total_done = imported + updated + skipped
                print(f"  Progress: {total_done}/{len(products_to_process)} "
                      f"(imported={imported}, updated={updated}, skipped={skipped}, errors={errors})")

            except Exception as e:
                print(f"  Batch error: {e}")
                errors += len(batch)

            batch = []
            time.sleep(API_DELAY)

    # Process remaining batch
    if batch and not dry_run:
        try:
            creates = [p for p in batch if "id" not in p]
            updates = [p for p in batch if "id" in p]
            payload = {}
            if creates:
                payload["create"] = creates
            if updates:
                payload["update"] = updates

            result = api.post("products/batch", payload)

            for p in result.get("create", []):
                if p.get("id"):
                    progress.mark_product(p["sku"], p["id"])
                    imported += 1
                else:
                    errors += 1

            for p in result.get("update", []):
                if p.get("id"):
                    progress.mark_product(p["sku"], p["id"])
                    updated += 1
                else:
                    errors += 1
        except Exception as e:
            print(f"  Final batch error: {e}")
            errors += len(batch)

    print(f"\n  === PRODUCTS DONE ===")
    print(f"  Imported: {imported}")
    print(f"  Updated: {updated}")
    print(f"  Skipped: {skipped}")
    print(f"  Errors: {errors}")


def cmd_images(api, args, progress):
    """Sideload product images via WooCommerce API.

    Scans local images/ directory for SKU-named JPGs, serves them via a
    public URL (--base-url), and tells WooCommerce to sideload each one.
    Uses batch API for efficiency (BATCH_SIZE products per request).
    """
    dry_run = args.dry_run
    limit = args.limit
    start = args.start
    base_url = getattr(args, "base_url", "").rstrip("/")
    print(f"{'[DRY RUN] ' if dry_run else ''}Sideloading product images via WooCommerce API...")

    if not base_url:
        print("  ERROR: --base-url is required (public URL serving images/ directory)")
        print("  Example: python3 woo_builder.py images --base-url https://xyz.trycloudflare.com")
        return

    # Scan local images directory
    local_images = {}
    if os.path.isdir(IMAGES_DIR):
        for fname in os.listdir(IMAGES_DIR):
            if fname.lower().endswith((".jpg", ".jpeg", ".png", ".webp")):
                sku = os.path.splitext(fname)[0]
                local_images[sku] = fname
    print(f"  Local images found: {len(local_images)}")

    if not local_images:
        print("  ERROR: No images found in images/ directory")
        return

    # Verify tunnel is working with a sample image
    sample_fname = next(iter(local_images.values()))
    sample_url = f"{base_url}/{sample_fname}"
    print(f"  Testing tunnel: {sample_url[:80]}...")
    try:
        resp = requests.head(sample_url, timeout=10, allow_redirects=True)
        if resp.status_code != 200:
            print(f"  ERROR: Tunnel returned HTTP {resp.status_code} - is the server running?")
            return
        print(f"  Tunnel OK (HTTP {resp.status_code})")
    except Exception as e:
        print(f"  ERROR: Cannot reach tunnel: {e}")
        return

    # Build SKU -> product ID map
    sku_to_product = dict(progress.data.get("products_imported", {}))
    if not sku_to_product:
        print("  Fetching product IDs from store...")
        products = api.get_all("products")
        for p in products:
            if p.get("sku"):
                sku_to_product[p["sku"]] = p["id"]
        print(f"  Found {len(sku_to_product)} products")

    # Check which products already have images
    print("  Checking existing product images...")
    products_with_images = set()
    all_products = api.get_all("products")
    for p in all_products:
        if p.get("images") and len(p["images"]) > 0:
            first_img = p["images"][0].get("src", "")
            if first_img and "placeholder" not in first_img and "woocommerce-placeholder" not in first_img:
                products_with_images.add(p.get("sku", ""))
    print(f"  Products already with images: {len(products_with_images)}")

    # Build work list: local images matched to products
    work = []
    no_product = 0
    for sku, fname in sorted(local_images.items()):
        if sku in products_with_images and not args.force:
            continue
        product_id = sku_to_product.get(sku)
        if not product_id:
            no_product += 1
            continue
        if progress.is_image_done(sku) and not args.force:
            continue
        url = f"{base_url}/{fname}"
        work.append({"sku": sku, "product_id": product_id, "url": url})

    # Apply offset/limit
    work = work[start:]
    if limit:
        work = work[:limit]

    print(f"  Products to update with images: {len(work)}")
    if no_product:
        print(f"  Images with no matching product: {no_product}")

    if not work:
        print("  Nothing to do.")
        return

    sideloaded = 0
    errors = 0

    # Process in batches via WC batch API
    for batch_start in range(0, len(work), BATCH_SIZE):
        batch = work[batch_start:batch_start + BATCH_SIZE]

        if dry_run:
            for item in batch:
                print(f"  [SIDELOAD] {item['sku']} -> {item['url'][:80]}...")
                sideloaded += 1
            continue

        updates = []
        for item in batch:
            updates.append({
                "id": int(item["product_id"]),
                "images": [{"src": item["url"], "name": item["sku"]}],
            })

        try:
            result = api.post("products/batch", {"update": updates})

            for p in result.get("update", []):
                sku = p.get("sku", "")
                if p.get("id") and p.get("images"):
                    progress.mark_image(sku, p["images"][0].get("id", 0))
                    sideloaded += 1
                else:
                    err_msg = p.get("error", {}).get("message", "unknown")
                    print(f"  Error for {sku}: {err_msg}")
                    errors += 1

            total = sideloaded + errors
            print(f"  Progress: {total}/{len(work)} "
                  f"(sideloaded={sideloaded}, errors={errors})")

        except Exception as e:
            print(f"  Batch error: {e}")
            errors += len(batch)

        time.sleep(API_DELAY * 2)  # extra delay for sideloading

    print(f"\n  === IMAGES DONE ===")
    print(f"  Sideloaded: {sideloaded}")
    print(f"  Already had images: {len(products_with_images)}")
    print(f"  Errors: {errors}")


def cmd_configure(api, args, progress):
    """Configure store settings."""
    dry_run = args.dry_run
    print(f"{'[DRY RUN] ' if dry_run else ''}Configuring store settings...")

    settings = [
        ("general/woocommerce_currency", "SAR"),
        ("general/woocommerce_currency_pos", "right_space"),
        ("products/woocommerce_weight_unit", "kg"),
        ("products/woocommerce_dimension_unit", "cm"),
        ("general/woocommerce_default_country", "SA"),
        ("general/woocommerce_store_city", "Riyadh"),
    ]

    for setting_path, value in settings:
        group, setting_id = setting_path.split("/", 1)
        if dry_run:
            print(f"  [SET] {setting_id} = {value}")
        else:
            try:
                api.put(f"settings/{group}/{setting_id}", {"value": value})
                print(f"  [SET] {setting_id} = {value}")
            except Exception as e:
                print(f"  Error setting {setting_id}: {e}")

    # Tax settings - 15% VAT
    if not dry_run:
        print("\n  Setting up tax (15% VAT)...")
        try:
            # Enable taxes
            api.put("settings/general/woocommerce_calc_taxes", {"value": "yes"})

            # Create tax class
            existing_taxes = api.get_all("taxes")
            if not any(t.get("rate") == "15.0000" and t.get("country") == "SA" for t in existing_taxes):
                api.post("taxes", {
                    "country": "SA",
                    "rate": "15.0000",
                    "name": "VAT",
                    "shipping": True,
                    "order": 1,
                })
                print("  [CREATED] SA VAT 15%")
            else:
                print("  [SKIP] SA VAT 15% already exists")
        except Exception as e:
            print(f"  Tax error: {e}")

    # Shipping zones
    if not dry_run:
        print("\n  Setting up shipping zones...")
        try:
            zones = api.get_all("shipping/zones")
            sa_zone = None
            for z in zones:
                if "saudi" in z.get("name", "").lower() or "sa" in z.get("name", "").lower():
                    sa_zone = z
                    break

            if not sa_zone:
                sa_zone = api.post("shipping/zones", {
                    "name": "Saudi Arabia",
                })
                print(f"  [CREATED] Shipping zone: Saudi Arabia (ID: {sa_zone['id']})")

                # Add SA location
                zone_id = sa_zone["id"]
                api.post(f"shipping/zones/{zone_id}/locations", [
                    {"code": "SA", "type": "country"}
                ])

                # Add flat rate
                api.post(f"shipping/zones/{zone_id}/methods", {
                    "method_id": "flat_rate",
                })

                # Add free shipping
                api.post(f"shipping/zones/{zone_id}/methods", {
                    "method_id": "free_shipping",
                })
                print("  [CREATED] Shipping methods: flat_rate + free_shipping")
            else:
                print(f"  [SKIP] Saudi Arabia zone already exists (ID: {sa_zone['id']})")
        except Exception as e:
            print(f"  Shipping error: {e}")

    if not dry_run:
        progress.data["store_configured"] = True
        progress.save()

    print("\n  Store configuration done.")


def cmd_all(api, args, progress):
    """Run all commands in order."""
    print("=" * 60)
    print("NEOGEN WOOCOMMERCE FULL STORE SETUP")
    print("=" * 60)

    print("\n[1/5] CATEGORIES")
    cmd_categories(api, args, progress)

    print("\n[2/5] ATTRIBUTES")
    cmd_attributes(api, args, progress)

    print("\n[3/5] PRODUCTS")
    cmd_products(api, args, progress)

    print("\n[4/5] IMAGES")
    cmd_images(api, args, progress)

    print("\n[5/5] CONFIGURE")
    cmd_configure(api, args, progress)

    print("\n" + "=" * 60)
    print("ALL DONE!")
    print("=" * 60)


# ============================================================
# CLI
# ============================================================
def main():
    parser = argparse.ArgumentParser(
        description="WooCommerce Store Builder for Neogen",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog=__doc__
    )
    sub = parser.add_subparsers(dest="command", required=True)

    # setup
    sub.add_parser("setup", help="Test API connection")

    # categories
    p = sub.add_parser("categories", help="Create category hierarchy")
    p.add_argument("--dry-run", action="store_true")

    # attributes
    p = sub.add_parser("attributes", help="Create product attributes")
    p.add_argument("--dry-run", action="store_true")

    # products
    p = sub.add_parser("products", help="Import/update products")
    p.add_argument("--dry-run", action="store_true")
    p.add_argument("--limit", type=int, default=0, help="Limit number of products")
    p.add_argument("--start", type=int, default=0, help="Start from product N")
    p.add_argument("--force", action="store_true", help="Re-import already done products")

    # images
    p = sub.add_parser("images", help="Upload local product images")
    p.add_argument("--dry-run", action="store_true")
    p.add_argument("--limit", type=int, default=0, help="Limit number of images")
    p.add_argument("--start", type=int, default=0, help="Start from image N")
    p.add_argument("--base-url", type=str, default="", help="Public URL serving images/ dir")
    p.add_argument("--force", action="store_true", help="Re-upload already done images")

    # configure
    p = sub.add_parser("configure", help="Configure store settings")
    p.add_argument("--dry-run", action="store_true")

    # all
    p = sub.add_parser("all", help="Run everything in order")
    p.add_argument("--dry-run", action="store_true")
    p.add_argument("--limit", type=int, default=0, help="Limit products/images")
    p.add_argument("--start", type=int, default=0, help="Start offset")
    p.add_argument("--force", action="store_true")
    p.add_argument("--base-url", type=str, default="", help="Public URL serving images/")

    args = parser.parse_args()

    # Add defaults for missing args
    if not hasattr(args, "dry_run"):
        args.dry_run = False
    if not hasattr(args, "limit"):
        args.limit = 0
    if not hasattr(args, "start"):
        args.start = 0
    if not hasattr(args, "force"):
        args.force = False

    api = WooAPI(STORE_URL, CONSUMER_KEY, CONSUMER_SECRET)
    progress = Progress(PROGRESS_FILE)

    commands = {
        "setup": lambda: cmd_setup(api, args),
        "categories": lambda: cmd_categories(api, args, progress),
        "attributes": lambda: cmd_attributes(api, args, progress),
        "products": lambda: cmd_products(api, args, progress),
        "images": lambda: cmd_images(api, args, progress),
        "configure": lambda: cmd_configure(api, args, progress),
        "all": lambda: cmd_all(api, args, progress),
    }

    commands[args.command]()


if __name__ == "__main__":
    main()
