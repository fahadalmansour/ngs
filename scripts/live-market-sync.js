const { chromium } = require('playwright');
const fs = require('fs');
const csv = require('csv-parser');
const { createObjectCsvWriter } = require('csv-writer');

const INPUT_FILE = 'data/catalogs/live/wc-export-enriched.csv';
const OUTPUT_FILE = 'output/sync/price-sync-live.csv';

// --- CONFIGURATION & LOGISTICS FORMULA ---
const SAR_PER_USD = 3.75;
const INTL_SHIPPING_KG = 60; // Stage 2: Import to KSA
const TAX_CUSTOMS_MULT = 1.2075; // 15% VAT + 5% Customs
const LOCAL_DELIVERY = 30; // Stage 4: Local Riyadh Shipping

/**
 * Generic scraper helper
 */
async function scrapeMarket(page, url, selector) {
    try {
        await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30000 });
        const element = await page.waitForSelector(selector, { timeout: 5000 });
        const text = await element.innerText();
        return parseFloat(text.replace(/[^0-9.]/g, ''));
    } catch (e) {
        return null;
    }
}

async function processRow(row, page) {
    const sku = row.SKU;
    const name = row['Product Name (EN)'] || row.Name;
    const weight = parseFloat(row['Weight (kg)']) || 0.5;

    // Skip digital items
    if (row.Category && row.Category.includes('Gift Cards')) {
         console.log(`Skipping digital item: ${sku}`);
         return null;
    }

    console.log(`\n🔍 Checking [${sku}] ${name}...`);

    let supplierPriceUSD = null;
    let sourceSite = '';

    // Stage 1: Buying (Determine Source based on SKU prefix)
    if (sku.startsWith('SH-')) {
        // China Sourcing (Smart Home)
        sourceSite = 'AliExpress';
        supplierPriceUSD = await scrapeMarket(page, 
            `https://www.aliexpress.com/wholesale?SearchText=${encodeURIComponent(sku)}`, 
            '.product-price-value'); 
    } else {
        // US Sourcing (Networking, etc.) - B&H
        sourceSite = 'B&H';
        // Try SKU first
        supplierPriceUSD = await scrapeMarket(page, 
            `https://www.bhphotovideo.com/c/search?Ntt=${encodeURIComponent(sku)}`, 
            '[data-selenium="pricing-price"], [data-selenium="price"]');
        
        // Fallback to Name if SKU fails
        if (!supplierPriceUSD) {
            supplierPriceUSD = await scrapeMarket(page, 
                `https://www.bhphotovideo.com/c/search?Ntt=${encodeURIComponent(name)}`, 
                '[data-selenium="pricing-price"], [data-selenium="price"]');
        }
    }

    // Stage 3: Market Benchmark (Scrape Amazon.sa)
    const amazonSAPrice = await scrapeMarket(page, 
        `https://www.amazon.sa/s?k=${encodeURIComponent(name)}`, 
        '.a-price-whole');

    let landedCost = null;
    let finalSalePrice = null;
    let warning = '';

    if (supplierPriceUSD) {
        // Stage 2: Import Calculation
        const buySAR = supplierPriceUSD * SAR_PER_USD;
        const intlShip = weight * INTL_SHIPPING_KG;
        landedCost = (buySAR + intlShip) * TAX_CUSTOMS_MULT;
        
        // Stage 4: Local Fulfillment & Competitive Logic
        // Break-even = Landed Cost + Riyadh Local Delivery
        const breakEven = landedCost + LOCAL_DELIVERY;
        const targetSalePrice = breakEven * 1.20; // 20% Base Margin
        
        if (amazonSAPrice) {
            if (targetSalePrice < amazonSAPrice) {
                // Beat Amazon by 5 SAR
                finalSalePrice = amazonSAPrice - 5;
            } else {
                // Squeeze to 10% margin if Amazon is cheaper
                finalSalePrice = breakEven * 1.10;
                warning = "Low Margin Warning (Cheaper on Amazon)";
            }
        } else {
            // No benchmark, use target 20% margin
            finalSalePrice = targetSalePrice;
        }

        console.log(`  Source (${sourceSite}): $${supplierPriceUSD}`);
        console.log(`  Landed Cost:  ${landedCost.toFixed(2)} SAR`);
        console.log(`  Amazon KSA:   ${amazonSAPrice ? amazonSAPrice + ' SAR' : 'N/A'}`);
        console.log(`  Final Sale:   ${finalSalePrice.toFixed(2)} SAR ${warning ? `[${warning}]` : ''}`);

        return {
            sku: sku,
            name: name,
            source_price_usd: supplierPriceUSD,
            landed_cost_sar: landedCost.toFixed(2),
            ksa_market_ref: amazonSAPrice || 'N/A',
            final_sale_price: finalSalePrice.toFixed(2),
            warning: warning
        };
    } else {
         console.log(`  Could not find source price on ${sourceSite}`);
         return null;
    }
}

async function run() {
    const results = [];
    const rows = [];

    if (!fs.existsSync(INPUT_FILE)) {
        console.error(`Error: ${INPUT_FILE} not found.`);
        return;
    }

    console.log("🚀 Starting NeoGen Market Sync...");

    fs.createReadStream(INPUT_FILE)
      .pipe(csv())
      .on('data', (data) => rows.push(data))
      .on('end', async () => {
          console.log(`Loaded ${rows.length} items from CSV.`);

          const browser = await chromium.launch({ headless: true });
          const context = await browser.newContext({
              userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
          });
          const page = await context.newPage();

          // Skip gift cards for the initial test run to see real logistics math
          const itemsToProcess = rows.filter(r => !r.SKU.startsWith('GC-')).slice(0, 10);
          console.log(`Processing 10 hardware items for verification...`);

          for (const row of itemsToProcess) {
              const result = await processRow(row, page);
              if (result) {
                  results.push(result);
              }
              await page.waitForTimeout(2000); // Polite delay
          }

          await browser.close();

          fs.mkdirSync('output/sync', { recursive: true });

          const csvWriter = createObjectCsvWriter({
              path: OUTPUT_FILE,
              header: [
                  {id: 'sku', title: 'SKU'},
                  {id: 'name', title: 'Name'},
                  {id: 'source_price_usd', title: 'Source_Price_USD'},
                  {id: 'landed_cost_sar', title: 'Landed_Cost_SAR'},
                  {id: 'ksa_market_ref', title: 'KSA_Market_Ref'},
                  {id: 'final_sale_price', title: 'Final_Sale_Price'},
                  {id: 'warning', title: 'Notes'}
              ]
          });

          await csvWriter.writeRecords(results);
          console.log(`\n✅ Sync complete. Results saved to ${OUTPUT_FILE}`);
      });
}

run();
