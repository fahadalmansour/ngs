#!/usr/bin/env bash
# Fix 404 page slugs on neogen.store
# Runs via SSH: ssh -p 21098 fsalmansour@162.254.39.146 'bash -s' < fix_wordpress_slugs.sh
# Or copy to VPS and run directly

set -e

WP_ROOT="$HOME/neogen.store"
cd "$WP_ROOT"

echo "=== Fixing WordPress page slugs on neogen.store ==="

# Rename slugs to canonical expected URLs
wp --allow-root post update 3   --post_name=privacy-policy  && echo "OK: /privacy/ -> /privacy-policy/"
wp --allow-root post update 473 --post_name=refund-policy   && echo "OK: /returns/ -> /refund-policy/"
wp --allow-root post update 31  --post_name=contact-us      && echo "OK: /contact/ -> /contact-us/"

# Flush rewrite rules
wp --allow-root rewrite flush --hard
echo "OK: Rewrite rules flushed"

echo ""
echo "=== Verifying URLs ==="
sleep 2

for path in privacy-policy refund-policy contact-us terms; do
    status=$(curl -s -o /dev/null -w "%{http_code}" -L "https://neogen.store/$path/")
    echo "  https://neogen.store/$path/ -> HTTP $status"
done

echo ""
echo "Done. Expected: all 200."
