<?php
/**
* Modal template untuk SKP Tenaga Ahli
* 
* @package     Asosiasi
* @subpackage  Admin/Views/SKP_Tenaga_Ahli
* @version     1.0.0
* @author      arisciwek
*
* Path: /admin/views/skp-tenaga-ahli/admin-view-member-modal-skp-tenaga-ahli.php
* 
* Description: Modal dialog untuk form tambah/edit SKP Tenaga Ahli
*              termasuk validasi dan file upload
* 
* Changelog:
* 1.0.0 - 2024-11-22
* - Initial creation
* - Added form fields for Tenaga Ahli SKP
* - Added file upload handling
*/

if (!defined('ABSPATH')) {
    exit;
}

$member_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get member's services for dropdown
$services = new Asosiasi_Services();
$member_services = $services->get_member_services($member_id);
?>

<div id="skp-tenaga-ahli-modal" class="skp-modal" role="dialog" aria-modal="true" aria-labelledby="skp-tenaga-ahli-modal-title" style="display:none;">
    <div class="skp-modal-content">
        <div class="skp-modal-header">
            <h2 id="modal-title" class="skp-modal-title"></h2>
            <button type="button" class="skp-modal-close" aria-label="<?php esc_attr_e('Close modal', 'asosiasi'); ?>">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <form id="skp-tenaga-ahli-form" method="post" enctype="multipart/form-data" class="skp-form">
            <?php wp_nonce_field('asosiasi_skp_tenaga_ahli_nonce', 'skp_nonce'); ?>
            
            <!-- Hidden Fields -->
            <input type="hidden" id="member_id" name="member_id" value="<?php echo esc_attr($member_id); ?>">
            <input type="hidden" id="skp_id" name="id" value="">

            <div class="skp-form-body">
                <!-- Service Selection -->
                <div class="skp-form-row">
                    <label for="service_id" class="skp-form-label">
                        <?php _e('Layanan', 'asosiasi'); ?>
                        <span class="required">*</span>
                    </label>
                    <div class="skp-form-field">
                        <select id="service_id" name="service_id" class="regular-text" required>
                            <option value=""><?php _e('Pilih Layanan', 'asosiasi'); ?></option>
                            <?php 
                            if ($member_services) {
                                foreach ($member_services as $service_id) {
                                    $service = $services->get_service($service_id);
                                    if ($service) {
                                        printf(
                                            '<option value="%d">%s - %s</option>',
                                            esc_attr($service['id']),
                                            esc_html($service['short_name']),
                                            esc_html($service['full_name'])
                                        );
                                    }
                                }
                            }
                            ?>
                        </select>
                        <p class="description"><?php _e('Pilih layanan untuk SKP ini', 'asosiasi'); ?></p>
                    </div>
                </div>

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
                
                <!-- START NAMA TENAGA AHLI FIELD -->
                <!-- Nama Tenaga Ahli -->
                <div class="skp-form-row">
                    <label for="nama_tenaga_ahli" class="skp-form-label">
                        <?php _e('Nama Tenaga Ahli', 'asosiasi'); ?>
                        <span class="required">*</span>
                    </label>
                    <div class="skp-form-field">
                        <input type="text" 
                               id="nama_tenaga_ahli" 
                               name="nama_tenaga_ahli" 
                               class="regular-text" 
                               required
                               autocomplete="off">
                    </div>
                </div>
                <!-- END NAMA TENAGA AHLI FIELD -->


                <!-- Penanggung jawab -->
                <div class="skp-form-row">
                    <label for="penanggung_jawab" class="skp-form-label">
                        <?php _e('Penanggung jawab', 'asosiasi'); ?>
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

                <!-- Status SKP -->
                <?php if (current_user_can('manage_options') || current_user_can('manage_skp_status')): ?>
                    <div class="skp-form-row">
                        <label for="status" class="skp-form-label">
                            <?php _e('Status SKP', 'asosiasi'); ?>
                        </label>
                        <div class="skp-form-field">
                            <select id="status" name="status" class="regular-text">
                                <option value="active"><?php _e('Aktif', 'asosiasi'); ?></option>
                                <option value="activated"><?php _e('Diaktifkan', 'asosiasi'); ?></option>
                                <option value="inactive"><?php _e('Tidak Aktif', 'asosiasi'); ?></option>
                            </select>
                            <p class="description">
                                <?php _e('Status ini akan override status default berdasarkan masa berlaku', 'asosiasi'); ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>

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
