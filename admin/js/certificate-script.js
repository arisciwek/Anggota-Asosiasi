/**
 * Certificate tab functionality
 * Path: admin/js/certificate-script.js
 */

jQuery(document).ready(function($) {
    // Test directory functionality
    $('.test-directory').on('click', function() {
        var $button = $(this);
        var folder = $('#temp_folder').val();
        
        $button.prop('disabled', true);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'test_temp_directory',
                folder: folder,
                _ajax_nonce: asosiasi_certificate.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data);
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert(asosiasi_certificate.test_error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });

    // Cleanup temp files functionality
    $('.cleanup-temp-files').on('click', function() {
        var $button = $(this);
        
        $button.prop('disabled', true);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'cleanup_temp_files',
                _ajax_nonce: asosiasi_certificate.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data);
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert(asosiasi_certificate.cleanup_error);
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });
});