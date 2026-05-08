# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this repo is

Parent workspace for **neogen.store** — a KSA-registered (CR #7053130576) WooCommerce store selling smart home products and digital courses. The repo orchestrates four nested app repos under `apps/`, a data/pricing pipeline in `scripts/`, and Playwright E2E tests. Live site runs on blazr.net VPS (31.220.42.110). **There is no staging environment.**

## Nested apps (each has its own `.git` and `CLAUDE.md`)

| App | Path | Purpose |
|---|---|---|
| `neogen-custom` | `apps/neogen-custom/` | PHP overlays deployed to WP: mu-plugins, snippets plugin, Blocksy child theme |
| `neohub` | `apps/neohub/` | Standalone bilingual (EN/AR) newsletter plugin with GitHub self-update |
| `neogen-deploy` | `apps/neogen-deploy/` | WP admin plugin that pulls `neogen-custom` and `neohub` from GitHub onto the live server |
| `NGS` | `apps/NGS/` | Read-only WP snapshot/backup — not deployed as-is |

For any change to the live site, work inside `apps/neogen-custom/` and follow its CLAUDE.md deploy instructions. The deploy flow is: `git push` → WP admin **Pull Latest** button — nothing reaches production until that button is clicked.

## Commands

### Data & pricing pipeline
```bash
npm run woo:generate          # generate WooCommerce import CSV from master catalog
npm run sourcing:generate     # generate supplier sourcing matrix
npm run sourcing:queue        # generate supplier price queue CSV
npm run sourcing:merge        # merge filled-in price queue back
npm run price:guard           # enforce price floors; --unpublish-missing-reference flag active
npm run price:governor        # apply business pricing rules (15% margin target over landed cost)
npm run price:validate        # validate prices; exits non-zero if sale price < landed cost
npm run captures:merge        # merge SiteSucker captures into canonical output set
npm run captures:verify       # verify site capture integrity
```

### E2E tests (Playwright — Chromium, Firefox, Safari)
```bash
npx playwright test
npx playwright test --project=chromium
npx playwright test tests/example.spec.js
```

### PHP syntax check (run before any deploy from this workspace)
```bash
find apps/neogen-custom -name '*.php' -exec php -l {} \;
```

## Pricing pipeline flow

```
data/catalogs/master/Neogen_Master_Catalog_Blueprint.xlsx
    → npm run sourcing:generate  → output/spreadsheet/supplier_sourcing_wide.csv
    → npm run sourcing:queue     → [manually fill in supplier prices]
    → npm run sourcing:merge     → merged queue
    → npm run price:governor     → output/sync/price-sync-daily.csv
    → npm run price:validate     → gate (fails hard if margin ≤ 0)
    → node scripts/update-prices.js → WooCommerce via browser automation

Live market path: scripts/live-market-sync.js (Playwright scraper) → output/sync/price-sync-live.csv
```

## Key constraints

- **No staging** — every deploy to `neogen-custom` is production. Treat every push as live-ready.
- **RTL required** — site is bilingual EN/AR. All UI changes must render correctly in both directions.
- **Brand tokens** — canonical palette/shadow/radius tokens are in `apps/neogen-custom/NeoGen Store — Brand Tokens v2.0.md`. The deployed source of truth is the `:root` block in `apps/neogen-custom/mu-plugins/neogen-theme-assets/neogen.css`.
- **Master catalog** — single source of truth is `data/catalogs/master/Neogen_Master_Catalog_Blueprint.xlsx`. Do not edit generated CSVs in `output/` directly.
- **migration/** — a separate nested git repo managing a product transfer pipeline (neogen.store → novakeys.store). Each migration phase requires explicit user approval before proceeding; no destructive API calls without confirmation.

## CI

- **GitHub Actions** (`.github/workflows/playwright.yml`): runs Playwright on push/PR to main.
- **GitHub Actions** (`.github/workflows/claude-ops.yml`): Python lint + price:validate placeholder + PHP syntax check + secrets scan.
- **Forgejo** (`.forgejo/workflows/ci.yaml`): minimal health-check on the self-hosted Forgejo instance.

## Operations

Full operations contract: `~/sites/_docs/ngs/` (`README.md`, `STACK.md`, `HOSTING.md`, `DEPLOY.md`, `AGENT.md`, `AUTOMATION.md`, `RUNBOOK.md`).

Owning Claude agent: **`wp-woo-standards-auditor`** (for any PHP under `apps/`) + general-purpose for the data pipeline.

The Notion mirror lives in the **NeoTech Sites & Repos** database.

## Pre-flight checklist (binding before any push) — added 2026-05-07

NeoGen has **no staging** — every push to `apps/neogen-custom/` is prod. So every PHP/JS change goes through the audit before the push:

- **Trigger:** *"Audit the current file using the standards in `wordpress-engineer` and fix any violations."*
- **Skill stack:** `wordpress-engineer` (user-scope, master) → `wordpress` (project-local, NeoGen gotchas) → `woocommerce-specialist`.
- **Slash command:** `/check-all` (the project-local skill is more conservative — surfaces every BLOCKER and HIGH, marks "deploy risk" footer).
- **Auditor agent:** `wp-woo-standards-auditor`.

## MCP integration

- `neogen_woo` is registered user-scope, read-only by default. Connection: `claude mcp get neogen_woo`. Path: custom direct-to-WC server at `~/scripts/woo-mcp/index.js`. To enable writes, see `~/scripts/woo-mcp/README.md`.

## Repo-readiness backlog

- Latest readiness audit: `~/.claude/reports/neogen/readiness-2026-05-07.md`
- Verdict: **READY-WITH-CAVEATS.** No BLOCKERs. Top-3 priorities: block `/wp-json/wp/v2/users` enumeration, enable LiteSpeed cache + Cloudflare orange-cloud (TTFB 2.5–2.7s today), promote CSP from report-only to enforce.
