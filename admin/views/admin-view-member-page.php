<?php
/**
 * Tampilan detail member dengan foto preview
 *
 * @package Asosiasi
 * @version 2.2.0
 * Path: admin/views/admin-view-member-page.php
 * 
 * Changelog:
 * 2.2.0 - 2024-11-15
 * - Removed image upload/delete forms
 * - Added Edit Photos button
 * - Enlarged image previews
 * - Improved layout for image preview only
 * - Simplified image display section
 * 2.1.0 - 2024-03-13
 * - Added member images support
 * - Added image upload and preview interface
 * - Added mandatory and optional image handling
 * 2.0.0 - Initial version with SKP support
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check untuk kedua kemungkinan parameter id
$member_id = 0;
if (isset($_GET['amp;id'])) {
    $member_id = intval($_GET['amp;id']); 
} else if (isset($_GET['id'])) {
    $member_id = intval($_GET['id']); 
}

// Inisialisasi handlers
$crud = new Asosiasi_CRUD();
$services = new Asosiasi_Services();
$images = new Asosiasi_Member_Images();

// Get member data
$member = $crud->get_member($member_id);

if ($member) {
    $member_services = $services->get_member_services($member_id);
    $member_images = $images->get_member_images($member_id);
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">
            <?php echo esc_html($member['company_name']); ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=asosiasi-add-member&action=edit&id=' . $member_id)); ?>" 
               class="page-title-action">
                <?php _e('Edit', 'asosiasi'); ?>
            </a>
        </h1>

        <div class="wrap">
            <hr class="wp-header-end">

            <!-- Container flex -->
            <div style="display: flex; gap: 20px;">
                <!-- Left Column -->
                <div style="flex: 0 0 32%;">
                    <!-- Company Information Card -->
                    <div class="card" style="max-width: 800px; margin-top: 20px;">
                        <h2 class="title" style="padding: 15px 20px; margin: 0; border-bottom: 1px solid #ddd;">
                            <?php _e('Informasi Perusahaan', 'asosiasi'); ?>
                        </h2>
                        <div class="inside" style="padding: 20px;">
                            <table class="form-table" style="margin: 0;">
                                <tr>
                                    <th scope="row" style="padding: 10px 0;"><?php _e('Nama Perusahaan', 'asosiasi'); ?></th>
                                    <td style="padding: 10px 0;"><?php echo esc_html($member['company_name']); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row" style="padding: 10px 0;"><?php _e('Nama Kontak', 'asosiasi'); ?></th>
                                    <td style="padding: 10px 0;"><?php echo esc_html($member['contact_person']); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row" style="padding: 10px 0;"><?php _e('Email', 'asosiasi'); ?></th>
                                    <td style="padding: 10px 0;">
                                        <a href="mailto:<?php echo esc_attr($member['email']); ?>" style="color: #0073aa; text-decoration: none;">
                                            <?php echo esc_html($member['email']); ?>
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" style="padding: 10px 0;"><?php _e('No. Telpon', 'asosiasi'); ?></th>
                                    <td style="padding: 10px 0;">
                                        <?php if (!empty($member['phone'])): ?>
                                            <a href="tel:<?php echo esc_attr($member['phone']); ?>" style="color: #0073aa; text-decoration: none;">
                                                <?php echo esc_html($member['phone']); ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Services Card -->
                    <div class="card" style="max-width: 800px; margin-top: 20px;">
                        <h2 style="margin-top: 0;"><?php _e('Layanan', 'asosiasi'); ?></h2>
                        <?php if ($member_services): ?>
                            <div class="service-tags" style="margin-top: 10px;">
                                <?php 
                                foreach ($member_services as $service_id):
                                    $service = $services->get_service($service_id);
                                    if ($service):
                                ?>
                                    <span class="service-tag" style="margin-right: 10px; margin-bottom: 10px;">
                                        <span class="service-name"><?php echo esc_html($service['short_name']); ?></span>
                                        <span class="service-description" style="display: block; font-size: 0.8em; color: #666;">
                                            <?php echo esc_html($service['full_name']); ?>
                                        </span>
                                    </span>
                                <?php 
                                    endif;
                                endforeach;
                                ?>
                            </div>
                        <?php else: ?>
                            <p><em><?php _e('Tidak ada layanan yang terdaftar', 'asosiasi'); ?></em></p>
                        <?php endif; ?>
                    </div>

                    <!-- Actions Card --><!-- Actions Card -->
                    <div class="card card-aksi" style="max-width: 800px; margin-top: 20px;">
                        <h2 class="title" style="padding: 15px 20px; margin: 0; border-bottom: 1px solid #ddd;">
                            <?php _e('Aksi', 'asosiasi'); ?>
                        </h2>
                        <div class="inside" style="padding: 20px;">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=asosiasi-add-member&action=edit&id=' . $member_id)); ?>" 
                               class="button button-primary" style="margin-right: 10px;">
                                <?php _e('Edit Member', 'asosiasi'); ?>
                            </a>

                            <button type="button" class="button" style="color: #d63638; margin-right: 10px;"
                                    onclick="if(confirm('<?php esc_attr_e('Are you sure you want to delete this member?', 'asosiasi'); ?>')) { 
                                        document.getElementById('delete-member-form').submit(); 
                                    }">
                                <?php _e('Delete Member', 'asosiasi'); ?>
                            </button>

                            <a href="<?php echo esc_url(admin_url('admin.php?page=asosiasi')); ?>" 
                               class="button">
                                <?php _e('Back to List', 'asosiasi'); ?>
                            </a>

                            <form id="delete-member-form" method="post" action="<?php echo admin_url('admin.php?page=asosiasi'); ?>" style="display:none;">
                                <?php wp_nonce_field('delete_member_' . $member_id); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="member_id" value="<?php echo $member_id; ?>">
                            </form>

                            <?php 
                            // Add download certificate button through hook
                            do_action('asosiasi_after_member_info', $member_id); 
                            ?>
                        </div>
                    </div>
                </div>

                <div style="flex: 0 0 36%;">
                     <!-- Middle Column - Additional Info -->
                    <?php include ASOSIASI_DIR . 'admin/views/admin-view-member-additional-info.php'; ?>
                </div>

                <!-- Right Column - Member Images -->
                <div style="flex: 0 0 32%;">
                    <div class="card" style="margin-top: 20px;">
                        <h2 class="title" style="padding: 15px 20px; margin: 0; border-bottom: 1px solid #ddd; display: flex; justify-content: space-between; align-items: center;">
                            <?php _e('Foto Anggota', 'asosiasi'); ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=asosiasi-edit-photos&id=' . $member_id)); ?>" 
                               class="button">
                                <?php _e('Edit Photos', 'asosiasi'); ?>
                            </a>
                        </h2>
                        <div class="inside" style="padding: 20px;">
                            <!-- Main Image Preview -->
                            <div class="main-image-preview" style="margin-bottom: 30px;">
                                <h3><?php _e('Foto Utama', 'asosiasi'); ?></h3>
                                <?php if (isset($member_images['mandatory'])): ?>
                                    <img src="<?php echo esc_url($member_images['mandatory']['url']); ?>" 
                                         alt="<?php echo esc_attr($member['company_name']); ?>"
                                         style="width: 100%; max-height: 272px; object-fit: contain; display: block; margin: 10px 0;">
                                <?php else: ?>
                                    <div style="width: 100%; height: 120px; background: #f9f9f9; display: flex; align-items: center; justify-content: center; border: 1px dashed #ddd;">
                                        <p><?php _e('No main image available', 'asosiasi'); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Additional Images Preview -->
                            <?php if (!empty($member_images['optional'])): ?>
                                <div class="additional-images">
                                    <h3><?php _e('Foto Tambahan', 'asosiasi'); ?></h3>
                                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 15px;">
                                        <?php foreach ($member_images['optional'] as $order => $image): ?>
                                            <div style="aspect-ratio: 4/3; overflow: hidden;">
                                                <img src="<?php echo esc_url($image['url']); ?>" 
                                                     alt="<?php printf(esc_attr__('Additional image %d', 'asosiasi'), $order); ?>"
                                                     style="width: 100%; height: 100%; object-fit: cover;">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="wrap">
        <?php 
        $skp_perusahaan = ASOSIASI_DIR . 'admin/views/admin-view-member-skp-perusahaan.php';
        if (file_exists($skp_perusahaan)) {
            echo '<div id="skp-perusahaan-container">';
            include $skp_perusahaan;
            echo '</div>';
        }
        ?>
        <hr />
        <?php 
        $skp_tenaga_ahli = ASOSIASI_DIR . 'admin/views/skp-tenaga-ahli/admin-view-member-skp-tenaga-ahli.php';
        if (file_exists($skp_tenaga_ahli)) {
            echo '<div id="skp-tenaga-ahli-container">';
            include $skp_tenaga_ahli;
            echo '</div>';
        }
        ?>
    </div>
    <?php
} else {
    ?>
    <div class="wrap">
        <h1><?php _e('Anggota Tidak Ditemukan', 'asosiasi'); ?></h1>
        <p><?php _e('Maaf, anggota yang Anda cari tidak ditemukan.', 'asosiasi'); ?></p>
        <a href="<?php echo admin_url('admin.php?page=asosiasi'); ?>" class="button">
            <?php _e('Kembali ke Daftar Anggota', 'asosiasi'); ?>
        </a>
    </div>
    <?php
}
