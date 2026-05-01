const fs = require('fs');
const csv = require('csv-parser');

const DAILY_SYNC_FILE = 'output/sync/price-sync-daily.csv';
const MARKET_SYNC_FILE = 'output/sync/price-sync-live.csv';

async function readCSV(filePath) {
    return new Promise((resolve) => {
        const results = [];
        if (!fs.existsSync(filePath)) return resolve(results);
        fs.createReadStream(filePath)
            .pipe(csv())
            .on('data', (data) => results.push(data))
            .on('end', () => resolve(results));
    });
}

async function run() {
    console.log("🛡️ NeoGen Price Validator: Sanity Checking Output...");
    
    const dailyUpdates = await readCSV(DAILY_SYNC_FILE);
    const marketData = await readCSV(MARKET_SYNC_FILE);
    
    const marketMap = {};
    marketData.forEach(row => {
        marketMap[row.SKU] = parseFloat(row.Landed_Cost_SAR);
    });
    
    let errors = 0;
    
    dailyUpdates.forEach(update => {
        const sku = update.sku;
        const salePrice = parseFloat(update.sale_price);
        const landedCost = marketMap[sku];
        
        if (landedCost && salePrice < landedCost) {
            console.error(`🚨 ERROR: [${sku}] Sale Price (${salePrice}) is LOWER than Landed Cost (${landedCost})!`);
            errors++;
        }
        
        if (salePrice <= 0) {
            console.error(`🚨 ERROR: [${sku}] Invalid Sale Price: ${salePrice}`);
            errors++;
        }
    });
    
    if (errors > 0) {
        console.log(`\n❌ Validation Failed with ${errors} error(s). ABORTING PUSH.`);
        process.exit(1);
    } else {
        console.log(`\n✅ Validation Passed. ${dailyUpdates.length} items verified for margin safety.`);
    }
}

run();
