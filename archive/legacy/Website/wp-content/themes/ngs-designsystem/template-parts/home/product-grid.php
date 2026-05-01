<?php
/**
 * Featured Products Grid Section
 *
 * @package NGS_DesignSystem
 */
?>

<section class="ngs-product-grid-section" data-ngs-section="product-grid">
    <div class="ngs-product-grid-section__container">
        <div class="ngs-product-grid-section__header">
            <div class="ngs-product-grid-section__title-wrapper">
                <h2 class="ngs-product-grid-section__title">
                    <?php esc_html_e('الأجهزة الأكثر شهرة', 'ngs-designsystem'); ?>
                </h2>

                <!-- Filter Tabs -->
                <div class="ngs-product-filter" role="tablist" aria-label="<?php esc_attr_e('Product filters', 'ngs-designsystem'); ?>">
                    <button class="ngs-product-filter__tab ngs-product-filter__tab--active"
                            role="tab"
                            aria-selected="true"
                            data-filter="all">
                        <?php esc_html_e('الكل', 'ngs-designsystem'); ?>
                    </button>
                    <button class="ngs-product-filter__tab"
                            role="tab"
                            aria-selected="false"
                            data-filter="zigbee">
                        Zigbee
                    </button>
                    <button class="ngs-product-filter__tab"
                            role="tab"
                            aria-selected="false"
                            data-filter="zwave">
                        Z-Wave
                    </button>
                    <button class="ngs-product-filter__tab"
                            role="tab"
                            aria-selected="false"
                            data-filter="wifi">
                        WiFi
                    </button>
                </div>
            </div>

            <a href="<?php echo esc_url(function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/')); ?>"
               class="ngs-product-grid-section__view-all">
                <?php esc_html_e('عرض الكل', 'ngs-designsystem'); ?>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M4 10h12m-6-6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>

        <?php
        // Query for featured products
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => 8,
            'post_status' => 'publish',
            'orderby' => 'menu_order',
            'order' => 'ASC',
        );

        // Prioritize featured products
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_visibility',
                'field'    => 'name',
                'terms'    => 'featured',
            ),
        );

        $products_query = new WP_Query($args);

        if ($products_query->have_posts()) :
        ?>
            <div class="ngs-product-grid" role="list">
                <?php
                $index = 0;
                while ($products_query->have_posts()) :
                    $products_query->the_post();
                    global $product;

                    // Set delay for stagger animation
                    set_query_var('ngs_animate_delay', $index * 50);
                    set_query_var('product', $product);

                    // Include product card template
                    get_template_part('template-parts/components/product-card');

                    $index++;
                endwhile;
                wp_reset_postdata();
                ?>
            </div>
        <?php else : ?>
            <div class="ngs-product-grid-section__no-products">
                <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <rect x="8" y="16" width="48" height="40" rx="4" stroke="currentColor" stroke-width="3"/>
                    <path d="M20 28h24M20 36h16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="38" cy="38" r="3" fill="currentColor"/>
                </svg>
                <p><?php esc_html_e('No products available at the moment. Check back soon!', 'ngs-designsystem'); ?></p>
                <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="ngs-btn ngs-btn--outline">
                    <?php esc_html_e('Contact Us', 'ngs-designsystem'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>
