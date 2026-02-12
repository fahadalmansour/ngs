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
                <!-- Search Toggle -->
                <button type="button" class="search-toggle" id="search-toggle" title="بحث" aria-label="بحث">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.35-4.35"></path></svg>
                </button>

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

    <!-- Search Overlay -->
    <div id="search-overlay" class="search-overlay" style="display: none;">
        <div class="search-overlay-inner">
            <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="search-form">
                <input type="search" name="s" placeholder="ابحث عن منتج..." autocomplete="off" autofocus>
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

    <style>
    /* Search Toggle Button */
    .search-toggle {
        background: transparent;
        border: none;
        color: #fff;
        cursor: pointer;
        padding: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: opacity 0.2s;
    }
    .search-toggle:hover {
        opacity: 0.8;
    }

    /* Search Overlay */
    .search-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.95);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.2s ease;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .search-overlay-inner {
        width: 100%;
        max-width: 600px;
        padding: 2rem;
        position: relative;
    }
    .search-overlay .search-form {
        display: flex;
        gap: 1rem;
        background: #fff;
        border-radius: 50px;
        padding: 0.5rem 0.5rem 0.5rem 1.5rem;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }
    .search-overlay .search-form input[type="search"] {
        flex: 1;
        border: none;
        outline: none;
        font-size: 1.2rem;
        background: transparent;
        font-family: inherit;
    }
    .search-overlay .search-form button {
        background: var(--color-primary, #1F1EFB);
        border: none;
        color: #fff;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }
    .search-overlay .search-form button:hover {
        background: var(--color-secondary, #0D2175);
    }
    .search-close {
        position: absolute;
        top: -3rem;
        left: 50%;
        transform: translateX(-50%);
        background: transparent;
        border: none;
        color: #fff;
        cursor: pointer;
        opacity: 0.7;
        transition: opacity 0.2s;
    }
    .search-close:hover {
        opacity: 1;
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var searchToggle = document.getElementById('search-toggle');
        var searchOverlay = document.getElementById('search-overlay');
        var searchClose = document.getElementById('search-close');
        var searchInput = searchOverlay ? searchOverlay.querySelector('input[type="search"]') : null;

        if (searchToggle && searchOverlay) {
            searchToggle.addEventListener('click', function() {
                searchOverlay.style.display = 'flex';
                if (searchInput) searchInput.focus();
            });

            searchClose.addEventListener('click', function() {
                searchOverlay.style.display = 'none';
            });

            searchOverlay.addEventListener('click', function(e) {
                if (e.target === searchOverlay) {
                    searchOverlay.style.display = 'none';
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && searchOverlay.style.display === 'flex') {
                    searchOverlay.style.display = 'none';
                }
            });
        }
    });
    </script>

    <div id="content" class="site-content">
