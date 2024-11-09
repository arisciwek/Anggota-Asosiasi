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

<div id="skp-modal" class="skp-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title" style="display:none;">
    <div class="skp-modal-content">
        <div class="skp-modal-header">
            <h2 id="modal-title" class="skp-modal-title"></h2>
            <button type="button" class="skp-modal-close" aria-label="<?php esc_attr_e('Close modal', 'asosiasi'); ?>">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <form id="skp-form" method="post" enctype="multipart/form-data" class="skp-form">
            <?php wp_nonce_field('asosiasi_skp_perusahaan_nonce', 'skp_nonce'); ?>
            
            <!-- Hidden Fields -->
            <input type="hidden" id="member_id" name="member_id" value="<?php echo esc_attr($member_id); ?>">
            <input type="hidden" id="skp_id" name="id" value="">
            <input type="hidden" id="skp_type" name="skp_type" value="">

            <div class="skp-form-body">
                <!-- Nomor SKP -->
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
                               required
                               autocomplete="off">
                    </div>
                </div>

                <!-- Penanggung Jawab -->
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
                               required
                               autocomplete="off">
                    </div>
                </div>

                <!-- Tanggal Terbit -->
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

                <!-- Masa Berlaku -->
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
                    </div>
                </div>

                <!-- File Upload -->
                <div class="skp-form-row">
                    <label for="pdf_file" class="skp-form-label">
                        <?php _e('File PDF', 'asosiasi'); ?>
                        <span id="pdf-required" class="required">*</span>
                    </label>
                    <div class="skp-form-field">
                        <input type="file" 
                               id="pdf_file" 
                               name="pdf_file" 
                               accept=".pdf"
                               required>
                        <p class="description">
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


<style>
/* Ensure modal is above other elements */
.skp-modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.7);
    z-index: 159999; /* Higher than WP admin bar */
    padding: 20px;
    overflow-y: auto;
}

.skp-modal-content {
    background: #fff;
    padding: 20px;
    max-width: 600px;
    margin: 40px auto;
    border-radius: 4px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    position: relative;
}

.skp-form-row {
    margin-bottom: 20px;
}

.skp-form-label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.skp-form-field input[type="text"],
.skp-form-field input[type="date"] {
    width: 100%;
}

.skp-current-file {
    margin-top: 10px;
    padding: 10px;
    background: #f0f0f1;
    border-radius: 4px;
}

.skp-form-footer {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #ddd;
    text-align: right;
}

.skp-form-footer .button {
    margin-left: 10px;
}
</style>