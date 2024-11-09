/**
 * SKP Perusahaan Handler
 * 
 * @package Asosiasi
 * @version 1.4.0
 * Path: assets/js/skp-perusahaan.js
 * 
 * Changelog:
 * 1.4.0 - 2024-03-13
 * - Complete rewrite with direct DOM manipulation
 * - Enhanced error handling and logging
 * - Improved form field population
 * - Added visual feedback
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
            this.$container.on('click', '.add-skp-btn', (e) => {
                const type = $(e.currentTarget).data('type');
                this.openModal('add', type);
            });

            this.$closeBtn.on('click', () => this.closeModal());
            
            $(window).on('click', (e) => {
                if ($(e.target).is(this.$modal)) {
                    this.closeModal();
                }
            });

            this.$form.on('submit', (e) => {
                e.preventDefault();
                this.submitForm();
            });

            this.$container.on('click', '.edit-skp', (e) => {
                e.preventDefault();
                const id = $(e.currentTarget).data('id');
                this.editSKP(id);
            });

            this.$container.on('click', '.delete-skp', (e) => {
                e.preventDefault();
                const id = $(e.currentTarget).data('id');
                this.confirmDelete(id);
            });

            $(document).on('keydown', (e) => {
                if (e.key === 'Escape' && this.$modal.is(':visible')) {
                    this.closeModal();
                }
            });
        },

        editSKP(id) {
            if (!id) {
                console.error('Invalid SKP ID');
                return;
            }

            this.setLoading(true);

            $.ajax({
                url: asosiasiSKPPerusahaan.ajaxurl,
                type: 'GET',
                data: {
                    action: 'get_skp_perusahaan',
                    id: id,
                    nonce: asosiasiSKPPerusahaan.skpPerusahaanNonce
                }
            })
            .done((response) => {
                console.log('Raw response:', response);

                if (response.success && response.data.skp) {
                    const skpData = response.data.skp;
                    console.log('SKP Data to populate:', skpData);
                    
                    // Open modal first
                    this.openModal('edit');
                    
                    // Then populate after a short delay
                    setTimeout(() => {
                        this.populateForm(skpData);
                    }, 100);
                } else {
                    this.showError('Failed to load SKP data');
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
            console.log('Starting form population with data:', data);
            
            this.resetForm();

            try {
                // Force set values using direct DOM manipulation
                this.setFieldValueDirect('skp_id', data.id);
                this.setFieldValueDirect('nomor_skp', data.nomor_skp);
                this.setFieldValueDirect('penanggung_jawab', data.penanggung_jawab);
                this.setFieldValueDirect('tanggal_terbit', data.tanggal_terbit);
                this.setFieldValueDirect('masa_berlaku', data.masa_berlaku);

                // Handle file information
                if (data.file_path) {
                    const $currentFile = $('#current-file');
                    const fileInfo = `
                        <div class="current-file-info">
                            <p>File saat ini: ${this.sanitizeText(data.file_name || data.file_path)}</p>
                            <a href="${data.file_url}" target="_blank" class="button button-small">
                                <span class="dashicons dashicons-pdf"></span> Lihat PDF
                            </a>
                        </div>
                    `;
                    $currentFile.html(fileInfo);
                    $('#pdf_file').prop('required', false);
                }

                // Verify values were set
                this.verifyFormValues();

            } catch (error) {
                console.error('Error in populateForm:', error);
                this.showError('Error populating form data');
            }
        },

        setFieldValueDirect(fieldId, value) {
            // Get element using vanilla JS
            const element = document.getElementById(fieldId);
            if (element) {
                // Set value multiple ways
                element.value = value;
                element.setAttribute('value', value);
                
                // Force update for React-like frameworks
                const event = new Event('input', { bubbles: true });
                element.dispatchEvent(event);
                element.dispatchEvent(new Event('change'));

                // Add visual feedback
                element.style.backgroundColor = '#f0f7ff';
                element.style.borderColor = '#2271b1';

                console.log(`Field ${fieldId} set to:`, value);
            } else {
                console.warn(`Field ${fieldId} not found`);
            }
        },

        verifyFormValues() {
            console.log('Verifying form values:');
            this.$form.find('input').each(function() {
                console.log(`${this.id}: "${this.value}"`);
            });
        },

        openModal(mode = 'add', type = 'company') {
            // Reset and prepare
            this.resetForm();
            
            // Set modal properties
            this.$formTitle.text(mode === 'add' ? 
                asosiasiSKPPerusahaan.strings.addTitle : 
                asosiasiSKPPerusahaan.strings.editTitle
            );
            
            this.$submitBtn.text(mode === 'add' ? 
                asosiasiSKPPerusahaan.strings.save : 
                asosiasiSKPPerusahaan.strings.update
            );

            // Show modal
            this.$modal
                .attr('aria-hidden', 'false')
                .show()
                .find('input:visible:first').focus();

            // Add modal overlay class to body
            $('body').addClass('modal-open');
        },

        closeModal() {
            this.$modal
                .attr('aria-hidden', 'true')
                .hide();
            
            this.resetForm();
            $('body').removeClass('modal-open');
        },

        resetForm() {
            this.$form[0].reset();
            this.$form.find('input[type="hidden"]').val('');
            this.$form.find('.error-message').remove();
            this.$form.find('#current-file').empty();
            this.$form.find('#pdf_file').prop('required', true);
            
            // Remove visual feedback
            this.$form.find('input').css({
                'backgroundColor': '',
                'borderColor': ''
            });
        },

        submitForm() {
            const formData = new FormData(this.$form[0]);
            const isEdit = !!formData.get('id');
            
            // Validate form data
            const requiredFields = ['nomor_skp', 'penanggung_jawab', 'tanggal_terbit', 'masa_berlaku'];
            let hasError = false;

            // Clear previous errors
            this.$form.find('.error-message').remove();

            // Check required fields
            requiredFields.forEach(field => {
                const $field = this.$form.find(`#${field}`);
                if (!$field.val()) {
                    this.addFieldError($field, asosiasiSKPPerusahaan.strings.fieldRequired || 'Field ini wajib diisi');
                    hasError = true;
                }
            });

            // Validate dates
            const tanggalTerbit = new Date(formData.get('tanggal_terbit'));
            const masaBerlaku = new Date(formData.get('masa_berlaku'));
            
            if (masaBerlaku <= tanggalTerbit) {
                const $field = this.$form.find('#masa_berlaku');
                this.addFieldError($field, asosiasiSKPPerusahaan.strings.invalidDate || 'Masa berlaku harus lebih besar dari tanggal terbit');
                hasError = true;
            }

            // Validate PDF only if:
            // 1. This is a new submission (not edit) OR
            // 2. This is an edit but user is uploading a new file
            const pdfFile = formData.get('pdf_file');
            const needsPDFValidation = !isEdit || (isEdit && pdfFile.size > 0);
            
            if (needsPDFValidation) {
                if (!pdfFile || pdfFile.size === 0) {
                    const $field = this.$form.find('#pdf_file');
                    this.addFieldError($field, asosiasiSKPPerusahaan.strings.pdfRequired || 'File PDF wajib diupload');
                    hasError = true;
                } else if (pdfFile.type !== 'application/pdf') {
                    const $field = this.$form.find('#pdf_file');
                    this.addFieldError($field, asosiasiSKPPerusahaan.strings.invalidFileType || 'File harus berformat PDF');
                    hasError = true;
                } else if (pdfFile.size > 5 * 1024 * 1024) { // 5MB limit
                    const $field = this.$form.find('#pdf_file');
                    this.addFieldError($field, asosiasiSKPPerusahaan.strings.fileTooLarge || 'Ukuran file maksimal 5MB');
                    hasError = true;
                }
            }

            if (hasError) {
                return false;
            }

            // If editing and no new file selected, remove the file field from FormData
            if (isEdit && (!pdfFile || pdfFile.size === 0)) {
                formData.delete('pdf_file');
            }

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

        addFieldError($field, message) {
            $field.after(`<span class="error-message" style="color: #dc3232; display: block; margin-top: 5px;">${message}</span>`);
            $field.css('border-color', '#dc3232');
        },
        
        loadSKPList() {
            this.setLoading(true);

            $.ajax({
                url: asosiasiSKPPerusahaan.ajaxurl,
                type: 'GET',
                data: {
                    action: 'get_skp_perusahaan_list',
                    member_id: this.memberId,
                    nonce: asosiasiSKPPerusahaan.skpPerusahaanNonce
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
                this.showError(asosiasiSKPPerusahaan.strings.loadError);
            })
            .always(() => {
                this.setLoading(false);
            });
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

        renderSKPList(list) {
            console.log('Rendering SKP list:', list);
            
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
                    <td>${this.sanitizeText(skp.nomor_skp)}</td>
                    <td>${this.sanitizeText(skp.penanggung_jawab)}</td>
                    <td>${skp.tanggal_terbit}</td>
                    <td>${skp.masa_berlaku}</td>
                    <td>
                        <span class="skp-status skp-status-${skp.status}">
                            ${this.sanitizeText(skp.status_label)}
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
                            <button type="button" 
                                    class="button edit-skp" 
                                    data-id="${skp.id}"
                                    title="${asosiasiSKPPerusahaan.strings.edit}">
                                ${asosiasiSKPPerusahaan.strings.edit}
                            </button>
                            <button type="button" 
                                    class="button delete-skp" 
                                    data-id="${skp.id}"
                                    title="${asosiasiSKPPerusahaan.strings.delete}">
                                ${asosiasiSKPPerusahaan.strings.delete}
                            </button>
                        </div>
                    </td>
                </tr>
            `;
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

        setSubmitting(isSubmitting) {
            this.$submitBtn.prop('disabled', isSubmitting)
                .text(isSubmitting ? 
                    asosiasiSKPPerusahaan.strings.saving : 
                    asosiasiSKPPerusahaan.strings.save
                );
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

            setTimeout(() => {
                $notice.fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
        },

        sanitizeText(text) {
            if (!text) return '';
            return text.replace(/[<>]/g, '');
        }
    };

    // Initialize when document is ready
    $(document).ready(() => {
        if ($('.skp-container').length) {
            SKPHandler.init();
        }
    });

})(jQuery);