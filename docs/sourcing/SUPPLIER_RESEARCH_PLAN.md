# NeoGen Supplier Research Plan

Date: 2026-05-01

## Operating Setup

You have two buying entities:

- US company, Wyoming: use for US reseller/distributor onboarding, US invoices, US shipping addresses, and export consolidation.
- Saudi company: use for Saudi/MENA local buying, VAT/import compliance, marketplace references, warranty handling, and local B2B relationships.

The goal is not just to find the cheapest listing. The goal is to produce a verified supplier cost floor for each product before publishing/importing prices.

## Starter Supplier Shortlist

Use this shortlist first before broad marketplace searching:

- `docs/sourcing/GOOD_SUPPLIERS_SHORTLIST.md`: human-readable starter list with why each supplier matters and what to do first.
- `data/sourcing/good_suppliers_shortlist.csv`: filterable supplier onboarding list.

Start with:

1. LikeCard Business, Reloadly, and Infinivoucher for gift cards, software cards, subscriptions, and game top-ups.
2. Ingram Micro Saudi, Zeta, and Ashtel for Saudi electronics and local invoice/warranty coverage.
3. Ingram Micro US, TD SYNNEX, and D&H for US distributor accounts under the Wyoming company.
4. B&H and Exertis Almo as specialist reference/buying channels for creator, AV, and specialist hardware.

## Fastest Safe Workflow

1. Fill one verified supplier price per product first.
2. Start with `output/spreadsheet/supplier_price_work_queue.csv`, because it contains only products missing a real-price reference.
3. Prioritize high-risk gift cards and subscription/top-up SKUs before hardware.
4. For each product, record:
   - Supplier Buy URL
   - Supplier Price
   - Currency
   - MOQ
   - Stock status
   - Region restrictions
   - Date checked
5. Merge the filled queue back into the sourcing matrix:

```bash
python3 scripts/merge_supplier_price_queue.py
```

6. Rerun the price guard:

```bash
python3 scripts/price_floor_guard.py --supplier-matrix output/spreadsheet/supplier_sourcing_matrix_filled.csv --unpublish-missing-reference
```

## Source Strategy By Product Type

### Gift Cards, Software Keys, Top-Ups

Use digital B2B providers first. Do not rely on random marketplace listings for digital codes unless the supplier is known and refund terms are clear.

Recommended research targets:

- Reloadly: global gift cards, airtime, and top-up APIs.
- Visoria: digital gift cards, games, subscriptions, and bulk distribution.
- Infinivoucher: MENA-focused B2B game top-up and voucher platform.
- G2Bulk: game top-ups and gift cards for resellers.
- HCTOPUP: bulk/top-up portal and B2B/API path.
- Skarla / LikeCard ecosystem: regional voucher and prepaid card distribution.

Validation rules:

- Region must match the SKU, for example US, KSA, or Global.
- For dollar gift cards, floor is at least USD face value converted to SAR unless supplier cost is higher.
- For game credits/top-ups, floor must come from the exact denomination, not a similar pack.
- Avoid publishing PlayStation Plus and game top-up SKUs until supplier cost is verified.

### Hardware, Networking, Homelab, Gaming, Creator Gear

Use US distributors and retailers for US company procurement, then compare against Saudi landed cost.

Recommended research targets:

- Ingram Micro US and Ingram Micro Saudi Arabia.
- TD SYNNEX US.
- Exertis Almo for Pro AV and commercial display/projector/creator equipment.
- B&H Photo for creator, camera, AV, networking, and specialist hardware references.
- D&H Distributing for SMB IT, consumer electronics, gaming, home networking, and resellers.
- Global electronics wholesalers such as Smarteck, SM Distribution, B&S International, Acquvia, and Argentek for bulk or hard-to-source SKUs.

Validation rules:

- Use reseller/distributor quote where possible, not retail search price.
- If buying via US company, compute landed cost before Saudi sell price:
  product cost + US shipping + export/consolidation + international freight + duty/customs/SABER where applicable + VAT + payment fee + local delivery buffer.
- For regulated electronics, confirm HS code, SASO/SABER/IECEE/CST needs before committing bulk stock.

### Saudi/MENA Local Buying

Use Saudi company when the local source gives better landed cost, warranty, or compliance position.

Recommended research targets:

- Ingram Micro Saudi Arabia.
- Zeta Technologies for electronics distribution in KSA.
- Ashtel KSA for IT, communications, electronics, and wholesale/brand distribution.
- Shareef Corner for Xiaomi AIOT, Ugreen, Oraimo, Amazfit, Momax, and related accessories.
- Amazon.sa and Noon as market references and occasional supplier fallback, not always as true wholesale sources.

Validation rules:

- Prefer Saudi invoice with VAT details for products that will be sold locally.
- Check official warranty and region compatibility.
- Treat marketplace seller prices as references unless seller reliability and available quantity are confirmed.

## Columns To Add Later

The current `output/spreadsheet/supplier_sourcing_matrix.csv` is enough to start. For better controls, add these columns after the first fill pass:

- MOQ
- Stock Status
- Date Checked
- Shipping Cost
- Landed Cost SAR
- Payment Fee
- VAT/Duty Included
- Region
- Warranty Type
- Supplier Confidence
- Contact Person / Account Manager

## Decision Rule

For each SKU, use the highest relevant floor among:

- gift card face value converted to SAR,
- verified supplier cost,
- landed cost if buying internationally,
- known local marketplace/reference price when supplier cost is unavailable.

If no real floor exists, leave the product unpublished in the strict import.
