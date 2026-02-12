<?php
/**
 * Template Name: Contact Us
 */

get_header();

// Get WhatsApp number from plugin settings
$whatsapp_number = get_option('neogen_whatsapp_number', '966500000000');
$whatsapp_display = '+' . preg_replace('/^(\d{3})(\d{2})(\d{3})(\d{4})$/', '$1 $2 $3 $4', $whatsapp_number);
$whatsapp_link = 'https://wa.me/' . $whatsapp_number . '?text=' . urlencode('مرحباً، أريد الاستفسار عن منتجاتكم');
?>

<div class="page-header" style="background: var(--color-bg); padding: 4rem 0; text-align: center;">
    <div class="container">
        <h1>تواصل معنا</h1>
        <p style="margin-top: 1rem; color: #64748b;">نحب نسمع منك! تواصل معنا بالطريقة اللي تناسبك</p>
    </div>
</div>

<div class="container" style="padding: 4rem 1rem;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 4rem;">

        <!-- Contact Info -->
        <div>
            <div style="margin-bottom: 2rem;">
                <h3 style="display: flex; align-items: center; gap: 0.5rem;">
                    📱 واتساب (الأسرع)
                </h3>
                <a href="<?php echo esc_url($whatsapp_link); ?>" target="_blank" style="font-size: 1.2rem; font-weight: bold; color: var(--color-primary); text-decoration: none;">
                    <?php echo esc_html($whatsapp_display); ?>
                </a>
                <p style="color: #64748b; margin-top: 0.5rem;">متاحين للرد على استفساراتك</p>
                <a href="<?php echo esc_url($whatsapp_link); ?>" target="_blank" class="btn btn-primary" style="margin-top: 1rem; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    ابدأ محادثة واتساب
                </a>
            </div>

            <div style="margin-bottom: 2rem;">
                <h3 style="display: flex; align-items: center; gap: 0.5rem;">
                    📧 البريد الإلكتروني
                </h3>
                <a href="mailto:support@neogen.store" style="font-size: 1.1rem; color: var(--color-text); text-decoration: none;">support@neogen.store</a>
                <p style="color: #64748b; margin-top: 0.5rem;">نرد خلال 24 ساعة كحد أقصى</p>
            </div>

            <div style="margin-bottom: 2rem;">
                <h3 style="display: flex; align-items: center; gap: 0.5rem;">
                    📍 موقعنا
                </h3>
                <p>الرياض، المملكة العربية السعودية</p>
                <p style="color: #64748b;">(متجر إلكتروني فقط - لا يوجد معرض)</p>
            </div>

            <div style="margin-top: 3rem; background: #f8fafc; padding: 1.5rem; border-radius: 1rem;">
                <h3 style="margin-bottom: 1rem;">🕐 ساعات العمل</h3>
                <ul style="list-style: none; padding: 0; line-height: 2;">
                    <li style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0;">
                        <span>الأحد - الخميس</span>
                        <strong>9 صباحاً - 10 مساءً</strong>
                    </li>
                    <li style="display: flex; justify-content: space-between; padding: 0.5rem 0;">
                        <span>الجمعة - السبت</span>
                        <strong>2 مساءً - 10 مساءً</strong>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Contact Form -->
        <div style="background: white; padding: 2rem; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
            <h3 style="margin-bottom: 1.5rem;">أرسل رسالة</h3>
            
            <!-- Note: This is a static HTML form. In production, use Contact Form 7 or WPForms shortcode -->
            <form>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">الاسم</label>
                    <input type="text" style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 0.5rem;" placeholder="اسمك الكريم">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">رقم الجوال</label>
                    <input type="tel" style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 0.5rem;" placeholder="05xxxxxxxx">
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">الموضوع</label>
                    <select style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 0.5rem;">
                        <option>استفسار عن منتج</option>
                        <option>دعم فني</option>
                        <option>مشكلة في الطلب</option>
                        <option>اقتراح أو شكوى</option>
                        <option>أخرى</option>
                    </select>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">الرسالة</label>
                    <textarea rows="5" style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 0.5rem;" placeholder="كيف نقدر نساعدك؟"></textarea>
                </div>

                <button type="button" class="btn btn-primary" style="width: 100%; border: none;">إرسال</button>
            </form>
        </div>

    </div>
</div>

<?php
get_footer();
