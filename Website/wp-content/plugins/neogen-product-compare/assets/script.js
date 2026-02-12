/**
 * Neogen Product Compare - JavaScript
 */

(function($) {
    'use strict';

    var compareData = {
        count: 0,
        products: [],
        ids: []
    };

    // Initialize
    $(document).ready(function() {
        loadCompareData();

        // Add to compare button click
        $(document).on('click', '.neogen-compare-btn', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var productId = $btn.data('product-id');

            if ($btn.hasClass('added')) {
                removeFromCompare(productId, $btn);
            } else {
                addToCompare(productId, $btn);
            }
        });

        // Remove from compare bar
        $(document).on('click', '.compare-bar-product .remove-btn', function(e) {
            e.preventDefault();
            var productId = $(this).data('product-id');
            removeFromCompare(productId);
        });

        // Clear all from bar
        $(document).on('click', '.btn-clear-compare', function(e) {
            e.preventDefault();
            clearCompare();
        });

        // Remove from compare page
        $(document).on('click', '.compare-table .remove-product', function(e) {
            e.preventDefault();
            var productId = $(this).data('product-id');
            removeFromCompare(productId, null, true);
        });
    });

    // Load compare data on page load
    function loadCompareData() {
        $.ajax({
            url: neogenCompare.ajax_url,
            type: 'POST',
            data: {
                action: 'neogen_get_compare_data'
            },
            success: function(response) {
                if (response.success) {
                    compareData = response.data;
                    updateUI();
                }
            }
        });
    }

    // Add to compare
    function addToCompare(productId, $btn) {
        if (compareData.count >= neogenCompare.max_products) {
            showNotice(neogenCompare.strings.max_reached, 'error');
            return;
        }

        $.ajax({
            url: neogenCompare.ajax_url,
            type: 'POST',
            data: {
                action: 'neogen_add_to_compare',
                nonce: neogenCompare.nonce,
                product_id: productId
            },
            beforeSend: function() {
                if ($btn) {
                    $btn.addClass('loading');
                }
            },
            success: function(response) {
                if (response.success) {
                    compareData.count = response.data.count;
                    compareData.products = response.data.products;
                    compareData.ids = response.data.products.map(function(p) { return p.id; });

                    updateUI();

                    if ($btn) {
                        $btn.addClass('added');
                        $btn.find('.compare-text').text(neogenCompare.strings.added);
                    }

                    showNotice(neogenCompare.strings.added, 'success');
                } else {
                    if (response.data && response.data.max_reached) {
                        showNotice(neogenCompare.strings.max_reached, 'error');
                    }
                }
            },
            complete: function() {
                if ($btn) {
                    $btn.removeClass('loading');
                }
            }
        });
    }

    // Remove from compare
    function removeFromCompare(productId, $btn, reload) {
        $.ajax({
            url: neogenCompare.ajax_url,
            type: 'POST',
            data: {
                action: 'neogen_remove_from_compare',
                nonce: neogenCompare.nonce,
                product_id: productId
            },
            success: function(response) {
                if (response.success) {
                    compareData.count = response.data.count;
                    compareData.products = response.data.products;
                    compareData.ids = response.data.products.map(function(p) { return p.id; });

                    updateUI();

                    if ($btn) {
                        $btn.removeClass('added');
                        $btn.find('.compare-text').text(neogenCompare.strings.add);
                    }

                    // Also update any button with this product ID
                    $('.neogen-compare-btn[data-product-id="' + productId + '"]')
                        .removeClass('added')
                        .find('.compare-text').text(neogenCompare.strings.add);

                    if (reload) {
                        location.reload();
                    }
                }
            }
        });
    }

    // Clear compare
    function clearCompare() {
        $.ajax({
            url: neogenCompare.ajax_url,
            type: 'POST',
            data: {
                action: 'neogen_clear_compare',
                nonce: neogenCompare.nonce
            },
            success: function(response) {
                if (response.success) {
                    compareData = {count: 0, products: [], ids: []};
                    updateUI();

                    // Reset all buttons
                    $('.neogen-compare-btn')
                        .removeClass('added')
                        .find('.compare-text').text(neogenCompare.strings.add);
                }
            }
        });
    }

    // Update UI
    function updateUI() {
        var $bar = $('#neogen-compare-bar');
        var $products = $bar.find('.compare-bar-products');
        var $count = $bar.find('.count-number');

        // Update count
        $count.text(compareData.count);

        // Update products in bar
        $products.empty();
        compareData.products.forEach(function(product) {
            var html = '<div class="compare-bar-product">' +
                '<img src="' + product.image + '" alt="' + product.name + '" />' +
                '<button type="button" class="remove-btn" data-product-id="' + product.id + '">&times;</button>' +
                '</div>';
            $products.append(html);
        });

        // Show/hide bar
        if (compareData.count > 0) {
            $bar.addClass('visible').show();
        } else {
            $bar.removeClass('visible');
            setTimeout(function() {
                if (!$bar.hasClass('visible')) {
                    $bar.hide();
                }
            }, 300);
        }

        // Update buttons state
        $('.neogen-compare-btn').each(function() {
            var $btn = $(this);
            var productId = $btn.data('product-id');

            if (compareData.ids.indexOf(productId) !== -1) {
                $btn.addClass('added');
                $btn.find('.compare-text').text(neogenCompare.strings.added);
            } else {
                $btn.removeClass('added');
                $btn.find('.compare-text').text(neogenCompare.strings.add);
            }
        });
    }

    // Show notice
    function showNotice(message, type) {
        var $notice = $('<div class="neogen-compare-notice ' + type + '">' + message + '</div>');

        $('body').append($notice);

        setTimeout(function() {
            $notice.addClass('visible');
        }, 10);

        setTimeout(function() {
            $notice.removeClass('visible');
            setTimeout(function() {
                $notice.remove();
            }, 300);
        }, 2000);
    }

    // Add notice styles dynamically
    $('<style>')
        .text(
            '.neogen-compare-notice {' +
            '  position: fixed;' +
            '  top: 100px;' +
            '  left: 50%;' +
            '  transform: translateX(-50%) translateY(-20px);' +
            '  background: #1a1a2e;' +
            '  color: #fff;' +
            '  padding: 12px 24px;' +
            '  border-radius: 8px;' +
            '  font-size: 14px;' +
            '  font-weight: 500;' +
            '  z-index: 10000;' +
            '  opacity: 0;' +
            '  transition: all 0.3s ease;' +
            '  box-shadow: 0 4px 15px rgba(0,0,0,0.2);' +
            '}' +
            '.neogen-compare-notice.visible {' +
            '  opacity: 1;' +
            '  transform: translateX(-50%) translateY(0);' +
            '}' +
            '.neogen-compare-notice.success {' +
            '  background: #10B981;' +
            '}' +
            '.neogen-compare-notice.error {' +
            '  background: #ef4444;' +
            '}'
        )
        .appendTo('head');

})(jQuery);
