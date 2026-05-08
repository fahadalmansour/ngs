#!/usr/bin/env bash
#
# diagnose-ar-redirect.sh — find the source of the /ar/ → product-page redirect.
#
# Read-only investigation. Run on the VPS over SSH:
#   ssh blazr-vps   # or whatever your VPS alias is
#   cd /var/www/ngs1   # or whatever your install root is
#   bash diagnose-ar-redirect.sh > /tmp/ar-diag.txt
#   exit
#   scp blazr-vps:/tmp/ar-diag.txt ~/Desktop/
#
# Then paste the contents of ~/Desktop/ar-diag.txt back into the chat and
# I'll propose a concrete fix on round 3.
#
# No mutations. No DB writes. No file edits.

set -u

WP_PATH="${WP_PATH:-$(pwd)}"
SITE_HOST="${SITE_HOST:-neogen.store}"

section() {
    printf "\n==================================================================\n"
    printf "%s\n" "$1"
    printf "==================================================================\n"
}

# ----- 1. .htaccess --------------------------------------------------------
section "1. .htaccess (every redirect/rewrite rule)"
if [[ -f "$WP_PATH/.htaccess" ]]; then
    cat -n "$WP_PATH/.htaccess"
else
    echo "(no .htaccess at $WP_PATH/.htaccess — possibly nginx-only or different path)"
    echo "Try: find / -name '.htaccess' 2>/dev/null | head"
fi

# ----- 2. WP options that mention 'ar', 'redirect', 'locale' ---------------
section "2. WP options matching /ar|redirect|locale|polylang|weglot|wpml/"
if command -v wp >/dev/null 2>&1; then
    wp option list --search='*redirect*' --format=table 2>/dev/null
    echo "---"
    wp option list --search='*locale*'   --format=table 2>/dev/null
    echo "---"
    wp option list --search='*ar*'       --format=table 2>/dev/null | head -40
    echo "---"
    wp option list --search='*polylang*' --format=table 2>/dev/null
    echo "---"
    wp option list --search='*weglot*'   --format=table 2>/dev/null
    echo "---"
    wp option list --search='*wpml*'     --format=table 2>/dev/null
else
    echo "(wp-cli not found in PATH — install via 'curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar' and 'chmod +x wp-cli.phar' or add to PATH)"
fi

# ----- 3. Active plugins that touch URL routing ----------------------------
section "3. Active plugins matching /redirect|polylang|weglot|wpml|locale|translat/"
if command -v wp >/dev/null 2>&1; then
    wp plugin list --status=active --format=csv 2>/dev/null \
        | grep -iE 'redirect|polylang|weglot|wpml|locale|translat|elementor' \
        || echo "(no active plugins matched — locale handling may be in a theme or .htaccess)"
fi

# ----- 4. Rank Math / Redirection plugin redirect entries ------------------
section "4. Stored redirects (Rank Math + Redirection plugin)"
if command -v wp >/dev/null 2>&1; then
    echo "--- Rank Math redirects (post_type=redirect) ---"
    RM_COUNT=$(wp post list --post_type=redirect --format=ids 2>/dev/null | wc -w | tr -d ' ')
    echo "count: $RM_COUNT"
    if [[ "$RM_COUNT" -gt 0 && "$RM_COUNT" -lt 200 ]]; then
        wp post list --post_type=redirect --format=table --fields=ID,post_title,post_status 2>/dev/null
    fi

    echo ""
    echo "--- Redirection plugin (post_type=redirection) ---"
    RD_COUNT=$(wp post list --post_type=redirection --format=ids 2>/dev/null | wc -w | tr -d ' ')
    echo "count: $RD_COUNT"
    if [[ "$RD_COUNT" -gt 0 && "$RD_COUNT" -lt 200 ]]; then
        wp post list --post_type=redirection --format=table --fields=ID,post_title 2>/dev/null
    fi

    echo ""
    echo "--- DB-direct check for any redirect tables ---"
    wp db query "SHOW TABLES LIKE '%redirect%';" 2>/dev/null
    wp db query "SHOW TABLES LIKE '%redirection%';" 2>/dev/null
    wp db query "SHOW TABLES LIKE '%rank_math%';" 2>/dev/null
fi

# ----- 5. WP rewrite rules touching /ar -----------------------------------
section "5. WP rewrite rules with 'ar' in pattern or target"
if command -v wp >/dev/null 2>&1; then
    wp rewrite list --format=table 2>/dev/null | grep -iE '/ar|^ar' | head -40
    echo "---"
    echo "If empty above, /ar/ is NOT a registered WP rewrite rule (so the redirect is upstream of WP — server config, plugin, or .htaccess)."
fi

# ----- 6. What does WP think /ar/ resolves to? ----------------------------
section "6. Direct curl to localhost (bypass any external CDN)"
echo "--- HEAD request, no redirects followed ---"
curl -sI --max-time 10 "http://127.0.0.1/ar/" -H "Host: $SITE_HOST" 2>&1 | head -20
echo ""
echo "--- HEAD request, follow redirects ---"
curl -sIL --max-time 10 "http://127.0.0.1/ar/" -H "Host: $SITE_HOST" 2>&1 | head -30

# ----- 7. Active theme + any locale switcher --------------------------------
section "7. Active theme + theme files mentioning /ar/"
if command -v wp >/dev/null 2>&1; then
    THEME=$(wp theme list --status=active --field=name 2>/dev/null)
    echo "Active theme: $THEME"
    if [[ -n "$THEME" && -d "$WP_PATH/wp-content/themes/$THEME" ]]; then
        grep -rEn --include='*.php' "/ar/|hreflang|switch_to_locale" "$WP_PATH/wp-content/themes/$THEME" 2>/dev/null | head -20
    fi
fi

# ----- 8. mu-plugins matching ----------------------------------------------
section "8. mu-plugins/ files mentioning /ar/, hreflang, or locale switcher"
if [[ -d "$WP_PATH/wp-content/mu-plugins" ]]; then
    grep -rEn --include='*.php' "/ar/|hreflang|switch_to_locale|locale.*ar_SA.*=" "$WP_PATH/wp-content/mu-plugins" 2>/dev/null | head -30
fi

# ----- 9. Server detection -------------------------------------------------
section "9. Server detection (LiteSpeed / nginx / Apache)"
if [[ -f /etc/nginx/nginx.conf ]]; then
    echo "nginx detected — check /etc/nginx/sites-enabled/* for redirect rules"
    sudo -n grep -rEn 'ar/|return 301|rewrite' /etc/nginx/sites-enabled/ 2>/dev/null | head -20
fi
if pgrep -f litespeed >/dev/null 2>&1 || pgrep -f openlitespeed >/dev/null 2>&1; then
    echo "LiteSpeed detected — check /usr/local/lsws/conf/vhosts/*/vhconf.conf for rewrite rules"
fi

section "Done. Save this output and paste back into the chat."
