/**
 * Neogen Product Importer - Admin JavaScript
 */

(function($) {
    'use strict';

    // Import Form Handler
    $('#neogen-import-form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $progress = $('#import-progress');
        var $result = $('#import-result');
        var $submitBtn = $form.find('button[type="submit"]');

        // Get form data
        var url = $('#product-url').val().trim();
        var categoryId = $('#product-category').val();
        var useN8n = $('#use-n8n').is(':checked');

        if (!url) {
            showResult('error', neogenImporter.strings.error + ': ' + 'يرجى إدخال رابط المنتج');
            return;
        }

        // Show progress
        $submitBtn.prop('disabled', true);
        $progress.show();
        $result.hide();
        $('#progress-text').text(neogenImporter.strings.importing);

        // Send AJAX request
        $.ajax({
            url: neogenImporter.ajax_url,
            type: 'POST',
            data: {
                action: 'neogen_import_product',
                nonce: neogenImporter.nonce,
                url: url,
                category_id: categoryId,
                use_n8n: useN8n ? 'true' : 'false'
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.status === 'processing') {
                        // N8N is processing, poll for status
                        pollImportStatus(response.data.import_id, $progress, $result, $submitBtn);
                    } else {
                        // Direct import completed
                        $progress.hide();
                        showResult('success', response.data.message, response.data.product_url);
                        $submitBtn.prop('disabled', false);
                        $('#product-url').val('');
                        refreshRecentImports();
                    }
                } else {
                    $progress.hide();
                    showResult('error', response.data.message || neogenImporter.strings.error);
                    $submitBtn.prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                $progress.hide();
                showResult('error', neogenImporter.strings.error + ': ' + error);
                $submitBtn.prop('disabled', false);
            }
        });
    });

    // Poll for import status (when using N8N)
    function pollImportStatus(importId, $progress, $result, $submitBtn) {
        var pollInterval = setInterval(function() {
            $.ajax({
                url: neogenImporter.ajax_url,
                type: 'POST',
                data: {
                    action: 'neogen_get_import_status',
                    nonce: neogenImporter.nonce,
                    import_id: importId
                },
                success: function(response) {
                    if (response.success) {
                        var data = response.data;

                        if (data.status === 'completed') {
                            clearInterval(pollInterval);
                            $progress.hide();
                            showResult('success', neogenImporter.strings.success, data.product_url, data.product_title);
                            $submitBtn.prop('disabled', false);
                            $('#product-url').val('');
                            refreshRecentImports();
                        } else if (data.status === 'failed') {
                            clearInterval(pollInterval);
                            $progress.hide();
                            showResult('error', data.error || neogenImporter.strings.error);
                            $submitBtn.prop('disabled', false);
                            refreshRecentImports();
                        }
                        // If still processing, continue polling
                    }
                },
                error: function() {
                    // On error, continue polling but don't stop
                }
            });
        }, 3000); // Poll every 3 seconds

        // Timeout after 5 minutes
        setTimeout(function() {
            clearInterval(pollInterval);
            $progress.hide();
            showResult('error', 'انتهت مهلة الانتظار. تحقق من سجل الاستيرادات.');
            $submitBtn.prop('disabled', false);
        }, 300000);
    }

    // Show result message
    function showResult(type, message, productUrl, productTitle) {
        var $result = $('#import-result');
        $result.removeClass('success error').addClass(type);

        var html = '<p>' + message + '</p>';

        if (productUrl) {
            html += '<p><a href="' + productUrl + '" target="_blank">';
            html += productTitle ? 'تعديل: ' + productTitle : 'تعديل المنتج';
            html += ' &rarr;</a></p>';
        }

        $result.html(html).show();
    }

    // Refresh recent imports table
    function refreshRecentImports() {
        // Reload the page to refresh the table
        // In a more advanced implementation, we'd use AJAX to update just the table
        // location.reload();
    }

    // Delete import record
    $(document).on('click', '.delete-import', function() {
        if (!confirm(neogenImporter.strings.confirm_delete)) {
            return;
        }

        var $btn = $(this);
        var importId = $btn.data('id');
        var $row = $btn.closest('tr');

        $.ajax({
            url: neogenImporter.ajax_url,
            type: 'POST',
            data: {
                action: 'neogen_delete_import',
                nonce: neogenImporter.nonce,
                import_id: importId
            },
            success: function(response) {
                if (response.success) {
                    $row.fadeOut(300, function() {
                        $(this).remove();
                    });
                } else {
                    alert(response.data.message || 'حدث خطأ');
                }
            },
            error: function() {
                alert('حدث خطأ في الاتصال');
            }
        });
    });

    // Retry import
    $(document).on('click', '.retry-import', function() {
        var url = $(this).data('url');
        if (url) {
            $('#product-url').val(url);
            $('html, body').animate({
                scrollTop: $('#neogen-import-form').offset().top - 50
            }, 500);
        }
    });

    // Copy buttons
    $(document).on('click', '.copy-btn', function() {
        var targetId = $(this).data('target');
        var text = $('#' + targetId).text();
        copyToClipboard(text);
        $(this).addClass('copied');
        setTimeout(function() {
            $(this).removeClass('copied');
        }.bind(this), 2000);
    });

    // Copy workflow
    $(document).on('click', '.copy-workflow', function() {
        var text = $('#workflow-json').text();
        copyToClipboard(text);
        var originalText = $(this).html();
        $(this).html('<span class="dashicons dashicons-yes"></span> تم النسخ!');
        setTimeout(function() {
            $(this).html(originalText);
        }.bind(this), 2000);
    });

    // Copy to clipboard helper
    function copyToClipboard(text) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text);
        } else {
            // Fallback for older browsers
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
        }
    }

    // Filter imports (history page)
    $('#filter-status, #filter-platform').on('change', function() {
        var status = $('#filter-status').val();
        var platform = $('#filter-platform').val();

        $('#history-table-body tr').each(function() {
            var $row = $(this);
            var rowStatus = $row.find('.status-badge').hasClass('status-' + status) || !status;
            var rowPlatform = $row.find('.platform-badge').hasClass(platform) || !platform;

            if (rowStatus && rowPlatform) {
                $row.show();
            } else {
                $row.hide();
            }
        });
    });

    // Regenerate API Key
    $('#regenerate-api-key').on('click', function() {
        var $btn = $(this);
        var originalHtml = $btn.html();

        if (!confirm('هل تريد إنشاء مفتاح API جديد؟ سيتم إلغاء المفتاح الحالي.')) {
            return;
        }

        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spinning"></span>');

        $.ajax({
            url: neogenImporter.ajax_url,
            type: 'POST',
            data: {
                action: 'neogen_regenerate_api_key',
                nonce: neogenImporter.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update API key display
                    $('#api-key').text(response.data.api_key);

                    // Update the header instruction as well
                    $('.n8n-info-box .description code').text('X-API-Key: ' + response.data.api_key);

                    // Show success
                    $btn.html('<span class="dashicons dashicons-yes"></span>');
                    setTimeout(function() {
                        $btn.html(originalHtml).prop('disabled', false);
                    }, 2000);
                } else {
                    alert(response.data.message || 'حدث خطأ');
                    $btn.html(originalHtml).prop('disabled', false);
                }
            },
            error: function() {
                alert('حدث خطأ في الاتصال');
                $btn.html(originalHtml).prop('disabled', false);
            }
        });
    });

})(jQuery);
