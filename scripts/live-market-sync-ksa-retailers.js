// KSA-retailer fallback scraper for SKUs that Amazon.sa scrape missed.
// Hits Microless, Jarir, eXtra search results. Reports first plausible SAR
// price found per retailer. Output → output/sync/price-sync-ksa-retailers-2026-05-07.csv
//
// Run after `live-market-sync-inscope.js`:
//   node scripts/live-market-sync-ksa-retailers.js

const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
const { createObjectCsvWriter } = require('csv-writer');

const INPUT_FILE = '/tmp/unfilled-skus.json';
const OUTPUT_FILE = 'output/sync/price-sync-ksa-retailers-2026-05-07.csv';

const RETAILERS = [
    {
        name: 'Microless',
        url: (q) => `https://saudi.microless.com/search/?q=${encodeURIComponent(q)}`,
        // Microless price markup: <span class="price">SAR 1,234</span>
        priceSelector: '.product-price, .price, [data-test="product-price"]',
    },
    {
        name: 'Jarir',
        // Jarir KSA english storefront
        url: (q) => `https://www.jarir.com/sa-en/catalogsearch/result/?q=${encodeURIComponent(q)}`,
        priceSelector: '.price-box .price, .price-wrapper .price, .price',
    },
    {
        name: 'eXtra',
        url: (q) => `https://www.extra.com/en-sa/search/?text=${encodeURIComponent(q)}`,
        priceSelector: '[data-testid="price-amount"], .product-price, .price',
    },
];

function looksLikePrice(text) {
    // Match an SAR-shaped number: optional commas/spaces/SAR/ر.س, must be 2-7 digits before optional decimals
    const m = text.match(/(\d{1,3}(?:[,\s]\d{3})*(?:\.\d{1,2})?|\d{2,7}(?:\.\d{1,2})?)/);
    if (!m) return null;
    const num = parseFloat(m[1].replace(/[,\s]/g, ''));
    if (!Number.isFinite(num)) return null;
    // Reject implausible: < 20 SAR (probably not a real product price) or > 100,000 SAR
    if (num < 20 || num > 100000) return null;
    return num;
}

async function scrapeRetailer(page, retailer, query) {
    const url = retailer.url(query);
    try {
        await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 25000 });
        const handles = await page.locator(retailer.priceSelector).all();
        for (const h of handles.slice(0, 5)) {
            try {
                const txt = (await h.innerText({ timeout: 2000 })).trim();
                const px = looksLikePrice(txt);
                if (px) return { price: px, raw: txt.slice(0, 60), url };
            } catch {}
        }
        return { price: null, raw: '(no price element matched)', url };
    } catch (err) {
        return { price: null, raw: `error: ${err.message.slice(0, 60)}`, url };
    }
}

async function run() {
    if (!fs.existsSync(INPUT_FILE)) {
        console.error(`Missing: ${INPUT_FILE}`);
        process.exit(1);
    }
    const skus = JSON.parse(fs.readFileSync(INPUT_FILE, 'utf-8'));
    console.log(`Loaded ${skus.length} unfilled SKUs.`);

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        userAgent: 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        locale: 'en-SA',
    });
    const page = await context.newPage();

    const results = [];
    for (let i = 0; i < skus.length; i++) {
        const sku = skus[i];
        const tag = `[${i + 1}/${skus.length}] ${sku.sku}`;
        process.stdout.write(`${tag} ${sku.name.slice(0, 50).padEnd(50)} `);

        const row = {
            sku: sku.sku,
            name: sku.name,
            category: sku.category,
            regular_price_sar: sku.regular_price,
        };
        let bestPrice = null;
        for (const retailer of RETAILERS) {
            const r = await scrapeRetailer(page, retailer, sku.name);
            row[`${retailer.name}_price`]  = r.price ?? '';
            row[`${retailer.name}_raw`]    = r.raw;
            row[`${retailer.name}_url`]    = r.url;
            if (r.price && (!bestPrice || r.price > bestPrice)) bestPrice = r.price;
            await page.waitForTimeout(800);
        }
        row.best_price_sar = bestPrice ?? '';

        const stat = bestPrice ? `✓ ${bestPrice}` : '— none';
        process.stdout.write(`${stat}\n`);
        results.push(row);
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
            { id: 'Microless_price', title: 'Microless_Price' },
            { id: 'Microless_raw', title: 'Microless_Raw' },
            { id: 'Microless_url', title: 'Microless_URL' },
            { id: 'Jarir_price', title: 'Jarir_Price' },
            { id: 'Jarir_raw', title: 'Jarir_Raw' },
            { id: 'Jarir_url', title: 'Jarir_URL' },
            { id: 'eXtra_price', title: 'eXtra_Price' },
            { id: 'eXtra_raw', title: 'eXtra_Raw' },
            { id: 'eXtra_url', title: 'eXtra_URL' },
            { id: 'best_price_sar', title: 'Best_Price_SAR' },
        ],
    });
    await writer.writeRecords(results);

    const filled = results.filter(r => r.best_price_sar !== '').length;
    console.log(`\n--- Summary ---`);
    console.log(`Processed: ${results.length} SKUs`);
    console.log(`At least one retailer hit: ${filled}/${results.length}`);
    console.log(`Output: ${OUTPUT_FILE}`);
}

run().catch(err => { console.error(err); process.exit(1); });
