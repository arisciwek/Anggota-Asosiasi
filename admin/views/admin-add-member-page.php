<?php
/**
 * Tampilan halaman tambah/edit anggota 
 *
 * @package Asosiasi
 * @version 2.7.1
 * Path: admin/views/admin-add-member-page.php
 * 
 * Changelog:
 * 2.7.1 - 2024-11-21
 * - Removed redundant data sanitization since it's handled in CRUD layer
 * - Maintained all existing functionality and validation
 * 
 * 2.7.0 - 2024-11-21
 * - Added new company info fields
 * - Added new leader info fields
 * - Added address fields
 * - Reorganized form sections
 */

if (!defined('ABSPATH')) {
    die;
}

/**
 * Handles safe redirects in WordPress admin with fallbacks
 * 
 * @param string $url The URL to redirect to
 * @param int $member_id The member ID for the success message
 * @param string $action The action performed ('update' or 'create')
 */
function try_redirect($url, $member_id, $action = 'update') {
    // Validate parameters
    $url = esc_url_raw($url);
    $member_id = absint($member_id);
    $action = sanitize_key($action);

    // Set appropriate success message
    $message = $action === 'update' 
        ? __('Member successfully updated.', 'asosiasi')
        : __('Member successfully created.', 'asosiasi');

    // Store message in transient for display after redirect
    set_transient('asosiasi_message', array(
        'type' => 'success',
        'message' => $message,
        'member_id' => $member_id
    ), MINUTE_IN_SECONDS);

    // Try PHP redirect if headers aren't sent
    if (!headers_sent()) {
        wp_safe_redirect($url);
        exit;
    }

    // Fallback to meta refresh and JavaScript
    ?>
    <script type="text/javascript">
        console.log('Redirecting to: <?php echo esc_js($url); ?>');
        window.location.href = '<?php echo esc_js($url); ?>';
    </script>
    <noscript>
        <meta http-equiv="refresh" content="0;url=<?php echo esc_attr($url); ?>">
    </noscript>
    <div class="notice notice-info">
        <p>
            <?php 
            printf(
                /* translators: %s: URL */
                __('If you are not redirected automatically, please <a href="%s">click here</a>.', 'asosiasi'),
                esc_url($url)
            ); 
            ?>
        </p>
    </div>
    <?php
    exit;
}

/**
 * Display stored message on the target page
 * Should be called at the top of the page after headers
 */
function display_redirect_message() {
    $message = get_transient('asosiasi_message');
    if ($message) {
        delete_transient('asosiasi_message');
        ?>
        <div class="notice notice-<?php echo esc_attr($message['type']); ?> is-dismissible">
            <p><?php echo esc_html($message['message']); ?></p>
        </div>
        <?php
    }
}

$crud = new Asosiasi_CRUD();
$services = new Asosiasi_Services();
$member = null;
$is_edit = false;
$member_services = array();

