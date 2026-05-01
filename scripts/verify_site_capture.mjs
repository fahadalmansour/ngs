#!/usr/bin/env node

import fs from 'node:fs/promises';
import path from 'node:path';
import { chromium } from 'playwright';

const ROOT = process.cwd();
const CAPTURE_ROOT = path.join(ROOT, 'archive/site-captures/neogen-store-best');
const OUTPUT_ROOT = path.join(ROOT, 'output/site-captures');
const SCREENSHOT_ROOT = path.join(ROOT, 'output/screenshots/site-capture-verification');
const REPORT_PATH = path.join(ROOT, 'docs/site-captures/LOCAL_CAPTURE_VERIFICATION.md');
const JSON_PATH = path.join(OUTPUT_ROOT, 'local-capture-verification.json');

const allowedHosts = new Set(['neogen.store', 'fonts.googleapis.com', 'fonts.gstatic.com']);

const pages = [
  ['home', 'neogen.store/index.html'],
  ['shop', 'neogen.store/shop/index.html'],
  ['gift_cards', 'neogen.store/product-category/gift-cards/index.html'],
  ['networking', 'neogen.store/product-category/networking/index.html'],
  ['apple_gift_card', 'neogen.store/product/apple-gift-card-100-بطاقة-آبل/index.html'],
  ['adobe', 'neogen.store/product/adobe-creative-cloud-1-year-أدوبي-كرييتف-كلاود/index.html'],
  ['asustor', 'neogen.store/product/asustor-flashstor-6-fs6706t-all-nvme-ssd-nas-for-extreme-performance/index.html'],
  ['flipper', 'neogen.store/product/flipper-zero-the-ultimate-multi-tool-for-geeks-and-cyber-research/index.html'],
];

function mime(filePath) {
  const ext = path.extname(filePath).toLowerCase();
  return ({
    '.html': 'text/html; charset=utf-8',
    '.htm': 'text/html; charset=utf-8',
    '.css': 'text/css; charset=utf-8',
    '.js': 'application/javascript; charset=utf-8',
    '.png': 'image/png',
    '.jpg': 'image/jpeg',
    '.jpeg': 'image/jpeg',
    '.webp': 'image/webp',
    '.gif': 'image/gif',
    '.svg': 'image/svg+xml',
    '.woff2': 'font/woff2',
    '.woff': 'font/woff',
    '.ttf': 'font/ttf',
    '.xml': 'application/xml; charset=utf-8',
    '.txt': 'text/plain; charset=utf-8',
  })[ext] || 'application/octet-stream';
}

async function exists(filePath) {
  try {
    await fs.access(filePath);
    return true;
  } catch {
    return false;
  }
}

async function isFile(filePath) {
  try {
    return (await fs.stat(filePath)).isFile();
  } catch {
    return false;
  }
}

async function firstExisting(candidates) {
  for (const candidate of candidates) {
    if (await isFile(candidate)) return candidate;
  }
  return null;
}

async function findQueryCapture(basePath, search) {
  if (!search) return null;

  const decodedQuery = decodeURIComponent(search.slice(1));
  const dir = path.dirname(basePath);
  const base = path.basename(basePath);
  const stem = base.replace(/\.[^.]+$/, '');
  const directPrefixes = [
    `${base}?${decodedQuery}`,
    `${base}﹖${decodedQuery}`,
    `${stem}?${decodedQuery}`,
    `${stem}﹖${decodedQuery}`,
    `index?${decodedQuery}`,
    `index﹖${decodedQuery}`,
  ];

  for (const candidateDir of [dir, basePath]) {
    let entries;
    try {
      entries = await fs.readdir(candidateDir);
    } catch {
      continue;
    }

    for (const prefix of directPrefixes) {
      const match = entries.find((entry) => entry.startsWith(prefix));
      if (match) {
        const matchPath = path.join(candidateDir, match);
        if (await isFile(matchPath)) return matchPath;
      }
    }

    if (entries.length === 1 && entries[0].startsWith('index﹖')) {
      const matchPath = path.join(candidateDir, entries[0]);
      if (await isFile(matchPath)) return matchPath;
    }
  }

  return null;
}

