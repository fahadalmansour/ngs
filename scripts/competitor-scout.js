#!/usr/bin/env node
/**
 * NeoGen Store thin scout wrapper.
 *
 * Delegates to repo-local Playwright scrapers (live-market-sync-*.js) for KSA
 * retailers and AliExpress; falls through to ~/scripts/competitor-scout/index.js
 * for everything else.
 *
 * Conforms to the JSON contract in ~/.claude/skills/competitor-scout/SKILL.md.
 *
 * Usage: node competitor-scout.js <URL> <SELECTOR>
 */

const { spawn } = require('node:child_process');
const path = require('node:path');
const os = require('node:os');

const ROUTES = [
  {
    pattern: /(^|\.)aliexpress\./i,
    script: path.join(__dirname, 'live-market-sync-aliexpress.js'),
    source: 'neogen-aliexpress',
  },
  {
    pattern: /(^|\.)(jarir|extra|noon|amazon\.sa)/i,
    script: path.join(__dirname, 'live-market-sync-ksa-retailers.js'),
    source: 'neogen-ksa-retailers',
  },
  {
    pattern: /(^|\.)inscope/i,
    script: path.join(__dirname, 'live-market-sync-inscope.js'),
    source: 'neogen-inscope',
  },
];

const FALLBACK_SCRIPT = path.join(os.homedir(), 'scripts', 'competitor-scout', 'index.js');
const FALLBACK_SOURCE = 'generic';

function emit(payload) {
  process.stdout.write(JSON.stringify(payload) + '\n');
}

function errorEnvelope(url, selector, message) {
  return {
    url,
    price: null,
    currency: 'unknown',
    availability: 'unknown',
    scraped_at: new Date().toISOString(),
    selector,
    status: 'error',
    message,
    source: FALLBACK_SOURCE,
  };
}

function pickRoute(url) {
  let host = '';
  try {
    host = new URL(url).host;
  } catch {
    return { script: FALLBACK_SCRIPT, source: FALLBACK_SOURCE };
  }
  for (const route of ROUTES) {
    if (route.pattern.test(host)) {
      return { script: route.script, source: route.source };
    }
  }
  return { script: FALLBACK_SCRIPT, source: FALLBACK_SOURCE };
}

/**
 * Normalise availability into the JSON-contract enum.
 *
 * Previously this ran as a single ternary that short-circuited on any
 * truthy `raw.availability` — including the strings "false" and "unknown",
 * which would map to "in_stock" instead of being passed through. Now we
 * accept an explicit allow-listed string in `raw.availability` first,
 * then fall back to the boolean `raw.in_stock`, then to "unknown".
 */
function normaliseAvailability(raw) {
  if (typeof raw.availability === 'string') {
    const a = raw.availability.toLowerCase().trim();
    if (a === 'in_stock' || a === 'out_of_stock' || a === 'unknown') {
      return a;
    }
  }
  if (raw.in_stock === true) return 'in_stock';
  if (raw.in_stock === false) return 'out_of_stock';
  return 'unknown';
}

function isJsonContract(obj) {
  return (
    obj &&
    typeof obj === 'object' &&
    'url' in obj &&
    'price' in obj &&
    'currency' in obj &&
    'status' in obj &&
    'source' in obj
  );
}

function reformatLegacy(raw, route, url, selector) {
  // Legacy live-market-sync-*.js scripts predate this contract. If their output
  // is already conformant, pass through; otherwise wrap into the contract shape.
  if (isJsonContract(raw)) {
    return { ...raw, source: raw.source || route.source };
  }

  const price = typeof raw.price === 'number' ? raw.price : Number(raw.price);
  return {
    url,
    price: Number.isFinite(price) ? price : null,
    currency: raw.currency || 'unknown',
    availability: normaliseAvailability(raw),
    scraped_at: raw.timestamp || raw.scraped_at || new Date().toISOString(),
    selector: raw.selector || selector,
    status: raw.status || (Number.isFinite(price) ? 'success' : 'error'),
    message: raw.message || '',
    source: route.source,
  };
}

function run(url, selector) {
  const route = pickRoute(url);
  const child = spawn('node', [route.script, url, selector], {
    stdio: ['ignore', 'pipe', 'pipe'],
  });

  let stdout = '';
  let stderr = '';
  child.stdout.on('data', (chunk) => { stdout += chunk.toString(); });
  child.stderr.on('data', (chunk) => { stderr += chunk.toString(); });

  child.on('close', (code) => {
    const trimmed = stdout.trim();
    let parsed = null;

    if (trimmed) {
      // Legacy scripts may emit multi-line; take the last JSON-looking line.
      const lines = trimmed.split('\n').filter(Boolean);
      const lastLine = lines[lines.length - 1];
      try {
        parsed = JSON.parse(lastLine);
      } catch {
        parsed = null;
      }
    }

    if (!parsed) {
      const message =
        code === 0
          ? `Delegated script ${route.script} produced no JSON output. stderr: ${stderr.slice(0, 200)}`
          : `Delegated script ${route.script} exited with code ${code}. stderr: ${stderr.slice(0, 200)}`;
      emit(errorEnvelope(url, selector, message));
      process.exit(code === 0 ? 1 : code);
      return;
    }

    emit(reformatLegacy(parsed, route, url, selector));
    process.exit(parsed.status === 'success' ? 0 : 1);
  });

  child.on('error', (err) => {
    emit(errorEnvelope(url, selector, `Failed to spawn ${route.script}: ${err.message}`));
    process.exit(1);
  });
}

function main() {
  const [url, selector] = process.argv.slice(2);
  if (!url || !selector) {
    process.stderr.write('Usage: node competitor-scout.js <URL> <SELECTOR>\n');
    process.exit(2);
  }
  run(url, selector);
}

main();
