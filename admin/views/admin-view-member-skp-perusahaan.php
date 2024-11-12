<?php
/**
 * Template for SKP Perusahaan section in member view
 *
 * @package Asosiasi
 * @version 1.2.4
 * Path: admin/views/admin-view-member-skp-perusahaan.php
 * 
 * Changelog:
 * 1.2.4 - 2024-03-17
 * - Fixed table structure to match AJAX response
 * - Added status column with proper styling
 * - Improved loading state display
 * - Updated column structure to match formatted data
 * 
 * 1.2.3 - Added service column & reordered columns
 * 1.2.2 - Added modal template include
 * 1.2.1 - Added PDF column with icon
 * 1.2.0 - Initial responsive table
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
                    <?php if (!empty($member_services)): ?>
                    <div class="skp-actions">
                        <button type="button" 
                                class="button add-skp-btn" 
                                data-type="company" 
                                data-member-id="<?php echo esc_attr($member_id); ?>">
                            <span class="dashicons dashicons-plus-alt"></span>
                            <?php _e('Tambah SKP', 'asosiasi'); ?>
                        </button>
                    </div>

                    <!-- SKP List Table -->
                    <div class="skp-table-container">
                        <table class="wp-list-table widefat fixed striped skp-table">
                            <thead>
                                <tr>
                                    <th class="column-number"><?php _e('No', 'asosiasi'); ?></th>
                                    <th class="column-nomor"><?php _e('Nomor SKP', 'asosiasi'); ?></th>
                                    <th class="column-service"><?php _e('Layanan', 'asosiasi'); ?></th>
                                    <th class="column-pj"><?php _e('Penanggung Jawab', 'asosiasi'); ?></th>
                                    <th class="column-date"><?php _e('Tanggal Terbit', 'asosiasi'); ?></th>
                                    <th class="column-date"><?php _e('Masa Berlaku', 'asosiasi'); ?></th>
                                    <th class="column-status"><?php _e('Status', 'asosiasi'); ?></th>
                                    <th class="column-pdf"><?php _e('File', 'asosiasi'); ?></th>
                                    <th class="column-actions"><?php _e('Aksi', 'asosiasi'); ?></th>
                                </tr>
                            </thead>
                            <tbody id="company-skp-list">
                                <tr class="skp-loading">
                                    <td colspan="9" class="text-center">
                                        <span class="spinner is-active"></span>
                                        <span class="loading-text">
                                            <?php _e('Memuat data SKP...', 'asosiasi'); ?>
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="notice notice-warning inline">
                        <p>
                            <?php _e('Anggota belum memiliki layanan yang terdaftar. Tambahkan layanan terlebih dahulu sebelum menambah SKP.', 'asosiasi'); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </fieldset>
        </div>

        <?php 
        // Include modal template if member has services
        if (!empty($member_services)) {
            require_once ASOSIASI_DIR . 'admin/views/admin-view-member-modal-skp-perusahaan.php';
        }
        ?>
    </div>
    <?php
}
?>