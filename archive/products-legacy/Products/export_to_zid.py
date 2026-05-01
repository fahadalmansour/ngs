#!/usr/bin/env python3.11
"""
Export WooCommerce products to Zid.sa CSV format with images.

Zid.sa CSV format based on their API product fields:
- name (required)
- sku (required, unique)
- price (required)
- sale_price (optional)
- quantity (optional, for non-infinite stock)
- is_infinite (optional, unlimited stock flag)
- is_taxable (optional)
- requires_shipping (optional)
- description (optional)
- short_description (optional)
- images (URLs, comma-separated)
- categories (names, comma-separated)
"""
import csv
import os
import sys
from dotenv import load_dotenv
import requests
from requests.auth import HTTPBasicAuth

# Load environment
load_dotenv('/Volumes/Fahadmega/NGS_Business/Products/.env')

STORE_URL = os.getenv('STORE_URL', 'https://neogen.store')
WC_KEY = os.getenv('WC_CONSUMER_KEY')
WC_SECRET = os.getenv('WC_CONSUMER_SECRET')

OUTPUT_DIR = '/Volumes/Fahadmega/NGS_Business/Products'
OUTPUT_CSV = os.path.join(OUTPUT_DIR, 'zid_import.csv')


def fetch_all_products():
    """Fetch all products from WooCommerce."""
    products = []
    page = 1
    per_page = 100

    print("Fetching products from WooCommerce...")
    while True:
        url = f"{STORE_URL}/wp-json/wc/v3/products"
        params = {
            'page': page,
            'per_page': per_page,
            'status': 'publish',
        }

        resp = requests.get(
            url,
            params=params,
            auth=HTTPBasicAuth(WC_KEY, WC_SECRET),
            timeout=60
        )

        if resp.status_code != 200:
            print(f"Error fetching page {page}: {resp.status_code}")
            break

        batch = resp.json()
        if not batch:
            break

        products.extend(batch)
        print(f"  Page {page}: {len(batch)} products (total: {len(products)})")

        if len(batch) < per_page:
            break

        page += 1

    return products


def get_category_names(categories):
    """Extract category names from WooCommerce category objects."""
    if not categories:
        return ''
    return ', '.join([cat.get('name', '') for cat in categories if cat.get('name')])


def get_image_urls(images):
    """Extract image URLs from WooCommerce image objects."""
    if not images:
        return ''
    return ', '.join([img.get('src', '') for img in images if img.get('src')])


def clean_html(text):
    """Remove HTML tags from text."""
    if not text:
        return ''
    import re
    # Remove HTML tags
    clean = re.sub(r'<[^>]+>', '', text)
    # Normalize whitespace
    clean = re.sub(r'\s+', ' ', clean).strip()
    return clean


def export_to_zid_csv(products):
    """Export products to Zid.sa compatible CSV format."""

    # Zid.sa CSV columns based on their API
    fieldnames = [
        'name',              # Product name (required)
        'name_ar',           # Arabic name
        'sku',               # SKU (required, unique)
        'price',             # Base price (required)
        'sale_price',        # Discounted price
        'quantity',          # Stock quantity
        'is_infinite',       # Unlimited stock (TRUE/FALSE)
        'is_taxable',        # Subject to tax (TRUE/FALSE)
        'requires_shipping', # Requires shipping (TRUE/FALSE)
        'is_draft',          # Draft/unpublished (TRUE/FALSE)
        'description',       # Full description
        'short_description', # Short description
        'categories',        # Category names (comma-separated)
        'images',            # Image URLs (comma-separated)
        'weight',            # Weight in kg
        'barcode',           # Barcode/EAN
    ]

    print(f"\nExporting {len(products)} products to {OUTPUT_CSV}...")

    with open(OUTPUT_CSV, 'w', newline='', encoding='utf-8') as f:
        writer = csv.DictWriter(f, fieldnames=fieldnames, quoting=csv.QUOTE_ALL)
        writer.writeheader()

        exported = 0
        skipped = 0

        for p in products:
            # Skip products without SKU (Zid requires unique SKU)
            sku = p.get('sku', '').strip()
            if not sku:
                # Generate SKU from product ID if missing
                sku = f"NGS-{p.get('id', 0)}"

            # Get name (prefer Arabic if available, otherwise use English)
            name = p.get('name', '')
            name_ar = name  # WooCommerce stores name as-is (already Arabic for this store)

            # Price handling
            price = p.get('regular_price', '') or p.get('price', '')
            sale_price = p.get('sale_price', '')

            # Skip if no price
            if not price:
                skipped += 1
                continue

            # Stock handling
            manage_stock = p.get('manage_stock', False)
            stock_quantity = p.get('stock_quantity')
            is_infinite = 'TRUE' if not manage_stock or stock_quantity is None else 'FALSE'
            quantity = stock_quantity if manage_stock and stock_quantity is not None else ''

            # Images
            images = get_image_urls(p.get('images', []))

            # Categories
            categories = get_category_names(p.get('categories', []))

            # Descriptions (clean HTML)
            description = clean_html(p.get('description', ''))
            short_description = clean_html(p.get('short_description', ''))

            # Weight
            weight = p.get('weight', '')

            row = {
                'name': name,
                'name_ar': name_ar,
                'sku': sku,
                'price': price,
                'sale_price': sale_price if sale_price else '',
                'quantity': quantity,
                'is_infinite': is_infinite,
                'is_taxable': 'TRUE',  # All products taxable in Saudi
                'requires_shipping': 'TRUE',  # All products require shipping
                'is_draft': 'FALSE',  # Publish immediately
                'description': description[:5000] if description else '',  # Limit length
                'short_description': short_description[:1000] if short_description else '',
                'categories': categories,
                'images': images,
                'weight': weight,
                'barcode': '',  # No barcode in WooCommerce data
            }

            writer.writerow(row)
            exported += 1

        print(f"  Exported: {exported}")
        print(f"  Skipped (no price): {skipped}")

    return exported


def main():
    print("=" * 60)
    print("WooCommerce to Zid.sa CSV Exporter")
    print("=" * 60)

    # Fetch products
    products = fetch_all_products()
    print(f"\nTotal products fetched: {len(products)}")

    if not products:
        print("No products to export!")
        return

    # Export to CSV
    exported = export_to_zid_csv(products)

    print(f"\n{'=' * 60}")
    print(f"CSV file saved: {OUTPUT_CSV}")
    print(f"Products exported: {exported}")
    print(f"\nNext steps:")
    print("1. Log in to your Zid.sa merchant dashboard")
    print("2. Go to Products > Import Products")
    print("3. Upload the CSV file: zid_import.csv")
    print("4. Map the columns if prompted")
    print("5. Review and confirm the import")
    print("=" * 60)


if __name__ == '__main__':
    main()
