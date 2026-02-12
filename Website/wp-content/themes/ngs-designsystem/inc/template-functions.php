<?php
/**
 * Template Helper Functions
 *
 * Reusable template functions for common UI elements.
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output breadcrumb trail with Schema.org markup
 *
 * @since 1.0.0
 */
function ngs_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}

	$breadcrumbs = array();
	$position = 1;

	// Home
	$breadcrumbs[] = array(
		'position' => $position++,
		'name'     => esc_html__( 'Home', 'ngs-designsystem' ),
		'url'      => home_url( '/' ),
	);

	// WooCommerce
	if ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
		if ( is_shop() ) {
			$breadcrumbs[] = array(
				'position' => $position++,
				'name'     => esc_html__( 'Shop', 'ngs-designsystem' ),
				'url'      => '',
			);
		} elseif ( is_product_category() || is_product_tag() ) {
			$breadcrumbs[] = array(
				'position' => $position++,
				'name'     => esc_html__( 'Shop', 'ngs-designsystem' ),
				'url'      => get_permalink( wc_get_page_id( 'shop' ) ),
			);
			$breadcrumbs[] = array(
				'position' => $position++,
				'name'     => single_term_title( '', false ),
				'url'      => '',
			);
		} elseif ( is_product() ) {
			$breadcrumbs[] = array(
				'position' => $position++,
				'name'     => esc_html__( 'Shop', 'ngs-designsystem' ),
				'url'      => get_permalink( wc_get_page_id( 'shop' ) ),
			);
			$breadcrumbs[] = array(
				'position' => $position++,
				'name'     => get_the_title(),
				'url'      => '',
			);
		}
	} elseif ( is_page() ) {
		$breadcrumbs[] = array(
			'position' => $position++,
			'name'     => get_the_title(),
			'url'      => '',
		);
	} elseif ( is_single() ) {
		$category = get_the_category();
		if ( ! empty( $category ) ) {
			$breadcrumbs[] = array(
				'position' => $position++,
				'name'     => $category[0]->name,
				'url'      => get_category_link( $category[0]->term_id ),
			);
		}
		$breadcrumbs[] = array(
			'position' => $position++,
			'name'     => get_the_title(),
			'url'      => '',
		);
	}

	// Output HTML
	if ( empty( $breadcrumbs ) ) {
		return;
	}

	echo '<nav class="ngs-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'ngs-designsystem' ) . '">';
	echo '<ol class="ngs-breadcrumbs__list" itemscope itemtype="https://schema.org/BreadcrumbList">';

	foreach ( $breadcrumbs as $crumb ) {
		echo '<li class="ngs-breadcrumbs__item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';

		if ( ! empty( $crumb['url'] ) ) {
			echo '<a href="' . esc_url( $crumb['url'] ) . '" itemprop="item">';
			echo '<span itemprop="name">' . esc_html( $crumb['name'] ) . '</span>';
			echo '</a>';
		} else {
			echo '<span itemprop="name">' . esc_html( $crumb['name'] ) . '</span>';
		}

		echo '<meta itemprop="position" content="' . absint( $crumb['position'] ) . '">';
		echo '</li>';
	}

	echo '</ol>';
	echo '</nav>';
}

/**
 * Output protocol badge pills for a product
 *
 * @since 1.0.0
 * @param int $product_id Product ID
 */
function ngs_protocol_badges( $product_id ) {
	$protocols = wp_get_post_terms( $product_id, 'pa_protocol', array( 'fields' => 'names' ) );

	if ( empty( $protocols ) || is_wp_error( $protocols ) ) {
		return;
	}

	echo '<div class="ngs-protocol-badges">';
	foreach ( $protocols as $protocol ) {
		$protocol_slug = sanitize_title( $protocol );
		echo '<span class="ngs-badge ngs-badge--protocol ngs-badge--' . esc_attr( $protocol_slug ) . '">';
		echo esc_html( $protocol );
		echo '</span>';
	}
	echo '</div>';
}

/**
 * Output stock status badge
 *
 * @since 1.0.0
 * @param WC_Product $product Product object
 */
