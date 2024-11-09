/**
 * SKP Perusahaan Handler
 * 
 * @package Asosiasi
 * @version 1.3.0
 * Path: assets/js/skp-perusahaan.js
 * 
 * Changelog:
 * 1.3.0 - 2024-03-13
 * - Fixed AJAX request parameters for edit operation
 * - Updated variable references from asosiasiAdmin to asosiasiSKPPerusahaan
 * - Added proper error handling for AJAX responses
 * - Fixed nonce parameter name
 * 
 * 1.2.0 - Fixed modal handling and form submission
 * 1.1.0 - Initial enhancement version
 */

(function($) {
    'use strict';

    const SKPHandler = {
        init() {
            this.cacheDom();
            this.bindEvents();
            this.loadSKPList();
        },

        cacheDom() {
            this.$container = $('.skp-container');
            this.$modal = $('#skp-modal');
            this.$form = $('#skp-form');
            this.$formTitle = this.$modal.find('#modal-title');
            this.$companyList = $('#company-skp-list');
            this.$submitBtn = this.$form.find('[type="submit"]');
            this.$closeBtn = this.$modal.find('.skp-modal-close, .skp-modal-cancel');
            this.memberId = this.$container.find('[data-member-id]').first().data('member-id');
        },
        
        bindEvents() {
            // Add SKP button
            this.$container.on('click', '.add-skp-btn', (e) => {
                const type = $(e.currentTarget).data('type');
                this.openModal('add', type);
            });

            // Modal close handlers
            this.$closeBtn.on('click', () => this.closeModal());
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

            // Edit button
            this.$container.on('click', '.edit-skp', (e) => {
                e.preventDefault();
                const id = $(e.currentTarget).data('id');
                this.editSKP(id);
            });

            // Delete button
            this.$container.on('click', '.delete-skp', (e) => {
                e.preventDefault();
                const id = $(e.currentTarget).data('id');
                this.confirmDelete(id);
            });

            // ESC key handler
            $(document).on('keydown', (e) => {
                if (e.key === 'Escape' && this.$modal.is(':visible')) {
                    this.closeModal();
                }
            });
        },

        editSKP(id) {
            $.ajax({
                url: asosiasiSKPPerusahaan.ajaxurl,
                type: 'GET',
                data: {
                    action: 'get_skp_perusahaan',
                    id: id,
                    nonce: asosiasiSKPPerusahaan.skpPerusahaanNonce
                },
                beforeSend: () => this.setLoading(true)
            })
            .done((response) => {
                if (response.success) {
                    this.populateForm(response.data.skp);
                    this.openModal('edit');
                } else {
                    this.showError(response.data.message);
                }
            })
            .fail(() => {
                this.showError(asosiasiSKPPerusahaan.strings.loadError);
            })
            .always(() => {
                this.setLoading(false);
            });
        },

        populateForm(data) {
            this.resetForm();
            
            this.$form.find('#skp_id').val(data.id);
            this.$form.find('#nomor_skp').val(data.nomor_skp);
            this.$form.find('#penanggung_jawab').val(data.penanggung_jawab);
            this.$form.find('#tanggal_terbit').val(data.tanggal_terbit);
            this.$form.find('#masa_berlaku').val(data.masa_berlaku);
            
            if (data.file_path) {
                const $currentFile = this.$form.find('#current-file');
                $currentFile.html(`File saat ini: ${data.file_name}`);
                this.$form.find('#pdf_file').prop('required', false);
            }
        },

        openModal(mode = 'add', type = 'company') {
            this.resetForm();
            
            const title = mode === 'add' ? 
                asosiasiSKPPerusahaan.strings.addTitle : 
                asosiasiSKPPerusahaan.strings.editTitle;
                
            this.$formTitle.text(title);
            this.$submitBtn.text(mode === 'add' ? 
                asosiasiSKPPerusahaan.strings.save : 
                asosiasiSKPPerusahaan.strings.update);
            
            this.$modal
                .attr('aria-hidden', 'false')
                .show()
                .find('input:visible:first').focus();
                
            $('body').addClass('modal-open');
        },

        closeModal() {
            this.$modal
                .attr('aria-hidden', 'true')
                .hide();
            
            this.resetForm();
            $('body').removeClass('modal-open');
        },

        submitForm() {
            const formData = new FormData(this.$form[0]);
            const isEdit = !!formData.get('id');
            
            formData.append('action', isEdit ? 'update_skp_perusahaan' : 'add_skp_perusahaan');
            formData.append('nonce', asosiasiSKPPerusahaan.skpPerusahaanNonce);
            formData.append('member_id', this.memberId);

            this.setSubmitting(true);

            $.ajax({
                url: asosiasiSKPPerusahaan.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false
            })
            .done((response) => {
                if (response.success) {
                    this.showSuccess(response.data.message);
                    this.loadSKPList();
                    this.closeModal();
                } else {
                    this.showError(response.data.message || asosiasiSKPPerusahaan.strings.saveError);
                }
            })
            .fail(() => {
                this.showError(asosiasiSKPPerusahaan.strings.saveError);
            })
            .always(() => {
                this.setSubmitting(false);
            });
        },

        confirmDelete(id) {
            if (confirm(asosiasiSKPPerusahaan.strings.confirmDelete)) {
                this.deleteSKP(id);
            }
        },

        deleteSKP(id) {
            $.ajax({
                url: asosiasiSKPPerusahaan.ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_skp_perusahaan',
                    id: id,
                    member_id: this.memberId,
                    nonce: asosiasiSKPPerusahaan.skpPerusahaanNonce
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
                this.showError(asosiasiSKPPerusahaan.strings.deleteError);
            });
        },

        loadSKPList() {
            $.ajax({
                url: asosiasiSKPPerusahaan.ajaxurl,
                type: 'GET',
                data: {
                    action: 'get_skp_perusahaan_list',
                    member_id: this.memberId,
                    nonce: asosiasiSKPPerusahaan.skpPerusahaanNonce
                },
                beforeSend: () => this.setLoading(true)
            })
            .done((response) => {
                if (response.success) {
                    this.renderSKPList(response.data.skp_list);
                } else {
                    this.showError(response.data.message);
                }
            })
            .fail(() => {
                this.showError(asosiasiSKPPerusahaan.strings.loadError);
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
                           title="${asosiasiSKPPerusahaan.strings.view}">
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
                            data-id="${skp.id}"
                            title="${asosiasiSKPPerusahaan.strings.edit}">
                        ${asosiasiSKPPerusahaan.strings.edit}
                    </button>
                `);
            }

            buttons.push(`
                <button type="button" 
                        class="button delete-skp" 
                        data-id="${skp.id}"
                        title="${asosiasiSKPPerusahaan.strings.delete}">
                    ${asosiasiSKPPerusahaan.strings.delete}
                </button>
            `);

            return buttons.join('');
        },

        renderEmptyState() {
            this.$companyList.html(`
                <tr>
                    <td colspan="8" class="skp-empty">
                        ${asosiasiSKPPerusahaan.strings.noSKP}
                    </td>
                </tr>
            `);
        },

        setLoading(isLoading) {
            if (isLoading) {
                this.$companyList.html(`
                    <tr>
                        <td colspan="8" class="skp-loading">
                            <span class="spinner is-active"></span>
                            ${asosiasiSKPPerusahaan.strings.loading}
                        </td>
                    </tr>
                `);
            }
        },

        setSubmitting(isSubmitting) {
            this.$submitBtn.prop('disabled', isSubmitting)
                .text(isSubmitting ? 
                    asosiasiSKPPerusahaan.strings.saving : 
                    asosiasiSKPPerusahaan.strings.save);
        },

        resetForm() {
            this.$form[0].reset();
            this.$form.find('[type="hidden"]').val('');
            this.$form.find('.error-message').remove();
            this.$form.find('#current-file').empty();
            this.$form.find('#pdf_file').prop('required', true);
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
                        <span class="screen-reader-text">
                            ${asosiasiSKPPerusahaan.strings.dismiss}
                        </span>
                    </button>
                </div>
            `);

            $('.wrap .wp-header-end').after($notice);

            setTimeout(() => {
                $notice.fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);

            $notice.find('.notice-dismiss').on('click', function() {
                $(this).closest('.notice').fadeOut('fast', function() {
                    $(this).remove();
                });
            });
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