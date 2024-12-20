/**
 * Modal Handler untuk SKP Tenaga Ahli
 *
 * @package     Asosiasi
 * @subpackage  Assets/JS/SKP_Tenaga_Ahli
 * @version     1.0.3
 */

var AsosiasiSKPTenagaAhliModal = {};

(function($) {
    'use strict';

    AsosiasiSKPTenagaAhliModal = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            // Add SKP button handler
            $(document).on('click', '.add-skp-tenaga-ahli-btn', this.openAddModal);

            // Edit button handler
            $('#skp-tenaga-ahli-section').on('click', '.edit-skp', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const skpId = $(this).data('id');
                AsosiasiSKPTenagaAhliModal.openEditModal(skpId);
            });

            // Form submission handler
            $('#skp-tenaga-ahli-form').on('submit', function(e) {
                e.preventDefault();
                AsosiasiSKPTenagaAhliModal.handleSubmit($(this));
            });

            // Modal close handlers
            $('.skp-modal-close, .skp-modal-cancel').on('click', this.closeModal);

            // Outside click handler
            $(document).on('click', '#skp-tenaga-ahli-modal', function(e) {
                if ($(e.target).is('#skp-tenaga-ahli-modal')) {
                    AsosiasiSKPTenagaAhliModal.closeModal();
                }
            });

            // Stop propagation on modal content
            $('#skp-tenaga-ahli-modal .skp-modal-content').on('click', function(e) {
                e.stopPropagation();
            });
        },

        openAddModal: function(e) {
            if(e) e.preventDefault();
            
            AsosiasiSKPTenagaAhliModal.resetForm();
            $('#skp-tenaga-ahli-modal #modal-title').text(
                asosiasiSKPTenagaAhli.strings.addTitle || 'Tambah SKP Tenaga Ahli'
            );
            $('#pdf_file').prop('required', true);
            $('#pdf-required').show();
            $('#skp-tenaga-ahli-modal').show();
        },

        openEditModal: function(skpId) {
            $('#skp-tenaga-ahli-modal #modal-title').text(
                asosiasiSKPTenagaAhli.strings.editTitle || 'Edit SKP Tenaga Ahli'
            );
            $('#skp-tenaga-ahli-modal').show();
            this.loadSKPDataTenagaAhli(skpId);
        },

        loadSKPDataTenagaAhli: function(skpId) {
            $.ajax({
                url: ajaxurl,
                type: 'GET',
                data: {
                    action: 'get_skp_tenaga_ahli',
                    id: skpId,
                    nonce: $('#skp_tenaga_ahli_nonce').val()
                },
                beforeSend: function() {
                    $('#skp-tenaga-ahli-form').find('input, select, button').prop('disabled', true);
                },
                success: function(response) {
                    if (response.success && response.data.skp) {
                        AsosiasiSKPTenagaAhliModal.fillForm(response.data.skp);
                        $('#pdf_file').prop('required', false);
                        $('#pdf-required').hide();
                    } else {
                        AsosiasiSKPUtils.showNotice('error', response.data.message || 'Data tidak valid');
                        AsosiasiSKPTenagaAhliModal.closeModal();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', {xhr, status, error});
                    AsosiasiSKPUtils.showNotice('error', 
                        asosiasiSKPTenagaAhli.strings.loadError || 'Gagal memuat data SKP'
                    );
                    AsosiasiSKPTenagaAhliModal.closeModal();
                },
                complete: function() {
                    $('#skp-tenaga-ahli-form').find('input, select, button').prop('disabled', false);
                }
            });
        },

        closeModal: function() {
            $('#skp-tenaga-ahli-modal').hide();
            AsosiasiSKPTenagaAhliModal.resetForm();
        },

        resetForm: function() {
            const $form = $('#skp-tenaga-ahli-form');
            $form[0].reset();
            $('#skp_id', $form).val('');
            $('#current-file', $form).empty();
            $('.error-message', $form).remove();
            
            // Reset file input state
            $('#pdf_file').prop('required', true);
            $('#pdf-required').show();
            
            $form.find('input, select, button').prop('disabled', false);
        },

        fillForm: function(data) {
            const $form = $('#skp-tenaga-ahli-form');
            
            $('#skp_id', $form).val(data.id);
            $('#service_id', $form).val(data.service_id);
            $('#nomor_skp', $form).val(data.nomor_skp);
            $('#nama_tenaga_ahli', $form).val(data.nama_tenaga_ahli);
            $('#penanggung_jawab', $form).val(data.penanggung_jawab);
            $('#tanggal_terbit', $form).val(data.tanggal_terbit);
            $('#masa_berlaku', $form).val(data.masa_berlaku);
            
            if ($('#status', $form).length) {
                $('#status', $form).val(data.status);
            }

            // Handle file requirements for edit mode
            $('#pdf_file').prop('required', false);
            $('#pdf-required').hide();

            // Show current file info if exists
            if (data.file_name) {
                const fileInfo = `
                    <p class="current-file-info">
                        ${asosiasiSKPTenagaAhli.strings.currentFile || 'File saat ini:'} 
                        <strong>${data.file_name}</strong>
                        <a href="${data.file_url}" target="_blank" class="button button-small">
                            <span class="dashicons dashicons-pdf"></span> 
                            ${asosiasiSKPTenagaAhli.strings.view || 'Lihat PDF'}
                        </a>
                    </p>
                `;
                $('#current-file', $form).html(fileInfo);
            }
        },

        handleSubmit: function($form) {
            // Validate form
            if (!AsosiasiSKPUtils.validateForm($form)) {
                return false;
            }

            // Get form data
            const formData = new FormData($form[0]);
            const isEdit = formData.get('id') ? true : false;

            if (!isEdit) {
                const $fileInput = $form.find('#pdf_file');
                if ($fileInput.length && !$fileInput[0].files.length) {
                    const label = $fileInput.prev('label').text().replace('*', '').trim();
                    // Ubah target insert ke form atau modal
                    $('<div class="notice notice-error is-dismissible">')
                        .append($('<p>', {
                            text: asosiasiSKPTenagaAhli.strings.fieldRequired ? 
                                  asosiasiSKPTenagaAhli.strings.fieldRequired.replace('%s', label) :
                                  `Field ${label} wajib diisi`
                        }))
                        .insertBefore($form.find('.skp-form-body')); // Insert di dalam form
                    return false;
                }
            }
            
            // Set proper action
            formData.append('action', isEdit ? 'update_skp_tenaga_ahli' : 'add_skp_tenaga_ahli');
            formData.append('nonce', $('#skp_tenaga_ahli_nonce').val());

            const $submitBtn = $form.find('button[type="submit"]');
            const originalText = $submitBtn.text();
            
            $submitBtn.prop('disabled', true)
                     .text(isEdit ? asosiasiSKPTenagaAhli.strings.saving : asosiasiSKPTenagaAhli.strings.adding);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        AsosiasiSKPUtils.showNotice('success', response.data.message);
                        AsosiasiSKPTenagaAhli.reloadTable(null, 'active');
                        setTimeout(() => {
                            AsosiasiSKPTenagaAhli.reloadTable(null, 'inactive');
                        }, 300);
                        AsosiasiSKPTenagaAhliModal.closeModal();
                    } else {
                        AsosiasiSKPUtils.showNotice('error', response.data.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:', {xhr, status, error});
                    AsosiasiSKPUtils.showNotice('error', 
                        asosiasiSKPTenagaAhli.strings.saveError || 'Gagal menyimpan data'
                    );
                },
                complete: function() {
                    $submitBtn.prop('disabled', false)
                             .text(originalText);
                }
            });
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        if ($('#skp-tenaga-ahli-section').length) {
            AsosiasiSKPTenagaAhliModal.init();
        }
    });

})(jQuery);
