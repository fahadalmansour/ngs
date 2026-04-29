<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Skip to content link for accessibility -->
<a class="ngs-skip-link" href="#main"><?php esc_html_e('Skip to content', 'ngs-designsystem'); ?></a>

<div id="page" class="ngs-site">
    <header class="ngs-header" role="banner">
        <div class="ngs-header__container">
            <!-- Logo -->
            <div class="ngs-header__logo">
                <?php if (has_custom_logo()) : ?>
                    <?php the_custom_logo(); ?>
                <?php else : ?>
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="ngs-header__logo-text" aria-label="<?php bloginfo('name'); ?> - <?php esc_attr_e('Homepage', 'ngs-designsystem'); ?>">
                        <?php bloginfo('name'); ?>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Desktop Navigation -->
            <nav class="ngs-nav" role="navigation" aria-label="<?php esc_attr_e('Main navigation', 'ngs-designsystem'); ?>">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'menu_class'     => 'ngs-nav__menu',
                    'container'      => false,
                    'fallback_cb'    => false,
                ));
                ?>
            </nav>

            <!-- Header Actions -->
            <div class="ngs-header__actions">
                <!-- Search toggle button -->
                <button class="ngs-header__action ngs-header__search-toggle"
                        aria-label="<?php esc_attr_e('Open search', 'ngs-designsystem'); ?>"
                        aria-expanded="false"
                        aria-controls="search-overlay"
                        data-search-toggle>
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M9 17A8 8 0 1 0 9 1a8 8 0 0 0 0 16zM19 19l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>

                <!-- Cart link with badge -->
                <?php if (function_exists('WC')) : ?>
                    <?php
                    $cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
                    $cart_count_display = $cart_count > 9 ? '9+' : $cart_count;
                    ?>
                    <a href="<?php echo esc_url(wc_get_cart_url()); ?>"
                       class="ngs-header__action ngs-header__cart"
                       aria-label="<?php echo esc_attr(sprintf(__('Shopping cart: %d items', 'ngs-designsystem'), $cart_count)); ?>">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L19 6H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <circle cx="8" cy="18" r="1" fill="currentColor"/>
                            <circle cx="16" cy="18" r="1" fill="currentColor"/>
                        </svg>
                        <?php if ($cart_count > 0) : ?>
                            <span class="ngs-header__cart-badge" data-cart-count><?php echo esc_html($cart_count_display); ?></span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <!-- Language toggle button -->
                <button class="ngs-header__action ngs-header__lang-toggle"
                        aria-label="<?php esc_attr_e('Switch language', 'ngs-designsystem'); ?>"
                        data-lang-toggle>
                    <span class="ngs-header__lang-text"><?php echo is_rtl() ? 'EN' : 'ع'; ?></span>
                </button>
            </div>

            <!-- Mobile hamburger button -->
            <button class="ngs-hamburger"
                    aria-label="<?php esc_attr_e('Menu', 'ngs-designsystem'); ?>"
                    aria-expanded="false"
                    aria-controls="mobile-nav"
                    data-mobile-toggle>
                <span class="ngs-hamburger__line"></span>
                <span class="ngs-hamburger__line"></span>
                <span class="ngs-hamburger__line"></span>
            </button>
        </div>
    </header>

    <!-- Mobile Navigation Drawer -->
    <div class="ngs-mobile-nav-overlay" id="mobile-nav-overlay" data-mobile-overlay></div>
    <nav class="ngs-mobile-nav" id="mobile-nav" role="navigation" aria-label="<?php esc_attr_e('Mobile navigation', 'ngs-designsystem'); ?>">
        <div class="ngs-mobile-nav__header">
            <button class="ngs-mobile-nav__close"
                    aria-label="<?php esc_attr_e('Close menu', 'ngs-designsystem'); ?>"
                    data-mobile-close>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        <div class="ngs-mobile-nav__content">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'primary',
                'menu_class'     => 'ngs-mobile-nav__menu',
                'container'      => false,
                'fallback_cb'    => false,
            ));
            ?>

            <!-- Mobile search -->
            <div class="ngs-mobile-nav__search">
                <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                    <input type="search"
                           name="s"
                           placeholder="<?php esc_attr_e('Search products...', 'ngs-designsystem'); ?>"
                           autocomplete="off"
                           aria-label="<?php esc_attr_e('Search products', 'ngs-designsystem'); ?>">
                    <?php if (function_exists('WC')) : ?>
                        <input type="hidden" name="post_type" value="product">
                    <?php endif; ?>
                    <button type="submit" aria-label="<?php esc_attr_e('Search', 'ngs-designsystem'); ?>">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M9 17A8 8 0 1 0 9 1a8 8 0 0 0 0 16zM19 19l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Search Overlay -->
    <div class="ngs-search-overlay" id="search-overlay" role="search">
        <div class="ngs-search-overlay__inner">
            <button class="ngs-search-overlay__close"
                    aria-label="<?php esc_attr_e('Close search', 'ngs-designsystem'); ?>"
                    data-search-close>
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="ngs-search-overlay__form">
                <div class="ngs-search-overlay__input-wrapper">
                    <svg width="24" height="24" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" class="ngs-search-overlay__icon">
                        <path d="M9 17A8 8 0 1 0 9 1a8 8 0 0 0 0 16zM19 19l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <input type="search"
                           name="s"
                           placeholder="<?php esc_attr_e('Search products...', 'ngs-designsystem'); ?>"
                           autocomplete="off"
                           class="ngs-search-overlay__input"
                           aria-label="<?php esc_attr_e('Search products', 'ngs-designsystem'); ?>"
                           autofocus>
                    <?php if (function_exists('WC')) : ?>
                        <input type="hidden" name="post_type" value="product">
                    <?php endif; ?>
                    <button type="submit" class="ngs-search-overlay__submit" aria-label="<?php esc_attr_e('Search', 'ngs-designsystem'); ?>">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast container for notifications -->
    <div class="ngs-toast-container" aria-live="assertive" aria-atomic="true"></div>

    <!-- Screen reader live region for cart updates -->
    <div class="ngs-sr-only" aria-live="polite" aria-atomic="true" id="ngs-cart-status"></div>

    <div id="content" class="ngs-site-content">
