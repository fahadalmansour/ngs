<?php
/**
 * Plugin Name: NGS Category Tabs
 * Description: Adds category tabs to WooCommerce shop page
 * Version: 1.0
 * Author: NGS
 */

if (!defined('ABSPATH')) exit;

// Add tabs before shop loop
add_action('woocommerce_before_shop_loop', 'ngs_category_tabs', 15);

function ngs_category_tabs() {
    // Only show on main shop page
    if (!is_shop() && !is_product_category()) return;

    // Main categories to show as tabs
    $main_categories = array(
        'all' => 'الكل',
        'nas-servers' => 'NAS & التخزين',
        'sensors' => 'الحساسات',
        'services' => 'الخدمات',
        'lighting' => 'الإضاءة',
        'climate' => 'التكييف',
        'security' => 'الأمان',
        'network' => 'الشبكات',
        'ha-hardware' => 'Home Assistant',
        'switches-plugs' => 'المفاتيح',
        'cameras-security' => 'الكاميرات',
        'hubs-bridges' => 'الهبات',
    );

    $current = is_product_category() ? get_queried_object()->slug : 'all';

    echo '<div class="ngs-category-tabs">';
    echo '<ul class="ngs-tabs-list">';

    foreach ($main_categories as $slug => $name) {
        $active = ($current === $slug) ? 'active' : '';
        $url = ($slug === 'all') ? wc_get_page_permalink('shop') : get_term_link($slug, 'product_cat');

        if (!is_wp_error($url)) {
            echo "<li class='ngs-tab {$active}'><a href='{$url}'>{$name}</a></li>";
        }
    }

    echo '</ul>';
    echo '</div>';
}

// Add CSS
add_action('wp_head', 'ngs_category_tabs_css');

function ngs_category_tabs_css() {
    ?>
    <style>
    .ngs-category-tabs {
        margin: 20px 0;
        padding: 0;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .ngs-tabs-list {
        display: flex;
        gap: 8px;
        list-style: none;
        margin: 0;
        padding: 10px 0;
        flex-wrap: nowrap;
        min-width: max-content;
    }
    .ngs-tab {
        margin: 0;
    }
    .ngs-tab a {
        display: block;
        padding: 10px 20px;
        background: #1a1a2e;
        color: #fff;
        text-decoration: none;
        border-radius: 25px;
        font-size: 14px;
        white-space: nowrap;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .ngs-tab a:hover {
        background: #10b981;
        color: #fff;
    }
    .ngs-tab.active a {
        background: #10b981;
        color: #fff;
        border-color: #10b981;
    }
    @media (max-width: 768px) {
        .ngs-tabs-list {
            padding: 10px 15px;
        }
        .ngs-tab a {
            padding: 8px 16px;
            font-size: 13px;
        }
    }
    </style>
    <?php
}
