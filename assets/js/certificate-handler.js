/**
* Certificate generation & download handler
*
* @package Asosiasi
* @version 1.0.0
* Path: assets/js/certificate-handler.js
* 
* Changelog:
* 1.0.0 - 2024-11-22
* - Initial release
* - Added generate certificate handler
* - Added download functionality
* - Added progress indicators
*/

(function($) {
   'use strict';
   
   // Initialize handler
   function initCertificateHandler() {
       $('.generate-certificate').on('click', handleGenerateCertificate);
   }

   // Handle certificate generation
   function handleGenerateCertificate(e) {
       e.preventDefault();

       const $button = $(this);
       const memberId = $button.data('member');
       const nonce = $button.data('nonce');

       if (!memberId) {
           showError(asosiasiCertificate.strings.invalidMember);
           return;
       }

       // Disable button & show loading
       $button.prop('disabled', true)
              .find('.dashicons')
              .removeClass('dashicons-download')
              .addClass('dashicons-update rotating');

       // Generate certificate
       $.ajax({
           url: asosiasiCertificate.ajaxurl,
           type: 'POST',
           data: {
               action: 'generate_member_certificate',
               member_id: memberId,
               nonce: nonce
           },
           success: function(response) {
               if (response.success) {
                   showSuccess(response.data.message);
                   downloadFile(response.data.file_url);
               } else {
                   showError(response.data.message);
               }
           },
           error: function() {
               showError(asosiasiCertificate.strings.errorGenerate);
           },
           complete: function() {
               // Reset button state
               $button.prop('disabled', false)
                      .find('.dashicons')
                      .removeClass('dashicons-update rotating')
                      .addClass('dashicons-download');
           }
       });
   }

   // Download generated file
   function downloadFile(url) {
       const $iframe = $('<iframe>', {
           src: url,
           style: 'display: none'
       }).appendTo('body');

       setTimeout(function() {
           $iframe.remove();
       }, 5000);
   }

   // Show success message
   function showSuccess(message) {
       const notice = $(`
           <div class="notice notice-success is-dismissible">
               <p>${message}</p>
               <button type="button" class="notice-dismiss">
                   <span class="screen-reader-text">${asosiasiCertificate.strings.dismiss}</span>
               </button>
           </div>
       `);

       addNotice(notice);
   }

   // Show error message
   function showError(message) {
       const notice = $(`
           <div class="notice notice-error is-dismissible">
               <p>${message}</p>
               <button type="button" class="notice-dismiss">
                   <span class="screen-reader-text">${asosiasiCertificate.strings.dismiss}</span>
               </button>
           </div>
       `);

       addNotice(notice);
   }

   // Add notice to page
   function addNotice($notice) {
       $('.wrap > h1').after($notice);

       // Auto dismiss after 5 seconds
       setTimeout(function() {
           $notice.fadeOut(300, function() {
               $(this).remove();
           });
       }, 5000);

       // Handle dismiss button
       $notice.on('click', '.notice-dismiss', function() {
           $notice.fadeOut(300, function() {
               $(this).remove();
           });
       });
   }

   // Initialize when document is ready
   $(document).ready(function() {
       initCertificateHandler();
   });

})(jQuery);