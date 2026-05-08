# Phase B — credential rotation + git history rewrite

**Date:** 2026-05-08
**Trigger:** readiness-2026-05-08 audit BLOCKER #1 — live secrets in git history.
**Companion to:** `apps/neogen-custom@v1.41.0` Phase A pass (HEAD-side cleanup; this runbook does the history-side cleanup + actual key rotations).
**Owner:** Fahad Almansour (the merchant must execute steps 1–4 personally; steps 5–8 can be co-executed with the assistant).

> **Read this whole document before running any command.**
> Steps 5–7 force-push rewritten history. Anyone with a clone of the repo (deploy plugin on the VPS, any laptop, any CI) needs to **re-clone fresh** after the force-push or their next pull will fail. There's no "undo" for a force-push that's already propagated to GitHub mirrors and downstream clones — so each step has its own gate, and the runbook stops cleanly between steps in case anything looks wrong.

---

## What's being cleaned

| File path (in some git history) | Repo | Secret type | Audit ref |
|---|---|---|---|
| `apps/NGS/db.sql` | local disk only — *not in any git history* | WC REST consumer-key + secret pair (DSers integration) — observed `ck_3cf8…` / `cs_0f08…` | BLOCKER #1 |
| `archive/wordpress-exports/negt/db.sql` | root `ngs` history (commit `a5f52a7`) | WP DB dump including the same WC REST keys + WP user_pass hashes | BLOCKER #1 |
| `archive/wordpress-exports/negt/wp-config.php` | root `ngs` history (until `ee41c8e` removed it from HEAD) | DB_PASSWORD + 8 WP salts | BLOCKER #1 |
| `archive/wordpress-exports/negt/wp-config-original.php` | root `ngs` history | DB_PASSWORD + 8 WP salts | BLOCKER #1 |
| `prototypes/neogen-custom-design/neogen-store-redesign/wp-config.php` | root `ngs` history | DB_PASSWORD + 8 WP salts | BLOCKER #1 |
| `scripts/archive/fix_footer_automated.py` | root `ngs` history (commit `a5f52a7`, removed from HEAD by `ee41c8e`) | hardcoded WP admin password `OtiXQOQTG2WAEg==` | BLOCKER #1 |
| `scripts/archive/fix_footer_elementor.py` | same | same | BLOCKER #1 |
| `scripts/archive/fix_footer_final.py` | same | same | BLOCKER #1 |
| `scripts/archive/fix_wordpress_site_v2.py` | same | same | BLOCKER #1 |

The `apps/NGS/db.sql` file is gitignored in both repos — it's never been committed. So no `git filter-repo` is needed for it; **rotate the keys + delete the local file**.

The `archive/products-legacy/Products/sql/001_hub_schema.sql` file is tracked but it's a schema-only file, not a data dump. Verify it carries no secrets (`grep -E 'INSERT INTO|DEFAULT|password' archive/products-legacy/Products/sql/001_hub_schema.sql`) — if clean, leave it alone.

The `archive/backups/wp-prod-2026-*.sql` files are gitignored — never in history, so they don't need filter-repo. **Rotate keys + verify the files are in `.gitignore` (already done in Phase A).**

---

## Pre-flight (before touching any credential)

Open six things in tabs / terminals so you can switch quickly:

1. WP admin → **WooCommerce → Settings → Advanced → REST API** (tab open, signed in)
2. WP admin → **Users → Profile** (tab open, signed in)
3. WP admin → **Tools → NeoGen Deploy** (tab open, signed in — for re-pasting the rotated GitHub PAT in step 4)
4. Terminal SSH'd to the VPS: `ssh vps` (currently `ssh -p 21098 fsalmansour@162.254.39.146`)
5. Local terminal at `/Users/fahadalmansour/sites/neogen-store/`
6. https://api.wordpress.org/secret-key/1.1/salt/ (tab open — fresh salts on every reload)

Confirm:
- [ ] You're signed in as the WP admin (the user whose password is being rotated).
- [ ] You have a backup admin account with `manage_options` cap (or you accept the risk of being locked out — see step 2.5 fallback).
- [ ] You can SSH to the VPS and run `wp --info` successfully there.
- [ ] You have a paper/Notes-app place to record the new credentials as they're created.

---

## Step 1 — Rotate the WooCommerce REST API keys

**Why first:** the `cs_…` consumer-secret pair from the Phase A audit is the one credential most likely to be actively scraped by the GitHub Secret Scanning bot. Rotating it first invalidates the leaked pair before anyone exploits it.

