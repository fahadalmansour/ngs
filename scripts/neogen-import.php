<?php
/**
 * Direct CSV → WC_Product creation, bypassing WC_Product_CSV_Importer.
 *
 * Run via:
 *   wp eval-file /tmp/neogen-import.php --skip-plugins=litespeed-cache --user=1
 *
 * The Woo CSV importer requires a column-mapping handshake that's
 * baked into the AJAX controller; using it from CLI silently produced
 * "Imported: 210 / DB: 0". Easier to parse the CSV ourselves and call
 * the WC_Product setters directly. Works for simple products; lines
 * with Type=variation or external are skipped (none exist in the
 * curated CSV anyway — checked locally).
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$csv = '/tmp/neogen_products.csv';
if ( ! file_exists( $csv ) ) {
    WP_CLI::error( "CSV not found: $csv" );
}

$fh = fopen( $csv, 'r' );
$header = fgetcsv( $fh );
$header[0] = preg_replace( '/^\xEF\xBB\xBF/', '', $header[0] );

WP_CLI::log( 'Header columns: ' . count( $header ) );

$created = 0;
$failed  = 0;
$skipped = 0;
$failures = array();

$row_no = 0;
while ( false !== ( $row = fgetcsv( $fh ) ) ) {
    $row_no++;
    $r = array_combine( $header, $row );

    $type = strtolower( trim( (string) ( $r['Type'] ?? 'simple' ) ) );
    if ( ! in_array( $type, array( 'simple', 'external', 'grouped', '' ), true ) ) {
        $skipped++;
        continue;
    }

    try {
        $product = new WC_Product_Simple();

        $name = trim( (string) ( $r['Name'] ?? '' ) );
        if ( $name === '' ) { $skipped++; continue; }

        $product->set_name( $name );
        $product->set_status( ( ( $r['Published'] ?? '1' ) === '1' ) ? 'publish' : 'draft' );
        $product->set_sku( trim( (string) ( $r['SKU'] ?? '' ) ) );
        $product->set_short_description( (string) ( $r['Short description'] ?? '' ) );
        $product->set_description( (string) ( $r['Description'] ?? '' ) );
        $product->set_catalog_visibility( $r['Visibility in catalog'] === 'hidden' ? 'hidden' : 'visible' );
        $product->set_featured( ( $r['Is featured?'] ?? '0' ) === '1' );

        // Pricing
        $regular = trim( (string) ( $r['Regular price'] ?? '' ) );
        $sale    = trim( (string) ( $r['Sale price'] ?? '' ) );
        if ( $regular !== '' ) { $product->set_regular_price( $regular ); }
        if ( $sale !== '' )    { $product->set_sale_price( $sale ); }

        // Stock
        $manage = $r['Stock'] !== '' && $r['Stock'] !== null;
        $product->set_manage_stock( $manage );
        if ( $manage ) {
            $product->set_stock_quantity( (int) $r['Stock'] );
            if ( ! empty( $r['Low stock amount'] ) ) {
                $product->set_low_stock_amount( (int) $r['Low stock amount'] );
            }
        }
        $product->set_stock_status( ( ( $r['In stock?'] ?? '1' ) === '1' ) ? 'instock' : 'outofstock' );
        $product->set_backorders( $r['Backorders allowed?'] === '1' ? 'yes' : 'no' );
        $product->set_sold_individually( $r['Sold individually?'] === '1' );

        // Tax
        $product->set_tax_status( ! empty( $r['Tax status'] ) ? $r['Tax status'] : 'taxable' );
        if ( ! empty( $r['Tax class'] ) ) { $product->set_tax_class( $r['Tax class'] ); }

        // Dimensions
        if ( ! empty( $r['Weight (kg)'] ) ) { $product->set_weight( $r['Weight (kg)'] ); }
        if ( ! empty( $r['Length (cm)'] ) ) { $product->set_length( $r['Length (cm)'] ); }
        if ( ! empty( $r['Width (cm)'] ) )  { $product->set_width( $r['Width (cm)'] ); }
        if ( ! empty( $r['Height (cm)'] ) ) { $product->set_height( $r['Height (cm)'] ); }

        $product->set_reviews_allowed( ( $r['Allow customer reviews?'] ?? '1' ) === '1' );
        if ( ! empty( $r['Purchase note'] ) ) { $product->set_purchase_note( $r['Purchase note'] ); }
        if ( $r['Position'] !== '' && is_numeric( $r['Position'] ) ) {
            $product->set_menu_order( (int) $r['Position'] );
        }

        // External
        if ( $type === 'external' ) {
            $product = new WC_Product_External( $product->get_id() );
            $product->set_props( array(
                'product_url' => $r['External URL'] ?? '',
                'button_text' => $r['Button text'] ?? '',
            ) );
        }

        // Categories — CSV format: "Smart Home & IoT | أتمتة المنزل الذكي" (semicolon-separated for multiple)
        $cat_raw = (string) ( $r['Categories'] ?? '' );
        if ( $cat_raw !== '' ) {
            $cat_names = array_filter( array_map( 'trim', explode( ',', html_entity_decode( $cat_raw ) ) ) );
            $cat_ids = array();
            foreach ( $cat_names as $cn ) {
                $term = get_term_by( 'name', $cn, 'product_cat' );
                if ( ! $term ) {
                    $created_term = wp_insert_term( $cn, 'product_cat' );
                    if ( ! is_wp_error( $created_term ) ) {
                        $cat_ids[] = (int) $created_term['term_id'];
                    }
                } else {
                    $cat_ids[] = (int) $term->term_id;
                }
            }
            if ( ! empty( $cat_ids ) ) { $product->set_category_ids( $cat_ids ); }
        }

        // Tags
        $tag_raw = (string) ( $r['Tags'] ?? '' );
        if ( $tag_raw !== '' ) {
            $tag_names = array_filter( array_map( 'trim', explode( ',', $tag_raw ) ) );
            $tag_ids = array();
            foreach ( $tag_names as $tn ) {
                $term = get_term_by( 'name', $tn, 'product_tag' );
                if ( ! $term ) {
                    $ct = wp_insert_term( $tn, 'product_tag' );
                    if ( ! is_wp_error( $ct ) ) { $tag_ids[] = (int) $ct['term_id']; }
                } else {
                    $tag_ids[] = (int) $term->term_id;
                }
            }
            if ( ! empty( $tag_ids ) ) { $product->set_tag_ids( $tag_ids ); }
        }

        // Save
        $id = $product->save();
        if ( ! $id ) { throw new Exception( 'save() returned 0' ); }

        // Custom meta from "Meta: _foo" columns
        foreach ( array( '_ng_product_video_url', '_ng_product_video_poster', '_ng_gift_card_brand', '_ng_ar_title' ) as $meta_key ) {
            $col = 'Meta: ' . $meta_key;
            if ( isset( $r[ $col ] ) && $r[ $col ] !== '' ) {
                update_post_meta( $id, $meta_key, $r[ $col ] );
            }
        }

        $created++;
        if ( $created % 25 === 0 ) {
            WP_CLI::log( "  ... $created products created" );
        }
    } catch ( Exception $e ) {
        $failed++;
        if ( count( $failures ) < 5 ) {
            $failures[] = "row $row_no (SKU=" . ( $r['SKU'] ?? '?' ) . "): " . $e->getMessage();
        }
    }
}
fclose( $fh );

WP_CLI::log( '---' );
WP_CLI::log( "Created: $created" );
WP_CLI::log( "Failed:  $failed" );
WP_CLI::log( "Skipped: $skipped" );
if ( $failures ) {
    WP_CLI::log( '---' );
    WP_CLI::log( 'First 5 failures:' );
    foreach ( $failures as $f ) { WP_CLI::log( "  $f" ); }
}

global $wpdb;
$db = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='product'" );
WP_CLI::log( "DB product count: $db" );
