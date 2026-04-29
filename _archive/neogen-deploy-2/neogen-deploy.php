<?php
/**
 * Plugin Name:       NeoGen Deploy
 * Plugin URI:        https://github.com/fahadalmansour/neogen-custom
 * Description:       Pull deployable custom code from a private GitHub repo into the WP filesystem. Admin-triggered, rate-limited, rollback-able.
 * Version:           1.0.0
 * Author:            Fahad Almansour
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * License:           GPLv2 or later
 *
 * SECURITY NOTE: This plugin writes files to wp-content/. Treat the PAT as a
 * site secret. If your WP admin account is compromised, this plugin becomes a
 * deployment backdoor. Recommended: enable 2FA on WP admin, rotate PAT every 90 days.
 */

defined('ABSPATH') || exit;

define('NGD_VERSION', '1.0.0');
define('NGD_FILE',    __FILE__);
define('NGD_DIR',     plugin_dir_path(__FILE__));
define('NGD_SLUG',    'neogen-deploy');
define('NGD_OPT',     'ngd_settings');
define('NGD_LOG',     WP_CONTENT_DIR . '/uploads/neogen-deploy.log');

// ============================================================
// ADMIN MENU + SETTINGS PAGE
// ============================================================

add_action('admin_menu', function () {
    add_management_page(
        'NeoGen Deploy',
        'NeoGen Deploy',
        'manage_options',
        NGD_SLUG,
        'ngd_render_page'
    );
});

add_action('admin_init', function () {
    register_setting(NGD_OPT, NGD_OPT, ['sanitize_callback' => 'ngd_sanitize']);
});

function ngd_defaults() {
    return [
        'repo_url'    => 'https://github.com/fahadalmansour/neogen-custom.git',
        'branch'      => 'main',
        'target_rel'  => 'mu-plugins/neogen-custom',
        'pat_enc'     => '', // encrypted PAT (base64 of nonce+ciphertext)
    ];
}

function ngd_get_settings() {
    $s = get_option(NGD_OPT, []);
    return array_merge(ngd_defaults(), is_array($s) ? $s : []);
}

function ngd_sanitize($input) {
    $out = ngd_get_settings();
    foreach (['repo_url', 'branch', 'target_rel'] as $k) {
        if (isset($input[$k])) {
            $v = trim((string) $input[$k]);
            if ($k === 'target_rel') {
                $v = ltrim($v, '/');
                if (strpos($v, '..') !== false || !preg_match('#^(mu-plugins|plugins|themes|uploads)/#', $v)) {
                    add_settings_error(NGD_OPT, 'bad_target', 'Target path must start with mu-plugins/, plugins/, themes/, or uploads/');
                    continue;
                }
            }
            if ($k === 'repo_url' && !preg_match('#^https://github\.com/[^/]+/[^/]+(\.git)?$#', $v)) {
                add_settings_error(NGD_OPT, 'bad_url', 'Repo URL must be a GitHub HTTPS URL');
                continue;
            }
            $out[$k] = $v;
        }
    }
    if (!empty($input['pat']) && is_string($input['pat'])) {
        $pat = trim($input['pat']);
        if (strlen($pat) > 10) {
            $out['pat_enc'] = ngd_encrypt($pat);
        }
    }
    return $out;
}

// ============================================================
// PAT ENCRYPTION (sodium libsodium, built into PHP 7.2+)
// ============================================================

