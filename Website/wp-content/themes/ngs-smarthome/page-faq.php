<?php
/**
 * Template Name: FAQ
 */

get_header();
?>

<div class="page-header" style="background: var(--color-bg); padding: 4rem 0; text-align: center;">
    <div class="container">
        <h1>الأسئلة الشائعة</h1>
        <p style="margin-top: 1rem; color: #64748b;">إجابات على أكثر الأسئلة شيوعاً</p>
    </div>
</div>

<div class="container" style="padding: 4rem 1rem;">
    <div style="max-width: 800px; margin: 0 auto;">

        <style>
            .faq-item {
                background: #fff;
                border: 1px solid var(--color-border);
                border-radius: 12px;
                margin-bottom: 1rem;
                overflow: hidden;
            }
            .faq-question {
                padding: 1.5rem;
                cursor: pointer;
                display: flex;
                justify-content: space-between;
                align-items: center;
                font-weight: 600;
                color: var(--color-secondary);
            }
            .faq-question:hover {
                background: var(--color-bg-light);
            }
            .faq-answer {
                padding: 0 1.5rem 1.5rem;
                color: var(--color-text-muted);
                line-height: 1.8;
                display: none;
            }
            .faq-item.active .faq-answer {
                display: block;
            }
            .faq-icon {
                font-size: 1.5rem;
                transition: transform 0.3s;
            }
            .faq-item.active .faq-icon {
                transform: rotate(45deg);
            }
            .faq-category {
                margin-bottom: 2rem;
            }
            .faq-category h2 {
                color: var(--color-primary);
                margin-bottom: 1.5rem;
                font-size: 1.3rem;
            }
        </style>

        <!-- الطلبات والشحن -->
        <div class="faq-category">
            <h2>الطلبات والشحن</h2>

            <div class="faq-item">
                <div class="faq-question">
                    كم مدة التوصيل؟
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-answer">
                    نشحن لجميع مناطق المملكة. عادة يصل الطلب خلال 2-5 أيام عمل حسب المدينة. المدن الرئيسية (الرياض، جدة، الدمام) غالباً 1-3 أيام.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    هل يوجد شحن مجاني؟
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-answer">
                    نعم! الشحن مجاني للطلبات فوق 300 ريال. للطلبات الأقل، رسوم الشحن تبدأ من 25 ريال.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    كيف أتابع طلبي؟
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-answer">
                    بمجرد شحن طلبك، ستصلك رسالة على البريد الإلكتروني تتضمن رقم التتبع. يمكنك أيضاً متابعة طلباتك من صفحة "حسابي".
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    ما طرق الدفع المتاحة؟
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-answer">
                    نقبل: مدى، فيزا، ماستركارد، Apple Pay، STC Pay، تابي (تقسيط)، تمارا (تقسيط). الدفع عند الاستلام متاح برسوم إضافية 15 ريال.
                </div>
            </div>
        </div>

        <!-- المنتجات والتوافق -->
        <div class="faq-category">
            <h2>المنتجات والتوافق</h2>

            <div class="faq-item">
                <div class="faq-question">
                    هل المنتجات أصلية؟
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-answer">
                    نعم، جميع منتجاتنا أصلية 100% ومستوردة مباشرة من الشركات المصنعة أو موزعيها المعتمدين. نقدم ضمان سنة على جميع المنتجات.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    هل تعمل المنتجات مع Home Assistant؟
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-answer">
                    نعم! نختار منتجاتنا بعناية للتوافق مع Home Assistant. معظم منتجاتنا تعمل عبر Zigbee أو WiFi ويمكن دمجها بسهولة.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    هل أحتاج Hub للحساسات؟
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-answer">
                    معظم حساسات Zigbee تحتاج Hub (مثل Aqara Hub أو Conbee). المنتجات التي تتطلب Hub يُذكر ذلك بوضوح في صفحة المنتج. منتجات WiFi لا تحتاج Hub.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    ما الفرق بين Zigbee و WiFi؟
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-answer">
                    <strong>Zigbee:</strong> استهلاك طاقة منخفض جداً (البطاريات تدوم سنوات)، يحتاج Hub، شبكة mesh قوية.<br>
                    <strong>WiFi:</strong> لا يحتاج Hub، اتصال مباشر بالراوتر، لكن استهلاك طاقة أعلى.
                </div>
            </div>
        </div>

        <!-- الضمان والاسترجاع -->
        <div class="faq-category">
            <h2>الضمان والاسترجاع</h2>

            <div class="faq-item">
                <div class="faq-question">
                    ما مدة الضمان؟
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-answer">
                    جميع المنتجات مضمونة لمدة سنة كاملة ضد العيوب المصنعية. الملحقات والكابلات مضمونة 6 أشهر.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    كيف أسترجع منتج؟
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-answer">
                    يمكنك الاسترجاع خلال 15 يوم من الاستلام بشرط أن يكون المنتج في حالته الأصلية مع التغليف. تواصل معنا عبر واتساب لبدء عملية الاسترجاع.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    متى أسترد المبلغ؟
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-answer">
                    بعد استلام المنتج المُرجع وفحصه، يُعاد المبلغ خلال 5-10 أيام عمل بنفس طريقة الدفع الأصلية.
                </div>
            </div>
        </div>

        <!-- الدعم الفني -->
        <div class="faq-category">
            <h2>الدعم الفني</h2>

            <div class="faq-item">
                <div class="faq-question">
                    هل تقدمون دعم فني للتركيب؟
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-answer">
                    نعم! نقدم دعم فني مجاني عبر واتساب لمساعدتك في تركيب وإعداد منتجاتك. كما نوفر فيديوهات تعليمية بالعربي.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    كيف أتواصل مع الدعم الفني؟
                    <span class="faq-icon">+</span>
                </div>
                <div class="faq-answer">
                    يمكنك التواصل معنا عبر:<br>
                    - واتساب (الأسرع): الزر الأخضر أسفل الصفحة<br>
                    - البريد: support@neogen.store<br>
                    متاحون 24/7 للرد على استفساراتك.
                </div>
            </div>
        </div>

        <!-- CTA -->
        <div style="background: var(--gradient-primary); padding: 3rem; border-radius: 16px; text-align: center; margin-top: 3rem;">
            <h3 style="color: #fff; margin-bottom: 1rem;">لم تجد جواب سؤالك؟</h3>
            <p style="color: rgba(255,255,255,0.9); margin-bottom: 1.5rem;">تواصل معنا مباشرة وسنساعدك!</p>
            <a href="https://wa.me/966500000000" class="btn" style="background: #fff; color: var(--color-primary);">تواصل واتساب</a>
        </div>

    </div>
</div>

<script>
document.querySelectorAll('.faq-question').forEach(function(question) {
    question.addEventListener('click', function() {
        this.parentElement.classList.toggle('active');
    });
});
</script>

<?php
get_footer();
