#!/usr/bin/env bash
#
# post-deploy-probe.sh — verify a Pull Latest deploy succeeded.
# Read-only. No login. No mutations. Run from repo root.
#
# Usage:
#   ./scripts/post-deploy-probe.sh                # uses VERSION from apps/neogen-custom
#   ./scripts/post-deploy-probe.sh --verbose      # also dumps raw response details
#   ./scripts/post-deploy-probe.sh 1.39.0         # check against an explicit version
#   ./scripts/post-deploy-probe.sh --site novakeys.store --version 1.2.3
#
# Exit codes:
#   0 = all critical checks passed
#   1 = at least one critical check failed
#
# Critical checks (red on failure, exit 1):
#   - Homepage returns HTTP 200
#   - No PHP fatals on homepage
#   - Live sitemap reachable
#   - Removed product categories return 404
#   - No gift-card asset paths in homepage HTML
#
# Soft checks (yellow on failure, do NOT block exit):
#   - Version badge match (only verifiable in admin bar; we check generator meta proximity)
#   - Plugin sprawl warnings (duplicate cookie / Stripe gateways)
#   - Legal pages present
#   - LiteSpeed full-page cache header present
#   - JSON-LD product schema on a sample product page

set -u

# ------------------------------- defaults -------------------------------------
SITE="https://neogen.store"
EXPECTED_VERSION=""
VERBOSE=0
TIMEOUT=20

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"

# ----------------------------- arg parsing ------------------------------------
while [[ $# -gt 0 ]]; do
    case "$1" in
        --site) SITE="$2"; shift 2;;
        --version) EXPECTED_VERSION="$2"; shift 2;;
        --verbose|-v) VERBOSE=1; shift;;
        --timeout) TIMEOUT="$2"; shift 2;;
        --help|-h)
            sed -n '/^# /,/^[^#]/p' "$0" | head -25 | sed 's/^# //'
            exit 0;;
        *)
            # Bare positional = expected version
            if [[ -z "$EXPECTED_VERSION" && "$1" =~ ^[0-9]+\.[0-9]+\.[0-9]+ ]]; then
                EXPECTED_VERSION="$1"; shift
            else
                echo "Unknown arg: $1" >&2; exit 2
            fi;;
    esac
done

# Auto-detect version from apps/neogen-custom/VERSION if not given
if [[ -z "$EXPECTED_VERSION" && -f "$REPO_ROOT/apps/neogen-custom/VERSION" ]]; then
    EXPECTED_VERSION="$(tr -d '[:space:]' < "$REPO_ROOT/apps/neogen-custom/VERSION")"
fi

# ----------------------------- helpers ----------------------------------------
RED='\033[0;31m'; YELLOW='\033[0;33m'; GREEN='\033[0;32m'; CYAN='\033[0;36m'; BOLD='\033[1m'; NC='\033[0m'

CRITICAL_FAILS=0
SOFT_WARNS=0

ok()    { printf "${GREEN}  ✓${NC} %s\n" "$1"; }
warn()  { printf "${YELLOW}  ⚠${NC} %s\n" "$1"; SOFT_WARNS=$((SOFT_WARNS+1)); }
fail()  { printf "${RED}  ✗${NC} %s\n" "$1"; CRITICAL_FAILS=$((CRITICAL_FAILS+1)); }
info()  { printf "${CYAN}  i${NC} %s\n" "$1"; }
section() { printf "\n${BOLD}%s${NC}\n" "$1"; }

fetch_status() {
    # echo "STATUS|TTFB|SIZE"
    curl -sS -o /dev/null --max-time "$TIMEOUT" -L -w "%{http_code}|%{time_starttransfer}|%{size_download}" "$1" 2>/dev/null || echo "000|0|0"
}

fetch_body() {
    curl -sS --max-time "$TIMEOUT" -L "$1" 2>/dev/null
}

fetch_headers() {
    curl -sS -I --max-time "$TIMEOUT" -L "$1" 2>/dev/null
}

# ----------------------------- header -----------------------------------------
printf "${BOLD}${CYAN}NeoGen post-deploy probe${NC} — $(date -u +'%Y-%m-%d %H:%M:%S UTC')\n"
printf "Site:     %s\n" "$SITE"
printf "Expected: %s\n" "${EXPECTED_VERSION:-(not specified)}"

# ============================ critical checks =================================
section "Critical"

# 1. Homepage HTTP
HOMEPAGE_RESULT=$(fetch_status "$SITE/")
IFS='|' read -r STATUS TTFB SIZE <<< "$HOMEPAGE_RESULT"
if [[ "$STATUS" == "200" ]]; then
    ok "Homepage HTTP 200 (TTFB ${TTFB}s, ${SIZE}B)"
    if [[ $(echo "$TTFB > 1.0" | bc -l 2>/dev/null) == "1" ]]; then
        warn "  TTFB ${TTFB}s exceeds 1.0s budget — check LiteSpeed cache"
    fi
else
    fail "Homepage returned HTTP $STATUS"
fi

