/**
 * Member view page JavaScript
 *
 * @package Asosiasi  
 * @version 2.1.0
 * Path: admin/js/view-member-script.js
 * 
 * Changelog:
 * 2.1.0 - 2024-03-14
 * - Added keyboard accessibility for service tags
 * - Enhanced delete confirmations
 * - Added auto-dismiss notices
 * 2.0.0 - Initial member view functionality
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Handle delete confirmation
        $('.button-link-delete').on('click', function(e) {
            if (!confirm(asosiasiViewMember.confirmDelete)) {
                e.preventDefault();
                return false;
            }
        });

        // Handle dismissible notices
        $('.notice.is-dismissible').each(function() {
            var $notice = $(this);
            setTimeout(function() {
                $notice.fadeOut();
            }, 3000);
        });

        // Keyboard accessibility for service tags
        $('.service-tag').on('keypress', function(e) {
            if (e.which === 13 || e.which === 32) { // Enter or Space
                $(this).find('.service-tooltip').toggle();
            }
        });
    });
})(jQuery);