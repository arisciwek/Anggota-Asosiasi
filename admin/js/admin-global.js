/**
 * Global JavaScript functionality for Asosiasi plugin admin
 */
(function($) {
    'use strict';

    // Confirm delete actions
    $('.button-link-delete').on('click', function(e) {
        if (!confirm(asosiasiAdmin.confirmDelete)) {
            e.preventDefault();
            return false;
        }
    });

    // Make notices dismissible
    $('.notice.is-dismissible').each(function() {
        var $notice = $(this);
        
        // Add dismiss button if not exists
        if (!$notice.find('.notice-dismiss').length) {
            $notice.append('<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice</span></button>');
        }
        
        // Handle dismiss click
        $notice.find('.notice-dismiss').on('click', function() {
            $notice.fadeOut(300, function() { 
                $(this).remove(); 
            });
        });
    });

    // Init tooltips for service tags if present
    if ($('.service-tag').length) {
        $('.service-tag').hover(
            function() {
                var $tag = $(this);
                if ($tag.attr('title')) {
                    var tooltip = $('<div class="tooltip">' + $tag.attr('title') + '</div>');
                    $tag.append(tooltip);
                }
            },
            function() {
                $(this).find('.tooltip').remove();
            }
        );
    }

    // Add tooltip styles dynamically
    $('<style>')
        .text(
            '.tooltip {' +
            '    position: absolute;' +
            '    bottom: 100%;' +
            '    left: 50%;' +
            '    transform: translateX(-50%);' +
            '    padding: 5px 8px;' +
            '    background: #333;' +
            '    color: #fff;' +
            '    font-size: 12px;' +
            '    border-radius: 3px;' +
            '    white-space: nowrap;' +
            '    margin-bottom: 5px;' +
            '    z-index: 100;' +
            '}' +
            '.tooltip:after {' +
            '    content: "";' +
            '    position: absolute;' +
            '    top: 100%;' +
            '    left: 50%;' +
            '    margin-left: -5px;' +
            '    border-width: 5px;' +
            '    border-style: solid;' +
            '    border-color: #333 transparent transparent;' +
            '}'
        )
        .appendTo('head');

})(jQuery);


