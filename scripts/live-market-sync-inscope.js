// Wrapper around live-market-sync.js scoped to the post-prune Smart Home + Security + Networking + Homelab subset (119 SKUs).
// Output → output/sync/price-sync-live-inscope-2026-05-07.csv
// Run: node scripts/live-market-sync-inscope.js

const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
const csv = require('csv-parser');
const { createObjectCsvWriter } = require('csv-writer');

const INPUT_FILE = 'data/catalogs/live/wc-export-enriched.csv';
const OUTPUT_FILE = 'output/sync/price-sync-live-inscope-2026-05-07.csv';

const IN_SCOPE_KEYWORDS = ['Smart Home', 'Networking', 'Homelab', 'Security'];

const SAR_PER_USD = 3.75;
const INTL_SHIPPING_KG = 60;
const TAX_CUSTOMS_MULT = 1.2075;
const LOCAL_DELIVERY = 30;

async function scrapeMarket(page, url, selector, label) {
    try {
        await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 25000 });
        const element = await page.waitForSelector(selector, { timeout: 5000 });
        const text = await element.innerText();
        const parsed = parseFloat(text.replace(/[^0-9.]/g, ''));
        return Number.isFinite(parsed) ? parsed : null;
    } catch (e) {
        return null;
    }
}

async function processRow(row, page, idx, total) {
    const sku = row.SKU;
    const name = row['Name'] || row['Product Name (EN)'] || '';
    const weight = parseFloat(row['Weight (kg)']) || 0.5;
    const tag = `[${idx}/${total}] ${sku}`;

    process.stdout.write(`${tag} ${name.slice(0, 50).padEnd(50)} `);

    let supplierPriceUSD = null;
    let sourceSite = '';

    if (sku.startsWith('SH-')) {
        sourceSite = 'AliExpress';
        supplierPriceUSD = await scrapeMarket(page,
            `https://www.aliexpress.com/wholesale?SearchText=${encodeURIComponent(name)}`,
            '.product-price-value, [class*="price"]', 'AliExpress');
    } else {
        sourceSite = 'B&H';
        supplierPriceUSD = await scrapeMarket(page,
            `https://www.bhphotovideo.com/c/search?Ntt=${encodeURIComponent(name)}`,
            '[data-selenium="pricing-price"], [data-selenium="price"]', 'B&H');
    }

    const amazonSAPrice = await scrapeMarket(page,
        `https://www.amazon.sa/s?k=${encodeURIComponent(name)}`,
        '.a-price-whole', 'Amazon.sa');

    const result = {
        sku,
        name,
        category: row.Categories || row.Category || '',
        weight_kg: weight,
        source_site: sourceSite,
        source_price_usd: supplierPriceUSD ?? '',
        amazon_sa_ref_sar: amazonSAPrice ?? '',
        landed_cost_sar: '',
        target_sale_sar: '',
        notes: ''
    };

    if (supplierPriceUSD) {
        const buySAR = supplierPriceUSD * SAR_PER_USD;
        const intlShip = weight * INTL_SHIPPING_KG;
        const landed = (buySAR + intlShip) * TAX_CUSTOMS_MULT + LOCAL_DELIVERY;
        result.landed_cost_sar = landed.toFixed(2);

        const target = landed * 1.15;
        if (amazonSAPrice && amazonSAPrice < target) {
            result.target_sale_sar = (Math.max(amazonSAPrice - 5, landed * 1.10)).toFixed(2);
            result.notes = 'Amazon undercuts target — squeezed to 10% margin';
        } else {
            result.target_sale_sar = target.toFixed(2);
        }
    } else {
        result.notes = `No source price on ${sourceSite}`;
    }

    const status = supplierPriceUSD ? `✓ $${supplierPriceUSD}` : 'no source';
    const amzn = amazonSAPrice ? `Amzn ${amazonSAPrice}` : '';
    process.stdout.write(`${status.padEnd(15)} ${amzn}\n`);
    return result;
}

async function run() {
    const rows = [];
    if (!fs.existsSync(INPUT_FILE)) {
        console.error(`Error: ${INPUT_FILE} not found.`);
        process.exit(1);
    }

    await new Promise((resolve) => {
        fs.createReadStream(INPUT_FILE)
            .pipe(csv())
            .on('data', (data) => rows.push(data))
            .on('end', resolve);
    });

    const inScope = rows.filter(r => {
        const cats = r.Categories || r.Category || '';
        return IN_SCOPE_KEYWORDS.some(k => cats.includes(k));
    });

    console.log(`Loaded ${rows.length} rows; ${inScope.length} in scope.`);
    if (inScope.length === 0) {
        console.error('Nothing in scope — aborting.');
        process.exit(1);
    }

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    });
    const page = await context.newPage();

    const results = [];
    const startedAt = Date.now();
    for (let i = 0; i < inScope.length; i++) {
        try {
            const r = await processRow(inScope[i], page, i + 1, inScope.length);
            results.push(r);
        } catch (err) {
            console.log(`  ERROR on ${inScope[i].SKU}: ${err.message.slice(0, 80)}`);
            results.push({
                sku: inScope[i].SKU, name: inScope[i].Name,
                category: inScope[i].Categories, weight_kg: inScope[i]['Weight (kg)'],
                source_site: '', source_price_usd: '', amazon_sa_ref_sar: '',
                landed_cost_sar: '', target_sale_sar: '', notes: `error: ${err.message.slice(0, 60)}`
            });
        }
        await page.waitForTimeout(1500);
    }
    await browser.close();

    fs.mkdirSync(path.dirname(OUTPUT_FILE), { recursive: true });
    const writer = createObjectCsvWriter({
        path: OUTPUT_FILE,
        header: [
            { id: 'sku', title: 'SKU' },
            { id: 'name', title: 'Name' },
            { id: 'category', title: 'Category' },
            { id: 'weight_kg', title: 'Weight_kg' },
            { id: 'source_site', title: 'Source_Site' },
            { id: 'source_price_usd', title: 'Source_Price_USD' },
            { id: 'amazon_sa_ref_sar', title: 'Amazon_SA_Ref_SAR' },
            { id: 'landed_cost_sar', title: 'Landed_Cost_SAR' },
            { id: 'target_sale_sar', title: 'Target_Sale_SAR' },
            { id: 'notes', title: 'Notes' }
        ]
    });
    await writer.writeRecords(results);

    const elapsed = ((Date.now() - startedAt) / 1000).toFixed(1);
    const filledSrc = results.filter(r => r.source_price_usd !== '').length;
    const filledAmzn = results.filter(r => r.amazon_sa_ref_sar !== '').length;
    console.log(`\n--- Summary ---`);
    console.log(`Processed: ${results.length} SKUs in ${elapsed}s`);
    console.log(`Source price found: ${filledSrc}/${results.length}`);
    console.log(`Amazon SA ref found: ${filledAmzn}/${results.length}`);
    console.log(`Output: ${OUTPUT_FILE}`);
}

run().catch(err => { console.error(err); process.exit(1); });
