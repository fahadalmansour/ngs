    </div><!-- #content -->

    <footer class="ngs-footer" role="contentinfo">
        <div class="ngs-footer__main">
            <div class="ngs-footer__container">
                <div class="ngs-footer__grid">
                    <!-- About Column -->
                    <div class="ngs-footer__col ngs-footer__col--about">
                        <div class="ngs-footer__brand">
                            <?php if (has_custom_logo()) : ?>
                                <?php the_custom_logo(); ?>
                            <?php else : ?>
                                <span class="ngs-footer__logo-text"><?php bloginfo('name'); ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="ngs-footer__tagline">
                            <?php
                            $tagline = get_theme_mod('ngs_footer_tagline', __('أجهزة بيت ذكي جاهزة للاستخدام في السعودية', 'ngs-designsystem'));
                            echo esc_html($tagline);
                            ?>
                        </p>
                        <div class="ngs-footer__social" role="navigation" aria-label="<?php esc_attr_e('Social media links', 'ngs-designsystem'); ?>">
                            <?php
                            $social_links = array(
                                'instagram' => array(
                                    'label' => __('Instagram', 'ngs-designsystem'),
                                    'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 2c2.717 0 3.056.01 4.122.06 1.065.05 1.79.217 2.428.465.66.254 1.216.598 1.772 1.153.509.5.902 1.105 1.153 1.772.247.637.415 1.363.465 2.428.047 1.066.06 1.405.06 4.122 0 2.717-.01 3.056-.06 4.122-.05 1.065-.218 1.79-.465 2.428a4.883 4.883 0 01-1.153 1.772c-.5.509-1.105.902-1.772 1.153-.637.247-1.363.415-2.428.465-1.066.047-1.405.06-4.122.06-2.717 0-3.056-.01-4.122-.06-1.065-.05-1.79-.218-2.428-.465a4.89 4.89 0 01-1.772-1.153 4.904 4.904 0 01-1.153-1.772c-.248-.637-.415-1.363-.465-2.428C2.013 15.056 2 14.717 2 12c0-2.717.01-3.056.06-4.122.05-1.066.217-1.79.465-2.428a4.88 4.88 0 011.153-1.772A4.897 4.897 0 015.45 2.525c.638-.248 1.362-.415 2.428-.465C8.944 2.013 9.283 2 12 2zm0 5a5 5 0 100 10 5 5 0 000-10zm6.5-.25a1.25 1.25 0 10-2.5 0 1.25 1.25 0 002.5 0zM12 9a3 3 0 110 6 3 3 0 010-6z"/></svg>',
                                ),
                                'twitter' => array(
                                    'label' => __('X (Twitter)', 'ngs-designsystem'),
                                    'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
                                ),
                                'youtube' => array(
                                    'label' => __('YouTube', 'ngs-designsystem'),
                                    'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
                                ),
                                'tiktok' => array(
                                    'label' => __('TikTok', 'ngs-designsystem'),
                                    'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-1-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z"/></svg>',
                                ),
                            );

                            foreach ($social_links as $platform => $data) {
                                $url = get_theme_mod('ngs_social_' . $platform);
                                if ($url) :
                            ?>
                                <a href="<?php echo esc_url($url); ?>"
                                   class="ngs-footer__social-link"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   aria-label="<?php echo esc_attr($data['label']); ?>">
                                    <?php echo $data['icon']; ?>
                                </a>
                            <?php
                                endif;
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Shop Column -->
                    <div class="ngs-footer__col">
                        <h3 class="ngs-footer__heading"><?php esc_html_e('Shop', 'ngs-designsystem'); ?></h3>
                        <nav class="ngs-footer__nav" aria-label="<?php esc_attr_e('Shop navigation', 'ngs-designsystem'); ?>">
                            <ul class="ngs-footer__menu">
                                <li><a href="<?php echo esc_url(home_url('/shop/ecosystem/')); ?>"><?php esc_html_e('By Ecosystem', 'ngs-designsystem'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/shop/protocol/')); ?>"><?php esc_html_e('By Protocol', 'ngs-designsystem'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/shop/brands/')); ?>"><?php esc_html_e('By Brand', 'ngs-designsystem'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/shop/bundles/')); ?>"><?php esc_html_e('Bundles', 'ngs-designsystem'); ?></a></li>
                            </ul>
                        </nav>
                    </div>

                    <!-- Support Column -->
                    <div class="ngs-footer__col">
                        <h3 class="ngs-footer__heading"><?php esc_html_e('Support', 'ngs-designsystem'); ?></h3>
                        <nav class="ngs-footer__nav" aria-label="<?php esc_attr_e('Support navigation', 'ngs-designsystem'); ?>">
                            <ul class="ngs-footer__menu">
                                <li><a href="<?php echo esc_url(home_url('/knowledge-base/')); ?>"><?php esc_html_e('Knowledge Base', 'ngs-designsystem'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/services/')); ?>"><?php esc_html_e('Installation Services', 'ngs-designsystem'); ?></a></li>
                                <?php
                                $whatsapp = get_theme_mod('ngs_whatsapp_number');
                                if ($whatsapp) :
                                    $whatsapp_url = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $whatsapp);
                                ?>
                                <li><a href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('WhatsApp Support', 'ngs-designsystem'); ?></a></li>
                                <?php endif; ?>
                                <li><a href="<?php echo esc_url(home_url('/contact/')); ?>"><?php esc_html_e('Contact Us', 'ngs-designsystem'); ?></a></li>
                            </ul>
                        </nav>
                    </div>

                    <!-- Legal Column -->
                    <div class="ngs-footer__col">
                        <h3 class="ngs-footer__heading"><?php esc_html_e('Legal', 'ngs-designsystem'); ?></h3>
                        <nav class="ngs-footer__nav" aria-label="<?php esc_attr_e('Legal navigation', 'ngs-designsystem'); ?>">
                            <ul class="ngs-footer__menu">
                                <li><a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>"><?php esc_html_e('Privacy Policy', 'ngs-designsystem'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/return-policy/')); ?>"><?php esc_html_e('Return Policy', 'ngs-designsystem'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/terms-of-service/')); ?>"><?php esc_html_e('Terms of Service', 'ngs-designsystem'); ?></a></li>
                                <li><a href="<?php echo esc_url(home_url('/warranty/')); ?>"><?php esc_html_e('Warranty', 'ngs-designsystem'); ?></a></li>
                                <?php
                                $maroof_url = get_theme_mod('ngs_maroof_url');
                                if ($maroof_url) :
                                ?>
                                <li><a href="<?php echo esc_url($maroof_url); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Maroof Profile', 'ngs-designsystem'); ?></a></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="ngs-footer__bottom">
            <div class="ngs-footer__container">
                <div class="ngs-footer__bottom-content">
                    <div class="ngs-footer__copyright">
                        <?php
                        $cr_number = get_theme_mod('ngs_cr_number');
                        $vat_number = get_theme_mod('ngs_vat_number');
                        $year = date('Y');
                        ?>
                        <p>
                            <?php
                            printf(
                                /* translators: 1: Year, 2: Site name, 3: CR number, 4: VAT number */
                                esc_html__('© %1$s %2$s', 'ngs-designsystem'),
                                esc_html($year),
                                esc_html(get_bloginfo('name'))
                            );
                            if ($cr_number) {
                                echo ' | ' . esc_html(sprintf(__('CR: %s', 'ngs-designsystem'), $cr_number));
                            }
                            if ($vat_number) {
                                echo ' | ' . esc_html(sprintf(__('VAT: %s', 'ngs-designsystem'), $vat_number));
                            }
                            ?>
                        </p>
                    </div>

                    <div class="ngs-footer__payment-badges" role="img" aria-label="<?php esc_attr_e('Accepted payment methods', 'ngs-designsystem'); ?>">
                        <span class="ngs-footer__payment-badge">Mada</span>
                        <span class="ngs-footer__payment-badge">Tamara</span>
                        <span class="ngs-footer__payment-badge">Tabby</span>
                        <span class="ngs-footer__payment-badge">Maroof</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
