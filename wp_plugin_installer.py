#!/usr/bin/env python3
"""
WordPress Plugin Installer via Playwright
Automates plugin upload/update through wp-admin dashboard.
"""

import argparse
import sys
import os
from playwright.sync_api import sync_playwright, TimeoutError as PlaywrightTimeout


def install_plugin(url, user, password, plugin_zip):
    if not os.path.exists(plugin_zip):
        print(f"ERROR: Plugin file not found: {plugin_zip}")
        sys.exit(1)

    plugin_name = os.path.basename(plugin_zip)
    # Derive site root from admin URL
    site_root = url.split("/wp-admin")[0] if "/wp-admin" in url else url.rstrip("/")
    admin_url = site_root + "/wp-admin"
    print(f"Installing {plugin_name} on {site_root}")

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=False, slow_mo=500)
        context = browser.new_context(viewport={"width": 1400, "height": 900})
        page = context.new_page()

        # Step 1: Login
        print("[1/5] Logging into WordPress...")
        login_url = site_root + "/wp-login.php"
        page.goto(login_url, timeout=60000)
        page.wait_for_selector("#user_login", state="visible", timeout=30000)
        page.fill("#user_login", user)
        page.fill("#user_pass", password)
        page.click("#wp-submit")
        page.wait_for_timeout(5000)

        if "wp-admin" in page.url:
            print("       Logged in successfully.")
        else:
            print(f"       Current URL after login: {page.url}")
            print("       Continuing...")

        # Step 2: Navigate to Add Plugin page
        print("[2/5] Navigating to plugin upload page...")
        page.goto(admin_url + "/plugin-install.php", wait_until="domcontentloaded", timeout=60000)

        # Click "Upload Plugin" button
        page.click(".upload-view-toggle")
        page.wait_for_selector("#pluginzip", state="visible", timeout=10000)

        # Step 3: Upload zip file
        print(f"[3/5] Uploading {plugin_name}...")
        page.set_input_files("#pluginzip", plugin_zip)
        page.click("#install-plugin-submit")

        # Wait for the installation to complete (large zip can take time)
        print("       Waiting for upload to complete...")
        page.wait_for_load_state("domcontentloaded", timeout=180000)

        # Step 4: Handle result - check for "replace" or "activate" or error
        page_content = page.content()

        if "plugin-install" in page.url and "overwrite" in page_content.lower():
            # WordPress asks to replace existing version
            print("[4/5] Existing version detected, replacing...")
            replace_btn = page.locator("a.update-from-upload-overwrite")
            if replace_btn.count() > 0:
                replace_btn.click()
                page.wait_for_load_state("domcontentloaded", timeout=180000)
                page_content = page.content()

        if "activate-plugin" in page_content or "Activate Plugin" in page_content:
            print("[4/5] Activating plugin...")
            activate_link = page.locator("a:has-text('Activate Plugin')")
            if activate_link.count() > 0:
                activate_link.click()
                page.wait_for_load_state("domcontentloaded", timeout=30000)
                print("       Plugin activated.")
            else:
                print("       Activate link not found, checking status...")
        elif "Plugin activated" in page_content:
            print("[4/5] Plugin already activated.")
        else:
            print("[4/5] Checking installation result...")

        # Step 5: Verify on plugins page
        print("[5/5] Verifying installation...")
        page.goto(admin_url + "/plugins.php", wait_until="domcontentloaded", timeout=60000)

        screenshot_path = os.path.join(os.path.dirname(plugin_zip), "wp_plugin_install_result.png")
        page.screenshot(path=screenshot_path, full_page=False)
        print(f"       Screenshot saved: {screenshot_path}")

        # Check if Elementor Pro is listed and active
        elementor_row = page.locator("tr[data-plugin*='elementor-pro']")
        if elementor_row.count() > 0:
            is_active = "active" in (elementor_row.get_attribute("class") or "")
            version_text = elementor_row.locator(".plugin-version-author-uri").text_content()
            print(f"       Elementor Pro found: {'ACTIVE' if is_active else 'INACTIVE'}")
            print(f"       Version info: {version_text.strip() if version_text else 'unknown'}")
        else:
            print("       Could not find Elementor Pro row in plugins list.")
            print("       Check the screenshot for details.")

        page.wait_for_timeout(3000)
        browser.close()

    print("Done.")


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Install WordPress plugin via browser automation")
    parser.add_argument("--url", required=True, help="WordPress admin URL (e.g. https://site.com/wp-admin/)")
    parser.add_argument("--user", required=True, help="WordPress admin username")
    parser.add_argument("--password", required=True, help="WordPress admin password")
    parser.add_argument("--plugin", required=True, help="Path to plugin .zip file")
    args = parser.parse_args()

    install_plugin(args.url, args.user, args.password, args.plugin)
