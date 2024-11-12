/**
 * SKP Perusahaan management script
 * Version: 1.2.0
 * Path: assets/js/skp-perusahaan-3.js
 * 
 * Changelog:
 * 1.2.0 - 2024-03-15
 * - Added service column display
 * - Updated renderSKPList to show service_short_name
 * 1.1.0 - Initial responsive table implementation
 */

(function($) {
    'use strict';

    let memberTable = null;

    // Initialize SKP Perusahaan functionality
    function initSKPPerusahaan() {
        loadSKPList();
        initModal();
        initFormHandlers();
        initDeleteHandlers();
    }

    // Load SKP list
    function loadSKPList() {
        $.ajax({
            url: asosiasiAdmin.ajaxurl,
            type: 'GET',
            data: {
                action: 'get_skp_perusahaan_list',
                member_id: $('#member_id').val(),
                nonce: asosiasiAdmin.skpNonce
            },
            success: function(response) {
                if (response.success) {
                    renderSKPList(response.data.skp_list);
                }
            },
            error: function() {
                showNotice('error', 'Failed to load SKP list.');
            }
        });
    }

    // Render SKP list
    function renderSKPList(skpList) {
        const tbody = $('#company-skp-list');
        tbody.empty();

        if (skpList.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="8" class="text-center">
                        ${asosiasiAdmin.strings.noSKP || 'No SKP found.'}
                    </td>
                </tr>
            `);
            return;
        }

        skpList.forEach((skp, index) => {
            tbody.append(`
                <tr>
                    <td>${index + 1}</td>
                    <td>${escapeHtml(skp.service_short_name || '')}</td>
                    <td>${escapeHtml(skp.nomor_skp)}</td>
                    <td>${escapeHtml(skp.penanggung_jawab)}</td>
                    <td>${skp.tanggal_terbit_formatted}</td>
                    <td>${skp.masa_berlaku_formatted}</td>
                    <td><span class="skp-status status-${skp.status}">${escapeHtml(skp.status)}</span></td>
                    <td>
                        <div class="button-group">
                            <button type="button" class="button edit-skp" 
                                    data-id="${skp.id}">
                                ${asosiasiAdmin.strings.edit || 'Edit'}
                            </button>
                            <button type="button" class="button delete-skp" 
                                    data-id="${skp.id}">
                                ${asosiasiAdmin.strings.delete || 'Delete'}
                            </button>
                            <a href="${skp.file_url}" class="button" 
                               target="_blank">
                                ${asosiasiAdmin.strings.view || 'View'}
                            </a>
                        </div>
                    </td>
                </tr>
            `);
        });
    }

    // Initialize modal
    function initModal() {
        // Add SKP button handler
        $('.add-skp-btn').on('click', function() {
            resetForm();
            $('#modal-title').text(asosiasiAdmin.strings.addSKP || 'Add SKP');
            $('#skp-modal').show();
        });

        // Close modal handler
        $('.close, .modal-cancel').on('click', closeModal);

        // Close modal when clicking outside
        $(window).on('click', function(event) {
            if ($(event.target).is('#skp-modal')) {
                closeModal();
            }
        });
    }

    // Initialize form handlers
    function initFormHandlers() {
        $('#skp-form').on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const isEdit = formData.get('id') ? true : false;
            
            formData.append('action', isEdit ? 'update_skp_perusahaan' : 'add_skp_perusahaan');
            formData.append('nonce', asosiasiAdmin.skpNonce);

            $.ajax({
                url: asosiasiAdmin.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showNotice('success', response.data.message);
                        loadSKPList();
                        closeModal();
                    } else {
                        showNotice('error', response.data.message);
                    }
                },
                error: function() {
                    showNotice('error', 'Server error occurred.');
                }
            });
        });

        // Edit button handler
        $('#company-skp-list').on('click', '.edit-skp', function() {
            const skpId = $(this).data('id');
            loadSKPData(skpId);
        });
    }

    // Initialize delete handlers
    function initDeleteHandlers() {
        $('#company-skp-list').on('click', '.delete-skp', function() {
            const skpId = $(this).data('id');
            if (confirm(asosiasiAdmin.strings.confirmDelete || 'Are you sure you want to delete this SKP?')) {
                deleteSKP(skpId);
            }
        });
    }

    // Load SKP data for editing
    function loadSKPData(skpId) {
        $.ajax({
            url: asosiasiAdmin.ajaxurl,
            type: 'GET',
            data: {
                action: 'get_skp_perusahaan',
                id: skpId,
                nonce: asosiasiAdmin.skpNonce
            },
            success: function(response) {
                if (response.success) {
                    const skp = response.data;
                    $('#skp_id').val(skp.id);
                    $('#service_id').val(skp.service_id);
                    $('#nomor_skp').val(skp.nomor_skp);
                    $('#penanggung_jawab').val(skp.penanggung_jawab);
                    $('#tanggal_terbit').val(skp.tanggal_terbit);
                    $('#masa_berlaku').val(skp.masa_berlaku);
                    
                    // Make file field optional for edit
                    $('#pdf_file').prop('required', false);
                    $('#pdf-required').hide();
                    
                    // Show current file info if exists
                    if (skp.file_path) {
                        $('#current-file').html(
                            `<p>Current file: ${skp.file_path}</p>`
                        );
                    }

                    $('#modal-title').text(asosiasiAdmin.strings.editSKP || 'Edit SKP');
                    $('#skp-modal').show();
                } else {
                    showNotice('error', response.data.message);
                }
            },
            error: function() {
                showNotice('error', 'Server error occurred.');
            }
        });
    }

    // Delete SKP
    function deleteSKP(skpId) {
        $.ajax({
            url: asosiasiAdmin.ajaxurl,
            type: 'POST',
            data: {
                action: 'delete_skp_perusahaan',
                id: skpId,
                member_id: $('#member_id').val(),
                nonce: asosiasiAdmin.skpNonce
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', response.data.message);
                    loadSKPList();
                } else {
                    showNotice('error', response.data.message);
                }
            },
            error: function() {
                showNotice('error', 'Server error occurred.');
            }
        });
    }

    // Utility functions
    function closeModal() {
        $('#skp-modal').hide();
        resetForm();
    }

    function resetForm() {
        $('#skp-form')[0].reset();
        $('#skp_id').val('');
        $('#pdf_file').prop('required', true);
        $('#pdf-required').show();
        $('#current-file').empty();
        $('.error-message').remove();
    }

    function showNotice(type, message) {
        const notice = $(`
            <div class="notice notice-${type} is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss"></button>
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
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // Initialize when document is ready
    $(document).ready(function() {
        if ($('#company-skp-list').length) {
            initSKPPerusahaan();
        }
    });

})(jQuery);