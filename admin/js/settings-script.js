/**
 * Settings page functionality
 *
 * @package Asosiasi
 * @version 2.1.0
 * Path: admin/js/settings-script.js
 * 
 * Changelog:
 * 2.1.0 - 2024-11-19
 * - Initial version extracted from admin-global.js
 * - Added settings specific functionality
 */

(function($) {
    'use strict';

    // Settings page initialization
    function initSettings() {
        initFormHandling();
        initServiceManagement();
        initPermissionsMatrix();
    }

    // Form handling
    function initFormHandling() {
        $('#asosiasi-settings-form').on('submit', function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $submitButton = $form.find('button[type="submit"]');
            
            $submitButton.prop('disabled', true);
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: $form.serialize(),
                success: function(response) {
                    if (response.success) {
                        AsosiasiUtils.showNotice('success', asosiasiSettings.strings.saved);
                    } else {
                        AsosiasiUtils.showNotice('error', asosiasiSettings.strings.saveError);
                    }
                },
                error: function() {
                    AsosiasiUtils.showNotice('error', asosiasiSettings.strings.saveError);
                },
                complete: function() {
                    $submitButton.prop('disabled', false);
                }
            });
        });
    }

    // Service management
    function initServiceManagement() {
        $('.delete-service').on('click', function(e) {
            if (!confirm(asosiasiSettings.strings.confirmDelete)) {
                e.preventDefault();
                return false;
            }
        });
    }

    // Permissions matrix
    function initPermissionsMatrix() {
        $('.permissions-matrix input[type="checkbox"]').on('change', function() {
            // Handle permission changes
        });
    }

    // Initialize when document is ready
    $(document).ready(function() {
        if ($('#asosiasi-settings-page').length) {
            initSettings();
        }
    });

})(jQuery);