function ngd_key() {
    $base = defined('AUTH_KEY') ? AUTH_KEY : '';
    $base .= defined('SECURE_AUTH_KEY') ? SECURE_AUTH_KEY : '';
    $base .= defined('NONCE_KEY') ? NONCE_KEY : '';
    return substr(hash('sha256', 'ngd' . $base, true), 0, SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
}

function ngd_encrypt($plaintext) {
    $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    $ct    = sodium_crypto_secretbox($plaintext, $nonce, ngd_key());
    return base64_encode($nonce . $ct);
}

function ngd_decrypt($b64) {
    if (!$b64) return '';
    $raw   = base64_decode($b64, true);
    if ($raw === false || strlen($raw) < SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + 1) return '';
    $nonce = substr($raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    $ct    = substr($raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    $pt    = sodium_crypto_secretbox_open($ct, $nonce, ngd_key());
    return $pt === false ? '' : $pt;
}

// ============================================================
// RATE LIMIT
// ============================================================

function ngd_rate_limit_check() {
    $uid = get_current_user_id();
    $key = 'ngd_rl_' . $uid;
    $count = (int) get_transient($key);
    if ($count >= 20) {
        return new WP_Error('rate_limited', 'Rate limit: 20 deploys/hour exceeded. Try again later.');
    }
    set_transient($key, $count + 1, HOUR_IN_SECONDS);
    return true;
}

// ============================================================
// LOGGING
// ============================================================

function ngd_log($event, $meta = []) {
    $line = json_encode([
        'ts'      => gmdate('c'),
        'user_id' => get_current_user_id(),
        'user'    => wp_get_current_user()->user_login ?? '-',
        'ip'      => $_SERVER['REMOTE_ADDR'] ?? '-',
        'event'   => $event,
        'meta'    => $meta,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    @file_put_contents(NGD_LOG, $line . "\n", FILE_APPEND | LOCK_EX);
}

// ============================================================
// GIT DETECTION + EXECUTION
// ============================================================

function ngd_has_git() {
    if (!function_exists('exec')) return false;
    @exec('git --version 2>&1', $out, $code);
    return $code === 0;
}

function ngd_git_run($cwd, $cmd) {
    $full = 'cd ' . escapeshellarg($cwd) . ' && ' . $cmd . ' 2>&1';
    @exec($full, $out, $code);
    return ['code' => $code, 'output' => implode("\n", $out)];
}

function ngd_auth_url($repo_url, $pat) {
    if (!$pat) return $repo_url;
    $clean = preg_replace('#^https://#', '', $repo_url);
    return 'https://x-access-token:' . $pat . '@' . $clean;
}

// ============================================================
// TARGET PATH RESOLUTION + SAFETY
// ============================================================

function ngd_target_abs() {
    $s = ngd_get_settings();
    $rel = $s['target_rel'];
    $abs = WP_CONTENT_DIR . '/' . $rel;
    // Canonicalize parent + rebuild
    $parent = dirname($abs);
    if (is_dir($parent)) {
        $rp = realpath($parent);
        if ($rp && strpos($rp, realpath(WP_CONTENT_DIR)) !== 0) {
            return new WP_Error('escape', 'Target path escapes wp-content/');
        }
    }
    return $abs;
}

// ============================================================
// AJAX ACTIONS (triggered by admin UI buttons)
// ============================================================

add_action('wp_ajax_ngd_action', function () {
    if (!current_user_can('manage_options')) wp_send_json_error(['msg' => 'Forbidden'], 403);
    if (!check_ajax_referer('ngd_nonce', '_nonce', false)) wp_send_json_error(['msg' => 'Bad nonce'], 403);
    $act = sanitize_text_field($_POST['act'] ?? '');
    $rate = ngd_rate_limit_check();
    if (is_wp_error($rate)) wp_send_json_error(['msg' => $rate->get_error_message()], 429);
    switch ($act) {
        case 'clone':     wp_send_json(ngd_do_clone());           break;
        case 'pull':      wp_send_json(ngd_do_pull());            break;
        case 'diff':      wp_send_json(ngd_do_diff());            break;
        case 'rollback':  wp_send_json(ngd_do_rollback());        break;
        case 'probe':     wp_send_json(ngd_do_probe());           break;
        default:          wp_send_json_error(['msg' => 'Unknown action: ' . $act], 400);
    }
});

function ngd_do_probe() {
    $s = ngd_get_settings();
    $target = ngd_target_abs();
    $has_git = ngd_has_git();
    $exists = !is_wp_error($target) && is_dir($target);
    $git_dir = $exists && is_dir($target . '/.git');
    $current = null;
    if ($git_dir && $has_git) {
        $r = ngd_git_run($target, 'git log -1 --format="%h %ci %s"');
        $current = $r['code'] === 0 ? trim($r['output']) : null;
    }
    return [
        'success' => true,
        'data'    => [
            'has_git'        => $has_git,
            'target'         => is_wp_error($target) ? '!err!' : $target,
            'target_exists'  => $exists,
            'is_repo'        => $git_dir,
            'current_commit' => $current,
            'rate_left'      => max(0, 20 - (int) get_transient('ngd_rl_' . get_current_user_id())),
        ],
    ];
}

function ngd_do_clone() {
    $s = ngd_get_settings();
    $target = ngd_target_abs();
    if (is_wp_error($target)) return ['success' => false, 'data' => ['msg' => $target->get_error_message()]];
    if (is_dir($target)) return ['success' => false, 'data' => ['msg' => 'Target already exists. Use Pull instead.']];
    $pat = ngd_decrypt($s['pat_enc']);
    if (!$pat) return ['success' => false, 'data' => ['msg' => 'PAT not set. Save settings first.']];
    if (!ngd_has_git()) return ngd_do_clone_zip();
    $url = ngd_auth_url($s['repo_url'], $pat);
    $parent = dirname($target);
    if (!wp_mkdir_p($parent)) return ['success' => false, 'data' => ['msg' => 'Cannot create parent: ' . $parent]];
    $r = ngd_git_run($parent, 'git clone --depth 50 ' . escapeshellarg($url) . ' ' . escapeshellarg(basename($target)));
    ngd_log('clone', ['target' => $target, 'code' => $r['code']]);
    if ($r['code'] !== 0) {
        @exec('rm -rf ' . escapeshellarg($target));
        return ['success' => false, 'data' => ['msg' => 'Clone failed', 'output' => $r['output']]];
    }
    return ['success' => true, 'data' => ['msg' => 'Cloned OK', 'output' => $r['output']]];
}

function ngd_do_clone_zip() {
    // Fallback: download ZIP from GitHub API and extract
    $s = ngd_get_settings();
    $target = ngd_target_abs();
    if (is_wp_error($target)) return ['success' => false, 'data' => ['msg' => $target->get_error_message()]];
    $pat = ngd_decrypt($s['pat_enc']);
    // Derive owner/repo from repo_url
    if (!preg_match('#github\.com/([^/]+)/([^/.]+)#', $s['repo_url'], $m)) {
        return ['success' => false, 'data' => ['msg' => 'Cannot parse repo_url']];
    }
    $owner = $m[1]; $repo = $m[2];
    $api_url = "https://api.github.com/repos/$owner/$repo/zipball/" . rawurlencode($s['branch']);
    $tmp = wp_tempnam('ngd-zip-');
    $resp = wp_remote_get($api_url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $pat,
            'Accept'        => 'application/vnd.github+json',
            'User-Agent'    => 'NeoGen-Deploy/' . NGD_VERSION,
        ],
        'timeout' => 60,
        'stream'  => true,
        'filename' => $tmp,
    ]);
    if (is_wp_error($resp)) return ['success' => false, 'data' => ['msg' => $resp->get_error_message()]];
    if (wp_remote_retrieve_response_code($resp) !== 200) {
        return ['success' => false, 'data' => ['msg' => 'Download failed: HTTP ' . wp_remote_retrieve_response_code($resp)]];
    }
    WP_Filesystem();
    global $wp_filesystem;
    $extract_to = dirname($target) . '/' . basename($target) . '.new';
    if (is_dir($extract_to)) $wp_filesystem->delete($extract_to, true);
    wp_mkdir_p($extract_to);
    $r = unzip_file($tmp, $extract_to);
    @unlink($tmp);
    if (is_wp_error($r)) return ['success' => false, 'data' => ['msg' => $r->get_error_message()]];
    // GitHub zips have one wrapping dir (owner-repo-sha/). Move its contents up.
    $dirs = glob($extract_to . '/*', GLOB_ONLYDIR);
    $src = !empty($dirs) ? $dirs[0] : $extract_to;
    if (!rename($src, $target)) {
        return ['success' => false, 'data' => ['msg' => 'Rename failed']];
    }
    @rmdir($extract_to);
    ngd_log('clone_zip', ['target' => $target]);
    return ['success' => true, 'data' => ['msg' => 'ZIP extracted OK (git binary not available, used GitHub zipball)']];
}

function ngd_do_pull() {
    $s = ngd_get_settings();
    $target = ngd_target_abs();
    if (is_wp_error($target)) return ['success' => false, 'data' => ['msg' => $target->get_error_message()]];
    if (!is_dir($target . '/.git')) {
        // Not a git working tree — fallback to zip overlay
        return ngd_do_pull_zip();
    }
    $pat = ngd_decrypt($s['pat_enc']);
    $url = ngd_auth_url($s['repo_url'], $pat);
    // Set auth URL on-the-fly (don't persist PAT to .git/config)
    ngd_git_run($target, 'git remote set-url origin ' . escapeshellarg($url));
    $fetch = ngd_git_run($target, 'git fetch --depth 50 origin ' . escapeshellarg($s['branch']));
    if ($fetch['code'] !== 0) {
        ngd_git_run($target, 'git remote set-url origin ' . escapeshellarg($s['repo_url']));
        return ['success' => false, 'data' => ['msg' => 'Fetch failed', 'output' => $fetch['output']]];
    }
    // Syntax check .php files about to change
    $diff = ngd_git_run($target, 'git diff --name-only HEAD origin/' . escapeshellarg($s['branch']));
    $changed = array_filter(explode("\n", $diff['output']));
    $php_fails = [];
    foreach ($changed as $rel) {
        if (substr($rel, -4) !== '.php') continue;
        // File content at the new commit
        $show = ngd_git_run($target, 'git show origin/' . escapeshellarg($s['branch']) . ':' . escapeshellarg($rel));
        if ($show['code'] !== 0) continue;
        $tmp = wp_tempnam('ngd-lint-');
        file_put_contents($tmp, $show['output']);
        @exec('php -l ' . escapeshellarg($tmp) . ' 2>&1', $lo, $lc);
        @unlink($tmp);
        if ($lc !== 0) $php_fails[] = $rel . ': ' . implode(' | ', $lo);
    }
    if ($php_fails) {
        ngd_git_run($target, 'git remote set-url origin ' . escapeshellarg($s['repo_url']));
        return ['success' => false, 'data' => ['msg' => 'Syntax check failed — deploy aborted', 'fails' => $php_fails]];
    }
    // Record current HEAD for rollback
    $old = ngd_git_run($target, 'git rev-parse HEAD');
    $old_sha = trim($old['output']);
    // Hard reset to origin/main
    $reset = ngd_git_run($target, 'git reset --hard origin/' . escapeshellarg($s['branch']));
    // Clean auth URL
    ngd_git_run($target, 'git remote set-url origin ' . escapeshellarg($s['repo_url']));
    if ($reset['code'] !== 0) {
        return ['success' => false, 'data' => ['msg' => 'Reset failed', 'output' => $reset['output']]];
    }
    $new = ngd_git_run($target, 'git rev-parse HEAD');
    $new_sha = trim($new['output']);
    ngd_log('pull', ['from' => $old_sha, 'to' => $new_sha, 'files' => count($changed)]);
    return ['success' => true, 'data' => [
        'msg' => 'Pulled successfully',
        'from' => $old_sha, 'to' => $new_sha,
        'files_changed' => count($changed),
        'changed' => $changed,
        'php_checked' => count(array_filter($changed, fn($f) => substr($f, -4) === '.php')),
    ]];
}

function ngd_do_pull_zip() {
    // When git isn't a working tree — re-download zip and overlay
    return ngd_do_clone_zip_overlay();
}

function ngd_do_clone_zip_overlay() {
    $s = ngd_get_settings();
    $target = ngd_target_abs();
    $pat = ngd_decrypt($s['pat_enc']);
    if (!preg_match('#github\.com/([^/]+)/([^/.]+)#', $s['repo_url'], $m)) {
        return ['success' => false, 'data' => ['msg' => 'Cannot parse repo_url']];
    }
    $owner = $m[1]; $repo = $m[2];
    $api_url = "https://api.github.com/repos/$owner/$repo/zipball/" . rawurlencode($s['branch']);
    $tmp = wp_tempnam('ngd-zip-');
    $resp = wp_remote_get($api_url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $pat,
            'Accept'        => 'application/vnd.github+json',
            'User-Agent'    => 'NeoGen-Deploy/' . NGD_VERSION,
        ],
        'timeout' => 60,
        'stream'  => true,
        'filename' => $tmp,
    ]);
    if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) !== 200) {
        return ['success' => false, 'data' => ['msg' => 'Download failed']];
    }
    WP_Filesystem();
    global $wp_filesystem;
    $extract_to = dirname($target) . '/' . basename($target) . '.new';
    if (is_dir($extract_to)) $wp_filesystem->delete($extract_to, true);
    wp_mkdir_p($extract_to);
    $r = unzip_file($tmp, $extract_to);
    @unlink($tmp);
    if (is_wp_error($r)) return ['success' => false, 'data' => ['msg' => $r->get_error_message()]];
    // Find the inner wrapper dir
    $dirs = glob($extract_to . '/*', GLOB_ONLYDIR);
    $src = !empty($dirs) ? $dirs[0] : $extract_to;
    // Backup old target
    $backup = $target . '.bak-' . gmdate('YmdHis');
    if (is_dir($target)) @rename($target, $backup);
    if (!rename($src, $target)) {
        if ($backup && is_dir($backup)) @rename($backup, $target);
        return ['success' => false, 'data' => ['msg' => 'Rename failed; original restored']];
    }
    @rmdir($extract_to);
    ngd_log('pull_zip', ['target' => $target, 'backup' => $backup]);
    return ['success' => true, 'data' => ['msg' => 'ZIP overlay OK', 'backup' => basename($backup)]];
}

