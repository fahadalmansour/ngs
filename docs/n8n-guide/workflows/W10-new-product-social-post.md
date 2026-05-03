# W10 — New Product → Auto Social Post (Telegram Channel + Twitter/X)

**Trigger:** WooCommerce Webhook — `product.created`
**What it does:** When a new product is published, formats a product announcement and posts it to your Telegram channel and (optionally) Twitter/X.
**Why it matters:** Reduces manual posting. Every new product is announced automatically.

---

## Prerequisites

- A public **Telegram channel** for NeoGen announcements (e.g. @NeoGenStore)
- Your Telegram Bot must be added as **admin** to that channel
- (Optional) Twitter/X Developer account with a project and app credentials

---

## Nodes

### 1. Webhook — Product Created
- **Node type:** `Webhook`
- **Path:** `new-product`

In WooCommerce: Webhooks → Add → Topic: `Product created` → URL: `https://n8n.neogen.store/webhook/new-product`

---

### 2. Code — Check Status + Extract Data
```javascript
const product = $input.first().json;

// Only announce published products
if (product.status !== 'publish') {
  return [];
}

const price = product.sale_price || product.regular_price || product.price;
const categories = (product.categories || []).map(c => c.name).join(', ');
const image = (product.images || [])[0]?.src || null;

return [{
  json: {
    id: product.id,
    name: product.name,
    sku: product.sku,
    price: parseFloat(price).toFixed(2),
    categories,
    description: product.short_description
      ? product.short_description.replace(/<[^>]+>/g, '').slice(0, 200)
      : '',
    url: product.permalink,
    image,
    inStock: product.stock_status === 'instock'
  }
}];
```

---

### 3. Code — Format Telegram Message
```javascript
const p = $input.first().json;

const stockBadge = p.inStock ? '✅ متوفر' : '⏳ قريباً';
const message = [
  `🆕 *منتج جديد في نيوجين ستور!*`,
  ``,
  `📦 *${p.name}*`,
  p.sku ? `🔖 SKU: ${p.sku}` : null,
  `🏷️ الفئة: ${p.categories}`,
  `💰 السعر: *${p.price} SAR*`,
  `${stockBadge}`,
  p.description ? `\n📝 ${p.description}` : null,
  ``,
  `🛒 اطلب الآن: ${p.url}`
].filter(Boolean).join('\n');

return [{ json: { ...p, telegramMessage: message } }];
```

---

### 4a. Telegram — Send to Channel (Text Only)
- **Node type:** `Telegram`
- **Operation:** `Send Message`
- **Chat ID:** `@NeoGenStoreChannel` (your channel username, with @)
- **Text:** `{{ $json.telegramMessage }}`
- **Parse Mode:** `Markdown`

---

### 4b. Telegram — Send Photo + Caption (if image exists)

Replace 4a with this if your products always have images:

- **Node type:** `HTTP Request`
- **Method:** `POST`
- **URL:** `https://api.telegram.org/bot{{ $env.TELEGRAM_BOT_TOKEN }}/sendPhoto`
- **Body (JSON):**
```json
{
  "chat_id": "@NeoGenStoreChannel",
  "photo": "{{ $json.image }}",
  "caption": "{{ $json.telegramMessage }}",
  "parse_mode": "Markdown"
}
```

Use an **IF** node before this step: if `image` is not null → send photo, else → send text.

---

### 5. (Optional) Twitter/X Post
- **Node type:** `HTTP Request`
- **Method:** `POST`
- **URL:** `https://api.twitter.com/2/tweets`
- **Authentication:** `OAuth2 (Twitter)`
  - Requires Twitter Developer App with OAuth 2.0 + write permissions
- **Body (JSON):**
```json
{
  "text": "🆕 New at NeoGen Store: {{ $json.name }} — {{ $json.price }} SAR\n\n{{ $json.url }}\n\n#NeoGenStore #SmartHome #Networking #Riyadh"
}
```

> Twitter's free API tier (Basic) allows 1,500 tweets/month write access. Sufficient for a product store.

---

## Wiring

```
Webhook (new-product)
    → Code (check status + extract)
    → Code (format message)
    → IF (has image?)
        YES → HTTP Request (Telegram sendPhoto)
        NO  → Telegram node (sendMessage)
    → (Optional) HTTP Request (Twitter post)
```

---

## Notes

- **Drafts**: If you create products as Draft and only publish later, the `product.updated` webhook topic fires when status changes to `publish`. Use that topic instead and add a check: `if (product.status === 'publish' && $input.first().json.status === 'publish')`.
- **Rate limiting**: If you bulk-import products (e.g. 50 at once via WP All Import), all 50 webhooks fire simultaneously. Add a `Wait` node (1 minute) or route through a queue to avoid Telegram rate limits.
- **Instagram**: Requires Facebook Business Manager + Instagram Graph API. More complex — consider Zapier for this specific channel.
