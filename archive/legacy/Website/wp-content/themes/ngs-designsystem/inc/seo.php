<?php
/**
 * SEO Functions
 *
 * Schema.org structured data and Open Graph tags.
 *
 * @package NGS_Design_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output Schema.org Product JSON-LD on single product pages
 *
 * @since 1.0.0
 */
function ngs_product_schema() {
	if ( ! is_product() ) {
		return;
	}

	global $product;

	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return;
	}

	$schema = array(
		'@context'    => 'https://schema.org/',
		'@type'       => 'Product',
		'name'        => $product->get_name(),
		'description' => wp_strip_all_tags( $product->get_short_description() ?: $product->get_description() ),
		'sku'         => $product->get_sku(),
		'brand'       => array(
			'@type' => 'Brand',
			'name'  => get_bloginfo( 'name' ),
		),
	);

	// Image
	$image_id = $product->get_image_id();
	if ( $image_id ) {
		$image = wp_get_attachment_image_src( $image_id, 'full' );
		if ( $image ) {
			$schema['image'] = $image[0];
		}
	}

	// Offers
	if ( $product->is_in_stock() ) {
		$schema['offers'] = array(
			'@type'         => 'Offer',
			'url'           => get_permalink( $product->get_id() ),
			'priceCurrency' => 'SAR',
			'price'         => $product->get_price(),
			'availability'  => 'https://schema.org/InStock',
			'seller'        => array(
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			),
		);

		// Valid through (optional - 30 days from now)
		$schema['offers']['priceValidUntil'] = gmdate( 'Y-m-d', strtotime( '+30 days' ) );
	}

	// Rating
	$rating_count = $product->get_rating_count();
	$average_rating = $product->get_average_rating();

	if ( $rating_count > 0 && $average_rating > 0 ) {
		$schema['aggregateRating'] = array(
			'@type'       => 'AggregateRating',
			'ratingValue' => $average_rating,
			'reviewCount' => $rating_count,
		);
	}

	// Output JSON-LD
	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}
add_action( 'wp_head', 'ngs_product_schema' );

/**
 * Output BreadcrumbList Schema.org JSON-LD
 *
 * @since 1.0.0
 */
function ngs_breadcrumb_schema() {
	if ( is_front_page() ) {
		return;
	}

	$breadcrumbs = array();
	$position = 1;

	// Home
	$breadcrumbs[] = array(
		'@type'    => 'ListItem',
		'position' => $position++,
		'name'     => esc_html__( 'Home', 'ngs-designsystem' ),
		'item'     => home_url( '/' ),
	);

	// Context-specific breadcrumbs
	if ( function_exists( 'is_woocommerce' ) && is_woocommerce() ) {
		if ( is_shop() ) {
			$breadcrumbs[] = array(
				'@type'    => 'ListItem',
				'position' => $position++,
				'name'     => esc_html__( 'Shop', 'ngs-designsystem' ),
				'item'     => get_permalink( wc_get_page_id( 'shop' ) ),
			);
		} elseif ( is_product_category() || is_product_tag() ) {
			$breadcrumbs[] = array(
				'@type'    => 'ListItem',
				'position' => $position++,
				'name'     => esc_html__( 'Shop', 'ngs-designsystem' ),
				'item'     => get_permalink( wc_get_page_id( 'shop' ) ),
			);
			$breadcrumbs[] = array(
				'@type'    => 'ListItem',
				'position' => $position++,
				'name'     => single_term_title( '', false ),
				'item'     => get_term_link( get_queried_object() ),
			);
		} elseif ( is_product() ) {
			global $product;
			$breadcrumbs[] = array(
				'@type'    => 'ListItem',
				'position' => $position++,
				'name'     => esc_html__( 'Shop', 'ngs-designsystem' ),
				'item'     => get_permalink( wc_get_page_id( 'shop' ) ),
			);
			$breadcrumbs[] = array(
				'@type'    => 'ListItem',
				'position' => $position++,
				'name'     => $product->get_name(),
				'item'     => get_permalink( $product->get_id() ),
			);
		}
	} elseif ( is_page() ) {
		$breadcrumbs[] = array(
			'@type'    => 'ListItem',
			'position' => $position++,
			'name'     => get_the_title(),
			'item'     => get_permalink(),
		);
	} elseif ( is_single() ) {
		$category = get_the_category();
		if ( ! empty( $category ) ) {
			$breadcrumbs[] = array(
				'@type'    => 'ListItem',
				'position' => $position++,
				'name'     => $category[0]->name,
				'item'     => get_category_link( $category[0]->term_id ),
			);
		}
		$breadcrumbs[] = array(
			'@type'    => 'ListItem',
			'position' => $position++,
			'name'     => get_the_title(),
			'item'     => get_permalink(),
		);
	}

	if ( count( $breadcrumbs ) < 2 ) {
		return;
	}

	$schema = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $breadcrumbs,
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}
add_action( 'wp_head', 'ngs_breadcrumb_schema' );

