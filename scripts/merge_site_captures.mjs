#!/usr/bin/env node

import { createHash } from 'node:crypto';
import { createReadStream } from 'node:fs';
import fs from 'node:fs/promises';
import path from 'node:path';

const ROOT = process.cwd();
const today = new Date().toISOString().slice(0, 10);

const targetRoot = path.join(ROOT, 'archive/site-captures/neogen-store-best');
const reportPath = path.join(ROOT, 'docs/site-captures/SITESUCKER_MERGE_REPORT.md');
const manifestPath = path.join(ROOT, 'output/site-captures/neogen-store-best-manifest.json');

const allowedDomains = new Set([
  'neogen.store',
  'fonts.googleapis.com',
  'fonts.gstatic.com',
]);

const rejectedDomains = new Set([
  'cdn.sift.com',
  'cookieadmin.net',
  'gravatar.com',
  'gmpg.org',
  'js.stripe.com',
  'pixel.wp.com',
  'stats.wp.com',
  'wa.me',
  'www.googletagmanager.com',
]);

const sourceRoots = [
  { root: 'archive/site-captures/webviwe', label: 'webviwe', priority: 100 },
  { root: 'archive/site-captures/ALL/webviwe', label: 'ALL/webviwe', priority: 98 },
  { root: 'archive/site-captures/webviwe/sitesucker', label: 'webviwe/sitesucker', priority: 92 },
  { root: 'archive/site-captures/ALL/webviwe/sitesucker', label: 'ALL/webviwe/sitesucker', priority: 90 },
  {
    root: 'apps/NGS/wp-content/themes/blocksy/static/bundle',
    label: 'apps/NGS blocksy bundle fill',
    priority: 88,
    targetPrefix: 'neogen.store/wp-content/themes/blocksy/static/bundle',
  },
  { root: 'apps/NGS', label: 'apps/NGS read-only fill', priority: 86 },
  { root: 'archive/trash/site-captures-raw-2026-05-01/webviwe', label: 'raw-trash/webviwe', priority: 84 },
  { root: 'archive/trash/site-captures-raw-2026-05-01/ALL/webviwe', label: 'raw-trash/ALL/webviwe', priority: 82 },
  { root: 'archive/trash/site-captures-raw-2026-05-01/webviwe/sitesucker', label: 'raw-trash/webviwe/sitesucker', priority: 80 },
  { root: 'archive/trash/site-captures-raw-2026-05-01/ALL/webviwe/sitesucker', label: 'raw-trash/ALL/webviwe/sitesucker', priority: 78 },
  { root: 'archive/trash/site-captures-raw-2026-05-01/ALL/SAFARE', label: 'raw-trash/ALL/SAFARE', priority: 76 },
  { root: 'archive/trash/site-captures-raw-2026-05-01/ALL/CHROME', label: 'raw-trash/ALL/CHROME', priority: 74 },
  { root: 'archive/trash/site-captures-raw-2026-05-01/ALL', label: 'raw-trash/ALL/root', priority: 62 },
  { root: 'archive/site-captures/ALL/SAFARE', label: 'ALL/SAFARE', priority: 75 },
  { root: 'archive/site-captures/ALL/CHROME', label: 'ALL/CHROME', priority: 70 },
  { root: 'archive/site-captures/ALL', label: 'ALL/root', priority: 60 },
];

const rawRootsToQuarantine = [
  'archive/site-captures/ALL',
  'archive/site-captures/webviwe',
];

const queryMarkers = ['?', '﹖'];
const badQueryPatterns = [
  /add-to-cart/i,
  /mailpoet_page/i,
  /replytocom/i,
  /orderby/i,
  /filter_/i,
  /rating_filter/i,
  /taxonomy=/i,
  /term=/i,
  /p=\d+/i,
];

const badHtmlPatterns = [
  /<title>\s*(404|not found|page not found)/i,
  /fatal error/i,
  /wordpress database error/i,
  /error establishing a database connection/i,
  /this site can.t be reached/i,
  /access denied/i,
  /just a moment/i,
];

