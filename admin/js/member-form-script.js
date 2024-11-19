/**
 * Script form member
 * 
 * @package Asosiasi
 * @version 2.1.0
 * Path: admin/js/member-form-script.js
 * 
 * Changelog:
 * 2.1.0 - 2024-11-19
 * - Extracted from form-script.js
 * - Optimized for member form handling
 * - Improved validation and error handling
 */

(function($) {
    'use strict';

    // Fungsi untuk menginisialisasi form member
    function initMemberForm() {
        initFormValidation();
        initServiceSelection();
        initRequiredFields();
    }

    // Validasi form member
    function initFormValidation() {
        $('#member-form').on('submit', function(e) {
            var hasError = false;
            var firstError = null;

            // Cek field required
            $('.required-field').each(function() {
                var $field = $(this);
                var $parent = $field.parents('td').first();
                var $error = $parent.find('.error-message');

                if (!$field.val().trim()) {
                    hasError = true;
                    
                    if (!firstError) {
                        firstError = $field;
                    }

                    $parent.addClass('form-invalid');
                    if (!$error.length) {
                        $parent.append('<span class="error-message">' + 
                            ($field.attr('data-error') || 'Field ini harus diisi') + 
                        '</span>');
                    }
                } else {
                    $parent.removeClass('form-invalid');
                    $error.remove();
                }
            });

            // Validasi email
            var $email = $('#email');
            if ($email.length && $email.val()) {
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test($email.val())) {
                    hasError = true;
                    var $parent = $email.parents('td').first();
                    $parent.addClass('form-invalid');
                    if (!$parent.find('.error-message').length) {
                        $parent.append('<span class="error-message">Format email tidak valid</span>');
                    }
                    if (!firstError) {
                        firstError = $email;
                    }
                }
            }

            if (hasError) {
                e.preventDefault();
                if (firstError) {
                    firstError.focus();
                }
                return false;
            }
        });
    }

    // Inisialisasi pemilihan layanan
    function initServiceSelection() {
        var $servicesList = $('.services-checkbox-list');
        
        // Toggle semua layanan
        if ($servicesList.length > 4) { // Tampilkan hanya jika ada banyak layanan
            var $toggleAll = $('<label class="toggle-all-services">' +
                '<input type="checkbox"> Pilih Semua Layanan</label>');
            
            $servicesList.prepend($toggleAll);
            
            $toggleAll.find('input').on('change', function() {
                var isChecked = $(this).prop('checked');
                $servicesList.find('input[type="checkbox"]').not(this).prop('checked', isChecked);
            });
        }

        // Update status "Pilih Semua" saat checkbox individual berubah
        $servicesList.on('change', 'input[type="checkbox"]', function() {
            var $toggle = $('.toggle-all-services input');
            if ($toggle.length) {
                var totalCheckboxes = $servicesList.find('input[type="checkbox"]').not($toggle).length;
                var checkedCheckboxes = $servicesList.find('input[type="checkbox"]:checked').not($toggle).length;
                $toggle.prop('checked', totalCheckboxes === checkedCheckboxes);
            }
        });
    }

    // Tandai field yang required
    function initRequiredFields() {
        $('input[required], select[required], textarea[required]').each(function() {
            $(this).addClass('required-field');
            var $label = $('label[for="' + $(this).attr('id') + '"]');
            if ($label.length && !$label.find('.required').length) {
                $label.append('<span class="required">*</span>');
            }
        });
    }

    // Inisialisasi saat dokumen siap
    $(document).ready(function() {
        try {
            initMemberForm();
        } catch (error) {
            console.error('Error initializing member form:', error);
        }
    });

})(jQuery);
