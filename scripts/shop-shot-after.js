// Capture /shop/ at 1280/768/360 in EN+AR for B1 resolution check.
// Saves to ~/.claude/reports/neogen-store/screenshots/2026-05-08-after/

const { chromium } = require('playwright');
const path = require('path');
const os = require('os');

const OUT = path.join(os.homedir(), '.claude/reports/neogen-store/screenshots/2026-05-08-after');

const VIEWPORTS = [
  { name: '1280', width: 1280, height: 900 },
  { name: '768',  width: 768,  height: 1024 },
  { name: '360',  width: 360,  height: 720 },
];

const PAGES = [
  { name: 'shop', url: 'https://neogen.store/shop/' },
];

const LOCALES = [
  { name: 'en', cookie: null },
  { name: 'ar', cookie: { name: 'wp-wpml_current_language', value: 'ar', domain: 'neogen.store', path: '/' } },
];

(async () => {
  const browser = await chromium.launch();
  for (const vp of VIEWPORTS) {
    for (const loc of LOCALES) {
      const ctx = await browser.newContext({ viewport: { width: vp.width, height: vp.height } });
      if (loc.cookie) await ctx.addCookies([loc.cookie]);
      const page = await ctx.newPage();
      for (const p of PAGES) {
        const url = loc.name === 'ar' ? p.url.replace('neogen.store/', 'neogen.store/ar/') : p.url;
        try {
          await page.goto(url, { waitUntil: 'networkidle', timeout: 30000 });
          // Force-trigger the IntersectionObserver scroll-reveal so fullPage
          // screenshots don't capture below-fold cards at opacity:0.
          await page.evaluate(() => {
            document.querySelectorAll('.reveal').forEach(el => {
              el.classList.add('in');
              el.style.transitionDelay = '0ms';
            });
          });
          await page.waitForTimeout(1200);
          const file = path.join(OUT, `${p.name}-${vp.name}-${loc.name}.png`);
          await page.screenshot({ path: file, fullPage: true });
          console.log(`saved ${file}`);
        } catch (e) {
          console.error(`fail ${p.name}-${vp.name}-${loc.name}: ${e.message}`);
        }
      }
      await ctx.close();
    }
  }
  await browser.close();
})();
