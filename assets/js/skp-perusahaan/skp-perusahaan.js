/**
 * Main handler untuk SKP Perusahaan
 *
 * @package Asosiasi
 * @version 1.0.1
 * Path: assets/js/skp-perusahaan/skp-perusahaan.js
 * 
 * Changelog:
 * 1.0.1 - 2024-11-19
 * - Fixed string references to use asosiasiSKPPerusahaan instead of asosiasiAdmin
 * - Improved error handling
 * - Added proper data validation
 * 1.0.0 - Initial version
 */

var AsosiasiSKPPerusahaan = AsosiasiSKPPerusahaan || {};

(function($) {
    'use strict';
    
    // Initialize SKP Perusahaan functionality
    function initSKPPerusahaan() {
        loadSKPList('active'); // Load active tab by default
        initTabHandlers();
    }
    
    // Expose public API for table reload
    AsosiasiSKPPerusahaan.reloadTable = function(memberId, status = 'active') {
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

    // Get member ID from hidden input or URL
    function getMemberId() {
        return $('#member_id').val() || 
               new URLSearchParams(window.location.search).get('id');
    }

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
                            asosiasiSKPPerusahaan.strings.loading : 
                            asosiasiSKPPerusahaan.strings.loading}
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
                    AsosiasiSKPUtils.showNotice('error', response.data.message || asosiasiSKPPerusahaan.strings.loadError);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                AsosiasiSKPUtils.showNotice('error', asosiasiSKPPerusahaan.strings.loadError);
            }
        });
    }

    function renderSKPList(skpList, status) {
        const targetId = status === 'active' ? '#active-skp-list' : '#inactive-skp-list';
        const tbody = $(targetId);
        tbody.empty();

        if (!skpList || skpList.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="9" class="text-center">
                        ${status === 'active' ? 
                            asosiasiSKPPerusahaan.strings.noActiveSKP : 
                            asosiasiSKPPerusahaan.strings.noInactiveSKP}
                    </td>
                </tr>
            `);
            return;
        }

        skpList.forEach((skp, index) => {
            tbody.append(`
                <tr>
                    <td>${index + 1}</td>
                    <td>${AsosiasiSKPUtils.escapeHtml(skp.nomor_skp)}</td>
                    <td>${AsosiasiSKPUtils.escapeHtml(skp.service_short_name)}</td>
                    <td>${AsosiasiSKPUtils.escapeHtml(skp.penanggung_jawab)}</td>
                    <td>${AsosiasiSKPUtils.escapeHtml(skp.tanggal_terbit)}</td>
                    <td>${AsosiasiSKPUtils.escapeHtml(skp.masa_berlaku)}</td>
                    <td>
                        <div class="status-wrapper">
                            <span class="skp-status status-${skp.status}">
                                ${AsosiasiSKPUtils.escapeHtml(skp.status_label)}
                            </span>
                            ${window.can_change_status ? `
                                <button type="button" 
                                        class="status-change-trigger" 
                                        data-id="${skp.id}" 
                                        data-current="${skp.status}"
                                        aria-label="${asosiasiSKPPerusahaan.strings.changeStatus}">
                                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                    <td>
                        <a href="${skp.file_url}" 
                           class="dashicons dashicons-pdf" 
                           target="_blank"
                           title="${asosiasiSKPPerusahaan.strings.view}">
                        </a>
                    </td>
                    <td>
                        <div class="button-group">
                            ${skp.can_edit ? `
                                <button type="button" class="button edit-skp" 
                                        data-id="${skp.id}">
                                    ${asosiasiSKPPerusahaan.strings.edit}
                                </button>
                                <button type="button" class="button delete-skp" 
                                        data-id="${skp.id}">
                                    ${asosiasiSKPPerusahaan.strings.delete}
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `);
        });
    }

    // Initialize when document is ready
    $(document).ready(function() {
        if ($('#skp-perusahaan-section').length) {
            initSKPPerusahaan();
        }
    });

})(jQuery);