function ngd_do_diff() {
    $s = ngd_get_settings();
    $target = ngd_target_abs();
    if (is_wp_error($target) || !is_dir($target . '/.git')) {
        return ['success' => true, 'data' => ['msg' => 'Diff unavailable (not a git working tree)', 'output' => '']];
    }
    $pat = ngd_decrypt($s['pat_enc']);
    $url = ngd_auth_url($s['repo_url'], $pat);
    ngd_git_run($target, 'git remote set-url origin ' . escapeshellarg($url));
    ngd_git_run($target, 'git fetch --depth 50 origin ' . escapeshellarg($s['branch']));
    $diff = ngd_git_run($target, 'git diff --stat HEAD origin/' . escapeshellarg($s['branch']));
    ngd_git_run($target, 'git remote set-url origin ' . escapeshellarg($s['repo_url']));
    return ['success' => true, 'data' => ['output' => $diff['output']]];
}

function ngd_do_rollback() {
    $s = ngd_get_settings();
    $target = ngd_target_abs();
    if (is_wp_error($target) || !is_dir($target . '/.git')) {
        return ['success' => false, 'data' => ['msg' => 'Rollback unavailable (not a git working tree)']];
    }
    $r = ngd_git_run($target, 'git reset --hard HEAD~1');
    if ($r['code'] !== 0) {
        return ['success' => false, 'data' => ['msg' => 'Rollback failed', 'output' => $r['output']]];
    }
    $now = ngd_git_run($target, 'git rev-parse HEAD');
    ngd_log('rollback', ['now' => trim($now['output'])]);
    return ['success' => true, 'data' => ['msg' => 'Rolled back one commit', 'now' => trim($now['output'])]];
}

