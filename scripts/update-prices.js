const { chromium } = require('playwright');
const fs = require('fs');
const csv = require('csv-parser');

const UPDATE_FILE = 'output/sync/price-sync-daily.csv';
// Note: In a real scenario, use environment variables for these
const WP_ADMIN_URL = 'https://neogen.store/wp-admin';
const USERNAME = process.env.WP_USERNAME || 'admin'; 
const PASSWORD = process.env.WP_PASSWORD || ''; 

async function readUpdates() {
    return new Promise((resolve) => {
        const results = [];
        if (!fs.existsSync(UPDATE_FILE)) return resolve(results);
        fs.createReadStream(UPDATE_FILE)
            .pipe(csv())
            .on('data', (data) => results.push(data))
            .on('end', () => resolve(results));
    });
}

(async () => {
    const updates = await readUpdates();
    if (updates.length === 0) {
        console.log("No updates to process.");
        return;
    }

    console.log(`🚀 Activating NeoGen Price Sentinel: Updating ${updates.length} items...`);

    const browser = await chromium.launch({ headless: true });
    const page = await browser.newPage();

    try {
        // 1. Login to WordPress
        await page.goto(`${WP_ADMIN_URL}/login.php`);
        await page.fill('#user_login', USERNAME);
        await page.fill('#user_pass', PASSWORD);
        await page.click('#wp-submit');
        await page.waitForNavigation();

        for (const item of updates) {
            console.log(`Updating [${item.sku}]...`);
            
            // 2. Navigate to Product Search in WooCommerce
            await page.goto(`${WP_ADMIN_URL}/edit.php?post_type=product&s=${item.sku}`);
            
            // 3. Click Quick Edit (assuming first result matches SKU)
            await page.hover('.row-title');
            await page.click('.editinline');
            
            // 4. Update Regular and Sale Price
            await page.fill('input[name="_regular_price"]', item.regular_price);
            await page.fill('input[name="_sale_price"]', item.sale_price);
            
            // 5. Save
            await page.click('.save');
            await page.waitForTimeout(2000); // Wait for AJAX save and UI refresh

            // 6. Visual Confirmation: Take a screenshot of the updated row
            fs.mkdirSync('output/screenshots', { recursive: true });
            await page.screenshot({ 
                path: `output/screenshots/confirm-${item.sku}.png`,
                fullPage: false,
                clip: { x: 0, y: 0, width: 1280, height: 400 } // Focus on the top result
            });

            console.log(`✅ Updated ${item.sku}. Screenshot saved to output/screenshots/confirm-${item.sku}.png`);
        }

    } catch (error) {
        console.error("❌ Fatal Sync Error:", error);
    } finally {
        await browser.close();
        console.log("🏁 Price Sync Session Ended.");
    }
})();
