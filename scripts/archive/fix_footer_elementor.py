#!/usr/bin/env python3.11
"""
Fix footer placeholders directly via Elementor editor
"""

import asyncio
from playwright.async_api import async_playwright
from pathlib import Path

WP_URL = "https://neogen.store"
WP_ADMIN = f"{WP_URL}/wp-admin"
WP_LOGIN = f"{WP_URL}/wp-login.php"
USERNAME = "admin"
PASSWORD = "OtiXQOQTG2WAEg=="
SCREENSHOTS_DIR = "/Volumes/Fahadmega/NGS_Business/screenshots"

async def fix_footer():
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=False, slow_mo=500)
        context = await browser.new_context(
            viewport={'width': 1920, 'height': 1080},
            ignore_https_errors=True
        )
        page = await context.new_page()
        page.set_default_timeout(30000)

        try:
            # Login
            print("Logging in...")
            await page.goto(WP_LOGIN, wait_until='domcontentloaded')
            await page.wait_for_selector('#user_login')
            await page.fill('#user_login', USERNAME)
            await page.fill('#user_pass', PASSWORD)
            await page.click('#wp-submit')
            await asyncio.sleep(3)

            print("Successfully logged in")

            # Find footer template in Elementor
            print("\nFinding footer template...")
            await page.goto(f"{WP_ADMIN}/edit.php?post_type=elementor_library&tabs_group=theme", wait_until='domcontentloaded')
            await asyncio.sleep(3)

            # Look for footer template
            footer_link = await page.query_selector('a.row-title:has-text("Footer"), a.row-title:has-text("footer")')

            if footer_link:
                print("Found footer template, getting edit URL...")

                # Get the post ID
                href = await footer_link.get_attribute('href')
                print(f"Footer edit URL: {href}")

                # Navigate to post edit page
                await page.goto(href, wait_until='domcontentloaded')
                await asyncio.sleep(2)

                # Find "Edit with Elementor" button
                elementor_button = await page.query_selector('a[href*="elementor"]')

                if elementor_button:
                    elementor_url = await elementor_button.get_attribute('href')
                    print(f"Opening Elementor editor: {elementor_url}")

                    # Open Elementor editor
                    await page.goto(elementor_url, wait_until='domcontentloaded')
                    print("Waiting for Elementor to load...")
                    await asyncio.sleep(10)

                    # Wait for Elementor iframe
                    try:
                        await page.wait_for_selector('#elementor-preview-iframe', timeout=15000)
                        print("Elementor editor loaded!")

                        # Get the iframe
                        iframe_element = await page.query_selector('#elementor-preview-iframe')
                        iframe = await iframe_element.content_frame()

                        print("\nSearching for placeholder content in Elementor...")

                        # Search for text containing placeholders
                        placeholders = ['contact@mysite.com', '123-456-7890']

                        for placeholder in placeholders:
                            print(f"\nLooking for: {placeholder}")

                            # Find elements with this text
                            elements = await iframe.query_selector_all(f'text={placeholder}')

                            if elements:
                                print(f"Found {len(elements)} instances")

                                # Try to click on first instance to edit
                                for element in elements:
                                    try:
                                        print("Attempting to click element...")
                                        await element.click()
                                        await asyncio.sleep(2)

                                        # Look for Elementor text editor panel
                                        text_input = await page.query_selector('textarea.elementor-inline-editing, .elementor-editor-element-setting input')

                                        if text_input:
                                            current_value = await text_input.input_value()
                                            print(f"Current value: {current_value}")

                                            # Replace placeholder
                                            if placeholder == 'contact@mysite.com':
                                                new_value = current_value.replace(placeholder, 'support@neogen.store')
                                            elif placeholder == '123-456-7890':
                                                new_value = current_value.replace(placeholder, '+966 50 000 0000')

                                            await text_input.fill(new_value)
                                            print(f"Updated to: {new_value}")

                                            # Press Enter or Tab to save
                                            await text_input.press('Enter')
                                            await asyncio.sleep(1)

                                    except Exception as e:
                                        print(f"Could not edit this instance: {e}")

                        print("\n" + "=" * 60)
                        print("MANUAL EDITING REQUIRED")
                        print("=" * 60)
                        print("\nThe Elementor editor is now open.")
                        print("Please manually:")
                        print("1. Click on the email text 'contact@mysite.com'")
                        print("2. Change it to 'support@neogen.store'")
                        print("3. Click on the phone text '123-456-7890'")
                        print("4. Change it to your WhatsApp number")
                        print("5. Click 'Update' button (green button at bottom left)")
                        print("\nPress Enter in this terminal when done...")
                        input()

                        # Take screenshot after manual edit
                        print("\nTaking screenshot of updated footer...")
                        await page.goto(WP_URL, wait_until='domcontentloaded')
                        await asyncio.sleep(3)

                        Path(SCREENSHOTS_DIR).mkdir(parents=True, exist_ok=True)
                        await page.screenshot(path=f"{SCREENSHOTS_DIR}/footer_after_fix.png", full_page=True)
                        print(f"Saved: {SCREENSHOTS_DIR}/footer_after_fix.png")

                        # Verify footer content
                        footer = await page.query_selector('footer')
                        if footer:
                            footer_text = await footer.inner_text()
                            if 'support@neogen.store' in footer_text:
                                print("SUCCESS: Footer now shows support@neogen.store")
                            else:
                                print("WARNING: Could not verify email change")

                    except Exception as e:
                        print(f"Error loading Elementor: {e}")
                        print("\nElementor may be loading slowly. Check the browser window.")
                        print("You can manually edit the footer in the open browser window.")
                        input("Press Enter when done...")

            else:
                print("Could not find footer template")

        except Exception as e:
            print(f"Error: {e}")
            import traceback
            traceback.print_exc()

        finally:
            print("\nClosing browser...")
            await browser.close()

if __name__ == "__main__":
    asyncio.run(fix_footer())