// ============================================================
// SETTINGS PAGE RENDER
// ============================================================

function ngd_render_page() {
    if (!current_user_can('manage_options')) return;
    $s = ngd_get_settings();
    $nonce = wp_create_nonce('ngd_nonce');
    ?>
    <div class="wrap">
        <h1>NeoGen Deploy <span style="font-size:13px; color:#666;">v<?php echo esc_html(NGD_VERSION); ?></span></h1>

        <div style="background:#fff3cd; border-left:4px solid #ffc107; padding:12px 16px; margin:16px 0;">
            <strong>⚠ Security:</strong> This plugin writes files to wp-content/. Keep your GitHub PAT secret,
            rotate it every 90 days, and enable 2FA on your WP admin account.
        </div>

        <h2>Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields(NGD_OPT); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th><label for="ngd_repo">Repo URL</label></th>
                    <td><input id="ngd_repo" name="<?php echo NGD_OPT; ?>[repo_url]" type="url" value="<?php echo esc_attr($s['repo_url']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="ngd_branch">Branch</label></th>
                    <td><input id="ngd_branch" name="<?php echo NGD_OPT; ?>[branch]" type="text" value="<?php echo esc_attr($s['branch']); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="ngd_target">Target (relative to wp-content/)</label></th>
                    <td>
                        <input id="ngd_target" name="<?php echo NGD_OPT; ?>[target_rel]" type="text" value="<?php echo esc_attr($s['target_rel']); ?>" class="regular-text">
                        <p class="description">Must start with <code>mu-plugins/</code>, <code>plugins/</code>, <code>themes/</code>, or <code>uploads/</code>.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="ngd_pat">GitHub PAT</label></th>
                    <td>
                        <input id="ngd_pat" name="<?php echo NGD_OPT; ?>[pat]" type="password" value="" class="regular-text" autocomplete="off" placeholder="<?php echo $s['pat_enc'] ? '••••••••••• (already set — paste to overwrite)' : 'github_pat_...'; ?>">
                        <p class="description">Fine-grained PAT with <code>Contents: Read and write</code> scope on the repo.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Save settings'); ?>
        </form>

        <hr>

        <h2>Actions</h2>
        <div id="ngd-probe-box" style="background:#fafafa; border:1px solid #e0e0e0; padding:12px 16px; margin:12px 0; font-family:monospace; font-size:12px;">
            Loading…
        </div>

        <p>
            <button class="button" data-ngd-act="clone">Clone (first time)</button>
            <button class="button button-primary" data-ngd-act="pull">Pull Latest</button>
            <button class="button" data-ngd-act="diff">Show Diff</button>
            <button class="button" data-ngd-act="rollback">Rollback (-1 commit)</button>
        </p>

        <pre id="ngd-out" style="background:#0f0f0f; color:#aaffaa; padding:16px; border-radius:4px; max-height:500px; overflow:auto; font-size:12px; white-space:pre-wrap;"></pre>

        <h3>Deploy Log (tail)</h3>
        <pre id="ngd-log" style="background:#f5f5f5; padding:12px; font-size:11px; max-height:300px; overflow:auto;"><?php
            if (file_exists(NGD_LOG)) {
                $lines = file(NGD_LOG, FILE_IGNORE_NEW_LINES);
                echo esc_html(implode("\n", array_slice($lines, -20)));
            } else {
                echo '(no deploys yet)';
            }
        ?></pre>
    </div>

    <script>
    (function () {
        const NONCE = <?php echo wp_json_encode($nonce); ?>;
        const AJAX = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
        const out = document.getElementById('ngd-out');
        const probeBox = document.getElementById('ngd-probe-box');

        function call(act) {
            out.textContent = '⏳ Running: ' + act + '...';
            const fd = new FormData();
            fd.append('action', 'ngd_action');
            fd.append('act', act);
            fd.append('_nonce', NONCE);
            fetch(AJAX, { method: 'POST', credentials: 'same-origin', body: fd })
                .then(r => r.json())
                .then(j => {
                    out.textContent = JSON.stringify(j, null, 2);
                    if (act !== 'diff') setTimeout(probe, 500);
                })
                .catch(e => { out.textContent = 'Error: ' + e.message; });
        }

        function probe() {
            const fd = new FormData();
            fd.append('action', 'ngd_action');
            fd.append('act', 'probe');
            fd.append('_nonce', NONCE);
            fetch(AJAX, { method: 'POST', credentials: 'same-origin', body: fd })
                .then(r => r.json())
                .then(j => {
                    const d = j.data || {};
                    probeBox.innerHTML =
                        '<strong>git binary:</strong> ' + (d.has_git ? '✓ available' : '✗ missing (will use ZIP fallback)') + '<br>' +
                        '<strong>target:</strong> ' + (d.target || '?') + '<br>' +
                        '<strong>exists:</strong> ' + (d.target_exists ? '✓' : '✗') +
                        ' &nbsp;<strong>is git repo:</strong> ' + (d.is_repo ? '✓' : '✗') + '<br>' +
                        '<strong>current commit:</strong> ' + (d.current_commit || '(none)') + '<br>' +
                        '<strong>rate limit left:</strong> ' + d.rate_left + '/20 per hour';
                });
        }

        document.querySelectorAll('[data-ngd-act]').forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                const act = btn.getAttribute('data-ngd-act');
                if (act === 'rollback' && !confirm('Rollback to previous commit? This reverts the last deploy.')) return;
                call(act);
            });
        });
        probe();
    })();
    </script>
    <?php
}
