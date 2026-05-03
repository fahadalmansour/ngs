# W9 — ZATCA Invoice Generation Notification

**Trigger:** WooCommerce Webhook — `order.created`
**What it does:** When a new order is placed, triggers invoice generation and notifies the customer that their ZATCA-compliant invoice is ready.
**Why it matters:** ZATCA Phase 2 requires e-invoices with QR codes for all B2C transactions. This workflow bridges WooCommerce orders with the invoice generation process.

---

## Prerequisites

- WooCommerce PDF Invoices Pro plugin installed (see `docs/ops/LEGAL-COMPLIANCE-CHECKLIST.md` — €59/yr)
- Plugin must expose a REST endpoint or webhook for invoice generation
- Alternative: use the plugin's built-in email — this workflow adds a separate ZATCA-specific notification

---

## Nodes

### 1. Webhook — New Order
- **Node type:** `Webhook`
- **Path:** `new-order-invoice`
- **Method:** `POST`

In WooCommerce: Webhooks → Add → Topic: `Order created` → URL: `https://n8n.neogen.store/webhook/new-order-invoice`

---

### 2. Code — Extract Invoice Data
```javascript
const order = $input.first().json;

const items = (order.line_items || []).map(item => ({
  name: item.name,
  qty: item.quantity,
  unitPrice: parseFloat(item.price),
  total: parseFloat(item.total)
}));

const subtotal = parseFloat(order.subtotal || order.total) / 1.15;
const vat = parseFloat(order.total) - subtotal;

const invoiceData = {
  orderId: order.id,
  orderKey: order.order_key,
  date: new Date().toISOString().split('T')[0],
  customer: {
    name: `${order.billing.first_name} ${order.billing.last_name}`.trim(),
    email: order.billing.email,
    phone: order.billing.phone,
    address: [
      order.billing.address_1,
      order.billing.city,
      order.billing.postcode,
      order.billing.country
    ].filter(Boolean).join(', ')
  },
  items,
  subtotal: subtotal.toFixed(2),
  vat: vat.toFixed(2),
  total: parseFloat(order.total).toFixed(2),
  paymentMethod: order.payment_method_title,
  invoiceUrl: `https://neogen.store/wp-admin/admin-ajax.php?action=generate_wpo_wcpdf&template_type=invoice&order_id=${order.id}`
};

return [{ json: invoiceData }];
```

---

### 3. HTTP Request — Trigger PDF Invoice Generation
- **Node type:** `HTTP Request`
- **Method:** `GET`
- **URL:**
  ```
  https://neogen.store/wp-json/wc/v3/orders/{{ $json.orderId }}
  ```
- **Auth:** Basic (WooCommerce keys)
- **Purpose:** Confirms order exists and triggers any invoice plugin hooks.

> If your PDF Invoices plugin has its own REST endpoint, call it here instead.

---

### 4. Code — Build Invoice Email HTML
```javascript
const d = $('Code — Extract Invoice Data').first().json;

const itemRows = d.items.map(item =>
  `<tr>
    <td style="padding:8px;border:1px solid #ddd;">${item.name}</td>
    <td style="padding:8px;border:1px solid #ddd;text-align:center;">${item.qty}</td>
    <td style="padding:8px;border:1px solid #ddd;text-align:right;">${item.unitPrice.toFixed(2)}</td>
    <td style="padding:8px;border:1px solid #ddd;text-align:right;">${item.total.toFixed(2)}</td>
  </tr>`
).join('');

const html = `
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial; padding: 20px; background: #f9f9f9;">
<div style="max-width: 650px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px;">

  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
      <h2 style="margin: 0; color: #0f3460;">فاتورة ضريبية</h2>
      <small style="color: #888;">Tax Invoice</small>
    </div>
    <div style="text-align: left;">
      <strong>نيوجين ستور</strong><br>
      <small>CR: 7053130576</small><br>
      <small>VAT: 3145127947</small>
    </div>
  </div>

  <table style="width:100%;margin-bottom:15px;">
    <tr>
      <td><strong>رقم الفاتورة:</strong> INV-${d.orderId}</td>
      <td style="text-align:left;"><strong>التاريخ:</strong> ${d.date}</td>
    </tr>
    <tr>
      <td><strong>العميل:</strong> ${d.customer.name}</td>
      <td style="text-align:left;"><strong>طريقة الدفع:</strong> ${d.paymentMethod}</td>
    </tr>
  </table>

  <table style="width:100%;border-collapse:collapse;margin-bottom:20px;">
    <thead>
      <tr style="background:#0f3460;color:white;">
        <th style="padding:8px;text-align:right;">المنتج</th>
        <th style="padding:8px;text-align:center;">الكمية</th>
        <th style="padding:8px;text-align:right;">سعر الوحدة</th>
        <th style="padding:8px;text-align:right;">الإجمالي</th>
      </tr>
    </thead>
    <tbody>${itemRows}</tbody>
  </table>

  <div style="text-align:left;">
    <table>
      <tr><td style="padding:4px 15px;">المجموع قبل الضريبة:</td><td><strong>${d.subtotal} SAR</strong></td></tr>
      <tr><td style="padding:4px 15px;">ضريبة القيمة المضافة (15%):</td><td><strong>${d.vat} SAR</strong></td></tr>
      <tr style="font-size:1.1em;background:#f0f0f0;">
        <td style="padding:6px 15px;"><strong>الإجمالي الكلي:</strong></td>
        <td><strong>${d.total} SAR</strong></td>
      </tr>
    </table>
  </div>

  <div style="margin-top:20px;padding:15px;background:#fff8e1;border-radius:5px;text-align:center;">
    <p style="margin:0;font-size:12px;color:#666;">
      QR Code — يُولَّد بواسطة نظام الفوترة الإلكترونية (ZATCA Phase 2)<br>
      <em>Attached as PDF to this email</em>
    </p>
  </div>

  <p style="margin-top:20px;font-size:11px;color:#aaa;text-align:center;">
    هذه الفاتورة صادرة إلكترونياً وفق متطلبات هيئة الزكاة والضريبة والجمارك
  </p>

</div>
</body>
</html>
`;

return [{ json: { ...d, html } }];
```

---

### 5. Send Email — Invoice to Customer
- **Node type:** `Send Email`
- **From:** `NeoGen Store <invoices@neogen.store>`
- **To:** `{{ $json.customer.email }}`
- **Subject:** `فاتورتك من نيوجين ستور — Order #{{ $json.orderId }}`
- **Email Format:** `HTML`
- **HTML:** `{{ $json.html }}`

> The PDF attachment with the ZATCA QR code is generated by WooCommerce PDF Invoices Pro and sent via its own email. This n8n email provides an additional HTML invoice summary.

---

## Wiring

```
Webhook (new-order-invoice)
    → Code (extract invoice data)
    → HTTP Request (trigger/confirm order)
    → Code (build HTML)
    → Send Email (invoice to customer)
```

---

## ZATCA Phase 2 Compliance Note

Full ZATCA Phase 2 compliance requires:
1. Integration with the **ZATCA Fatoora API** (clearance for B2B, reporting for B2C)
2. **Cryptographic signing** of each invoice XML
3. **QR code** with base64-encoded seller name, VAT number, timestamp, total, and VAT amount

This workflow handles the **customer notification** part. The ZATCA API integration must be done at the plugin level (WooCommerce PDF Invoices Pro + ZATCA plugin) or via a dedicated ZATCA integration service.

**Recommended plugin for full compliance:** Zatca WooCommerce (Arabic plugin, available on CodeCanyon ~300 SAR/year).