const ignoreFileNames = new Set([
  '.DS_Store',
  '_downloads.html',
  'Untitled.log',
  'Untitled 2.log',
]);

function toAbs(relativePath) {
  return path.join(ROOT, relativePath);
}

async function exists(filePath) {
  try {
    await fs.access(filePath);
    return true;
  } catch {
    return false;
  }
}

async function uniquePath(basePath) {
  if (!(await exists(basePath))) return basePath;
  for (let i = 1; i < 1000; i += 1) {
    const candidate = `${basePath}-${i}`;
    if (!(await exists(candidate))) return candidate;
  }
  throw new Error(`Could not allocate unique path for ${basePath}`);
}

async function walkFiles(rootPath) {
  const out = [];

  async function walk(current) {
    let entries;
    try {
      entries = await fs.readdir(current, { withFileTypes: true });
    } catch {
      return;
    }

    for (const entry of entries) {
      const fullPath = path.join(current, entry.name);
      if (entry.isDirectory()) {
        await walk(fullPath);
      } else if (entry.isFile()) {
        out.push(fullPath);
      }
    }
  }

  await walk(rootPath);
  return out;
}

function domainRelative(sourceRootAbs, filePath) {
  const rel = path.relative(sourceRootAbs, filePath);
  const source = sourceRoots.find((item) => path.join(ROOT, item.root) === sourceRootAbs);
  if (source?.targetPrefix) {
    return {
      domain: 'neogen.store',
      relativePath: path.posix.join(source.targetPrefix, ...rel.split(path.sep)).normalize('NFC'),
    };
  }

  const parts = rel.split(path.sep);
  const domainIndex = parts.findIndex((part) => allowedDomains.has(part) || rejectedDomains.has(part));
  if (domainIndex === -1) return null;
  const domain = parts[domainIndex];
  return {
    domain,
    relativePath: parts.slice(domainIndex).join('/').normalize('NFC'),
  };
}

function hasQueryNoise(relativePath) {
  if (!queryMarkers.some((marker) => relativePath.includes(marker))) return false;
  if (!isHtml(relativePath)) return false;

  const basename = path.posix.basename(relativePath);
  if (!/^(index(?:\.html?)?|xmlrpc\.php)[?﹖]/i.test(basename)) return false;

  return badQueryPatterns.some((pattern) => pattern.test(relativePath));
}

function isHtml(relativePath) {
  return /\.(html?|php)$/i.test(relativePath);
}

async function sha256(filePath) {
  return new Promise((resolve, reject) => {
    const hash = createHash('sha256');
    const stream = createReadStream(filePath);
    stream.on('data', (chunk) => hash.update(chunk));
    stream.on('error', reject);
    stream.on('end', () => resolve(hash.digest('hex')));
  });
}

async function htmlQuality(filePath, size) {
  const sample = await fs.readFile(filePath, 'utf8').catch(() => '');
  let score = 0;

  if (sample.includes('<!doctype') || sample.includes('<!DOCTYPE')) score += 8;
  if (/<html[\s>]/i.test(sample)) score += 8;
  if (/<body[\s>]/i.test(sample)) score += 8;
  if (/<\/html>/i.test(sample)) score += 8;
  if (/woocommerce|wp-content|neogen/i.test(sample)) score += 8;
  if (/product_title|summary entry-summary|add_to_cart_button/i.test(sample)) score += 8;
  if (badHtmlPatterns.some((pattern) => pattern.test(sample))) score -= 80;
  if (size < 512) score -= 50;

  score += Math.min(20, Math.log10(Math.max(size, 1)) * 4);
  return score;
}

