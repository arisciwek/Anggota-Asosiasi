/**
 * Utility functions untuk SKP Perusahaan
 *
 * @package Asosiasi
 * @version 1.0.1
 * Path: assets/js/skp-perusahaan/skp-perusahaan-utils.js
 * 
 * Changelog:
 * 1.0.1 - 2024-11-19
 * - Changed asosiasiAdmin references to asosiasiSKPPerusahaan
 * - Added safety checks for strings object
 * - Added default values for all string references
 * 1.0.0 - Initial version
 */

var AsosiasiSKPUtils = {
    /**
     * Escape HTML characters
     * @param {string} str String to escape
     * @returns {string} Escaped string
     */
    escapeHtml: function(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    },

    /**
     * Show notice message
     * @param {string} type Notice type (success/error)
     * @param {string} message Message to display
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
     * @param {string} currentStatus Current SKP status
     * @returns {Array} Array of available status options
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
     * @param {string} date Date string
     * @returns {string} Formatted date
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
     * @param {string} datetime Datetime string
     * @returns {string} Formatted time
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
     * @param {jQuery} $form Form element
     * @returns {boolean} Validation result
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
     * @returns {string|null} Member ID or null
     */
    getMemberId: function() {
        return $('#member_id').val() || 
               new URLSearchParams(window.location.search).get('id');
    },

    /**
     * Reload all tabs
     * @param {boolean} activeFirst Load active tab first
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
