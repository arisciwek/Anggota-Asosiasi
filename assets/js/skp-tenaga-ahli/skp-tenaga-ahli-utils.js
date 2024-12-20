/**
 * Utility functions untuk SKP Tenaga Ahli
 *
 * @package     Asosiasi
 * @subpackage  Assets/JS/SKP_Tenaga_Ahli
 * @version     1.0.0
 * @author      arisciwek
 *
 * Description: Fungsi-fungsi utility yang digunakan di SKP Tenaga Ahli
 */

var AsosiasiSKPTenagaAhliUtils = (function($) {
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
                            ${asosiasiSKPTenagaAhli.strings.dismissNotice || 'Tutup notifikasi'}
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
                            label: asosiasiSKPTenagaAhli.strings.statusInactive || 'Tidak Aktif'
                        },
                        { 
                            value: 'expired', 
                            label: asosiasiSKPTenagaAhli.strings.statusExpired || 'Kadaluarsa'
                        }
                    ];
                case 'inactive':
                    return [
                        { 
                            value: 'activated', 
                            label: asosiasiSKPTenagaAhli.strings.statusActivated || 'Diaktifkan'
                        }
                    ];
                case 'expired':
                    return [
                        { 
                            value: 'activated', 
                            label: asosiasiSKPTenagaAhli.strings.statusActivated || 'Diaktifkan'
                        }
                    ];
                case 'activated':
                    return [
                        { 
                            value: 'inactive', 
                            label: asosiasiSKPTenagaAhli.strings.statusInactive || 'Tidak Aktif'
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
                    AsosiasiSKPTenagaAhliUtils.showNotice('error', 
                        asosiasiSKPTenagaAhli.strings.fieldRequired ?
                        asosiasiSKPTenagaAhli.strings.fieldRequired.replace('%s', label) :
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
         * Get status class from label
         */
        getStatusClassFromLabel: function(label) {
            label = label.toLowerCase();
            const statusMap = {
                'aktif': 'skp-status-active',
                'active': 'skp-status-active',
                'diaktifkan': 'skp-status-active', 
                'activated': 'skp-status-active',
                'tidak aktif': 'skp-status-inactive',
                'inactive': 'skp-status-inactive',
                'kadaluarsa': 'skp-status-expired',
                'expired': 'skp-status-expired'
            };
            return statusMap[label] || 'skp-status-inactive';
        },

        /**
         * Reload all tables
         */
        reloadAllTables: function() {
            AsosiasiSKPTenagaAhli.reloadTable(null, 'active');
            setTimeout(() => {
                AsosiasiSKPTenagaAhli.reloadTable(null, 'inactive');
            }, 300);
        }
    };
})(jQuery);

