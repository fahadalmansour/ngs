/**
 * Neogen Product Registration - Frontend JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // Toggle order accordion
        $('.order-header').on('click', function() {
            var $this = $(this);
            var $products = $this.next('.order-products');

            $this.toggleClass('open');
            $products.slideToggle(200);
        });

        // Register serial number
        $('.register-serial-btn').on('click', function() {
            var $btn = $(this);
            var $form = $btn.closest('.registration-form');
            var $input = $form.find('.serial-input');
            var $message = $form.next('.registration-message');

            var serial = $input.val().trim();
            var orderId = $input.data('order-id');
            var productId = $input.data('product-id');

            if (!serial) {
                showMessage($message, 'يرجى إدخال الرقم التسلسلي', 'error');
                $input.focus();
                return;
            }

            // Disable button during request
            $btn.prop('disabled', true).text('جاري التسجيل...');
            $form.addClass('loading');

            $.ajax({
                url: neogenReg.ajax_url,
                type: 'POST',
                data: {
                    action: 'neogen_register_serial',
                    nonce: neogenReg.register_nonce,
                    serial_number: serial,
                    order_id: orderId,
                    product_id: productId
                },
                success: function(response) {
                    if (response.success) {
                        showMessage($message, response.data.message, 'success');
                        $input.val('').prop('disabled', true);
                        $btn.text('✓ تم التسجيل').addClass('registered');

                        // Refresh page after short delay to show updated status
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        showMessage($message, response.data.message, 'error');
                        $btn.prop('disabled', false).text('تسجيل');
                    }
                },
                error: function() {
                    showMessage($message, 'حدث خطأ. يرجى المحاولة مرة أخرى.', 'error');
                    $btn.prop('disabled', false).text('تسجيل');
                },
                complete: function() {
                    $form.removeClass('loading');
                }
            });
        });

        // Download manual
        $('.download-manual-btn').on('click', function() {
            var $btn = $(this);
            var productId = $btn.data('product-id');

            $btn.prop('disabled', true).text('جاري التحميل...');

            $.ajax({
                url: neogenReg.ajax_url,
                type: 'POST',
                data: {
                    action: 'neogen_download_manual',
                    nonce: neogenReg.download_nonce,
                    product_id: productId
                },
                success: function(response) {
                    if (response.success) {
                        // Open PDF in new tab
                        window.open(response.data.url, '_blank');
                        $btn.prop('disabled', false).text('📄 تحميل الدليل');
                    } else {
                        alert(response.data.message);
                        $btn.prop('disabled', false).text('📄 تحميل الدليل');
                    }
                },
                error: function() {
                    alert('حدث خطأ. يرجى المحاولة مرة أخرى.');
                    $btn.prop('disabled', false).text('📄 تحميل الدليل');
                }
            });
        });

        // Enter key to submit serial
        $('.serial-input').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $(this).closest('.registration-form').find('.register-serial-btn').click();
            }
        });

        // Helper function to show messages
        function showMessage($el, message, type) {
            $el.removeClass('success error').addClass(type).text(message).show();

            if (type === 'success') {
                setTimeout(function() {
                    $el.fadeOut();
                }, 5000);
            }
        }

    });

})(jQuery);
