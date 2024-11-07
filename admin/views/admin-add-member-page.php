<?php
/**
 * Tampilan halaman tambah/edit anggota
 *
 * @package Asosiasi
 * @version 1.3.0
 */

if (!defined('ABSPATH')) {
    die;
}

$crud = new Asosiasi_CRUD();
$services = new Asosiasi_Services();
$member = null;
$is_edit = false;
$member_services = array();

// Enable error reporting for debugging
if (WP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Handle edit
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $member_id = intval($_GET['id']);
    $member = $crud->get_member($member_id);
    $member_services = $services->get_member_services($member_id);
    $is_edit = true;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_member'])) {
    // Verify nonce
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'save_member')) {
        wp_die(__('Invalid nonce specified', 'asosiasi'), __('Error', 'asosiasi'), array(
            'response' => 403,
            'back_link' => true,
        ));
    }

    // Sanitize and validate input data
    $data = array(
        'company_name' => sanitize_text_field($_POST['company_name']),
        'contact_person' => sanitize_text_field($_POST['contact_person']),
        'email' => sanitize_email($_POST['email']),
        'phone' => sanitize_text_field($_POST['phone'])
    );

    // Validate required fields
    $required_fields = array('company_name', 'contact_person', 'email');
    $errors = array();
    
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $errors[] = sprintf(__('Field %s is required.', 'asosiasi'), $field);
        }
    }

    // Validate email
    if (!is_email($data['email'])) {
        $errors[] = __('Invalid email address.', 'asosiasi');
    }

    // Array untuk menyimpan ID layanan yang dipilih
    $selected_services = isset($_POST['member_services']) ? array_map('intval', $_POST['member_services']) : array();

    // Log data for debugging
    if (WP_DEBUG) {
        error_log('Member Update Data: ' . print_r($data, true));
        error_log('Selected Services: ' . print_r($selected_services, true));
        error_log('Is Edit: ' . ($is_edit ? 'true' : 'false'));
        if ($is_edit) {
            error_log('Member ID: ' . $member_id);
        }
    }

    if (empty($errors)) {
        if ($is_edit) {
            // Update existing member
            $update_result = $crud->update_member($member_id, $data);
            
            if (WP_DEBUG) {
                error_log('Update Result: ' . ($update_result ? 'success' : 'failed'));
            }

            if ($update_result !== false) {
                // Update member services
                $services_result = $services->add_member_services($member_id, $selected_services);
                
                if (WP_DEBUG) {
                    error_log('Services Update Result: ' . ($services_result ? 'success' : 'failed'));
                }

                $message = __('Anggota berhasil diperbarui.', 'asosiasi');
                $member = $crud->get_member($member_id); // Refresh data
                $member_services = $services->get_member_services($member_id);
            } else {
                $error = __('Gagal memperbarui data anggota.', 'asosiasi');
            }
        } else {
            // Create new member
            $new_member_id = $crud->create_member($data);
            if ($new_member_id) {
                // Add services for new member
                $services->add_member_services($new_member_id, $selected_services);
                $message = __('Anggota baru berhasil ditambahkan.', 'asosiasi');
                wp_redirect(admin_url('admin.php?page=asosiasi-list-members'));
                exit;
            } else {
                $error = __('Gagal menambahkan anggota baru.', 'asosiasi');
            }
        }
    } else {
        $error = implode('<br>', $errors);
    }
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php echo $is_edit ? __('Edit Anggota', 'asosiasi') : __('Tambah Anggota Baru', 'asosiasi'); ?>
    </h1>
    <hr class="wp-header-end">

    <?php if (isset($message)): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo wp_kses_post($error); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="" id="member-form">
        <?php wp_nonce_field('save_member'); ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="company_name"><?php _e('Nama Perusahaan', 'asosiasi'); ?> <span class="required">*</span></label>
                </th>
                <td>
                    <input type="text" 
                           id="company_name"
                           name="company_name" 
                           value="<?php echo $member ? esc_attr($member['company_name']) : ''; ?>"
                           class="regular-text"
                           required>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="contact_person"><?php _e('Nama Kontak', 'asosiasi'); ?> <span class="required">*</span></label>
                </th>
                <td>
                    <input type="text" 
                           id="contact_person"
                           name="contact_person" 
                           value="<?php echo $member ? esc_attr($member['contact_person']) : ''; ?>"
                           class="regular-text"
                           required>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="email"><?php _e('Email', 'asosiasi'); ?> <span class="required">*</span></label>
                </th>
                <td>
                    <input type="email" 
                           id="email"
                           name="email" 
                           value="<?php echo $member ? esc_attr($member['email']) : ''; ?>"
                           class="regular-text"
                           required>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="phone"><?php _e('Telepon', 'asosiasi'); ?></label>
                </th>
                <td>
                    <input type="tel" 
                           id="phone"
                           name="phone" 
                           value="<?php echo $member ? esc_attr($member['phone']) : ''; ?>"
                           class="regular-text">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label><?php _e('Layanan', 'asosiasi'); ?></label>
                </th>
                <td>
                    <?php if ($all_services = $services->get_services()): ?>
                        <div class="services-checkbox-list">
                            <?php foreach ($all_services as $service): ?>
                                <label class="service-checkbox">
                                    <input type="checkbox" 
                                           name="member_services[]" 
                                           value="<?php echo esc_attr($service['id']); ?>"
                                           <?php checked(in_array($service['id'], $member_services)); ?>>
                                    <?php echo esc_html($service['short_name']); ?> - 
                                    <span class="service-full-name">
                                        <?php echo esc_html($service['full_name']); ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="description">
                            <?php _e('Belum ada layanan yang tersedia. ', 'asosiasi'); ?>
                            <a href="<?php echo admin_url('admin.php?page=asosiasi-settings'); ?>">
                                <?php _e('Tambah layanan', 'asosiasi'); ?>
                            </a>
                        </p>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <p class="submit">
            <input type="submit" 
                   name="submit_member" 
                   id="submit_member" 
                   class="button button-primary" 
                   value="<?php echo $is_edit ? __('Update Anggota', 'asosiasi') : __('Tambah Anggota', 'asosiasi'); ?>">
            <a href="<?php echo admin_url('admin.php?page=asosiasi-list-members'); ?>" class="button">
                <?php _e('Batal', 'asosiasi'); ?>
            </a>
        </p>
    </form>
</div>