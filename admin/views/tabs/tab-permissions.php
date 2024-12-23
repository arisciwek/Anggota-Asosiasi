<?php
/**
 * Tampilan tab pengaturan hak akses role
 * 
 * @package Asosiasi
 * @version 2.2.0
 * Path: admin/views/tab-permissions.php
 * 
 * Changelog:
 * 2.2.0 - 2024-11-16
 * - Added manage_skp_status permission
 * 2.1.0 - 2024-03-13
 * - Initial release of role permissions matrix
 */

if (!defined('ABSPATH')) {
    die;
}

// Define permissions that can be assigned
$permission_labels = array(
    'list_asosiasi_members' => __('Lihat Daftar Anggota', 'asosiasi'),
    'view_asosiasi_members' => __('Lihat Detail Anggota', 'asosiasi'),
    'add_asosiasi_members' => __('Tambah Anggota', 'asosiasi'),
    'edit_asosiasi_members' => __('Edit Semua Anggota', 'asosiasi'),
    'edit_own_asosiasi_members' => __('Edit Anggota Sendiri', 'asosiasi'),
    'delete_asosiasi_members' => __('Hapus Anggota', 'asosiasi'),
    'manage_skp_status' => __('Kelola Status SKP', 'asosiasi')
);

// Get all editable roles
$all_roles = get_editable_roles();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_role_permissions') {
    if (!check_admin_referer('asosiasi_manage_permissions')) {
        wp_die(__('Invalid security token sent.', 'asosiasi'));
    }

    $updated = false;
    foreach ($all_roles as $role_name => $role_info) {
        // Skip administrator as they have full access
        if ($role_name === 'administrator') {
            continue;
        }

        $role = get_role($role_name);
        if ($role) {
            foreach ($permission_labels as $cap => $label) {
                $has_cap = isset($_POST['permissions'][$role_name][$cap]);
                // Only update if different from current state
                if ($role->has_cap($cap) !== $has_cap) {
                    if ($has_cap) {
                        $role->add_cap($cap);
                    } else {
                        $role->remove_cap($cap);
                    }
                    $updated = true;
                }
            }
        }
    }

    if ($updated) {
        add_settings_error(
            'asosiasi_messages', 
            'permissions_updated', 
            __('Hak akses role berhasil diperbarui.', 'asosiasi'), 
            'success'
        );
    }
}
?>

<div class="permissions-section">
    <form method="post" action="<?php echo add_query_arg('tab', 'permissions'); ?>">
        <?php wp_nonce_field('asosiasi_manage_permissions'); ?>
        <input type="hidden" name="action" value="update_role_permissions">

        <p class="description">
            <?php _e('Atur hak akses untuk setiap role dalam mengelola Anggota Asosiasi. Administrator secara otomatis memiliki akses penuh.', 'asosiasi'); ?>
        </p>

        <table class="widefat fixed permissions-matrix">
            <thead>
                <tr>
                    <th class="column-role"><?php _e('Role', 'asosiasi'); ?></th>
                    <?php foreach ($permission_labels as $cap => $label): ?>
                        <th class="column-permission">
                            <?php echo esc_html($label); ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php 
                foreach ($all_roles as $role_name => $role_info):
                    // Skip administrator
                    if ($role_name === 'administrator') continue;
                    
                    $role = get_role($role_name);
                ?>
                    <tr>
                        <td class="column-role">
                            <strong><?php echo translate_user_role($role_info['name']); ?></strong>
                        </td>
                        <?php foreach ($permission_labels as $cap => $label): ?>
                            <td class="column-permission">
                                <label class="screen-reader-text">
                                    <?php echo esc_html(sprintf(
                                        /* translators: 1: permission name, 2: role name */
                                        __('%1$s untuk role %2$s', 'asosiasi'),
                                        $label,
                                        $role_info['name']
                                    )); ?>
                                </label>
                                <input type="checkbox" 
                                       name="permissions[<?php echo esc_attr($role_name); ?>][<?php echo esc_attr($cap); ?>]" 
                                       value="1"
                                       <?php checked($role->has_cap($cap)); ?>>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th class="column-role"><?php _e('Role', 'asosiasi'); ?></th>
                    <?php foreach ($permission_labels as $cap => $label): ?>
                        <th class="column-permission">
                            <?php echo esc_html($label); ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </tfoot>
        </table>

        <?php submit_button(__('Simpan Perubahan', 'asosiasi')); ?>
    </form>
</div>