1. WP admin → **WooCommerce → Settings → Advanced → REST API**.
2. Find the key labelled "DSers integration" (or whichever description matches the leaked `ck_3cf87710…`).
3. Click **Revoke** on that row.
4. Confirm the row is gone.
5. Click **Add Key** → set Description: `DSers (rotated 2026-05-08)`, User: the same user who owned the original key, Permissions: same as the original (probably Read/Write).
6. Click **Generate API Key**. Copy the new `ck_…` and `cs_…` pair to your Notes app.
7. Update DSers (or whatever third-party consumes this key) with the new pair. **Until you do step 7, the integration is dead.**

Verification:
```bash
ssh vps 'cd /home/fsalmansour/neogen.store && wp wc rest-api list --user=1 2>&1 | head' | grep -E 'consumer_key|description'
# Expect: only the new key labelled "DSers (rotated 2026-05-08)" present.
# Expect: the old `ck_3cf87710…` is NOT present.
```

If the verification shows the old key still present — stop. Don't proceed to step 2 until step 1 is fully complete.

---

## Step 2 — Rotate the WP admin password

**Audit observation:** the password `OtiXQOQTG2WAEg==` was hardcoded in 4 `scripts/archive/fix_footer_*.py` files in commit `a5f52a7`. Phase A's working-tree fix landed in `ee41c8e` but the password is still in history until step 5.

