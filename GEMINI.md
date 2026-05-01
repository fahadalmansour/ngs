# NeoGen Smart Store (NGS)

## Project Overview
This repository is a multi-faceted workspace for **neogen.store**, a Saudi-based (KSA) e-commerce platform specializing in Smart Home products and digital courses. It encompasses a Next.js frontend prototype, custom WordPress/WooCommerce overlays, standalone plugins, and various data/brand assets.

The project is characterized by its "Deploy to Production" workflow, where changes are pushed to GitHub and then pulled onto the live server via custom-built deployment tools.

## Core Components

### 1. Custom WordPress Overlays (`/neogen-custom`)
The primary source of truth for live site logic. It is deployed as an overlay to the WordPress installation.
- **`mu-plugins/`**: Essential site-wide hooks and the admin-bar version badge (`🚀 NG X.X.X`).
- **`plugins/neogen-snippets/`**: A collection of modular PHP snippets auto-loaded by a companion plugin.
- **`themes/blocksy-child/`**: Custom styling and template overrides for the Blocksy child theme.
- **`NeoGen Store — Brand Tokens v2.0.md`**: Canonical source of record for the brand's visual identity.

### 2. Next.js Frontend Prototype (`/neogen store` & root)
A modern, tech-focused e-commerce UI built with React/Next.js and Tailwind CSS.
- **Key Pages**: Home, Products, Courses, Cart, Checkout, etc.
- **Data**: Uses local JSON catalogs (e.g., `catalog_samples.json`).

### 3. NeoHub Plugin (`/neohub`)
A bilingual (EN/AR) newsletter signup plugin with its own deployment mechanism.
- **Features**: Custom DB storage, GitHub self-update, admin management, and CSV export.

### 4. Data & Scripts (`/data`, `/scripts`, `/NGS`)
- **`/data`**: Contains catalogs, financial reports, and infrastructure notes.
- **`/NGS`**: Likely a backup or snapshot of the live WordPress environment (includes `wp-config.php`, `index.php`, etc.).
- **`/scripts`**: Automation and utility scripts.

### 5. Automated Testing (`/tests`)
Uses **Playwright** for end-to-end testing of the web experience.

## Building and Running

### Frontend Prototype (Root Context)
```bash
# Install dependencies
npm install

# Run the development server
npm run dev

# Run Playwright tests
npx playwright test
```

### NeoHub Plugin Development
Located in `/neohub`.
- **Release/Commit**: Use `bin/commit.sh` and `bin/release.sh` to ensure consistent versioning across the three required locations (`VERSION`, plugin header, and constant).

## Deployment Workflow

### NeoGen Custom Overlay
1. **Edit & Commit**: Make changes in `/neogen-custom`.
2. **Push**: `git push origin main`.
3. **Pull**: Visit `https://neogen.store/wp-admin/tools.php?page=neogen-deploy` and click **Pull Latest**.
4. **Verify**: Check the admin-bar badge (`🚀 NG <version>`) on the live site.

### NeoHub Plugin
1. **Release**: Run `bin/release.sh X.Y.Z --push`.
2. **Update**: Go to **Settings → NeoHub** in the WP admin and click **Pull Latest**.

## Development Conventions
- **Timezone**: The site is locked to `Asia/Riyadh` via `mu-plugins/neogen-site-custom.php`.
- **Versioning**: Every deploy must bump the version in `VERSION` and the corresponding PHP file (e.g., `neogen-site-custom.php`).
- **Safety**: No staging environment exists; all pushes are to production. `php -l` is used by deployers to prevent syntax errors from breaking the site.
- **Brand Tokens**: Always refer to `neogen-custom/NeoGen Store — Brand Tokens v2.0.md` for current colors and styles.
- **RTL Support**: The site is bilingual; ensure all UI changes support both English and Arabic (RTL).
