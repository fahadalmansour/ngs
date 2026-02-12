/**
 * Neogen WhatsApp - JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Close CTA bubble
        $('.neogen-wa-cta .cta-close').on('click', function(e) {
            e.preventDefault();
            $(this).closest('.neogen-wa-cta').addClass('hidden');

            // Remember user closed it (for this session)
            sessionStorage.setItem('neogen_wa_cta_closed', 'true');
        });

        // Check if CTA was previously closed
        if (sessionStorage.getItem('neogen_wa_cta_closed') === 'true') {
            $('.neogen-wa-cta').addClass('hidden');
        }

        // Show CTA after delay
        setTimeout(function() {
            if (sessionStorage.getItem('neogen_wa_cta_closed') !== 'true') {
                $('.neogen-wa-cta').css('display', 'flex');
            }
        }, 3000);

        // Hide CTA when button is clicked
        $('.neogen-wa-button').on('click', function() {
            $('.neogen-wa-cta').addClass('hidden');
        });

        // Auto-hide CTA after some time
        setTimeout(function() {
            $('.neogen-wa-cta').fadeOut();
        }, 15000);
    });

})(jQuery);
