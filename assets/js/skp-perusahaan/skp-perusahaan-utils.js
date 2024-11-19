/**
 * Utility functions untuk SKP Perusahaan
 *
 * @package Asosiasi
 * @version 1.0.2
 * Path: assets/js/skp-perusahaan/skp-perusahaan-utils.js
 * 
 * Changelog:
 * 1.0.2 - 2024-11-19
 * - Fixed jQuery dependency wrapping
 * - Separated jQuery dependent utils
 * - Added proper IIFE pattern
 * 1.0.1 - Added string safety checks
 * 1.0.0 - Initial version
 */

var AsosiasiSKPUtils = (function($) {
    'use strict';
    
    return {
        /**
         * Escape HTML characters
         */
        escapeHtml: function(str) {
            if (!str) return '';
            const div = document.createElement('div');
            div.textContent = str;
            return div.innerHTML;
        },

        /**
         * Show notice message
         */
        showNotice: function(type, message) {
            const notice = $(`
                <div class="notice notice-${type} is-dismissible">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">
                            ${asosiasiSKPPerusahaan.strings.dismissNotice || 'Tutup notifikasi'}
                        </span>
                    </button>
                </div>
            `);

            $('.wrap > h1').after(notice);

            // Auto dismiss after 5 seconds
            setTimeout(function() {
                notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);

            // Dismiss button handler
            notice.find('.notice-dismiss').on('click', function() {
                notice.fadeOut(300, function() {
                    $(this).remove();
                });
            });
        },

        /**
         * Get available status options
         */
        getAvailableStatuses: function(currentStatus) {
            switch (currentStatus) {
                case 'active':
                    return [
                        { 
                            value: 'inactive', 
                            label: asosiasiSKPPerusahaan.strings.statusInactive || 'Tidak Aktif'
                        },
                        { 
                            value: 'expired', 
                            label: asosiasiSKPPerusahaan.strings.statusExpired || 'Kadaluarsa'
                        }
                    ];
                case 'inactive':
                    return [
                        { 
                            value: 'activated', 
                            label: asosiasiSKPPerusahaan.strings.statusActivated || 'Diaktifkan'
                        }
                    ];
                case 'expired':
                    return [
                        { 
                            value: 'activated', 
                            label: asosiasiSKPPerusahaan.strings.statusActivated || 'Diaktifkan'
                        }
                    ];
                case 'activated':
                    return [
                        { 
                            value: 'inactive', 
                            label: asosiasiSKPPerusahaan.strings.statusInactive || 'Tidak Aktif'
                        }
                    ];
                default:
                    return [];
            }
        },

        /**
         * Format date to local format
         */
        formatDate: function(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },

        /**
         * Format time to local format
         */
        formatTime: function(datetime) {
            if (!datetime) return '';
            return new Date(datetime).toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        /**
         * Validate required fields in form
         */
        validateForm: function($form) {
            let isValid = true;
            $form.find('[required]').each(function() {
                if (!$(this).val()) {
                    isValid = false;
                    const label = $(this).prev('label').text().replace('*', '').trim();
                    AsosiasiSKPUtils.showNotice('error', 
                        asosiasiSKPPerusahaan.strings.fieldRequired ?
                        asosiasiSKPPerusahaan.strings.fieldRequired.replace('%s', label) :
                        `Field ${label} wajib diisi`
                    );
                }
            });
            return isValid;
        },

        /**
         * Get member ID from various sources
         */
        getMemberId: function() {
            return $('#member_id').val() || 
                   new URLSearchParams(window.location.search).get('id');
        },

        /**
         * Reload all tabs
         */
        reloadAllTabs: function(activeFirst = true) {
            if (activeFirst) {
                AsosiasiSKPPerusahaan.reloadTable(null, 'active');
                setTimeout(() => AsosiasiSKPPerusahaan.reloadTable(null, 'inactive'), 300);
            } else {
                AsosiasiSKPPerusahaan.reloadTable(null, 'inactive');
                setTimeout(() => AsosiasiSKPPerusahaan.reloadTable(null, 'active'), 300);
            }
        }
    };
})(jQuery);
