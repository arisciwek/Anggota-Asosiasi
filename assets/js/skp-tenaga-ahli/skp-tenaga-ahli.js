/**
 * Main Handler untuk SKP Tenaga Ahli
 *
 * @package     Asosiasi
 * @subpackage  Assets/JS/SKP_Tenaga_Ahli
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /asosiasi/assets/js/skp-tenaga-ahli/skp-tenaga-ahli.js
 *
 * Description: Menangani semua interaksi utama SKP Tenaga Ahli
 *              termasuk loading data dan tab management
 *
 * Changelog:
 * 1.0.0 - 2024-11-22
 * - Initial creation
 * - Added table loading
 * - Added tab handling
 * - Added data formatting
 */

var AsosiasiSKPTenagaAhli = AsosiasiSKPTenagaAhli || {};

(function($) {
   'use strict';
    function initSKPTenagaAhli() {
        // Add container specificity
        const $container = $('#skp-tenaga-ahli-container');
        const nonce = $('#skp_tenaga_ahli_nonce').val();
        if (!nonce) {
            console.error('SKP Tenaga Ahli nonce not found');
            return;
        }

        loadSKPList('active'); 
        initTabHandlers($container);
    }

    function initTabHandlers($container) {
        // Scope selectors to container
        $container.find('.nav-tab-wrapper .nav-tab').on('click', function(e) {
            e.preventDefault();
            const $this = $(this);
            const status = $this.data('tab');
            
            // Scope all selectors to container
            $container.find('.nav-tab-wrapper .nav-tab').removeClass('nav-tab-active');
            $this.addClass('nav-tab-active');
            
            $container.find('.tab-pane').removeClass('active');
            $container.find(`#skp-${status}`).addClass('active');
            
            if (status === 'history') {
                if (typeof AsosiasiSKPTenagaAhliStatus !== 'undefined') {
                    AsosiasiSKPTenagaAhliStatus.loadStatusHistory();
                }
            } else {
                loadSKPList(status);
            }
        });
    }

   // Expose public API for table reload
   AsosiasiSKPTenagaAhli.reloadTable = function(memberId, status = 'active') {
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

    function loadSKPList(status = 'active') {
        const memberId = getMemberId();
        if (!memberId) {
            console.warn('Member ID not found');
            return;
        }

        // Perbaikan ID target
        const targetId = status === 'active' ? 
            '#active-skp-tenaga-ahli-list' : 
            '#inactive-skp-tenaga-ahli-list';
        const $target = $(targetId);
        
        // Show loading state
        $target.html(`
            <tr class="skp-loading">
                <td colspan="10" class="text-center">
                    <span class="spinner is-active"></span>
                    <span class="loading-text">
                        ${asosiasiSKPTenagaAhli.strings.loading || 'Memuat data SKP...'}
                    </span>
                </td>
            </tr>
        `);

        $.ajax({
            url: ajaxurl,
            type: 'GET',
            data: {
                action: 'get_skp_tenaga_ahli_list',
                member_id: memberId,
                status: status,
                nonce: $('#skp_tenaga_ahli_nonce').val() // Use specific nonce
            },
            success: function(response) {
                console.log('SKP Tenaga Ahli response:', response); // Add logging
                if (response.success) {
                    renderSKPList(response.data.skp_list, status);
                } else {
                    console.error('Error loading SKP list:', response.data);
                    AsosiasiSKPUtils.showNotice('error', response.data.message || asosiasiSKPTenagaAhli.strings.loadError);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                AsosiasiSKPUtils.showNotice('error', asosiasiSKPTenagaAhli.strings.loadError);
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
                           (asosiasiSKPTenagaAhli.strings.noActiveSKP || 'Tidak ada SKP aktif') : 
                           (asosiasiSKPTenagaAhli.strings.noInactiveSKP || 'Tidak ada SKP tidak aktif')}
                   </td>
               </tr>
           `);
           return;
       }

       skpList.forEach((skp, index) => {
           const availableStatuses = AsosiasiSKPUtils.getAvailableStatuses(skp.status);
           const statusOptions = availableStatuses.map(status => 
               `<option value="${status.value}">${status.label}</option>`
           ).join('');

           tbody.append(`
               <tr>
                   <td>${index + 1}</td>
                   <td>${AsosiasiSKPUtils.escapeHtml(skp.nomor_skp)}</td>
                   <td>${AsosiasiSKPUtils.escapeHtml(skp.service_short_name)}</td>
                   <td>${AsosiasiSKPUtils.escapeHtml(skp.nama_tenaga_ahli)}</td>
                   <td>${AsosiasiSKPUtils.escapeHtml(skp.jabatan)}</td>
                   <td>${AsosiasiSKPUtils.escapeHtml(skp.tanggal_terbit)}</td>
                   <td>${AsosiasiSKPUtils.escapeHtml(skp.masa_berlaku)}</td>
                   <td>
                       <div class="status-wrapper" data-skp-id="${skp.id}" data-current-status="${skp.status}">
                           <span class="skp-status status-${skp.status}">
                               ${AsosiasiSKPUtils.escapeHtml(skp.status_label)}
                           </span>
                           ${window.can_change_status ? `
                               <button type="button" 
                                       class="status-change-trigger" 
                                       data-id="${skp.id}"
                                       data-current="${skp.status}"
                                       aria-label="${asosiasiSKPTenagaAhli.strings.changeStatus || 'Ubah Status'}">
                                   <span class="dashicons dashicons-arrow-down-alt2"></span>
                               </button>
                               <div class="status-select" style="display:none;">
                                   <select data-id="${skp.id}" data-current="${skp.status}">
                                       <option value="">
                                           ${asosiasiSKPTenagaAhli.strings.selectStatus || 'Pilih Status'}
                                       </option>
                                       ${statusOptions}
                                   </select>
                               </div>
                           ` : ''}
                       </div>
                   </td>
                   <td>
                       <a href="${skp.file_url}" 
                          class="dashicons dashicons-pdf" 
                          target="_blank"
                          title="${asosiasiSKPTenagaAhli.strings.view || 'Lihat PDF'}">
                       </a>
                   </td>
                   <td>
                       <div class="button-group">
                           ${skp.can_edit ? `
                               <button type="button" class="button edit-skp" 
                                       data-id="${skp.id}">
                                   ${asosiasiSKPTenagaAhli.strings.edit || 'Edit'}
                               </button>
                               <button type="button" class="button delete-skp" 
                                       data-id="${skp.id}">
                                   ${asosiasiSKPTenagaAhli.strings.delete || 'Hapus'}
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
       if ($('#skp-tenaga-ahli-section').length) {
           initSKPTenagaAhli();
       }
   });

})(jQuery);