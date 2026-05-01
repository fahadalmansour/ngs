#!/usr/bin/env python3
"""Batch import products to WooCommerce from CSV using the REST API."""

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


def api_call(endpoint, method="GET", data=None):
    """Make an authenticated WooCommerce API call using requests."""
    url = f"{STORE}/wp-json/wc/v3/{endpoint}"
    auth = HTTPBasicAuth(CK, CS)
    try:
        if method == "GET":
            resp = requests.get(url, auth=auth, timeout=30)
        elif method == "POST":
            resp = requests.post(url, auth=auth, json=data, timeout=60)
        else:
            resp = requests.request(method, url, auth=auth, json=data, timeout=60)
        resp.raise_for_status()
        return resp.json()
    except requests.exceptions.HTTPError as e:
        return {"error": str(e), "status_code": e.response.status_code}
    except requests.exceptions.RequestException as e:
        return {"error": str(e)}


# Get existing SKUs
print("Fetching existing SKUs...")
existing_skus = set()
page = 1
while True:
    products = api_call(f"products?per_page=100&page={page}")
    if not products or isinstance(products, dict):
        break
    for p in products:
        if p.get("sku"):
            existing_skus.add(p["sku"])
    if len(products) < 100:
        break
    page += 1
print(f"Found {len(existing_skus)} existing products")

# Read CSV
csv_path = os.path.join(os.path.dirname(__file__), "woocommerce-final.csv")
with open(csv_path, "r") as f:
    reader = csv.DictReader(f)
    rows = list(reader)

# Prepare products to import
to_import = []
for row in rows:
    sku = row.get("SKU", "")
    if sku in existing_skus or not sku:
        continue

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

    img = row.get("Images", "")
    if img and img.startswith("http"):
        product["images"] = [{"src": img}]

    brand = row.get("Brands", "")
    if brand and brand != "nan":
        product["brands"] = [{"name": brand}]

    to_import.append(product)

print(f"Products to import: {len(to_import)}")

# Import in batches of 10
batch_size = 10
imported = 0
errors = 0

for i in range(0, len(to_import), batch_size):
    batch = to_import[i : i + batch_size]
    result = api_call("products/batch", "POST", {"create": batch})

    if result.get("create"):
        for p in result["create"]:
            if p.get("id"):
                imported += 1
            else:
                errors += 1
        print(f"Progress: {imported}/{len(to_import)} imported, {errors} errors")
    else:
        print(f"Batch error: {str(result)[:100]}")
        errors += len(batch)

    time.sleep(1)

print(f"\n=== COMPLETE ===")
print(f"Imported: {imported}")
print(f"Errors: {errors}")
print(f"Total in store: {len(existing_skus) + imported}")
