/**
 * Global DocGen JavaScript
 *
 * @package     Asosiasi
 * @subpackage  DocGen
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: modules/assets/js/asosiasi-docgen-script.js
 * 
 * Description: Global functions untuk document generation
 *              dan utilities yang digunakan di semua modul.
 */

const AsosiasiDocGen = {
    /**
     * Initialize DocGen functionality
     */
    init() {
        this.initGenerateHandlers();
    },

    /**
     * Setup handlers untuk generate buttons
     */
    initGenerateHandlers() {
        // Handle loading state
        this.handleLoadingState();
        
        // Handle success/error messages
        this.handleMessages();
    },

    /**
     * Handle loading state untuk buttons
     */
    handleLoadingState() {
        jQuery(document).on('docgen:generating', '.generate-doc-button', function() {
            const $button = jQuery(this);
            const $spinner = $button.find('.spinner');
            
            $button.prop('disabled', true);
            $spinner.addClass('is-active');
        });

        jQuery(document).on('docgen:complete', '.generate-doc-button', function() {
            const $button = jQuery(this);
            const $spinner = $button.find('.spinner');
            
            $button.prop('disabled', false);
            $spinner.removeClass('is-active');
        });
    },

    /**
     * Handle success/error messages
     */
    handleMessages() {
        // Success message
        jQuery(document).on('docgen:success', function(e, data) {
            const $result = jQuery('#generation-result-' + data.source);
            const $download = $result.find('#download-profile-' + data.source);
            
            $download
                .attr('href', data.url)
                .attr('download', data.file);
            $result.fadeIn();
        });

        // Error message
        jQuery(document).on('docgen:error', function(e, error) {
            alert(error);
        });
    },

    /**
     * Generate document via AJAX
     * @param {Object} data Form data
     * @param {string} source Data source (json/form)
     * @return {Promise}
     */
    generateDocument(data, source) {
        return new Promise((resolve, reject) => {
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'generate_company_profile',
                    source: source,
                    _ajax_nonce: data.nonce,
                    form_data: data.formData
                },
                success: function(response) {
                    if (response.success) {
                        resolve(response.data);
                    } else {
                        reject(response.data || 'Error generating document');
                    }
                },
                error: function() {
                    reject('Server error occurred');
                }
            });
        });
    }
};

// Initialize quando document ready
jQuery(document).ready(function() {
    AsosiasiDocGen.init();
});

