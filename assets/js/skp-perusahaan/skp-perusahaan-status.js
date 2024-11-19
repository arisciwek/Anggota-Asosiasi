/**
 * Status handlers untuk SKP Perusahaan
 *
 * @package Asosiasi
 * @version 1.0.2
 * Path: assets/js/skp-perusahaan/skp-perusahaan-status.js
 * 
 * Changelog:
 * 1.0.2 - 2024-11-19
 * - Changed asosiasiAdmin references to asosiasiSKPPerusahaan
 * - Added safety checks for strings object
 * 1.0.1 - Fixed jQuery initialization pattern
 * 1.0.0 - Initial version
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
            // Toggle status select dropdown
            $(document).on('click', '.status-change-trigger', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const $wrapper = $(this).closest('.status-wrapper');
                const $select = $wrapper.find('.status-select');
                
                // Hide other open selects
                $('.status-select').not($select).hide();
                $select.toggle();

                // Populate status options if empty
                if (!$select.children().length) {
                    const currentStatus = $(this).data('current');
                    const options = ['<option value="">' + 
                        (asosiasiSKPPerusahaan.strings.selectStatus || 'Pilih Status') + 
                        '</option>'];

                    AsosiasiSKPUtils.getAvailableStatuses(currentStatus).forEach(function(status) {
                        options.push(`<option value="${status.value}">${status.label}</option>`);
                    });

                    $select.html(options.join(''));
                }
            });

            // Handle status selection
            $(document).on('change', '.status-select', function() {
                const $select = $(this);
                const skpId = $select.closest('.status-wrapper').data('skp-id');
                const oldStatus = $select.closest('.status-wrapper').data('current-status');
                const newStatus = $select.val();

                if (!newStatus) return;

                // Hide select and reset
                $select.hide().val('');

                // Populate modal fields
                $('#status_skp_id').val(skpId);
                $('#status_old_status').val(oldStatus);
                $('#status_new_status').val(newStatus);
                $('#status_reason').val('').focus();

                // Show modal
                $('#status-change-modal').show();
            });

            // Close dropdowns when clicking outside
            $(window).on('click', function(e) {
                if (!$(e.target).closest('.status-wrapper').length) {
                    $('.status-select').hide();
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
                $historyList.append(`
                    <tr>
                        <td>${index + 1}</td>
                        <td>${AsosiasiSKPUtils.escapeHtml(item.nomor_skp)}</td>
                        <td>
                            <span class="skp-status status-${item.old_status}">
                                ${AsosiasiSKPUtils.escapeHtml(item.old_status_label)}
                            </span>
                        </td>
                        <td>
                            <span class="skp-status status-${item.new_status}">
                                ${AsosiasiSKPUtils.escapeHtml(item.new_status_label)}
                            </span>
                        </td>
                        <td>${AsosiasiSKPUtils.escapeHtml(item.reason)}</td>
                        <td>${AsosiasiSKPUtils.escapeHtml(item.changed_by)}</td>
                        <td>
                            ${AsosiasiSKPUtils.formatDate(item.changed_at)}
                            ${AsosiasiSKPUtils.formatTime(item.changed_at)}
                        </td>
                    </tr>
                `);
            });
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        if ($('#skp-perusahaan-section').length) {
            AsosiasiSKPPerusahaanStatus.init();
        }
    });

})(jQuery);