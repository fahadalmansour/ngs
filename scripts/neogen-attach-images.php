<?php
/**
 * Re-walk the curated CSV and attach each product's first image as
 * its featured image. Images live on disk already (restored from the
 * 28-Apr backup), so no HTTP download is needed — we register the
 * attachment from the local upload path.
 *
 * Run with:
 *   wp eval-file /tmp/neogen-attach-images.php --skip-plugins=litespeed-cache --user=1
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

$csv = '/tmp/neogen_products.csv';
if ( ! file_exists( $csv ) ) {
    WP_CLI::error( "CSV not found: $csv" );
}

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

$uploads_dir  = wp_upload_dir()['basedir'];     // /home/fsalmansour/neogen.store/wp-content/uploads
$uploads_url  = wp_upload_dir()['baseurl'];     // https://neogen.store/wp-content/uploads

WP_CLI::log( "uploads_dir: $uploads_dir" );

$fh = fopen( $csv, 'r' );
$header = fgetcsv( $fh );
$header[0] = preg_replace( '/^\xEF\xBB\xBF/', '', $header[0] );

$attached = 0;
$skipped_have = 0;
$missing_file = 0;
$no_image     = 0;
$no_product   = 0;
$missing_examples = array();

while ( false !== ( $row = fgetcsv( $fh ) ) ) {
    $r = array_combine( $header, $row );
    $sku = trim( (string) ( $r['SKU'] ?? '' ) );
    if ( $sku === '' ) { continue; }

    $product_id = wc_get_product_id_by_sku( $sku );
    if ( ! $product_id ) { $no_product++; continue; }

    $product = wc_get_product( $product_id );
    if ( ! $product ) { $no_product++; continue; }

    if ( $product->get_image_id() ) { $skipped_have++; continue; }

    $img_csv = trim( (string) ( $r['Images'] ?? '' ) );
    if ( $img_csv === '' ) { $no_image++; continue; }

    // First URL only (Woo CSV uses comma-separated for gallery; first = featured).
    $first_url = trim( explode( ',', $img_csv )[0] );
    $first_url = explode( '?', $first_url )[0]; // strip ?wsr / ?ver=...

    // Map URL → local path. Both forms appear:
    //   https://neogen.store/wp-content/uploads/<file>
    //   https://neogen.store/wp-content/uploads/2025/07/<file>
    $path = parse_url( $first_url, PHP_URL_PATH );
    if ( ! $path ) { $no_image++; continue; }
    $marker = '/wp-content/uploads/';
    $pos = strpos( $path, $marker );
    if ( $pos === false ) { $no_image++; continue; }
    $rel = substr( $path, $pos + strlen( $marker ) );      // e.g. "adobe.png"
    $abs = $uploads_dir . '/' . $rel;

    if ( ! file_exists( $abs ) ) {
        $missing_file++;
        if ( count( $missing_examples ) < 5 ) {
            $missing_examples[] = "  $sku → $rel";
        }
        continue;
    }

    // Insert as attachment owned by this product.
    $filetype = wp_check_filetype( basename( $abs ) );
    $att_id   = wp_insert_attachment( array(
        'post_mime_type' => $filetype['type'] ?: 'image/jpeg',
        'post_title'     => $product->get_name(),
        'post_content'   => '',
        'post_status'    => 'inherit',
        'guid'           => $uploads_url . '/' . $rel,
    ), $abs, $product_id );

    if ( ! $att_id || is_wp_error( $att_id ) ) { continue; }

    $meta = wp_generate_attachment_metadata( $att_id, $abs );
    wp_update_attachment_metadata( $att_id, $meta );

    $product->set_image_id( $att_id );
    $product->save();
    $attached++;

    if ( $attached % 25 === 0 ) {
        WP_CLI::log( "  ... $attached attached" );
    }
}
fclose( $fh );

WP_CLI::log( '---' );
WP_CLI::log( "Attached now:        $attached" );
WP_CLI::log( "Already had image:   $skipped_have" );
WP_CLI::log( "Local file missing:  $missing_file" );
WP_CLI::log( "No image URL in row: $no_image" );
WP_CLI::log( "Product not found:   $no_product" );

if ( $missing_examples ) {
    WP_CLI::log( '---' );
    WP_CLI::log( 'Missing-file examples:' );
    foreach ( $missing_examples as $line ) { WP_CLI::log( $line ); }
}
