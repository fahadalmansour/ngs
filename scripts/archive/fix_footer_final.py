#!/usr/bin/env python3.11
"""
Final footer fix using direct Elementor data manipulation
"""

import asyncio
import json
import re
from playwright.async_api import async_playwright
from pathlib import Path

WP_URL = "https://neogen.store"
WP_ADMIN = f"{WP_URL}/wp-admin"
WP_LOGIN = f"{WP_URL}/wp-login.php"
USERNAME = "admin"
PASSWORD = "OtiXQOQTG2WAEg=="
SCREENSHOTS_DIR = "/Volumes/Fahadmega/NGS_Business/screenshots"

async def fix_footer_content():
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=False, slow_mo=300)
        context = await browser.new_context(
            viewport={'width': 1920, 'height': 1080},
            ignore_https_errors=True
        )
        page = await context.new_page()
        page.set_default_timeout(40000)

        try:
            # Login
            print("=" * 70)
            print("Footer Content Fixer - Direct Elementor Edit")
            print("=" * 70)
            print("\nLogging in...")
            await page.goto(WP_LOGIN, wait_until='domcontentloaded')
            await page.wait_for_selector('#user_login')
            await page.fill('#user_login', USERNAME)
            await page.fill('#user_pass', PASSWORD)
            await page.click('#wp-submit')
            await asyncio.sleep(3)
            print("Logged in successfully")

            # Navigate to Elementor templates
            print("\nFinding footer template...")
            await page.goto(f"{WP_ADMIN}/edit.php?post_type=elementor_library&tabs_group=theme", wait_until='domcontentloaded')
            await asyncio.sleep(3)

            # Find footer template and get its ID
            footer_row = await page.query_selector('tr:has-text("Footer")')
            if not footer_row:
                print("ERROR: Could not find footer template")
                return

            # Get post ID from the row
            post_id_match = await footer_row.get_attribute('id')
            if post_id_match:
                post_id = post_id_match.replace('post-', '')
                print(f"Found footer template with ID: {post_id}")

                # Open Elementor editor
                elementor_url = f"{WP_URL}/wp-admin/post.php?post={post_id}&action=elementor"
                print(f"Opening Elementor editor: {elementor_url}")

                await page.goto(elementor_url, wait_until='domcontentloaded', timeout=60000)
                print("Waiting for Elementor to fully load...")
                await asyncio.sleep(15)

                # Wait for Elementor to be ready
                try:
                    await page.wait_for_selector('#elementor-preview-iframe', timeout=30000)
                    print("Elementor editor loaded!")

                    # Get the preview iframe
                    iframe_element = await page.query_selector('#elementor-preview-iframe')
                    iframe = await iframe_element.content_frame()

                    print("\n" + "=" * 70)
                    print("INTERACTIVE EDITING MODE")
                    print("=" * 70)
                    print("\nThe Elementor editor is now open in the browser.")
                    print("\nPlease follow these steps:")
                    print("\n1. In the Elementor editor (visible in browser):")
                    print("   - Look for the 'Get In Touch' section in the footer")
                    print("   - Click on 'contact@mysite.com' text")
                    print("   - Change it to: support@neogen.store")
                    print("   - Click on '123-456-7890' text")
                    print("   - Change it to: +966 50 000 0000 (or your preferred number)")
                    print("\n2. Click the green 'Update' button at the bottom left")
                    print("\n3. Wait for 'Saved' confirmation")
                    print("\n4. Press Enter in this terminal to continue...")

                    input()

                    print("\nVerifying changes...")

                    # Navigate to homepage to check footer
                    await page.goto(WP_URL, wait_until='domcontentloaded')
                    await asyncio.sleep(3)

                    # Check footer content
                    footer = await page.query_selector('footer')
                    if footer:
                        footer_html = await footer.inner_html()
                        footer_text = await footer.inner_text()

                        print("\nFooter verification:")
                        if 'contact@mysite.com' in footer_text:
                            print("  X Placeholder email still present")
                        elif 'support@neogen.store' in footer_text:
                            print("  ✓ Email updated to support@neogen.store")
                        else:
                            print("  ? Email status unclear")

                        if '123-456-7890' in footer_text:
                            print("  X Placeholder phone still present")
                        else:
                            print("  ✓ Phone number updated")

                        # Take final screenshot
                        print("\nTaking final screenshot...")
                        Path(SCREENSHOTS_DIR).mkdir(parents=True, exist_ok=True)

                        await page.evaluate("window.scrollTo(0, document.body.scrollHeight)")
                        await asyncio.sleep(2)

                        await page.screenshot(
                            path=f"{SCREENSHOTS_DIR}/footer_fixed_final.png",
                            full_page=False
                        )
                        print(f"Saved: {SCREENSHOTS_DIR}/footer_fixed_final.png")

                        # Full page screenshot
                        await page.screenshot(
                            path=f"{SCREENSHOTS_DIR}/homepage_after_footer_fix.png",
                            full_page=True
                        )
                        print(f"Saved: {SCREENSHOTS_DIR}/homepage_after_footer_fix.png")

                except Exception as e:
                    print(f"Error with Elementor editor: {e}")
                    print("\nThe browser is still open. You can manually edit the footer.")
                    print("Press Enter when done...")
                    input()

            else:
                print("Could not extract post ID")

            print("\n" + "=" * 70)
            print("SUMMARY")
            print("=" * 70)
            print("\nIf you successfully edited the footer:")
            print("- Check the screenshots in:", SCREENSHOTS_DIR)
            print("- Visit neogen.store to verify live changes")
            print("- Footer should show support@neogen.store")
            print("\nIf editing failed:")
            print("- Try manually: WP Admin > Elementor > Templates > Footer")
            print("- Or: WP Admin > Appearance > Customize")

        except Exception as e:
            print(f"\nError: {e}")
            import traceback
            traceback.print_exc()

        finally:
            print("\nClosing browser in 5 seconds...")
            await asyncio.sleep(5)
            await browser.close()

if __name__ == "__main__":
    asyncio.run(fix_footer_content())
