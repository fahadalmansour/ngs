<?php
// Clean removal: drop encrypted PAT + settings from DB. Keep deploy log for audit.
if (!defined('WP_UNINSTALL_PLUGIN')) exit;
delete_option('ngd_settings');
// Note: we do NOT delete the target directory (mu-plugins/neogen-custom) so the
// deployed code stays running. Only removing this plugin's own settings.
