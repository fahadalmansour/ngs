<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="rtl">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="neogen - متجرك العربي الأول للبيوت الذكية والإلكترونيات. منتجات ذكية عالية الجودة، دعم فني بالعربي، شحن سريع لجميع مناطق المملكة.">
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <!-- Google Fonts: Tajawal + Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    <header id="masthead" class="site-header">
        <div class="container header-inner">
            <div class="site-branding">
                <?php
                if ( has_custom_logo() ) {
                    the_custom_logo();
                } else {
                    ?>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                        neogen
                    </a>
                    <?php
                }
                ?>
            </div><!-- .site-branding -->

            <nav id="site-navigation" class="main-navigation">
                <?php
                // Fallback menu if no custom menu set
                if ( has_nav_menu( 'primary' ) ) {
                    wp_nav_menu( array(
                        'theme_location' => 'primary',
                        'menu_id'        => 'primary-menu',
                        'container'      => false,
                    ) );
                } else {
                    echo '<ul>';
                    echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">الرئيسية</a></li>';
                    if ( function_exists( 'wc_get_page_permalink' ) ) {
                        echo '<li><a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">المنتجات</a></li>';
                    }
                    echo '<li><a href="' . esc_url( home_url( '/about/' ) ) . '">من نحن</a></li>';
                    echo '<li><a href="' . esc_url( home_url( '/contact/' ) ) . '">تواصل معنا</a></li>';
                    echo '</ul>';
                }
                ?>
            </nav><!-- #site-navigation -->

            <div class="header-actions">
                <?php if ( function_exists( 'wc_get_cart_url' ) ) : ?>
                <a href="<?php echo wc_get_cart_url(); ?>" class="cart-link" title="السلة">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    <span class="cart-count"><?php echo WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?></span>
                </a>
                <?php endif; ?>
                <?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
                <a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" class="btn btn-outline" style="padding: 0.5rem 1rem; font-size: 0.9rem; border-color: #FFFFFF; color: #FFFFFF;">
                    حسابي
                </a>
                <?php endif; ?>
            </div>
        </div>
    </header><!-- #masthead -->

    <div id="content" class="site-content">
