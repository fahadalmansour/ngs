#!/usr/bin/env python3.11
"""
WordPress Site Fixer for neogen.store
Fixes shop navigation, footer placeholders, and takes verification screenshots
"""

import asyncio
import os
import base64
from playwright.async_api import async_playwright, Page
from pathlib import Path
import json

# Load credentials from .env file or environment
def _load_env():
    env_paths = [
        os.path.join(os.path.dirname(__file__), ".env"),
        os.path.join(os.path.dirname(__file__), "Products", ".env"),
    ]
    for env_path in env_paths:
        if os.path.exists(env_path):
            with open(env_path) as f:
                for line in f:
                    line = line.strip()
                    if line and not line.startswith("#") and "=" in line:
                        key, _, value = line.partition("=")
                        os.environ.setdefault(key.strip(), value.strip())

_load_env()

# Configuration
WP_URL = os.environ.get("WP_URL", "https://neogen.store")
WP_ADMIN = f"{WP_URL}/wp-admin"
WP_LOGIN = f"{WP_URL}/wp-login.php"
USERNAME = os.environ.get("WP_ADMIN_USER", "")
PASSWORD = os.environ.get("WP_ADMIN_PASSWORD", "")
SCREENSHOTS_DIR = os.path.join(os.path.dirname(__file__), "screenshots")

# WooCommerce API credentials
WC_CONSUMER_KEY = os.environ.get("WC_CONSUMER_KEY_NEOGEN", "")
WC_CONSUMER_SECRET = os.environ.get("WC_CONSUMER_SECRET_NEOGEN", "")

if not all([USERNAME, PASSWORD]):
    print("ERROR: Missing WP credentials. Set WP_ADMIN_USER, WP_ADMIN_PASSWORD")
    print("       in environment or in .env")
    import sys
    sys.exit(1)

