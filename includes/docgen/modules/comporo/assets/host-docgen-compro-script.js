/**
 * Host DocGen Company Profile Module Scripts
 *
 * @package     Host_DocGen
 * @subpackage  Modules/Compro/Assets
 * @version     1.0.0
 * 
 * Description:
 * Module-specific scripts untuk Company Profile page.
 * Menangani form submission dan generasi dokumen.
 * 
 * Filename Convention:
 * - Original  : host-docgen-compro-script.js
 * - To Change : [plugin-name]-docgen-[module-name]-script.js
 * 
 * Path: modules/compro/assets/js/host-docgen-compro-script.js
 * Timestamp: 2024-11-29 10:50:00
 * 
 * Dependencies:
 * - jQuery
 * - WordPress AJAX
 * - host-docgen-script.js (global scripts)
 * 
 * Global Objects:
 * - hostDocGenCompro: Localized script data from PHP
 * 
 * @author     Host Developer
 * @copyright  2024 Host Organization
 * @license    GPL-2.0+
 */

jQuery(document).ready(function($) {
    // Handle JSON generation
    $('#generate-json').on('click', function(e) {
        e.preventDefault();
        
        const $button = $(this);
        const $spinner = $button.find('.spinner');
        const $result = $('#json-result');
        
        // Disable button and show spinner
        $button.prop('disabled', true);
        $spinner.addClass('is-active');
        $result.hide();
        
        // Send AJAX request
        $.ajax({
            url: hostDocGenCompro.ajaxUrl,
            type: 'POST',
            data: {
                action: 'generate_compro',
                source: 'json',
                _ajax_nonce: hostDocGenCompro.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#download-json')
                        .attr('href', response.data.url)
                        .attr('download', response.data.file);
                    $result.fadeIn();
                } else {
                    alert(response.data || hostDocGenCompro.strings.generateError);
                }
            },
            error: function() {
                alert(hostDocGenCompro.strings.generateError);
            },
            complete: function() {
                $button.prop('disabled', false);
                $spinner.removeClass('is-active');
            }
        });
    });
    
    // Handle form submission
    $('#compro-form').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $button = $form.find('button[type="submit"]');
        const $spinner = $button.find('.spinner');
        const $result = $('#form-result');
        
        // Disable button and show spinner
        $button.prop('disabled', true);
        $spinner.addClass('is-active');
        $result.hide();
        
        // Get form data including wp_editor content
        const formData = $form.serializeArray();
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get('description')) {
            formData.push({
                name: 'description',
                value: tinyMCE.get('description').getContent()
            });
        }
        
        // Send AJAX request
        $.ajax({
            url: hostDocGenCompro.ajaxUrl,
            type: 'POST',
            data: {
                action: 'generate_compro',
                source: 'form',
                form_data: $.param(formData),
                _ajax_nonce: hostDocGenCompro.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#download-form')
                        .attr('href', response.data.url)
                        .attr('download', response.data.file);
                    $result.fadeIn();
                    
                    // Optional: Reset form after successful generation
                    if (typeof tinyMCE !== 'undefined' && tinyMCE.get('description')) {
                        tinyMCE.get('description').setContent('');
                    }
                    $form[0].reset();
                } else {
                    alert(response.data || hostDocGenCompro.strings.generateError);
                }
            },
            error: function() {
                alert(hostDocGenCompro.strings.generateError);
            },
            complete: function() {
                $button.prop('disabled', false);
                $spinner.removeClass('is-active');
            }
        });
    });
    
    // Enhance form UX
    $('input[required], textarea[required]').on('input', function() {
        $(this).toggleClass('invalid', !this.checkValidity());
    });
});

