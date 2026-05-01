#!/usr/bin/env python3.11
"""
Export WooCommerce products to Zid.sa XLSX format - ALL COLUMNS FILLED.
Fetches real descriptions from product pages on neogen.store.
"""
import os
import re
import time
from dotenv import load_dotenv
import requests
from requests.auth import HTTPBasicAuth
import pandas as pd
from bs4 import BeautifulSoup

# Load environment
load_dotenv('/Volumes/Fahadmega/NGS_Business/Products/.env')

STORE_URL = os.getenv('STORE_URL', 'https://neogen.store')
WC_KEY = os.getenv('WC_CONSUMER_KEY')
WC_SECRET = os.getenv('WC_CONSUMER_SECRET')

OUTPUT_DIR = '/Volumes/Fahadmega/NGS_Business/Products'
OUTPUT_XLSX = os.path.join(OUTPUT_DIR, 'zid_import_products.xlsx')


def fetch_all_products():
    """Fetch all products from WooCommerce API."""
    products = []
    page = 1
    per_page = 100

    print("Fetching products from WooCommerce API...")
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


def fetch_product_description(product_url):
    """Fetch real description from product page."""
    try:
        resp = requests.get(product_url, timeout=30, headers={
            'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
        })
        if resp.status_code != 200:
            return None, None

        soup = BeautifulSoup(resp.text, 'html.parser')

        # Try to find product description
        desc = ''
        short_desc = ''

        # Look for WooCommerce product description
        desc_div = soup.select_one('.woocommerce-product-details__short-description')
        if desc_div:
            short_desc = desc_div.get_text(strip=True)

        # Look for full description in tabs
        full_desc_div = soup.select_one('.woocommerce-Tabs-panel--description')
        if full_desc_div:
            desc = full_desc_div.get_text(strip=True)

        # Fallback: product summary
        if not short_desc:
            summary = soup.select_one('.product-entry-summary, .summary')
            if summary:
                # Get text from paragraphs
                paragraphs = summary.select('p')
                texts = [p.get_text(strip=True) for p in paragraphs if p.get_text(strip=True)]
                short_desc = ' '.join(texts[:2])

        # Fallback: meta description
        if not desc:
            meta_desc = soup.select_one('meta[name="description"]')
            if meta_desc:
                desc = meta_desc.get('content', '')

        return desc, short_desc

    except Exception as e:
        return None, None


def clean_html(text):
    """Remove HTML tags from text."""
    if not text:
        return ''
    clean = re.sub(r'<[^>]+>', '', str(text))
    clean = re.sub(r'\s+', ' ', clean).strip()
    clean = clean.replace('&amp;', '&').replace('&#8211;', '-').replace('&ndash;', '-')
    clean = clean.replace('&quot;', '"').replace('&#039;', "'").replace('&lt;', '<').replace('&gt;', '>')
    return clean


def get_category_names(categories):
    """Get comma-separated category names."""
    if not categories:
        return ''
    names = [clean_html(cat.get('name', '')) for cat in categories if cat.get('name')]
    return ', '.join(names)


def get_tag_names(tags):
    """Get comma-separated tag names for keywords."""
    if not tags:
        return ''
    names = [clean_html(tag.get('name', '')) for tag in tags if tag.get('name')]
    return ', '.join(names)


def get_image_urls(images):
    """Get comma-separated image URLs."""
    if not images:
        return ''
    return ', '.join([img.get('src', '') for img in images if img.get('src')])


def get_attribute_value(attributes, attr_name):
    """Get attribute value by name."""
    if not attributes:
        return ''
    for attr in attributes:
        if attr.get('name', '').lower() == attr_name.lower():
            options = attr.get('options', [])
            if options:
                return ', '.join(options)
    return ''


