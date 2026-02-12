#!/usr/bin/env python3.11
"""Check installed plugins, active theme, and existing pages."""

import asyncio
import os
import sys

try:
    from playwright.async_api import async_playwright
except ImportError:
    sys.exit("playwright required")


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

JS_PLUGINS = """
async (nonce) => {
    const resp = await fetch('/wp-json/wp/v2/plugins?per_page=100', {
        headers: { 'X-WP-Nonce': nonce }
    });
    if (!resp.ok) return { error: resp.status };
    const plugins = await resp.json();
    return plugins.map(p => ({
        plugin: p.plugin,
        name: p.name,
        status: p.status,
        version: p.version
    }));
}
"""

JS_THEMES = """
async (nonce) => {
    const resp = await fetch('/wp-json/wp/v2/themes?status=active', {
        headers: { 'X-WP-Nonce': nonce }
    });
    if (!resp.ok) return { error: resp.status };
    const themes = await resp.json();
    return themes.map(t => ({
        name: t.name?.rendered || t.name,
        stylesheet: t.stylesheet,
        version: t.version,
        status: t.status
    }));
}
"""

JS_PAGES = """
async (nonce) => {
    const resp = await fetch('/wp-json/wp/v2/pages?per_page=100&status=any', {
        headers: { 'X-WP-Nonce': nonce }
    });
    if (!resp.ok) return { error: resp.status };
    const pages = await resp.json();
    return pages.map(p => ({
        id: p.id,
        title: p.title?.rendered || p.title?.raw,
        slug: p.slug,
        status: p.status,
        template: p.template
    }));
}
"""


async def main():
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        ctx = await browser.new_context(
            viewport={"width": 1920, "height": 1080}, ignore_https_errors=True
        )
        page = await ctx.new_page()
        page.set_default_timeout(30000)

        # Login
        print("Logging in...")
        await page.goto(f"{WP_URL}/wp-login.php", wait_until="domcontentloaded")
        await page.wait_for_selector("#user_login")
        await page.fill("#user_login", USERNAME)
        await page.fill("#user_pass", PASSWORD)
        await page.click("#wp-submit")
        await page.wait_for_url(f"**{WP_URL}/wp-admin/**", timeout=30000)

        await page.goto(f"{WP_URL}/wp-admin/index.php", wait_until="domcontentloaded")
        nonce = await page.evaluate(
            "() => typeof wpApiSettings !== 'undefined' ? wpApiSettings.nonce : null"
        )

        # Plugins
        print("\n=== Installed Plugins ===")
        plugins = await page.evaluate(JS_PLUGINS, nonce)
        if isinstance(plugins, dict) and "error" in plugins:
            print(f"Error: {plugins}")
        else:
            for pl in plugins:
                status = "ACTIVE" if pl["status"] == "active" else pl["status"]
                print(f"  [{status}] {pl['name']} v{pl.get('version','?')}")

        # Theme
        print("\n=== Active Theme ===")
        themes = await page.evaluate(JS_THEMES, nonce)
        if isinstance(themes, dict) and "error" in themes:
            print(f"Error: {themes}")
        else:
            for t in themes:
                print(f"  {t['name']} ({t['stylesheet']}) v{t.get('version','?')}")

        # Pages
        print("\n=== Existing Pages ===")
        pages_list = await page.evaluate(JS_PAGES, nonce)
        if isinstance(pages_list, dict) and "error" in pages_list:
            print(f"Error: {pages_list}")
        else:
            for pg in pages_list:
                tmpl = f" [{pg['template']}]" if pg.get("template") else ""
                print(f"  #{pg['id']} {pg['title']} ({pg['slug']}) [{pg['status']}]{tmpl}")

        await browser.close()


if __name__ == "__main__":
    asyncio.run(main())
