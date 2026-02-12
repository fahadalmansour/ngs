<?php
/**
 * Plugin Name: NGS Product Tabs Shortcode
 * Description: [ngs_product_tabs] shortcode for category tabs with products
 * Version: 1.0
 * Author: NGS
 */

if (!defined('ABSPATH')) exit;

// Register shortcode
add_shortcode('ngs_product_tabs', 'ngs_product_tabs_shortcode');

function ngs_product_tabs_shortcode($atts) {
    $atts = shortcode_atts(array(
        'categories' => 'nas-servers,sensors,services,lighting,climate,security,cameras-security,network',
        'products_per_tab' => 8,
    ), $atts);

    $categories = explode(',', $atts['categories']);

    ob_start();
    ?>
    <div class="ngs-product-tabs-container">
        <div class="ngs-tabs-header">
            <?php
            $first = true;
            foreach ($categories as $cat_slug) {
                $cat_slug = trim($cat_slug);
                $category = get_term_by('slug', $cat_slug, 'product_cat');
                if ($category) {
                    $active = $first ? 'active' : '';
                    echo "<button class='ngs-tab-btn {$active}' data-tab='{$cat_slug}'>{$category->name}</button>";
                    $first = false;
                }
            }
            ?>
        </div>

        <div class="ngs-tabs-content">
            <?php
            $first = true;
            foreach ($categories as $cat_slug) {
                $cat_slug = trim($cat_slug);
                $category = get_term_by('slug', $cat_slug, 'product_cat');
                if (!$category) continue;

                $active = $first ? 'active' : '';
                echo "<div class='ngs-tab-panel {$active}' data-panel='{$cat_slug}'>";

                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => intval($atts['products_per_tab']),
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'product_cat',
                            'field' => 'slug',
                            'terms' => $cat_slug,
                        ),
                    ),
                );

                $products = new WP_Query($args);

                if ($products->have_posts()) {
                    woocommerce_product_loop_start();
                    while ($products->have_posts()) {
                        $products->the_post();
                        wc_get_template_part('content', 'product');
                    }
                    woocommerce_product_loop_end();
                } else {
                    echo '<p class="ngs-no-products">لا توجد منتجات في هذه الفئة</p>';
                }

                wp_reset_postdata();
                echo '</div>';
                $first = false;
            }
            ?>
        </div>
    </div>

    <style>
    .ngs-product-tabs-container {
        margin: 30px 0;
    }
    .ngs-tabs-header {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 10px;
    }
    .ngs-tab-btn {
        padding: 12px 24px;
        border: none;
        background: #1a1a2e;
        color: #fff;
        border-radius: 25px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    .ngs-tab-btn:hover,
    .ngs-tab-btn.active {
        background: #10b981;
    }
    .ngs-tab-panel {
        display: none;
    }
    .ngs-tab-panel.active {
        display: block;
    }
    .ngs-no-products {
        text-align: center;
        padding: 40px;
        color: #666;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.ngs-tab-btn');
        const panels = document.querySelectorAll('.ngs-tab-panel');

        buttons.forEach(btn => {
            btn.addEventListener('click', function() {
                const tab = this.dataset.tab;

                buttons.forEach(b => b.classList.remove('active'));
                panels.forEach(p => p.classList.remove('active'));

                this.classList.add('active');
                document.querySelector(`.ngs-tab-panel[data-panel="${tab}"]`).classList.add('active');
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
