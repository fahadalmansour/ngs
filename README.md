# Neogen Smart Store

## Project Overview

This project is an e-commerce platform for 'NGS' (Neogen Store), designed to showcase and sell Smart Home products and digital courses. It aims to provide a modern, clean, and tech-focused online shopping experience.

## Features

- **Dynamic Homepage:** Hero section, featured products, featured courses.
- **Product & Course Pages:** Filterable galleries for browsing items.
- **Detailed Item Pages:** In-depth descriptions, specifications, and 'Add to Cart' functionality.
- **Shopping Cart:** Slide-out panel for managing selected items.
- **Simulated Checkout:** Multi-step form to simulate the purchase process.

## Technology Stack

- **Frontend:** Next.js (React) for a fast, SEO-friendly user interface.
- **Styling:** Tailwind CSS for rapid and custom UI development.
- **Backend/Data:** Next.js API Routes for simplified backend logic; local JSON file as a data source (populated from existing product/course catalogs).

## Sitemap (High-Level)

- Home (/)
- Products (/products)
- Courses (/courses)
- Cart (/cart)
- Checkout (/checkout)
- About Us (/about)
- Contact Us (/contact)
- Legal Pages (Privacy Policy, Terms of Service)

## Setup and Installation

To get this project up and running locally, follow these steps:

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/fahadalmansour/ngs.git
    cd ngs
    ```
2.  **Install dependencies:**
    ```bash
    npm install # or yarn install
    ```
3.  **Run the development server:**
    ```bash
    npm run dev # or yarn dev
    ```
    Open [http://localhost:3000](http://localhost:3000) in your browser to see the application.

## Usage

Once the development server is running, you can navigate through the pages, browse products and courses, add items to the cart, and go through the simulated checkout process.

## Project Structure

See [docs/PROJECT_MAP.md](docs/PROJECT_MAP.md) for the current workspace map and workflow commands.

Key folders:

- `apps/`: nested app/site/plugin repos (`NGS`, `neogen-custom`, `neohub`, `neogen-deploy`).
- `data/catalogs/master/`: master catalog workbook.
- `data/catalogs/live/`: WooCommerce/live-store CSV references.
- `data/sourcing/`: filterable supplier research CSVs.
- `archive/site-captures/neogen-store-best/`: cleaned SiteSucker static capture of `neogen.store`.
- `docs/sourcing/`: supplier research plans and onboarding notes.
- `docs/site-captures/`: SiteSucker merge report.
- `output/spreadsheet/`: generated WooCommerce imports, supplier matrices, and audit files.
- `output/sync/`: market/price sync outputs.
- `scripts/`: automation for catalog exports, sourcing, and price-floor checks.

## Catalog Workflows

Generate WooCommerce import files:

```bash
npm run woo:generate
```

Generate supplier sourcing files:

```bash
npm run sourcing:generate
npm run sourcing:queue
```

After filling real supplier prices, merge and rerun the price guard:

```bash
npm run sourcing:merge
npm run price:guard
```

## Site Capture Workflow

Merge raw SiteSucker pulls (`ALL`, `webviwe`, browser variants) into the cleaned local capture:

```bash
npm run captures:merge
```