class WordPressFixer:
    def __init__(self):
        self.page = None
        self.context = None
        self.browser = None
        self.nonce = None

    async def setup(self, playwright):
        """Initialize browser and context"""
        print("Setting up browser...")
        self.browser = await playwright.chromium.launch(
            headless=False,
            slow_mo=500  # Slow down operations for stability
        )
        self.context = await self.browser.new_context(
            viewport={'width': 1920, 'height': 1080},
            user_agent='Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            ignore_https_errors=True
        )
        self.page = await self.context.new_page()
        self.page.set_default_timeout(30000)  # Set default timeout to 30s

    async def login(self):
        """Login to WordPress admin"""
        print(f"Logging in to {WP_LOGIN}...")
        await self.page.goto(WP_LOGIN, wait_until='domcontentloaded', timeout=30000)

        # Wait for login form
        await self.page.wait_for_selector('#user_login', timeout=10000)

        # Fill login form
        await self.page.fill('#user_login', USERNAME)
        await self.page.fill('#user_pass', PASSWORD)

        # Click login and wait for navigation
        await self.page.click('#wp-submit')

        # Wait for either dashboard or admin page
        try:
            await self.page.wait_for_url(f"{WP_ADMIN}/**", timeout=30000)
            print("Successfully logged in!")
        except Exception as e:
            # Check if we're already logged in by looking for admin bar
            current_url = self.page.url
            print(f"Current URL after login: {current_url}")
            if WP_ADMIN in current_url or await self.page.query_selector('#wpadminbar'):
                print("Successfully logged in (already on admin page)!")
            else:
                raise e

    async def get_rest_nonce(self):
        """Get WordPress REST API nonce from admin page"""
        print("Getting REST API nonce...")
        await self.page.goto(f"{WP_ADMIN}/index.php")

        # Extract nonce from wpApiSettings
        nonce = await self.page.evaluate("""
            () => {
                if (typeof wpApiSettings !== 'undefined' && wpApiSettings.nonce) {
                    return wpApiSettings.nonce;
                }
                return null;
            }
        """)

        if nonce:
            self.nonce = nonce
            print(f"Got REST nonce: {nonce[:20]}...")
        else:
            print("Warning: Could not get REST nonce, will use admin cookies")

        return nonce

    async def check_woocommerce_shop_page(self):
        """Check which page is set as WooCommerce shop page"""
        print("\nChecking WooCommerce shop page setting...")
        await self.page.goto(f"{WP_ADMIN}/admin.php?page=wc-settings&tab=products")
        await self.page.wait_for_load_state('networkidle')

        # Try to find the shop page dropdown
        try:
            shop_page_select = await self.page.query_selector('select[name="woocommerce_shop_page_id"]')
            if shop_page_select:
                selected_value = await shop_page_select.evaluate('el => el.value')
                print(f"Current WooCommerce shop page ID: {selected_value}")
                return selected_value
        except Exception as e:
            print(f"Could not find shop page setting: {e}")

        return None

    async def fix_shop_page_setting(self):
        """Fix WooCommerce shop page to point to page 687"""
        print("\n=== FIXING SHOP PAGE ===")

        # First check current setting
        current_shop_id = await self.check_woocommerce_shop_page()

        if current_shop_id == "687":
            print("Shop page is already set to 687, checking navigation menu...")
        else:
            print(f"Shop page is set to {current_shop_id}, changing to 687...")
            await self.page.goto(f"{WP_ADMIN}/admin.php?page=wc-settings&tab=products")
            await self.page.wait_for_load_state('networkidle')

            # Change the shop page
            await self.page.select_option('select[name="woocommerce_shop_page_id"]', '687')

            # Save changes
            await self.page.click('button[name="save"]')
            await self.page.wait_for_load_state('networkidle')
            print("Shop page setting updated to page 687")

        # Now check and fix navigation menu
        await self.fix_navigation_menu()

    async def fix_navigation_menu(self):
        """Fix navigation menu to point Shop link to correct page"""
        print("\nFixing navigation menu...")
        await self.page.goto(f"{WP_ADMIN}/nav-menus.php")
        await self.page.wait_for_load_state('networkidle')

        # Find all menu items
        try:
            # Look for the Shop menu item
            menu_items = await self.page.query_selector_all('.menu-item')

            for item in menu_items:
                title = await item.query_selector('.menu-item-title')
                if title:
                    text = await title.inner_text()
                    if 'Shop' in text or 'متجر' in text:
                        print(f"Found menu item: {text}")

                        # Click to expand
                        edit_button = await item.query_selector('.item-edit')
                        if edit_button:
                            await edit_button.click()
                            await asyncio.sleep(1)

                            # Find the URL/page input
                            url_input = await item.query_selector('input.edit-menu-item-url')
                            page_select = await item.query_selector('select.select-menu-item-object-id')

                            if page_select:
                                # It's a page link, update to page 687
                                await page_select.select_option('687')
                                print("Updated menu item to page 687")
                            elif url_input:
                                current_url = await url_input.input_value()
                                print(f"Current URL: {current_url}")
                                if '2758' in current_url:
                                    new_url = f"{WP_URL}/shop"
                                    await url_input.fill(new_url)
                                    print(f"Updated URL to: {new_url}")

            # Save menu
            await self.page.click('#save_menu_header')
            await self.page.wait_for_load_state('networkidle')
            print("Navigation menu saved")

        except Exception as e:
            print(f"Error fixing navigation menu: {e}")

    async def fix_footer_content(self):
        """Fix footer placeholder content"""
        print("\n=== FIXING FOOTER CONTENT ===")

        # Get REST nonce if not already obtained
        if not self.nonce:
            await self.get_rest_nonce()

        # Try to find and edit footer via Elementor or Customizer
        await self.page.goto(f"{WP_ADMIN}/customize.php")
        await self.page.wait_for_load_state('networkidle')
        await asyncio.sleep(3)

        try:
            # Look for footer settings in customizer
            # This will vary based on theme, so we'll try common patterns

            # First, try to find contact email
            email_inputs = await self.page.query_selector_all('input[type="email"], input[value*="contact@mysite.com"]')
            for email_input in email_inputs:
                value = await email_input.input_value()
                if 'contact@mysite.com' in value:
                    await email_input.fill('support@neogen.store')
                    print("Updated email to support@neogen.store")

            # Try to find phone number
            phone_inputs = await self.page.query_selector_all('input[type="tel"], input[value*="123-456-7890"]')
            for phone_input in phone_inputs:
                value = await phone_input.input_value()
                if '123-456-7890' in value:
                    await phone_input.fill('+966 50 000 0000')  # Placeholder Saudi number
                    print("Updated phone number")

            # Look for text areas with placeholder content
            textareas = await self.page.query_selector_all('textarea')
            for textarea in textareas:
                value = await textarea.input_value()
                if 'contact@mysite.com' in value:
                    new_value = value.replace('contact@mysite.com', 'support@neogen.store')
                    await textarea.fill(new_value)
                    print("Updated email in textarea")
                if '123-456-7890' in value:
                    new_value = value.replace('123-456-7890', '+966 50 000 0000')
                    await textarea.fill(new_value)
                    print("Updated phone in textarea")

            # Try to publish changes
            publish_button = await self.page.query_selector('#save')
            if publish_button:
                await publish_button.click()
                await self.page.wait_for_load_state('networkidle')
                print("Footer changes published")

        except Exception as e:
            print(f"Error updating footer via customizer: {e}")
            print("Will try alternative method...")

        # Alternative: Try to find footer in theme editor or widgets
        await self.page.goto(f"{WP_ADMIN}/widgets.php")
        await self.page.wait_for_load_state('networkidle')
        await asyncio.sleep(2)

        try:
            # Look for footer widget areas
            footer_widgets = await self.page.query_selector_all('[id*="footer"]')
            for widget in footer_widgets:
                # Check if widget contains placeholder content
                content = await widget.inner_text()
                if 'contact@mysite.com' in content or '123-456-7890' in content:
                    print(f"Found footer widget with placeholder content")
                    # Try to click edit button
                    edit_button = await widget.query_selector('.widget-control-edit')
                    if edit_button:
                        await edit_button.click()
                        await asyncio.sleep(1)

                        # Find and update text fields
                        inputs = await widget.query_selector_all('input[type="text"], textarea')
                        for input_field in inputs:
                            value = await input_field.input_value()
                            if 'contact@mysite.com' in value:
                                new_value = value.replace('contact@mysite.com', 'support@neogen.store')
                                await input_field.fill(new_value)
                            if '123-456-7890' in value:
                                new_value = value.replace('123-456-7890', '+966 50 000 0000')
                                await input_field.fill(new_value)

                        # Save widget
                        save_button = await widget.query_selector('.widget-control-save')
                        if save_button:
                            await save_button.click()
                            await asyncio.sleep(1)
        except Exception as e:
            print(f"Error updating footer widgets: {e}")

    async def take_screenshots(self):
        """Take verification screenshots"""
        print("\n=== TAKING SCREENSHOTS ===")

        # Ensure screenshots directory exists
        Path(SCREENSHOTS_DIR).mkdir(parents=True, exist_ok=True)

        # 1. Homepage - scroll through sections
        print("Taking homepage screenshots...")
        await self.page.goto(WP_URL, wait_until='networkidle')
        await asyncio.sleep(2)

        # Full page screenshot
        await self.page.screenshot(path=f"{SCREENSHOTS_DIR}/homepage_full.png", full_page=True)
        print(f"Saved: {SCREENSHOTS_DIR}/homepage_full.png")

        # Viewport screenshots while scrolling
        await self.page.screenshot(path=f"{SCREENSHOTS_DIR}/homepage_top.png")

        # Scroll and capture sections
        await self.page.evaluate("window.scrollTo(0, document.body.scrollHeight / 3)")
        await asyncio.sleep(1)
        await self.page.screenshot(path=f"{SCREENSHOTS_DIR}/homepage_middle.png")

        await self.page.evaluate("window.scrollTo(0, document.body.scrollHeight * 2 / 3)")
        await asyncio.sleep(1)
        await self.page.screenshot(path=f"{SCREENSHOTS_DIR}/homepage_lower.png")

        await self.page.evaluate("window.scrollTo(0, document.body.scrollHeight)")
        await asyncio.sleep(1)
        await self.page.screenshot(path=f"{SCREENSHOTS_DIR}/homepage_footer.png")
        print("Homepage screenshots complete")

        # 2. Shop page
        print("\nTaking shop page screenshots...")
        await self.page.goto(f"{WP_URL}/shop", wait_until='networkidle')
        await asyncio.sleep(2)

        await self.page.screenshot(path=f"{SCREENSHOTS_DIR}/shop_page_full.png", full_page=True)
        await self.page.screenshot(path=f"{SCREENSHOTS_DIR}/shop_page_top.png")
        print(f"Saved: {SCREENSHOTS_DIR}/shop_page_full.png")
        print(f"Saved: {SCREENSHOTS_DIR}/shop_page_top.png")

        # 3. Product page - try to find and visit first product
        print("\nTaking product page screenshot...")
        try:
            # Find first product link
            product_link = await self.page.query_selector('.woocommerce-loop-product__link, .product a, a.product')
            if product_link:
                await product_link.click()
                await self.page.wait_for_load_state('networkidle')
                await asyncio.sleep(2)

                await self.page.screenshot(path=f"{SCREENSHOTS_DIR}/product_page_full.png", full_page=True)
                await self.page.screenshot(path=f"{SCREENSHOTS_DIR}/product_page_top.png")
                print(f"Saved: {SCREENSHOTS_DIR}/product_page_full.png")
                print(f"Saved: {SCREENSHOTS_DIR}/product_page_top.png")
            else:
                print("Could not find product link on shop page")
        except Exception as e:
            print(f"Error capturing product page: {e}")

        print("\nAll screenshots saved!")

    async def verify_fixes(self):
        """Verify that fixes were applied correctly"""
        print("\n=== VERIFICATION ===")

        # Check shop page
        await self.page.goto(f"{WP_URL}/shop", wait_until='networkidle')
        await asyncio.sleep(2)

        # Check if it shows products
        products = await self.page.query_selector_all('.product, .woocommerce-loop-product')
        print(f"Shop page shows {len(products)} products")

        # Check footer
        await self.page.goto(WP_URL, wait_until='networkidle')
        footer_content = await self.page.query_selector('footer')
        if footer_content:
            footer_text = await footer_content.inner_text()
            has_placeholder_email = 'contact@mysite.com' in footer_text
            has_placeholder_phone = '123-456-7890' in footer_text
            has_real_email = 'support@neogen.store' in footer_text

            print(f"Footer contains placeholder email: {has_placeholder_email}")
            print(f"Footer contains placeholder phone: {has_placeholder_phone}")
            print(f"Footer contains real email: {has_real_email}")

            if has_placeholder_email or has_placeholder_phone:
                print("WARNING: Footer still contains placeholder content!")
                print("You may need to edit footer via Elementor or theme options")

    async def cleanup(self):
        """Close browser"""
        if self.browser:
            await self.browser.close()

async def main():
    """Main execution flow"""
    print("=" * 60)
    print("WordPress Site Fixer for neogen.store")
    print("=" * 60)

    async with async_playwright() as p:
        fixer = WordPressFixer()

        try:
            await fixer.setup(p)
            await fixer.login()

            # Fix shop page
            await fixer.fix_shop_page_setting()

            # Fix footer content
            await fixer.fix_footer_content()

            # Take screenshots
            await fixer.take_screenshots()

            # Verify fixes
            await fixer.verify_fixes()

            print("\n" + "=" * 60)
            print("FIXES COMPLETED!")
            print("=" * 60)
            print("\nSummary:")
            print("1. Shop page navigation checked and fixed")
            print("2. Footer content updated (verify manually if needed)")
            print("3. Screenshots saved to:", SCREENSHOTS_DIR)
            print("\nNext steps:")
            print("- Review screenshots to verify changes")
            print("- If footer still has placeholders, edit via Elementor")
            print("- Test shop navigation from main menu")

        except Exception as e:
            print(f"\nERROR: {e}")
            import traceback
            traceback.print_exc()
        finally:
            await fixer.cleanup()

if __name__ == "__main__":
    asyncio.run(main())
