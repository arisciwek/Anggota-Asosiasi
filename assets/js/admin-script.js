/**
* Global admin functionality for Asosiasi
*
* @package Asosiasi
* @version 2.1.0
* Path: assets/js/admin-script.js
* 
* Changelog:
* 2.1.0 - 2024-03-14
* - Enhanced service checkbox handling
* - Added form validation 
* - Improved search filtering
* 2.0.0 - Initial admin functionality
*/

jQuery(document).ready(function($) {
    // Handle service checkboxes
    $('.service-checkbox input[type="checkbox"]').on('change', function() {
        var checkedServices = $('.service-checkbox input[type="checkbox"]:checked').length;
        if (checkedServices > 0) {
            $('#submit_member').prop('disabled', false);
        }
    });

    // Confirm delete action
    $('.button-link-delete').on('click', function(e) {
        if (!confirm(asosiasiAdmin.deleteConfirmText)) {
            e.preventDefault();
            return false;
        }
    });

    // Search filter for services
    $('#service-search').on('keyup', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('.service-checkbox').each(function() {
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(searchTerm) > -1);
        });
    });

    // Toggle all services
    $('#toggle-all-services').on('click', function() {
        var isChecked = $(this).prop('checked');
        $('.service-checkbox input[type="checkbox"]').prop('checked', isChecked);
    });

    // Form validation
    $('#member-form').on('submit', function(e) {
        var required = ['company_name', 'contact_person', 'email'];
        var hasError = false;

        required.forEach(function(field) {
            var $field = $('#' + field);
            var $parent = $field.parent('td');
            
            if (!$field.val()) {
                hasError = true;
                $parent.addClass('form-invalid');
                if (!$parent.find('.error-message').length) {
                    $parent.append('<span class="error-message" style="color: #dc3232;">This field is required</span>');
                }
            } else {
                $parent.removeClass('form-invalid');
                $parent.find('.error-message').remove();
            }
        });

        if (hasError) {
            e.preventDefault();
            return false;
        }
    });

    // Initialize tooltips if available
    if ($.fn.tooltip) {
        $('.service-tag').tooltip({
            position: {
                my: "center bottom-10",
                at: "center top"
            }
        });
    }
});
