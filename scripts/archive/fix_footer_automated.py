#!/usr/bin/env python3.11
"""
Automated footer fix using Elementor API and direct database access
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

async def automated_footer_fix():
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=False)
        context = await browser.new_context(
            viewport={'width': 1920, 'height': 1080},
            ignore_https_errors=True
        )
        page = await context.new_page()
        page.set_default_timeout(40000)

        try:
            print("=" * 70)
            print("Automated Footer Fixer")
            print("=" * 70)

            # Login
            print("\nLogging in...")
            await page.goto(WP_LOGIN, wait_until='domcontentloaded')
            await page.wait_for_selector('#user_login')
            await page.fill('#user_login', USERNAME)
            await page.fill('#user_pass', PASSWORD)
            await page.click('#wp-submit')
            await asyncio.sleep(3)
            print("Logged in successfully")

            # Use WordPress AJAX to update footer via post meta
            print("\nFetching footer template...")

            # Get REST API nonce
            await page.goto(f"{WP_ADMIN}/index.php")
            await asyncio.sleep(2)

            nonce = await page.evaluate("""
                () => {
                    if (typeof wpApiSettings !== 'undefined' && wpApiSettings.nonce) {
                        return wpApiSettings.nonce;
                    }
                    return null;
                }
            """)

            if nonce:
                print(f"Got REST nonce: {nonce[:20]}...")

                # Get footer post content
                footer_post_id = "2681"

                print(f"\nFetching footer post {footer_post_id} via REST API...")

                # Use REST API to get post content
                response = await page.evaluate(f"""
                    async () => {{
                        try {{
                            const response = await fetch('{WP_URL}/wp-json/wp/v2/elementor_library/{footer_post_id}', {{
                                headers: {{
                                    'X-WP-Nonce': '{nonce}'
                                }}
                            }});

                            if (response.ok) {{
                                return await response.json();
                            }}
                            return null;
                        }} catch (e) {{
                            return {{ error: e.message }};
                        }}
                    }}
                """)

                if response and 'content' in response:
                    print("Successfully retrieved footer content")

                    content = response['content']['rendered']

                    # Replace placeholder content
                    print("\nReplacing placeholder content...")
                    updated_content = content.replace('contact@mysite.com', 'support@neogen.store')
                    updated_content = updated_content.replace('123-456-7890', '+966 50 000 0000')

                    changes_made = content != updated_content

                    if changes_made:
                        print("Found placeholders, updating...")

                        # Update via REST API
                        # Escape the content properly
                        escaped_content = updated_content.replace('\\', '\\\\').replace('`', '\\`').replace('$', '\\$')

                        update_js = f"""
                            async () => {{
                                try {{
                                    const response = await fetch('{WP_URL}/wp-json/wp/v2/elementor_library/{footer_post_id}', {{
                                        method: 'POST',
                                        headers: {{
                                            'Content-Type': 'application/json',
                                            'X-WP-Nonce': '{nonce}'
                                        }},
                                        body: JSON.stringify({{
                                            content: `{escaped_content}`
                                        }})
                                    }});

                                    if (response.ok) {{
                                        return {{ success: true }};
                                    }}
                                    return {{ success: false, status: response.status }};
                                }} catch (e) {{
                                    return {{ error: e.message }};
                                }}
                            }}
                        """

                        update_response = await page.evaluate(update_js)

                        if update_response and update_response.get('success'):
                            print("✓ Footer updated successfully via REST API")
                        else:
                            print(f"✗ Update failed: {update_response}")
                    else:
                        print("No placeholder content found in footer")

            # Alternative method: Use post editor
            print("\n\nAlternative method: Direct post editing...")
            await page.goto(f"{WP_ADMIN}/post.php?post=2681&action=edit", wait_until='domcontentloaded')
            await asyncio.sleep(5)

            # Try to switch to text editor mode
            try:
                # Click "Code editor" button if visible
                code_editor_button = await page.query_selector('button[aria-label="Code editor"]')
                if code_editor_button:
                    print("Switching to code editor...")
                    await code_editor_button.click()
                    await asyncio.sleep(2)

                # Find the editor textarea
                editor = await page.query_selector('.editor-post-text-editor, textarea[name="content"]')
                if editor:
                    print("Found editor, getting content...")
                    content = await editor.input_value()

                    if 'contact@mysite.com' in content or '123-456-7890' in content:
                        print("Found placeholders in editor")
                        updated = content.replace('contact@mysite.com', 'support@neogen.store')
                        updated = updated.replace('123-456-7890', '+966 50 000 0000')

                        print("Updating content...")
                        await editor.fill(updated)
                        await asyncio.sleep(1)

                        # Click update button
                        update_button = await page.query_selector('.editor-post-publish-button, button:has-text("Update")')
                        if update_button:
                            await update_button.click()
                            print("✓ Clicked update button")
                            await asyncio.sleep(3)

            except Exception as e:
                print(f"Direct editing failed: {e}")

            # Verify changes
            print("\n\nVerifying changes on live site...")
            await page.goto(WP_URL, wait_until='domcontentloaded')
            await asyncio.sleep(3)

            footer = await page.query_selector('footer')
            if footer:
                footer_text = await footer.inner_text()

                print("\nFooter verification:")
                has_old_email = 'contact@mysite.com' in footer_text
                has_new_email = 'support@neogen.store' in footer_text
                has_old_phone = '123-456-7890' in footer_text

                if has_old_email:
                    print("  ✗ Placeholder email still present: contact@mysite.com")
                elif has_new_email:
                    print("  ✓ Email updated: support@neogen.store")
                else:
                    print("  ? Email status unclear")

                if has_old_phone:
                    print("  ✗ Placeholder phone still present: 123-456-7890")
                else:
                    print("  ✓ Phone number appears updated")

                # Take screenshots
                print("\nTaking final screenshots...")
                Path(SCREENSHOTS_DIR).mkdir(parents=True, exist_ok=True)

                await page.evaluate("window.scrollTo(0, document.body.scrollHeight)")
                await asyncio.sleep(2)

                await page.screenshot(path=f"{SCREENSHOTS_DIR}/footer_after_automated_fix.png")
                print(f"Saved: {SCREENSHOTS_DIR}/footer_after_automated_fix.png")

                await page.screenshot(
                    path=f"{SCREENSHOTS_DIR}/homepage_full_after_fix.png",
                    full_page=True
                )
                print(f"Saved: {SCREENSHOTS_DIR}/homepage_full_after_fix.png")

                # Summary
                print("\n" + "=" * 70)
                print("SUMMARY")
                print("=" * 70)

                if not has_old_email and not has_old_phone:
                    print("\n✓ SUCCESS! Footer placeholders have been updated")
                else:
                    print("\n✗ Footer still contains placeholder content")
                    print("\nManual fix required:")
                    print("1. Go to: https://neogen.store/wp-admin/")
                    print("2. Navigate to: Elementor > My Templates > Theme Builder")
                    print("3. Click 'Edit with Elementor' on the Footer template")
                    print("4. Find 'Get In Touch' section")
                    print("5. Update:")
                    print("   - contact@mysite.com → support@neogen.store")
                    print("   - 123-456-7890 → Your WhatsApp number")
                    print("6. Click 'Update' (green button bottom left)")

        except Exception as e:
            print(f"\nError: {e}")
            import traceback
            traceback.print_exc()

        finally:
            print("\nClosing browser...")
            await asyncio.sleep(3)
            await browser.close()

if __name__ == "__main__":
    asyncio.run(automated_footer_fix())
