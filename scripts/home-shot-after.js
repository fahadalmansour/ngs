const { chromium } = require('playwright');
const path = require('path');
const os = require('os');
const OUT = path.join(os.homedir(), '.claude/reports/neogen-store/screenshots/2026-05-08-after');
(async () => {
  const browser = await chromium.launch();
  for (const vp of [{n:'1280',w:1280,h:900},{n:'360',w:360,h:720}]) {
    const ctx = await browser.newContext({ viewport: { width: vp.w, height: vp.h } });
    const page = await ctx.newPage();
    await page.goto('https://neogen.store/', { waitUntil: 'networkidle', timeout: 30000 });
    await page.evaluate(() => document.querySelectorAll('.reveal').forEach(el => { el.classList.add('in'); el.style.transitionDelay='0ms'; }));
    await page.waitForTimeout(1200);
    const f = path.join(OUT, `home-${vp.n}-en.png`);
    await page.screenshot({ path: f, fullPage: true });
    console.log('saved', f);
    await ctx.close();
  }
  await browser.close();
})();
