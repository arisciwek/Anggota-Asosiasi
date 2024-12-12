/**
 * Main Handler untuk SKP Tenaga Ahli
 *
 * @package     Asosiasi
 * @subpackage  Assets/JS/SKP_Tenaga_Ahli
 * @version     1.0.3
 * @author      arisciwek
 *
 * Path: /asosiasi/assets/js/skp-tenaga-ahli/skp-tenaga-ahli.js
 *
 * Description: Menangani semua interaksi utama SKP Tenaga Ahli dengan isolasi penuh
 *
 * Changelog:
 * 1.0.3 - 2024-12-12
 * - Added complete namespace isolation
 * - Removed localStorage dependency
 * - Fixed tab handling with namespaced classes
 */

var AsosiasiSKPTenagaAhli = AsosiasiSKPTenagaAhli || {};

(function($) {
    'use strict';
    
    // Private state untuk menjaga isolasi data
    const _state = {
        currentTab: 'active',
        currentData: null
    };

    // Namespace untuk selectors dengan prefix khusus
    const SELECTORS = {
        SECTION: '#skp-tenaga-ahli-section',
        CONTAINER: '.skp-tenaga-ahli-container',
        TAB_WRAPPER: '.nav-tab-wrapper-tenaga-ahli',
        TAB: '.nav-tab-tenaga-ahli',
        TAB_ACTIVE: 'nav-tab-tenaga-ahli-active',
        CONTENT: '.tab-pane-tenaga-ahli',
        ACTIVE_CONTENT: 'active',
        ACTIVE_LIST: '#active-skp-tenaga-ahli-list',
        INACTIVE_LIST: '#inactive-skp-tenaga-ahli-list'
    };

    function initSKPTenagaAhli() {
        if (!$(SELECTORS.SECTION).length) return;

        initTabHandlers();
        preventExternalTabInterference();
        
        // Initial load B1 saat pertama kali
        loadSKPTenagaAhliList('active');
    }

    function initTabHandlers() {
        // Event delegation dengan scope yang ketat
        $(SELECTORS.SECTION).on('click', `${SELECTORS.TAB}`, function(e) {
            e.preventDefault();
            e.stopPropagation(); // Prevent event bubbling
            
            const $this = $(this);
            const status = $this.data('tab');
            
            // Update state
            _state.currentTab = status;
            
            // Update UI dengan scope spesifik
            $(`${SELECTORS.SECTION} ${SELECTORS.TAB}`)
                .removeClass(SELECTORS.TAB_ACTIVE);
            $this.addClass(SELECTORS.TAB_ACTIVE);
            
            // Update content visibility dengan scope spesifik
            $(`${SELECTORS.SECTION} ${SELECTORS.CONTENT}`)
                .removeClass(SELECTORS.ACTIVE_CONTENT)
                .hide();
            $(`#skp-tenaga-ahli-${status}`)
                .addClass(SELECTORS.ACTIVE_CONTENT)
                .show();
            
            if (status === 'history') {
                if (typeof AsosiasiSKPTenagaAhliStatus !== 'undefined') {
                    AsosiasiSKPTenagaAhliStatus.loadStatusHistory();
                }
            } else {
                loadSKPTenagaAhliList(status);
            }
        });
    }

    function preventExternalTabInterference() {
        // Handle external tab clicks
        $(document).on('click', '.nav-tab', function(e) {
            // Jika klik dari luar section SKP Tenaga Ahli
            if (!$(e.target).closest(SELECTORS.SECTION).length) {
                // Jaga state tab Tenaga Ahli tetap sesuai yang aktif
                const $activeTab = $(`${SELECTORS.SECTION} ${SELECTORS.TAB}.${SELECTORS.TAB_ACTIVE}`);
                if ($activeTab.length) {
                    const currentStatus = $activeTab.data('tab');
                    if (currentStatus && currentStatus !== 'history') {
                        // Refresh data jika perlu
                        loadSKPTenagaAhliList(currentStatus);
                    }
                }
            }
        });
    }

    function loadSKPTenagaAhliList(status = 'active') {
        const memberId = getMemberId();
        if (!memberId) {
            console.warn('Member ID not found for SKP Tenaga Ahli');
            return;
        }

        const targetSelector = status === 'active' ? 
            SELECTORS.ACTIVE_LIST : SELECTORS.INACTIVE_LIST;
        const $target = $(targetSelector);
        
        if (!$target.length) {
            console.warn(`Target element ${targetSelector} not found`);
            return;
        }

        // Show loading state
        $target.html(`
            <tr class="skp-loading">
                <td colspan="10" class="text-center">
                    <span class="spinner is-active"></span>
                    <span class="loading-text">
                        ${asosiasiSKPTenagaAhli.strings.loading || 'Memuat data SKP Tenaga Ahli...'}
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
                nonce: $('#skp_tenaga_ahli_nonce').val()
            },
            success: function(response) {
                if (response.success) {
                    // Update state
                    _state.currentData = response.data.skp_list;
                    renderSKPTenagaAhliList(response.data.skp_list, status);
                } else {
                    console.error('Error loading SKP Tenaga Ahli list:', response.data);
                    AsosiasiSKPUtils.showNotice('error', 
                        response.data.message || asosiasiSKPTenagaAhli.strings.loadError
                    );
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error loading SKP Tenaga Ahli:', error);
                AsosiasiSKPUtils.showNotice('error', asosiasiSKPTenagaAhli.strings.loadError);
            }
        });
    }

    function renderSKPTenagaAhliList(skpList, status) {
        const targetId = status === 'active' ? 
            'active-skp-tenaga-ahli-list' : 'inactive-skp-tenaga-ahli-list';
        const $target = $(`#${targetId}`);
        
        if (!$target.length) return;

        $target.empty();

        if (!skpList || skpList.length === 0) {
            $target.append(`
                <tr>
                    <td colspan="10" class="text-center">
                        ${status === 'active' ? 
                            (asosiasiSKPTenagaAhli.strings.noActiveSKP || 'Tidak ada SKP Tenaga Ahli aktif') : 
                            (asosiasiSKPTenagaAhli.strings.noInactiveSKP || 'Tidak ada SKP Tenaga Ahli tidak aktif')}
                    </td>
                </tr>
            `);
            return;
        }

        skpList.forEach((skp, index) => {
            // Existing render code tetap sama
            const availableStatuses = AsosiasiSKPUtils.getAvailableStatuses(skp.status);
            const statusOptions = availableStatuses.map(status => 
                `<option value="${status.value}">${status.label}</option>`
            ).join('');

            $target.append(`
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

    function getMemberId() {
        return $('#member_id').val() || 
               new URLSearchParams(window.location.search).get('id');
    }

    // Public API dengan namespace spesifik
    AsosiasiSKPTenagaAhli.reloadTable = function(memberId, status = 'active') {
        if (!memberId) {
            memberId = getMemberId();
        }
        if (memberId) {
            loadSKPTenagaAhliList(status);
        }
    };

    // Initialize
    $(document).ready(function() {
        if ($(SELECTORS.SECTION).length) {
            initSKPTenagaAhli();
        }
    });

})(jQuery);
