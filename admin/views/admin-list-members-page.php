<?php 
$crud = new Asosiasi_CRUD();
$services = new Asosiasi_Services();
$members = $crud->get_members();

// Handle actions
if (isset($_POST['action']) && isset($_POST['member_id'])) {
    if (!wp_verify_nonce($_POST['_wpnonce'], 'delete_member_' . $_POST['member_id'])) {
        wp_die(__('Invalid nonce specified', 'asosiasi'));
    }

    $member_id = intval($_POST['member_id']);
    if ($_POST['action'] === 'delete') {
        if ($crud->delete_member($member_id)) {
            add_settings_error(
                'asosiasi_messages',
                'asosiasi_message',
                __('Anggota berhasil dihapus.', 'asosiasi'),
                'success'
            );
        }
        $members = $crud->get_members(); // Refresh list
    }
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Daftar Anggota', 'asosiasi'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=asosiasi-add-member'); ?>" class="page-title-action">
        <?php _e('Tambah Baru', 'asosiasi'); ?>
    </a>
    <hr class="wp-header-end">

    <?php settings_errors('asosiasi_messages'); ?>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col"><?php _e('Perusahaan', 'asosiasi'); ?></th>
                <th scope="col"><?php _e('Kontak', 'asosiasi'); ?></th>
                <th scope="col"><?php _e('Email', 'asosiasi'); ?></th>
                <th scope="col"><?php _e('Telepon', 'asosiasi'); ?></th>
                <th scope="col"><?php _e('Layanan', 'asosiasi'); ?></th>
                <th scope="col"><?php _e('Aksi', 'asosiasi'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($members): ?>
                <?php foreach ($members as $member): ?>
                    <tr>
                        <td><?php echo esc_html($member['company_name']); ?></td>
                        <td><?php echo esc_html($member['contact_person']); ?></td>
                        <td><?php echo esc_html($member['email']); ?></td>
                        <td><?php echo esc_html($member['phone']); ?></td>
                        <td>
                            <?php 
                            $member_services = $services->get_member_services($member['id']);
                            if ($member_services) {
                                $service_list = array();
                                foreach ($member_services as $service_id) {
                                    $service = $services->get_service($service_id);
                                    if ($service) {
                                        $service_list[] = '<span title="' . esc_attr($service['full_name']) . '">' . 
                                                        esc_html($service['short_name']) . '</span>';
                                    }
                                }
                                echo implode(', ', $service_list);
                            } else {
                                echo '<em>' . __('Tidak ada layanan', 'asosiasi') . '</em>';
                            }
                            ?>
                        </td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=asosiasi-add-member&action=edit&id=' . $member['id']); ?>" 
                               class="button button-small">
                                <?php _e('Edit', 'asosiasi'); ?>
                            </a>
                            <form method="post" style="display:inline;">
                                <?php wp_nonce_field('delete_member_' . $member['id']); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                <button type="submit" class="button button-small button-link-delete" 
                                        onclick="return confirm('<?php _e('Yakin ingin menghapus?', 'asosiasi'); ?>')">
                                    <?php _e('Hapus', 'asosiasi'); ?>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6"><?php _e('Belum ada anggota yang terdaftar.', 'asosiasi'); ?></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>