# WooCommerce Export Enrichment — Summary Report

## What was done

Two enrichments were applied to the source export `wc-export.csv`:

1. **Shipping dimensions** were added for every physical product. Gift cards
   and software keys had their dimension fields explicitly left blank and were
   assigned the shipping class `digital-no-shipping`.
2. **Amazon Saudi Arabia price comparison** columns were added next to your
   existing prices, populated for the products that already had reference
   prices stored in your store metadata.

The output file `wc-export-enriched.csv` has the same column structure as the
input, plus seven new analysis columns at the end.

## Product breakdown

- Total products in source file: **288**
- Gift cards / digital products (no shipping): **83**
  - Identified via metadata tag: 73
  - Identified via SKU prefix only (metadata tag missing): 10
- Physical products requiring shipping dimensions: **205**
  - With model-specific dimensions from curated table: 205
  - With generic fallback (verify before going live): 0

## Dimension confidence breakdown

Each physical product is marked with a confidence level in the
`Dimension Confidence` column:

- **`high`** — Well-known product with publicly documented manufacturer specs.
  Values are based on those specs and expected to be accurate within roughly
  10%. Acceptable for shipping rate calculation. Spot-check high-value items.
- **`medium`** — Product is identifiable but specific dimension recall is less
  certain. Values are reasoned estimates based on the product type. Verify
  before going live, especially for items where shipping cost matters.
- **`low`** — Generic fallback. Should be physically measured before use.
- **`n/a`** — Gift card / digital product with no physical shipping.

All dimension values represent **shipping package** dimensions (not bare
product), since WooCommerce uses these for shipping rate calculation with
carriers like Aramex, SMSA, and DHL.

## Amazon Saudi Arabia price comparison

- Products with Amazon SA reference price available: **32**
- Reference data was last refreshed: 2026-04-29 (2 days before this analysis)
- Source: stored in your `Meta: _ng_amazon_sa_ref_price` field

Pricing status breakdown:
- Priced more than 10% **above** Amazon SA: **32** products
- Priced within ±10% of Amazon SA: **0** products
- Priced more than 10% **below** Amazon SA: **0** products

### Products priced HIGHER than Amazon SA (potentially losing customers)
- **NG-GAM-001** — 8BitDo Ultimate C Bluetooth Controller - Multi-Platform Gami
  Your price: 180 SAR | Amazon: 140 SAR | +28.6%
- **NT-CBL-FSC-001** — 10G SFP+ DAC Twinax Cable (1m)
  Your price: 80 SAR | Amazon: 63 SAR | +27.0%
- **NG-GAM-011** — SteelSeries QcK Heavy XXL - Extra-Large Gaming Mouse Pad
  Your price: 200 SAR | Amazon: 158 SAR | +26.6%
- **GM-CTR-MSF-001** — Xbox Elite Controller Series 2 Core
  Your price: 720 SAR | Amazon: 569 SAR | +26.5%
- **NG-GAM-007** — 8BitDo Arcade Stick - Authentic Arcade Experience at Home
  Your price: 530 SAR | Amazon: 419 SAR | +26.5%
- **NG-NET-012** — Ubiquiti UniFi Cloud Gateway Ultra - All-in-One UniFi Gatewa
  Your price: 680 SAR | Amazon: 540 SAR | +25.9%
- **SH-HUB-HASS-001** — Home Assistant Green
  Your price: 890 SAR | Amazon: 707 SAR | +25.9%
- **NG-ENT-004** — UniFi 6 Long-Range (U6-LR) - High-Performance WiFi 6 Access 
  Your price: 1180 SAR | Amazon: 938 SAR | +25.8%
- **GM-HST-STS-001** — SteelSeries Arctis Nova 7 Wireless
  Your price: 860 SAR | Amazon: 684 SAR | +25.7%
- **GM-KBD-KEY-001** — Keychron K10 HE (Arabic Layout)
  Your price: 690 SAR | Amazon: 549 SAR | +25.7%
- **GM-RGB-GOV-001** — Govee DreamView G1 Pro Gaming Light
  Your price: 790 SAR | Amazon: 629 SAR | +25.6%
- **NT-MPC-DEL-001** — Dell OptiPlex 7070 Micro (Refurb)
  Your price: 1610 SAR | Amazon: 1282 SAR | +25.6%
- **NG-3DP-006** — BIQU H2 V2S Direct Drive Extruder - Lightweight Upgrade for 
  Your price: 540 SAR | Amazon: 430 SAR | +25.6%
- **NG-ENT-007** — MikroTik CRS326-24G-2S+RM 24-Port Managed Gigabit Switch wit
  Your price: 1110 SAR | Amazon: 884 SAR | +25.6%