/**
 * Output Open Graph meta tags
 *
 * @since 1.0.0
 */
function ngs_og_tags() {
	// Locale
	echo '<meta property="og:locale" content="ar_SA">' . "\n";

	// Type
	$og_type = is_single() || is_page() ? 'article' : 'website';
	if ( function_exists( 'is_product' ) && is_product() ) {
		$og_type = 'product';
	}
	echo '<meta property="og:type" content="' . esc_attr( $og_type ) . '">' . "\n";

	// Title
	$og_title = wp_get_document_title();
	echo '<meta property="og:title" content="' . esc_attr( $og_title ) . '">' . "\n";

	// Description
	$og_description = '';
	if ( is_singular() ) {
		$post = get_post();
		if ( $post ) {
			if ( function_exists( 'is_product' ) && is_product() ) {
				global $product;
				$og_description = wp_strip_all_tags( $product->get_short_description() ?: $product->get_description() );
			} else {
				$og_description = has_excerpt() ? get_the_excerpt() : wp_trim_words( $post->post_content, 30 );
			}
		}
	} else {
		$og_description = get_bloginfo( 'description' );
	}
	if ( $og_description ) {
		echo '<meta property="og:description" content="' . esc_attr( wp_strip_all_tags( $og_description ) ) . '">' . "\n";
	}

	// URL
	$og_url = is_singular() ? get_permalink() : home_url( '/' );
	echo '<meta property="og:url" content="' . esc_url( $og_url ) . '">' . "\n";

	// Site name
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";

	// Image
	$og_image = '';
	if ( is_singular() && has_post_thumbnail() ) {
		$og_image = get_the_post_thumbnail_url( null, 'full' );
	} elseif ( function_exists( 'is_product' ) && is_product() ) {
		global $product;
		$image_id = $product->get_image_id();
		if ( $image_id ) {
			$image = wp_get_attachment_image_src( $image_id, 'full' );
			if ( $image ) {
				$og_image = $image[0];
			}
		}
	}

	if ( ! $og_image ) {
		// Fallback to site logo
		$custom_logo_id = get_theme_mod( 'custom_logo' );
		if ( $custom_logo_id ) {
			$logo = wp_get_attachment_image_src( $custom_logo_id, 'full' );
			if ( $logo ) {
				$og_image = $logo[0];
			}
		}
	}

	if ( $og_image ) {
		echo '<meta property="og:image" content="' . esc_url( $og_image ) . '">' . "\n";
	}

	// Product-specific OG tags
	if ( function_exists( 'is_product' ) && is_product() ) {
		global $product;
		echo '<meta property="product:price:amount" content="' . esc_attr( $product->get_price() ) . '">' . "\n";
		echo '<meta property="product:price:currency" content="SAR">' . "\n";

		if ( $product->is_in_stock() ) {
			echo '<meta property="product:availability" content="in stock">' . "\n";
		}
	}

	// Twitter Card
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	if ( $og_title ) {
		echo '<meta name="twitter:title" content="' . esc_attr( $og_title ) . '">' . "\n";
	}
	if ( $og_description ) {
		echo '<meta name="twitter:description" content="' . esc_attr( wp_strip_all_tags( $og_description ) ) . '">' . "\n";
	}
	if ( $og_image ) {
		echo '<meta name="twitter:image" content="' . esc_url( $og_image ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'ngs_og_tags', 5 );
