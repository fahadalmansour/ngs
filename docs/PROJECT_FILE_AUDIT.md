# Project File Audit

Date: 2026-05-01

## Scope

Active folders scanned:

- `data/`
- `docs/`
- `output/`

Excluded:

- `.git/`
- `node_modules/`
- nested repos: `apps/NGS/`, `apps/neogen-custom/`, `apps/neohub/`
- archived duplicate artifacts under `archive/legacy/`
- raw SiteSucker captures under `archive/trash/`

## Result

- Active files scanned: 88
- Active duplicate groups: 0
- Active CSV/XLSX/ZIP validity issues: 0

## Duplicate Handling

Redundant generated copies were moved out of the active working folders into:

```text
archive/legacy/duplicates-2026-05-01/
```

One pre-existing tracked duplicate was also moved there:

```text
data/financials/Smart_Home_Business_.xlsx
```

It was byte-identical to:

```text
data/financials/Smart_Home_Business_COMPLETE.xlsx
```

The canonical generated outputs now live under:

```text
output/spreadsheet/
```

The canonical master catalog now lives at:

```text
data/catalogs/master/Neogen_Master_Catalog_Blueprint.xlsx
```

## Script Adjustments

- `scripts/price_floor_guard.py` now skips `woocommerce_ready_import_price_floor_checked.csv` when it would be identical to `woocommerce_ready_import.csv`.
- `scripts/merge_supplier_price_queue.py` now skips `supplier_sourcing_matrix_filled.csv` when no supplier prices were filled, avoiding a duplicate matrix.

## Nested Folder Audit

Requested nested folders:

- `apps/NGS/`
- `apps/neogen-custom/`

Repo state at audit time:

- `apps/NGS/`: clean nested git repo, remote `git@github.com:fahadalmansour/neogen-store.git`
- `apps/neogen-custom/`: nested git repo with active modified plugin/theme work, remote `git@github.com:fahadalmansour/neogen-custom.git`

File validity scan after cleanup:

- Files checked: 30,824
- CSV/XLSX/ZIP validity issues: 0

Duplicate scan notes:

- `apps/NGS/` contains thousands of byte-identical files. These are mostly expected WordPress/export duplicates, including plugin/theme assets and media mirrored between `apps/NGS/wp-content/uploads/` and `apps/NGS/neogen.store/wp-content/uploads/`.
- Those duplicates were not removed because `apps/NGS/` is a full WordPress/static export repo and path-level duplicates may still be required by the export shape.

Corrupt file handling:

```text
apps/neogen-custom/plugins/neogen-pro/.!83180!neogen-pro-vVersion:.zip
```

This file was a 5-byte invalid ZIP and was not tracked by git. It was quarantined to:

```text
apps/neogen-custom/_archive/corrupt-files-2026-05-01/.!83180!neogen-pro-vVersion:.zip
```

## SiteSucker Capture Cleanup

The raw SiteSucker captures were merged into a single clean archive:

```text
archive/site-captures/neogen-store-best/
```

Kept:

- 3,169 best files
- 1,155 HTML pages
- 283 product pages
- `neogen.store`, `fonts.googleapis.com`, and `fonts.gstatic.com` assets only

Rejected:

- `add-to-cart`, `p=123`, `mailpoet_page`, and taxonomy query duplicates
- `wp-json` dynamic endpoint captures
- pixel, analytics, tracker, cookie redirect, Stripe runtime, WhatsApp redirect, and other external noisy domains
- empty files and SiteSucker metadata/log files

Raw captures were moved out of the active capture folder:

```text
archive/trash/site-captures-raw-2026-05-01/
```

The merge report is:

```text
docs/site-captures/SITESUCKER_MERGE_REPORT.md
```