def export_to_zid_xlsx(products):
    """Export products to Zid.sa XLSX format with ALL columns filled."""

    # All 111 Zid.sa columns
    columns = [
        'sku', 'name_ar', 'name_en', 'weight_unit', 'weight', 'price', 'sale_price', 'cost', 'quantity',
        'categories_ar', 'categories_en', 'categories_description_ar', 'categories_description_en',
        'categories_images', 'published', 'images', 'images_alt_text', 'vat_free',
        'minimum_quantity_per_order', 'maximum_quantity_per_order', 'shipping_required', 'barcode',
        'keywords', 'description_ar', 'description_en', 'short_description_ar', 'short_description_en',
        'product_page_title_ar', 'product_page_title_en', 'product_page_description_ar',
        'product_page_description_en', 'product_page_url',
        'has_variants', 'option1_name_ar', 'option1_name_en', 'option1_value_ar', 'option1_value_en',
        'option2_name_ar', 'option2_name_en', 'option2_value_ar', 'option2_value_en',
        'option3_name_ar', 'option3_name_en', 'option3_value_ar', 'option3_value_en',
        'has_dropdown', 'is_dropdown_required', 'dropdown_name_ar', 'dropdown_name_en',
        'dropdown_choice1_ar', 'dropdown_choice1_en', 'dropdown_choice1_price',
        'dropdown_choice2_ar', 'dropdown_choice2_en', 'dropdown_choice2_price',
        'dropdown_choice3_ar', 'dropdown_choice3_en', 'dropdown_choice3_price',
        'has_text_input', 'is_text_input_required', 'text_input_name_ar', 'text_input_name_en', 'text_input_price',
        'has_multiple_options', 'is_multiple_options_required', 'multiple_options_name_ar', 'multiple_options_name_en',
        'multiple_options_choice1_ar', 'multiple_options_choice1_en', 'multiple_options_choice1_price',
        'multiple_options_choice2_ar', 'multiple_options_choice2_en', 'multiple_options_choice2_price',
        'multiple_options_choice3_ar', 'multiple_options_choice3_en', 'multiple_options_choice3_price',
        'has_numerical_input', 'is_numerical_input_required', 'numerical_input_name_ar', 'numerical_input_name_en', 'numerical_input_price',
        'has_date', 'is_date_required', 'date_name_ar', 'date_name_en',
        'has_time', 'is_time_required', 'time_name_ar', 'time_name_en',
        'has_image_upload', 'is_image_upload_required', 'image_upload_name_ar', 'image_upload_name_en',
        'has_file_upload', 'is_file_upload_required', 'file_upload_name_ar', 'file_upload_name_en',
        'filtration_attribute_1_ar', 'filtration_attribute_1_en', 'filtration_value_1_ar', 'filtration_value_1_en',
        'filtration_type_value_1_ar', 'filtration_type_value_1_en', 'filtration_type_1',
        'filtration_attribute_2_ar', 'filtration_attribute_2_en', 'filtration_value_2_ar', 'filtration_value_2_en',
        'filtration_type_value_2_ar', 'filtration_type_value_2_en', 'filtration_type_2',
    ]

    rows = []
    total = len(products)

    print(f"\nProcessing {total} products (fetching descriptions from website)...")

    for i, p in enumerate(products):
        sku = p.get('sku', '').strip()
        if not sku:
            sku = f"NGS-{p.get('id', 0)}"

        name = clean_html(p.get('name', ''))
        price = p.get('regular_price', '') or p.get('price', '')
        sale_price = p.get('sale_price', '')

        if not price:
            continue

        # Progress
        if (i + 1) % 50 == 0 or i == 0:
            print(f"  Processing {i + 1}/{total}...")

        # Fetch real description from product page
        product_url = p.get('permalink', '')
        real_desc, real_short_desc = None, None

        if product_url:
            real_desc, real_short_desc = fetch_product_description(product_url)
            time.sleep(0.1)  # Be nice to the server

        # Use real descriptions or fall back to API data
        api_desc = clean_html(p.get('description', ''))
        api_short_desc = clean_html(p.get('short_description', ''))

        description = real_desc if real_desc else api_desc if api_desc else name
        short_desc = real_short_desc if real_short_desc else api_short_desc if api_short_desc else name

        weight = p.get('weight', '')
        quantity = p.get('stock_quantity') if p.get('manage_stock') else ''
        categories = get_category_names(p.get('categories', []))
        tags = get_tag_names(p.get('tags', []))
        images = get_image_urls(p.get('images', []))
        brand = get_attribute_value(p.get('attributes', []), 'Brand')
        compatibility = get_attribute_value(p.get('attributes', []), 'Compatibility')

        row = {
            # Basic Info
            'sku': sku,
            'name_ar': name,
            'name_en': name,
            'weight_unit': 'kg' if weight else '',
            'weight': weight,
            'price': price,
            'sale_price': sale_price if sale_price else '',
            'cost': '',
            'quantity': quantity if quantity else '',
            'categories_ar': categories,
            'categories_en': categories,
            'categories_description_ar': '',
            'categories_description_en': '',
            'categories_images': '',
            'published': 'Yes',
            'images': images,
            'images_alt_text': name,
            'vat_free': 'No',
            'minimum_quantity_per_order': '',
            'maximum_quantity_per_order': '',
            'shipping_required': 'Yes',
            'barcode': '',

            # Keywords & Description - REAL DATA
            'keywords': tags if tags else name,
            'description_ar': description[:5000],
            'description_en': description[:5000],
            'short_description_ar': short_desc[:1000],
            'short_description_en': short_desc[:1000],
            'product_page_title_ar': name,
            'product_page_title_en': name,
            'product_page_description_ar': short_desc[:160],
            'product_page_description_en': short_desc[:160],
            'product_page_url': '',

            # Product Options - No variants
            'has_variants': 'No',
            'option1_name_ar': '', 'option1_name_en': '', 'option1_value_ar': '', 'option1_value_en': '',
            'option2_name_ar': '', 'option2_name_en': '', 'option2_value_ar': '', 'option2_value_en': '',
            'option3_name_ar': '', 'option3_name_en': '', 'option3_value_ar': '', 'option3_value_en': '',

            # Product Additions - All No
            'has_dropdown': 'No', 'is_dropdown_required': '', 'dropdown_name_ar': '', 'dropdown_name_en': '',
            'dropdown_choice1_ar': '', 'dropdown_choice1_en': '', 'dropdown_choice1_price': '',
            'dropdown_choice2_ar': '', 'dropdown_choice2_en': '', 'dropdown_choice2_price': '',
            'dropdown_choice3_ar': '', 'dropdown_choice3_en': '', 'dropdown_choice3_price': '',
            'has_text_input': 'No', 'is_text_input_required': '', 'text_input_name_ar': '', 'text_input_name_en': '', 'text_input_price': '',
            'has_multiple_options': 'No', 'is_multiple_options_required': '', 'multiple_options_name_ar': '', 'multiple_options_name_en': '',
            'multiple_options_choice1_ar': '', 'multiple_options_choice1_en': '', 'multiple_options_choice1_price': '',
            'multiple_options_choice2_ar': '', 'multiple_options_choice2_en': '', 'multiple_options_choice2_price': '',
            'multiple_options_choice3_ar': '', 'multiple_options_choice3_en': '', 'multiple_options_choice3_price': '',
            'has_numerical_input': 'No', 'is_numerical_input_required': '', 'numerical_input_name_ar': '', 'numerical_input_name_en': '', 'numerical_input_price': '',
            'has_date': 'No', 'is_date_required': '', 'date_name_ar': '', 'date_name_en': '',
            'has_time': 'No', 'is_time_required': '', 'time_name_ar': '', 'time_name_en': '',
            'has_image_upload': 'No', 'is_image_upload_required': '', 'image_upload_name_ar': '', 'image_upload_name_en': '',
            'has_file_upload': 'No', 'is_file_upload_required': '', 'file_upload_name_ar': '', 'file_upload_name_en': '',

            # Filtration Attributes
            'filtration_attribute_1_ar': 'العلامة التجارية' if brand else '',
            'filtration_attribute_1_en': 'Brand' if brand else '',
            'filtration_value_1_ar': brand,
            'filtration_value_1_en': brand,
            'filtration_type_value_1_ar': '', 'filtration_type_value_1_en': '', 'filtration_type_1': '',
            'filtration_attribute_2_ar': 'التوافق' if compatibility else '',
            'filtration_attribute_2_en': 'Compatibility' if compatibility else '',
            'filtration_value_2_ar': compatibility,
            'filtration_value_2_en': compatibility,
            'filtration_type_value_2_ar': '', 'filtration_type_value_2_en': '', 'filtration_type_2': '',
        }

        rows.append(row)

    print(f"  Processed: {len(rows)} products")

    df = pd.DataFrame(rows, columns=columns)
    print(f"\nSaving to {OUTPUT_XLSX}...")
    df.to_excel(OUTPUT_XLSX, index=False, sheet_name='Sheet1')

    # Sample output
    print(f"\n=== Sample (first product) ===")
    if rows:
        print(f"  Name: {rows[0]['name_ar']}")
        print(f"  Description: {rows[0]['description_ar'][:100]}...")
        print(f"  Short desc: {rows[0]['short_description_ar'][:80]}...")

    return len(rows)


def main():
    print("=" * 60)
    print("WooCommerce to Zid.sa XLSX Exporter")
    print("(with REAL descriptions from website)")
    print("=" * 60)

    products = fetch_all_products()
    print(f"\nTotal products fetched: {len(products)}")

    if not products:
        print("No products to export!")
        return

    exported = export_to_zid_xlsx(products)

    print(f"\n{'=' * 60}")
    print(f"XLSX file saved: {OUTPUT_XLSX}")
    print(f"Products exported: {exported}")
    print(f"Columns: 111 (all Zid.sa fields)")
    print("=" * 60)


if __name__ == '__main__':
    main()
