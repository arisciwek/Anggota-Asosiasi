/**
 * Status Handler untuk SKP Tenaga Ahli
 *
 * @package     Asosiasi
 * @subpackage  Assets/JS/SKP_Tenaga_Ahli
 * @version     1.0.2
 * @author      arisciwek
 *
 * Description: Menangani semua interaksi terkait perubahan status
 *              dan riwayat status SKP Tenaga Ahli
 */
var AsosiasiSKPTenagaAhliStatus = {};

(function($) {
    'use strict';
    let isInitialized = false;

    AsosiasiSKPTenagaAhliStatus = {
        init: function() {
            if (isInitialized) {
                console.log('SKP Tenaga Ahli Status already initialized');
                return;
            }
            console.log('Initializing SKP Tenaga Ahli Status...');
            this.initStatusChangeHandlers();
            this.initModalHandlers();
            isInitialized = true;
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
            });

            // Handle status selection
            $(document).on('change', '.status-select select', function() {
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

                if (!AsosiasiSKPTenagaAhliUtils.validateForm($(this))) {
                    return;
                }

                const $form = $(this);
                const skpId = $('#status_skp_id').val();
                const newStatus = $('#status_new_status').val();
                const reason = $('#status_reason').val();

                const $submitBtn = $form.find('button[type="submit"]');
                const originalText = $submitBtn.text();
                
                $submitBtn.prop('disabled', true)
                         .text(asosiasiSKPTenagaAhli.strings.saving || 'Menyimpan...');

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'update_skp_tenaga_ahli_status',
                        nonce: $('#skp_tenaga_ahli_nonce').val(),
                        skp_id: skpId,
                        new_status: newStatus,
                        reason: reason
                    },
                    success: function(response) {
                        if (response.success) {
                            AsosiasiSKPTenagaAhliUtils.showNotice('success', response.data.message);
                            AsosiasiSKPTenagaAhliUtils.reloadAllTables();
                            
                            // Close modal
                            $('#status-change-modal').hide();
                        } else {
                            AsosiasiSKPTenagaAhliUtils.showNotice('error', 
                                response.data.message || 'Gagal mengubah status'
                            );
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', {xhr, status, error});
                        AsosiasiSKPTenagaAhliUtils.showNotice('error', 
                            asosiasiSKPTenagaAhli.strings.statusChangeError || 
                            'Gagal mengubah status SKP'
                        );
                    },
                    complete: function() {
                        $submitBtn.prop('disabled', false).text(originalText);
                    }
                });
            });

            // Modal close handlers
            $('.skp-modal-close, .skp-modal-cancel').on('click', function() {
                $('#status-change-modal').hide();
            });

            // Close modal on outside click
            $(window).on('click', function(e) {
                if ($(e.target).is('#status-change-modal')) {
                    $('#status-change-modal').hide();
                }
            });

            // Prevent modal close on content click
            $('#status-change-modal .skp-modal-content').on('click', function(e) {
                e.stopPropagation();
            });
        },

        loadStatusHistory: function() {
            const $historyList = $('#tenaga-ahli-status-history-list');
            const memberId = AsosiasiSKPUtils.getMemberId();

            if (!$historyList.length || !memberId) return;

            $historyList.html(`
                <tr class="skp-loading">
                    <td colspan="7" class="text-center">
                        <span class="spinner is-active"></span>
                        <span class="loading-text">
                            ${asosiasiSKPTenagaAhli.strings.loading || 'Memuat riwayat status...'}
                        </span>
                    </td>
                </tr>
            `);

            $.ajax({
                url: ajaxurl,
                type: 'GET',
                data: {
                    action: 'get_skp_tenaga_ahli_status_history',
                    member_id: memberId,
                    nonce: $('#skp_tenaga_ahli_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        AsosiasiSKPTenagaAhliStatus.renderStatusHistory(response.data.history);
                    } else {
                        AsosiasiSKPUtils.showNotice('error', response.data.message);
                        $historyList.empty();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('History load error:', error);
                    AsosiasiSKPUtils.showNotice('error', 
                        asosiasiSKPTenagaAhli.strings.loadHistoryError || 
                        'Gagal memuat riwayat status'
                    );
                    $historyList.empty();
                }
            });
        },

        renderStatusHistory: function(history) {
            const $historyList = $('#tenaga-ahli-status-history-list');
            $historyList.empty();

            if (!history || !history.length) {
                $historyList.html(`
                    <tr>
                        <td colspan="7" class="text-center">
                            ${asosiasiSKPTenagaAhli.strings.noHistory || 'Belum ada riwayat perubahan status'}
                        </td>
                    </tr>
                `);
                return;
            }

            history.forEach((item, index) => {
                const oldStatusClass = AsosiasiSKPTenagaAhliUtils.getStatusClassFromLabel(item.old_status);
                const newStatusClass = AsosiasiSKPTenagaAhliUtils.getStatusClassFromLabel(item.new_status);
                
                $historyList.append(`
                    <tr>
                        <td>${index + 1}</td>
                        <td>${AsosiasiSKPTenagaAhliUtils.escapeHtml(item.nomor_skp)}</td>
                        <td>${AsosiasiSKPTenagaAhliUtils.escapeHtml(item.nama_tenaga_ahli)}</td>
                        <td>
                            <span class="skp-status ${oldStatusClass}">
                                ${AsosiasiSKPTenagaAhliUtils.escapeHtml(item.old_status)}
                            </span>
                        </td>
                        <td>
                            <span class="skp-status ${newStatusClass}">
                                ${AsosiasiSKPTenagaAhliUtils.escapeHtml(item.new_status)}
                            </span>
                        </td>
                        <td>${AsosiasiSKPTenagaAhliUtils.escapeHtml(item.reason)}</td>
                        <td>${AsosiasiSKPTenagaAhliUtils.escapeHtml(item.changed_by)}</td>
                        <td>${item.changed_at}</td>
                    </tr>
                `);
            });
        },

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
        if ($('#skp-tenaga-ahli-section').length) {
            AsosiasiSKPTenagaAhliStatus.init();
        }
    });

})(jQuery);