async function candidateFor(source, filePath) {
  const sourceRootAbs = toAbs(source.root);
  const relInfo = domainRelative(sourceRootAbs, filePath);
  if (!relInfo) return { reject: 'outside allowed domains' };

  const { domain, relativePath } = relInfo;
  const name = path.basename(relativePath);

  if (rejectedDomains.has(domain)) return { reject: `external/noisy domain: ${domain}` };
  if (!allowedDomains.has(domain)) return { reject: `unknown domain: ${domain}` };
  if (relativePath.startsWith('neogen.store/wp-json/')) return { reject: 'dynamic wp-json API endpoint' };
  if (ignoreFileNames.has(name)) return { reject: 'capture metadata/log file' };
  if (name.startsWith('._')) return { reject: 'macOS sidecar file' };
  if (hasQueryNoise(relativePath)) return { reject: 'query/add-to-cart duplicate' };

  const stat = await fs.stat(filePath);
  if (stat.size === 0) return { reject: 'empty file' };

  let score = source.priority;
  score += Math.min(25, Math.log10(Math.max(stat.size, 1)) * 5);

  if (isHtml(relativePath)) {
    score += await htmlQuality(filePath, stat.size);
  } else {
    score += Math.min(12, Math.log10(Math.max(stat.size, 1)) * 3);
  }

  return {
    candidate: {
      source: source.label,
      sourcePath: filePath,
      relativePath,
      size: stat.size,
      score,
    },
  };
}

function betterCandidate(a, b) {
  if (!a) return b;
  if (b.score !== a.score) return b.score > a.score ? b : a;
  if (b.size !== a.size) return b.size > a.size ? b : a;
  return b.source.localeCompare(a.source) < 0 ? b : a;
}

async function linkOrCopy(sourcePath, destPath) {
  await fs.mkdir(path.dirname(destPath), { recursive: true });
  try {
    await fs.link(sourcePath, destPath);
  } catch (error) {
    if (error.code !== 'EXDEV' && error.code !== 'EPERM' && error.code !== 'EEXIST') {
      throw error;
    }
    await fs.copyFile(sourcePath, destPath);
  }
}

async function moveRawCaptures() {
  const moved = [];
  let trashRoot = null;
  for (const rawRoot of rawRootsToQuarantine) {
    const sourcePath = toAbs(rawRoot);
    if (!(await exists(sourcePath))) continue;
    if (!trashRoot) {
      trashRoot = await uniquePath(toAbs(`archive/trash/site-captures-raw-${today}`));
      await fs.mkdir(trashRoot, { recursive: true });
    }
    const destPath = path.join(trashRoot, path.basename(rawRoot));
    await fs.rename(sourcePath, destPath);
    moved.push({ from: rawRoot, to: path.relative(ROOT, destPath) });
  }

  return trashRoot ? { trashRoot: path.relative(ROOT, trashRoot), moved } : null;
}

async function movePreviousTarget() {
  const previousRoot = await uniquePath(toAbs(`archive/trash/site-captures-previous-${today}`));
  await fs.mkdir(path.dirname(previousRoot), { recursive: true });
  await fs.rename(targetRoot, previousRoot);
  return path.relative(ROOT, previousRoot);
}

