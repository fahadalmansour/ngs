# W4 — Low Stock Alert → Telegram

**Trigger:** Schedule (every morning at 8:00 AM Riyadh time)
**What it does:** Queries WooCommerce for all products with stock ≤ 5 units, formats a summary, sends it to your Telegram.
**Why it matters:** Prevents selling out-of-stock items that stay published, which hurts customer trust and ZATCA compliance.

---

## Nodes

### 1. Schedule Trigger
- **Node type:** `Schedule Trigger`
- **Rule:** Every day at `08:00`
- **Timezone:** `Asia/Riyadh`

---

### 2. WooCommerce — Get Low Stock Products
- **Node type:** `HTTP Request`
- **Method:** `GET`
- **URL:**
  ```
  https://neogen.store/wp-json/wc/v3/products?stock_status=instock&per_page=100&orderby=stock_quantity&order=asc
  ```
- **Authentication:** `Header Auth`
  - Add header `Authorization` → `Basic {{ Base64(ck_773d963e...:cs_d8e032...) }}`
  - Tip: In n8n use the **WooCommerce credential** instead: set Auth Type = `Basic Auth`, Username = Consumer Key, Password = Consumer Secret.
- **Response Format:** `JSON`

> You can also use the built-in **WooCommerce node** → Operation: `Get All` → Resource: `Product` → add Filter: `stock_status = instock`.

---

### 3. Code — Filter ≤ 5 Units
- **Node type:** `Code`
- **Language:** JavaScript
```javascript
const products = $input.all();
const lowStock = products
  .map(p => p.json)
  .filter(p => p.stock_quantity !== null && p.stock_quantity <= 5);

return lowStock.map(p => ({
  json: {
    id: p.id,
    name: p.name,
    sku: p.sku,
    stock: p.stock_quantity,
    price: p.price,
    url: p.permalink
  }
}));
```

---

### 4. IF — Any Low Stock Items?
- **Node type:** `IF`
- **Condition:** `{{ $input.all().length }}` **greater than** `0`
- **True branch** → goes to format message
- **False branch** → `No Operation` (do nothing)

---

### 5. Code — Format Telegram Message
- **Node type:** `Code`
- **Language:** JavaScript
```javascript
const items = $input.all().map(p => p.json);
const lines = items.map(p =>
  `• ${p.name} (SKU: ${p.sku || 'N/A'}) — ${p.stock} units left`
);

const body = [
  `⚠️ *NeoGen Low Stock Alert*`,
  `📅 ${new Date().toLocaleDateString('en-SA', { timeZone: 'Asia/Riyadh' })}`,
  ``,
  ...lines,
  ``,
  `${items.length} product(s) need restocking.`
].join('\n');

return [{ json: { message: body } }];
```

---

### 6. Telegram — Send Alert
- **Node type:** `Telegram`
- **Credential:** Your Telegram Bot credential
  - Create bot via [@BotFather](https://t.me/BotFather) → get token
  - In n8n: Credentials → New → Telegram → paste token
- **Operation:** `Send Message`
- **Chat ID:** Your personal Telegram chat ID (send `/start` to your bot, then call `https://api.telegram.org/bot<TOKEN>/getUpdates` to find your chat ID)
- **Text:** `{{ $json.message }}`
- **Parse Mode:** `Markdown`

---

## Wiring

```
Schedule Trigger
    → HTTP Request (get products)
    → Code (filter ≤5)
    → IF (any?)
        TRUE → Code (format message) → Telegram
        FALSE → No Operation
```

---

## Testing

1. Set the Schedule Trigger to **Manual trigger** temporarily.
2. Click **Test workflow**.
3. Check the Code node output — verify the filtered list is correct.
4. Check your Telegram — message should arrive.
5. Switch back to Schedule trigger when satisfied.

---

## Notes

- If you want to alert on **out-of-stock** products too, change the filter URL to `stock_status=outofstock` and merge both HTTP calls with a **Merge** node.
- To also send to WhatsApp, duplicate the Telegram node and replace with a **HTTP Request** to the WhatsApp Business API or UltraMsg.
