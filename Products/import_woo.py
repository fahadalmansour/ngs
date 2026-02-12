#!/usr/bin/env python3
"""Import products one-by-one to WooCommerce from CSV using the REST API."""

import csv
import json
import os
import sys
import time

import requests
from requests.auth import HTTPBasicAuth

# Load credentials from .env file or environment
def load_env():
    env_path = os.path.join(os.path.dirname(__file__), ".env")
    if os.path.exists(env_path):
        with open(env_path) as f:
            for line in f:
                line = line.strip()
                if line and not line.startswith("#") and "=" in line:
                    key, _, value = line.partition("=")
                    os.environ.setdefault(key.strip(), value.strip())

load_env()

STORE = os.environ.get("STORE_URL", "")
CK = os.environ.get("WC_CONSUMER_KEY", "")
CS = os.environ.get("WC_CONSUMER_SECRET", "")

if not all([STORE, CK, CS]):
    print("ERROR: Missing credentials. Set STORE_URL, WC_CONSUMER_KEY, WC_CONSUMER_SECRET")
    print("       in environment or in Products/.env")
    sys.exit(1)


def get_existing_skus():
    """Get all existing SKUs from the store."""
    skus = set()
    page = 1
    auth = HTTPBasicAuth(CK, CS)
    while True:
        url = f"{STORE}/wp-json/wc/v3/products"
        resp = requests.get(url, auth=auth, params={"per_page": 100, "page": page}, timeout=30)
        resp.raise_for_status()
        products = resp.json()
        if not products:
            break
        for p in products:
            if p.get("sku"):
                skus.add(p["sku"])
        page += 1
        if len(products) < 100:
            break
    return skus


def create_product(product_data):
    """Create a single product via API."""
    url = f"{STORE}/wp-json/wc/v3/products"
    auth = HTTPBasicAuth(CK, CS)
    resp = requests.post(url, auth=auth, json=product_data, timeout=60)
    resp.raise_for_status()
    return resp.json()


# Get existing SKUs
print("Checking existing products...")
existing = get_existing_skus()
print(f"Found {len(existing)} existing products")

# Read CSV and import new products
csv_path = os.path.join(os.path.dirname(__file__), "woocommerce-final.csv")
with open(csv_path, "r") as f:
    reader = csv.DictReader(f)
    rows = list(reader)

imported = 0
skipped = 0
errors = 0

for i, row in enumerate(rows):
    sku = row.get("SKU", "")
    if sku in existing:
        skipped += 1
        continue

    # Prepare product data
    product = {
        "name": row.get("Name", ""),
        "type": "simple",
        "regular_price": str(row.get("Regular price", "")),
        "description": row.get("Description", ""),
        "short_description": row.get("Short description", ""),
        "sku": sku,
        "manage_stock": True,
        "stock_quantity": 10,
        "categories": [{"name": row.get("Categories", "Uncategorized")}],
        "status": "publish",
    }

    # Add image if exists
    img = row.get("Images", "")
    if img and img.startswith("http"):
        product["images"] = [{"src": img}]

    # Add brand if exists
    brand = row.get("Brands", "")
    if brand and brand != "nan":
        product["brands"] = [{"name": brand}]

    try:
        resp = create_product(product)
        if resp.get("id"):
            imported += 1
            existing.add(sku)
            if imported % 10 == 0:
                print(f"Progress: {imported} imported, {skipped} skipped, {errors} errors")
        else:
            errors += 1
            if errors < 5:
                print(f"Error: {resp.get('message', str(resp)[:100])}")
    except requests.exceptions.HTTPError as e:
        errors += 1
        if errors < 5:
            print(f"HTTP Error: {e}")
    except requests.exceptions.RequestException as e:
        errors += 1
        if errors < 5:
            print(f"Request Error: {e}")

    time.sleep(0.3)  # Rate limiting

print(f"\n=== DONE ===")
print(f"Imported: {imported}")
print(f"Skipped (already exists): {skipped}")
print(f"Errors: {errors}")
print(f"Total in store: {len(existing)}")
