/**
 * Script untuk tab sertifikat di settings
 * 
 * @package Asosiasi
 * @version 1.0.0
 * Path: admin/js/certificate-settings.js
 * 
 * Changelog:
 * 1.0.0 - 2024-11-21 18:15 WIB
 */

jQuery(document).ready(function($) {
    'use strict';

    // File input validation
    $('#template_file').on('change', function() {
        const file = this.files[0];
        const validTypes = ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        
        if (file && !validTypes.includes(file.type)) {
            this.value = '';
            alert(asosiasiSettings.strings.invalidFileType || 'Only DOCX files are allowed');
            return false;
        }

        const maxSize = 2 * 1024 * 1024; // 2MB
        if (file && file.size > maxSize) {
            this.value = '';
            alert(asosiasiSettings.strings.fileTooLarge || 'File size must not exceed 2MB');
            return false;
        }
    });

    // Form submission
    $('.template-upload-form').on('submit', function() {
        const $submitBtn = $(this).find('button[type="submit"]');
        $submitBtn.prop('disabled', true);
        return true;
    });
});