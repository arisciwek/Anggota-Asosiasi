<?php
/**
 * Tampilan tab kelola layanan
 * 
 * @package Asosiasi
 * @version 2.1.0
 * 
 * Changelog:
 * 2.1.0 - 2024-03-13
 * - Separated services management into dedicated file
 * - Added proper file documentation
 * - Improved error handling and security checks
 */

if (!defined('ABSPATH')) {
    die;
}

// Initialize Services
$services = new Asosiasi_Services();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!check_admin_referer('asosiasi_manage_service')) {
        wp_die(__('Invalid security token sent.', 'asosiasi'));
    }
    
    $service_data = array(
        'short_name' => sanitize_text_field($_POST['short_name']),
        'full_name' => sanitize_text_field($_POST['full_name'])
    );

    switch ($_POST['action']) {
        case 'add_service':
            if ($services->add_service($service_data)) {
                add_settings_error('asosiasi_messages', 'service_added', 
                    __('Layanan berhasil ditambahkan.', 'asosiasi'), 'success');
            }
            break;
            
        case 'edit_service':
            if ($services->update_service($_POST['service_id'], $service_data)) {
                add_settings_error('asosiasi_messages', 'service_updated', 
                    __('Layanan berhasil diperbarui.', 'asosiasi'), 'success');
            }
            break;
            
        case 'delete_service':
            if ($services->delete_service($_POST['service_id'])) {
                add_settings_error('asosiasi_messages', 'service_deleted', 
                    __('Layanan berhasil dihapus.', 'asosiasi'), 'success');
            }
            break;
    }
}

// Get all services
$all_services = $services->get_services();

// Get service for editing if in edit mode
$edit_service = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['service_id'])) {
    $edit_service = $services->get_service($_GET['service_id']);
}
?>

<div class="services-section">
    <!-- Add/Edit Service Form -->
    <form method="post" action="<?php echo add_query_arg('tab', 'services'); ?>">
        <?php wp_nonce_field('asosiasi_manage_service'); ?>
        <input type="hidden" name="action" value="<?php echo $edit_service ? 'edit_service' : 'add_service'; ?>">
        <?php if ($edit_service): ?>
            <input type="hidden" name="service_id" value="<?php echo $edit_service['id']; ?>">
        <?php endif; ?>
        
        <h3><?php echo $edit_service ? __('Edit Layanan', 'asosiasi') : __('Tambah Layanan Baru', 'asosiasi'); ?></h3>
        
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="short_name"><?php _e('Nama Singkat', 'asosiasi'); ?></label>
                </th>
                <td>
                    <input type="text" id="short_name" name="short_name" 
                        value="<?php echo $edit_service ? esc_attr($edit_service['short_name']) : ''; ?>" 
                        class="regular-text" required>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="full_name"><?php _e('Nama Lengkap', 'asosiasi'); ?></label>
                </th>
                <td>
                    <input type="text" id="full_name" name="full_name" 
                        value="<?php echo $edit_service ? esc_attr($edit_service['full_name']) : ''; ?>" 
                        class="regular-text" required>
                </td>
            </tr>
        </table>
        
        <?php 
        submit_button(
            $edit_service ? __('Update Layanan', 'asosiasi') : __('Tambah Layanan', 'asosiasi')
        ); 
        
        if ($edit_service): ?>
            <a href="<?php echo add_query_arg('tab', 'services'); ?>" class="button">
                <?php _e('Batal', 'asosiasi'); ?>
            </a>
        <?php endif; ?>
    </form>

    <!-- Services List -->
    <h3><?php _e('Daftar Layanan', 'asosiasi'); ?></h3>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Nama Singkat', 'asosiasi'); ?></th>
                <th><?php _e('Nama Lengkap', 'asosiasi'); ?></th>
                <th><?php _e('Aksi', 'asosiasi'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($all_services): foreach ($all_services as $service): ?>
                <tr>
                    <td><?php echo esc_html($service['short_name']); ?></td>
                    <td><?php echo esc_html($service['full_name']); ?></td>
                    <td>
                        <a href="<?php echo add_query_arg(array('tab' => 'services', 'action' => 'edit', 'service_id' => $service['id'])); ?>" 
                           class="button button-small">
                            <?php _e('Edit', 'asosiasi'); ?>
                        </a>
                        <form method="post" style="display:inline;">
                            <?php wp_nonce_field('asosiasi_manage_service'); ?>
                            <input type="hidden" name="action" value="delete_service">
                            <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                            <button type="submit" class="button button-small button-link-delete" 
                                    onclick="return confirm('<?php _e('Yakin ingin menghapus layanan ini?', 'asosiasi'); ?>')">
                                <?php _e('Hapus', 'asosiasi'); ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr>
                    <td colspan="3"><?php _e('Belum ada layanan yang ditambahkan.', 'asosiasi'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>