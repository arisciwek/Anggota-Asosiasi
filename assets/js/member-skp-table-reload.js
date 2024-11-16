/**
 * Handler untuk reload tabel SKP di halaman member view
 *
 * @package Asosiasi
 * @version 1.0.4
 * Path: assets/js/member-skp-table-reload.js
 * 
 * Changelog:
 * 1.0.4 - 2024-03-20
 * - Initial version synchronized with existing SKP module
 * - Added proper member ID detection
 * - Implemented safe reload mechanism
 */

(function($) {
    'use strict';
    
    // Initialize reload handler
    function initMemberSKPTableReload() {
        // Only run on view member page with SKP table
        if ($('#skp-perusahaan-section').length === 0) {
            return;
        }

        // Initialize after a small delay to ensure SKP module is loaded
        setTimeout(function() {
            var memberId = $('#member_id').val() || 
                          new URLSearchParams(window.location.search).get('id');
            
            if (memberId && typeof AsosiasiSKP !== 'undefined') {
                AsosiasiSKP.reloadTable(memberId);
            }
        }, 100);
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initMemberSKPTableReload();
    });

})(jQuery);