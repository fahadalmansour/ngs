<?php
/**
 * Enqueue Scripts and Styles
 *
 * Handles all CSS and JavaScript loading with conditional logic.
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dequeue default WooCommerce styles
 *
 * @since 1.0.0
 * @return array Empty array to disable all default WooCommerce styles
 */
function ngs_dequeue_woocommerce_styles( $enqueue_styles ) {
	return array(); // Disable all default WooCommerce styles
}
add_filter( 'woocommerce_enqueue_styles', 'ngs_dequeue_woocommerce_styles' );

/**
 * Enqueue theme styles
 *
 * @since 1.0.0
 */
function ngs_enqueue_styles() {
	$version = NGS_THEME_VERSION;
	$theme_uri = NGS_THEME_URI;

	// Critical CSS - Load first
	wp_enqueue_style( 'ngs-tokens', $theme_uri . '/assets/css/tokens.css', array(), $version );
	wp_enqueue_style( 'ngs-reset', $theme_uri . '/assets/css/reset.css', array( 'ngs-tokens' ), $version );
	wp_enqueue_style( 'ngs-typography', $theme_uri . '/assets/css/typography.css', array( 'ngs-tokens' ), $version );
	wp_enqueue_style( 'ngs-layout', $theme_uri . '/assets/css/layout.css', array( 'ngs-tokens' ), $version );

	// Component CSS
	wp_enqueue_style( 'ngs-header', $theme_uri . '/assets/css/components/header.css', array( 'ngs-layout' ), $version );
	wp_enqueue_style( 'ngs-footer', $theme_uri . '/assets/css/components/footer.css', array( 'ngs-layout' ), $version );
	wp_enqueue_style( 'ngs-buttons', $theme_uri . '/assets/css/components/buttons.css', array( 'ngs-tokens' ), $version );
	wp_enqueue_style( 'ngs-cards', $theme_uri . '/assets/css/components/cards.css', array( 'ngs-tokens' ), $version );
	wp_enqueue_style( 'ngs-badges', $theme_uri . '/assets/css/components/badges.css', array( 'ngs-tokens' ), $version );
	wp_enqueue_style( 'ngs-forms', $theme_uri . '/assets/css/components/forms.css', array( 'ngs-tokens' ), $version );
	wp_enqueue_style( 'ngs-navigation', $theme_uri . '/assets/css/components/navigation.css', array( 'ngs-tokens' ), $version );
	wp_enqueue_style( 'ngs-breadcrumbs', $theme_uri . '/assets/css/components/breadcrumbs.css', array( 'ngs-tokens' ), $version );
	wp_enqueue_style( 'ngs-accordion', $theme_uri . '/assets/css/components/accordion.css', array( 'ngs-tokens' ), $version );
	wp_enqueue_style( 'ngs-tabs', $theme_uri . '/assets/css/components/tabs.css', array( 'ngs-tokens' ), $version );
	wp_enqueue_style( 'ngs-modal', $theme_uri . '/assets/css/components/modal.css', array( 'ngs-tokens' ), $version );
	wp_enqueue_style( 'ngs-gallery', $theme_uri . '/assets/css/components/gallery.css', array( 'ngs-tokens' ), $version );

	// Page-specific CSS
	if ( is_front_page() ) {
		wp_enqueue_style( 'ngs-home', $theme_uri . '/assets/css/pages/home.css', array( 'ngs-layout' ), $version );
	}

	if ( is_shop() || is_product_category() || is_product_tag() ) {
		wp_enqueue_style( 'ngs-shop', $theme_uri . '/assets/css/pages/shop.css', array( 'ngs-layout' ), $version );
	}

	if ( is_product() ) {
		wp_enqueue_style( 'ngs-product', $theme_uri . '/assets/css/pages/product.css', array( 'ngs-layout' ), $version );
	}

	if ( is_cart() ) {
		wp_enqueue_style( 'ngs-cart', $theme_uri . '/assets/css/pages/cart.css', array( 'ngs-layout' ), $version );
	}

	if ( is_checkout() ) {
		wp_enqueue_style( 'ngs-checkout', $theme_uri . '/assets/css/pages/checkout.css', array( 'ngs-layout' ), $version );
	}

	// Self-hosted fonts with preload
	wp_enqueue_style( 'ngs-fonts', $theme_uri . '/assets/fonts/fonts.css', array(), $version );
}
add_action( 'wp_enqueue_scripts', 'ngs_enqueue_styles', 5 );

