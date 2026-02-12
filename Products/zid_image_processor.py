#!/usr/bin/env python3
"""
Zid Image Processor for Neogen Store
=====================================
Downloads product images from WooCommerce, processes them to Zid specs,
uploads to VPS, and updates the CSV with direct URLs.

Zid Image Requirements:
- Dimensions: 2000 × 2000 px (square)
- Aspect ratio: 1:1
- Format: JPEG
- Quality: 80-85% compression
- File size: Under 300 KB
- Color space: sRGB
- Background: White (#FFFFFF) preferred
"""

import os
import sys
import csv
import json
import requests
from io import BytesIO
from pathlib import Path
from urllib.parse import urlparse
import subprocess
from concurrent.futures import ThreadPoolExecutor, as_completed
from dotenv import load_dotenv

# Load environment variables
load_dotenv()

# Try to import PIL
try:
    from PIL import Image, ImageOps
    PIL_AVAILABLE = True
except ImportError:
    PIL_AVAILABLE = False
    print("WARNING: Pillow not installed. Run: pip install Pillow")

# Configuration
STORE_URL = os.getenv('STORE_URL', 'https://neogen.store')
WC_KEY = os.getenv('WC_CONSUMER_KEY')
WC_SECRET = os.getenv('WC_CONSUMER_SECRET')

# VPS Configuration (Namecheap cPanel)
VPS_HOST = "162.254.39.146"
VPS_PORT = 21098
VPS_USER = "fsalmansour"
VPS_PATH = "/home/fsalmansour/public_html/product-images"
VPS_URL_BASE = "https://fahadalmansour.site/product-images"

# Local paths
SCRIPT_DIR = Path(__file__).parent
OUTPUT_DIR = SCRIPT_DIR / "zid_images"
PROCESSED_DIR = OUTPUT_DIR / "processed"
CSV_INPUT = SCRIPT_DIR / "woocommerce-final.csv"
CSV_OUTPUT = Path("/Users/fahadalmansour/Downloads/neogen_zid_import.csv")

# Image settings
TARGET_SIZE = (2000, 2000)
JPEG_QUALITY = 82  # 80-85% range
MAX_FILE_SIZE = 300 * 1024  # 300 KB
BACKGROUND_COLOR = (255, 255, 255)  # White


def ensure_dirs():
    """Create necessary directories."""
    OUTPUT_DIR.mkdir(exist_ok=True)
    PROCESSED_DIR.mkdir(exist_ok=True)


def fetch_woo_products():
    """Load products from local CSV file."""
    print("Loading products from CSV...")

    products = []

    # Read from existing WooCommerce export CSV
    import csv
    csv_path = SCRIPT_DIR / "woocommerce-final.csv"

    if not csv_path.exists():
        print(f"ERROR: CSV not found: {csv_path}")
        return []

    with open(csv_path, 'r', encoding='utf-8') as f:
        reader = csv.DictReader(f)
        for row in reader:
            # Convert CSV row to product dict format
            images = []
            if row.get('Images'):
                images = [{'src': row['Images']}]

            products.append({
                'sku': row.get('SKU', ''),
                'name': row.get('Name', ''),
                'images': images
            })

    print(f"Total products loaded: {len(products)}")
    return products


def download_image(url, timeout=30):
    """Download image from URL and return PIL Image."""
    if not url or 'placeholder' in url.lower():
        return None

    try:
        headers = {
            'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
        }
        response = requests.get(url, headers=headers, timeout=timeout)
        response.raise_for_status()
        return Image.open(BytesIO(response.content))
    except Exception as e:
        print(f"  Error downloading {url}: {e}")
        return None


