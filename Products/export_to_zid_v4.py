#!/usr/bin/env python3.11
"""
Export WooCommerce products to Zid.sa XLSX format - COMPLETE DATA.
Scrapes ALL product info from neogen.store website pages:
- Full descriptions from tabs
- FAQ content from accordions
- Short descriptions
- All available product details
"""
import os
import re
import time
import sys
from dotenv import load_dotenv
import requests
from requests.auth import HTTPBasicAuth
import pandas as pd
from bs4 import BeautifulSoup
from concurrent.futures import ThreadPoolExecutor, as_completed

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


def fetch_all_product_info(product_url, product_name):
    """Fetch ALL product information from website page."""
    result = {
        'description': '',
        'short_description': '',
        'faq_content': '',
        'all_content': '',
        'json_ld': None,
    }

    try:
        resp = requests.get(product_url, timeout=30, headers={
            'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36'
        })
        if resp.status_code != 200:
            return result

        soup = BeautifulSoup(resp.text, 'html.parser')

        # 1. Short description
        short_desc_div = soup.select_one('.woocommerce-product-details__short-description')
        if short_desc_div:
            result['short_description'] = short_desc_div.get_text(strip=True)

        # 2. Full description from tabs
        desc_tab = soup.select_one('.woocommerce-Tabs-panel--description')
        if desc_tab:
            result['description'] = desc_tab.get_text(strip=True)

        # 3. Try Elementor tab content
        if not result['description']:
            tab_contents = soup.select('.elementor-tab-content')
            if tab_contents:
                texts = [tc.get_text(strip=True) for tc in tab_contents if tc.get_text(strip=True)]
                result['description'] = '\n\n'.join(texts)

        # 4. FAQ/Accordion content
        faq_items = []

        # Try Elementor accordions
        accordions = soup.select('.elementor-toggle-item')
        for acc in accordions:
            title = acc.select_one('.elementor-toggle-title')
            content = acc.select_one('.elementor-toggle-content')
            if title and content:
                q = title.get_text(strip=True)
                a = content.get_text(strip=True)
                if q and a:
                    faq_items.append((q, a))

        # Try FAQ section
        faq_section = soup.select('.faq-item, .accordion-item, [class*="faq"]')
        for item in faq_section:
            q_elem = item.select_one('.question, .faq-question, [class*="question"]')
            a_elem = item.select_one('.answer, .faq-answer, [class*="answer"]')
            if q_elem and a_elem:
                q = q_elem.get_text(strip=True)
                a = a_elem.get_text(strip=True)
                if q and a:
                    faq_items.append((q, a))

        if faq_items:
            faq_text = "الأسئلة الشائعة:\n\n"
            for q, a in faq_items:
                faq_text += f"س: {q}\nج: {a}\n\n"
            result['faq_content'] = faq_text.strip()

        # 5. Try to extract JSON-LD structured data
        json_ld = soup.select_one('script[type="application/ld+json"]')
        if json_ld:
            try:
                import json
                data = json.loads(json_ld.string)
                if isinstance(data, dict) and data.get('@type') == 'Product':
                    if data.get('description'):
                        result['json_ld'] = data.get('description')
            except:
                pass

        # 6. Fallback: product summary area
        if not result['description'] and not result['short_description']:
            summary = soup.select_one('.product-entry-summary, .summary, .product-summary')
            if summary:
                paragraphs = summary.select('p')
                texts = [p.get_text(strip=True) for p in paragraphs if p.get_text(strip=True)]
                if texts:
                    result['short_description'] = ' '.join(texts[:2])

        # 7. Meta description fallback
        if not result['description']:
            meta_desc = soup.select_one('meta[name="description"]')
            if meta_desc:
                result['description'] = meta_desc.get('content', '')

        # Combine all content
        all_parts = []
        if result['description']:
            all_parts.append(result['description'])
        if result['faq_content']:
            all_parts.append('---')
            all_parts.append(result['faq_content'])
        result['all_content'] = '\n\n'.join(all_parts) if all_parts else ''

        return result

    except Exception as e:
        return result


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


def process_single_product(args):
    """Process a single product (for parallel execution)."""
    p, idx, total = args

    sku = p.get('sku', '').strip()
    if not sku:
        sku = f"NGS-{p.get('id', 0)}"

    name = clean_html(p.get('name', ''))
    price = p.get('regular_price', '') or p.get('price', '')
    sale_price = p.get('sale_price', '')

    if not price:
        return None

    # Fetch real content from website
    product_url = p.get('permalink', '')
    web_info = {'description': '', 'short_description': '', 'faq_content': '', 'all_content': ''}

    if product_url:
        web_info = fetch_all_product_info(product_url, name)

    # Use web scraped data or fall back to API data
    api_desc = clean_html(p.get('description', ''))
    api_short_desc = clean_html(p.get('short_description', ''))

    # Best available description - prioritize API data since website has placeholders
    # Skip website content if it contains placeholder text
    placeholder = "A detailed answer to provide information about your business"

    web_desc = web_info['all_content'] if web_info['all_content'] and placeholder not in web_info['all_content'] else ''
    web_short = web_info['short_description'] if web_info['short_description'] and placeholder not in web_info['short_description'] else ''

    description = web_desc if web_desc else (api_desc if api_desc else name)
    short_desc = web_short if web_short else (api_short_desc if api_short_desc else name)

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

    return row


def export_to_zid_xlsx(products):
    """Export products to Zid.sa XLSX format with ALL website data."""

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

    print(f"\nProcessing {total} products (scraping website for full data)...")
    print("This may take a few minutes...\n")

    # Process products in parallel for speed
    from concurrent.futures import ThreadPoolExecutor, as_completed

    args_list = [(p, i, total) for i, p in enumerate(products)]
    completed = 0

    with ThreadPoolExecutor(max_workers=10) as executor:
        futures = {executor.submit(process_single_product, args): args for args in args_list}

        for future in as_completed(futures):
            completed += 1
            if completed % 50 == 0 or completed == 1 or completed == total:
                pct = (completed / total) * 100
                print(f"  Progress: {completed}/{total} ({pct:.1f}%)")
                sys.stdout.flush()

            row = future.result()
            if row:
                rows.append(row)

    print(f"\n  Processed: {len(rows)} products")

    df = pd.DataFrame(rows, columns=columns)
    print(f"\nSaving to {OUTPUT_XLSX}...")
    df.to_excel(OUTPUT_XLSX, index=False, sheet_name='Sheet1')

    # Sample output
    print(f"\n=== Sample (first product) ===")
    if rows:
        print(f"  Name: {rows[0]['name_ar']}")
        desc = rows[0]['description_ar']
        print(f"  Description ({len(desc)} chars): {desc[:150]}...")
        print(f"  Short desc: {rows[0]['short_description_ar'][:80]}...")

    return len(rows)


def main():
    print("=" * 60)
    print("WooCommerce to Zid.sa XLSX Exporter")
    print("(with COMPLETE website data scraping)")
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
