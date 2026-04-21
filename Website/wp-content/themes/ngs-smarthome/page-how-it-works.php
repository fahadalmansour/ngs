<?php
/**
 * Template Name: How It Works
 */

get_header();
?>

<main class="ngs-page-wrap" role="main">
    <?php while ( have_posts() ) : the_post(); ?>
        <section class="ngs-page-hero">
            <div class="container">
                <h1><?php the_title(); ?></h1>
                <p>كيف تعمل تجربة NGS من اختيار المنتج حتى الدعم بعد الشراء.</p>
            </div>
        </section>

        <section class="ngs-page-content-section">
            <div class="container">
                <?php
                $raw_content = trim( wp_strip_all_tags( get_post_field( 'post_content', get_the_ID() ) ) );
                if ( '' === $raw_content ) :
                ?>
                    <article class="ngs-entry-content">
                        <h2>خطوات العمل</h2>
                        <ol>
                            <li><strong>استكشف المنتجات:</strong> تصفح المتجر حسب القسم أو التوافق مع نظامك (Home Assistant, HomeKit, Google, Alexa).</li>
                            <li><strong>اختر الحل المناسب:</strong> راجع المواصفات والتقييمات أو تواصل معنا على واتساب لاختيار الأنسب.</li>
                            <li><strong>أكمل الطلب بأمان:</strong> ادفع عبر وسائل الدفع المتاحة مع خيارات التقسيط (تابي/تمارا).</li>
                            <li><strong>شحن واستلام سريع:</strong> يتم تجهيز الطلب والشحن حسب المدينة، مع تحديثات واضحة لحالة الطلب.</li>
                            <li><strong>تركيب وتشغيل:</strong> اتبع دليل الاستخدام والفيديوهات، والدعم الفني يساعدك خطوة بخطوة.</li>
                            <li><strong>دعم وضمان:</strong> ما بعد البيع يشمل ضمان المنتجات وسياسة استرجاع واضحة خلال المدة المحددة.</li>
                        </ol>

                        <h2>متى تتواصل معنا؟</h2>
                        <ul>
                            <li>إذا كنت غير متأكد من التوافق بين الأجهزة.</li>
                            <li>إذا كنت تبدأ أول مشروع منزل ذكي وتحتاج باقة جاهزة.</li>
                            <li>إذا واجهت أي خطوة تركيب أو إعداد بعد الاستلام.</li>
                        </ul>

                        <p><strong>واتساب:</strong> +966 5X XXX XXXX (Placeholder)</p>
                    </article>
                <?php else : ?>
                    <article class="ngs-entry-content">
                        <?php the_content(); ?>
                    </article>
                <?php endif; ?>
            </div>
        </section>
    <?php endwhile; ?>
</main>

<?php
get_footer();
