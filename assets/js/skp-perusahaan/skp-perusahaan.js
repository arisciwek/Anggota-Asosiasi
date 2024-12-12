/**
* Main handler untuk SKP Perusahaan
*
* @package Asosiasi
* @version 1.1.0
* Path: assets/js/skp-perusahaan/skp-perusahaan.js
* 
* Changelog:
* 1.1.0 - 2024-11-19 14:32 WIB
* - Added history tab condition in initTabHandlers
* - Keep existing active/inactive tab functionality
* - Move history loading to skp-perusahaan-status.js
* 
* 1.0.2 - Fixed status dropdown functionality
* 1.0.1 - Fixed string references
* 1.0.0 - Initial version
*/

var AsosiasiSKPPerusahaan = AsosiasiSKPPerusahaan || {};

(function($) {
   'use strict';
   
   // Initialize SKP Perusahaan functionality
   function initSKPPerusahaan() {
       loadSKPList('active'); // Load active tab by default
       initTabHandlers();

        // Add delete handler
        $('#skp-perusahaan-section').on('click', '.delete-skp', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const skpId = $(this).data('id');
            
            if (!confirm(asosiasiSKPPerusahaan.strings.confirmDelete || 'Yakin ingin menghapus SKP ini?')) {
                return;
            }

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_skp_perusahaan',
                    id: skpId,
                    nonce: $('#skp_nonce').val()
                },
                success: function(response) {
                    if (response.success) {
                        AsosiasiSKPUtils.showNotice('success', response.data.message);
                        // Reload tabel sesuai tab yang aktif
                        const currentStatus = $('.nav-tab-active').data('tab') || 'active';
                        loadSKPList(currentStatus);
                   
                        // Reload tabel Tenaga Ahli dengan delay
                        setTimeout(function() {
                           AsosiasiSKPTenagaAhli.reloadTable(null, 'active');
                        }, 150);

                    } else {
                        AsosiasiSKPUtils.showNotice('error', response.data.message);
                    }
                },
                error: function() {
                    AsosiasiSKPUtils.showNotice('error', 
                        asosiasiSKPPerusahaan.strings.deleteError || 'Gagal menghapus SKP'
                    );
                }
            });
        });


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
           
           // Load appropriate data based on tab
           if (status === 'history') {
               // History tab is handled by skp-perusahaan-status.js
               if (typeof AsosiasiSKPPerusahaanStatus !== 'undefined') {
                   AsosiasiSKPPerusahaanStatus.loadStatusHistory();
               }
           } else {
               // Active/Inactive tabs handled here
               loadSKPList(status);
           }
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

       // Fixed: Use correct target IDs for SKP Perusahaan
       const targetId = status === 'active' ? 
             '#active-skp-list' : 
             '#inactive-skp-list';

       const $target = $(targetId);
       
       // Show loading state
       $target.html(`
           <tr class="skp-loading">
               <td colspan="9" class="text-center">
                   <span class="spinner is-active"></span>
                   <span class="loading-text">
                       ${asosiasiSKPPerusahaan.strings.loading || 'Memuat data SKP...'}
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
                           (asosiasiSKPPerusahaan.strings.noActiveSKP || 'Tidak ada SKP aktif') : 
                           (asosiasiSKPPerusahaan.strings.noInactiveSKP || 'Tidak ada SKP tidak aktif')}
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
                   <td>${AsosiasiSKPUtils.escapeHtml(skp.penanggung_jawab)}</td>
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
                                       aria-label="${asosiasiSKPPerusahaan.strings.changeStatus || 'Ubah Status'}">
                                   <span class="dashicons dashicons-arrow-down-alt2"></span>
                               </button>
                               <div class="status-select" style="display:none;">
                                   <select data-id="${skp.id}" data-current="${skp.status}">
                                       <option value="">
                                           ${asosiasiSKPPerusahaan.strings.selectStatus || 'Pilih Status'}
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
                          title="${asosiasiSKPPerusahaan.strings.view || 'Lihat PDF'}">
                       </a>
                   </td>
                   <td>
                       <div class="button-group">
                           ${skp.can_edit ? `
                               <button type="button" class="button edit-skp" 
                                       data-id="${skp.id}">
                                   ${asosiasiSKPPerusahaan.strings.edit || 'Edit'}
                               </button>
                               <button type="button" class="button delete-skp" 
                                       data-id="${skp.id}">
                                   ${asosiasiSKPPerusahaan.strings.delete || 'Hapus'}
                               </button>
                           ` : ''}
                       </div>
                   </td>
               </tr>
           `);
       });

       // Expose reload method untuk digunakan dari luar
        window.reloadSKPTenagaAhli = function() {
            loadSKPTenagaAhliList('active');
        };
   }

   // Initialize when document is ready
   $(document).ready(function() {
       if ($('#skp-perusahaan-section').length) {
           initSKPPerusahaan();
       }
   });

})(jQuery);
