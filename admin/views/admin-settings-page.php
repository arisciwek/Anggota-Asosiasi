<?php
/**
 * Tampilan halaman pengaturan
 * 
 * @package Asosiasi
 * @version 2.1.0
 * 
 * Changelog:
 * 2.1.0 - 2024-03-13
 * - Added tab interface for settings
 * - Separated general settings and services management into different tabs
 * - Improved code organization for future tab additions
 * - Added tab state persistence using URL parameters
 */

if (!defined('ABSPATH')) {
    die;
}

// Handle CRUD Layanan
$services = new Asosiasi_Services();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        check_admin_referer('asosiasi_manage_service');
        
        $service_data = array(
            'short_name' => sanitize_text_field($_POST['short_name']),
            'full_name' => sanitize_text_field($_POST['full_name'])
        );

        switch ($_POST['action']) {
            case 'add_service':
                if ($services->add_service($service_data)) {
                    add_settings_error('asosiasi_messages', 'service_added', __('Layanan berhasil ditambahkan.', 'asosiasi'), 'success');
                }
                break;
                
            case 'edit_service':
                if ($services->update_service($_POST['service_id'], $service_data)) {
                    add_settings_error('asosiasi_messages', 'service_updated', __('Layanan berhasil diperbarui.', 'asosiasi'), 'success');
                }
                break;
                
            case 'delete_service':
                if ($services->delete_service($_POST['service_id'])) {
                    add_settings_error('asosiasi_messages', 'service_deleted', __('Layanan berhasil dihapus.', 'asosiasi'), 'success');
                }
                break;
        }
    }
}

// Get current tab
$current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';

// Get all services
$all_services = $services->get_services();

// Get service for editing if in edit mode
$edit_service = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['service_id'])) {
    $edit_service = $services->get_service($_GET['service_id']);
}

// Define tabs
$tabs = array(
    'general' => __('Pengaturan Umum', 'asosiasi'),
    'services' => __('Kelola Layanan', 'asosiasi')
);
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors('asosiasi_messages'); ?>

    <nav class="nav-tab-wrapper wp-clearfix">
        <?php
        foreach ($tabs as $tab_key => $tab_caption) {
            $active = $current_tab === $tab_key ? 'nav-tab-active' : '';
            $url = add_query_arg('tab', $tab_key);
            echo '<a href="' . esc_url($url) . '" class="nav-tab ' . $active . '">' . esc_html($tab_caption) . '</a>';
        }
        ?>
    </nav>

    <div class="tab-content">
        <?php if ($current_tab === 'general'): ?>
            <!-- General Settings Section -->
            <form method="post" action="options.php">
                <?php
                settings_fields('asosiasi_settings_group');
                do_settings_sections('asosiasi_settings_group');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="asosiasi_organization_name"><?php _e('Nama Organisasi', 'asosiasi'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="asosiasi_organization_name" name="asosiasi_organization_name" 
                                value="<?php echo esc_attr(get_option('asosiasi_organization_name')); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="asosiasi_contact_email"><?php _e('Email Kontak', 'asosiasi'); ?></label>
                        </th>
                        <td>
                            <input type="email" id="asosiasi_contact_email" name="asosiasi_contact_email" 
                                value="<?php echo esc_attr(get_option('asosiasi_contact_email')); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

        <?php elseif ($current_tab === 'services'): ?>
            <!-- Services Management Section -->
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
        <?php endif; ?>
    </div>
</div>