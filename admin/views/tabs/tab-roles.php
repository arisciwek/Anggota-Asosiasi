<?php
/**
 * Tampilan tab pengaturan role dan permission
 * 
 * @package Asosiasi
 * @version 2.2.1
 * Path: admin/views/tab-roles.php
 * 
 * Changelog:
 * 2.2.1 - 2024-11-16
 * - Updated capability labels for consistency
 * - Fixed manage_skp_status label
 * 2.2.0 - 2024-11-16
 * - Added manage_skp_status capability
 * 2.1.0 - 2024-03-13
 * - Initial release
 */

if (!defined('ABSPATH')) {
    die;
}

// Define default capabilities
$default_caps = array(
    'list_members' => __('Lihat Daftar Anggota Asosiasi', 'asosiasi'),
    'view_members' => __('Lihat Detail Anggota Asosiasi', 'asosiasi'),
    'add_members' => __('Tambah Anggota Asosiasi', 'asosiasi'),
    'edit_members' => __('Edit Semua Anggota Asosiasi', 'asosiasi'),
    'edit_own_members' => __('Edit Anggota Asosiasi Sendiri', 'asosiasi'),
    'delete_members' => __('Hapus Anggota Asosiasi', 'asosiasi'),
    'manage_skp_status' => __('Kelola Status SKP Asosiasi', 'asosiasi')
);

// Get existing role
$membership_manager_role = get_role('membership_manager');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!check_admin_referer('asosiasi_manage_roles')) {
        wp_die(__('Invalid security token sent.', 'asosiasi'));
    }

    switch ($_POST['action']) {
        case 'create_role':
            // Create role if it doesn't exist
            if (!$membership_manager_role) {
                add_role(
                    'membership_manager',
                    __('Manajer Keanggotaan', 'asosiasi'),
                    array(
                        'read' => true,
                        'list_members' => true,
                        'view_members' => true
                    )
                );
                add_settings_error(
                    'asosiasi_messages', 
                    'role_created', 
                    __('Role Manajer Keanggotaan berhasil dibuat.', 'asosiasi'), 
                    'success'
                );
            }
            break;

        case 'update_capabilities':
            if ($membership_manager_role) {
                foreach ($default_caps as $cap => $label) {
                    $has_cap = isset($_POST['capabilities'][$cap]);
                    $membership_manager_role->add_cap($cap, $has_cap);
                }
                add_settings_error(
                    'asosiasi_messages', 
                    'caps_updated', 
                    __('Hak akses berhasil diperbarui.', 'asosiasi'), 
                    'success'
                );
            }
            break;

        case 'delete_role':
            if ($membership_manager_role) {
                remove_role('membership_manager');
                add_settings_error(
                    'asosiasi_messages', 
                    'role_deleted', 
                    __('Role Manajer Keanggotaan berhasil dihapus.', 'asosiasi'), 
                    'success'
                );
            }
            break;
    }

    // Refresh role data after changes
    $membership_manager_role = get_role('membership_manager');
}
?>

<div class="roles-section">
    <div class="role-management">
        <h3><?php _e('Pengaturan Role', 'asosiasi'); ?></h3>
        
        <?php if (!$membership_manager_role): ?>
            <form method="post" action="<?php echo add_query_arg('tab', 'roles'); ?>">
                <?php wp_nonce_field('asosiasi_manage_roles'); ?>
                <input type="hidden" name="action" value="create_role">
                <p><?php _e('Role Manajer Keanggotaan belum dibuat.', 'asosiasi'); ?></p>
                <?php submit_button(__('Buat Role Manajer Keanggotaan', 'asosiasi')); ?>
            </form>
        <?php else: ?>
            <div class="role-capabilities">
                <form method="post" action="<?php echo add_query_arg('tab', 'roles'); ?>">
                    <?php wp_nonce_field('asosiasi_manage_roles'); ?>
                    <input type="hidden" name="action" value="update_capabilities">
                    
                    <table class="form-table">
                        <tr>
                            <th><?php _e('Hak Akses', 'asosiasi'); ?></th>
                            <th><?php _e('Status', 'asosiasi'); ?></th>
                        </tr>
                        <?php foreach ($default_caps as $cap => $label): ?>
                            <tr>
                                <td><?php echo esc_html($label); ?></td>
                                <td>
                                    <label class="toggle">
                                        <input type="checkbox" 
                                               name="capabilities[<?php echo esc_attr($cap); ?>]" 
                                               value="1"
                                               <?php checked($membership_manager_role->has_cap($cap)); ?>>
                                    </label>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>

                    <?php submit_button(__('Simpan Perubahan', 'asosiasi')); ?>
                </form>

                <form method="post" action="<?php echo add_query_arg('tab', 'roles'); ?>" 
                      onsubmit="return confirm('<?php esc_attr_e('Yakin ingin menghapus role ini? Semua pengguna dengan role ini akan kehilangan aksesnya.', 'asosiasi'); ?>');">
                    <?php wp_nonce_field('asosiasi_manage_roles'); ?>
                    <input type="hidden" name="action" value="delete_role">
                    <?php submit_button(
                        __('Hapus Role Manajer Keanggotaan', 'asosiasi'), 
                        'delete', 
                        'submit', 
                        true,
                        array('class' => 'button-link-delete')
                    ); ?>
                </form>
            </div>

            <div class="role-info">
                <h4><?php _e('Informasi Role Default', 'asosiasi'); ?></h4>
                <p><strong><?php _e('Administrator', 'asosiasi'); ?>:</strong> 
                    <?php _e('Memiliki akses penuh ke semua fitur.', 'asosiasi'); ?></p>
                <p><strong><?php _e('Editor', 'asosiasi'); ?>:</strong> 
                    <?php _e('Memiliki akses penuh ke semua fitur.', 'asosiasi'); ?></p>
                <p><strong><?php _e('Manajer Keanggotaan', 'asosiasi'); ?>:</strong> 
                    <?php _e('Role khusus untuk mengelola keanggotaan sesuai hak akses yang diberikan.', 'asosiasi'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>