<?php
/**
 * Halaman edit foto member dengan layout dua kolom
 *
 * @package Asosiasi
 * @version 2.2.0
 * Path: admin/views/admin-edit-member-images.php
 * 
 * Changelog:
 * 2.2.0 - 2024-11-19 14:20:21
 * - Restruktur layout menjadi dua kolom
 * - Kolom kiri untuk foto utama
 * - Kolom kanan untuk foto tambahan
 * - Optimasi spacing dan alignment
 * 
 * 2.1.0 - 2024-03-13
 * - Initial release
 * - Added dedicated page for image management
 * - Added back to member navigation
 * - Improved image layout and user experience
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check untuk parameter id
$member_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Inisialisasi handlers
$crud = new Asosiasi_CRUD();
$images = new Asosiasi_Member_Images();

// Get member data
$member = $crud->get_member($member_id);
$member_images = $images->get_member_images($member_id);

if ($member) {
    // Handle image upload
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_image') {
        check_admin_referer('upload_member_image');
        
        if (!empty($_FILES['member_image'])) {
            $type = sanitize_text_field($_POST['image_type']);
            $order = isset($_POST['image_order']) ? intval($_POST['image_order']) : 0;
            
            $result = $images->upload_image($member_id, $_FILES['member_image'], $type, $order);
            
            if (is_wp_error($result)) {
                add_settings_error(
                    'member_images',
                    'upload_failed',
                    $result->get_error_message(),
                    'error'
                );
            } else {
                add_settings_error(
                    'member_images',
                    'upload_success',
                    __('Foto berhasil diupload.', 'asosiasi'),
                    'success'
                );
                
                // Refresh images data
                $member_images = $images->get_member_images($member_id);
            }
        }
    }

    // Handle image deletion
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_image') {
        check_admin_referer('delete_member_image');
        
        $type = sanitize_text_field($_POST['image_type']);
        $order = isset($_POST['image_order']) ? intval($_POST['image_order']) : 0;
        
        if ($images->delete_image($member_id, $type, $order)) {
            add_settings_error(
                'member_images',
                'delete_success',
                __('Foto berhasil dihapus.', 'asosiasi'),
                'success'
            );
            
            // Refresh images data
            $member_images = $images->get_member_images($member_id);
        }
    }
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">
            <?php printf(__('Edit Photos - %s', 'asosiasi'), esc_html($member['company_name'])); ?>
        </h1>
        
        <a href="<?php echo esc_url(admin_url('admin.php?page=asosiasi-view-member&id=' . $member_id)); ?>" 
           class="page-title-action">
            <?php _e('Back to Member', 'asosiasi'); ?>
        </a>

        <hr class="wp-header-end">
        
        <?php settings_errors('member_images'); ?>

        <div class="image-management-container">
            <!-- Kolom Kiri: Foto Utama -->
            <div class="main-image-column">
                <div class="card">
                    <div class="card-header">
                        <h3><?php _e('Main Image', 'asosiasi'); ?> <span class="required">*</span></h3>
                    </div>
                    <div class="card-body">
                        <div class="mandatory-image-section">
                            <?php if (isset($member_images['mandatory'])): ?>
                                <div class="image-preview main-image-preview">
                                    <img src="<?php echo esc_url($member_images['mandatory']['url']); ?>" 
                                         alt="<?php echo esc_attr($member['company_name']); ?>">
                                    
                                    <form method="post" class="image-actions">
                                        <?php wp_nonce_field('delete_member_image'); ?>
                                        <input type="hidden" name="action" value="delete_image">
                                        <input type="hidden" name="image_type" value="mandatory">
                                        <button type="submit" class="button" 
                                                onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this image?', 'asosiasi'); ?>')">
                                            <?php _e('Delete Image', 'asosiasi'); ?>
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="upload-placeholder main-image-placeholder">
                                    <p><?php _e('No main image uploaded', 'asosiasi'); ?></p>
                                </div>
                            <?php endif; ?>

                            <form method="post" enctype="multipart/form-data" class="upload-form main-image-form">
                                <?php wp_nonce_field('upload_member_image'); ?>
                                <input type="hidden" name="action" value="upload_image">
                                <input type="hidden" name="image_type" value="mandatory">
                                <div class="form-row">
                                    <input type="file" name="member_image" accept="image/jpeg,image/png" required>
                                    <input type="submit" class="button" 
                                           value="<?php echo isset($member_images['mandatory']) ? 
                                                 esc_attr__('Replace Image', 'asosiasi') : 
                                                 esc_attr__('Upload Image', 'asosiasi'); ?>">
                                </div>
                                <p class="image-description">
                                    <?php _e('Maximum file size: 1.5MB. Allowed formats: JPG, PNG', 'asosiasi'); ?>
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Foto Tambahan -->
            <div class="additional-images-column">
                <div class="card">
                    <div class="card-header">
                        <h3><?php _e('Additional Images', 'asosiasi'); ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="optional-images-grid">
                            <?php for ($i = 1; $i <= 3; $i++): ?>
                                <div class="optional-image">
                                    <?php if (isset($member_images['optional'][$i])): ?>
                                        <div class="image-preview">
                                            <img src="<?php echo esc_url($member_images['optional'][$i]['url']); ?>" 
                                                 alt="<?php printf(esc_attr__('Optional image %d', 'asosiasi'), $i); ?>">
                                            
                                            <form method="post" class="image-actions">
                                                <?php wp_nonce_field('delete_member_image'); ?>
                                                <input type="hidden" name="action" value="delete_image">
                                                <input type="hidden" name="image_type" value="optional">
                                                <input type="hidden" name="image_order" value="<?php echo $i; ?>">
                                                <button type="submit" class="button" 
                                                        onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this image?', 'asosiasi'); ?>')">
                                                    <?php _e('Delete', 'asosiasi'); ?>
                                                </button>
                                            </form>
                                        </div>
                                    <?php else: ?>
                                        <div class="upload-placeholder">
                                            <p><?php printf(esc_html__('Optional Image %d', 'asosiasi'), $i); ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <form method="post" enctype="multipart/form-data" class="upload-form">
                                        <?php wp_nonce_field('upload_member_image'); ?>
                                        <input type="hidden" name="action" value="upload_image">
                                        <input type="hidden" name="image_type" value="optional">
                                        <input type="hidden" name="image_order" value="<?php echo $i; ?>">
                                        <div class="form-row">
                                            <input type="file" name="member_image" accept="image/jpeg,image/png" required>
                                            <input type="submit" class="button" 
                                                   value="<?php echo isset($member_images['optional'][$i]) ? 
                                                         esc_attr__('Replace', 'asosiasi') : 
                                                         esc_attr__('Upload', 'asosiasi'); ?>">
                                        </div>
                                    </form>
                                </div>
                            <?php endfor; ?>
                        </div>
                        <p class="image-description">
                            <?php _e('Maximum file size: 1.5MB per image. Allowed formats: JPG, PNG', 'asosiasi'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
} else {
    ?>
    <div class="wrap">
        <h1><?php _e('Member Not Found', 'asosiasi'); ?></h1>
        <p><?php _e('Sorry, the member you are looking for does not exist.', 'asosiasi'); ?></p>
        <a href="<?php echo admin_url('admin.php?page=asosiasi'); ?>" class="button">
            <?php _e('Back to Members List', 'asosiasi'); ?>
        </a>
    </div>
    <?php
}
