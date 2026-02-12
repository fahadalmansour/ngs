<?php
/**
 * The Template for displaying product archives
 *
 * @package neogen
 */

defined( 'ABSPATH' ) || exit;

get_header();

// Get current category
$current_cat = get_queried_object();
$is_category = is_product_category();
?>

<!-- Shop Header -->
<div class="shop-header" style="background: var(--gradient-primary); padding: 3rem 0; color: #fff;">
    <div class="container">
        <?php if ( $is_category && $current_cat ) : ?>
            <h1 style="color: #fff; margin-bottom: 0.5rem;"><?php echo esc_html( $current_cat->name ); ?></h1>
            <?php if ( $current_cat->description ) : ?>
                <p style="opacity: 0.9; max-width: 600px;"><?php echo wp_kses_post( $current_cat->description ); ?></p>
            <?php endif; ?>
        <?php else : ?>
            <h1 style="color: #fff; margin-bottom: 0.5rem;"><?php _e( 'جميع المنتجات', 'neogen-smarthome' ); ?></h1>
            <p style="opacity: 0.9;"><?php _e( 'تصفح مجموعتنا الكاملة من منتجات البيت الذكي', 'neogen-smarthome' ); ?></p>
        <?php endif; ?>
    </div>
</div>

<!-- Breadcrumbs -->
<div class="breadcrumb-container" style="background: var(--color-bg-light); padding: 1rem 0; border-bottom: 1px solid var(--color-border);">
    <div class="container">
        <?php woocommerce_breadcrumb(); ?>
    </div>
</div>

<div class="container" style="padding: 2rem 1rem 4rem;">
    <div class="shop-layout" style="display: grid; grid-template-columns: 250px 1fr; gap: 2rem;">

        <!-- Sidebar Filters -->
        <aside class="shop-sidebar">
            <div class="filter-section" style="background: #fff; padding: 1.5rem; border-radius: 12px; border: 1px solid var(--color-border); margin-bottom: 1.5rem;">
                <h3 style="font-size: 1rem; margin-bottom: 1rem;"><?php _e( 'الفئات', 'neogen-smarthome' ); ?></h3>
                <?php
                $categories = get_terms( array(
                    'taxonomy' => 'product_cat',
                    'hide_empty' => true,
                    'parent' => 0,
                ) );

                if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) :
                ?>
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li style="margin-bottom: 0.5rem;">
                        <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"
                           style="display: block; padding: 0.5rem; border-radius: 6px; <?php echo ! $is_category ? 'background: var(--color-bg-light); font-weight: bold;' : ''; ?>">
                            <?php _e( 'الكل', 'neogen-smarthome' ); ?>
                        </a>
                    </li>
                    <?php foreach ( $categories as $cat ) :
                        $is_active = $is_category && $current_cat && $current_cat->term_id === $cat->term_id;
                    ?>
                    <li style="margin-bottom: 0.5rem;">
                        <a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"
                           style="display: block; padding: 0.5rem; border-radius: 6px; <?php echo $is_active ? 'background: var(--color-bg-light); font-weight: bold; color: var(--color-primary);' : ''; ?>">
                            <?php echo esc_html( $cat->name ); ?>
                            <span style="color: var(--color-text-muted); font-size: 0.85rem;">(<?php echo $cat->count; ?>)</span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>

            <!-- Price Filter -->
            <div class="filter-section" style="background: #fff; padding: 1.5rem; border-radius: 12px; border: 1px solid var(--color-border);">
                <h3 style="font-size: 1rem; margin-bottom: 1rem;"><?php _e( 'فلترة حسب السعر', 'neogen-smarthome' ); ?></h3>
                <?php the_widget( 'WC_Widget_Price_Filter' ); ?>
            </div>
        </aside>

        <!-- Products Grid -->
        <div class="shop-content">
            <!-- Toolbar -->
            <div class="shop-toolbar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding: 1rem; background: #fff; border-radius: 12px; border: 1px solid var(--color-border);">
                <div class="results-count" style="color: var(--color-text-muted);">
                    <?php woocommerce_result_count(); ?>
                </div>
                <div class="shop-ordering">
                    <?php woocommerce_catalog_ordering(); ?>
                </div>
            </div>

            <?php
            if ( woocommerce_product_loop() ) {
                woocommerce_product_loop_start();

                if ( wc_get_loop_prop( 'total' ) ) {
                    while ( have_posts() ) {
                        the_post();
                        wc_get_template_part( 'content', 'product' );
                    }
                }

                woocommerce_product_loop_end();

                // Pagination
                woocommerce_pagination();

            } else {
                do_action( 'woocommerce_no_products_found' );
            }
            ?>
        </div>

    </div>
</div>

<style>
/* Shop Page Specific Styles */
.shop-sidebar .woocommerce-widget-layered-nav-list,
.shop-sidebar .woocommerce ul.product-categories {
    list-style: none !important;
    padding: 0 !important;
}

.shop-sidebar .widget_price_filter .price_slider_wrapper {
    padding: 0.5rem 0;
}

.shop-sidebar .widget_price_filter .ui-slider {
    background: var(--color-border);
    border-radius: 4px;
    height: 6px;
}

.shop-sidebar .widget_price_filter .ui-slider .ui-slider-range {
    background: var(--color-primary);
}

.shop-sidebar .widget_price_filter .ui-slider .ui-slider-handle {
    background: var(--color-primary);
    border: 2px solid #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.woocommerce .products {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)) !important;
    gap: 1.5rem !important;
}

.woocommerce .products .product {
    margin: 0 !important;
    padding: 1.5rem !important;
}

.woocommerce .products .product img {
    border-radius: 8px;
}

.woocommerce nav.woocommerce-pagination {
    margin-top: 2rem;
}

.woocommerce nav.woocommerce-pagination ul {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    border: none;
}

.woocommerce nav.woocommerce-pagination ul li {
    border: none;
}

.woocommerce nav.woocommerce-pagination ul li a,
.woocommerce nav.woocommerce-pagination ul li span {
    background: #fff;
    border: 1px solid var(--color-border);
    border-radius: 8px;
    padding: 0.75rem 1rem;
}

.woocommerce nav.woocommerce-pagination ul li a:hover,
.woocommerce nav.woocommerce-pagination ul li span.current {
    background: var(--color-primary);
    color: #fff;
    border-color: var(--color-primary);
}

@media (max-width: 768px) {
    .shop-layout {
        grid-template-columns: 1fr !important;
    }

    .shop-sidebar {
        order: 2;
    }

    .shop-toolbar {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
}
</style>

<?php
get_footer();
