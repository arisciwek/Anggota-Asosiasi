(function($) {
    'use strict';

    // Search functionality
    function initSearch() {
        $('.asosiasi-search-input').on('keyup', function() {
            const searchTerm = $(this).val().toLowerCase();
            
            if ($('.asosiasi-member-grid').length) {
                // Grid layout
                $('.asosiasi-member-card').each(function() {
                    const text = $(this).text().toLowerCase();
                    const hasMatch = text.indexOf(searchTerm) > -1;
                    $(this).toggle(hasMatch);
                    
                    if (hasMatch) {
                        // Highlight matching service tags
                        $(this).find('.service-tag').each(function() {
                            const tagText = $(this).text().toLowerCase();
                            if (tagText.indexOf(searchTerm) > -1) {
                                $(this).addClass('highlight');
                            } else {
                                $(this).removeClass('highlight');
                            }
                        });
                    }
                });
            } else {
                // List layout
                $('.asosiasi-member-item').each(function() {
                    const text = $(this).text().toLowerCase();
                    const hasMatch = text.indexOf(searchTerm) > -1;
                    $(this).toggle(hasMatch);
                    
                    if (hasMatch) {
                        // Highlight matching service tags
                        $(this).find('.service-tag').each(function() {
                            const tagText = $(this).text().toLowerCase();
                            if (tagText.indexOf(searchTerm) > -1) {
                                $(this).addClass('highlight');
                            } else {
                                $(this).removeClass('highlight');
                            }
                        });
                    }
                });
            }

            // Show/hide no results message
            const visibleItems = $('.asosiasi-member-card:visible, .asosiasi-member-item:visible').length;
            if (visibleItems === 0) {
                if (!$('.asosiasi-no-results').length) {
                    const message = $('<p>', {
                        class: 'asosiasi-no-results',
                        text: asosiasiAjax.noResultsText || 'Tidak ada hasil yang ditemukan.'
                    });
                    $('.asosiasi-member-grid, .asosiasi-member-list-items').after(message);
                }
            } else {
                $('.asosiasi-no-results').remove();
            }
        });
    }

    // Initialize tooltips for service tags
    function initTooltips() {
        $('.service-tag').each(function() {
            const $tag = $(this);
            const fullName = $tag.attr('title');
            
            $tag.hover(
                function() {
                    const tooltip = $('<div>', {
                        class: 'service-tooltip',
                        text: fullName
                    });
                    $tag.append(tooltip);
                    
                    // Position tooltip
                    const tagOffset = $tag.offset();
                    const tooltipWidth = tooltip.outerWidth();
                    const windowWidth = $(window).width();
                    
                    if (tagOffset.left + tooltipWidth > windowWidth) {
                        tooltip.css('right', '0');
                    }
                },
                function() {
                    $(this).find('.service-tooltip').remove();
                }
            );
        });
    }

    // Animate items on load
    function animateItems() {
        $('.asosiasi-member-item, .asosiasi-member-card').css({
            'opacity': 0,
            'transform': 'translateY(20px)'
        }).each(function(i) {
            $(this).delay(i * 100).animate({
                'opacity': 1,
                'transform': 'translateY(0)'
            }, 500);
        });
    }

    // Filter by service tag
    function initServiceTagFilter() {
        $('.service-tag').click(function(e) {
            e.preventDefault();
            const searchInput = $('.asosiasi-search-input');
            const tagText = $(this).text().trim();
            
            // Toggle search
            if (searchInput.val() === tagText) {
                searchInput.val('').trigger('keyup');
            } else {
                searchInput.val(tagText).trigger('keyup');
            }
        });
    }

    // Add additional CSS styles dynamically
    function addDynamicStyles() {
        const styles = `
            .service-tooltip {
                position: absolute;
                bottom: 100%;
                left: 50%;
                transform: translateX(-50%);
                padding: 5px 10px;
                background: #333;
                color: #fff;
                font-size: 12px;
                border-radius: 4px;
                white-space: nowrap;
                margin-bottom: 5px;
                z-index: 100;
            }
            
            .service-tooltip:after {
                content: '';
                position: absolute;
                top: 100%;
                left: 50%;
                margin-left: -5px;
                border-width: 5px;
                border-style: solid;
                border-color: #333 transparent transparent transparent;
            }
            
            .service-tag.highlight {
                background-color: #ffd700;
                color: #333;
            }
            
            .service-tag {
                cursor: pointer;
            }
            
            .service-tag:active {
                transform: scale(0.95);
            }
        `;
        
        $('<style>').text(styles).appendTo('head');
    }

    // Initialize when document is ready
    $(document).ready(function() {
        addDynamicStyles();
        initSearch();
        initTooltips();
        initServiceTagFilter();
        animateItems();

        // Handle window resize for tooltips
        let resizeTimer;
        $(window).resize(function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                $('.service-tooltip').remove();
            }, 250);
        });
    });

})(jQuery);