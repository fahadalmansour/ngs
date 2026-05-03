# W5 — Daily Sales Report → Telegram

**Trigger:** Schedule (every day at 11:00 PM Riyadh time)
**What it does:** Pulls all orders from today via WooCommerce REST API, calculates total revenue and order count, sends a summary to Telegram.
**Why it matters:** End-of-day snapshot without logging into WP admin.

---

## Nodes

### 1. Schedule Trigger
- **Node type:** `Schedule Trigger`
- **Rule:** Every day at `23:00`
- **Timezone:** `Asia/Riyadh`

---

### 2. Code — Build Date Range
- **Node type:** `Code`
```javascript
const now = new Date();
const riyadhOffset = 3 * 60; // UTC+3
const localNow = new Date(now.getTime() + riyadhOffset * 60000);

const year = localNow.getUTCFullYear();
const month = String(localNow.getUTCMonth() + 1).padStart(2, '0');
const day = String(localNow.getUTCDate()).padStart(2, '0');

const dateFrom = `${year}-${month}-${day}T00:00:00`;
const dateTo   = `${year}-${month}-${day}T23:59:59`;

return [{ json: { dateFrom, dateTo, dateLabel: `${day}/${month}/${year}` } }];
```

---

### 3. HTTP Request — Get Today's Orders
- **Node type:** `HTTP Request`
- **Method:** `GET`
- **URL:**
  ```
  https://neogen.store/wp-json/wc/v3/orders?after={{ $json.dateFrom }}&before={{ $json.dateTo }}&per_page=100&status=processing,completed
  ```
- **Authentication:** Basic Auth (WooCommerce Consumer Key / Secret)
- **Response Format:** `JSON`

---

### 4. Code — Calculate Summary
- **Node type:** `Code`
```javascript
const orders = $input.all().map(p => p.json);
const dateLabel = $('Code — Build Date Range').first().json.dateLabel;

const totalRevenue = orders.reduce((sum, o) => sum + parseFloat(o.total || 0), 0);
const completed = orders.filter(o => o.status === 'completed').length;
const processing = orders.filter(o => o.status === 'processing').length;

// Top products
const productMap = {};
orders.forEach(order => {
  (order.line_items || []).forEach(item => {
    if (!productMap[item.name]) productMap[item.name] = 0;
    productMap[item.name] += item.quantity;
  });
});
const topProducts = Object.entries(productMap)
  .sort((a, b) => b[1] - a[1])
  .slice(0, 5)
  .map(([name, qty]) => `  • ${name} ×${qty}`);

const message = [
  `📊 *NeoGen Daily Sales — ${dateLabel}*`,
  ``,
  `🛒 Orders: ${orders.length} (${completed} completed, ${processing} processing)`,
  `💰 Revenue: ${totalRevenue.toFixed(2)} SAR`,
  ``,
  topProducts.length ? `🏆 Top Products:\n${topProducts.join('\n')}` : `No orders today.`,
  ``,
  `🔗 View orders: https://neogen.store/wp-admin/edit.php?post_type=shop_order`
].join('\n');

return [{ json: { message, orderCount: orders.length, revenue: totalRevenue } }];
```

---

### 5. Telegram — Send Report
- **Node type:** `Telegram`
- **Operation:** `Send Message`
- **Chat ID:** Your Telegram chat ID
- **Text:** `{{ $json.message }}`
- **Parse Mode:** `Markdown`

---

## Wiring

```
Schedule Trigger
    → Code (date range)
    → HTTP Request (get orders)
    → Code (calculate summary)
    → Telegram
```

---

## Optional Additions

- **Google Sheets**: Add a `Google Sheets` node after the summary code to append a daily row to a sales tracking spreadsheet.
- **Weekly rollup**: Duplicate this workflow, change trigger to Monday 08:00, change the date range to last 7 days.
- **Zero-order alert**: Add an IF node — if `orderCount = 0`, send a different message ("No orders today — check if site is up").

---

## Testing

1. Manually trigger the workflow.
2. Check the **HTTP Request** output — confirm orders array is not empty (or intentionally empty if today had no orders).
3. Read the **Code** output for the formatted message.
4. Verify Telegram message arrives correctly formatted.
