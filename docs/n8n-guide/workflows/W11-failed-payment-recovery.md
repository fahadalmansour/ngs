# W11 — Failed Payment Recovery

**Trigger:** WooCommerce Webhook — `order.updated` (status → failed)
**What it does:** When an order payment fails, waits 30 minutes then sends a recovery email with a direct checkout link. If still unpaid after 24 hours, sends a WhatsApp follow-up.
**Why it matters:** Failed payments are lost revenue. A timed recovery sequence recovers 15–30% of failed checkouts.

---

## Architecture

Two sub-workflows:

- **A**: Catches failed orders, writes to Google Sheet with scheduled retry timestamps.
- **B**: Runs on schedule, checks sheet, sends recovery messages at the right time.

---

## Sub-workflow A: Log Failed Orders

### 1. Webhook — Order Failed
- **Path:** `order-failed`
- In WooCommerce: Topic: `Order updated` → URL: `https://n8n.neogen.store/webhook/order-failed`

### 2. Code — Filter Failed + Build Row
```javascript
const order = $input.first().json;

if (order.status !== 'failed') return [];

const now = Date.now();
const email30min = new Date(now + 30 * 60 * 1000).toISOString();
const whatsapp24h = new Date(now + 24 * 60 * 60 * 1000).toISOString();

const products = (order.line_items || []).map(i => i.name).join(', ');

return [{
  json: {
    orderId: order.id,
    email: order.billing.email,
    firstName: order.billing.first_name || '',
    phone: order.billing.phone || '',
    products,
    total: parseFloat(order.total).toFixed(2),
    checkoutUrl: `https://neogen.store/checkout/order-pay/${order.id}/?pay_for_order=true&key=${order.order_key}`,
    email30min,
    whatsapp24h,
    emailSent: 'no',
    whatsappSent: 'no'
  }
}];
```

### 3. Google Sheets — Append Row
- **Spreadsheet:** `NeoGen Failed Orders`
- **Sheet:** `Recovery`
- **Columns:** `orderId, email, firstName, phone, products, total, checkoutUrl, email30min, whatsapp24h, emailSent, whatsappSent`

---

## Sub-workflow B: Send Recovery Messages

### 1. Schedule Trigger
- Every `15 minutes`

### 2. Google Sheets — Read All
- Get all rows from `NeoGen Failed Orders` → `Recovery`

### 3. Code — Find Due Items
```javascript
const now = new Date().toISOString();
const rows = $input.all().map(r => r.json);

const emailDue = rows.filter(r =>
  r.emailSent === 'no' && r.email30min <= now
);

const whatsappDue = rows.filter(r =>
  r.whatsappSent === 'no' && r.whatsapp24h <= now
);

// Combine with action type
const allDue = [
  ...emailDue.map(r => ({ ...r, action: 'email' })),
  ...whatsappDue.map(r => ({ ...r, action: 'whatsapp' }))
];

return allDue.map(r => ({ json: r }));
```

### 4. IF — Email or WhatsApp?
- **Condition:** `{{ $json.action }}` **equals** `email`
- **True** → Send Email node
- **False** → WhatsApp node

### 5a. Send Email — Recovery Email
- **To:** `{{ $json.email }}`
- **Subject:** `إتمام طلبك من نيوجين ستور — Complete your order`
- **HTML:**
```html
<div dir="rtl" style="font-family: Arial; padding: 20px; max-width: 600px;">
  <h2>لم يكتمل طلبك بعد 🛒</h2>
  <p>مرحباً {{ $json.firstName }},</p>
  <p>لاحظنا أن عملية الدفع لطلبك لم تكتمل.</p>
  <p><strong>المنتجات:</strong> {{ $json.products }}</p>
  <p><strong>الإجمالي:</strong> {{ $json.total }} SAR</p>
  <a href="{{ $json.checkoutUrl }}" 
     style="background: #e63946; color: white; padding: 14px 30px; 
            text-decoration: none; border-radius: 5px; display: inline-block; margin: 15px 0;">
    أكمل الدفع الآن
  </a>
  <p style="font-size: 12px; color: #888;">
    الرابط صالح لمدة 48 ساعة. إذا واجهت مشكلة، تواصل معنا عبر واتساب.
  </p>
</div>
```

After sending: update Google Sheet row → `emailSent = yes`

### 5b. HTTP Request — WhatsApp Follow-up
- **URL:** `https://api.ultramsg.com/{{ $env.ULTRAMSG_INSTANCE }}/messages/chat`
- **Body:**
```json
{
  "token": "{{ $env.ULTRAMSG_TOKEN }}",
  "to": "{{ $json.phone }}",
  "body": "مرحباً {{ $json.firstName }}،\n\nلاحظنا أن طلبك من نيوجين ستور ({{ $json.products }}) لم يكتمل.\n\nأكمل الدفع هنا:\n{{ $json.checkoutUrl }}\n\nإجمالي الطلب: {{ $json.total }} SAR\n\nنحن هنا إذا احتجت مساعدة 🙏"
}
```

After sending: update Google Sheet row → `whatsappSent = yes`

---

## Wiring — Sub-workflow A
```
Webhook (order-failed)
    → Code (filter + build row)
    → Google Sheets (append)
```

## Wiring — Sub-workflow B
```
Schedule (every 15 min)
    → Google Sheets (read all)
    → Code (find due items)
    → IF (email or whatsapp?)
        email → Send Email → Sheets (mark emailSent=yes)
        whatsapp → HTTP Request (UltraMsg) → Sheets (mark whatsappSent=yes)
```

---

## Notes

- The direct `checkoutUrl` uses WooCommerce's order-pay endpoint — no re-login required for the customer.
- If phone number is missing, the WhatsApp node will fail silently. Add an IF check: if `phone` is empty → skip WhatsApp.
- Saudi phone numbers should include country code: `966XXXXXXXXX` (no leading +).
- Consider adding a third touchpoint at 48h with a small discount code to seal the deal.
