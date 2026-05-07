// AliExpress source-price scraper for in-scope SKUs.
// Pivoted from B&H Photo (bot-blocked, 0/119 hits) to AliExpress (works, prices in SAR).
//
// Strategy:
//   - Hit AliExpress search for the product name (auto-redirects to ar.aliexpress.com)
//   - Extract all SAR-denominated prices from body innerText via regex
//   - Filter to plausible range: [NeoGen list × 0.10, NeoGen list × 2.0]
//     (drops both filter widgets and counterfeit-clone outliers)
//   - Take MEDIAN of plausible prices as the supplier-cost estimate
//
// Output → output/sync/price-sync-aliexpress-2026-05-07.csv
// Run:    node scripts/live-market-sync-aliexpress.js

const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
const csv = require('csv-parser');
const { createObjectCsvWriter } = require('csv-writer');

const INPUT_FILE = 'data/catalogs/live/wc-export-enriched.csv';
const OUTPUT_FILE = 'output/sync/price-sync-aliexpress-2026-05-07.csv';

const IN_SCOPE_KEYWORDS = ['Smart Home', 'Networking', 'Homelab', 'Security'];

// Sanity thresholds vs NeoGen list price
const PLAUSIBLE_LOW_RATIO = 0.10;   // < 10% of list = noise (filter widgets, accessories)
const PLAUSIBLE_HIGH_RATIO = 2.00;  // > 200% of list = wrong product (different SKU)

// Polite delay between SKUs
const PER_SKU_DELAY_MS = 1500;

function median(nums) {
    if (nums.length === 0) return null;
    const s = [...nums].sort((a, b) => a - b);
    const mid = Math.floor(s.length / 2);
    return s.length % 2 === 0 ? (s[mid - 1] + s[mid]) / 2 : s[mid];
}

async function scrapeAliExpress(page, productName, listPriceSAR) {
    const url = `https://www.aliexpress.com/wholesale?SearchText=${encodeURIComponent(productName)}`;
    try {
        await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });
        await page.waitForTimeout(2500); // wait for cards to render
        const text = await page.evaluate(() => document.body.innerText);

        // Match AliExpress SAR prices like "1,234.56ر.س" or "73.33ر.س"
        const matches = [...text.matchAll(/([\d,]+(?:\.\d{1,2})?)\s*ر\.?س/g)];
        const allPrices = matches
            .map(m => parseFloat(m[1].replace(/,/g, '')))
            .filter(n => Number.isFinite(n));

        const lowBound = listPriceSAR * PLAUSIBLE_LOW_RATIO;
        const highBound = listPriceSAR * PLAUSIBLE_HIGH_RATIO;
        const plausible = allPrices.filter(p => p >= lowBound && p <= highBound);

        return {
            url: page.url(), // captured post-redirect URL
            count_all: allPrices.length,
            count_plausible: plausible.length,
            min_plausible: plausible.length ? Math.min(...plausible) : null,
            median_plausible: plausible.length ? median(plausible) : null,
            max_plausible: plausible.length ? Math.max(...plausible) : null,
            sample: allPrices.slice(0, 8),
        };
    } catch (err) {
        return { url, error: err.message.slice(0, 100) };
    }
}

async function run() {
    if (!fs.existsSync(INPUT_FILE)) {
        console.error(`Missing: ${INPUT_FILE}`);
        process.exit(1);
    }

    const rows = [];
    await new Promise((resolve) => {
        fs.createReadStream(INPUT_FILE)
            .pipe(csv())
            .on('data', (d) => rows.push(d))
            .on('end', resolve);
    });

    const inScope = rows.filter(r => {
        const cats = r.Categories || r.Category || '';
        return IN_SCOPE_KEYWORDS.some(k => cats.includes(k));
    });
    console.log(`Loaded ${rows.length} rows; ${inScope.length} in scope.`);

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        locale: 'en-US',
    });
    const page = await context.newPage();

    const startedAt = Date.now();
    const results = [];
    for (let i = 0; i < inScope.length; i++) {
        const r = inScope[i];
        const sku = r.SKU;
        const name = r.Name || '';
        const list = parseFloat((r['Regular price'] || '').replace(/,/g, '')) || 0;
        const tag = `[${i + 1}/${inScope.length}] ${sku}`;
        process.stdout.write(`${tag} ${name.slice(0, 50).padEnd(50)} `);

        if (list === 0) {
            process.stdout.write(`(no list price — skipping)\n`);
            results.push({ sku, name, regular_price_sar: '', skipped: 'no list price' });
            continue;
        }

        const ae = await scrapeAliExpress(page, name, list);
        const out = {
            sku,
            name,
            category: r.Categories || '',
            regular_price_sar: list,
            aliexpress_url: ae.url || '',
            ae_count_all: ae.count_all ?? 0,
            ae_count_plausible: ae.count_plausible ?? 0,
            ae_min_plausible: ae.min_plausible ?? '',
            ae_median_plausible: ae.median_plausible ?? '',
            ae_max_plausible: ae.max_plausible ?? '',
            ae_sample: (ae.sample || []).join('|'),
            error: ae.error || '',
        };
        results.push(out);

        const stat = ae.median_plausible
            ? `✓ med=${ae.median_plausible.toFixed(2)} (n=${ae.count_plausible}/${ae.count_all})`
            : `— no plausible (n=${ae.count_all})`;
        process.stdout.write(`${stat}\n`);

        await page.waitForTimeout(PER_SKU_DELAY_MS);
    }
    await browser.close();

    fs.mkdirSync(path.dirname(OUTPUT_FILE), { recursive: true });
    const writer = createObjectCsvWriter({
        path: OUTPUT_FILE,
        header: [
            { id: 'sku', title: 'SKU' },
            { id: 'name', title: 'Name' },
            { id: 'category', title: 'Category' },
            { id: 'regular_price_sar', title: 'NeoGen_List_SAR' },
            { id: 'aliexpress_url', title: 'AliExpress_URL' },
            { id: 'ae_count_all', title: 'AE_Prices_Count' },
            { id: 'ae_count_plausible', title: 'AE_Plausible_Count' },
            { id: 'ae_min_plausible', title: 'AE_Min_Plausible' },
            { id: 'ae_median_plausible', title: 'AE_Median_Plausible' },
            { id: 'ae_max_plausible', title: 'AE_Max_Plausible' },
            { id: 'ae_sample', title: 'AE_Sample' },
            { id: 'error', title: 'Error' },
            { id: 'skipped', title: 'Skipped' },
        ],
    });
    await writer.writeRecords(results);

    const elapsed = ((Date.now() - startedAt) / 1000).toFixed(1);
    const filled = results.filter(r => r.ae_median_plausible !== '').length;
    console.log(`\n--- Summary ---`);
    console.log(`Processed: ${results.length} SKUs in ${elapsed}s`);
    console.log(`Median price found: ${filled}/${results.length}`);
    console.log(`Output: ${OUTPUT_FILE}`);
}

run().catch(err => { console.error(err); process.exit(1); });