1. WP admin → **Users → Profile** (the admin user — the one whose password is leaked).
2. Scroll to **Account Management** → **New Password** → click **Generate password**.
3. Copy the generated password. Save in your Notes app (you'll need to re-login after submission).
4. Click **Update Profile**.

   You'll be logged out automatically because WP rotates session tokens on password change. Sign back in with the new password.

5. **(Optional but recommended)** Add 2FA to the admin profile if not already on. This addresses the audit's HIGH "Deploy-trust gap: 2FA on the deploy admin not confirmable" — even with the rotation, the audit's HIGH item is still open until 2FA is enabled.

Verification:
- The old password `OtiXQOQTG2WAEg==` no longer logs you in (try in an incognito window, then come back to the main session).
- WP-CLI confirms last password change date:
  ```bash
  ssh vps 'cd /home/fsalmansour/neogen.store && wp user get 1 --field=user_registered'
  ```
  (this shows account creation; password-change-date isn't directly readable in WP-CLI, but a fresh login attempt is sufficient verification)

---

## Step 3 — Regenerate the WP salts

**Why:** the 8 SALT/KEY constants in the leaked `wp-config.php` files sign every authenticated cookie + nonce on the live site. If the leak hasn't been actively exploited yet, rotating now invalidates all in-flight session tokens and starts fresh.

**Side effect:** every logged-in user (admins, customers with active sessions) gets logged out on the next request. Schedule this for a low-traffic window if customer impact is a concern.

1. Visit **https://api.wordpress.org/secret-key/1.1/salt/** in a browser → reload until you see fresh values (every reload generates new ones).
2. Copy the entire 8-line block.
3. SSH to the VPS:
   ```bash
   ssh vps
   cd /home/fsalmansour/neogen.store
   cp wp-config.php wp-config.php.backup-$(date +%Y%m%d-%H%M%S)   # belt-and-braces backup
   ```
4. Edit `wp-config.php` with the editor of your choice (`nano wp-config.php` or `vi wp-config.php`).
5. Find the existing SALT/KEY block — looks like:
   ```php
   define('AUTH_KEY',         'put your unique phrase here');
   define('SECURE_AUTH_KEY',  'put your unique phrase here');
   ...
   ```
6. **Replace those 8 lines** with the fresh block from api.wordpress.org. Save and exit.
7. Verify the file still parses:
   ```bash
   php -l wp-config.php
   ```
   Expect: `No syntax errors detected`.
8. Bounce PHP-FPM (or wait for the next request — opcache will pick up the change, but for safety):
   ```bash
   # cPanel users typically restart Apache via the cPanel UI, or:
   sudo systemctl reload apache2 2>&1   # may not be available — try the cPanel UI
   ```
   On shared cPanel, you may not have systemctl access; just wait 60s and the next page load picks up the change.

Verification:
- Hit the homepage in a private window → renders normally (no 500).
- Try a stale cookie from before the rotation (e.g. an admin session in a tab that was open before step 6) → should redirect to login.

If anything 500s after the salt rotation:
```bash
ssh vps 'cd /home/fsalmansour/neogen.store && cp wp-config.php.backup-* wp-config.php'
# next page load reverts. Re-investigate before re-trying.
```

---

## Step 4 — Rotate the GitHub PAT used by the NeoGen Deploy plugin

**Why:** `apps/neogen-deploy/` uses a GitHub PAT (Personal Access Token) stored in WP options to pull from the private repo. The PAT itself is encrypted at rest (libsodium + WP salts) but step 3 just rotated the salts, so the PAT decryption may have already broken — better to issue a fresh PAT and re-paste it.

1. https://github.com/settings/tokens → find the existing PAT used by NeoGen Deploy → **Delete**.
2. Click **Generate new token (classic)** with the same scopes as before (probably `repo` + `read:org`, expiry 90 days).
3. Copy the new `ghp_…` token. Save in Notes.
4. WP admin → **Tools → NeoGen Deploy** → re-paste the PAT in the field provided → save.
5. **DO NOT click Pull Latest yet** — step 5 is about to rewrite history; the deploy plugin would pull a now-broken history into prod.

---

## Step 5 — `git filter-repo` to scrub secrets from history

**Why this is destructive:** every commit hash from `a5f52a7` onwards changes. Anyone with a clone of `fahadalmansour/ngs` will have a divergent history after step 6's force-push. The deploy plugin's clone on the VPS becomes stale and has to be re-cloned.

5.1 Install `git-filter-repo` if not present:
```bash
brew install git-filter-repo   # macOS
# or: pip3 install git-filter-repo
git filter-repo --version       # confirm
```

5.2 Make a safety clone (this is your "oh shit" backup):
```bash
cd /Users/fahadalmansour/sites/
git clone --mirror /Users/fahadalmansour/sites/neogen-store/.git neogen-store-prefilter-backup-$(date +%Y%m%d-%H%M%S).git
ls -la neogen-store-prefilter-backup-*.git/ | head
```
Confirm the backup mirror exists. If filter-repo goes wrong, you restore by `git clone` from this mirror.

5.3 Run filter-repo on the root repo:
```bash
cd /Users/fahadalmansour/sites/neogen-store

git filter-repo --invert-paths \
  --path archive/wordpress-exports/negt/db.sql \
  --path archive/wordpress-exports/negt/wp-config.php \
  --path archive/wordpress-exports/negt/wp-config-original.php \
  --path prototypes/neogen-custom-design/neogen-store-redesign/wp-config.php \
  --path scripts/archive/fix_footer_automated.py \
  --path scripts/archive/fix_footer_elementor.py \
  --path scripts/archive/fix_footer_final.py \
  --path scripts/archive/fix_wordpress_site_v2.py \
  --force
```

filter-repo will warn:
```
Found a fresh clone or no remote refs — proceeding.
…
Parsed N commits
…
New history written, now repacking…
```

After it finishes:
```bash
git log --all --diff-filter=A --name-only --format= -- archive/wordpress-exports/negt/db.sql 2>&1
# Expect: empty output. The file is gone from every commit.

grep -roE 'OtiXQOQTG2WAEg==' .git/  2>/dev/null | head
# Expect: empty. The hardcoded password no longer exists in any pack file.
```

**If either of those greps returns a hit, do not proceed.** filter-repo missed something. Restart from the safety clone.

5.4 filter-repo may have stripped the `origin` remote (it does this defensively, since pushing to the same origin without --force overwrites history). Re-add:
```bash
git remote add origin git@github.com:fahadalmansour/ngs.git    # or however origin was configured
```

5.5 The current Phase A commits (v1.41.0 → v1.47.0) will have been re-hashed. The submodule pointer commits in the root repo still reference the un-rewritten commit hashes from `apps/neogen-custom`. **These are unaffected — `apps/neogen-custom` is its own repo and we didn't rewrite its history.** The submodule pointers stay valid.

---

## Step 6 — Force-push rewritten history

**Last gate before irreversible.** Confirm:
- [ ] Step 5.3 completed cleanly with no remaining secret hits.
- [ ] Backup mirror exists at `/Users/fahadalmansour/sites/neogen-store-prefilter-backup-*.git`.
- [ ] You've notified anyone else with a clone (CI runners, other devs) that they need to re-clone.
- [ ] Step 4 just rotated the deploy plugin's PAT — its old clone on the VPS is about to become stale; we'll re-clone in step 8.

Then:
```bash
git push origin main --force-with-lease
```

`--force-with-lease` is safer than `--force` — it refuses to push if the remote has new commits you haven't seen yet (i.e. someone else pushed during steps 1–5). If it refuses, fetch + re-run filter-repo against the new tip + retry.

---

## Step 7 — GitHub Secret Scanning notification

GitHub's automated secret-scanning will already have flagged the leaked WC keys, WP password, and possibly the WP salts. After the force-push:

1. https://github.com/fahadalmansour/ngs/security/secret-scanning → review the alerts.
2. For each alert: click **Close as → Revoked** (now true after steps 1–4).
3. If any alert is for a key still in use — re-rotate immediately.

Optional but defensible:
- Open a **private security advisory** at https://github.com/fahadalmansour/ngs/security/advisories/new describing the rotation. Useful audit trail if you ever need to show due-diligence to ZATCA / PDPL inspectors.

---

## Step 8 — Re-clone the deploy plugin's working copy on the VPS

```bash
ssh vps
cd /home/fsalmansour/neogen.store/wp-content/mu-plugins
# inspect the deploy-plugin working tree first; back up if anything custom lives there
ls -la neogen-custom/ neogen-pro/   # whatever the deploy plugin's working dirs are
```

The NeoGen Deploy plugin pulls into `wp-content/mu-plugins/neogen-custom/` and `wp-content/plugins/neogen-pro/` (per the plugin docs). After the force-push, those clones are at orphaned commits. The cleanest fix:

1. WP admin → **Tools → NeoGen Deploy** → click **Pull Latest** → it'll fetch the rewritten history and reset the working tree.
2. Verify admin-bar badge reads `NG 1.47.0` (the latest version we shipped this session).

If Pull Latest errors out with "fatal: refusing to merge unrelated histories" or "non-fast-forward":
```bash
ssh vps
cd /home/fsalmansour/neogen.store/wp-content/mu-plugins/neogen-custom   # or wherever the plugin clones to
git fetch origin
git reset --hard origin/main
```

Verification:
- `wp --path=/home/fsalmansour/neogen.store cache flush`
- Hit the homepage in incognito → renders cleanly.
- Hit a previously-zero-priced product → renders 10,000 SAR (v1.47.0 floor active).
- Hit `/?author=1` → 301 redirects to `/` (v1.41.0 anti-enum active).

---

## After Phase B

| Item | State |
|---|---|
| BLOCKER #1 — committed secrets | **Closed** if every grep in step 5.3 came back empty |
| HIGH — 2FA on deploy admin | Still open unless step 2.5 enabled it |
| HIGH — GitHub PAT rotation reminder | Open — set a calendar reminder for ~85 days from today (PAT expiry) |
| HIGH — single VPS = SPOF | Out of scope — separate runbook |

Re-run the post-deploy probe to confirm the cumulative state:
```bash
cd /Users/fahadalmansour/sites/neogen-store
./scripts/post-deploy-probe.sh > /tmp/post-phase-b-probe.txt
diff /tmp/post-phase-b-probe.txt ~/.claude/reports/neogen/readiness-2026-05-08.md
```

Save the probe output as `~/.claude/reports/neogen/readiness-2026-05-08-after-phase-b.md` for the audit trail.

---

## Rollback

- **Step 1 rollback:** the old WC REST keys are revoked permanently — no rollback. If DSers integration breaks, generate yet another fresh pair and update DSers again.
- **Step 2 rollback:** sign in with the new password (you saved it). If you somehow lost it: SSH to VPS → `wp user reset-password 1 --skip-email` → use the printed password.
- **Step 3 rollback:** restore from the wp-config.php.backup-* file you made in step 3.3.
- **Step 4 rollback:** revoke the new PAT, generate another, re-paste in NeoGen Deploy.
- **Step 5/6 rollback:** restore from the safety mirror clone made in step 5.2:
  ```bash
  cd /Users/fahadalmansour/sites/
  rm -rf neogen-store-broken
  mv neogen-store neogen-store-broken
  git clone neogen-store-prefilter-backup-*.git neogen-store
  cd neogen-store
  git push origin main --force-with-lease   # restore old history to GitHub
  ```
  Note: this re-introduces the secrets back into git history. Step 1–4 rotations stay valid (those keys are revoked at the source), but the old hashes show up again. Useful only if filter-repo went catastrophically wrong.

---

**Status of this runbook:** drafted by the assistant 2026-05-08 from `~/.claude/reports/neogen/readiness-2026-05-08.md` BLOCKER #1 + the v1.41.0 Phase A repo cleanup. **No destructive action has been taken.** The merchant executes this end-to-end at their own pace; the assistant can co-execute steps 5–8 with explicit per-step confirmation.