if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $member_id = intval($_GET['id']);
    $member = $crud->get_member($member_id);
    $member_services = $services->get_member_services($member_id);
    $is_edit = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_member'])) {
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'save_member')) {
        error_log("Nonce verification failed");
        wp_die(__('Invalid nonce specified', 'asosiasi'));
    }

    // Collect all fields without sanitization (handled by CRUD layer)
    $data = array(
        // Basic Info
        'company_name' => $_POST['company_name'],
        'contact_person' => $_POST['contact_person'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        
        // New Fields
        'business_field' => isset($_POST['business_field']) ? $_POST['business_field'] : '',
        'ahu_number' => isset($_POST['ahu_number']) ? $_POST['ahu_number'] : '',
        'npwp' => isset($_POST['npwp']) ? $_POST['npwp'] : '',
        
        // Leader Info
        'company_leader' => isset($_POST['company_leader']) ? $_POST['company_leader'] : '',
        'leader_position' => isset($_POST['leader_position']) ? $_POST['leader_position'] : '',
        
        // Address Info
        'company_address' => isset($_POST['company_address']) ? $_POST['company_address'] : '',
        'city' => isset($_POST['city']) ? $_POST['city'] : '',
        'postal_code' => isset($_POST['postal_code']) ? $_POST['postal_code'] : '',
        'valid_until' => isset($_POST['valid_until']) ? $_POST['valid_until'] : ''
    );

    $required_fields = array('company_name', 'contact_person', 'email');
    $errors = array();
    
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            $errors[] = sprintf(__('Field %s is required.', 'asosiasi'), $field);
        }
    }

    if (!is_email($data['email'])) {
        $errors[] = __('Invalid email address.', 'asosiasi');
    }

    $selected_services = isset($_POST['member_services']) ? array_map('intval', $_POST['member_services']) : array();

    if (empty($errors)) {
        if ($is_edit) {
            if ($crud->update_member($member_id, $data)) {
                $services->add_member_services($member_id, $selected_services);
                try_redirect(
                    admin_url("admin.php?page=asosiasi-view-member&id={$member_id}"),
                    $member_id,
                    'update'
                );
            } else {
                error_log("Member update failed");
                $error = __('Gagal memperbarui data anggota.', 'asosiasi');
            }
        } else {
            error_log("Attempting to create new member");
            $new_member_id = $crud->create_member($data);
            if ($new_member_id) {
                error_log("New member created with ID: $new_member_id");
                $services->add_member_services($new_member_id, $selected_services);
                try_redirect(
                    admin_url("admin.php?page=asosiasi-view-member&id={$new_member_id}"),
                    $new_member_id,
                    'create'
                );
            } else {
                error_log("Member creation failed");
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

    <?php settings_errors('asosiasi_messages'); ?>

    <?php if (isset($error)): ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo wp_kses_post($error); ?></p>
        </div>
    <?php endif; ?>

    <form method="post" action="" id="member-form">
        <?php wp_nonce_field('save_member'); ?>

        <?php 
        // Tentukan apakah pengguna memiliki kemampuan untuk mengedit
        $disabled = '';
        if ( ! current_user_can( 'edit_asosiasi_members' ) ) {
            $disabled = 'disabled';  // Jika tidak memiliki kemampuan, set disabled
        }
        ?>

        <div class="form-wrapper">
            <div class="two-column-grid">
                <!-- Left Column -->
                <div class="left-column">
                    <!-- Informasi Umum -->
                    <div class="form-section">
                        <h2><?php _e('Informasi Umum', 'asosiasi'); ?></h2>
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
                                    <label for="business_field"><?php _e('Bidang Usaha', 'asosiasi'); ?></label>
                                </th>
                                <td>
                                    <input type="text" 
                                           id="business_field"
                                           name="business_field" 
                                           value="<?php echo $member ? esc_attr($member['business_field']) : ''; ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="ahu_number"><?php _e('Nomor Pengesahan AHU', 'asosiasi'); ?></label>
                                </th>
                                <td>
                                    <input type="text" 
                                           id="ahu_number"
                                           name="ahu_number" 
                                           value="<?php echo $member ? esc_attr($member['ahu_number']) : ''; ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="npwp"><?php _e('NPWP', 'asosiasi'); ?></label>
                                </th>
                                <td>
                                    <input type="text" 
                                           id="npwp"
                                           name="npwp" 
                                           value="<?php echo $member ? esc_attr($member['npwp']) : ''; ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Informasi Pimpinan -->
                    <div class="form-section informasi-pimpinan">
                        <h2><?php _e('Informasi Pimpinan', 'asosiasi'); ?></h2>
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">
                                    <label for="company_leader"><?php _e('Pimpinan Perusahaan', 'asosiasi'); ?></label>
                                </th>
                                <td>
                                    <input type="text" 
                                           id="company_leader"
                                           name="company_leader" 
                                           value="<?php echo $member ? esc_attr($member['company_leader']) : ''; ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="leader_position"><?php _e('Jabatan', 'asosiasi'); ?></label>
                                </th>
                                <td>
                                    <input type="text" 
                                           id="leader_position"
                                           name="leader_position" 
                                           value="<?php echo $member ? esc_attr($member['leader_position']) : ''; ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="right-column">
                    <!-- Kontak & Lokasi -->
                    <div class="form-section">
                        <h2><?php _e('Kontak & Lokasi', 'asosiasi'); ?></h2>
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">
                                    <label for="company_address"><?php _e('Alamat Perusahaan', 'asosiasi'); ?></label>
                                </th>
                                <td>
                                    <textarea id="company_address"
                                             name="company_address" 
                                             class="large-text"
                                             rows="3"><?php echo $member ? esc_textarea($member['company_address']) : ''; ?></textarea>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="city"><?php _e('Kabupaten / Kota', 'asosiasi'); ?></label>
                                </th>
                                <td>
                                    <input type="text" 
                                           id="city"
                                           name="city" 
                                           value="<?php echo $member ? esc_attr($member['city']) : ''; ?>"
                                           class="regular-text">
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <label for="postal_code"><?php _e('Kode Pos', 'asosiasi'); ?></label>
                                </th>
                                <td>
                                    <input type="text" 
                                           id="postal_code"
                                           name="postal_code" 
                                           value="<?php echo $member ? esc_attr($member['postal_code']) : ''; ?>"
                                           class="small-text"
                                           maxlength="5">
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
                        </table>
                    </div>
                </div>
            </div>

            <!-- Layanan (Full Width) -->
            <div class="services-section">
                <h2><?php _e('Layanan', 'asosiasi'); ?></h2>
                <table class="form-table">
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
            </div>


            <!-- Membership (Full Width) -->
            <div class="membership-section">
                <h2><?php _e('Keanggotaan', 'asosiasi'); ?></h2>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label><?php _e('Masa Berlaku', 'asosiasi'); ?></label>
                        </th>
                        <td>
                            <input type="date" 
                                   id="valid_until"
                                   name="valid_until" 
                                   value="<?php echo $member ? esc_attr($member['valid_until']) : ''; ?>"
                                   class="regular-text"
                                   <?php echo $disabled; ?>>

                        </td>
                    </tr>
                </table>
            </div>


        </div>

        <p class="submit">
            <input type="submit" 
                   name="submit_member" 
                   id="submit_member" 
                   class="button button-primary" 
                   value="<?php echo $is_edit ? __('Update Anggota', 'asosiasi') : __('Tambah Anggota', 'asosiasi'); ?>">
            <a href="<?php echo admin_url('admin.php?page=asosiasi'); ?>" class="button">
                <?php _e('Batal', 'asosiasi'); ?>
            </a>
        </p>
    </form>
</div>
