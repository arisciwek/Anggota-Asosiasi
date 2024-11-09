<?php
/**
 * Template for SKP Perusahaan section in member view
 *
 * @package Asosiasi
 * @version 1.2.0
 * Path: admin/views/admin-view-member-skp-perusahaan.php
 * 
 * Changelog:
 * 1.2.0 - Added PDF column with proper icon handling
 * 1.1.0 - Initial responsive table implementation
 */

if (!defined('ABSPATH')) {
    exit;
}

if ($member) {
    $member_services = $services->get_member_services($member_id);
    ?>
    <div class="wrap">
        <div class="skp-container">
            <!-- SKP Perusahaan Section -->
            <fieldset class="skp-card skp-section" id="skp-perusahaan-section">
                <legend>
                    <h3><?php _e('SKP Perusahaan', 'asosiasi'); ?></h3>
                </legend>
                
                <div class="skp-content">
                    <div class="skp-actions">
                        <button type="button" 
                                class="button add-skp-btn" 
                                data-type="company" 
                                data-member-id="<?php echo esc_attr($member_id); ?>">
                            <?php _e('Add SKP', 'asosiasi'); ?>
                        </button>
                    </div>

                    <!-- SKP List Table -->
                    <table class="wp-list-table widefat fixed striped skp-table">
                        <thead>
                            <tr>
                                <th scope="col" class="skp-column-number"><?php _e('No', 'asosiasi'); ?></th>
                                <th scope="col" class="skp-column-nomor"><?php _e('Nomor SKP', 'asosiasi'); ?></th>
                                <th scope="col" class="skp-column-pj"><?php _e('Penanggung Jawab', 'asosiasi'); ?></th>
                                <th scope="col" class="skp-column-date"><?php _e('Tanggal Terbit', 'asosiasi'); ?></th>
                                <th scope="col" class="skp-column-date"><?php _e('Masa Berlaku', 'asosiasi'); ?></th>
                                <th scope="col" class="skp-column-status"><?php _e('Status', 'asosiasi'); ?></th>
                                <th scope="col" class="skp-column-pdf"><?php _e('PDF', 'asosiasi'); ?></th>
                                <th scope="col" class="skp-column-actions"><?php _e('Actions', 'asosiasi'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="company-skp-list">
                            <tr>
                                <td colspan="8" class="skp-loading">
                                    <span class="spinner is-active"></span>
                                    <?php _e('Loading SKP data...', 'asosiasi'); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </fieldset>
        </div>
    </div>
    <?php
}

// Modifikasi JavaScript untuk render PDF icon dan handling
?>
<script>
// Script moved to skp-perusahaan.js
</script>