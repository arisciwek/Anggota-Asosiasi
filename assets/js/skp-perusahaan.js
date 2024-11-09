/**
 * SKP Perusahaan Handler
 * 
 * @package Asosiasi
 * @version 1.1.0
 * Path: assets/js/skp-perusahaan.js
 */

(function($) {
    'use strict';

    // SKP Handler Module
    const SKPHandler = {
        init() {
            this.cacheDom();
            this.bindEvents();
            this.loadSKPList();
        },

        cacheDom() {
            this.$container = $('.skp-container');
            this.$companyList = $('#company-skp-list');
            this.$expertList = $('#expert-skp-list');
            this.$modal = $('#skp-modal');
            this.$form = $('#skp-form');
            this.memberId = this.$container.find('[data-member-id]').first().data('member-id');
        },

        bindEvents() {
            // Add SKP button
            this.$container.on('click', '.add-skp-btn', (e) => {
                const type = $(e.currentTarget).data('type');
                this.openModal(type);
            });

            // Modal close handlers
            this.$modal.on('click', '.close, .modal-cancel', () => this.closeModal());
            $(window).on('click', (e) => {
                if ($(e.target).is(this.$modal)) {
                    this.closeModal();
                }
            });

            // Form submission
            this.$form.on('submit', (e) => {
                e.preventDefault();
                this.submitForm();
            });

            // Delete SKP
            this.$container.on('click', '.delete-skp', (e) => {
                const id = $(e.currentTarget).data('id');
                this.confirmDelete(id);
            });

            // Edit SKP
            this.$container.on('click', '.edit-skp', (e) => {
                const id = $(e.currentTarget).data('id');
                this.editSKP(id);
            });
        },

        loadSKPList() {
            this.setLoading(true);

            $.ajax({
                url: asosiasiAdmin.ajaxurl,
                type: 'GET',
                data: {
                    action: 'get_skp_perusahaan_list',
                    member_id: this.memberId,
                    nonce: asosiasiAdmin.skpNonce
                }
            })
            .done((response) => {
                if (response.success) {
                    this.renderSKPList(response.data.skp_list);
                } else {
                    this.showError(response.data.message);
                }
            })
            .fail(() => {
                this.showError(asosiasiAdmin.strings.loadError);
            })
            .always(() => {
                this.setLoading(false);
            });
        },

        renderSKPList(list) {
            if (!list || !list.length) {
                this.renderEmptyState();
                return;
            }

            const rows = list.map((skp, index) => this.createSKPRow(skp, index + 1));
            this.$companyList.html(rows.join(''));
        },
        
        createSKPRow(skp, index) {
            return `
                <tr>
                    <td>${index}</td>
                    <td>${this.escapeHtml(skp.nomor_skp)}</td>
                    <td>${this.escapeHtml(skp.penanggung_jawab)}</td>
                    <td>${skp.tanggal_terbit}</td>
                    <td>${skp.masa_berlaku}</td>
                    <td>
                        <span class="skp-status skp-status-${skp.status}">
                            ${this.escapeHtml(skp.status_label)}
                        </span>
                    </td>
                    <td>
                        <a href="${skp.file_url}" 
                           class="skp-pdf-link" 
                           target="_blank"
                           title="${asosiasiAdmin.strings.viewPdf}">
                            <span class="dashicons dashicons-pdf"></span>
                        </a>
                    </td>
                    <td>
                        <div class="skp-actions-group">
                            ${this.createActionButtons(skp)}
                        </div>
                    </td>
                </tr>
            `;
        },

        createActionButtons(skp) {
            const buttons = [];

            if (skp.can_edit) {
                buttons.push(`
                    <button type="button" 
                            class="button edit-skp" 
                            data-id="${skp.id}">
                        ${asosiasiAdmin.strings.edit}
                    </button>
                `);
            }

            buttons.push(`
                <button type="button" 
                        class="button delete-skp" 
                        data-id="${skp.id}">
                    ${asosiasiAdmin.strings.delete}
                </button>
                <a href="${skp.file_url}" 
                   class="button" 
                   target="_blank">
                    ${asosiasiAdmin.strings.view}
                </a>
            `);

            return buttons.join('');
        },

        renderEmptyState() {
            const message = asosiasiAdmin.strings.noSKP || 'No SKP found.';
            this.$companyList.html(`
                <tr>
                    <td colspan="7" class="skp-empty">${message}</td>
                </tr>
            `);
        },

        submitForm() {
            const formData = new FormData(this.$form[0]);
            const isEdit = !!formData.get('id');
            
            formData.append('action', isEdit ? 'update_skp_perusahaan' : 'add_skp_perusahaan');
            formData.append('nonce', asosiasiAdmin.skpNonce);
            formData.append('member_id', this.memberId);

            $.ajax({
                url: asosiasiAdmin.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: () => this.setSubmitting(true)
            })
            .done((response) => {
                if (response.success) {
                    this.showSuccess(response.data.message);
                    this.loadSKPList();
                    this.closeModal();
                } else {
                    this.showError(response.data.message);
                }
            })
            .fail(() => {
                this.showError(asosiasiAdmin.strings.saveError);
            })
            .always(() => {
                this.setSubmitting(false);
            });
        },

        confirmDelete(id) {
            if (confirm(asosiasiAdmin.strings.confirmDelete)) {
                this.deleteSKP(id);
            }
        },

        deleteSKP(id) {
            $.ajax({
                url: asosiasiAdmin.ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_skp_perusahaan',
                    id: id,
                    member_id: this.memberId,
                    nonce: asosiasiAdmin.skpNonce
                }
            })
            .done((response) => {
                if (response.success) {
                    this.showSuccess(response.data.message);
                    this.loadSKPList();
                } else {
                    this.showError(response.data.message);
                }
            })
            .fail(() => {
                this.showError(asosiasiAdmin.strings.deleteError);
            });
        },

        editSKP(id) {
            // Implementation for edit functionality
            // Will be added in future version
        },

        openModal(type) {
            this.resetForm();
            
            // Set modal title based on type
            const title = type === 'company' 
                ? asosiasiAdmin.strings.addCompanySKP 
                : asosiasiAdmin.strings.addExpertSKP;
            this.$modal.find('.skp-modal-title').text(title);
            
            // Set form type
            this.$modal.find('#skp_type').val(type);
            
            // Show modal with accessibility
            this.$modal
                .attr('aria-hidden', 'false')
                .show()
                .find('input:first').focus();
                
            // Trap focus in modal
            this.trapFocus();
            
            // Disable page scroll
            $('body').addClass('modal-open');
        },

        closeModal() {
            this.$modal
                .attr('aria-hidden', 'true')
                .hide();
            
            this.resetForm();
            
            // Enable page scroll
            $('body').removeClass('modal-open');
        },

        trapFocus() {
            const $modal = this.$modal;
            const $focusableElements = $modal.find(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            );
            
            const $firstFocusable = $focusableElements.first();
            const $lastFocusable = $focusableElements.last();
            
            $modal.on('keydown', function(e) {
                if (e.key === 'Tab' || e.keyCode === 9) {
                    if (e.shiftKey) {
                        if (document.activeElement === $firstFocusable[0]) {
                            e.preventDefault();
                            $lastFocusable.focus();
                        }
                    } else {
                        if (document.activeElement === $lastFocusable[0]) {
                            e.preventDefault();
                            $firstFocusable.focus();
                        }
                    }
                }
                
                if (e.key === 'Escape' || e.keyCode === 27) {
                    this.closeModal();
                }
            }.bind(this));
        },

        resetForm() {
            this.$form[0].reset();
            this.$form.find('[type="hidden"]').val('');
            this.$form.find('.error-message').remove();
        },

        setLoading(isLoading) {
            if (isLoading) {
                this.$companyList.html(`
                    <tr>
                        <td colspan="7" class="skp-loading">
                            ${asosiasiAdmin.strings.loading}
                        </td>
                    </tr>
                `);
            }
        },

        setSubmitting(isSubmitting) {
            const $submit = this.$form.find('[type="submit"]');
            $submit.prop('disabled', isSubmitting);
            $submit.text(isSubmitting ? asosiasiAdmin.strings.saving : asosiasiAdmin.strings.save);
        },

        showSuccess(message) {
            this.showNotice('success', message);
        },

        showError(message) {
            this.showNotice('error', message);
        },

        showNotice(type, message) {
            const $notice = $(`
                <div class="notice notice-${type} is-dismissible">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss notice</span>
                    </button>
                </div>
            `);

            $('.wp-header-end').after($notice);

            // Auto dismiss after 5 seconds
            setTimeout(() => {
                $notice.fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
        },

        escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    };

    // Initialize when document is ready
    $(document).ready(() => {
        if ($('.skp-container').length) {
            SKPHandler.init();
        }
    });

})(jQuery);