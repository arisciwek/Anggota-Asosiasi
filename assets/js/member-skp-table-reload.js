/**
 * Handler untuk reload tabel SKP di halaman member view
 *
 * @package Asosiasi
 * @version 1.1.0
 * Path: assets/js/member-skp-table-reload.js
 * 
 * Changelog:
 * 1.1.0 - 2024-11-16
 * - Added support for active/inactive SKP tabs
 * - Modified reload behavior to respect active tab
 * - Added tab state persistence
 * - Improved error handling for tab-specific loads
 * 
 * 1.0.3 - 2024-03-20
 * - Modified to handle all page load scenarios
 * - Removed form_success dependency
 * - Improved initialization timing
 */

(function($) {
    'use strict';
    
    // Initialize reload handler
    function initMemberSKPTableReload() {
        if ($('#skp-perusahaan-section').length === 0) {
            return;
        }

        // Initialize tabs if present
        initTabs();

        // Wait for all components to be fully loaded
        $(window).on('load', function() {
            var memberId = getMemberId();
            if (memberId) {
                // Get active tab from localStorage or default to 'active'
                var activeTab = localStorage.getItem('asosiasi_skp_active_tab') || 'active';
                switchToTab(activeTab);
                reloadMemberSKPTable(memberId, activeTab);
            }
        });
    }

    // Initialize tab functionality
    function initTabs() {
        $('.skp-tabs .nav-tab').on('click', function(e) {
            e.preventDefault();
            var tab = $(this).data('tab');
            switchToTab(tab);
            
            var memberId = getMemberId();
            if (memberId) {
                reloadMemberSKPTable(memberId, tab);
            }
        });
    }

    // Switch to specified tab
    function switchToTab(tab) {
        // Update tab UI
        $('.nav-tab').removeClass('nav-tab-active');
        $('.nav-tab[data-tab="' + tab + '"]').addClass('nav-tab-active');
        
        // Update content visibility
        $('.tab-pane').removeClass('active');
        $('#skp-' + tab).addClass('active');
        
        // Store selected tab
        localStorage.setItem('asosiasi_skp_active_tab', tab);
    }

    // Get member ID from various sources
    function getMemberId() {
        var memberId = $('#member_id').val();
        
        if (!memberId) {
            var urlParams = new URLSearchParams(window.location.search);
            memberId = urlParams.get('id');
        }
        
        return memberId;
    }

    // Main reload function
    function reloadMemberSKPTable(memberId, activeTab = 'active') {
        if (!memberId) {
            return;
        }

        // Check for SKP module availability
        if (typeof AsosiasiSKP === 'undefined' || typeof AsosiasiSKP.reloadTable !== 'function') {
            setTimeout(function() {
                reloadMemberSKPTable(memberId, activeTab);
            }, 500);
            return;
        }

        // Call reload with tab context
        AsosiasiSKP.reloadTable(memberId, activeTab);
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initMemberSKPTableReload();
    });

})(jQuery);
