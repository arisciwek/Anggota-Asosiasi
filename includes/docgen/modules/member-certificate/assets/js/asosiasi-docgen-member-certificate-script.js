/**
 * Certificate Generation Script
 *
 * @package     Asosiasi
 * @subpackage  DocGen/Modules/Certificate/Assets
 */
// File: assets/js/asosiasi-docgen-member-certificate-script.js

jQuery(document).ready(function($) {
    // Handler untuk generate DOCX (kode yang sudah ada tetap sama)
    $('#generate-certificate').on('click', function(e) {
        e.preventDefault();
        
        const button = $(this);
        const memberId = button.data('member');
        const spinner = button.find('.spinner');
        
        // Disable button and show spinner
        button.prop('disabled', true);
        spinner.addClass('is-active');
        
        // Make AJAX request
        $.ajax({
            url: asosiasiDocGenCert.ajaxUrl,
            type: 'POST',
            data: {
                action: 'generate_member_certificate_docx',
                member_id: memberId,
                _ajax_nonce: asosiasiDocGenCert.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Create hidden iframe for download
                    const iframe = document.createElement('iframe');
                    iframe.style.display = 'none';
                    iframe.src = response.data.url;
                    document.body.appendChild(iframe);
                    
                    // Show success message
                    // alert(asosiasiDocGenCert.strings.generateSuccess);
                } else {
                    alert(asosiasiDocGenCert.strings.generateError + ': ' + response.data);
                }
            },
            error: function() {
                alert(asosiasiDocGenCert.strings.generateError);
            },
            complete: function() {
                // Re-enable button and hide spinner
                button.prop('disabled', false);
                spinner.removeClass('is-active');
            }
        });
    });


    // Handler untuk generate PDF - sekarang menggunakan pola yang sama
    $('#generate-pdf-certificate').on('click', function(e) {
        e.preventDefault();
        
        const button = $(this);
        const memberId = button.data('member');
        const spinner = button.find('.spinner');
        
        button.prop('disabled', true);
        spinner.addClass('is-active');

        $.ajax({
            url: asosiasiDocGenCert.ajaxUrl,
            type: 'POST',
            data: {
                action: 'generate_member_certificate_pdf',
                member_id: memberId,
                _ajax_nonce: asosiasiDocGenCert.nonce
            },
            success: function(response) {
                if (response.success && response.data.url) {
                    // Buat download link
                    const link = document.createElement('a');
                    link.href = response.data.url;
                    link.download = response.data.file;
                    link.style.display = 'none';
                    document.body.appendChild(link);
                    link.click();
                    setTimeout(() => {
                        document.body.removeChild(link);
                    }, 1000);
                    
                    alert(asosiasiDocGenCert.strings.pdfSuccess);
                } else {
                    alert(asosiasiDocGenCert.strings.pdfError);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                alert(asosiasiDocGenCert.strings.pdfError);
            },
            complete: function() {
                button.prop('disabled', false);
                spinner.removeClass('is-active');
            }
        });
    });

});

