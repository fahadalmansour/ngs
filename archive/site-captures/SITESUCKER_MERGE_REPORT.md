# SiteSucker Capture Merge Report

Generated: 2026-05-01

## Result

- Canonical capture: `archive/site-captures/neogen-store-best/`
- Files kept: 3309
- HTML pages kept: 1155
- Product pages kept: 283
- Approximate kept size: 183.9 MB
- Manifest: `output/site-captures/neogen-store-best-manifest.json`
- Previous cleaned capture: `archive/trash/site-captures-previous-2026-05-01-2`

## Source Policy

The merge keeps the best static copy of `neogen.store` plus local Google font assets. Tracker, analytics, payment-runtime, cookie, beacon, and WhatsApp redirect domains are rejected because they are noisy or dynamic in a static SiteSucker archive.

Query captures such as `add-to-cart`, `p=123`, `mailpoet_page`, taxonomy query duplicates, and similar files are rejected when a clean permalink version exists.

## Sources Scanned

| Source | Path | Files scanned |
| --- | --- | ---: |
| apps/NGS blocksy bundle fill | `apps/NGS/wp-content/themes/blocksy/static/bundle` | 141 |
| apps/NGS read-only fill | `apps/NGS` | 33384 |
| raw-trash/webviwe | `archive/trash/site-captures-raw-2026-05-01/webviwe` | 3429 |
| raw-trash/ALL/webviwe | `archive/trash/site-captures-raw-2026-05-01/ALL/webviwe` | 3429 |
| raw-trash/webviwe/sitesucker | `archive/trash/site-captures-raw-2026-05-01/webviwe/sitesucker` | 205 |
| raw-trash/ALL/webviwe/sitesucker | `archive/trash/site-captures-raw-2026-05-01/ALL/webviwe/sitesucker` | 205 |
| raw-trash/ALL/SAFARE | `archive/trash/site-captures-raw-2026-05-01/ALL/SAFARE` | 4975 |
| raw-trash/ALL/CHROME | `archive/trash/site-captures-raw-2026-05-01/ALL/CHROME` | 13840 |
| raw-trash/ALL/root | `archive/trash/site-captures-raw-2026-05-01/ALL` | 22351 |

## Rejections

| Reason | Count |
| --- | ---: |
| outside allowed domains | 29547 |
| query/add-to-cart duplicate | 18984 |
| external/noisy domain: pixel.wp.com | 14713 |
| dynamic wp-json API endpoint | 3350 |
| capture metadata/log file | 50 |
| external/noisy domain: stats.wp.com | 30 |
| external/noisy domain: www.googletagmanager.com | 13 |
| external/noisy domain: js.stripe.com | 12 |
| external/noisy domain: gmpg.org | 4 |
| external/noisy domain: cdn.sift.com | 3 |
| external/noisy domain: cookieadmin.net | 3 |
| external/noisy domain: gravatar.com | 3 |
| external/noisy domain: wa.me | 3 |
| empty file | 1 |

## Raw Capture Quarantine

| From | To |
| --- | --- |
| none | none |
