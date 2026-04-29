<?php
/**
 * WooCommerce Integration
 *
 * Custom WooCommerce functionality and modifications.
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register custom product attributes
 *
 * @since 1.0.0
 */
function ngs_register_product_attributes() {
	// Protocol attribute
	if ( ! taxonomy_exists( 'pa_protocol' ) ) {
		register_taxonomy( 'pa_protocol', 'product', array(
			'label'        => esc_html__( 'Protocol', 'ngs-designsystem' ),
			'hierarchical' => false,
			'public'       => true,
			'show_ui'      => true,
		) );
	}

	// Compatibility attribute
	if ( ! taxonomy_exists( 'pa_compatibility' ) ) {
		register_taxonomy( 'pa_compatibility', 'product', array(
			'label'        => esc_html__( 'Compatibility', 'ngs-designsystem' ),
			'hierarchical' => false,
			'public'       => true,
			'show_ui'      => true,
		) );
	}

	// Power source attribute
	if ( ! taxonomy_exists( 'pa_power_source' ) ) {
		register_taxonomy( 'pa_power_source', 'product', array(
			'label'        => esc_html__( 'Power Source', 'ngs-designsystem' ),
			'hierarchical' => false,
			'public'       => true,
			'show_ui'      => true,
		) );
	}
}
add_action( 'init', 'ngs_register_product_attributes' );

/**
 * AJAX add to cart handler
 *
 * @since 1.0.0
 */
function ngs_ajax_add_to_cart() {
	// Verify nonce
	check_ajax_referer( 'ngs-ajax-nonce', 'nonce' );

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$quantity = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 1;

	if ( ! $product_id ) {
		wp_send_json_error( array(
			'message' => esc_html__( 'Invalid product', 'ngs-designsystem' ),
		) );
	}

	$added = WC()->cart->add_to_cart( $product_id, $quantity );

	if ( $added ) {
		wp_send_json_success( array(
			'message'    => esc_html__( 'Product added to cart', 'ngs-designsystem' ),
			'cart_count' => WC()->cart->get_cart_contents_count(),
			'cart_hash'  => WC()->cart->get_cart_hash(),
		) );
	} else {
		wp_send_json_error( array(
			'message' => esc_html__( 'Failed to add product to cart', 'ngs-designsystem' ),
		) );
	}
}
add_action( 'wp_ajax_ngs_add_to_cart', 'ngs_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_ngs_add_to_cart', 'ngs_ajax_add_to_cart' );

/**
 * Update cart fragments for AJAX cart
 *
 * @since 1.0.0
 * @param array $fragments Cart fragments
 * @return array Modified fragments
 */
function ngs_cart_fragments( $fragments ) {
	$cart_count = WC()->cart->get_cart_contents_count();

	ob_start();
	?>
	<span class="ngs-cart-count"><?php echo absint( $cart_count ); ?></span>
	<?php
	$fragments['.ngs-cart-count'] = ob_get_clean();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'ngs_cart_fragments' );

/**
 * Set product columns to 4 on desktop
 *
 * @since 1.0.0
 * @return int Number of columns
 */
function ngs_product_columns() {
	return 4;
}
add_filter( 'loop_shop_columns', 'ngs_product_columns' );

/**
 * Set products per page to 12
 *
 * @since 1.0.0
 * @return int Number of products per page
 */
function ngs_products_per_page() {
	return 12;
}
add_filter( 'loop_shop_per_page', 'ngs_products_per_page' );

/**
 * Remove default WooCommerce breadcrumbs
 *
 * @since 1.0.0
 */
function ngs_remove_wc_breadcrumbs() {
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
}
add_action( 'init', 'ngs_remove_wc_breadcrumbs' );

/**
 * Add custom ordering options
 *
 * @since 1.0.0
 * @param array $sortby Sorting options
 * @return array Modified sorting options
 */
function ngs_custom_product_sorting( $sortby ) {
	$sortby['price'] = esc_html__( 'Price: Low to High', 'ngs-designsystem' );
	$sortby['price-desc'] = esc_html__( 'Price: High to Low', 'ngs-designsystem' );
	$sortby['date'] = esc_html__( 'Newest', 'ngs-designsystem' );
	$sortby['popularity'] = esc_html__( 'Popularity', 'ngs-designsystem' );
	$sortby['rating'] = esc_html__( 'Rating', 'ngs-designsystem' );

	return $sortby;
}
add_filter( 'woocommerce_default_catalog_orderby_options', 'ngs_custom_product_sorting' );
add_filter( 'woocommerce_catalog_orderby', 'ngs_custom_product_sorting' );

/**
 * Customize product loop hooks
 *
 * @since 1.0.0
 */
function ngs_customize_product_loop() {
	// Remove default hooks
	remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
	remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );

	// Add custom hooks (implement in template files)
	// add_action( 'woocommerce_shop_loop_item_title', 'ngs_custom_product_title', 10 );
	// add_action( 'woocommerce_after_shop_loop_item_title', 'ngs_custom_product_price', 10 );
}
add_action( 'init', 'ngs_customize_product_loop' );

/**
 * Customize quantity input
 *
 * @since 1.0.0
 * @param array $args Quantity input args
 * @return array Modified args
 */
function ngs_quantity_input_args( $args ) {
	$args['classes'] = array( 'ngs-quantity-input' );
	$args['input_value'] = isset( $args['input_value'] ) ? $args['input_value'] : 1;
	return $args;
}
add_filter( 'woocommerce_quantity_input_args', 'ngs_quantity_input_args' );

/**
 * Add Saudi phone number validation for checkout
 *
 * @since 1.0.0
 * @param array $fields Checkout fields
 * @return array Modified fields
 */
function ngs_checkout_phone_validation( $fields ) {
	$fields['billing']['billing_phone']['custom_attributes'] = array(
		'pattern' => '^(05|5)[0-9]{8}$',
		'title'   => esc_html__( 'Please enter a valid Saudi phone number (e.g., 0501234567)', 'ngs-designsystem' ),
	);

	$fields['billing']['billing_phone']['placeholder'] = esc_html__( '05XXXXXXXX', 'ngs-designsystem' );

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'ngs_checkout_phone_validation' );

/**
 * Add VAT to checkout totals
 *
 * @since 1.0.0
 */
function ngs_display_vat_total() {
	$vat_number = get_theme_mod( 'ngs_vat_number' );
	if ( ! empty( $vat_number ) ) {
		echo '<div class="ngs-vat-notice">';
		echo '<small>' . esc_html__( 'VAT Number:', 'ngs-designsystem' ) . ' ' . esc_html( $vat_number ) . '</small>';
		echo '</div>';
	}
}
add_action( 'woocommerce_review_order_after_order_total', 'ngs_display_vat_total' );

/**
 * Disable WooCommerce styles
 * Note: Already handled in enqueue.php via filter
 *
 * @since 1.0.0
 */
// add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );
