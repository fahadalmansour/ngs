// Inject the v1.49.0 CSS deltas onto the live site and re-shoot 360 home
// so we can verify H3 (collage hidden) and H5 (trust strip 1-col) visually
// before pushing.

const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
const os = require('os');

const OUT = path.join(os.homedir(), '.claude/reports/neogen-store/screenshots/2026-05-08-after');

const PATCH = `
@media (max-width: 480px) {
  .ng-hero-collage { display: none !important; }
}
@media (max-width: 420px) {
  .ng-foot-trust { grid-template-columns: 1fr !important; gap: 8px !important; padding: 20px 16px !important; }
  .ng-foot-trust-item { padding: 14px 16px !important; }
  .ng-foot-trust-sub { font-size: 11.5px !important; line-height: 1.45 !important; }
}
`;

(async () => {
  const browser = await chromium.launch();
  const ctx = await browser.newContext({ viewport: { width: 360, height: 720 } });
  const page = await ctx.newPage();
  await page.goto('https://neogen.store/', { waitUntil: 'networkidle', timeout: 30000 });
  await page.addStyleTag({ content: PATCH });
  await page.evaluate(() => {
    document.querySelectorAll('.reveal').forEach(el => {
      el.classList.add('in');
      el.style.transitionDelay = '0ms';
    });
  });
  await page.waitForTimeout(1200);
  const f = path.join(OUT, 'home-360-en-v1.49.0.png');
  await page.screenshot({ path: f, fullPage: true });
  console.log('saved', f);
  await ctx.close();
  await browser.close();
})();