/**
 * Preload fonts in head
 *
 * @since 1.0.0
 */
function ngs_preload_fonts() {
	$theme_uri = NGS_THEME_URI;
	?>
	<link rel="preload" href="<?php echo esc_url( $theme_uri . '/assets/fonts/Inter-Regular.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="<?php echo esc_url( $theme_uri . '/assets/fonts/Inter-Medium.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="<?php echo esc_url( $theme_uri . '/assets/fonts/Inter-SemiBold.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="<?php echo esc_url( $theme_uri . '/assets/fonts/Cairo-Regular.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
	<link rel="preload" href="<?php echo esc_url( $theme_uri . '/assets/fonts/Cairo-SemiBold.woff2' ); ?>" as="font" type="font/woff2" crossorigin>
	<?php
}
add_action( 'wp_head', 'ngs_preload_fonts', 1 );

/**
 * Enqueue theme scripts
 *
 * @since 1.0.0
 */
function ngs_enqueue_scripts() {
	$version = NGS_THEME_VERSION;
	$theme_uri = NGS_THEME_URI;

	// Main JavaScript
	wp_enqueue_script( 'ngs-main', $theme_uri . '/assets/js/main.js', array(), $version, array(
		'strategy'  => 'defer',
		'in_footer' => true,
	) );

	// Component scripts - conditional loading
	if ( is_product() ) {
		wp_enqueue_script( 'ngs-gallery', $theme_uri . '/assets/js/components/gallery.js', array(), $version, array(
			'strategy'  => 'defer',
			'in_footer' => true,
		) );
	}

	if ( is_shop() || is_product_category() || is_product_tag() ) {
		wp_enqueue_script( 'ngs-filters', $theme_uri . '/assets/js/components/filters.js', array(), $version, array(
			'strategy'  => 'defer',
			'in_footer' => true,
		) );
	}

	if ( is_cart() || is_checkout() || is_account_page() ) {
		wp_enqueue_script( 'ngs-cart', $theme_uri . '/assets/js/components/cart.js', array(), $version, array(
			'strategy'  => 'defer',
			'in_footer' => true,
		) );
	}

	if ( is_checkout() || is_account_page() ) {
		wp_enqueue_script( 'ngs-forms', $theme_uri . '/assets/js/components/forms.js', array(), $version, array(
			'strategy'  => 'defer',
			'in_footer' => true,
		) );
	}

	// Accordion (FAQ pages, product descriptions)
	if ( is_page() || is_product() ) {
		wp_enqueue_script( 'ngs-accordion', $theme_uri . '/assets/js/components/accordion.js', array(), $version, array(
			'strategy'  => 'defer',
			'in_footer' => true,
		) );
	}

	// Counter (front page)
	if ( is_front_page() ) {
		wp_enqueue_script( 'ngs-counter', $theme_uri . '/assets/js/components/counter.js', array(), $version, array(
			'strategy'  => 'defer',
			'in_footer' => true,
		) );
	}

	// Localize script for AJAX
	wp_localize_script( 'ngs-main', 'ngsAjax', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'ngs-ajax-nonce' ),
		'strings' => array(
			'addedToCart'    => esc_html__( 'Added to cart', 'ngs-designsystem' ),
			'error'          => esc_html__( 'An error occurred', 'ngs-designsystem' ),
			'loading'        => esc_html__( 'Loading...', 'ngs-designsystem' ),
			'viewCart'       => esc_html__( 'View Cart', 'ngs-designsystem' ),
			'continueShopping' => esc_html__( 'Continue Shopping', 'ngs-designsystem' ),
		),
	) );

	// Comments reply script
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'ngs_enqueue_scripts', 10 );
