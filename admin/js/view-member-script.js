// admin/js/view-member-script.js
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