# Cache headers
HEADERS=$(fetch_headers "$SITE/")
if echo "$HEADERS" | grep -qi "x-litespeed-cache:"; then
    ok "x-litespeed-cache header present (full-page cache active)"
else
    warn "No x-litespeed-cache header — full-page cache likely OFF"
fi

# 2. Quick fatal scan — fetch homepage body and check the first ~3 lines
HOMEPAGE_BODY=$(fetch_body "$SITE/")
if echo "$HOMEPAGE_BODY" | head -3 | grep -qE 'Fatal|Parse error|Warning|Notice|Deprecated'; then
    fail "PHP error markers in first 3 lines of homepage HTML"
    [[ $VERBOSE -eq 1 ]] && echo "$HOMEPAGE_BODY" | head -5
else
    ok "No PHP fatals at top of homepage HTML"
fi

# 3. Sitemap reachable + product count
SITEMAP_BODY=$(fetch_body "$SITE/wp-sitemap-posts-product-1.xml")
PRODUCT_COUNT=$(echo "$SITEMAP_BODY" | grep -oE '<loc>' | wc -l | tr -d ' ')
if [[ "$PRODUCT_COUNT" -gt 0 ]]; then
    ok "Sitemap product count: $PRODUCT_COUNT"
    if [[ -f "$REPO_ROOT/data/catalogs/master/Neogen_Master_Catalog_Blueprint.csv" ]]; then
        MASTER_COUNT=$(python3 -c "import csv; print(sum(1 for _ in csv.DictReader(open('$REPO_ROOT/data/catalogs/master/Neogen_Master_Catalog_Blueprint.csv'))))" 2>/dev/null || echo "?")
        if [[ "$MASTER_COUNT" != "?" ]]; then
            DELTA=$((PRODUCT_COUNT - MASTER_COUNT))
            if [[ "$DELTA" -eq 0 ]]; then
                ok "  Live count matches master ($MASTER_COUNT)"
            elif [[ "$DELTA" -gt 0 ]]; then
                warn "  Live has $DELTA orphan products vs master ($MASTER_COUNT) — reconciliation needed"
            else
                warn "  Live missing $((-DELTA)) products vs master ($MASTER_COUNT) — re-import may be needed"
            fi
        fi
    fi
else
    fail "Sitemap returned no products"
fi

# 4. Removed categories return 404
for cat_slug in "gift-cards" "gift-cards-software-keys" "gaming"; do
    URL="$SITE/product-category/$cat_slug/"
    STATUS_LINE=$(fetch_status "$URL")
    CAT_STATUS=$(echo "$STATUS_LINE" | cut -d'|' -f1)
    if [[ "$CAT_STATUS" == "404" ]]; then
        ok "/product-category/$cat_slug/ → 404 (removed cleanly)"
    elif [[ "$CAT_STATUS" == "200" ]]; then
        fail "/product-category/$cat_slug/ → 200 (CATEGORY STILL LIVE — needs trashing)"
    else
        warn "/product-category/$cat_slug/ → $CAT_STATUS (unexpected)"
    fi
done

# 5. No gift-card asset paths in homepage HTML
GC_REFS=$(echo "$HOMEPAGE_BODY" | grep -oE '/wp-content/[^/]+/[^/]+/(neogen-gift-cards?|gift-cards-header|neogen-gift-card-keys|gift-cards/[a-z]+\.webp)' | sort -u)
if [[ -z "$GC_REFS" ]]; then
    ok "No gift-card asset paths in homepage HTML"
else
    fail "Gift-card asset paths still referenced in HTML:"
    echo "$GC_REFS" | sed 's/^/    /'
fi

# ============================ soft checks =====================================
section "Soft"

# Version + generator meta
WP_VER=$(echo "$HOMEPAGE_BODY" | grep -oE '<meta name="generator" content="WordPress [0-9.]+' | head -1 | grep -oE '[0-9.]+$')
WC_VER=$(echo "$HOMEPAGE_BODY" | grep -oE '<meta name="generator" content="WooCommerce [0-9.]+' | head -1 | grep -oE '[0-9.]+$')
EL_VER=$(echo "$HOMEPAGE_BODY" | grep -oE 'Elementor [0-9.]+' | head -1 | grep -oE '[0-9.]+$')
[[ -n "$WP_VER" ]] && info "WordPress: $WP_VER"
[[ -n "$WC_VER" ]] && info "WooCommerce: $WC_VER"
[[ -n "$EL_VER" ]] && info "Elementor: $EL_VER"

if [[ -n "$EXPECTED_VERSION" ]]; then
    info "Expected NEOGEN_CUSTOM_VERSION: $EXPECTED_VERSION"
    info "  (admin-bar badge only visible when logged in — please verify 'NG $EXPECTED_VERSION' manually)"
fi

# Plugin sprawl
ACTIVE_PLUGINS=$(echo "$HOMEPAGE_BODY" | grep -oE '/wp-content/(mu-plugins|plugins)/[a-zA-Z0-9_\-]+/' | sort -u)
if echo "$ACTIVE_PLUGINS" | grep -q cookieadmin && echo "$ACTIVE_PLUGINS" | grep -q cookieadmin-pro; then
    warn "Both cookieadmin AND cookieadmin-pro active — pick one and deactivate the other"
