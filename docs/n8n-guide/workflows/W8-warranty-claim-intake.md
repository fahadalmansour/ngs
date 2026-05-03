# W8 — Warranty Claim Intake → Google Sheet + WhatsApp

**Trigger:** n8n Form (public URL) or Webhook from WP contact form (e.g. WPForms/CF7)
**What it does:** Customer submits a warranty claim → saved to Google Sheet → Fahad gets a WhatsApp notification.
**Why it matters:** Replaces manual WhatsApp-based claims with a traceable, searchable log. Required for Saudi e-commerce dispute resolution documentation.

---

## Option A — Use n8n's Built-in Form

n8n can serve a public HTML form without WordPress. Best for quick setup.

### Nodes

#### 1. n8n Form Trigger
- **Node type:** `n8n Form Trigger`
- **Path:** `warranty-claim`
- **Form Title:** Warranty Claim / طلب ضمان
- **Fields:**
  | Field Label | Type | Required |
  |---|---|---|
  | Full Name / الاسم الكامل | Text | Yes |
  | Email | Email | Yes |
  | Order Number / رقم الطلب | Text | Yes |
  | Product Name / اسم المنتج | Text | Yes |
  | Issue Description / وصف المشكلة | Textarea | Yes |
  | Purchase Date / تاريخ الشراء | Date | Yes |
  | Preferred Contact / طريقة التواصل | Dropdown: WhatsApp, Email, Phone | Yes |
  | Phone / رقم الجوال | Text | No |

Public URL will be: `https://n8n.neogen.store/form/warranty-claim`

---

#### 2. Code — Prepare Data
```javascript
const form = $input.first().json;

const ticketId = `WRN-${Date.now().toString().slice(-6)}`;
const submittedAt = new Date().toLocaleString('en-SA', { timeZone: 'Asia/Riyadh' });

return [{
  json: {
    ticketId,
    submittedAt,
    name: form['Full Name / الاسم الكامل'],
    email: form['Email'],
    orderNumber: form['Order Number / رقم الطلب'],
    product: form['Product Name / اسم المنتج'],
    issue: form['Issue Description / وصف المشكلة'],
    purchaseDate: form['Purchase Date / تاريخ الشراء'],
    contactMethod: form['Preferred Contact / طريقة التواصل'],
    phone: form['Phone / رقم الجوال'] || 'Not provided',
    status: 'Open'
  }
}];
```

---

#### 3. Google Sheets — Log Claim
- **Operation:** `Append`
- **Spreadsheet:** `NeoGen Warranty Claims`
- **Sheet:** `Claims`
- **Columns:** `ticketId, submittedAt, name, email, orderNumber, product, issue, purchaseDate, contactMethod, phone, status`

Create this spreadsheet in Google Drive first with those column headers in row 1.

---

#### 4. HTTP Request — WhatsApp Notification (UltraMsg)
- **Node type:** `HTTP Request`
- **Method:** `POST`
- **URL:** `https://api.ultramsg.com/{{ $env.ULTRAMSG_INSTANCE }}/messages/chat`
- **Body (JSON):**
```json
{
  "token": "{{ $env.ULTRAMSG_TOKEN }}",
  "to": "966XXXXXXXXX",
  "body": "🔧 *New Warranty Claim — {{ $json.ticketId }}*\n\n👤 {{ $json.name }}\n📦 Order: {{ $json.orderNumber }}\n🛒 Product: {{ $json.product }}\n❗ Issue: {{ $json.issue }}\n📅 Purchased: {{ $json.purchaseDate }}\n📱 Contact: {{ $json.contactMethod }} — {{ $json.phone }}\n\n_Logged in Google Sheets_"
}
```

Set `ULTRAMSG_INSTANCE` and `ULTRAMSG_TOKEN` as n8n environment variables:  
Settings → Variables → New Variable

---

#### 5. Send Email — Confirmation to Customer
- **To:** `{{ $json.email }}`
- **Subject:** `Warranty Claim Received — {{ $json.ticketId }}`
- **HTML:**
```html
<div dir="rtl" style="font-family: Arial; padding: 20px; max-width: 600px;">
  <h2>تم استلام طلب الضمان ✅</h2>
  <p>مرحباً {{ $json.name }},</p>
  <p>تم استلام طلب الضمان الخاص بك بنجاح.</p>
  <table style="border-collapse: collapse; width: 100%;">
    <tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>رقم الطلب</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{{ $json.ticketId }}</td></tr>
    <tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>المنتج</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{{ $json.product }}</td></tr>
    <tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>الوصف</strong></td><td style="padding: 8px; border: 1px solid #ddd;">{{ $json.issue }}</td></tr>
  </table>
  <p style="margin-top: 15px;">سيتواصل معك فريقنا خلال <strong>48 ساعة عمل</strong>.</p>
  <p style="font-size: 12px; color: #888; margin-top: 20px;">
    NeoGen Store — CR 7053130576 — neogen.store
  </p>
</div>
```

---

## Wiring

```
n8n Form Trigger
    → Code (prepare data + ticket ID)
    → Google Sheets (log claim)
    → HTTP Request (WhatsApp to Fahad)
    → Send Email (confirmation to customer)
```

---

## Option B — Webhook from WordPress Contact Form

If you prefer the form to live on the WordPress site:

1. Use **WPForms** or **Contact Form 7** on a `/warranty/` page.
2. Configure the form to POST to: `https://n8n.neogen.store/webhook/warranty-claim`
3. Replace the `n8n Form Trigger` node with a `Webhook` node at path `warranty-claim`.
4. Adjust field names in the Code node to match your WP form's field IDs.

---

## Sheet Structure (Google Sheets)

| ticketId | submittedAt | name | email | orderNumber | product | issue | purchaseDate | contactMethod | phone | status |
|---|---|---|---|---|---|---|---|---|---|---|
| WRN-123456 | 01/05/2026 10:32 | Ahmed Ali | ahmed@email.com | 1045 | Ubiquiti AP | Not connecting | 2026-04-15 | WhatsApp | 966501234567 | Open |

Update `status` manually to `In Progress` or `Resolved` as you handle each case.
