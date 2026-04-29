# NeoGen Deploy

One-click git-pull deployment from a private GitHub repo into your WordPress filesystem.

## What it does

- Clones (once) or pulls (each deploy) a repo into `wp-content/<target>/`
- Encrypts the GitHub PAT at rest using libsodium + WP's AUTH_KEY
- PHP syntax-checks every changed `.php` file before swapping it in
- Keeps the previous commit alive for 1-click rollback
- Rate-limits to 20 deploys/hour per admin user
- Full audit log at `wp-content/uploads/neogen-deploy.log`
- Falls back to GitHub ZIP download when `git` binary is missing

## Install

1. Upload `neogen-deploy-1.0.0.zip` via WP admin → Plugins → Add New → Upload Plugin.
2. Activate.
3. Go to **Tools → NeoGen Deploy**.
4. Paste your PAT + repo URL + target path. Save.
5. Click **Clone (first time)**.
6. Thereafter: commit to the repo → click **Pull Latest**.

## Security caveats

- The PAT gives GitHub write access to the repo. Keep it secret. Rotate every 90 days.
- Any WP admin can trigger a deploy. Limit admin accounts + enable 2FA.
- This plugin deliberately runs `exec('git ...')` — requires `exec()` enabled in PHP.
- If `git` binary is not available on your host, the ZIP fallback works but diff + rollback are degraded.

## Rollback

Click **Rollback (-1 commit)** in the plugin UI. Reverts to the commit before the last pull.

Alternatively: in the repo, `git revert HEAD && git push`, then click Pull Latest.

## Uninstall

Deactivate + delete via WP admin. The deployed code stays (in `mu-plugins/neogen-custom/` or wherever your target is). Only plugin settings + encrypted PAT are removed.

## Files

- `neogen-deploy.php` — main plugin
- `uninstall.php` — settings cleanup
- `README.md` — this file