function ngs_stock_badge( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}

	$stock_status = $product->get_stock_status();
	$stock_quantity = $product->get_stock_quantity();

	$badge_class = 'ngs-badge ngs-badge--stock';
	$badge_text = '';

	switch ( $stock_status ) {
		case 'instock':
			if ( $stock_quantity && $stock_quantity <= 5 ) {
				$badge_class .= ' ngs-badge--low-stock';
				$badge_text = sprintf(
					/* translators: %d: stock quantity */
					esc_html__( 'Only %d left', 'ngs-designsystem' ),
					$stock_quantity
				);
			} else {
				$badge_class .= ' ngs-badge--in-stock';
				$badge_text = esc_html__( 'In Stock', 'ngs-designsystem' );
			}
			break;

		case 'outofstock':
			$badge_class .= ' ngs-badge--out-of-stock';
			$badge_text = esc_html__( 'Out of Stock', 'ngs-designsystem' );
			break;

		case 'onbackorder':
			$badge_class .= ' ngs-badge--backorder';
			$badge_text = esc_html__( 'On Backorder', 'ngs-designsystem' );
			break;
	}

	if ( $badge_text ) {
		echo '<span class="' . esc_attr( $badge_class ) . '">' . esc_html( $badge_text ) . '</span>';
	}
}

/**
 * Output trust badges row
 *
 * @since 1.0.0
 */
function ngs_trust_badges() {
	$badges = array(
		array(
			'icon'  => 'shield-check',
			'title' => esc_html__( '2 Year Warranty', 'ngs-designsystem' ),
			'desc'  => esc_html__( 'All products covered', 'ngs-designsystem' ),
		),
		array(
			'icon'  => 'truck',
			'title' => esc_html__( 'Free Shipping', 'ngs-designsystem' ),
			'desc'  => esc_html__( 'Orders over 500 SAR', 'ngs-designsystem' ),
		),
		array(
			'icon'  => 'headset',
			'title' => esc_html__( '24/7 Support', 'ngs-designsystem' ),
			'desc'  => esc_html__( 'Expert assistance', 'ngs-designsystem' ),
		),
		array(
			'icon'  => 'refresh',
			'title' => esc_html__( '14 Day Returns', 'ngs-designsystem' ),
			'desc'  => esc_html__( 'Easy returns policy', 'ngs-designsystem' ),
		),
	);

	echo '<div class="ngs-trust-badges">';
	foreach ( $badges as $badge ) {
		echo '<div class="ngs-trust-badge">';
		echo ngs_get_theme_svg( $badge['icon'] );
		echo '<div class="ngs-trust-badge__content">';
		echo '<div class="ngs-trust-badge__title">' . esc_html( $badge['title'] ) . '</div>';
		echo '<div class="ngs-trust-badge__desc">' . esc_html( $badge['desc'] ) . '</div>';
		echo '</div>';
		echo '</div>';
	}
	echo '</div>';
}

/**
 * Output payment method badges
 *
 * @since 1.0.0
 */
function ngs_payment_badges() {
	$payment_methods = array(
		'mada'      => 'Mada',
		'visa'      => 'Visa',
		'mastercard' => 'Mastercard',
		'tamara'    => 'Tamara',
		'tabby'     => 'Tabby',
		'applepay'  => 'Apple Pay',
	);

	echo '<div class="ngs-payment-badges">';
	foreach ( $payment_methods as $slug => $name ) {
		echo '<img src="' . esc_url( NGS_THEME_URI . '/assets/images/badges/' . $slug . '.svg' ) . '" alt="' . esc_attr( $name ) . '" class="ngs-payment-badge" loading="lazy">';
	}
	echo '</div>';
}

/**
 * Get social media links from customizer
 *
 * @since 1.0.0
 * @return array Array of social links with icon and URL
 */
function ngs_get_social_links() {
	$links = array();

	$socials = array(
		'instagram' => 'instagram',
		'twitter'   => 'twitter',
		'youtube'   => 'youtube',
		'tiktok'    => 'tiktok',
	);

	foreach ( $socials as $key => $icon ) {
		$url = get_theme_mod( 'ngs_' . $key . '_url' );
		if ( ! empty( $url ) ) {
			$links[] = array(
				'icon' => $icon,
				'url'  => esc_url( $url ),
				'name' => ucfirst( $key ),
			);
		}
	}

	return $links;
}

/**
 * Check if product has AR/3D model
 *
 * @since 1.0.0
 * @param int $product_id Product ID
 * @return bool True if product has 3D model
 */
function ngs_product_has_3d_model( $product_id ) {
	$model_url = get_post_meta( $product_id, '_neogen_3d_model_url', true );
	return ! empty( $model_url );
}

/**
 * Get inline SVG icon
 *
 * @since 1.0.0
 * @param string $icon_name Icon name (without .svg extension)
 * @return string SVG markup or empty string
 */
function ngs_get_theme_svg( $icon_name ) {
	$icon_path = NGS_THEME_DIR . '/assets/images/icons/' . $icon_name . '.svg';

	if ( ! file_exists( $icon_path ) ) {
		return '';
	}

	return file_get_contents( $icon_path );
}