async function mapUrlToFile(urlString) {
  let url;
  try {
    url = new URL(urlString);
  } catch {
    return null;
  }

  if (!allowedHosts.has(url.hostname)) return null;

  let hostname = url.hostname;
  let pathname = decodeURIComponent(url.pathname);

  if (hostname === 'fonts.googleapis.com' && pathname.startsWith('/fonts.gstatic.com/')) {
    hostname = 'fonts.gstatic.com';
    pathname = pathname.replace('/fonts.gstatic.com', '');
  }

  const hostRoot = path.join(CAPTURE_ROOT, hostname);
  let basePath = path.join(hostRoot, pathname);

  const candidates = [
    pathname.endsWith('/') ? path.join(hostRoot, pathname, 'index.html') : null,
    basePath,
    `${basePath}.html`,
    path.join(basePath, 'index.html'),
  ].filter(Boolean);

  const normalMatch = await firstExisting(candidates);
  if (normalMatch) return normalMatch;

  return findQueryCapture(basePath, url.search);
}

async function countFiles(dir, predicate = () => true) {
  let count = 0;
  async function walk(current) {
    const entries = await fs.readdir(current, { withFileTypes: true }).catch(() => []);
    for (const entry of entries) {
      const fullPath = path.join(current, entry.name);
      if (entry.isDirectory()) await walk(fullPath);
      if (entry.isFile() && await predicate(fullPath)) count += 1;
    }
  }
  await walk(dir);
  return count;
}

async function countDirectProductPages() {
  const productRoot = path.join(CAPTURE_ROOT, 'neogen.store/product');
  const entries = await fs.readdir(productRoot, { withFileTypes: true }).catch(() => []);
  let count = 0;
  for (const entry of entries) {
    if (!entry.isDirectory()) continue;
    if (await isFile(path.join(productRoot, entry.name, 'index.html'))) count += 1;
  }
  return count;
}

async function triggerLazyImages(page) {
  await page.evaluate(async () => {
    const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));
    const max = Math.max(
      document.body?.scrollHeight || 0,
      document.documentElement?.scrollHeight || 0,
    );

    for (let y = 0; y <= max; y += Math.max(300, Math.floor(window.innerHeight * 0.8))) {
      window.scrollTo(0, y);
      await delay(120);
    }

    window.scrollTo(0, 0);
    await delay(250);
  });

  await page.waitForLoadState('networkidle', { timeout: 5000 }).catch(() => {});
}

async function settleImages(page) {
  await page.evaluate(async () => {
    const images = Array.from(document.images);

    for (const image of images) {
      image.loading = 'eager';
      image.scrollIntoView({ block: 'center', inline: 'center' });
      await new Promise((resolve) => setTimeout(resolve, 40));
    }

    await Promise.allSettled(images.map((image) => {
      if (image.complete) return Promise.resolve();
      return new Promise((resolve) => {
        const timer = setTimeout(resolve, 5000);
        image.addEventListener('load', () => {
          clearTimeout(timer);
          resolve();
        }, { once: true });
        image.addEventListener('error', () => {
          clearTimeout(timer);
          resolve();
        }, { once: true });
      });
    }));

    window.scrollTo(0, 0);
  });

  await page.waitForLoadState('networkidle', { timeout: 5000 }).catch(() => {});
}

async function main() {
  await fs.mkdir(OUTPUT_ROOT, { recursive: true });
  await fs.mkdir(SCREENSHOT_ROOT, { recursive: true });
  await fs.mkdir(path.dirname(REPORT_PATH), { recursive: true });

  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1440, height: 1000 },
    deviceScaleFactor: 1,
  });

  const routed = {
    fulfilledLocalAssetRequests: 0,
    missingLocalAssetRequests: [],
    blockedExternalRequests: [],
  };

  await context.route('**/*', async (route) => {
    const req = route.request();
    const reqUrl = req.url();
    if (reqUrl.startsWith('file://')) {
      const url = new URL(reqUrl);
      const pathname = decodeURIComponent(url.pathname);

      if (url.search.includes('wc-ajax=get_refreshed_fragments')) {
        return route.fulfill({
          status: 204,
          body: '',
          contentType: 'text/plain',
        });
      }

      if (pathname.startsWith('/wp-content/')) {
        const mapped = path.join(CAPTURE_ROOT, 'neogen.store', pathname);
        if (await isFile(mapped)) {
          return route.fulfill({ path: mapped, contentType: mime(mapped) });
        }
      }

      return route.continue();
    }

    const mapped = await mapUrlToFile(reqUrl);
    if (mapped) {
      routed.fulfilledLocalAssetRequests += 1;
      return route.fulfill({ path: mapped, contentType: mime(mapped) });
    }

    let host = '';
    try {
      host = new URL(reqUrl).hostname;
    } catch {
      host = reqUrl;
    }

    if (allowedHosts.has(host)) routed.missingLocalAssetRequests.push(reqUrl);
    else routed.blockedExternalRequests.push(reqUrl);
    return route.abort();
  });

  const page = await context.newPage();
  const consoleErrors = [];
  const pageErrors = [];
  const requestFailures = [];
  const badResponses = [];

  page.on('console', (msg) => {
    if (msg.type() === 'error') consoleErrors.push(msg.text());
  });
  page.on('pageerror', (error) => pageErrors.push(error.message));
  page.on('requestfailed', (request) => {
    requestFailures.push({ url: request.url(), failure: request.failure()?.errorText || '' });
  });
  page.on('response', (response) => {
    if (response.status() >= 400) {
      badResponses.push({ status: response.status(), url: response.url() });
    }
  });

  const pageResults = [];
  for (const [name, relativePath] of pages) {
    const filePath = path.join(CAPTURE_ROOT, relativePath);
    const beforeFailures = requestFailures.length;
    const beforeBadResponses = badResponses.length;
    const response = await page.goto(`file://${filePath}`, {
      waitUntil: 'domcontentloaded',
      timeout: 30000,
    }).catch((error) => ({ error: error.message }));

    await page.waitForTimeout(1200);
    await triggerLazyImages(page);
    await settleImages(page);
    const metrics = await page.evaluate(() => {
      const images = Array.from(document.images).map((image) => {
        const rect = image.getBoundingClientRect();
        return {
          src: image.currentSrc || image.src,
          alt: image.alt || '',
          complete: image.complete,
          naturalWidth: image.naturalWidth,
          naturalHeight: image.naturalHeight,
          visibleWidth: Math.round(rect.width),
          visibleHeight: Math.round(rect.height),
        };
      });

      return {
        title: document.title,
        bodyTextLength: document.body?.innerText?.trim().length || 0,
        productTitle: document.querySelector('.product_title, h1')?.textContent?.trim() || '',
        imageCount: images.length,
        brokenImageCount: images.filter((image) => !image.complete || image.naturalWidth === 0).length,
        brokenImageSamples: images
          .filter((image) => !image.complete || image.naturalWidth === 0)
          .slice(0, 12)
          .map((image) => ({
            src: image.src,
            alt: image.alt,
            complete: image.complete,
            naturalWidth: image.naturalWidth,
            naturalHeight: image.naturalHeight,
          })),
        visibleImageCount: images.filter((image) => image.visibleWidth > 10 && image.visibleHeight > 10).length,
        linkCount: document.links.length,
        scriptCount: document.scripts.length,
        stylesheetCount: document.styleSheets.length,
      };
    });

    await page.screenshot({
      path: path.join(SCREENSHOT_ROOT, `${name}.png`),
      fullPage: false,
    });

    pageResults.push({
      name,
      relativePath,
      ok: !response?.error && metrics.bodyTextLength > 1000 && metrics.brokenImageCount === 0,
      navigationError: response?.error || null,
      newRequestFailures: requestFailures.length - beforeFailures,
      newBadResponses: badResponses.length - beforeBadResponses,
      ...metrics,
    });
  }

  await browser.close();

  const productPageCount = await countDirectProductPages();
  const totalFileCount = await countFiles(CAPTURE_ROOT);
  const emptyFileCount = await countFiles(CAPTURE_ROOT, async (filePath) => {
    const stat = await fs.stat(filePath);
    return stat.size === 0;
  });

  const result = {
    checkedAt: new Date().toISOString(),
    captureRoot: path.relative(ROOT, CAPTURE_ROOT),
    screenshotRoot: path.relative(ROOT, SCREENSHOT_ROOT),
    totalFileCount,
    productPageCount,
    emptyFileCount,
    pageResults,
    routed: {
      fulfilledLocalAssetRequests: routed.fulfilledLocalAssetRequests,
      missingLocalAssetRequestCount: routed.missingLocalAssetRequests.length,
      missingLocalAssetSamples: [...new Set(routed.missingLocalAssetRequests)].slice(0, 20),
      blockedExternalRequestCount: routed.blockedExternalRequests.length,
      blockedExternalHostSamples: [
        ...new Set(routed.blockedExternalRequests.map((reqUrl) => {
          try {
            return new URL(reqUrl).hostname;
          } catch {
            return reqUrl;
          }
        })),
      ].slice(0, 20),
    },
    browserIssues: {
      consoleErrorCount: consoleErrors.length,
      consoleErrorSamples: [...new Set(consoleErrors)].slice(0, 20),
      pageErrorCount: pageErrors.length,
      pageErrorSamples: [...new Set(pageErrors)].slice(0, 20),
      requestFailureCount: requestFailures.length,
      requestFailureSamples: requestFailures.slice(0, 20),
      badResponseCount: badResponses.length,
      badResponseSamples: badResponses.slice(0, 20),
    },
  };

  const pageRows = pageResults.map((item) => (
    `| ${item.name} | ${item.ok ? 'pass' : 'review'} | ${item.bodyTextLength} | ${item.imageCount} | ${item.brokenImageCount} | ${item.visibleImageCount} |`
  )).join('\n');

  const report = `# Local Site Capture Verification

Generated: ${result.checkedAt}

## Result

- Capture root: \`${result.captureRoot}\`
- Screenshot folder: \`${result.screenshotRoot}\`
- Files checked in capture: ${result.totalFileCount}
- Product pages present: ${result.productPageCount}
- Browser pages sampled: ${result.pageResults.length}
- Pages passing sampled checks: ${result.pageResults.filter((item) => item.ok).length}
- Missing local asset requests: ${result.routed.missingLocalAssetRequestCount}
- Blocked external hosts: ${result.routed.blockedExternalHostSamples.join(', ') || 'none'}

## Page Samples

| Page | Status | Text length | Images | Broken images | Visible images |
| --- | --- | ---: | ---: | ---: | ---: |
${pageRows}

## Notes

- Static verification routes \`https://neogen.store/*\`, \`fonts.googleapis.com/*\`, and \`fonts.gstatic.com/*\` back to the local capture.
- Third-party runtime calls are intentionally blocked during verification.
- Screenshots were saved for visual review.
`;

  await fs.writeFile(JSON_PATH, JSON.stringify(result, null, 2));
  await fs.writeFile(REPORT_PATH, report);

  console.log(JSON.stringify({
    report: path.relative(ROOT, REPORT_PATH),
    json: path.relative(ROOT, JSON_PATH),
    screenshots: path.relative(ROOT, SCREENSHOT_ROOT),
    sampledPages: result.pageResults.length,
    passingPages: result.pageResults.filter((item) => item.ok).length,
    productPageCount: result.productPageCount,
    missingLocalAssetRequestCount: result.routed.missingLocalAssetRequestCount,
    blockedExternalHostSamples: result.routed.blockedExternalHostSamples,
  }, null, 2));
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
