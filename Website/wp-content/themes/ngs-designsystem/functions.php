<?php
/**
 * NGS Design System Theme Functions
 *
 * Main orchestrator file that requires all functionality files.
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Theme Constants
 */
define( 'NGS_THEME_VERSION', '1.0.0' );
define( 'NGS_THEME_DIR', get_template_directory() );
define( 'NGS_THEME_URI', get_template_directory_uri() );

/**
 * Core Theme Files
 */
require_once NGS_THEME_DIR . '/inc/theme-setup.php';
require_once NGS_THEME_DIR . '/inc/enqueue.php';
require_once NGS_THEME_DIR . '/inc/customizer.php';
require_once NGS_THEME_DIR . '/inc/template-functions.php';
require_once NGS_THEME_DIR . '/inc/walker-nav-menu.php';
require_once NGS_THEME_DIR . '/inc/seo.php';

/**
 * WooCommerce Integration
 * Load only if WooCommerce is active
 */
if ( class_exists( 'WooCommerce' ) ) {
	require_once NGS_THEME_DIR . '/inc/woocommerce.php';
	require_once NGS_THEME_DIR . '/inc/ar-support.php';
}
