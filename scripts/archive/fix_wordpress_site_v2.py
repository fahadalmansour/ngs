#!/usr/bin/env python3.11
"""
WordPress Site Fixer v2 for neogen.store
Simplified version with better error handling and focus on achievable fixes
"""

import asyncio
import os
from playwright.async_api import async_playwright, Page
from pathlib import Path
import json

# Configuration
WP_URL = "https://neogen.store"
WP_ADMIN = f"{WP_URL}/wp-admin"
WP_LOGIN = f"{WP_URL}/wp-login.php"
USERNAME = "admin"
PASSWORD = "OtiXQOQTG2WAEg=="
SCREENSHOTS_DIR = "/Volumes/Fahadmega/NGS_Business/screenshots"

class WordPressFixer:
    def __init__(self):
        self.page = None
        self.context = None
        self.browser = None
        self.fixes_applied = []
        self.screenshots_taken = []

    async def setup(self, playwright):
        """Initialize browser and context"""
        print("Setting up browser...")
        self.browser = await playwright.chromium.launch(
            headless=False,
            slow_mo=300
        )
        self.context = await self.browser.new_context(
            viewport={'width': 1920, 'height': 1080},
            user_agent='Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36',
            ignore_https_errors=True
        )
        self.page = await self.context.new_page()
        self.page.set_default_timeout(30000)

    async def login(self):
        """Login to WordPress admin"""
        print(f"Logging in to {WP_LOGIN}...")
        await self.page.goto(WP_LOGIN, wait_until='domcontentloaded', timeout=30000)
        await self.page.wait_for_selector('#user_login', timeout=10000)

        await self.page.fill('#user_login', USERNAME)
        await self.page.fill('#user_pass', PASSWORD)
        await self.page.click('#wp-submit')

        try:
            await self.page.wait_for_url(f"{WP_ADMIN}/**", timeout=30000)
        except:
            pass

        if WP_ADMIN in self.page.url or await self.page.query_selector('#wpadminbar'):
            print("Successfully logged in!")
        else:
            raise Exception("Login failed")

    async def check_shop_page_status(self):
        """Check current shop page configuration"""
        print("\n=== CHECKING SHOP PAGE STATUS ===")

        try:
            # Check WooCommerce setting
            await self.page.goto(f"{WP_ADMIN}/admin.php?page=wc-settings&tab=products", wait_until='domcontentloaded')
            await asyncio.sleep(3)

            shop_page_select = await self.page.query_selector('select[name="woocommerce_shop_page_id"]')
            if shop_page_select:
                selected_value = await shop_page_select.evaluate('el => el.value')
                print(f"WooCommerce shop page ID: {selected_value}")

                if selected_value != "687":
                    print(f"Changing shop page from {selected_value} to 687...")
                    await shop_page_select.select_option('687')
                    await self.page.click('button[name="save"]')
                    await asyncio.sleep(3)
                    self.fixes_applied.append("Changed WooCommerce shop page to 687")
                else:
                    print("Shop page already correctly set to 687")
                    self.fixes_applied.append("Shop page already correct (687)")

        except Exception as e:
            print(f"Error checking shop page: {e}")

        # Verify shop page works
        try:
            await self.page.goto(f"{WP_URL}/shop", wait_until='domcontentloaded')
            await asyncio.sleep(3)

            products = await self.page.query_selector_all('.product, .type-product')
            print(f"Shop page displays {len(products)} products")

            if len(products) > 0:
                self.fixes_applied.append(f"Shop page verified - showing {len(products)} products")
        except Exception as e:
            print(f"Error verifying shop page: {e}")

    async def check_navigation_menu(self):
        """Check and document navigation menu status"""
        print("\n=== CHECKING NAVIGATION MENU ===")

        try:
            await self.page.goto(WP_URL, wait_until='domcontentloaded')
            await asyncio.sleep(2)

            # Find shop link in menu
            shop_links = await self.page.query_selector_all('a[href*="shop"], nav a')

            for link in shop_links:
                text = await link.inner_text()
                href = await link.get_attribute('href')
                if 'shop' in text.lower() or 'متجر' in text:
                    print(f"Shop menu link: {text} -> {href}")

                    if '2758' in href:
                        print("WARNING: Shop link still points to page 2758")
                        self.fixes_applied.append("NEEDS FIX: Shop menu points to page 2758")
                    elif '/shop' in href or '687' in href:
                        print("Shop link correctly points to shop page")
                        self.fixes_applied.append("Shop menu navigation is correct")

        except Exception as e:
            print(f"Error checking navigation: {e}")

    async def check_footer_content(self):
        """Check footer for placeholder content"""
        print("\n=== CHECKING FOOTER CONTENT ===")

        try:
            await self.page.goto(WP_URL, wait_until='domcontentloaded')
            await asyncio.sleep(2)

            footer = await self.page.query_selector('footer')
            if footer:
                footer_text = await footer.inner_text()

                issues = []
                if 'contact@mysite.com' in footer_text:
                    issues.append("Placeholder email: contact@mysite.com")
                if '123-456-7890' in footer_text:
                    issues.append("Placeholder phone: 123-456-7890")

                if issues:
                    print("Footer contains placeholder content:")
                    for issue in issues:
                        print(f"  - {issue}")
                    self.fixes_applied.append(f"NEEDS FIX: Footer has {len(issues)} placeholders")
                else:
                    print("Footer content looks good (no obvious placeholders)")
                    self.fixes_applied.append("Footer content verified")

                # Check for actual contact info
                if 'support@neogen.store' in footer_text:
                    print("Found correct email: support@neogen.store")
                if 'neogen.store' in footer_text.lower():
                    print("Footer mentions neogen.store")

        except Exception as e:
            print(f"Error checking footer: {e}")

    async def fix_footer_via_database(self):
        """Attempt to fix footer content via direct database approach"""
        print("\n=== ATTEMPTING FOOTER FIX VIA ELEMENTOR ===")

        try:
            # Try to find Elementor templates
            await self.page.goto(f"{WP_ADMIN}/edit.php?post_type=elementor_library", wait_until='domcontentloaded')
            await asyncio.sleep(3)

            # Look for footer template
            rows = await self.page.query_selector_all('.row-title')
            for row in rows:
                text = await row.inner_text()
                if 'footer' in text.lower():
                    print(f"Found template: {text}")
                    href = await row.get_attribute('href')

                    # Try to edit with Elementor
                    await self.page.goto(href, wait_until='domcontentloaded')
                    await asyncio.sleep(2)

                    # Look for "Edit with Elementor" button
                    edit_button = await self.page.query_selector('.elementor-edit-template, a[href*="elementor"]')
                    if edit_button:
                        print("Found Elementor edit button")
                        self.fixes_applied.append("Located footer template - needs manual Elementor edit")
                        break

        except Exception as e:
            print(f"Could not access Elementor templates: {e}")
            self.fixes_applied.append("Footer needs manual edit via Elementor or Theme Customizer")

    async def take_screenshots(self):
        """Take verification screenshots"""
        print("\n=== TAKING SCREENSHOTS ===")

        Path(SCREENSHOTS_DIR).mkdir(parents=True, exist_ok=True)

        screenshots = [
            (f"{WP_URL}", "homepage", True),
            (f"{WP_URL}/shop", "shop_page", True),
        ]

        for url, name, scroll in screenshots:
            try:
                print(f"Capturing {name}...")
                await self.page.goto(url, wait_until='domcontentloaded', timeout=30000)
                await asyncio.sleep(3)

                # Full page screenshot
                full_path = f"{SCREENSHOTS_DIR}/{name}_full.png"
                await self.page.screenshot(path=full_path, full_page=True)
                self.screenshots_taken.append(full_path)
                print(f"  Saved: {full_path}")

                # Viewport screenshot
                viewport_path = f"{SCREENSHOTS_DIR}/{name}_viewport.png"
                await self.page.screenshot(path=viewport_path)
                self.screenshots_taken.append(viewport_path)
                print(f"  Saved: {viewport_path}")

                if scroll and name == "homepage":
                    # Footer screenshot
                    await self.page.evaluate("window.scrollTo(0, document.body.scrollHeight)")
                    await asyncio.sleep(1)
                    footer_path = f"{SCREENSHOTS_DIR}/{name}_footer.png"
                    await self.page.screenshot(path=footer_path)
                    self.screenshots_taken.append(footer_path)
                    print(f"  Saved: {footer_path}")

            except Exception as e:
                print(f"  Error capturing {name}: {e}")

        # Try to capture a product page
        try:
            print("Capturing product page...")
            await self.page.goto(f"{WP_URL}/shop", wait_until='domcontentloaded')
            await asyncio.sleep(2)

            product_link = await self.page.query_selector('.woocommerce-LoopProduct-link, .product a, a.woocommerce-loop-product__link')
            if product_link:
                await product_link.click()
                await asyncio.sleep(3)

                product_path = f"{SCREENSHOTS_DIR}/product_page_full.png"
                await self.page.screenshot(path=product_path, full_page=True)
                self.screenshots_taken.append(product_path)
                print(f"  Saved: {product_path}")

                viewport_product_path = f"{SCREENSHOTS_DIR}/product_page_viewport.png"
                await self.page.screenshot(path=viewport_product_path)
                self.screenshots_taken.append(viewport_product_path)
                print(f"  Saved: {viewport_product_path}")

        except Exception as e:
            print(f"  Could not capture product page: {e}")

    async def generate_report(self):
        """Generate a summary report"""
        print("\n" + "=" * 70)
        print("FIXES & VERIFICATION REPORT")
        print("=" * 70)

        print("\nFixes Applied / Status Checked:")
        for i, fix in enumerate(self.fixes_applied, 1):
            print(f"{i}. {fix}")

        print(f"\nScreenshots Taken ({len(self.screenshots_taken)}):")
        for screenshot in self.screenshots_taken:
            print(f"  - {screenshot}")

        print("\n" + "=" * 70)
        print("NEXT STEPS:")
        print("=" * 70)

        needs_attention = [f for f in self.fixes_applied if "NEEDS FIX" in f or "manual" in f.lower()]
        if needs_attention:
            print("\nItems requiring manual attention:")
            for item in needs_attention:
                print(f"  ! {item}")

        print("\nRecommendations:")
        print("1. Review screenshots in:", SCREENSHOTS_DIR)
        print("2. Test shop navigation from main menu")
        print("3. If footer has placeholders, edit via:")
        print("   - Appearance > Customize")
        print("   - Elementor > Templates > Footer")
        print("   - Theme Settings")

    async def cleanup(self):
        """Close browser"""
        if self.browser:
            await self.browser.close()

async def main():
    """Main execution flow"""
    print("=" * 70)
    print("WordPress Site Fixer v2 for neogen.store")
    print("=" * 70)

    async with async_playwright() as p:
        fixer = WordPressFixer()

        try:
            await fixer.setup(p)
            await fixer.login()

            # Check and fix shop page
            await fixer.check_shop_page_status()

            # Check navigation menu
            await fixer.check_navigation_menu()

            # Check footer content
            await fixer.check_footer_content()

            # Try to fix footer
            await fixer.fix_footer_via_database()

            # Take screenshots
            await fixer.take_screenshots()

            # Generate report
            await fixer.generate_report()

        except Exception as e:
            print(f"\nERROR: {e}")
            import traceback
            traceback.print_exc()
        finally:
            await fixer.cleanup()

if __name__ == "__main__":
    asyncio.run(main())
