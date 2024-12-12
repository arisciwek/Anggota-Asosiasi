/**
 * Status handlers untuk SKP Perusahaan
 *
 * @package Asosiasi
 * @version 1.0.4
 * Path: assets/js/skp-perusahaan/skp-perusahaan-status.js
 * 
 * Changelog:
 * 1.0.4 - 2024-11-19 15:35 WIB
 * - Fixed status label rendering in history tab 
 * - Added status label property usage
 * - Improved error handling
 */

var AsosiasiSKPPerusahaanStatus = {};

(function($) {
    'use strict';

    AsosiasiSKPPerusahaanStatus = {
        init: function() {
            this.initStatusChangeHandlers();
            this.initModalHandlers();
        },

        initStatusChangeHandlers: function() {
            // Tambahkan scope ke #skp-perusahaan-section
            $('#skp-perusahaan-section').on('click', '.status-change-trigger', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const $wrapper = $(this).closest('.status-wrapper');
                const $select = $wrapper.find('.status-select');
                
                // Hide other open selects within SKP Perusahaan section only
                $('#skp-perusahaan-section .status-select').not($select).hide();
                $select.toggle();
            });

            // Scope status selection ke SKP Perusahaan
            $('#skp-perusahaan-section').on('change', '.status-select select', function() {
                const $select = $(this);
                const skpId = $select.data('id');
                const oldStatus = $select.data('current');
                const newStatus = $select.val();

                if (!newStatus) return;

                // Hide select wrapper
                $select.closest('.status-select').hide();

                // Populate modal fields
                $('#status_skp_id').val(skpId);
                $('#status_old_status').val(oldStatus);
                $('#status_new_status').val(newStatus);
                $('#status_reason').val('');

                // Show modal
                $('#status-change-modal').show();
                $('#status_reason').focus();
                
                // Reset select
                $select.val('');
            });

            // Close dropdowns when clicking outside - scope ke SKP Perusahaan
            $(window).on('click', function(e) {
                if (!$(e.target).closest('#skp-perusahaan-section .status-wrapper').length) {
                    $('#skp-perusahaan-section .status-select').hide();
                }
            });
        },

        initModalHandlers: function() {
            // Handle form submission
            $('#status-change-form').on('submit', function(e) {
                e.preventDefault();

                if (!AsosiasiSKPUtils.validateForm($(this))) {
            return;
        }

                const formData = new FormData(this);
                formData.append('action', 'update_skp_status');
                formData.append('nonce', $('#skp_nonce').val());

                const $submitBtn = $(this).find('button[type="submit"]');
                const originalText = $submitBtn.text();
                
                $submitBtn.prop('disabled', true)
                         .text(asosiasiSKPPerusahaan.strings.saving || 'Menyimpan...');

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
                            AsosiasiSKPPerusahaanStatus.loadStatusHistory();
                        } else {
                            AsosiasiSKPUtils.showNotice('error', response.data.message);
                        }
                        $('#status-change-modal').hide();
                    },
                    error: function(xhr, status, error) {
                        console.error('Status update error:', error);
                        AsosiasiSKPUtils.showNotice('error', 
                            asosiasiSKPPerusahaan.strings.statusChangeError || 
                            'Gagal mengubah status SKP'
                        );
                    },
                    complete: function() {
                        $submitBtn.prop('disabled', false).text(originalText);
                    }
                });
            });

            // Modal close handlers
            $('.status-modal-close, .status-modal-cancel').on('click', function() {
                $('#status-change-modal').hide();
            });

            // Close modal on outside click
            $(window).on('click', function(e) {
                if ($(e.target).is('#status-change-modal')) {
                    $('#status-change-modal').hide();
                }
            });

            // Prevent modal close on content click
            $('#status-change-modal .modal-content').on('click', function(e) {
                e.stopPropagation();
            });
        },

        loadStatusHistory: function() {
            const $historyList = $('#status-history-list');
            const memberId = AsosiasiSKPUtils.getMemberId();

            if (!$historyList.length || !memberId) return;

            $historyList.html(`
                <tr class="skp-loading">
                    <td colspan="7" class="text-center">
                        <span class="spinner is-active"></span>
                        <span class="loading-text">
                            ${asosiasiSKPPerusahaan.strings.loading || 'Memuat riwayat status...'}
                        </span>
                    </td>
                </tr>
            `);

            $.ajax({
                url: ajaxurl,
                type: 'GET',
                data: {
                    action: 'get_skp_status_history',
                    member_id: memberId,
                    nonce: $('#skp_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        AsosiasiSKPPerusahaanStatus.renderStatusHistory(response.data.history);
                    } else {
                        AsosiasiSKPUtils.showNotice('error', response.data.message);
                        $historyList.empty();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Status history load error:', error);
                    AsosiasiSKPUtils.showNotice('error', 
                        asosiasiSKPPerusahaan.strings.loadHistoryError || 
                        'Gagal memuat riwayat status'
                    );
                    $historyList.empty();
                }
            });
        },

        renderStatusHistory: function(history) {
            const $historyList = $('#status-history-list');
            $historyList.empty();

            if (!history || !history.length) {
                $historyList.html(`
                    <tr>
                        <td colspan="7" class="text-center">
                            ${asosiasiSKPPerusahaan.strings.noHistory || 'Belum ada riwayat perubahan status'}
                        </td>
                    </tr>
                `);
                return;
            }

            history.forEach((item, index) => {
                // Dapatkan class CSS sesuai status
                const oldStatusClass = this.getStatusClassFromLabel(item.old_status);
                const newStatusClass = this.getStatusClassFromLabel(item.new_status);
                
                $historyList.append(`
                    <tr>
                        <td>${index + 1}</td>
                        <td>${AsosiasiSKPUtils.escapeHtml(item.nomor_skp)}</td>
                        <td>
                            <span class="skp-status ${oldStatusClass}">
                                ${AsosiasiSKPUtils.escapeHtml(item.old_status)}
                            </span>
                        </td>
                        <td>
                            <span class="skp-status ${newStatusClass}">
                                ${AsosiasiSKPUtils.escapeHtml(item.new_status)}
                            </span>
                        </td>
                        <td>${AsosiasiSKPUtils.escapeHtml(item.reason)}</td>
                        <td>${AsosiasiSKPUtils.escapeHtml(item.changed_by)}</td>
                        <td>${item.changed_at}</td>
                    </tr>
                `);
            });
        },

        // Helper function untuk mendapatkan class CSS dari label status
        getStatusClassFromLabel: function(label) {
            label = label.toLowerCase();
            const statusMap = {
                'aktif': 'skp-status-active',
                'active': 'skp-status-active',
                'diaktifkan': 'skp-status-active', 
                'activated': 'skp-status-active',
                'tidak aktif': 'skp-status-inactive',
                'inactive': 'skp-status-inactive',
                'kadaluarsa': 'skp-status-expired',
                'expired': 'skp-status-expired'
            };
            return statusMap[label] || 'skp-status-inactive';
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        if ($('#skp-perusahaan-section').length) {
            AsosiasiSKPPerusahaanStatus.init();
        }
    });

})(jQuery);
