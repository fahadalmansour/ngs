# NGS Project Map

Date: 2026-05-01

This workspace contains the parent NGS project plus separate nested project trees. Treat nested `.git` folders as independent repos.

## Root

- `README.md`: project overview and workflow entry points.
- `package.json`: Node dependencies and script shortcuts.
- `scripts/`: automation for WooCommerce exports, supplier sourcing, price guards, and sync checks.
- `apps/`: separate app/plugin/site repos kept out of the parent project flow.
- `data/`: source data and reusable inputs.
- `docs/`: project documentation and plans.
- `output/`: generated files ready for review/import.
- `archive/`: preserved historical, raw, duplicate, backup, and quarantined artifacts.
- `prototypes/`: UI/design experiments and old site prototypes.

## Important Boundaries

- `apps/NGS/` is a nested git repository and full site/export tree. Do not commit it into the parent repo as one blob.
- `apps/neogen-custom/` is also a nested git repository.
- `apps/neohub/` is also a nested git repository.
- The parent repo should own the sourcing scripts, docs, source catalogs, and generated import outputs.

## Catalog And Sourcing Files

- Master workbook: `data/catalogs/master/Neogen_Master_Catalog_Blueprint.xlsx`
- Live WooCommerce exports/reference CSVs: `data/catalogs/live/`
- Supplier docs: `docs/sourcing/`
- Supplier filterable lists: `data/sourcing/`
- Generated WooCommerce/supplier outputs: `output/spreadsheet/`
- Price sync outputs: `output/sync/`
- Duplicate/old artifacts moved out of active folders: `archive/legacy/duplicates-2026-05-01/`

## SiteSucker Store Capture

- Canonical cleaned capture: `archive/site-captures/neogen-store-best/`
- Merge report: `docs/site-captures/SITESUCKER_MERGE_REPORT.md`
- Manifest: `output/site-captures/neogen-store-best-manifest.json`
- Raw SiteSucker folders were moved to: `archive/trash/site-captures-raw-2026-05-01/`

Regenerate the cleaned capture from the raw folders:

```bash
npm run captures:merge
```

## File Health

The active project folders `data/`, `docs/`, and `output/` were scanned on 2026-05-01 after the SiteSucker merge:

- Duplicate groups: 0
- CSV/XLSX/ZIP validity issues: 0

The scan excludes nested git projects, raw site captures, trash, backups, and archived duplicate artifacts.

## Current Core Workflow

Generate WooCommerce import and buy links:

```bash
npm run woo:generate
```

Generate supplier sourcing matrix:

```bash
npm run sourcing:generate
```

Generate focused supplier price work queue:

```bash
npm run sourcing:queue
```

After filling supplier prices, merge queue back into the full matrix:

```bash
npm run sourcing:merge
```

Run the price-floor guard and unpublish products without real price references:

```bash
npm run price:guard
```

## Main Import File

The strict safe WooCommerce import is:

```text
output/spreadsheet/woocommerce_ready_import_safe_verified_only.csv
```

Use the normal import only when you intentionally want all products published:

```text
output/spreadsheet/woocommerce_ready_import.csv
```
