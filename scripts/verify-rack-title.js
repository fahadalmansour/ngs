// Inject the v1.50.0 PHP fix simulation: replace .ng-rack-title .ar
// content for tiles where it currently holds a long English wall, then
// shoot the gaming category page to preview the post-deploy state.

const { chromium } = require('playwright');
const path = require('path');
const os = require('os');

const OUT = path.join(os.homedir(), '.claude/reports/neogen-store/screenshots/2026-05-08-after');

// Map of category slug → expected short Arabic name (matches what
// ng_ar_label() will produce on the WP "English | Arabic" pipe-named
// terms post-deploy).
const SHORT_NAMES = {
  'smart-home':           'البيت الذكي',
  'gaming':               'ألعاب',
  'networking':           'الشبكات',
  'hardware':             'أجهزة PC',
  'cables-adapters':      'كابلات ومحولات',
  'security-surveillance':'المراقبة والأمن',
};

(async () => {
  const browser = await chromium.launch();
  const ctx = await browser.newContext({ viewport: { width: 1280, height: 900 } });
  const page = await ctx.newPage();
  await page.goto('https://neogen.store/product-category/gaming/', { waitUntil: 'networkidle', timeout: 30000 });

  // Force reveal so below-fold rack is captured.
  await page.evaluate(() => document.querySelectorAll('.reveal').forEach(el => {
    el.classList.add('in'); el.style.transitionDelay = '0ms';
  }));

  // Apply the v1.50.0 CSS clamp.
  await page.addStyleTag({ content: `
    .ng-rack-title .ar {
      display: -webkit-box !important;
      -webkit-line-clamp: 2 !important;
      -webkit-box-orient: vertical !important;
      overflow: hidden !important;
    }
  `});

  // Simulate the PHP fix client-side: rewrite each rack tile's .ar text
  // to the expected short name based on the link slug.
  await page.evaluate((SHORT_NAMES) => {
    document.querySelectorAll('.ng-rack-unit').forEach(unit => {
      const href = unit.getAttribute('href') || '';
      const m = href.match(/\/product-category\/([^/]+)\/?$/);
      if (!m) return;
      const slug = m[1];
      const ar = unit.querySelector('.ng-rack-title .ar');
      if (ar && SHORT_NAMES[slug]) {
        ar.textContent = SHORT_NAMES[slug];
      }
    });
  }, SHORT_NAMES);

  await page.waitForTimeout(900);
  const f = path.join(OUT, 'cat-gaming-1280-en-v1.50.0.png');
  await page.screenshot({ path: f, fullPage: true });
  console.log('saved', f);
  await ctx.close();
  await browser.close();
})();
