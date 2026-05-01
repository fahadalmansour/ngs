#!/usr/bin/env python3.11
"""
Fix footer by triggering Elementor internal save to regenerate HTML.
The _elementor_data was already updated - we just need to re-save the template.
"""

import asyncio
import os
import sys

try:
    from playwright.async_api import async_playwright
except ImportError:
    print("ERROR: playwright required")
    sys.exit(1)


def _load_env():
    for env_path in [
        os.path.join(os.path.dirname(__file__), ".env"),
        os.path.join(os.path.dirname(__file__), "..", ".env"),
    ]:
        if os.path.exists(env_path):
            with open(env_path) as f:
                for line in f:
                    line = line.strip()
                    if line and not line.startswith("#") and "=" in line:
                        key, _, value = line.partition("=")
                        os.environ.setdefault(key.strip(), value.strip())


_load_env()

WP_URL = os.environ.get("WP_URL", "https://neogen.store")
USERNAME = os.environ.get("WP_ADMIN_USER", "")
PASSWORD = os.environ.get("WP_ADMIN_PASSWORD", "")
POST_ID = 2681
OLD_PHONE = "+966 55 123 4567"
NEW_PHONE = "+966570131122"

SCREENSHOTS_DIR = os.path.join(os.path.dirname(__file__), "..", "screenshots")
os.makedirs(SCREENSHOTS_DIR, exist_ok=True)


