<?php
/**
 * Modal template for SKP Perusahaan
 * 
 * @package Asosiasi
 * @version 1.1.0
 * Path: admin/views/admin-view-member-modal-skp-perusahaan.php
 */

if (!defined('ABSPATH')) {
    exit;
}

$member_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>
<div id="skp-modal" class="skp-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="skp-modal-content">
        <div class="skp-modal-header">
            <h2 id="modal-title" class="skp-modal-title"></h2>
            <button type="button" class="skp-modal-close" aria-label="<?php esc_attr_e('Close modal', 'asosiasi'); ?>">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <form id="skp-form" method="post" enctype="multipart/form-data" class="skp-form">
            <?php wp_nonce_field('asosiasi_skp_perusahaan_nonce', 'skp_nonce'); ?>
            <input type="hidden" name="member_id" value="<?php echo esc_attr($member_id); ?>">
            <input type="hidden" name="id" id="skp_id" value="">
            <input type="hidden" name="skp_type" id="skp_type" value="">

            <div class="skp-form-body">
                <div class="skp-form-row">
                    <label for="nomor_skp" class="skp-form-label">
                        <?php _e('Nomor SKP', 'asosiasi'); ?>
                        <span class="required">*</span>
                    </label>
                    <div class="skp-form-field">
                        <input type="text" 
                               id="nomor_skp" 
                               name="nomor_skp" 
                               class="regular-text" 
                               required>
                    </div>
                </div>

                <div class="skp-form-row">
                    <label for="penanggung_jawab" class="skp-form-label">
                        <?php _e('Penanggung Jawab', 'asosiasi'); ?>
                        <span class="required">*</span>
                    </label>
                    <div class="skp-form-field">
                        <input type="text" 
                               id="penanggung_jawab" 
                               name="penanggung_jawab" 
                               class="regular-text" 
                               required>
                    </div>
                </div>

                <div class="skp-form-row">
                    <label for="tanggal_terbit" class="skp-form-label">
                        <?php _e('Tanggal Terbit', 'asosiasi'); ?>
                        <span class="required">*</span>
                    </label>
                    <div class="skp-form-field">
                        <input type="date" 
                               id="tanggal_terbit" 
                               name="tanggal_terbit" 
                               class="regular-text" 
                               required>
                    </div>
                </div>

                <div class="skp-form-row">
                    <label for="masa_berlaku" class="skp-form-label">
                        <?php _e('Masa Berlaku', 'asosiasi'); ?>
                        <span class="required">*</span>
                    </label>
                    <div class="skp-form-field">
                        <input type="date" 
                               id="masa_berlaku" 
                               name="masa_berlaku" 
                               class="regular-text" 
                               required>
                        <p class="description">
                            <?php _e('Tanggal berakhirnya masa berlaku SKP', 'asosiasi'); ?>
                        </p>
                    </div>
                </div>

                <div class="skp-form-row">
                    <label for="pdf_file" class="skp-form-label">
                        <?php _e('File PDF', 'asosiasi'); ?>
                        <span id="pdf-required" class="required">*</span>
                    </label>
                    <div class="skp-form-field">
                        <input type="file" 
                               id="pdf_file" 
                               name="pdf_file" 
                               class="regular-text" 
                               accept=".pdf"
                               aria-describedby="pdf-description">
                        <p id="pdf-description" class="description">
                            <?php _e('Upload file PDF SKP. Maksimal 2MB.', 'asosiasi'); ?>
                        </p>
                        <div id="current-file" class="skp-current-file"></div>
                    </div>
                </div>
            </div>

            <div class="skp-form-footer">
                <button type="button" class="button skp-modal-cancel">
                    <?php _e('Cancel', 'asosiasi'); ?>
                </button>
                <button type="submit" class="button button-primary">
                    <?php _e('Save SKP', 'asosiasi'); ?>
                </button>
            </div>
        </form>
    </div>
</div>
