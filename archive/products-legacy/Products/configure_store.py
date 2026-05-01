#!/usr/bin/env python3
"""Configure WooCommerce store settings via REST API."""

import os
import sys
import requests
from requests.auth import HTTPBasicAuth


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

STORE = os.environ.get("STORE_URL", "")
CK = os.environ.get("WC_CONSUMER_KEY", "")
CS = os.environ.get("WC_CONSUMER_SECRET", "")
AUTH = HTTPBasicAuth(CK, CS)


def update_setting(group, setting_id, value):
    """Update a WooCommerce setting."""
    url = f"{STORE}/wp-json/wc/v3/settings/{group}/{setting_id}"
    resp = requests.put(url, auth=AUTH, json={"value": value}, timeout=30)
    if resp.status_code == 200:
        return True
    print(f"    WARN: {setting_id} -> {resp.status_code}: {resp.text[:100]}")
    return False


def main():
    print("=== Neogen Store Configuration ===")
    print(f"Store: {STORE}")
    print()

    # 1. General Settings
    print("1. General Settings")
    settings = {
        ("general", "woocommerce_store_address"): "King Fahd Road",
        ("general", "woocommerce_store_address_2"): "Al Olaya District",
        ("general", "woocommerce_store_city"): "Riyadh",
        ("general", "woocommerce_default_country"): "SA",
        ("general", "woocommerce_currency"): "SAR",
        ("general", "woocommerce_currency_pos"): "right_space",
    }

    for (group, setting_id), value in settings.items():
        ok = update_setting(group, setting_id, value)
        status = "OK" if ok else "FAIL"
        print(f"  [{status}] {setting_id} = {value}")

    # 2. Tax Settings - prices should include tax for Saudi consumers
    print("\n2. Tax Settings (VAT 15%)")
    tax_settings = {
        ("tax", "woocommerce_prices_include_tax"): "yes",
        ("tax", "woocommerce_tax_display_shop"): "incl",
        ("tax", "woocommerce_tax_display_cart"): "incl",
        ("tax", "woocommerce_price_display_suffix"): "شامل الضريبة",
    }

    for (group, setting_id), value in tax_settings.items():
        ok = update_setting(group, setting_id, value)
        status = "OK" if ok else "FAIL"
        print(f"  [{status}] {setting_id} = {value}")

    # Check if VAT rate exists
    resp = requests.get(f"{STORE}/wp-json/wc/v3/taxes", auth=AUTH, timeout=30)
    taxes = resp.json()
    has_vat = any(t.get("rate") == "15.0000" for t in taxes if isinstance(t, dict))
    if has_vat:
        print("  [OK] VAT 15% rate already exists")
    else:
        # Create VAT rate
        vat = requests.post(
            f"{STORE}/wp-json/wc/v3/taxes",
            auth=AUTH,
            json={
                "country": "SA",
                "rate": "15.0000",
                "name": "VAT",
                "shipping": True,
                "class": "standard",
            },
            timeout=30,
        )
        if vat.status_code in (200, 201):
            print("  [OK] Created VAT 15% rate")
        else:
            print(f"  [FAIL] VAT creation: {vat.status_code}")

    # 3. Enable COD Payment Gateway
    print("\n3. Payment Gateways")
    resp = requests.put(
        f"{STORE}/wp-json/wc/v3/payment_gateways/cod",
        auth=AUTH,
        json={
            "enabled": True,
            "title": "الدفع عند الاستلام",
            "description": "ادفع نقدا عند استلام الطلب",
        },
        timeout=30,
    )
    if resp.status_code == 200:
        print("  [OK] COD (Cash on Delivery) enabled")
    else:
        print(f"  [FAIL] COD: {resp.status_code}")

    # Enable bank transfer
    resp = requests.put(
        f"{STORE}/wp-json/wc/v3/payment_gateways/bacs",
        auth=AUTH,
        json={
            "enabled": True,
            "title": "تحويل بنكي",
            "description": "قم بالتحويل مباشرة إلى حسابنا البنكي",
        },
        timeout=30,
    )
    if resp.status_code == 200:
        print("  [OK] Bank Transfer enabled")
    else:
        print(f"  [FAIL] BACS: {resp.status_code}")

    # 4. Verify Shipping Zones
    print("\n4. Shipping Zones")
    resp = requests.get(f"{STORE}/wp-json/wc/v3/shipping/zones", auth=AUTH, timeout=30)
    zones = resp.json()
    for z in zones:
        methods_resp = requests.get(
            f"{STORE}/wp-json/wc/v3/shipping/zones/{z['id']}/methods",
            auth=AUTH,
            timeout=30,
        )
        methods = methods_resp.json()
        method_names = [m.get("method_title", "") for m in methods]
        print(f"  Zone #{z['id']}: {z['name']} -> {', '.join(method_names) or 'no methods'}")

    # 5. Account & Privacy settings
    print("\n5. Account Settings")
    account_settings = {
        ("account", "woocommerce_enable_guest_checkout"): "yes",
        ("account", "woocommerce_enable_checkout_login_reminder"): "yes",
        ("account", "woocommerce_enable_myaccount_registration"): "yes",
    }

    for (group, setting_id), value in account_settings.items():
        ok = update_setting(group, setting_id, value)
        status = "OK" if ok else "FAIL"
        print(f"  [{status}] {setting_id} = {value}")

    # 6. Final verification
    print("\n=== Configuration Summary ===")
    verify = {
        "Currency": ("general", "woocommerce_currency"),
        "Country": ("general", "woocommerce_default_country"),
        "Tax Incl": ("tax", "woocommerce_prices_include_tax"),
        "Tax Display Shop": ("tax", "woocommerce_tax_display_shop"),
        "Tax Display Cart": ("tax", "woocommerce_tax_display_cart"),
    }

    for label, (group, sid) in verify.items():
        resp = requests.get(
            f"{STORE}/wp-json/wc/v3/settings/{group}/{sid}",
            auth=AUTH,
            timeout=30,
        )
        val = resp.json().get("value", "?")
        print(f"  {label}: {val}")

    # Check payment gateways status
    resp = requests.get(
        f"{STORE}/wp-json/wc/v3/payment_gateways", auth=AUTH, timeout=30
    )
    for g in resp.json():
        if g.get("enabled"):
            print(f"  Payment: {g['id']} ({g.get('title','')}) ENABLED")

    print("\nDone!")


if __name__ == "__main__":
    main()
