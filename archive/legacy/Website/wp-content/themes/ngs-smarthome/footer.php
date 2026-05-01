    </div><!-- #content -->

    <footer id="colophon" class="site-footer">
        <div class="container footer-grid">
            <div class="footer-widget">
                <h4>عن NGS</h4>
                <p>NGS Smart Home متجر متخصص في حلول المنزل الذكي للمستخدم العربي، مع تركيز على التوافق مع Home Assistant وتجربة تركيب أسهل.</p>
            </div>

            <div class="footer-widget">
                <h4>روابط سريعة</h4>
                <nav class="footer-links">
                    <?php
                    if ( has_nav_menu( 'footer' ) ) {
                        wp_nav_menu(
                            array(
                                'theme_location' => 'footer',
                                'container'      => false,
                                'depth'          => 1,
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
            </div>

            <div class="footer-widget">
                <h4>السياسات</h4>
                <nav class="footer-links">
                    <ul>
                        <li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">سياسة الخصوصية</a></li>
                        <li><a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>">الشروط والأحكام</a></li>
                        <li><a href="<?php echo esc_url( home_url( '/return-policy/' ) ); ?>">سياسة الاسترجاع</a></li>
                        <li><a href="<?php echo esc_url( home_url( '/warranty/' ) ); ?>">الضمان</a></li>
                    </ul>
                </nav>
            </div>

            <div class="footer-widget">
                <h4>تواصل معنا</h4>
                <?php
                $has_whatsapp = function_exists( 'ngs_has_whatsapp_number' ) ? ngs_has_whatsapp_number() : false;
                $whatsapp_display = function_exists( 'ngs_get_whatsapp_display' ) ? ngs_get_whatsapp_display() : '+966 5X XXX XXXX';
                $whatsapp_link = function_exists( 'ngs_get_whatsapp_link' ) ? ngs_get_whatsapp_link() : '#';
                ?>
                <ul class="footer-links ngs-footer-contact-list">
                    <li>
                        <a href="<?php echo esc_url( $whatsapp_link ); ?>"<?php echo $has_whatsapp ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
                            واتساب: <?php echo esc_html( $whatsapp_display ); ?>
                        </a>
                    </li>
                    <li><a href="mailto:support@ngs-smarthome.sa">support@ngs-smarthome.sa</a></li>
                    <li>الرياض، المملكة العربية السعودية</li>
                </ul>

                <p class="ngs-footer-meta-note">Placeholder: حدّث السجل التجاري والرقم الضريبي من بيانات النشاط.</p>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="container">
                <p style="margin-bottom: 0.5rem;">&copy; <?php echo esc_html( date( 'Y' ) ); ?> NGS Smart Home. جميع الحقوق محفوظة.</p>
                <p style="font-size: 0.8rem; opacity: 0.8; margin: 0;">السجل التجاري: [رقم السجل] | الرقم الضريبي: [رقم ضريبي] | دفع آمن 100%</p>
            </div>
        </div>
    </footer>
</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
