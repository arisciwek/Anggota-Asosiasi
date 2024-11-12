/**
 * SKP Perusahaan Handler
 * 
 * @package Asosiasi
 * @version 1.4.2
 * Path: assets/js/skp-perusahaan.js
 * 
 * Changelog:
 * 1.4.2 - 2024-03-15
 * - Fixed service_id not loading in edit form
 * - Added detailed logging for debugging
 * - Enhanced error handling and validation
 * - Improved form field population logic
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
            console.log('Fetching SKP data for ID:', id);

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
                console.log('Server response:', response);

                if (response.success && response.data.skp) {
                    const skpData = response.data.skp;
                    console.log('SKP Data to populate:', skpData);
                    
                    // Open modal first
                    this.openModal('edit');
                    
                    // Then populate after a short delay to ensure modal is ready
                    setTimeout(() => {
                        this.populateForm(skpData);
                    }, 100);
                } else {
                    this.showError('Failed to load SKP data');
                    console.error('Invalid response format:', response);
                }
            })
            .fail((jqXHR, textStatus, errorThrown) => {
                console.error('AJAX request failed:', {
                    status: textStatus,
                    error: errorThrown,
                    response: jqXHR.responseText
                });
                this.showError(asosiasiSKPPerusahaan.strings.loadError);
            })
            .always(() => {
                this.setLoading(false);
            });
        },

        populateForm(data) {
            console.log('Starting form population with data:', data);
            
            if (!data) {
                console.error('No data provided to populateForm');
                return;
            }

            this.resetForm();

            try {
                // Log all available form fields
                const formFields = this.$form.find('input, select').get();
                console.log('Available form fields:', formFields.map(f => f.id));

                // Force set values using direct DOM manipulation
                this.setFieldValueDirect('skp_id', data.id);
                this.setFieldValueDirect('nomor_skp', data.nomor_skp);
                this.setFieldValueDirect('penanggung_jawab', data.penanggung_jawab);
                this.setFieldValueDirect('tanggal_terbit', data.tanggal_terbit);
                this.setFieldValueDirect('masa_berlaku', data.masa_berlaku);
                
                // Handle service selection
                const serviceSelect = document.getElementById('service_id');
                if (serviceSelect) {
                    console.log('Service select found');
                    console.log('Available options:', Array.from(serviceSelect.options).map(opt => ({value: opt.value, text: opt.text})));
                    console.log('Attempting to set service_id:', data.service_id);
                    
                    serviceSelect.value = data.service_id;
                    
                    // Verify selection
                    console.log('Service selection result:', {
                        selectedValue: serviceSelect.value,
                        expectedValue: data.service_id,
                        matched: serviceSelect.value == data.service_id
                    });
                    
                    // Trigger change event
                    serviceSelect.dispatchEvent(new Event('change'));
                } else {
                    console.error('Service select element not found');
                }

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
                    $currentFile.html(fileInfo).show();
                    $('#pdf_file').prop('required', false);
                }

                // Verify all values were set
                this.verifyFormValues();

            } catch (error) {
                console.error('Error in populateForm:', error);
                this.showError('Error populating form data');
            }
        },

        setFieldValueDirect(fieldId, value) {
            const element = document.getElementById(fieldId);
            if (element) {
                element.value = value;
                element.setAttribute('value', value);
                
                // Trigger events
                element.dispatchEvent(new Event('input', { bubbles: true }));
                element.dispatchEvent(new Event('change'));

                // Visual feedback
                element.style.backgroundColor = '#f0f7ff';
                element.style.borderColor = '#2271b1';

                console.log(`Field ${fieldId} set to:`, value);
            } else {
                console.warn(`Field ${fieldId} not found`);
            }
        },

        verifyFormValues() {
            console.log('Verifying form values:');
            this.$form.find('input, select').each(function() {
                console.log(`${this.id}: "${this.value}" (type: ${this.type})`);
            });
        },

        openModal(mode = 'add', type = 'company') {
            this.resetForm();
            
            this.$formTitle.text(mode === 'add' ? 
                asosiasiSKPPerusahaan.strings.addTitle : 
                asosiasiSKPPerusahaan.strings.editTitle
            );
            
            this.$submitBtn.text(mode === 'add' ? 
                asosiasiSKPPerusahaan.strings.save : 
                asosiasiSKPPerusahaan.strings.update
            );

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

        resetForm() {
            this.$form[0].reset();
            this.$form.find('input[type="hidden"]').val('');
            this.$form.find('.error-message').remove();
            this.$form.find('#current-file').empty();
            this.$form.find('#pdf_file').prop('required', true);
            
            this.$form.find('input, select').css({
                'backgroundColor': '',
                'borderColor': ''
            });
        },

        submitForm() {
            const formData = new FormData(this.$form[0]);
            const isEdit = !!formData.get('id');
            
            if (!this.validateForm(formData)) {
                return false;
            }

            this.setSubmitting(true);

            formData.append('action', isEdit ? 'update_skp_perusahaan' : 'add_skp_perusahaan');
            formData.append('nonce', asosiasiSKPPerusahaan.skpPerusahaanNonce);
            formData.append('member_id', this.memberId);

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

        validateForm(formData) {
            this.$form.find('.error-message').remove();
            let hasError = false;

            // Required fields
            const requiredFields = ['nomor_skp', 'penanggung_jawab', 'tanggal_terbit', 'masa_berlaku', 'service_id'];
            requiredFields.forEach(field => {
                const value = formData.get(field);
                if (!value) {
                    this.addFieldError($(`#${field}`), asosiasiSKPPerusahaan.strings.fieldRequired);
                    hasError = true;
                }
            });

            // Date validation
            const tanggalTerbit = new Date(formData.get('tanggal_terbit'));
            const masaBerlaku = new Date(formData.get('masa_berlaku'));
            
            if (masaBerlaku <= tanggalTerbit) {
                this.addFieldError($('#masa_berlaku'), asosiasiSKPPerusahaan.strings.invalidDate);
                hasError = true;
            }

            // File validation for new SKP
            const isEdit = !!formData.get('id');
            const pdfFile = formData.get('pdf_file');
            
            if (!isEdit || (isEdit && pdfFile.size > 0)) {
                if (!pdfFile || pdfFile.size === 0) {
                    this.addFieldError($('#pdf_file'), asosiasiSKPPerusahaan.strings.pdfRequired);
                    hasError = true;
                } else if (pdfFile.type !== 'application/pdf') {
                    this.addFieldError($('#pdf_file'), asosiasiSKPPerusahaan.strings.invalidFileType);
                    hasError = true;
                } else if (pdfFile.size > 5 * 1024 * 1024) {
                    this.addFieldError($('#pdf_file'), asosiasiSKPPerusahaan.strings.fileTooLarge);
                    hasError = true;
                }
            }

            return !hasError;
        },

        addFieldError($field, message) {
            $field.after(`<span class="error-message">${message}</span>`)
                  .css('border-color', '#dc3232');
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
                    this.showError(response.data.message || asosiasiSKPPerusahaan.strings.deleteError);
                }
            })
            .fail(() => {
                this.showError(asosiasiSKPPerusahaan.strings.deleteError);
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

        setSubmitting(isSubmitting) {
            this.$submitBtn.prop('disabled', isSubmitting)
                .text(isSubmitting ? 
                    asosiasiSKPPerusahaan.strings.saving : 
                    (this.$form.find('#skp_id').val() ? 
                        asosiasiSKPPerusahaan.strings.update : 
                        asosiasiSKPPerusahaan.strings.save)
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
                        <span class="screen-reader-text">
                            ${asosiasiSKPPerusahaan.strings.dismiss}
                        </span>
                    </button>
                </div>
            `);

            $('.wrap .wp-header-end').after($notice);

            // Auto dismiss after 5 seconds
            setTimeout(() => {
                $notice.fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);

            // Handle manual dismiss
            $notice.find('.notice-dismiss').on('click', function() {
                $(this).parent().fadeOut('fast', function() {
                    $(this).remove();
                });
            });
        },

        sanitizeText(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    };

    // Initialize when document is ready
    $(document).ready(() => {
        if ($('.skp-container').length) {
            SKPHandler.init();
        }
    });

})(jQuery);