/**
 * Modal handlers untuk SKP Perusahaan
 *
 * @package Asosiasi
 * @version 1.0.2
 * Path: assets/js/skp-perusahaan/skp-perusahaan-modal.js
 * 
 * Changelog:
 * 1.0.2 - 2024-11-19
 * - Changed asosiasiAdmin references to asosiasiSKPPerusahaan
 * - Added safety checks for strings object
 * 1.0.1 - Fixed jQuery initialization pattern
 * 1.0.0 - Initial version
 */

var AsosiasiSKPPerusahaanModal = {};

(function($) {
    'use strict';

    AsosiasiSKPPerusahaanModal = {
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
                    AsosiasiSKPPerusahaanModal.closeModal();
                }
            });

            // Stop propagation on modal content
            $('.skp-modal-content').on('click', function(e) {
                e.stopPropagation();
            });

            // Form submission
            $('#skp-form').on('submit', this.handleSubmit);

            // Edit button handler
            $('#skp-perusahaan-section').on('click', '.edit-skp', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const skpId = $(this).data('id');
                AsosiasiSKPPerusahaanModal.loadSKPData(skpId);
            });
        },

        openAddModal: function(e) {
            e.preventDefault();
            AsosiasiSKPPerusahaanModal.resetForm();
            $('#modal-title').text(asosiasiSKPPerusahaan.strings.addTitle || 'Tambah SKP');
            $('#pdf_file').prop('required', true);
            $('#pdf-required').show();
            $('#skp-modal').show();
        },

        closeModal: function() {
            $('#skp-modal').hide();
            AsosiasiSKPPerusahaanModal.resetForm();
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
           
            formData.append('action', isEdit ? 'update_skp_perusahaan' : 'add_skp_perusahaan');
            formData.append('nonce', $('#skp_nonce').val());

            const submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).text(isEdit ? 
                (asosiasiSKPPerusahaan.strings.saving || 'Menyimpan...') : 
                (asosiasiSKPPerusahaan.strings.adding || 'Menambahkan...'));
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        AsosiasiSKPUtils.showNotice('success', response.data.message);
                        // Hanya reload tabel SKP Perusahaan, bukan semua tabel
                        AsosiasiSKPPerusahaan.reloadTable(formData.get('member_id'), 'active');

                        AsosiasiSKPPerusahaanModal.closeModal();
                        
                        // Reload kedua tabel
                        setTimeout(function() {
                            // Reload tabel perusahaan
                            AsosiasiSKPPerusahaan.reloadTable(formData.get('member_id'));
                            
                            // Reload tabel tenaga ahli menggunakan existing function
                            AsosiasiSKPTenagaAhli.reloadTable(formData.get('member_id'), 'active');
                        }, 150);
                    } else {
                        AsosiasiSKPUtils.showNotice('error', response.data.message);
                    }
                },
                error: function() {
                    AsosiasiSKPUtils.showNotice('error', asosiasiSKPPerusahaan.strings.saveError || 'Terjadi kesalahan saat menyimpan data');
                },
                complete: function() {
                    submitBtn.prop('disabled', false)
                           .text(isEdit ? 
                               (asosiasiSKPPerusahaan.strings.save || 'Simpan') : 
                               (asosiasiSKPPerusahaan.strings.add || 'Tambah'));
                }
            });
        },

        loadSKPData: function(skpId) {
            $.ajax({
                url: ajaxurl,
                type: 'GET',
                data: {
                    action: 'get_skp_perusahaan',
                    id: skpId,
                    nonce: $('#skp_nonce').val()
                },
                beforeSend: function() {
                    $('#modal-title').text(asosiasiSKPPerusahaan.strings.loading || 'Memuat data...');
                    $('#skp-modal').show();
                    $('#skp-form').find('input, select, button').prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        AsosiasiSKPPerusahaanModal.fillForm(response.data.skp);
                        $('#modal-title').text(asosiasiSKPPerusahaan.strings.editTitle || 'Edit SKP');
                        $('#pdf_file').prop('required', false);
                        $('#pdf-required').hide();
                    } else {
                        AsosiasiSKPUtils.showNotice('error', response.data.message);
                        AsosiasiSKPPerusahaanModal.closeModal();
                    }
                },
                error: function() {
                    AsosiasiSKPUtils.showNotice('error', asosiasiSKPPerusahaan.strings.loadError || 'Gagal memuat data SKP');
                    AsosiasiSKPPerusahaanModal.closeModal();
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
            $('#penanggung_jawab').val(data.penanggung_jawab);
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
                        ${asosiasiSKPPerusahaan.strings.currentFile || 'File saat ini:'} 
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
        if ($('#skp-perusahaan-section').length) {
            AsosiasiSKPPerusahaanModal.init();
        }
    });

})(jQuery);
