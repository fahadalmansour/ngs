<?php
/**
 * Plugin Name:       NeoGen Deploy
 * Plugin URI:        https://github.com/fahadalmansour/neogen-custom
 * Description:       Pull deployable custom code from a private GitHub repo into the WP filesystem. Admin-triggered, rate-limited, rollback-able.
 * Version:           1.0.1
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

define('NGD_VERSION', '1.0.1');
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
    $loader_msg = ngd_install_loader($target);
    return ['success' => true, 'data' => ['msg' => 'Cloned OK · ' . $loader_msg, 'output' => $r['output']]];
}

/**
 * Writes an auto-loader file at wp-content/mu-plugins/<basename>-loader.php
 * that requires all *.php from <target>/mu-plugins/ (WP's mu-plugin scanner
 * only auto-loads direct .php files in mu-plugins/, not subdirectories).
 *
 * Also registers the /themes/blocksy-child/functions.php if present by
 * requiring it from the loader (bypasses theme-install flow).
 *
 * Idempotent — safe to call on every clone/pull.
 */
function ngd_install_loader($target_abs) {
    if (!is_string($target_abs) || !is_dir($target_abs)) return 'loader skipped (target missing)';
    $mu_dir      = WP_CONTENT_DIR . '/mu-plugins';
    $rel_target  = 'mu-plugins/' . basename($target_abs);
    $loader_slug = preg_replace('/[^a-z0-9-]/', '-', strtolower(basename($target_abs))) . '-loader';
    $loader_path = $mu_dir . '/' . $loader_slug . '.php';
    if (!is_dir($mu_dir)) {
        if (!wp_mkdir_p($mu_dir)) return 'loader FAILED (cannot create mu-plugins dir)';
    }
    // Only create loader if the repo has a mu-plugins/ subfolder
    $src_mu = $target_abs . '/mu-plugins';
    if (!is_dir($src_mu)) return 'no mu-plugins/ in repo — loader not installed';

    $target_basename = basename($target_abs);
    $loader_content = <<<PHP
<?php
/**
 * Plugin Name: NeoGen Deploy Loader — {$target_basename}
 * Description: Auto-requires all *.php from wp-content/mu-plugins/{$target_basename}/mu-plugins/. Generated by neogen-deploy plugin — do not edit by hand.
 * Version: 1.0.1
 * Author: neogen-deploy
 */
defined('ABSPATH') || exit;

\$ngd_src = __DIR__ . '/{$target_basename}/mu-plugins';
if (is_dir(\$ngd_src)) {
    foreach (glob(\$ngd_src . '/*.php') as \$ngd_file) {
        require_once \$ngd_file;
    }
}
unset(\$ngd_src, \$ngd_file);
PHP;
    $bytes = @file_put_contents($loader_path, $loader_content);
    if ($bytes === false) return 'loader write FAILED (permissions?)';
    @chmod($loader_path, 0644);
    ngd_log('loader_install', ['loader' => $loader_path, 'bytes' => $bytes]);
    return 'loader installed at ' . basename($loader_path);
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
    $loader_msg = ngd_install_loader($target);
    ngd_log('pull', ['from' => $old_sha, 'to' => $new_sha, 'files' => count($changed), 'loader' => $loader_msg]);
    return ['success' => true, 'data' => [
        'msg' => 'Pulled successfully · ' . $loader_msg,
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
    $log_tail = '';
    if (file_exists(NGD_LOG)) {
        $lines = file(NGD_LOG, FILE_IGNORE_NEW_LINES);
        $log_tail = esc_html(implode("\n", array_slice($lines, -20)));
    }
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@300;400;500;600;700&family=JetBrains+Mono:wght@300;400;500;700&family=Major+Mono+Display&display=swap">

    <style id="ngd-style">
      /* ===== SCOPE: #ngd-root only — don't bleed into rest of WP admin ===== */
      #ngd-root {
        --ngd-bg:       #050505;
        --ngd-surface:  #0F0E0C;
        --ngd-raised:   #16140F;
        --ngd-hair:     rgba(0,209,255,0.12);
        --ngd-hair-hot: rgba(0,209,255,0.35);
        --ngd-accent:   #00D1FF;
        --ngd-accent-lo:#66E4FF;
        --ngd-accent-dp:#0099CC;
        --ngd-glow:     rgba(0,209,255,0.45);
        --ngd-text:     #E5E3DD;
        --ngd-warm:     #CFC9BB;
        --ngd-muted:    #8F8A7E;
        --ngd-dim:      #4F4A40;
        --ngd-signal:   #3FE88F;
        --ngd-alert:    #E8734A;
        --f-disp:       'Chakra Petch', sans-serif;
        --f-mono:       'JetBrains Mono', ui-monospace, monospace;
        --f-ultra:      'Major Mono Display', monospace;

        position: relative;
        background: var(--ngd-bg);
        color: var(--ngd-warm);
        font-family: var(--f-disp);
        padding: 24px 32px 48px;
        margin: -10px -20px 0 -20px; /* Kill default WP admin padding */
        min-height: calc(100vh - 32px);
        overflow: hidden;
      }
      #ngd-root * { box-sizing: border-box; }
      #ngd-root::before {
        content: '';
        position: absolute; inset: 0; pointer-events: none; z-index: 0;
        background-image:
          linear-gradient(rgba(0,209,255,0.035) 1px, transparent 1px),
          linear-gradient(90deg, rgba(0,209,255,0.035) 1px, transparent 1px);
        background-size: 48px 48px;
        mask-image: radial-gradient(ellipse at 20% 0%, black 30%, transparent 80%);
      }
      #ngd-root > * { position: relative; z-index: 1; }

      /* ===== SYSTEM BAR ===== */
      .ngd-sysbar {
        display: flex; justify-content: space-between; align-items: center;
        flex-wrap: wrap; gap: 16px;
        padding: 12px 16px; margin-bottom: 24px;
        border: 1px solid var(--ngd-hair);
        background: rgba(5,5,5,0.6); backdrop-filter: blur(8px);
        font-family: var(--f-mono); font-size: 11px;
        letter-spacing: 0.18em; text-transform: uppercase; color: var(--ngd-muted);
      }
      .ngd-sysbar .pill { display: inline-flex; align-items: center; gap: 10px; }
      .ngd-dot { width: 6px; height: 6px; border-radius: 50%; background: var(--ngd-signal); box-shadow: 0 0 6px var(--ngd-signal); animation: ngd-pulse 2s infinite; }
      @keyframes ngd-pulse { 0%,70%{opacity:1} 80%,100%{opacity:.35} }
      .ngd-sysbar b { color: var(--ngd-accent); font-weight: 500; }

      /* ===== HERO / TITLE ===== */
      .ngd-hero { display: grid; grid-template-columns: 1fr auto; gap: 24px; align-items: end; margin-bottom: 32px; }
      @media (max-width:900px) { .ngd-hero { grid-template-columns: 1fr; } }
      .ngd-kicker {
        font-family: var(--f-mono);
        font-size: 11px; letter-spacing: 0.3em; text-transform: uppercase;
        color: var(--ngd-accent); display: inline-flex; align-items: center; gap: 10px;
        margin-bottom: 8px;
      }
      .ngd-kicker::before { content: ''; width: 28px; height: 1px; background: var(--ngd-accent); }
      .ngd-title {
        font-family: var(--f-disp); font-weight: 700;
        font-size: clamp(34px,4vw,56px); letter-spacing: -0.01em;
        line-height: 1; color: var(--ngd-text); text-transform: uppercase; margin: 0;
      }
      .ngd-title em { font-style: normal; color: var(--ngd-accent); text-shadow: 0 0 28px var(--ngd-glow); }
      .ngd-title-sub {
        font-family: var(--f-mono); font-size: 12px; letter-spacing: 0.2em;
        color: var(--ngd-muted); text-transform: uppercase; margin-top: 8px;
      }
      .ngd-version-tag {
        font-family: var(--f-ultra);
        font-size: 48px; letter-spacing: 0.08em;
        color: var(--ngd-warm); line-height: 1;
      }
      .ngd-version-tag .v { color: var(--ngd-accent); }

      /* ===== PANEL (HUD corners) ===== */
      .ngd-panel {
        background: var(--ngd-surface);
        border: 1px solid var(--ngd-hair);
        padding: 24px; margin: 16px 0; position: relative;
      }
      .ngd-panel::before, .ngd-panel::after {
        content: ''; position: absolute; width: 22px; height: 22px;
        border: 1px solid var(--ngd-accent); opacity: 0.6;
      }
      .ngd-panel::before { top: -1px; left: -1px; border-right: none; border-bottom: none; }
      .ngd-panel::after  { bottom: -1px; right: -1px; border-left: none; border-top: none; }
      .ngd-panel-head {
        display: flex; justify-content: space-between; align-items: baseline;
        margin-bottom: 16px; padding-bottom: 12px;
        border-bottom: 1px dashed var(--ngd-hair);
      }
      .ngd-panel-head h3 {
        font-family: var(--f-disp); font-weight: 700;
        font-size: 18px; letter-spacing: 0.04em;
        text-transform: uppercase; color: var(--ngd-text); margin: 0;
      }
      .ngd-panel-head .tag {
        font-family: var(--f-mono); font-size: 10px;
        letter-spacing: 0.25em; color: var(--ngd-muted); text-transform: uppercase;
      }

      /* ===== WARNING BAND ===== */
      .ngd-warn {
        background: linear-gradient(90deg, rgba(232,115,74,0.08), transparent);
        border-left: 3px solid var(--ngd-alert);
        padding: 12px 16px; margin-bottom: 24px;
        font-family: var(--f-mono); font-size: 12px; letter-spacing: 0.04em;
        color: var(--ngd-warm);
      }
      .ngd-warn b { color: var(--ngd-alert); font-weight: 500; letter-spacing: 0.15em; text-transform: uppercase; }

      /* ===== FORM ===== */
      .ngd-form { display: grid; grid-template-columns: 160px 1fr; gap: 14px 20px; align-items: center; }
      @media (max-width: 640px) { .ngd-form { grid-template-columns: 1fr; gap: 4px 0; } .ngd-form > label { padding-top: 10px; } }
      .ngd-form > label {
        font-family: var(--f-mono); font-size: 11px;
        letter-spacing: 0.22em; text-transform: uppercase; color: var(--ngd-muted);
      }
      .ngd-form > label::before {
        content: '//'; margin-right: 8px; color: var(--ngd-accent); opacity: 0.5;
      }
      .ngd-input, .ngd-input:focus {
        background: var(--ngd-bg) !important;
        color: var(--ngd-text) !important;
        border: 1px solid var(--ngd-hair-hot) !important;
        padding: 10px 14px !important;
        font-family: var(--f-mono) !important;
        font-size: 13px !important;
        letter-spacing: 0.02em !important;
        outline: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        width: 100%;
      }
      .ngd-input:focus {
        border-color: var(--ngd-accent) !important;
        box-shadow: 0 0 0 1px var(--ngd-accent), 0 0 12px rgba(0,209,255,0.2) !important;
      }
      .ngd-input::placeholder { color: var(--ngd-dim); }
      .ngd-desc {
        grid-column: 2;
        font-family: var(--f-mono); font-size: 10px; letter-spacing: 0.08em;
        color: var(--ngd-muted); margin-top: -4px;
      }
      .ngd-desc code {
        background: var(--ngd-raised); padding: 1px 6px;
        color: var(--ngd-accent-lo); font-size: 10px; border: 1px solid var(--ngd-hair);
      }

      /* ===== BUTTONS ===== */
      .ngd-btn {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 18px; margin: 0 6px 8px 0;
        background: transparent; color: var(--ngd-text);
        border: 1px solid var(--ngd-hair-hot);
        font-family: var(--f-mono); font-weight: 500;
        font-size: 11px; letter-spacing: 0.14em; text-transform: uppercase;
        cursor: pointer; transition: all .2s cubic-bezier(0.2,0,0.2,1);
        position: relative; overflow: hidden;
      }
      .ngd-btn::before {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(135deg, var(--ngd-accent), var(--ngd-accent-dp));
        opacity: 0; transition: opacity .2s;
      }
      .ngd-btn:hover {
        border-color: var(--ngd-accent); color: var(--ngd-accent);
        transform: translateY(-1px);
        box-shadow: 0 0 0 1px var(--ngd-accent), 0 6px 20px var(--ngd-glow);
      }
      .ngd-btn > span { position: relative; z-index: 2; }
      .ngd-btn-primary {
        background: linear-gradient(135deg, var(--ngd-accent) 0%, var(--ngd-accent-dp) 100%);
        color: #FFFFFF !important;
        border-color: var(--ngd-accent);
        font-weight: 700;
      }
      .ngd-btn-primary:hover {
        background: linear-gradient(135deg, var(--ngd-accent-lo), var(--ngd-accent));
        color: #FFFFFF !important;
      }
      .ngd-btn-danger { color: var(--ngd-alert); border-color: rgba(232,115,74,0.4); }
      .ngd-btn-danger:hover { color: var(--ngd-alert); border-color: var(--ngd-alert); box-shadow: 0 0 0 1px var(--ngd-alert), 0 6px 20px rgba(232,115,74,0.3); }
      .ngd-btn .arrow { opacity: 0.6; transition: all .2s; }
      .ngd-btn:hover .arrow { opacity: 1; transform: translateX(3px); }

      /* Save settings button (form submit) — WP default hijack */
      #ngd-root .button-primary,
      #ngd-root #submit {
        background: linear-gradient(135deg, var(--ngd-accent), var(--ngd-accent-dp)) !important;
        color: #FFFFFF !important;
        border: 1px solid var(--ngd-accent) !important;
        border-radius: 0 !important;
        font-family: var(--f-mono) !important;
        font-weight: 700 !important;
        font-size: 11px !important;
        letter-spacing: 0.2em !important;
        text-transform: uppercase !important;
        padding: 10px 22px !important;
        text-shadow: none !important;
        box-shadow: none !important;
        transition: all 0.2s cubic-bezier(0.2,0,0.2,1) !important;
      }
      #ngd-root .button-primary:hover {
        background: linear-gradient(135deg, var(--ngd-accent-lo), var(--ngd-accent)) !important;
        transform: translateY(-1px);
        box-shadow: 0 8px 24px var(--ngd-glow) !important;
      }

      /* ===== READOUT GRID ===== */
      .ngd-readout { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px,1fr)); gap: 1px; background: var(--ngd-hair); border: 1px solid var(--ngd-hair); }
      .ngd-readout-cell {
        background: var(--ngd-surface); padding: 14px 16px;
        font-family: var(--f-mono);
      }
      .ngd-readout-cell .label {
        font-size: 10px; letter-spacing: 0.22em; text-transform: uppercase;
        color: var(--ngd-muted); margin-bottom: 6px;
      }
      .ngd-readout-cell .value {
        font-size: 14px; color: var(--ngd-text); font-weight: 500;
        word-break: break-all;
      }
      .ngd-readout-cell .value b { color: var(--ngd-accent); font-weight: 700; }
      .ngd-readout-cell .value.ok   { color: var(--ngd-signal); }
      .ngd-readout-cell .value.fail { color: var(--ngd-alert); }

      /* ===== CONSOLE OUTPUT ===== */
      .ngd-console {
        background: #020202;
        border: 1px solid var(--ngd-hair-hot);
        padding: 18px 20px;
        font-family: var(--f-mono); font-size: 12px; line-height: 1.7;
        color: var(--ngd-signal);
        white-space: pre-wrap; word-break: break-word;
        max-height: 520px; overflow: auto;
        position: relative;
      }
      .ngd-console::before {
        content: '// CONSOLE'; position: absolute; top: 8px; right: 14px;
        font-size: 9px; letter-spacing: 0.25em; color: var(--ngd-dim);
      }
      .ngd-console:empty::after {
        content: '◌ standby — click an action above.';
        color: var(--ngd-dim); font-size: 11px; letter-spacing: 0.15em;
      }
      /* Scrollbar */
      .ngd-console::-webkit-scrollbar { width: 8px; height: 8px; }
      .ngd-console::-webkit-scrollbar-track { background: transparent; }
      .ngd-console::-webkit-scrollbar-thumb { background: var(--ngd-hair-hot); }
      .ngd-console::-webkit-scrollbar-thumb:hover { background: var(--ngd-accent); }

      /* ===== LOG TAIL ===== */
      .ngd-log {
        background: var(--ngd-surface);
        border: 1px solid var(--ngd-hair);
        padding: 14px 16px;
        font-family: var(--f-mono); font-size: 11px;
        color: var(--ngd-muted);
        max-height: 280px; overflow: auto;
        line-height: 1.65;
        white-space: pre-wrap; word-break: break-all;
      }

      /* ===== CORNER QUADRANTS ===== */
      .ngd-quad {
        position: absolute; font-family: var(--f-mono); font-size: 10px;
        letter-spacing: 0.2em; color: var(--ngd-dim); text-transform: uppercase;
        pointer-events: none; z-index: 2;
      }
      .ngd-quad.tr { top: 24px; right: 32px; text-align: right; }
      .ngd-quad b { color: var(--ngd-accent); font-weight: 500; }

      /* Running state */
      .ngd-running .ngd-btn[data-ngd-act] { opacity: 0.5; pointer-events: none; }

      /* Hide redundant WP UI */
      #ngd-root .wrap > h1:first-child { display: none; }
    </style>

    <div id="ngd-root">
      <div class="ngd-quad tr">COMMAND // <b>DEPLOY</b><br>SESSION // <?php echo esc_html(substr(wp_create_nonce('ngd_session'), 0, 8)); ?></div>

      <!-- SYSTEM BAR -->
      <div class="ngd-sysbar">
        <div class="pill"><span class="ngd-dot"></span>NEOGEN // DEPLOY STATION</div>
        <div style="display:flex; gap:20px; flex-wrap:wrap;">
          <span>ENV // <b>PROD</b></span>
          <span>USER // <b><?php echo esc_html(wp_get_current_user()->user_login); ?></b></span>
          <span>BUILD // <b><?php echo esc_html(NGD_VERSION); ?></b></span>
        </div>
      </div>

      <!-- HERO -->
      <div class="ngd-hero">
        <div>
          <div class="ngd-kicker">mission control · deploy pipeline</div>
          <h1 class="ngd-title">Pull · <em>Deploy</em> · Rollback</h1>
          <div class="ngd-title-sub">// Git-pull engine for neogen-custom · admin-only · rate-limited 20/hr</div>
        </div>
        <div class="ngd-version-tag">N<span class="v">G</span></div>
      </div>

      <!-- WARNING -->
      <div class="ngd-warn">
        <b>⚠ secure operation</b> &nbsp;—&nbsp; This station deploys arbitrary code to the production filesystem. Keep the PAT private, rotate every 90 days, and confirm 2FA is live on your admin account.
      </div>

      <!-- SETTINGS PANEL -->
      <div class="ngd-panel">
        <div class="ngd-panel-head">
          <h3>01 // Deploy Configuration</h3>
          <span class="tag">persistent · encrypted at rest</span>
        </div>
        <form method="post" action="options.php">
          <?php settings_fields(NGD_OPT); ?>
          <div class="ngd-form">
            <label for="ngd_repo">Repo URL</label>
            <input id="ngd_repo" class="ngd-input" name="<?php echo NGD_OPT; ?>[repo_url]" type="url" value="<?php echo esc_attr($s['repo_url']); ?>">

            <label for="ngd_branch">Branch</label>
            <input id="ngd_branch" class="ngd-input" name="<?php echo NGD_OPT; ?>[branch]" type="text" value="<?php echo esc_attr($s['branch']); ?>">

            <label for="ngd_target">Target</label>
            <input id="ngd_target" class="ngd-input" name="<?php echo NGD_OPT; ?>[target_rel]" type="text" value="<?php echo esc_attr($s['target_rel']); ?>">
            <div class="ngd-desc">// relative to <code>wp-content/</code> · must begin with <code>mu-plugins/</code>, <code>plugins/</code>, <code>themes/</code>, or <code>uploads/</code></div>

            <label for="ngd_pat">GitHub PAT</label>
            <input id="ngd_pat" class="ngd-input" name="<?php echo NGD_OPT; ?>[pat]" type="password" value="" autocomplete="off"
                   placeholder="<?php echo $s['pat_enc'] ? '▪▪▪▪▪▪▪▪  ·  paste to overwrite' : 'github_pat_xxx...'; ?>">
            <div class="ngd-desc">// Fine-grained PAT · <code>Contents: Read and write</code> scope on the repo only</div>
          </div>
          <div style="margin-top:20px;"><?php submit_button('✓ Save Configuration', 'primary', 'submit', false); ?></div>
        </form>
      </div>

      <!-- STATUS READOUT -->
      <div class="ngd-panel">
        <div class="ngd-panel-head">
          <h3>02 // Status Readout</h3>
          <span class="tag">live · refreshes after each action</span>
        </div>
        <div class="ngd-readout" id="ngd-readout">
          <div class="ngd-readout-cell"><div class="label">git binary</div><div class="value" id="r-git">—</div></div>
          <div class="ngd-readout-cell"><div class="label">target dir</div><div class="value" id="r-target" style="font-size:12px;">—</div></div>
          <div class="ngd-readout-cell"><div class="label">repo state</div><div class="value" id="r-state">—</div></div>
          <div class="ngd-readout-cell"><div class="label">current commit</div><div class="value" id="r-commit" style="font-size:11px;">—</div></div>
          <div class="ngd-readout-cell"><div class="label">rate limit</div><div class="value" id="r-rate">—</div></div>
        </div>
      </div>

      <!-- ACTIONS -->
      <div class="ngd-panel">
        <div class="ngd-panel-head">
          <h3>03 // Actions</h3>
          <span class="tag">select → execute → observe console</span>
        </div>
        <div style="display:flex; flex-wrap:wrap; gap:0;">
          <button class="ngd-btn" data-ngd-act="clone"><span>⬇ Clone</span><span class="arrow">→</span></button>
          <button class="ngd-btn ngd-btn-primary" data-ngd-act="pull"><span>⟳ Pull Latest</span><span class="arrow">→</span></button>
          <button class="ngd-btn" data-ngd-act="diff"><span>◉ Show Diff</span><span class="arrow">→</span></button>
          <button class="ngd-btn ngd-btn-danger" data-ngd-act="rollback"><span>← Rollback</span></button>
        </div>
      </div>

      <!-- CONSOLE -->
      <div class="ngd-panel">
        <div class="ngd-panel-head">
          <h3>04 // Output Console</h3>
          <span class="tag">stream · last action result</span>
        </div>
        <div id="ngd-out" class="ngd-console"></div>
      </div>

      <!-- DEPLOY LOG -->
      <div class="ngd-panel">
        <div class="ngd-panel-head">
          <h3>05 // Deploy Log <span style="color:var(--ngd-muted); font-size:11px; font-family:var(--f-mono); margin-left:8px; letter-spacing:0.15em;">— tail 20</span></h3>
          <span class="tag"><?php echo file_exists(NGD_LOG) ? 'historical' : 'empty'; ?></span>
        </div>
        <pre class="ngd-log"><?php echo $log_tail ?: '◌ no deploys yet.'; ?></pre>
      </div>
    </div>

    <script>
    (function () {
        const NONCE = <?php echo wp_json_encode($nonce); ?>;
        const AJAX = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
        const root = document.getElementById('ngd-root');
        const out  = document.getElementById('ngd-out');
        const r = {
            git:    document.getElementById('r-git'),
            target: document.getElementById('r-target'),
            state:  document.getElementById('r-state'),
            commit: document.getElementById('r-commit'),
            rate:   document.getElementById('r-rate'),
        };

        function call(act) {
            root.classList.add('ngd-running');
            out.textContent = '';
            typewrite(out, '> ' + act + ' · initiating...\n');
            const fd = new FormData();
            fd.append('action', 'ngd_action');
            fd.append('act', act);
            fd.append('_nonce', NONCE);
            fetch(AJAX, { method: 'POST', credentials: 'same-origin', body: fd })
                .then(r => r.json())
                .then(j => {
                    out.textContent = '';
                    const ok = j.success !== false;
                    typewrite(out,
                        '> ' + act + ' · ' + (ok ? '✓ completed' : '✗ failed') + '\n\n' +
                        JSON.stringify(j, null, 2)
                    );
                    if (!ok) out.style.color = 'var(--ngd-alert)';
                    else     out.style.color = 'var(--ngd-signal)';
                    if (act !== 'diff') setTimeout(probe, 600);
                })
                .catch(e => { out.textContent = '✗ network error: ' + e.message; out.style.color = 'var(--ngd-alert)'; })
                .finally(() => root.classList.remove('ngd-running'));
        }

        function typewrite(el, text) {
            let i = 0; el.textContent = '';
            (function loop(){
                if (i >= text.length) return;
                const chunk = Math.min(12, text.length - i);
                el.textContent += text.substr(i, chunk);
                el.scrollTop = el.scrollHeight;
                i += chunk;
                if (i < text.length) requestAnimationFrame(loop);
            })();
        }

        function probe() {
            const fd = new FormData();
            fd.append('action', 'ngd_action');
            fd.append('act', 'probe');
            fd.append('_nonce', NONCE);
            fetch(AJAX, { method: 'POST', credentials: 'same-origin', body: fd })
                .then(res => res.json())
                .then(j => {
                    const d = j.data || {};
                    r.git.innerHTML = d.has_git
                        ? '<span class="value ok">✓ git available</span>'
                        : '<span class="value fail">✗ missing — zip fallback</span>';
                    r.target.textContent = d.target || '—';
                    const stateHTML = [];
                    stateHTML.push(d.target_exists ? '<b>exists</b>' : '<span style="color:var(--ngd-alert)">missing</span>');
                    stateHTML.push(d.is_repo ? '<b>git-tracked</b>' : '<span style="color:var(--ngd-dim)">not a repo</span>');
                    r.state.innerHTML = stateHTML.join(' · ');
                    r.commit.textContent = d.current_commit || '(none)';
                    r.rate.innerHTML = '<b>' + (d.rate_left ?? 0) + '</b> / 20  · 1h window';
                });
        }

        document.querySelectorAll('[data-ngd-act]').forEach(btn => {
            btn.addEventListener('click', e => {
                e.preventDefault();
                const act = btn.getAttribute('data-ngd-act');
                if (act === 'rollback' && !confirm('⚠ Rollback to previous commit?\n\nThis reverts the last deploy. Not undoable via this plugin.')) return;
                call(act);
            });
        });
        probe();
    })();
    </script>
    <?php
}
