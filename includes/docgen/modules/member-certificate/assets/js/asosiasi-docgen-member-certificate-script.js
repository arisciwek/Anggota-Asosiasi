/**
 * Certificate Generation Script
 *
 * @package     Asosiasi
 * @subpackage  DocGen/Modules/Certificate/Assets
 */

jQuery(document).ready(function($) {
    // Handle certificate generation
    $('#generate-certificate').on('click', function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const $spinner = $button.find('.spinner');
        
        // Disable button and show spinner
        $button.prop('disabled', true);
        $spinner.addClass('is-active');
        
        // Send AJAX request
        $.ajax({
            url: asosiasiDocGenCert.ajaxUrl,
            type: 'POST',
            data: {
                action: 'generate_member_certificate',
                member_id: $button.data('member'),
                _ajax_nonce: asosiasiDocGenCert.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Create temporary link and trigger download
                    const link = document.createElement('a');
                    link.href = response.data.url;
                    link.download = response.data.file;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    alert(response.data || asosiasiDocGenCert.strings.generateError);
                }
            },
            error: function() {
                alert(asosiasiDocGenCert.strings.generateError);
            },
            complete: function() {
                // Re-enable button and hide spinner
                $button.prop('disabled', false);
                $spinner.removeClass('is-active');
            }
        });
    });
});
