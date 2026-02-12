<?php
/**
 * Template Name: Front Page
 */

get_header();
?>

<!-- Hero Section -->
<section class="hero-section" style="background: linear-gradient(rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.8)), url('https://images.unsplash.com/photo-1558002038-10917738d476?auto=format&fit=crop&w=1600&q=80'); background-size: cover; background-position: center; color: white; padding: 6rem 0; text-align: center;">
    <div class="container">
        <h1 style="font-size: 3rem; color: white; margin-bottom: 1.5rem;">حوّل بيتك إلى بيت ذكي.. أسهل مما تتخيل!</h1>
        <p style="font-size: 1.25rem; margin-bottom: 2.5rem; max-width: 800px; margin-left: auto; margin-right: auto; opacity: 0.9;">
            منتجات ذكية عالية الجودة | دعم فني بالعربي 24/7 | شحن مجاني فوق 300 ريال
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="<?php echo wc_get_page_permalink( 'shop' ); ?>" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem 2rem;">تسوق الآن</a>
            <button type="button" id="play-video-btn" class="btn btn-outline" style="border-color: white; color: white; display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                شاهد الفيديو التعريفي
            </button>
        </div>
    </div>
</section>

<!-- Video Modal -->
<div id="video-modal" class="video-modal" style="display: none;">
    <div class="video-modal-backdrop"></div>
    <div class="video-modal-content">
        <button type="button" class="video-modal-close" aria-label="إغلاق">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
        </button>
        <div class="video-container">
            <!-- Replace VIDEO_ID with your YouTube video ID -->
            <iframe id="youtube-player" width="560" height="315" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
    </div>
</div>

<style>
.video-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 10001;
    display: flex;
    align-items: center;
    justify-content: center;
}
.video-modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.9);
}
.video-modal-content {
    position: relative;
    width: 90%;
    max-width: 900px;
    z-index: 1;
}
.video-modal-close {
    position: absolute;
    top: -50px;
    right: 0;
    background: transparent;
    border: none;
    color: #fff;
    cursor: pointer;
    opacity: 0.8;
    transition: opacity 0.2s;
}
.video-modal-close:hover {
    opacity: 1;
}
.video-container {
    position: relative;
    padding-bottom: 56.25%; /* 16:9 */
    height: 0;
    overflow: hidden;
    border-radius: 12px;
    background: #000;
}
.video-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var playBtn = document.getElementById('play-video-btn');
    var modal = document.getElementById('video-modal');
    var closeBtn = modal ? modal.querySelector('.video-modal-close') : null;
    var backdrop = modal ? modal.querySelector('.video-modal-backdrop') : null;
    var iframe = document.getElementById('youtube-player');

    // YouTube Video ID - Change this to your actual video ID
    var videoId = 'dQw4w9WgXcQ'; // Placeholder - replace with real video

    function openModal() {
        if (modal && iframe) {
            iframe.src = 'https://www.youtube.com/embed/' + videoId + '?autoplay=1&rel=0';
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal() {
        if (modal && iframe) {
            modal.style.display = 'none';
            iframe.src = '';
            document.body.style.overflow = '';
        }
    }

    if (playBtn) {
        playBtn.addEventListener('click', openModal);
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    if (backdrop) {
        backdrop.addEventListener('click', closeModal);
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal && modal.style.display === 'flex') {
            closeModal();
        }
    });
});
</script>

