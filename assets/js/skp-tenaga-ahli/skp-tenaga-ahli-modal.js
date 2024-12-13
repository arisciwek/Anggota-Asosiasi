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
                AsosiasiSKPTenagaAhliModal.loadSKPData(skpId);
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

            // Prevent modal content clicks from closing
            $('#skp-tenaga-ahli-modal .skp-modal-content').on('click', function(e) {
                e.stopPropagation();
            });
        },

        openAddModal: function() {
            AsosiasiSKPTenagaAhliModal.resetForm();
            $('#modal-title').text(asosiasiSKPTenagaAhli.strings.addTitle || 'Tambah SKP Tenaga Ahli');
            $('#pdf_file').prop('required', true);
            $('#pdf-required').show();
            $('#skp-tenaga-ahli-modal').show();
        },

        loadSKPData: function(skpId) {
            $.ajax({
                url: ajaxurl,
                type: 'GET',
                data: {
                    action: 'get_skp_tenaga_ahli',
                    id: skpId,
                    nonce: $('#skp_tenaga_ahli_nonce').val()
                },
                beforeSend: function() {
                    $('#modal-title').text(asosiasiSKPTenagaAhli.strings.loading || 'Memuat data...');
                    $('#skp-tenaga-ahli-modal').show();
                    $('#skp-tenaga-ahli-form').find('input, select, button').prop('disabled', true);
                },
                success: function(response) {
                    if (response.success && response.data.skp) {
                        AsosiasiSKPTenagaAhliModal.fillForm(response.data.skp);
                        $('#modal-title').text(asosiasiSKPTenagaAhli.strings.editTitle || 'Edit SKP Tenaga Ahli');
                        // File tidak required saat edit
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

        openEditModal: function(skpId) {
            e.preventDefault();
            AsosiasiSKPPerusahaanModal.resetForm();
            $('#modal-title').text(asosiasiSKPTenagaAhli.strings.addTitle || 'Edit SKP');
            $('#pdf_file').prop('required', true);
            $('#pdf-required').show();
            $('#skp-modal').show();
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

            // Reset state untuk tambah baru
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

            // Reset file requirements
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

        validatePDFRequirement: function($form) {
            const isEdit = $('#skp_id', $form).val() !== '';
            const $fileInput = $('#pdf_file', $form);
            const hasNewFile = $fileInput[0].files.length > 0;
            const hasExistingFile = $('#current-file .current-file-info', $form).length > 0;

            // Jika mode tambah baru, file harus ada
            if (!isEdit && !hasNewFile) {
                AsosiasiSKPUtils.showNotice('error', 'File PDF wajib diunggah');
                return false;
            }

            // Jika mode edit, file tidak wajib jika sudah ada file sebelumnya
            if (isEdit && !hasNewFile && !hasExistingFile) {
                AsosiasiSKPUtils.showNotice('error', 'File PDF wajib diunggah');
                return false;
            }

            // Validasi tipe file jika ada file baru
            if (hasNewFile) {
                const file = $fileInput[0].files[0];
                if (file.type !== 'application/pdf') {
                    AsosiasiSKPUtils.showNotice('error', 'File harus berformat PDF');
                    return false;
                }

                // Validasi ukuran file (maksimal 2MB)
                const maxSize = 2 * 1024 * 1024; // 2MB dalam bytes
                if (file.size > maxSize) {
                    AsosiasiSKPUtils.showNotice('error', 'Ukuran file maksimal 2MB');
                    return false;
                }
            }

            return true;
        },

        handleSubmit: function(e) {
            e.preventDefault();

            const $form = $(this);

            // Validasi PDF
            if (!AsosiasiSKPPerusahaanModal.validatePDFRequirement($form)) {
                return false;
            }

            // Validasi field lainnya
            if (!AsosiasiSKPUtils.validateForm($form)) {
                return false;
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
                        AsosiasiSKPPerusahaan.reloadTable(formData.get('member_id'), 'active');
                        AsosiasiSKPPerusahaanModal.closeModal();
                        
                        // Reload kedua tabel dengan delay
                        setTimeout(function() {
                            AsosiasiSKPPerusahaan.reloadTable(formData.get('member_id'));
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
        }

    };

    // Initialize when document is ready
    $(document).ready(function() {
        if ($('#skp-tenaga-ahli-section').length) {
            AsosiasiSKPTenagaAhliModal.init();
        }
    });

})(jQuery);