async def main():
    print("=== Footer Fix v4 - Elementor Internal Save ===")
    print(f"Post: #{POST_ID}")
    print()

    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        ctx = await browser.new_context(
            viewport={"width": 1920, "height": 1080},
            ignore_https_errors=True,
        )
        page = await ctx.new_page()
        page.set_default_timeout(60000)

        # Login
        print("1. Logging in...")
        await page.goto(f"{WP_URL}/wp-login.php", wait_until="domcontentloaded")
        await page.wait_for_selector("#user_login")
        await page.fill("#user_login", USERNAME)
        await page.fill("#user_pass", PASSWORD)
        await page.click("#wp-submit")
        try:
            await page.wait_for_url(f"**{WP_URL}/wp-admin/**", timeout=30000)
        except Exception:
            if "/wp-admin" not in page.url:
                print(f"   Failed: {page.url}")
                await browser.close()
                return
        print("   OK")

        # Open Elementor editor
        print("2. Opening Elementor editor...")
        url = f"{WP_URL}/wp-admin/post.php?post={POST_ID}&action=elementor"
        await page.goto(url, wait_until="domcontentloaded")

        # Wait for Elementor to fully initialize
        print("   Waiting for Elementor to load...")
        loaded = False
        for attempt in range(20):
            await page.wait_for_timeout(2000)
            check = await page.evaluate("""
                () => {
                    if (typeof elementor === 'undefined') return 'no_elementor';
                    if (!elementor.documents) return 'no_documents';
                    const doc = elementor.documents.getCurrent();
                    if (!doc) return 'no_current_doc';
                    return 'ready';
                }
            """)
            print(f"   Attempt {attempt + 1}: {check}")
            if check == "ready":
                loaded = True
                break

        if not loaded:
            print("   Elementor didn't fully load. Taking screenshot...")
            await page.screenshot(path=os.path.join(SCREENSHOTS_DIR, "elementor_not_loaded.png"))
            # Try a different approach - use Elementor's AJAX API directly
            print("   Trying direct AJAX approach...")

        # Get Elementor nonces
        print("3. Getting Elementor nonces...")
        nonces = await page.evaluate("""
            () => {
                const result = {};
                // Standard WP nonce
                if (typeof wpApiSettings !== 'undefined') {
                    result.wpNonce = wpApiSettings.nonce;
                }
                // Elementor nonces
                if (typeof elementor !== 'undefined') {
                    result.elementorNonce = elementor.config?.nonce;
                    result.ajaxNonce = elementor.config?.document?.nonce;
                }
                // Also check for elementorCommon
                if (typeof elementorCommon !== 'undefined') {
                    result.commonNonce = elementorCommon.config?.ajax?.nonce;
                }
                // Check jQuery nonce
                if (typeof elementorFrontendConfig !== 'undefined') {
                    result.frontendNonce = elementorFrontendConfig.nonce;
                }
                return result;
            }
        """)
        print(f"   Nonces: {nonces}")

        # Try to save using Elementor's internal AJAX
        print("4. Triggering Elementor save via AJAX...")

        # Approach A: Use the Elementor save endpoint directly
        save_result = await page.evaluate("""
            async (params) => {
                const { postId, nonces } = params;

                // Get the _elementor_data
                const wpNonce = nonces.wpNonce;
                const resp = await fetch(
                    `/wp-json/wp/v2/elementor_library/${postId}?context=edit`,
                    { headers: { 'X-WP-Nonce': wpNonce } }
                );
                if (!resp.ok) return { error: `fetch: ${resp.status}` };
                const post = await resp.json();
                const eleData = post.meta?._elementor_data;

                if (!eleData) return { error: 'no _elementor_data in meta' };

                // The _elementor_data should already have the new phone
                // We need to POST it back to trigger HTML regeneration
                // Use Elementor's save_builder AJAX action

                const ajaxNonce = nonces.commonNonce || nonces.elementorNonce;
                if (!ajaxNonce) return { error: 'no elementor nonce' };

                // Build the save request as Elementor does internally
                const formData = new FormData();
                formData.append('action', 'elementor_ajax');
                formData.append('editor_post_id', postId);
                formData.append('_nonce', ajaxNonce);

                // The save action
                const actions = {
                    save_builder: {
                        action: 'save_builder',
                        data: {
                            status: 'publish',
                            elements: typeof eleData === 'string' ? eleData : JSON.stringify(eleData)
                        }
                    }
                };
                formData.append('actions', JSON.stringify(actions));

                const saveResp = await fetch('/wp-admin/admin-ajax.php', {
                    method: 'POST',
                    body: formData
                });

                const saveText = await saveResp.text();
                return {
                    status: saveResp.status,
                    ok: saveResp.ok,
                    response: saveText.substring(0, 300)
                };
            }
        """, {"postId": POST_ID, "nonces": nonces})
        print(f"   Save result: {save_result}")

        # Wait for save to process
        await page.wait_for_timeout(3000)

        # Verify
        print("5. Verifying on frontend...")
        vp = await ctx.new_page()
        await vp.goto(f"{WP_URL}/?t={os.getpid()}", wait_until="domcontentloaded")
        await vp.wait_for_timeout(3000)
        await vp.evaluate("window.scrollTo(0, document.body.scrollHeight)")
        await vp.wait_for_timeout(2000)

        html = await vp.content()
        await vp.screenshot(path=os.path.join(SCREENSHOTS_DIR, "footer_v4.png"))

        if NEW_PHONE in html:
            print(f"   SUCCESS: Phone updated to {NEW_PHONE}!")
        elif OLD_PHONE in html:
            print(f"   Still showing old phone. Server cache is likely involved.")
            # Let's also check if there's a LiteSpeed Cache or similar
            print("   Checking for cache plugins...")
            await page.goto(f"{WP_URL}/wp-admin/plugins.php", wait_until="domcontentloaded")
            plugins_html = await page.content()
            for cache_name in [
                "litespeed", "wp-super-cache", "w3-total-cache",
                "wp-fastest-cache", "autoptimize", "breeze",
                "hummingbird", "sg-cachepress", "comet-cache",
            ]:
                if cache_name in plugins_html.lower():
                    print(f"   Found cache plugin: {cache_name}")
        else:
            import re
            phones = re.findall(r"\+966[\d\s-]+", html)
            print(f"   Phones: {phones[:5]}")

        await browser.close()
    print("\nDone!")


if __name__ == "__main__":
    asyncio.run(main())