def process_image(img, sku):
    """
    Process image to Zid specifications:
    - Convert to RGB (sRGB)
    - Resize to 2000x2000 with white background
    - Optimize JPEG quality to stay under 300KB
    """
    if img is None:
        return None

    try:
        # Convert to RGB if necessary (handles RGBA, P mode, etc.)
        if img.mode in ('RGBA', 'LA', 'P'):
            # Create white background
            background = Image.new('RGB', img.size, BACKGROUND_COLOR)
            if img.mode == 'P':
                img = img.convert('RGBA')
            background.paste(img, mask=img.split()[-1] if img.mode == 'RGBA' else None)
            img = background
        elif img.mode != 'RGB':
            img = img.convert('RGB')

        # Calculate resize dimensions maintaining aspect ratio
        orig_width, orig_height = img.size
        ratio = min(TARGET_SIZE[0] / orig_width, TARGET_SIZE[1] / orig_height)
        new_size = (int(orig_width * ratio), int(orig_height * ratio))

        # Resize with high-quality resampling
        img = img.resize(new_size, Image.Resampling.LANCZOS)

        # Create square canvas with white background
        canvas = Image.new('RGB', TARGET_SIZE, BACKGROUND_COLOR)

        # Center the image on canvas
        offset = ((TARGET_SIZE[0] - new_size[0]) // 2,
                  (TARGET_SIZE[1] - new_size[1]) // 2)
        canvas.paste(img, offset)

        return canvas

    except Exception as e:
        print(f"  Error processing image for {sku}: {e}")
        return None


def save_optimized_jpeg(img, output_path, target_size=MAX_FILE_SIZE):
    """Save image as JPEG, optimizing quality to meet file size target."""
    if img is None:
        return False

    quality = JPEG_QUALITY

    while quality >= 60:
        buffer = BytesIO()
        img.save(buffer, format='JPEG', quality=quality, optimize=True)
        size = buffer.tell()

        if size <= target_size:
            # Save to file
            with open(output_path, 'wb') as f:
                f.write(buffer.getvalue())
            return True

        quality -= 5

    # If still too large, save at minimum quality
    img.save(output_path, format='JPEG', quality=60, optimize=True)
    return True


def upload_to_vps(local_path, remote_filename):
    """Upload file to VPS via SCP."""
    remote_path = f"{VPS_PATH}/{remote_filename}"

    try:
        # Use scp with custom port and SSH key
        cmd = [
            "scp", "-P", str(VPS_PORT),
            "-i", os.path.expanduser("~/.ssh/id_rsa"),
            "-o", "StrictHostKeyChecking=no",
            str(local_path),
            f"{VPS_USER}@{VPS_HOST}:{remote_path}"
        ]

        result = subprocess.run(cmd, capture_output=True, text=True, timeout=60)

        if result.returncode == 0:
            return f"{VPS_URL_BASE}/{remote_filename}"
        else:
            print(f"  SCP error: {result.stderr}")
            return None

    except Exception as e:
        print(f"  Upload error: {e}")
        return None


def process_product(product, existing_urls=None):
    """Process a single product's images."""
    sku = product.get('sku', '')
    name = product.get('name', '')
    images = product.get('images', [])

    if not sku:
        return None

    # Check if already processed
    if existing_urls and sku in existing_urls:
        return {'sku': sku, 'url': existing_urls[sku]}

    if not images:
        return {'sku': sku, 'url': ''}

    # Get primary image
    primary_image = images[0].get('src', '')

    if not primary_image or 'placeholder' in primary_image.lower():
        return {'sku': sku, 'url': ''}

    print(f"Processing: {sku} - {name[:40]}")

    # Download
    img = download_image(primary_image)
    if img is None:
        return {'sku': sku, 'url': ''}

    # Process
    processed = process_image(img, sku)
    if processed is None:
        return {'sku': sku, 'url': ''}

    # Save locally
    filename = f"{sku.replace('/', '-')}.jpg"
    local_path = PROCESSED_DIR / filename

    if not save_optimized_jpeg(processed, local_path):
        return {'sku': sku, 'url': ''}

    file_size = local_path.stat().st_size
    print(f"  Saved: {filename} ({file_size / 1024:.1f} KB)")

    # Upload to VPS
    public_url = upload_to_vps(local_path, filename)

    if public_url:
        print(f"  Uploaded: {public_url}")
        return {'sku': sku, 'url': public_url}
    else:
        # Return local path if upload fails
        return {'sku': sku, 'url': '', 'local': str(local_path)}


def load_progress():
    """Load previously processed URLs."""
    progress_file = OUTPUT_DIR / "progress.json"
    if progress_file.exists():
        with open(progress_file) as f:
            return json.load(f)
    return {}


def save_progress(data):
    """Save progress to file."""
    progress_file = OUTPUT_DIR / "progress.json"
    with open(progress_file, 'w') as f:
        json.dump(data, f, indent=2)


def update_zid_csv(image_urls):
    """Update the Zid CSV with new image URLs."""
    print(f"\nUpdating CSV: {CSV_OUTPUT}")

    # Read existing CSV
    rows = []
    with open(CSV_OUTPUT, 'r', encoding='utf-8-sig') as f:
        reader = csv.DictReader(f)
        fieldnames = reader.fieldnames
        for row in reader:
            sku = row.get('sku', '')
            if sku in image_urls and image_urls[sku]:
                row['images'] = image_urls[sku]
            rows.append(row)

    # Write updated CSV
    with open(CSV_OUTPUT, 'w', encoding='utf-8-sig', newline='') as f:
        writer = csv.DictWriter(f, fieldnames=fieldnames)
        writer.writeheader()
        writer.writerows(rows)

    # Count updated
    updated = sum(1 for sku, url in image_urls.items() if url)
    print(f"Updated {updated} product image URLs")


def setup_vps_directory():
    """Create the product-images directory on VPS if it doesn't exist."""
    print(f"Setting up VPS directory: {VPS_PATH}")

    try:
        cmd = [
            "ssh", "-p", str(VPS_PORT),
            "-i", os.path.expanduser("~/.ssh/id_rsa"),
            "-o", "StrictHostKeyChecking=no",
            f"{VPS_USER}@{VPS_HOST}",
            f"mkdir -p {VPS_PATH} && chmod 755 {VPS_PATH}"
        ]

        result = subprocess.run(cmd, capture_output=True, text=True, timeout=30)

        if result.returncode == 0:
            print("  VPS directory ready")
            return True
        else:
            print(f"  Error: {result.stderr}")
            return False

    except Exception as e:
        print(f"  SSH error: {e}")
        return False


def main():
    """Main execution flow."""
    if not PIL_AVAILABLE:
        print("ERROR: Pillow is required. Install with: pip install Pillow")
        sys.exit(1)

    print("=" * 60)
    print("Zid Image Processor for Neogen Store")
    print("=" * 60)

    # Setup
    ensure_dirs()

    # Load progress
    progress = load_progress()
    print(f"Previously processed: {len(progress)} products")

    # Setup VPS directory
    if not setup_vps_directory():
        print("WARNING: Could not setup VPS directory. Images will be saved locally only.")

    # Fetch products from WooCommerce
    products = fetch_woo_products()

    if not products:
        print("No products found!")
        return

    # Process each product
    image_urls = dict(progress)  # Start with existing progress

    print(f"\nProcessing {len(products)} products...")
    print("-" * 40)

    for i, product in enumerate(products):
        sku = product.get('sku', '')

        # Skip if already processed
        if sku in image_urls and image_urls[sku]:
            continue

        result = process_product(product, image_urls)

        if result:
            image_urls[result['sku']] = result.get('url', '')

            # Save progress periodically
            if (i + 1) % 10 == 0:
                save_progress(image_urls)
                print(f"Progress saved: {i + 1}/{len(products)}")

    # Final save
    save_progress(image_urls)

    # Update Zid CSV
    if CSV_OUTPUT.exists():
        update_zid_csv(image_urls)

    # Summary
    print("\n" + "=" * 60)
    print("SUMMARY")
    print("=" * 60)

    total = len(products)
    with_urls = sum(1 for url in image_urls.values() if url)
    without_urls = total - with_urls

    print(f"Total products: {total}")
    print(f"With images: {with_urls}")
    print(f"Without images: {without_urls}")
    print(f"\nProcessed images saved to: {PROCESSED_DIR}")
    print(f"Updated CSV: {CSV_OUTPUT}")


if __name__ == "__main__":
    main()
