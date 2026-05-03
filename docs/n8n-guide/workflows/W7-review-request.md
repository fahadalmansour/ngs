# W7 — Post-Purchase Review Request Email

**Trigger:** WooCommerce Webhook — `order.updated` (status → completed)
**What it does:** 3 days after an order is marked "Completed", sends an email asking the customer to leave a product review.
**Why it matters:** Social proof is critical for a new Saudi store. Reviews directly affect Google Shopping ranking.

---

## How the Delay Works

n8n doesn't have a "wait 3 days" node in the free tier. Two options:

**Option A — n8n Wait node** (cloud/paid or self-hosted with queue mode)
Use the built-in `Wait` node, set to 3 days. Simple, but requires queue-mode execution.

**Option B — Google Sheets + daily check** (works on any self-hosted n8n)
1. When order completes → write row to Google Sheet with order ID, email, product, and `send_after` timestamp (now + 3 days).
2. A second workflow runs daily → reads the sheet → finds rows where `send_after <= today` → sends email → marks row `sent`.

This guide uses **Option B** as it works on any n8n instance.

---

## Sub-workflow A: Log Completed Orders

### Nodes

#### 1. Webhook — Order Completed
- **Node type:** `Webhook`
- **Path:** `order-completed`

In WooCommerce: Webhooks → Add → Topic: `Order updated` → URL: `https://n8n.neogen.store/webhook/order-completed`

> Filter in code below — only process status = completed.

#### 2. Code — Check Status + Build Row
```javascript
const order = $input.first().json;

// Only continue for completed orders
if (order.status !== 'completed') {
  return []; // stops execution
}

const sendAfter = new Date(Date.now() + 3 * 24 * 60 * 60 * 1000)
  .toISOString().split('T')[0]; // YYYY-MM-DD

const productNames = (order.line_items || []).map(i => i.name).join(', ');
const productIds = (order.line_items || []).map(i => i.product_id).join(',');

return [{
  json: {
    orderId: order.id,
    email: order.billing.email,
    firstName: order.billing.first_name || '',
    productNames,
    productIds,
    sendAfter,
    sent: 'no'
  }
}];
```

#### 3. Google Sheets — Append Row
- **Node type:** `Google Sheets`
- **Operation:** `Append`
- **Spreadsheet:** `NeoGen Review Queue`
- **Sheet:** `Sheet1`
- **Columns:** `orderId, email, firstName, productNames, productIds, sendAfter, sent`

---

## Sub-workflow B: Send Due Review Requests (Daily)

### Nodes

#### 1. Schedule Trigger
- Every day at `10:00` Asia/Riyadh

#### 2. Google Sheets — Read All Rows
- **Operation:** `Get All`
- **Spreadsheet:** `NeoGen Review Queue`
- **Sheet:** `Sheet1`
- **Return All:** true

#### 3. Code — Filter Due + Unsent
```javascript
const today = new Date().toISOString().split('T')[0];
const rows = $input.all().map(r => r.json);

const due = rows.filter(r =>
  r.sent === 'no' && r.sendAfter <= today
);

return due.map(r => ({ json: r }));
```

#### 4. Send Email — Review Request
- **To:** `{{ $json.email }}`
- **Subject:** `كيف كانت تجربتك؟ — How was your order, {{ $json.firstName }}?`
- **HTML:**
```html
<div dir="rtl" style="font-family: Arial; padding: 20px; max-width: 600px;">
  <h2>مرحباً {{ $json.firstName }}! ⭐</h2>
  <p>نأمل أن تكون راضياً عن طلبك: <strong>{{ $json.productNames }}</strong></p>
  <p>رأيك يساعدنا ويساعد المشترين الآخرين. هل يمكنك تقييم تجربتك؟</p>
  <a href="https://neogen.store/my-account/orders/{{ $json.orderId }}/" 
     style="background: #0f3460; color: white; padding: 12px 25px; 
            text-decoration: none; border-radius: 5px; display: inline-block;">
    اكتب تقييمك الآن
  </a>
  <p style="margin-top: 20px; font-size: 12px; color: #888;">
    NeoGen Store — شكراً لثقتك بنا
  </p>
</div>
```

#### 5. Google Sheets — Mark as Sent
- **Operation:** `Update`
- **Spreadsheet:** `NeoGen Review Queue`
- **Row Number:** `{{ $json.row_number }}` (n8n adds this automatically when reading sheets)
- **Column:** `sent` → value: `yes`

---

## Wiring — Sub-workflow A
```
Webhook (order-completed)
    → Code (check status + build row)
    → Google Sheets (append)
```

## Wiring — Sub-workflow B
```
Schedule Trigger
    → Google Sheets (read all)
    → Code (filter due + unsent)
    → Send Email
    → Google Sheets (mark sent)
```

---

## Notes

- The `row_number` field is automatically added by n8n's Google Sheets node when using "Get All".
- If you have many orders, add pagination to the Google Sheets read (or move to a DB).
- Review link can also go directly to a specific product review tab: `https://neogen.store/?p={{ $json.productIds }}#tab-reviews`