- **GM-STR-HPX-001** — HyperX QuadCast S USB Microphone
  Your price: 580 SAR | Amazon: 462 SAR | +25.5%
- **NT-WAP-UBQ-001** — Ubiquiti UniFi U6 Pro
  Your price: 910 SAR | Amazon: 725 SAR | +25.5%
- **NG-3DP-002** — Bambu Lab AMS - Automatic Material System for Multi-Color 3D
  Your price: 1250 SAR | Amazon: 996 SAR | +25.5%
- **NG-SH-002** — UniFi Protect G4 Pro Camera - 4K Ultra HD Surveillance Excel
  Your price: 2520 SAR | Amazon: 2008 SAR | +25.5%
- **NT-NET-UBQ-001** — Ubiquiti USW-Pro-24-PoE
  Your price: 2370 SAR | Amazon: 1889 SAR | +25.5%
- **NG-ACC-008** — CalDigit TS4 Thunderbolt 4 Dock - 18-Port Professional Docki
  Your price: 2170 SAR | Amazon: 1730 SAR | +25.4%
- **GM-MON-SAM-002** — Samsung Odyssey G7 G70D (32" 4K 144Hz)
  Your price: 2600 SAR | Amazon: 2073 SAR | +25.4%
- **NG-MKR-004** — Meta Quest 3 128GB - The Most Powerful Mixed Reality Headset
  Your price: 3060 SAR | Amazon: 2441 SAR | +25.4%
- **NG-3DP-003** — Creality K1 Max - Large Format 3D Printer at 600mm/s
  Your price: 3380 SAR | Amazon: 2699 SAR | +25.2%
- **GM-MON-ASU-001** — ASUS ROG Swift OLED PG27AQDM (27" QHD 240Hz)
  Your price: 4080 SAR | Amazon: 3259 SAR | +25.2%
- **GM-CHR-SEC-001** — Secretlab Titan Evo
  Your price: 4630 SAR | Amazon: 3699 SAR | +25.2%
- **NG-ENT-003** — Ubiquiti UniFi Dream Machine Pro (UDM-Pro) - All-in-One Ente
  Your price: 2570 SAR | Amazon: 2054 SAR | +25.1%
- **GM-MON-SAM-001** — Samsung Odyssey OLED G8 (34" UWQHD 175Hz)
  Your price: 4870 SAR | Amazon: 3893 SAR | +25.1%
- **NG-ENT-009** — HPE ProLiant DL380 Gen10 Server (Refurbished) - Data Center 
  Your price: 8630 SAR | Amazon: 6899 SAR | +25.1%
- **NT-FWL-NGT-002** — Netgate 2100 MAX pfSense+ Gateway
  Your price: 2740 SAR | Amazon: 2191 SAR | +25.1%
- **NG-SEC-004** — Ubiquiti UniFi Protect G4 Doorbell Pro - 5MP Smart Doorbell 
  Your price: 3140 SAR | Amazon: 2511 SAR | +25.0%
- **NT-CBL-GEN-001** — Cat6a Shielded Bulk Cable (305m)
  Your price: 740 SAR | Amazon: 592 SAR | +25.0%
- **NG-MKR-008** — Creality Ender-3 V3 SE - The Best Entry-Level 3D Printer
  Your price: 1250 SAR | Amazon: 1000 SAR | +25.0%

### Products priced LOWER than Amazon SA (verify margin)

_None._

## Things you should review before re-importing this file

1. **The 10 gift cards that needed SKU-based detection** — your store's
   metadata tagging system missed these; consider adding the
   `_ng_gift_card_brand`, `_ng_gift_card_region`, `_ng_gift_card_denom`
   metadata to them so future automation works correctly.

2. **Apparent duplicate products** — several SKUs appear to refer to the
   same product (e.g. `GM-STR-ELG-001` and `NG-MKR-005` both being Elgato
   Stream Deck MK.2; `NG-NET-004` and `NT-NET-MKT-001` both being MikroTik
   CRS305-1G-4S+IN). Worth investigating whether these are intentional.

3. **The `digital-no-shipping` shipping class** — make sure this class
   exists in your WooCommerce shipping settings (Settings → Shipping →
   Shipping classes) before importing. If it doesn't exist, either create
   it first or change the value in the CSV to a class you do have.

4. **Medium-confidence dimensions** — there are 13
   products marked `medium` confidence in the Dimension Confidence column.
   Filter for these in a spreadsheet program and verify them, especially
   for high-volume items.

5. **The 7 new analysis columns at the end** — if your WooCommerce import
   complains about unknown columns, simply delete the last 7 columns before
   importing. The shipping dimensions in the original `Weight (kg)`,
   `Length (cm)`, `Width (cm)`, `Height (cm)` columns will still be filled in.
