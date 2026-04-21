<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="rtl">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="NGS Smart Home - متجرك العربي للبيوت الذكية. منتجات موثوقة، دعم فني بالعربي، وشحن سريع داخل المملكة.">
    <link rel="profile" href="https://gmpg.org/xfn/11">

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
                        NGS Smart Home
                    </a>
                    <?php
                }
                ?>
            </div>

            <nav id="site-navigation" class="main-navigation">
                <?php
                if ( has_nav_menu( 'primary' ) ) {
                    wp_nav_menu(
                        array(
                            'theme_location' => 'primary',
                            'menu_id'        => 'primary-menu',
                            'container'      => false,
                        )
                    );
                } else {
                    echo '<ul>';
                    echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">الرئيسية</a></li>';
                    if ( function_exists( 'wc_get_page_permalink' ) ) {
                        echo '<li><a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">المنتجات</a></li>';
                    }
                    echo '<li><a href="' . esc_url( home_url( '/about-us/' ) ) . '">من نحن</a></li>';
                    echo '<li><a href="' . esc_url( home_url( '/contact-us/' ) ) . '">تواصل معنا</a></li>';
                    echo '<li><a href="' . esc_url( home_url( '/faq/' ) ) . '">الأسئلة الشائعة</a></li>';
                    echo '<li><a href="' . esc_url( home_url( '/how-it-works/' ) ) . '">كيف نعمل</a></li>';
                    echo '</ul>';
                }
                ?>
            </nav>

            <div class="header-actions">
                <button type="button" class="search-toggle" id="search-toggle" title="بحث" aria-label="بحث">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
                </button>

                <?php if ( function_exists( 'wc_get_cart_url' ) ) : ?>
                <a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="cart-link" title="السلة">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                    <span class="cart-count"><?php echo ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0; ?></span>
                </a>
                <?php endif; ?>

                <?php if ( function_exists( 'wc_get_page_permalink' ) ) : ?>
                <a href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ); ?>" class="btn btn-outline ngs-account-link">
                    حسابي
                </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div id="search-overlay" class="search-overlay" hidden>
        <div class="search-overlay-inner">
            <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="search-form">
                <input type="search" name="s" placeholder="ابحث عن منتج..." autocomplete="off">
                <input type="hidden" name="post_type" value="product">
                <button type="submit" aria-label="بحث">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
                </button>
            </form>
            <button type="button" class="search-close" id="search-close" aria-label="إغلاق">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
            </button>
        </div>
    </div>

    <div id="content" class="site-content">
