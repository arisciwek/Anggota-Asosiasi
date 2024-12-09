/**
 * Modal Handler untuk SKP Tenaga Ahli
 *
 * @package     Asosiasi
 * @subpackage  Assets/JS/SKP_Tenaga_Ahli
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /asosiasi/assets/js/skp-tenaga-ahli/skp-tenaga-ahli-modal.js
 *
 * Description: Menangani semua interaksi pada modal dialog untuk
 *              tambah/edit SKP Tenaga Ahli termasuk form handling
 *
 * Changelog:
 * 1.0.0 - 2024-11-22
 * - Initial creation
 * - Added form handling
 * - Added file upload
 * - Added validation
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
            $('.add-skp-btn').on('click', this.openAddModal);

            // Close modal handlers
            $('.skp-modal-close, .skp-modal-cancel').on('click', this.closeModal);

            // Outside click handler
            $(window).on('click', function(event) {
                if ($(event.target).is('#skp-modal')) {
                    AsosiasiSKPTenagaAhliModal.closeModal();
                }
            });

            // Stop propagation on modal content
            $('.skp-modal-content').on('click', function(e) {
                e.stopPropagation();
            });

            // Form submission
            $('#skp-form').on('submit', this.handleSubmit);

            // Edit button handler
            $('#skp-tenaga-ahli-section').on('click', '.edit-skp', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const skpId = $(this).data('id');
                AsosiasiSKPTenagaAhliModal.loadSKPData(skpId);
            });
        },

        openAddModal: function(e) {
            e.preventDefault();
            AsosiasiSKPTenagaAhliModal.resetForm();
            $('#modal-title').text(asosiasiSKPTenagaAhli.strings.addTitle || 'Tambah SKP');
            $('#pdf_file').prop('required', true);
            $('#pdf-required').show();
            $('#skp-modal').show();
        },

        closeModal: function() {
            $('#skp-modal').hide();
            AsosiasiSKPTenagaAhliModal.resetForm();
        },

        resetForm: function() {
            $('#skp-form')[0].reset();
            $('#skp_id').val('');
            $('#current-file').empty();
            $('.error-message').remove();
        },

        handleSubmit: function(e) {
            e.preventDefault();

            if (!AsosiasiSKPUtils.validateForm($(this))) {
                return;
            }

            const formData = new FormData(this);
            const isEdit = formData.get('id') ? true : false;
           
            formData.append('action', isEdit ? 'update_skp_tenaga_ahli' : 'add_skp_tenaga_ahli');
            formData.append('nonce', $('#skp_nonce').val());

            const submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).text(isEdit ? 
                (asosiasiSKPTenagaAhli.strings.saving || 'Menyimpan...') : 
                (asosiasiSKPTenagaAhli.strings.adding || 'Menambahkan...'));
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        AsosiasiSKPUtils.showNotice('success', response.data.message);
                        AsosiasiSKPUtils.reloadAllTabs();
                        AsosiasiSKPTenagaAhliModal.closeModal();
                    } else {
                        AsosiasiSKPUtils.showNotice('error', response.data.message);
                    }
                },
                error: function() {
                    AsosiasiSKPUtils.showNotice('error', asosiasiSKPTenagaAhli.strings.saveError || 'Terjadi kesalahan saat menyimpan data');
                },
                complete: function() {
                    submitBtn.prop('disabled', false)
                           .text(isEdit ? 
                               (asosiasiSKPTenagaAhli.strings.save || 'Simpan') : 
                               (asosiasiSKPTenagaAhli.strings.add || 'Tambah'));
                }
            });
        },

        loadSKPData: function(skpId) {
            $.ajax({
                url: ajaxurl,
                type: 'GET',
                data: {
                    action: 'get_skp_tenaga_ahli',
                    id: skpId,
                    nonce: $('#skp_nonce').val()
                },
                beforeSend: function() {
                    $('#modal-title').text(asosiasiSKPTenagaAhli.strings.loading || 'Memuat data...');
                    $('#skp-modal').show();
                    $('#skp-form').find('input, select, button').prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        AsosiasiSKPTenagaAhliModal.fillForm(response.data.skp);
                        $('#modal-title').text(asosiasiSKPTenagaAhli.strings.editTitle || 'Edit SKP');
                        $('#pdf_file').prop('required', false);
                        $('#pdf-required').hide();
                    } else {
                        AsosiasiSKPUtils.showNotice('error', response.data.message);
                        AsosiasiSKPTenagaAhliModal.closeModal();
                    }
                },
                error: function() {
                    AsosiasiSKPUtils.showNotice('error', asosiasiSKPTenagaAhli.strings.loadError || 'Gagal memuat data SKP');
                    AsosiasiSKPTenagaAhliModal.closeModal();
                },
                complete: function() {
                    $('#skp-form').find('input, select, button').prop('disabled', false);
                }
            });
        },

        fillForm: function(data) {
            $('#skp_id').val(data.id);
            $('#service_id').val(data.service_id);
            $('#nomor_skp').val(data.nomor_skp);
            $('#nama_tenaga_ahli').val(data.nama_tenaga_ahli);
            $('#jabatan').val(data.jabatan);
            $('#tanggal_terbit').val(data.tanggal_terbit);
            $('#masa_berlaku').val(data.masa_berlaku);
            
            // Set status if field exists
            if ($('#status').length) {
                $('#status').val(data.status);
            }

            // Show current file info if exists
            if (data.file_name) {
                const fileInfo = `
                    <p class="current-file-info">
                        ${asosiasiSKPTenagaAhli.strings.currentFile || 'File saat ini:'} 
                        <strong>${data.file_name}</strong>
                        <a href="${data.file_url}" target="_blank">
                            <span class="dashicons dashicons-pdf"></span>
                        </a>
                    </p>
                `;
                $('#current-file').html(fileInfo);
            }
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        if ($('#skp-tenaga-ahli-section').length) {
            AsosiasiSKPTenagaAhliModal.init();
        }
    });

})(jQuery);