const fs = require('fs');
const path = require('path');
const csv = require('csv-parser');
const { createObjectCsvWriter } = require('csv-writer');
const XLSX = require('xlsx');

const SYNC_RESULTS_FILE = 'output/sync/price-sync-live.csv';
const BLUEPRINT_FILE = 'data/catalogs/master/Neogen_Master_Catalog_Blueprint.xlsx';
const OUTPUT_FILE = 'output/sync/price-sync-daily.csv';

// NeoGen Pricing Rules
const TARGET_MARGIN = 0.15; // 15% margin over landed cost
const MARKET_VARIANCE_THRESHOLD = 0.05; // Stay within 5% of market price

async function readSyncResults() {
    return new Promise((resolve) => {
        const results = {};
        if (!fs.existsSync(SYNC_RESULTS_FILE)) return resolve(results);
        
        fs.createReadStream(SYNC_RESULTS_FILE)
            .pipe(csv())
            .on('data', (data) => {
                results[data.SKU] = {
                    landedCost: parseFloat(data.Landed_Cost_SAR),
                    marketRef: data.KSA_Market_Ref === 'N/A' ? null : parseFloat(data.KSA_Market_Ref)
                };
            })
            .on('end', () => resolve(results));
    });
}

function readBlueprint() {
    if (!fs.existsSync(BLUEPRINT_FILE)) {
        console.error(`Blueprint file ${BLUEPRINT_FILE} not found.`);
        return [];
    }
    const workbook = XLSX.readFile(BLUEPRINT_FILE);
    const sheetName = workbook.SheetNames[0];
    const worksheet = workbook.Sheets[sheetName];
    return XLSX.utils.sheet_to_json(worksheet);
}

async function run() {
    console.log("🏛️ NeoGen Pricing Governor: Starting Decision Pipeline...");
    
    const syncResults = await readSyncResults();
    const blueprintRows = readBlueprint();
    
    const updates = [];

    blueprintRows.forEach(row => {
        const sku = row['SKU'];
        const pricingStatus = row['Pricing Status'];
        
        if (pricingStatus === 'Needs Reprice' && syncResults[sku]) {
            const syncData = syncResults[sku];
            const landedCost = syncData.landedCost;
            
            // Priority: Use scraped market ref, fallback to blueprint's 'Amazon SA Ref Price'
            const marketRef = syncData.marketRef || parseFloat(row['Amazon SA Ref Price']);
            
            let suggestedSalePrice = landedCost * (1 + TARGET_MARGIN);
            
            if (marketRef && !isNaN(marketRef)) {
                const lowerBound = marketRef * (1 - MARKET_VARIANCE_THRESHOLD);
                const upperBound = marketRef * (1 + MARKET_VARIANCE_THRESHOLD);
                
                if (suggestedSalePrice < lowerBound) {
                    suggestedSalePrice = lowerBound;
                } else if (suggestedSalePrice > upperBound) {
                    suggestedSalePrice = upperBound;
                }
            }
            
            const absoluteFloor = landedCost * 1.05;
            if (suggestedSalePrice < absoluteFloor) {
                suggestedSalePrice = absoluteFloor;
            }

            updates.push({
                sku: sku,
                regular_price: (suggestedSalePrice * 1.15).toFixed(2),
                sale_price: suggestedSalePrice.toFixed(2)
            });
            
            console.log(`✅ Repriced [${sku}]: Landed ${landedCost.toFixed(2)} -> Sale ${suggestedSalePrice.toFixed(2)}`);
        }
    });

    if (updates.length > 0) {
        fs.mkdirSync(path.dirname(OUTPUT_FILE), { recursive: true });
        const csvWriter = createObjectCsvWriter({
            path: OUTPUT_FILE,
            header: [
                {id: 'sku', title: 'sku'},
                {id: 'regular_price', title: 'regular_price'},
                {id: 'sale_price', title: 'sale_price'}
            ]
        });
        await csvWriter.writeRecords(updates);
        console.log(`\n🚀 Generated ${OUTPUT_FILE} with ${updates.length} updates.`);
    } else {
        console.log("\n⏸️ No items currently flagged for 'Needs Reprice' with valid sync data.");
    }
}

run();
