<?php
/**
 * Modal template untuk perubahan status SKP Tenaga Ahli
 * 
 * @package     Asosiasi
 * @subpackage  Admin/Views/SKP_Tenaga_Ahli
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /admin/views/skp-tenaga-ahli/admin-view-member-modal-status-skp-tenaga-ahli.php
 * 
 * Description: Modal dialog untuk perubahan status SKP Tenaga Ahli
 *              termasuk validasi dan tracking history
 *
 * Changelog:
 * 1.0.0 - 2024-11-22
 * - Initial creation
 * - Added status change form
 * - Added validation and permissions
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check user permissions
$can_change_status = current_user_can('manage_options') || current_user_can('manage_skp_status');

if ($can_change_status):
?>
<!-- Status Change Modal -->
<div id="status-change-modal" class="skp-modal" role="dialog" aria-modal="true" aria-labelledby="status-modal-title" style="display:none;">
    <div class="skp-modal-content">
        <div class="skp-modal-header">
            <h2 id="status-modal-title" class="skp-modal-title">
                <?php _e('Ubah Status SKP', 'asosiasi'); ?>
            </h2>
            <button type="button" class="skp-modal-close" aria-label="<?php esc_attr_e('Close modal', 'asosiasi'); ?>">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <form id="status-change-form" method="post" class="skp-form">
            <?php wp_nonce_field('asosiasi_skp_tenaga_ahli_nonce', 'status_nonce'); ?>
            
            <!-- Hidden fields with matching IDs -->
            <input type="hidden" id="status_skp_id" name="skp_id" value="">
            <input type="hidden" id="status_skp_type" name="skp_type" value="tenaga_ahli">
            <input type="hidden" id="status_old_status" name="old_status" value="">
            <input type="hidden" id="status_new_status" name="new_status" value="">

            <div class="skp-form-body">
                <!-- Status Change Reason -->
                <div class="skp-form-row">
                    <label for="status_reason" class="skp-form-label">
                        <?php _e('Alasan Perubahan Status', 'asosiasi'); ?>
                        <span class="required">*</span>
                    </label>
                    <div class="skp-form-field">
                        <textarea id="status_reason" 
                                 name="reason" 
                                 class="large-text" 
                                 rows="4"
                                 required></textarea>
                        <p class="description">
                            <?php _e('Jelaskan alasan perubahan status SKP ini', 'asosiasi'); ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="skp-form-footer">
                <button type="button" class="button skp-modal-cancel">
                    <?php _e('Batal', 'asosiasi'); ?>
                </button>
                <button type="submit" class="button button-primary">
                    <?php _e('Simpan Perubahan', 'asosiasi'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Expose permission status to JavaScript -->
<script>
    window.can_change_status = <?php echo $can_change_status ? 'true' : 'false'; ?>;
</script>
<?php 
endif;