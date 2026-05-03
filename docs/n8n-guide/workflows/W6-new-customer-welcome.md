# W6 — New Customer Welcome Email

**Trigger:** WooCommerce Webhook — `customer.created`
**What it does:** When a new customer account is created, sends a personalised Arabic/English welcome email via SMTP.
**Why it matters:** First impression. Reduces buyer anxiety in a new store. Increases repeat purchases.

---

## Step 1 — Create the WooCommerce Webhook

1. In WordPress admin go to **WooCommerce → Settings → Advanced → Webhooks**
2. Click **Add webhook**
3. Settings:
   - **Name:** `n8n — New Customer`
   - **Status:** Active
   - **Topic:** `Customer created`
   - **Delivery URL:** `https://n8n.neogen.store/webhook/new-customer`
   - **Secret:** Leave blank (or set one and verify in n8n)
4. Save — WooCommerce will now POST to n8n on every new registration.

---

## Nodes

### 1. Webhook Trigger
- **Node type:** `Webhook`
- **HTTP Method:** `POST`
- **Path:** `new-customer`
- **Response Mode:** `Immediately`
- **Response Code:** `200`

The payload will include: `id`, `email`, `first_name`, `last_name`, `date_created`, `billing` (address object).

---

### 2. Code — Prepare Email Data
- **Node type:** `Code`
```javascript
const customer = $input.first().json;

const firstName = customer.first_name || 'عزيزنا';
const email = customer.email;
const city = customer.billing?.city || '';

const subject = `مرحباً بك في نيوجين ستور — Welcome to NeoGen Store`;

const htmlBody = `
<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head><meta charset="UTF-8"></head>
<body style="font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px;">
  <div style="max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px;">
    
    <h2 style="color: #1a1a2e;">مرحباً ${firstName}! 👋</h2>
    <p>شكراً لتسجيلك في <strong>نيوجين ستور</strong> — متجرك التقني المتخصص في الشبكات والمنزل الذكي.</p>
    
    <h3 style="color: #0f3460;">ماذا يمكنك الآن؟</h3>
    <ul>
      <li>🛒 تصفح المتجر واضف منتجات للمفضلة</li>
      <li>📦 تتبع طلباتك من حسابك الشخصي</li>
      <li>💬 تواصل معنا عبر واتساب للدعم الفني</li>
    </ul>
    
    <div style="margin: 20px 0; padding: 15px; background: #f0f7ff; border-radius: 5px;">
      <strong>🎁 كود خصم ترحيبي: WELCOME10</strong><br>
      <small>خصم 10% على طلبك الأول — صالح 7 أيام</small>
    </div>
    
    <a href="https://neogen.store/shop/" 
       style="background: #0f3460; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px;">
      تسوق الآن
    </a>
    
    <hr style="margin: 25px 0; border: 1px solid #eee;">
    <p style="font-size: 12px; color: #888; text-align: center;">
      NeoGen Store · CR 7053130576 · VAT 3145127947<br>
      8102 Al Khaboub, Al Malqa, Riyadh 13521
    </p>
  </div>
</body>
</html>
`;

return [{ json: { email, subject, htmlBody, firstName } }];
```

---

### 3. Send Email (SMTP)
- **Node type:** `Send Email`
- **Credential:** Your SMTP credential
  - Host: `mail.neogen.store` (or Namecheap: `mail.privateemail.com`)
  - Port: `587`
  - Username: `hello@neogen.store`
  - Password: your email password
- **From:** `NeoGen Store <hello@neogen.store>`
- **To:** `{{ $json.email }}`
- **Subject:** `{{ $json.subject }}`
- **Email Format:** `HTML`
- **HTML:** `{{ $json.htmlBody }}`

---

## Wiring

```
Webhook (POST /new-customer)
    → Code (prepare email data)
    → Send Email (SMTP)
```

---

## Optional: Add Discount Coupon via WooCommerce API

After the Send Email node, add:

- **Node type:** `HTTP Request`
- **Method:** `POST`
- **URL:** `https://neogen.store/wp-json/wc/v3/coupons`
- **Auth:** Basic (WooCommerce keys)
- **Body (JSON):**
```json
{
  "code": "WELCOME10-{{ $('Webhook').first().json.id }}",
  "discount_type": "percent",
  "amount": "10",
  "individual_use": true,
  "usage_limit": 1,
  "email_restrictions": ["{{ $('Webhook').first().json.email }}"],
  "expiry_date": "{{ new Date(Date.now() + 7*24*60*60*1000).toISOString().split('T')[0] }}"
}
```

This creates a unique, single-use 10% coupon tied to that customer's email, expiring in 7 days. Then inject the generated `code` into the email HTML via a second Code node before Send Email.

---

## Testing

1. In WordPress, create a test customer account manually (Users → Add New → Role: Customer).
2. WooCommerce fires the `customer.created` webhook.
3. Check n8n execution log — the webhook node should show the customer payload.
4. Verify the welcome email arrives in the test inbox with correct name and HTML rendering.
