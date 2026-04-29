<?php
/**
 * Theme Setup
 *
 * Initial theme configuration and support features.
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * NGS Theme Setup
 *
 * @since 1.0.0
 */
function ngs_theme_setup() {
	// Automatic feed links
	add_theme_support( 'automatic-feed-links' );

	// Let WordPress manage the document title
	add_theme_support( 'title-tag' );

	// Enable Post Thumbnails
	add_theme_support( 'post-thumbnails' );

	// Custom image sizes
	add_image_size( 'ngs-product-card', 576, 576, true );    // 2x for retina (288x288 display)
	add_image_size( 'ngs-hero', 2000, 1500, true );          // Hero banners
	add_image_size( 'ngs-thumbnail', 288, 288, true );       // Small thumbnails

	// HTML5 support
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'gallery',
		'caption',
		'style',
		'script',
	) );

	// Selective refresh for widgets
	add_theme_support( 'customize-selective-refresh-widgets' );

	// Responsive embeds
	add_theme_support( 'responsive-embeds' );

	// Block styles
	add_theme_support( 'wp-block-styles' );

	// Custom logo
	add_theme_support( 'custom-logo', array(
		'height'      => 250,
		'width'       => 250,
		'flex-width'  => true,
		'flex-height' => true,
	) );

	// WooCommerce support
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	// Register navigation menus
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary Menu', 'ngs-designsystem' ),
		'footer'  => esc_html__( 'Footer Menu', 'ngs-designsystem' ),
		'mobile'  => esc_html__( 'Mobile Menu', 'ngs-designsystem' ),
	) );

	// Content width
	if ( ! isset( $content_width ) ) {
		$content_width = 1200;
	}

	// Load text domain for translations
	load_theme_textdomain( 'ngs-designsystem', NGS_THEME_DIR . '/languages' );
}
add_action( 'after_setup_theme', 'ngs_theme_setup' );

/**
 * Disable admin bar for non-admins
 *
 * @since 1.0.0
 */
function ngs_disable_admin_bar() {
	if ( ! current_user_can( 'administrator' ) && ! current_user_can( 'editor' ) ) {
		show_admin_bar( false );
	}
}
add_action( 'after_setup_theme', 'ngs_disable_admin_bar' );
