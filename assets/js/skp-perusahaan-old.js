/**
 * SKP Perusahaan handling
 * File ini tidak digunakan lagi karena sudah dipecah
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
 * - Fixed event handlers for new tab structure
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
        initTabHandlers();
        initStatusChangeHandlers(); // Add this line
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

    // Initialize tab handlers
    function initTabHandlers() {
        $('.nav-tab-wrapper .nav-tab').on('click', function(e) {
            e.preventDefault();
            const $this = $(this);
            const status = $this.data('tab');
            
            // Update active tab
            $('.nav-tab-wrapper .nav-tab').removeClass('nav-tab-active');
            $this.addClass('nav-tab-active');
            
            // Show corresponding content
            $('.tab-pane').removeClass('active');
            $(`#skp-${status}`).addClass('active');
            
            // Load data for the tab
            loadSKPList(status);
        });
    }

    // Add this after getMemberId function
    // Modify loadSKPList to accept status parameter
    function loadSKPList(status = 'active') {
        const memberId = getMemberId();
        if (!memberId) {
            console.warn('Member ID not found');
            return;
        }

        const targetId = status === 'active' ? '#active-skp-list' : '#inactive-skp-list';
        const $target = $(targetId);
        
        // Show loading state
        $target.html(`
            <tr class="skp-loading">
                <td colspan="9" class="text-center">
                    <span class="spinner is-active"></span>
                    <span class="loading-text">
                        ${status === 'active' ? 
                            'Memuat data SKP aktif...' : 
                            'Memuat data SKP tidak aktif...'}
                    </span>
                </td>
            </tr>
        `);

        $.ajax({
            url: ajaxurl,
            type: 'GET',
            data: {
                action: 'get_skp_perusahaan_list',
                member_id: memberId,
                status: status,
                nonce: $('#skp_nonce').val()
            },
            success: function(response) {
                if (response.success) {
                    renderSKPList(response.data.skp_list, status);
                } else {
                    console.error('Error loading SKP list:', response.data);
                    showNotice('error', response.data.message || 'Gagal memuat data SKP');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                showNotice('error', 'Gagal memuat data SKP');
            }
        });
    }

    // Add this after loadSKPList
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
                    <td>
                        <div class="status-wrapper">
                            <span class="skp-status status-${skp.status}">${escapeHtml(skp.status_label)}</span>
                            ${window.can_change_status ? `
                                <button type="button" 
                                        class="status-change-trigger" 
                                        data-id="${skp.id}" 
                                        data-current="${skp.status}"
                                        aria-label="${asosiasiAdmin.strings.changeStatus || 'Ubah status'}">
                                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                                </button>
                                <select class="status-select" 
                                        style="display:none;"
                                        data-id="${skp.id}" 
                                        data-current-status="${skp.status}">
                                    <option value="">${asosiasiAdmin.strings.selectStatus || 'Pilih Status'}</option>
                                    ${getAvailableStatuses(skp.status).map(s => 
                                        `<option value="${s.value}">${s.label}</option>`
                                    ).join('')}
                                </select>
                            ` : ''}
                        </div>
                    </td>
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

    /**
     * Get available status options based on current status
     */
    function getAvailableStatuses(currentStatus) {
        switch (currentStatus) {
            case 'active':
                return [
                    { 
                        value: 'inactive', 
                        label: asosiasiAdmin.strings.statusInactive || 'Tidak Aktif'
                    },
                    { 
                        value: 'expired', 
                        label: asosiasiAdmin.strings.statusExpired || 'Kadaluarsa'
                    }
                ];
            case 'inactive':
                return [
                    { 
                        value: 'activated', 
                        label: asosiasiAdmin.strings.statusActivated || 'Diaktifkan'
                    }
                ];
            case 'expired':
                return [
                    { 
                        value: 'activated', 
                        label: asosiasiAdmin.strings.statusActivated || 'Diaktifkan'
                    }
                ];
            case 'activated':
                return [
                    { 
                        value: 'inactive', 
                        label: asosiasiAdmin.strings.statusInactive || 'Tidak Aktif'
                    }
                ];
            default:
                return [];
        }
    }

    /**
     * Initialize status change handlers
     */
    function initStatusChangeHandlers() {
        // Toggle status select dropdown
        $(document).on('click', '.status-change-trigger', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const $select = $(this).siblings('.status-select');
            $('.status-select').not($select).hide();
            $select.toggle();
        });

        // Handle status selection
        $(document).on('change', '.status-select', function() {
            const $select = $(this);
            const skpId = $select.data('id');
            const oldStatus = $select.data('current-status');
            const newStatus = $select.val();

            if (!newStatus) return;
            
            // Hide select
            $select.hide();

            // Populate status change modal
            $('#status_skp_id').val(skpId);
            $('#status_old_status').val(oldStatus);
            $('#status_new_status').val(newStatus);
            $('#status_reason').val('');
            
            // Reset select to placeholder
            $select.val('');
            
            // Show modal
            $('#status-change-modal').show();
        });

        // Close dropdowns when clicking outside
        $(window).on('click', function(e) {
            if (!$(e.target).closest('.status-wrapper').length) {
                $('.status-select').hide();
            }
        });

        // Handle status change form submission
        $('#status-change-form').on('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'update_skp_status');
            formData.append('nonce', asosiasiAdmin.strings.nonce);

            $.ajax({
                url: asosiasiAdmin.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        // Reload both tabs to ensure proper status display
                        loadSKPList('active');
                        loadSKPList('inactive');
                        // Also reload history tab if it exists
                        if ($('#skp-history').length) {
                            loadStatusHistory();
                        }
                        showNotice('success', response.data.message);
                    } else {
                        showNotice('error', response.data.message);
                    }
                    $('#status-change-modal').hide();
                },
                error: function() {
                    showNotice('error', asosiasiAdmin.strings.errorServer);
                    $('#status-change-modal').hide();
                }
            });
        });
    }

    // Handle status change form submission
    $('#status-change-form').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'update_skp_status');
        formData.append('nonce', $('#skp_nonce').val());

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Reload both tabs to ensure proper status display
                    loadSKPList('active');
                    setTimeout(() => {
                        loadSKPList('inactive');
                    }, 300);
                    
                    // Also reload history tab if it exists
                    if ($('#skp-history').length) {
                        loadStatusHistory();
                    }
                    showNotice('success', response.data.message);
                } else {
                    console.error('Error:', response.data.message);
                    showNotice('error', response.data.message);
                }
                $('#status-change-modal').hide();
            },
            error: function(xhr, status, error) {
                console.error('Ajax error:', status, error);
                showNotice('error', asosiasiAdmin.strings.errorServer);
                $('#status-change-modal').hide();
            }
        });
    });
        
    // Add this helper function to better handle tab updates
    function reloadAllTabs(activeFirst = true) {
        if (activeFirst) {
            loadSKPList('active');
            setTimeout(() => loadSKPList('inactive'), 300);
        } else {
            loadSKPList('inactive');
            setTimeout(() => loadSKPList('active'), 300);
        }
    }

    // Add after escapeHtml function
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

    // Get member ID from hidden input or URL
    function getMemberId() {
        return $('#member_id').val() || 
               new URLSearchParams(window.location.search).get('id');
    }

    // Initialize form handlers
    function initFormHandlers() {
        // Move event delegation to container that always exists
        $('#skp-perusahaan-section').on('click', '.edit-skp', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const skpId = $(this).data('id');
            loadSKPData(skpId);
        });

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
                        // Reload both tabs after update
                        loadSKPList('active');
                        loadSKPList('inactive');
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
    }

    // Initialize delete handlers
    function initDeleteHandlers() {
        // Update event delegation to container
        $('#skp-perusahaan-section').on('click', '.delete-skp', function(e) {
            e.preventDefault();
            const skpId = $(this).data('id');
            if (confirm(asosiasiAdmin.strings.confirmDelete || 'Yakin ingin menghapus SKP ini?')) {
                deleteSKP(skpId);
            }
        });
    }

    // Initialize modal handlers
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

        // Close modal handlers
        $('.skp-modal-close, .skp-modal-cancel').on('click', function(e) {
            e.preventDefault();
            closeModal();
        });

        $(window).on('click', function(event) {
            if ($(event.target).is('#skp-modal')) {
                closeModal();
            }
        });

        $('.skp-modal-content').on('click', function(e) {
            e.stopPropagation();
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

    // Fill form with SKP data
    function fillForm(data) {
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

    // Rest of the utility functions remain unchanged...
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

    // Initialize when document is ready
    $(document).ready(function() {
        if ($('#skp-perusahaan-section').length) {
            initSKPPerusahaan();
        }
    });

})(jQuery);