#!/usr/bin/env python3
"""
NeoGen Store — AliExpress Price Sync Script
Usage:
  python3 price_sync.py                    # Dry run (show changes, don't apply)
  python3 price_sync.py --apply            # Apply price changes to WooCommerce
  python3 price_sync.py --check-stock      # Check stock status only

Requires: pip install requests

Config: Edit PRICING_RULES below + fill in aliexpress_prices.csv
"""

import csv
import json
import math
import argparse
import requests
from requests.auth import HTTPBasicAuth
from pathlib import Path

# =============================================
# CONFIGURATION — EDIT THESE
# =============================================
STORE_URL = "https://ngs1.blazr.net"   # Change to neogen.store when domain migrated

# WooCommerce API keys (from DSers or newly created)
# Get from: WP Admin → WooCommerce → Settings → Advanced → REST API
WC_KEY = ""       # ← PASTE YOUR CONSUMER KEY HERE
WC_SECRET = ""    # ← PASTE YOUR CONSUMER SECRET HERE

# Pricing formula: final_price = (aliexpress_usd × USD_TO_SAR × MARKUP) + FLAT_FEE
USD_TO_SAR = 3.75
MARKUP_MULTIPLIER = 1.8   # 80% markup over cost
FLAT_FEE_SAR = 30         # Covers shipping + handling

# Price brackets — override multiplier for high/low value items
PRICING_RULES = [
    # (max_cost_usd, multiplier, flat_fee)
    (10,    2.5,  20),    # Cheap items: 150% markup + 20 SAR
    (30,    2.0,  25),    # Low-mid: 100% markup + 25 SAR
    (100,   1.8,  30),    # Mid: 80% markup + 30 SAR
    (300,   1.6,  40),    # High: 60% markup + 40 SAR
    (1000,  1.5,  50),    # Premium: 50% markup + 50 SAR
    (99999, 1.35, 100),   # Luxury: 35% markup + 100 SAR
]

# Path to the mapping CSV with AliExpress prices filled in
MAPPING_CSV = Path(__file__).parent / "neogen_aliexpress_mapping.csv"

# =============================================
# LOGIC — DON'T EDIT BELOW UNLESS YOU KNOW
# =============================================

def calculate_price(cost_usd):
    """Apply tiered pricing formula."""
    for max_cost, mult, flat in PRICING_RULES:
        if cost_usd <= max_cost:
            raw = (cost_usd * USD_TO_SAR * mult) + flat
            # Round to nice price ending
            if raw < 100:
                return math.ceil(raw / 5) * 5 - 1
            elif raw < 500:
                return math.ceil(raw / 10) * 10 - 1
            elif raw < 2000:
                return math.ceil(raw / 50) * 50 - 1
            elif raw < 10000:
                return math.ceil(raw / 100) * 100 - 1
            else:
                return math.ceil(raw / 500) * 500 - 1
    return round(cost_usd * USD_TO_SAR * 1.35 + 100)


def load_mapping():
    """Load CSV mapping with AliExpress prices."""
    products = []
    with open(MAPPING_CSV, encoding="utf-8-sig") as f:
        reader = csv.DictReader(f)
        for row in reader:
            ali_price = row.get("aliexpress_price_usd", "").strip()
            if ali_price:
                try:
                    products.append({
                        "wc_id": int(row["wc_id"]),
                        "sku": row["sku"],
                        "name": row["name"],
                        "current_price": float(row.get("current_price_sar", 0)),
                        "ali_price_usd": float(ali_price),
                    })
                except ValueError:
                    pass
    return products


def sync_prices(apply=False):
    """Calculate new prices and optionally push to WooCommerce."""
    if not WC_KEY or not WC_SECRET:
        print("ERROR: Set WC_KEY and WC_SECRET in the script first!")
        print("Get them from: WP Admin → WooCommerce → Settings → Advanced → REST API")
        return

    products = load_mapping()
    if not products:
        print("No products with AliExpress prices found in CSV.")
        print(f"Fill the 'aliexpress_price_usd' column in: {MAPPING_CSV}")
        return

    auth = HTTPBasicAuth(WC_KEY, WC_SECRET)
    changes = []

    print(f"\n{'SKU':<18} {'Product':<40} {'AliEx $':<10} {'Current':<10} {'New SAR':<10} {'Margin'}")
    print("─" * 120)

    for p in products:
        new_price = calculate_price(p["ali_price_usd"])
        cost_sar = p["ali_price_usd"] * USD_TO_SAR
        margin_pct = ((new_price - cost_sar) / cost_sar * 100) if cost_sar > 0 else 0
        changed = abs(new_price - p["current_price"]) > 5

        flag = "← CHANGE" if changed else ""
        print(f"{p['sku']:<18} {p['name'][:38]:<40} ${p['ali_price_usd']:<9.2f} {p['current_price']:<9.0f} {new_price:<9.0f} {margin_pct:>5.0f}%  {flag}")

        if changed:
            # Set regular_price higher for "was" display, sale_price = actual
            regular = math.ceil(new_price * 1.12 / 10) * 10 - 1  # ~12% "discount" look
            changes.append({
                "id": p["wc_id"],
                "regular_price": str(regular),
                "sale_price": str(new_price),
            })

    print(f"\n{'─' * 120}")
    print(f"Total: {len(products)} products checked, {len(changes)} need price updates")

    if not apply:
        print("\n→ DRY RUN: No changes applied. Use --apply to push prices.")
        return

    if not changes:
        print("All prices are current. Nothing to update.")
        return

    # Batch update in chunks of 50
    print(f"\nPushing {len(changes)} price updates...")
    for i in range(0, len(changes), 50):
        batch = changes[i:i+50]
        resp = requests.post(
            f"{STORE_URL}/wp-json/wc/v3/products/batch",
            auth=auth,
            json={"update": batch},
            timeout=60,
        )
        if resp.status_code == 200:
            data = resp.json()
            print(f"  Batch {i//50+1}: {len(data.get('update',[]))} updated")
        else:
            print(f"  Batch {i//50+1}: ERROR {resp.status_code} - {resp.text[:100]}")

    print("\nDone! Prices synced.")


def check_stock():
    """Check which products are out of stock on AliExpress (manual check needed)."""
    products = load_mapping()
    print(f"\n{len(products)} products have AliExpress prices.")
    print("Stock checking requires scraping AliExpress (blocked by anti-bot).")
    print("Use DSers dashboard for automatic stock sync instead.")
    print("\nDSers stock sync: DSers.com → Settings → Sync → Enable auto-sync")


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="NeoGen AliExpress Price Sync")
    parser.add_argument("--apply", action="store_true", help="Apply price changes")
    parser.add_argument("--check-stock", action="store_true", help="Check stock")
    args = parser.parse_args()

    if args.check_stock:
        check_stock()
    else:
        sync_prices(apply=args.apply)
