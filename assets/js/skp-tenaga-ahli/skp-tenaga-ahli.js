/**
* Main handler untuk SKP Tenaga Ahli
*
* @package Asosiasi
* @version 1.0.0
* Path: assets/js/skp-tenaga-ahli/skp-tenaga-ahli.js
* 
* Changelog:
* 1.0.0 - 2024-11-22
* - Initial creation
* - Added CRUD operations
* - Added table handlers
*/

var AsosiasiSKPTenagaAhli = AsosiasiSKPTenagaAhli || {};

(function($) {
   'use strict';
   
   // Initialize SKP Tenaga Ahli functionality
   function initSKPTenagaAhli() {
       loadSKPTenagaAhliList('active'); // Load active tab by default
       initSKPTenagaAhliTabHandlers();
   }
   
   // Expose public API for table reload
   AsosiasiSKPTenagaAhli.reloadTable = function(memberId, status = 'active') {
       if (!memberId) {
           memberId = $('#member_id').val();
       }
       if (memberId) {
           loadSKPTenagaAhliList(status, memberId);
       } else {
           console.warn('Member ID not provided for SKP Tenaga Ahli table reload');
       }
   };

   // Initialize tab handlers
   function initSKPTenagaAhliTabHandlers() {
       $('.skp-tenaga-ahli-nav-tab-wrapper .skp-tenaga-ahli-nav-tab').on('click', function(e) {
           e.preventDefault();
           const $this = $(this);
           const status = $this.data('tab');
           
           // Update active tab
           $('.skp-tenaga-ahli-nav-tab-wrapper .skp-tenaga-ahli-nav-tab').removeClass('nav-tab-active');
           $this.addClass('nav-tab-active');
           
           // Show corresponding content
           $('.tab-pane').removeClass('active');
           $(`#skp-${status}`).addClass('active');
           
           // Load appropriate data based on tab
           if (status === 'history') {
               if (typeof AsosiasiSKPTenagaAhliStatus !== 'undefined') {
                   AsosiasiSKPTenagaAhliStatus.loadStatusHistory();
               }
           } else {
               loadSKPTenagaAhliList(status);
           }
       });
   }

   // Get member ID from hidden input or URL
   function getMemberId() {
       return $('#member_id').val() || 
              new URLSearchParams(window.location.search).get('id');
   }

    // Periksa dulu apakah nonce tersedia
    const nonce = $('#skp_tenaga_ahli_nonce').val();
    if (!nonce) {
        console.error('Nonce not found');
        AsosiasiSKPUtils.showNotice('error', 'Security token not found');
        return;
    }

   function loadSKPTenagaAhliList(status = 'active') {
       const memberId = getMemberId();
       if (!memberId) {
           console.warn('Member ID not found');
           return;
       }

       const targetId = status === 'active' ? '#active-skp-tenaga-ahli-list' : '#inactive-skp-tenaga-ahli-list';
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
               nonce: nonce // Gunakan nonce yang sudah divalidasi

           },
           success: function(response) {
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
    const targetId = status === 'active' ? '#active-skp-tenaga-ahli-list' : '#inactive-skp-tenaga-ahli-list';
    const tbody = $(targetId);
    tbody.empty();

    if (!skpList || skpList.length === 0) {
        tbody.append(`
            <tr>
                <td colspan="10" class="text-center">
                    ${status === 'active' ? 'Tidak ada SKP aktif' : 'Tidak ada SKP tidak aktif'}
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
                        <div class="skp-status skp-status-${skp.status} inline-block">
                            <div class="current-skp-tenaga-ahli-status">
                                ${AsosiasiSKPUtils.escapeHtml(skp.status_label)}
                            </div>
                            ${window.can_change_status ? `
                            <div class="dropdown inline-block mleft5">
                                <a href="#" 
                                   class="dropdown-toggle status-change-trigger"
                                   data-id="${skp.id}"
                                   data-current="${skp.status}"
                                   data-toggle="tooltip" 
                                   title="Ubah Status">
                                    <i class="dashicons dashicons-arrow-down-alt2"></i>
                                </a>
                                <div class="dropdown-menu" role="menu">
                                    <select class="status-change-select" 
                                            data-id="${skp.id}" 
                                            data-current="${skp.status}">
                                        <option value="">Pilih Status</option>
                                        ${statusOptions}
                                    </select>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                    </td>
                    <td>
                        <a href="${skp.file_url}" 
                           class="dashicons dashicons-pdf" 
                           target="_blank"
                           title="Lihat PDF">
                        </a>
                    </td>
                    <td>
                        <div class="button-group">
                            ${skp.can_edit ? `
                                <button type="button" class="button edit-skp" data-id="${skp.id}">
                                    Edit
                                </button>
                                <button type="button" class="button delete-skp" data-id="${skp.id}">
                                    Hapus
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
