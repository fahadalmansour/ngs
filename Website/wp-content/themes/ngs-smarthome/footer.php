    </div><!-- #content -->

    <footer id="colophon" class="site-footer">
        <div class="container footer-grid">
            <div class="footer-widget">
                <h4>عن neogen</h4>
                <p>متجرك العربي الأول للبيوت الذكية والإلكترونيات. نساعدك تحول بيتك لبيت ذكي بأقل التكاليف مع دعم فني متخصص بالعربي.</p>
                <div style="margin-top: 1.5rem;">
                    <!-- Social Icons -->
                    <a href="https://twitter.com/neogenstore" style="margin-left: 1rem; font-size: 1.2rem;" title="X (Twitter)">𝕏</a>
                    <a href="https://instagram.com/neogenstore" style="margin-left: 1rem; font-size: 1.2rem;" title="Instagram">📷</a>
                    <a href="https://youtube.com/@neogenstore" style="margin-left: 1rem; font-size: 1.2rem;" title="YouTube">▶️</a>
                    <a href="https://tiktok.com/@neogenstore" style="font-size: 1.2rem;" title="TikTok">🎵</a>
                </div>
            </div>

            <div class="footer-widget">
                <h4>روابط سريعة</h4>
                <nav class="footer-links">
                    <?php
                    if ( has_nav_menu( 'footer' ) ) {
                        wp_nav_menu( array(
                            'theme_location' => 'footer',
                            'container'      => false,
                            'depth'          => 1,
                        ) );
                    } else {
                        echo '<ul>';
                        echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">الرئيسية</a></li>';
                        if ( function_exists( 'wc_get_page_permalink' ) ) {
                            echo '<li><a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">المنتجات</a></li>';
                        }
                        echo '<li><a href="' . esc_url( home_url( '/about/' ) ) . '">من نحن</a></li>';
                        echo '<li><a href="' . esc_url( home_url( '/contact/' ) ) . '">تواصل معنا</a></li>';
                        echo '<li><a href="' . esc_url( home_url( '/faq/' ) ) . '">الأسئلة الشائعة</a></li>';
                        echo '</ul>';
                    }
                    ?>
                </nav>
            </div>

            <div class="footer-widget">
                <h4>السياسات</h4>
                <nav class="footer-links">
                    <ul>
                        <li><a href="<?php echo esc_url( home_url( '/return-policy/' ) ); ?>">سياسة الاسترجاع</a></li>
                        <li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">سياسة الخصوصية</a></li>
                        <li><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">الشروط والأحكام</a></li>
                        <li><a href="<?php echo esc_url( home_url( '/warranty/' ) ); ?>">سياسة الضمان</a></li>
                    </ul>
                </nav>
            </div>

            <div class="footer-widget">
                <h4>تواصل معنا</h4>
                <ul class="footer-links" style="list-style: none; padding: 0;">
                    <li style="margin-bottom: 1rem;">
                        <a href="https://wa.me/966500000000" style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.2rem;">📱</span> واتساب: +966 5X XXX XXXX
                        </a>
                    </li>
                    <li style="margin-bottom: 1rem;">
                        <a href="mailto:support@neogen.store" style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.2rem;">✉️</span> support@neogen.store
                        </a>
                    </li>
                    <li style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-size: 1.2rem;">📍</span> الرياض، المملكة العربية السعودية
                    </li>
                </ul>

                <div style="margin-top: 2rem;">
                    <h4 style="margin-bottom: 1rem;">طرق الدفع</h4>
                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
                        <span style="background: rgba(255,255,255,0.1); padding: 0.5rem 0.75rem; border-radius: 4px; font-size: 0.85rem;">مدى</span>
                        <span style="background: rgba(255,255,255,0.1); padding: 0.5rem 0.75rem; border-radius: 4px; font-size: 0.85rem;">Apple Pay</span>
                        <span style="background: rgba(255,255,255,0.1); padding: 0.5rem 0.75rem; border-radius: 4px; font-size: 0.85rem;">Visa</span>
                        <span style="background: rgba(255,255,255,0.1); padding: 0.5rem 0.75rem; border-radius: 4px; font-size: 0.85rem;">تابي</span>
                        <span style="background: rgba(255,255,255,0.1); padding: 0.5rem 0.75rem; border-radius: 4px; font-size: 0.85rem;">تمارا</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container">
                <p style="margin-bottom: 0.5rem;">&copy; <?php echo date( 'Y' ); ?> neogen. جميع الحقوق محفوظة.</p>
                <p style="font-size: 0.8rem; opacity: 0.7; margin: 0;">
                    ✓ مسجل لدى وزارة التجارة | ✓ ضريبة مضافة مشمولة | ✓ دفع آمن 100%
                </p>
            </div>
        </div>
    </footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