fi
if echo "$ACTIVE_PLUGINS" | grep -q woocommerce-payments && echo "$ACTIVE_PLUGINS" | grep -q woocommerce-gateway-stripe; then
    warn "Both woocommerce-payments AND woocommerce-gateway-stripe active — overlapping; pick one"
fi

# Legal pages — actual NeoGen URLs (per docs/ops/LEGAL-COMPLIANCE-CHECKLIST.md)
# Returns are folded into the Terms (Section IV) and Refund Policy pages, so
# we don't probe a separate /return-policy/ — the real merchant URL set is:
declare -a LEGAL_PAGES=(
    "terms|Terms & Conditions|paid-acquisition blocker if missing"
    "refund-policy|Refund & Returns Policy|paid-acquisition blocker if missing"
    "privacy-policy|Privacy Policy|KSA PDPL requires a privacy policy"
    "contact-us|Contact Us|MOC e-commerce law Article 14 requires it"
    "about|About|trust signal — soft warn"
)
for entry in "${LEGAL_PAGES[@]}"; do
    IFS='|' read -r slug label warn_msg <<< "$entry"
    URL="$SITE/$slug/"
    STATUS_LINE=$(fetch_status "$URL")
    PAGE_STATUS=$(echo "$STATUS_LINE" | cut -d'|' -f1)
    if [[ "$PAGE_STATUS" == "200" ]]; then
        ok "/$slug/ → 200 ($label)"
    elif [[ "$PAGE_STATUS" == "404" ]]; then
        warn "/$slug/ → 404 ($label — $warn_msg)"
    else
        warn "/$slug/ → $PAGE_STATUS ($label — unexpected)"
    fi
done

# Arabic locale
AR_RESULT=$(fetch_status "$SITE/ar/")
AR_STATUS=$(echo "$AR_RESULT" | cut -d'|' -f1)
AR_FINAL=$(curl -sS -o /dev/null -w "%{url_effective}" --max-time "$TIMEOUT" -L "$SITE/ar/" 2>/dev/null || echo "?")
if [[ "$AR_FINAL" == "$SITE/ar/" ]]; then
    ok "/ar/ resolves to itself ($AR_STATUS)"
elif [[ "$AR_FINAL" == *"/ar/"* ]]; then
    ok "/ar/ → $AR_FINAL (Arabic landing page)"
elif [[ "$AR_FINAL" == *"/product/"* ]]; then
    warn "/ar/ redirects to a product page ($AR_FINAL) — locale broken"
else
    warn "/ar/ → $AR_FINAL ($AR_STATUS) — verify Arabic landing"
fi

# JSON-LD on a sample product page
SAMPLE_PRODUCT=$(echo "$SITEMAP_BODY" | grep -oE '<loc>[^<]+</loc>' | head -1 | sed 's|<[^>]*>||g')
if [[ -n "$SAMPLE_PRODUCT" ]]; then
    PRODUCT_BODY=$(fetch_body "$SAMPLE_PRODUCT")
    JSONLD_COUNT=$(echo "$PRODUCT_BODY" | grep -oE 'application/ld\+json' | wc -l | tr -d ' ')
    if [[ "$JSONLD_COUNT" -gt 0 ]]; then
        ok "Sample product page emits $JSONLD_COUNT JSON-LD block(s)"
    else
        warn "Sample product page has 0 JSON-LD blocks — Google rich-result eligibility lost"
    fi
fi

# wp-login exposure
LOGIN_STATUS=$(fetch_status "$SITE/wp-login.php" | cut -d'|' -f1)
if [[ "$LOGIN_STATUS" == "200" ]]; then
    warn "/wp-login.php → 200 (no IP allowlist — credential-stuffing risk)"
elif [[ "$LOGIN_STATUS" == "403" ]] || [[ "$LOGIN_STATUS" == "401" ]]; then
    ok "/wp-login.php → $LOGIN_STATUS (access restricted)"
fi

# xmlrpc exposure
if echo "$HEADERS" | grep -qi "x-pingback:"; then
    warn "x-pingback header present — xmlrpc.php advertised (consider disabling)"
fi

# CSP enforcement
if echo "$HEADERS" | grep -qi "content-security-policy:"; then
    ok "CSP enforced"
elif echo "$HEADERS" | grep -qi "content-security-policy-report-only:"; then
    info "CSP is report-only (not enforced) — fine for monitoring, not for blocking attacks"
fi

# ============================ summary =========================================
section "Summary"
printf "  Critical fails: %d\n" "$CRITICAL_FAILS"
printf "  Soft warns:     %d\n" "$SOFT_WARNS"

if [[ "$CRITICAL_FAILS" -eq 0 ]]; then
    printf "${GREEN}${BOLD}✓ Deploy is healthy${NC}\n"
    exit 0
else
    printf "${RED}${BOLD}✗ Deploy has %d critical issue(s)${NC}\n" "$CRITICAL_FAILS"
    exit 1
fi
