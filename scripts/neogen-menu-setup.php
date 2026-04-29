<?php
/**
 * Two passes:
 *   1. Rename product-category slugs from URL-encoded Arabic to clean
 *      ASCII matching front-page.php's $copy_map keys (smart-home,
 *      gaming, homelab, networking, hardware, gift-cards, …). Display
 *      names get HTML-entities decoded so "&amp;" becomes "&".
 *   2. Build a primary nav menu and assign it to menu_1 + menu_mobile.
 *      Includes Gift Cards per the latest correction.
 *
 * Run with:
 *   wp eval-file /tmp/neogen-menu-setup.php --skip-plugins=litespeed-cache --user=1
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// ---- Pass 1: Clean category slugs + display names ----

$slug_map = array(
    'Smart Home & IoT | أتمتة المنزل الذكي'           => 'smart-home',
    'Gaming | ألعاب'                                   => 'gaming',
    'Homelab | هوم لاب'                                => 'homelab',
    'Networking | شبكات واتصالات'                      => 'networking',
    'Hardware | أجهزة PC'                              => 'hardware',
    'Gift Cards & Software Keys | بطاقات رقمية ومفاتيح' => 'gift-cards',
    'Drones & Robotics | درونز وروبوتات'              => 'drones-robotics',
    'Cables & Adapters | كابلات ومحولات'              => 'cables-adapters',
    '3D Printing & CNC | طباعة ثلاثية الأبعاد'         => '3d-printing-cnc',
    'Security & Surveillance | أمن ومراقبة'           => 'security-surveillance',
    'Accessories & Lifestyle | إكسسوارات'             => 'accessories-lifestyle',
    'Enthusiasts & Gamers | هواة التقنية'             => 'enthusiasts-gamers',
);

$renamed = 0;
foreach ( $slug_map as $name => $slug ) {
    // Try HTML-encoded form first (since that's what's stored).
    $encoded = htmlspecialchars( $name, ENT_QUOTES );
    $term = get_term_by( 'name', $encoded, 'product_cat' );
    if ( ! $term ) {
        $term = get_term_by( 'name', $name, 'product_cat' );
    }
    if ( ! $term ) {
        WP_CLI::log( "  [skip] not found: $name" );
        continue;
    }

    $update = array(
        'slug' => $slug,
        'name' => html_entity_decode( $term->name ),
    );
    $r = wp_update_term( $term->term_id, 'product_cat', $update );
    if ( is_wp_error( $r ) ) {
        WP_CLI::log( "  [err]  $slug: " . $r->get_error_message() );
    } else {
        $renamed++;
    }
}
WP_CLI::log( "Renamed $renamed category slugs." );

// ---- Pass 2: Primary nav menu with categories + key pages ----

$menu_name = 'Primary';
$existing  = wp_get_nav_menu_object( $menu_name );
if ( $existing ) {
    $menu_id = (int) $existing->term_id;
} else {
    $menu_id = wp_create_nav_menu( $menu_name );
    if ( is_wp_error( $menu_id ) ) {
        WP_CLI::error( 'Could not create Primary menu: ' . $menu_id->get_error_message() );
    }
}
WP_CLI::log( "Primary menu id: $menu_id" );

// Wipe any existing items so this is idempotent.
$existing_items = wp_get_nav_menu_items( $menu_id );
if ( $existing_items ) {
    foreach ( $existing_items as $item ) {
        wp_delete_post( $item->ID, true );
    }
}

// Build the menu in order. AR labels for an Arabic-first site.
$home_id    = (int) get_option( 'page_on_front' );
$shop_id    = (int) wc_get_page_id( 'shop' );
$cart_id    = (int) wc_get_page_id( 'cart' );
$contact_id = 0;
foreach ( get_posts( array( 'post_type' => 'page', 'name' => 'contact', 'posts_per_page' => 1 ) ) as $p ) {
    $contact_id = (int) $p->ID;
}

$items = array();

if ( $home_id ) {
    $items[] = array( 'type' => 'page', 'id' => $home_id, 'title' => 'الرئيسية' );
} else {
    $items[] = array( 'type' => 'custom', 'url' => home_url( '/' ), 'title' => 'الرئيسية' );
}

if ( $shop_id > 0 ) {
    $items[] = array( 'type' => 'page', 'id' => $shop_id, 'title' => 'المتجر' );
}

// 6 product categories. Order matches the homepage rack + gift-cards last.
$cats_in_order = array(
    'smart-home'  => 'البيت الذكي',
    'gaming'      => 'الألعاب',
    'homelab'     => 'هوم لاب',
    'networking'  => 'الشبكات',
    'hardware'    => 'الأجهزة',
    'gift-cards'  => 'بطاقات رقمية',
);
foreach ( $cats_in_order as $slug => $label ) {
    $term = get_term_by( 'slug', $slug, 'product_cat' );
    if ( $term ) {
        $items[] = array( 'type' => 'taxonomy', 'tax' => 'product_cat', 'id' => $term->term_id, 'title' => $label );
    } else {
        WP_CLI::log( "  [warn] category slug '$slug' not found — skipping menu item" );
    }
}

if ( $contact_id ) {
    $items[] = array( 'type' => 'page', 'id' => $contact_id, 'title' => 'تواصل' );
}

if ( $cart_id > 0 ) {
    $items[] = array( 'type' => 'page', 'id' => $cart_id, 'title' => 'السلة' );
}

$position = 0;
foreach ( $items as $i ) {
    $position += 10;
    $args = array(
        'menu-item-status'   => 'publish',
        'menu-item-position' => $position,
        'menu-item-title'    => $i['title'],
    );
    if ( $i['type'] === 'page' ) {
        $args['menu-item-object']    = 'page';
        $args['menu-item-object-id'] = $i['id'];
        $args['menu-item-type']      = 'post_type';
    } elseif ( $i['type'] === 'taxonomy' ) {
        $args['menu-item-object']    = $i['tax'];
        $args['menu-item-object-id'] = $i['id'];
        $args['menu-item-type']      = 'taxonomy';
    } else {
        $args['menu-item-url']  = $i['url'];
        $args['menu-item-type'] = 'custom';
    }
    wp_update_nav_menu_item( $menu_id, 0, $args );
}

// Assign to menu_1 (Blocksy primary header) and menu_mobile.
$locations = array_merge(
    (array) get_theme_mod( 'nav_menu_locations', array() ),
    array(
        'menu_1'      => $menu_id,
        'menu_mobile' => $menu_id,
    )
);
set_theme_mod( 'nav_menu_locations', $locations );

WP_CLI::log( "Primary menu (#$menu_id) built with " . count( $items ) . ' items, assigned to menu_1 + menu_mobile.' );

// Verify
$assigned = (array) get_theme_mod( 'nav_menu_locations', array() );
WP_CLI::log( '---' );
WP_CLI::log( 'menu_1 → ' . ( $assigned['menu_1'] ?? '(unassigned)' ) );
WP_CLI::log( 'menu_mobile → ' . ( $assigned['menu_mobile'] ?? '(unassigned)' ) );
WP_CLI::log( '---' );
foreach ( wp_get_nav_menu_items( $menu_id ) as $it ) {
    WP_CLI::log( '  - ' . $it->title . '  →  ' . $it->url );
}