async function main() {
  const quarantineRaw = process.argv.includes('--quarantine-raw');
  const replaceTarget = process.argv.includes('--replace-target');

  if (await exists(targetRoot)) {
    if (!replaceTarget) {
      throw new Error(`${path.relative(ROOT, targetRoot)} already exists. Move it aside or use --replace-target before re-running.`);
    }
  }

  const winners = new Map();
  const rejected = new Map();
  const scannedSources = [];
  let previousTarget = null;

  if (await exists(targetRoot)) {
    previousTarget = await movePreviousTarget();
  }

  for (const source of sourceRoots) {
    const sourceRootAbs = toAbs(source.root);
    if (!(await exists(sourceRootAbs))) continue;

    const files = await walkFiles(sourceRootAbs);
    scannedSources.push({ ...source, files: files.length });

    for (const filePath of files) {
      const result = await candidateFor(source, filePath);
      if (result.reject) {
        rejected.set(result.reject, (rejected.get(result.reject) || 0) + 1);
        continue;
      }

      const current = winners.get(result.candidate.relativePath);
      winners.set(result.candidate.relativePath, betterCandidate(current, result.candidate));
    }
  }

  const copied = [];
  for (const [relativePath, winner] of [...winners.entries()].sort(([a], [b]) => a.localeCompare(b))) {
    const destPath = path.join(targetRoot, relativePath);
    await linkOrCopy(winner.sourcePath, destPath);
    copied.push({
      relativePath,
      source: winner.source,
      sourcePath: path.relative(ROOT, winner.sourcePath),
      size: winner.size,
      score: Number(winner.score.toFixed(2)),
      sha256: await sha256(destPath),
    });
  }

  const productPages = copied.filter((item) => /^neogen\.store\/product\/[^/]+\/index\.html$/.test(item.relativePath));
  const htmlPages = copied.filter((item) => /\.html?$/i.test(item.relativePath));
  const totalBytes = copied.reduce((sum, item) => sum + item.size, 0);

  let quarantine = null;
  if (quarantineRaw) {
    quarantine = await moveRawCaptures();
  }

  await fs.mkdir(path.dirname(reportPath), { recursive: true });
  await fs.mkdir(path.dirname(manifestPath), { recursive: true });

  const rejectedRows = [...rejected.entries()]
    .sort((a, b) => b[1] - a[1])
    .map(([reason, count]) => `| ${reason} | ${count} |`)
    .join('\n');

  const sourceRows = scannedSources
    .map((source) => `| ${source.label} | \`${source.root}\` | ${source.files} |`)
    .join('\n');

  const movedRows = quarantine?.moved?.length
    ? quarantine.moved.map((item) => `| \`${item.from}\` | \`${item.to}\` |`).join('\n')
    : '| none | none |';

  const report = `# SiteSucker Capture Merge Report

Generated: ${today}

## Result

- Canonical capture: \`archive/site-captures/neogen-store-best/\`
- Files kept: ${copied.length}
- HTML pages kept: ${htmlPages.length}
- Product pages kept: ${productPages.length}
- Approximate kept size: ${(totalBytes / 1024 / 1024).toFixed(1)} MB
- Manifest: \`output/site-captures/neogen-store-best-manifest.json\`
- Previous cleaned capture: ${previousTarget ? `\`${previousTarget}\`` : 'none'}

## Source Policy

The merge keeps the best static copy of \`neogen.store\` plus local Google font assets. Tracker, analytics, payment-runtime, cookie, beacon, and WhatsApp redirect domains are rejected because they are noisy or dynamic in a static SiteSucker archive.

Query captures such as \`add-to-cart\`, \`p=123\`, \`mailpoet_page\`, taxonomy query duplicates, and similar files are rejected when a clean permalink version exists.

## Sources Scanned

| Source | Path | Files scanned |
| --- | --- | ---: |
${sourceRows}

## Rejections

| Reason | Count |
| --- | ---: |
${rejectedRows || '| none | 0 |'}

## Raw Capture Quarantine

| From | To |
| --- | --- |
${movedRows}
`;

  await fs.writeFile(reportPath, report);
  await fs.writeFile(
    manifestPath,
    JSON.stringify(
      {
        generated: today,
        target: path.relative(ROOT, targetRoot),
        fileCount: copied.length,
        htmlPageCount: htmlPages.length,
        productPageCount: productPages.length,
        totalBytes,
        scannedSources,
        rejected: Object.fromEntries([...rejected.entries()].sort((a, b) => b[1] - a[1])),
        quarantine,
        previousTarget,
        files: copied,
      },
      null,
      2,
    ),
  );

  console.log(`Canonical capture: ${path.relative(ROOT, targetRoot)}`);
  console.log(`Files kept: ${copied.length}`);
  console.log(`HTML pages kept: ${htmlPages.length}`);
  console.log(`Product pages kept: ${productPages.length}`);
  console.log(`Approximate kept size: ${(totalBytes / 1024 / 1024).toFixed(1)} MB`);
  if (quarantine) {
    console.log(`Raw captures moved to: ${quarantine.trashRoot}`);
  }
  console.log(`Report: ${path.relative(ROOT, reportPath)}`);
  console.log(`Manifest: ${path.relative(ROOT, manifestPath)}`);
}

main().catch((error) => {
  console.error(error.message);
  process.exit(1);
});