<!-- Features Section -->
<section class="features-section" style="padding: 4rem 0; background: white;">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <div class="feature-item" style="text-align: center; padding: 1.5rem; border-radius: 1rem; background: var(--color-bg);">
                <h3>🚀 خبراء Home Assistant</h3>
                <p>الوحيدون في السعودية المتخصصون في أنظمة الهوم اسيستانت.</p>
            </div>
            <div class="feature-item" style="text-align: center; padding: 1.5rem; border-radius: 1rem; background: var(--color-bg);">
                <h3>💬 دعم فني عربي</h3>
                <p>دعم فني متخصص على واتساب 24/7 لمساعدتك.</p>
            </div>
            <div class="feature-item" style="text-align: center; padding: 1.5rem; border-radius: 1rem; background: var(--color-bg);">
                <h3>🛡️ ضمان ذهبي</h3>
                <p>ضمان سنة كاملة واسترجاع خلال 15 يوم.</p>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories-section" style="padding: 4rem 0;">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 3rem;">أقسام المنتجات</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
            <?php
            $categories = array(
                '🔒 الأمان والحماية' => 'security',
                '💡 الإضاءة الذكية' => 'lighting',
                '🌡️ التحكم بالجو' => 'climate',
                '🏠 أتمتة المنزل' => 'automation',
                '🎵 الصوت والترفيه' => 'audio',
                '💧 الري الذكي' => 'irrigation'
            );
            foreach($categories as $name => $slug) {
                echo '<a href="' . wc_get_page_permalink( 'shop' ) . '?category=' . $slug . '" class="category-card" style="background: white; padding: 2rem; border-radius: 0.5rem; text-align: center; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: transform 0.3s; display: block; color: var(--color-text); font-weight: bold; font-size: 1.1rem;">' . $name . '</a>';
            }
            ?>
            <style>.category-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px rgba(0,0,0,0.1) !important; color: var(--color-primary) !important; }</style>
        </div>
    </div>
</section>

<!-- Best Sellers (WooCommerce) -->
<section class="best-sellers" style="padding: 4rem 0; background: white;">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 3rem;">المنتجات الأكثر مبيعاً</h2>
        <?php echo do_shortcode('[products limit="4" columns="4" best_selling="true"]'); ?>
        <div style="text-align: center; margin-top: 2rem;">
            <a href="<?php echo wc_get_page_permalink( 'shop' ); ?>" class="btn btn-outline">عرض كل المنتجات</a>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section class="testimonials" style="padding: 4rem 0;">
    <div class="container">
        <h2 style="text-align: center; margin-bottom: 3rem;">ماذا يقول عملاؤنا؟</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <div class="testimonial" style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.01);">
                <p style="font-style: italic; margin-bottom: 1rem;">"حولت شقتي لبيت ذكي بأقل من 2000 ريال! الدعم الفني ممتاز والشروحات واضحة"</p>
                <strong>- أحمد من الرياض</strong> ⭐⭐⭐⭐⭐
            </div>
            <div class="testimonial" style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.01);">
                <p style="font-style: italic; margin-bottom: 1rem;">"أخيراً لقيت متجر يفهم Home Assistant ويتكلم عربي!"</p>
                <strong>- سارة من جدة</strong> ⭐⭐⭐⭐⭐
            </div>
            <div class="testimonial" style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.01);">
                <p style="font-style: italic; margin-bottom: 1rem;">"الشحن وصل في يومين والتركيب كان سهل مع الفيديوهات المرفقة"</p>
                <strong>- محمد من الدمام</strong> ⭐⭐⭐⭐⭐
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<?php
$cta_whatsapp_number = get_option('neogen_whatsapp_number', '966500000000');
$cta_whatsapp_link = 'https://wa.me/' . $cta_whatsapp_number . '?text=' . urlencode('مرحباً، أحتاج مساعدة في اختيار المنتجات المناسبة لبيتي');
?>
<section class="cta-section" style="padding: 5rem 0; background: var(--color-primary); color: white; text-align: center;">
    <div class="container">
        <h2 style="color: white; margin-bottom: 1.5rem;">محتار من وين تبدأ؟</h2>
        <p style="font-size: 1.2rem; margin-bottom: 2rem; opacity: 0.9;">تواصل معنا على واتساب ونساعدك تختار المنتجات المناسبة لبيتك!</p>
        <a href="<?php echo esc_url($cta_whatsapp_link); ?>" target="_blank" class="btn" style="background: white; color: var(--color-primary); display: inline-flex; align-items: center; gap: 0.5rem;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            تواصل واتساب
        </a>
    </div>
</section>

<?php
get_footer();
