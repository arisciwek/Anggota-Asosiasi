<?php
/**
 * Tampilan informasi tambahan anggota
 *
 * @package Asosiasi
 * @version 1.0.0
 * Path: admin/views/admin-view-member-additional-info.php
 * 
 * Changelog:
 * 1.0.0 - 2024-11-21 16:45:00 WIB
 * - Initial release
 * - Added business information section
 * - Added leader information section
 * - Added location information section
 * - Styled for middle column layout
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<!-- Middle Column - Additional Information -->
<div style="flex: 0 0 30%;">
    <!-- Business Information Card -->
    <div class="card" style="margin-top: 20px;">
        <h2 class="title" style="padding: 15px 20px; margin: 0; border-bottom: 1px solid #ddd;">
            <?php _e('Informasi Badan Usaha', 'asosiasi'); ?>
        </h2>
        <div class="inside" style="padding: 20px;">
            <table class="form-table" style="margin: 0;">
                <tr>
                    <th scope="row" style="padding: 10px 0;"><?php _e('Bidang Usaha', 'asosiasi'); ?></th>
                    <td style="padding: 10px 0;">
                        <?php echo !empty($member['business_field']) ? esc_html($member['business_field']) : '<em>' . __('Belum diisi', 'asosiasi') . '</em>'; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row" style="padding: 10px 0;"><?php _e('No. AHU', 'asosiasi'); ?></th>
                    <td style="padding: 10px 0;">
                        <?php echo !empty($member['ahu_number']) ? esc_html($member['ahu_number']) : '<em>' . __('Belum diisi', 'asosiasi') . '</em>'; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row" style="padding: 10px 0;"><?php _e('NPWP', 'asosiasi'); ?></th>
                    <td style="padding: 10px 0;">
                        <?php echo !empty($member['npwp']) ? esc_html($member['npwp']) : '<em>' . __('Belum diisi', 'asosiasi') . '</em>'; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Leader Information Card -->
    <div class="card" style="margin-top: 20px;">
        <h2 class="title" style="padding: 15px 20px; margin: 0; border-bottom: 1px solid #ddd;">
            <?php _e('Informasi Pimpinan', 'asosiasi'); ?>
        </h2>
        <div class="inside" style="padding: 20px;">
            <table class="form-table" style="margin: 0;">
                <tr>
                    <th scope="row" style="padding: 10px 0;"><?php _e('Nama Pimpinan', 'asosiasi'); ?></th>
                    <td style="padding: 10px 0;">
                        <?php echo !empty($member['company_leader']) ? esc_html($member['company_leader']) : '<em>' . __('Belum diisi', 'asosiasi') . '</em>'; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row" style="padding: 10px 0;"><?php _e('Jabatan', 'asosiasi'); ?></th>
                    <td style="padding: 10px 0;">
                        <?php echo !empty($member['leader_position']) ? esc_html($member['leader_position']) : '<em>' . __('Belum diisi', 'asosiasi') . '</em>'; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Location Information Card -->
    <div class="card" style="margin-top: 20px;">
        <h2 class="title" style="padding: 15px 20px; margin: 0; border-bottom: 1px solid #ddd;">
            <?php _e('Lokasi', 'asosiasi'); ?>
        </h2>
        <div class="inside" style="padding: 20px;">
            <table class="form-table" style="margin: 0;">
                <tr>
                    <th scope="row" style="padding: 10px 0;"><?php _e('Alamat', 'asosiasi'); ?></th>
                    <td style="padding: 10px 0;">
                        <?php 
                        if (!empty($member['company_address'])) {
                            echo nl2br(esc_html($member['company_address']));
                        } else {
                            echo '<em>' . __('Belum diisi', 'asosiasi') . '</em>';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row" style="padding: 10px 0;"><?php _e('Kota', 'asosiasi'); ?></th>
                    <td style="padding: 10px 0;">
                        <?php echo !empty($member['city']) ? esc_html($member['city']) : '<em>' . __('Belum diisi', 'asosiasi') . '</em>'; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row" style="padding: 10px 0;"><?php _e('Kode Pos', 'asosiasi'); ?></th>
                    <td style="padding: 10px 0;">
                        <?php echo !empty($member['postal_code']) ? esc_html($member['postal_code']) : '<em>' . __('Belum diisi', 'asosiasi') . '</em>'; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
