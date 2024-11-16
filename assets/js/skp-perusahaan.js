/**
 * SKP Perusahaan handling
 * 
 * @package Asosiasi
 * @version 1.3.2
 * Path: assets/js/skp-perusahaan.js
 * 
 * Changelog:
 * 1.3.2 - 2024-11-16
 * - Added support for active/inactive SKP separation
 * - Modified loadSKPList to handle tab-specific data
 * - Updated renderSKPList for tab contexts
 * - Added status parameter to AJAX calls
 * 
 * 1.3.1 - 2024-03-18
 * - Added global AsosiasiSKP namespace
 * - Added public reloadTable method for external reload
 */


var AsosiasiSKP = AsosiasiSKP || {};

(function($) {
    'use strict';

    // Initialize SKP Perusahaan functionality
    function initSKPPerusahaan() {
        loadSKPList('active'); // Load active tab by default
        initModal();
        initFormHandlers();
        initDeleteHandlers();
    }
    
    // Expose public API for table reload
    AsosiasiSKP.reloadTable = function(memberId, status = 'active') {
        if (!memberId) {
            memberId = $('#member_id').val();
        }
        if (memberId) {
            loadSKPList(status, memberId);
        } else {
            console.warn('Member ID not provided for SKP table reload');
        }
    };

    // Get member ID from hidden input or URL
    function getMemberId() {
        return $('#member_id').val() || 
               new URLSearchParams(window.location.search).get('id');
    }

    // Modify loadSKPList to accept status parameter
    function loadSKPList(status = 'active', memberId = null) {
        const nonce = $('#skp_nonce').val();
        if (!memberId) {
            memberId = $('#member_id').val();
        }
        
        console.log('Loading SKP list for member:', memberId, 'status:', status);
        
        $.ajax({
            url: asosiasiAdmin.ajaxurl,
            type: 'GET',
            data: {
                action: 'get_skp_perusahaan_list',
                member_id: memberId,
                status: status,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    renderSKPList(response.data.skp_list, status);
                } else {
                    showNotice('error', response.data.message || 'Gagal memuat data SKP');
                }
            },
            error: function() {
                showNotice('error', 'Gagal memuat data SKP');
            }
        });
    }

    // Initialize modal
    function initModal() {
        // Add SKP button handler
        $('.add-skp-btn').on('click', function(e) {
            e.preventDefault();
            resetForm();
            $('#modal-title').text(asosiasiAdmin.strings.addSKP || 'Tambah SKP');
            $('#pdf_file').prop('required', true);
            $('#pdf-required').show();
            $('#skp-modal').show();
        });

        // Close modal handler
        $('.skp-modal-close, .skp-modal-cancel').on('click', function(e) {
            e.preventDefault();
            closeModal();
        });

        // Close modal when clicking outside
        $(window).on('click', function(event) {
            if ($(event.target).is('#skp-modal')) {
                closeModal();
            }
        });

        // Prevent modal close when clicking inside modal
        $('.skp-modal-content').on('click', function(e) {
            e.stopPropagation();
        });
    }

    // Initialize form handlers
    function initFormHandlers() {
        $('#skp-form').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const isEdit = formData.get('id') ? true : false;
           
            formData.append('action', isEdit ? 'update_skp_perusahaan' : 'add_skp_perusahaan');
            formData.append('nonce', $('#skp_nonce').val());

            const submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).text(isEdit ? 'Menyimpan...' : 'Menambahkan...');
            
            $.ajax({
                url: asosiasiAdmin.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showNotice('success', response.data.message);
                        loadSKPList(getMemberId());
                        closeModal();
                    } else {
                        showNotice('error', response.data.message);
                    }
                },
                error: function() {
                    showNotice('error', 'Terjadi kesalahan saat menyimpan data');
                },
                complete: function() {
                    submitBtn.prop('disabled', false)
                           .text(isEdit ? 'Simpan' : 'Tambah');
                }
            });
        });

        // Edit button handler
        $('#company-skp-list').on('click', '.edit-skp', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const skpId = $(this).data('id');
            loadSKPData(skpId);
        });
    }

    // Initialize delete handlers
    function initDeleteHandlers() {
        $('#company-skp-list').on('click', '.delete-skp', function(e) {
            e.preventDefault();
            const skpId = $(this).data('id');
            if (confirm(asosiasiAdmin.strings.confirmDelete || 'Yakin ingin menghapus SKP ini?')) {
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
                nonce: $('#skp_nonce').val()
            },
            beforeSend: function() {
                $('#modal-title').text('Memuat data...');
                $('#skp-modal').show();
                $('#skp-form').find('input, select, button').prop('disabled', true);
            },
            success: function(response) {
                if (response.success) {
                    fillForm(response.data.skp);
                    $('#modal-title').text(asosiasiAdmin.strings.editSKP || 'Edit SKP');
                    $('#pdf_file').prop('required', false);
                    $('#pdf-required').hide();
                } else {
                    showNotice('error', response.data.message);
                    closeModal();
                }
            },
            error: function() {
                showNotice('error', 'Gagal memuat data SKP');
                closeModal();
            },
            complete: function() {
                $('#skp-form').find('input, select, button').prop('disabled', false);
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
                member_id: getMemberId(),
                nonce: $('#skp_nonce').val()
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', response.data.message);
                    loadSKPList(getMemberId());
                } else {
                    showNotice('error', response.data.message);
                }
            },
            error: function() {
                showNotice('error', 'Gagal menghapus SKP');
            }
        });
    }

    // Fill form with SKP data
    function fillForm(data) {
        $('#skp_id').val(data.id);
        $('#service_id').val(data.service_id);
        $('#nomor_skp').val(data.nomor_skp);
        $('#penanggung_jawab').val(data.penanggung_jawab);
        $('#tanggal_terbit').val(data.tanggal_terbit);
        $('#masa_berlaku').val(data.masa_berlaku);

        // Show current file info if exists
        if (data.file_name) {
            const fileInfo = `
                <p class="current-file-info">
                    File saat ini: 
                    <strong>${data.file_name}</strong>
                    <a href="${data.file_url}" target="_blank">
                        <span class="dashicons dashicons-pdf"></span>
                    </a>
                </p>
            `;
            $('#current-file').html(fileInfo);
        }
    }

    // Utility functions
    function closeModal() {
        $('#skp-modal').hide();
        resetForm();
    }

    function resetForm() {
        $('#skp-form')[0].reset();
        $('#skp_id').val('');
        $('#current-file').empty();
        $('.error-message').remove();
    }

    function showNotice(type, message) {
        const notice = $(`
            <div class="notice notice-${type} is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Tutup notifikasi</span>
                </button>
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

    // Render SKP list with status context
    function renderSKPList(skpList, status) {
        const targetId = status === 'active' ? '#active-skp-list' : '#inactive-skp-list';
        const tbody = $(targetId);
        tbody.empty();

        if (!skpList || skpList.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="9" class="text-center">
                        ${status === 'active' ? 
                            (asosiasiAdmin.strings.noActiveSKP || 'Tidak ada SKP aktif') : 
                            (asosiasiAdmin.strings.noInactiveSKP || 'Tidak ada SKP tidak aktif')}
                    </td>
                </tr>
            `);
            return;
        }

        skpList.forEach((skp, index) => {
            tbody.append(`
                <tr>
                    <td>${index + 1}</td>
                    <td>${escapeHtml(skp.nomor_skp)}</td>
                    <td>${escapeHtml(skp.service_short_name)}</td>
                    <td>${escapeHtml(skp.penanggung_jawab)}</td>
                    <td>${escapeHtml(skp.tanggal_terbit)}</td>
                    <td>${escapeHtml(skp.masa_berlaku)}</td>
                    <td><span class="skp-status status-${skp.status}">${escapeHtml(skp.status_label)}</span></td>
                    <td>
                        <a href="${skp.file_url}" 
                           class="dashicons dashicons-pdf" 
                           target="_blank"
                           title="${asosiasiAdmin.strings.view || 'Lihat PDF'}">
                        </a>
                    </td>
                    <td>
                        <div class="button-group">
                            ${skp.can_edit ? `
                                <button type="button" class="button edit-skp" 
                                        data-id="${skp.id}">
                                    ${asosiasiAdmin.strings.edit || 'Edit'}
                                </button>
                                <button type="button" class="button delete-skp" 
                                        data-id="${skp.id}">
                                    ${asosiasiAdmin.strings.delete || 'Hapus'}
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `);
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
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
