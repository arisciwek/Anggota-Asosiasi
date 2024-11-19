/**
 * SKP operations handlers
 * 
 * @package Asosiasi
 * @version 2.2.0
 * Path: assets/js/skp-operations.js
 * 
 * Changelog:
 * 2.2.0 - 2024-11-17
 * - Added status change functionality
 * - Enhanced form validation
 * - Improved modal handling
 * 2.1.0 - Added file size validation
 * 2.0.0 - Initial SKP operations
 */

(function($) {
    'use strict';

    // Modal management
    let currentSkpType = '';

    function openModal(type) {
        currentSkpType = type;
        $('#skp_type').val(type);
        $('#modal-title').text(type === 'company' ? 'Add Company SKP' : 'Add Expert SKP');
        $('#skp-modal').show();
        resetForm();
    }

    function closeModal() {
        $('#skp-modal').hide();
        resetForm();
    }

    function resetForm() {
        $('#skp-form')[0].reset();
        $('.error-message').remove();
    }

    // Form validation
    function validateForm() {
        $('.error-message').remove();
        let isValid = true;

        const issueDate = new Date($('#issue_date').val());
        const expiryDate = new Date($('#expiry_date').val());

        if (expiryDate <= issueDate) {
            $('#expiry_date').after('<span class="error-message">' + skpData.strings.errorDateValid + '</span>');
            isValid = false;
        }

        const fileInput = $('#pdf_file')[0];
        if (fileInput.files.length > 0) {
            const fileSize = fileInput.files[0].size / 1024 / 1024; // Size in MB
            if (fileSize > 5) {
                $('#pdf_file').after('<span class="error-message">' + skpData.strings.errorFileSize + '</span>');
                isValid = false;
            }
        }

        return isValid;
    }

    // AJAX operations
    function loadSkpList(type) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_skp_list',
                member_id: skpData.memberID,
                skp_type: type,
                nonce: skpData.nonce
            },
            success: function(response) {
                if (response.success) {
                    const targetId = type === 'company' ? '#company-skp-list' : '#expert-skp-list';
                    $(targetId).html(response.data);
                } else {
                    console.error('Error loading SKP list:', response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
            }
        });
    }

    // Event handlers
    $(document).ready(function() {
        // Open modal button click
        $('.add-skp-btn').on('click', function() {
            openModal($(this).data('type'));
        });

        // Close modal click
        $('.skp-modal-close').on('click', closeModal);
        $(window).on('click', function(event) {
            if ($(event.target).hasClass('skp-modal')) {
                closeModal();
            }
        });

        // Form submission
        $('#skp-form').on('submit', function(e) {
            e.preventDefault();
            if (!validateForm()) return;

            const formData = new FormData(this);
            formData.append('action', 'save_skp');
            formData.append('nonce', skpData.nonce);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        closeModal();
                        loadSkpList(currentSkpType);
                    } else {
                        alert(response.data.message || skpData.strings.errorServer);
                    }
                },
                error: function() {
                    alert(skpData.strings.errorServer);
                }
            });
        });

        // Delete SKP
        $(document).on('click', '.delete-skp', function(e) {
            e.preventDefault();
            if (!confirm(skpData.strings.confirmDelete)) return;

            const skpId = $(this).data('id');
            const type = $(this).data('type');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_skp',
                    skp_id: skpId,
                    nonce: skpData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        loadSkpList(type);
                    } else {
                        alert(response.data.message || skpData.strings.errorServer);
                    }
                },
                error: function() {
                    alert(skpData.strings.errorServer);
                }
            });
        });

        // Initial load
        loadSkpList('company');
        loadSkpList('expert');
    });

})(jQuery);