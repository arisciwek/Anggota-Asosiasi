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
            this.$modal = $('#skp-modal');
            this.$form = $('#skp-form');
            this.$formTitle = this.$modal.find('.modal-title');
            this.$companyList = $('#company-skp-list');
            this.$submitBtn = this.$form.find('[type="submit"]');
            this.memberId = this.$container.find('[data-member-id]').first().data('member-id');
        },

        bindEvents() {
            // Add SKP button
            this.$container.on('click', '.add-skp-btn', (e) => {
                const type = $(e.currentTarget).data('type');
                this.openModal('add', type);
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

            // Edit button
            this.$container.on('click', '.edit-skp', (e) => {
                const id = $(e.currentTarget).data('id');
                this.editSKP(id);
            });

            // Delete button
            this.$container.on('click', '.delete-skp', (e) => {
                const id = $(e.currentTarget).data('id');
                this.confirmDelete(id);
            });

            // ESC key to close modal
            $(document).on('keydown', (e) => {
                if (e.key === 'Escape' && this.$modal.is(':visible')) {
                    this.closeModal();
                }
            });
        },

        editSKP(id) {
            this.setLoading(true);
            
            $.ajax({
                url: asosiasiAdmin.ajaxurl,
                type: 'GET',
                data: {
                    action: 'get_skp_perusahaan',
                    id: id,
                    nonce: asosiasiAdmin.skpNonce
                }
            })
            .done((response) => {
                if (response.success) {
                    this.populateForm(response.data.skp);
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

        populateForm(skp) {
            // Reset and prepare form
            this.resetForm();
            
            // Populate form fields
            this.$form.find('#skp_id').val(skp.id);
            this.$form.find('#nomor_skp').val(skp.nomor_skp);
            this.$form.find('#penanggung_jawab').val(skp.penanggung_jawab);
            this.$form.find('#tanggal_terbit').val(skp.tanggal_terbit);
            this.$form.find('#masa_berlaku').val(skp.masa_berlaku);
            
            // Open modal in edit mode
            this.openModal('edit', skp.type || 'company');
        },

        openModal(mode = 'add', type = 'company') {
            this.resetForm();
            
            // Set modal properties based on mode
            if (mode === 'add') {
                this.$formTitle.text('Add SKP');
                this.$submitBtn.text('Save SKP');
                this.$form.find('#pdf_file').prop('required', true);
            } else {
                this.$formTitle.text('Edit SKP');
                this.$submitBtn.text('Update SKP');
                this.$form.find('#pdf_file').prop('required', false);
            }
            
            // Show modal
            this.$modal
                .attr('aria-hidden', 'false')
                .show()
                .find('input:visible:first').focus();
        },

        closeModal() {
            this.$modal
                .attr('aria-hidden', 'true')
                .hide();
            
            this.resetForm();
        },

        submitForm() {
            const formData = new FormData(this.$form[0]);
            const isEdit = !!formData.get('id');
            
            formData.append('action', isEdit ? 'update_skp_perusahaan' : 'add_skp_perusahaan');
            formData.append('nonce', asosiasiAdmin.skpNonce);
            formData.append('member_id', this.memberId);

            this.setSubmitting(true);

            $.ajax({
                url: asosiasiAdmin.ajaxurl,
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
                    this.showError(response.data.message);
                }
            })
            .fail(() => {
                this.showError('Failed to save SKP');
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
                this.showError('Failed to delete SKP');
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
                this.showError('Failed to load SKP list');
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
                           title="View PDF">
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
                        Edit
                    </button>
                `);
            }

            buttons.push(`
                <button type="button" 
                        class="button delete-skp" 
                        data-id="${skp.id}">
                    Delete
                </button>
            `);

            return buttons.join('');
        },

        renderEmptyState() {
            this.$companyList.html(`
                <tr>
                    <td colspan="8" class="skp-empty">
                        No SKP found
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
                            Loading SKP data...
                        </td>
                    </tr>
                `);
            }
        },

        setSubmitting(isSubmitting) {
            this.$submitBtn.prop('disabled', isSubmitting)
                .text(isSubmitting ? 'Saving...' : 'Save SKP');
        },

        resetForm() {
            this.$form[0].reset();
            this.$form.find('[type="hidden"]').val('');
            this.$form.find('.error-message').remove();
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

            $('.wrap .wp-header-end').after($notice);

            // Auto dismiss
            setTimeout(() => {
                $notice.fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);

            // Handle dismiss button
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