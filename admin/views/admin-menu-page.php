<?php
/**
 * Dashboard dan daftar anggota asosiasi
 *
 * @package Asosiasi
 * @version 2.1.0
 * Changelog:
 * 2.1.0 - Menambahkan link ke detail anggota pada nama perusahaan
 * 2.0.0 - Menggabungkan dashboard dengan list members
 * 1.3.0 - Versi awal dashboard
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die;
}

$crud = new Asosiasi_CRUD();
$services = new Asosiasi_Services();
$total_members = count($crud->get_members());
$members = $crud->get_members();

// Handle delete action
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
        $total_members = count($members); // Update total
    }
}
?>

<div class="wrap">
    <!-- Welcome Panel -->
    <h1><?php _e('Dashboard Asosiasi', 'asosiasi'); ?></h1>
    
    <!-- Welcome Panel dengan styling yang diperbaiki -->
    <div class="welcome-panel asosiasi-welcome-panel">
        <div class="welcome-panel-content">
            <h2 class="welcome-title"><?php _e('Selamat Datang!', 'asosiasi'); ?></h2>
            <p class="about-description">
                <?php _e('Gunakan plugin ini untuk mengelola data anggota asosiasi Anda.', 'asosiasi'); ?>
            </p>
            <div class="welcome-panel-column-container">
                <div class="welcome-panel-column">
                    <h3><?php _e('Statistik Anggota', 'asosiasi'); ?></h3>
                    <p>
                        <?php 
                        printf(
                            __('Total Anggota: %d', 'asosiasi'),
                            $total_members
                        ); 
                        ?>
                    </p>
                </div>
                <div class="welcome-panel-column">
                    <h3><?php _e('Aksi Cepat', 'asosiasi'); ?></h3>
                    <ul>
                        <li>
                            <a href="<?php echo admin_url('admin.php?page=asosiasi-add-member'); ?>" class="button button-primary">
                                <?php _e('Tambah Anggota Baru', 'asosiasi'); ?>
                            </a>
                        </li>
                        <li style="margin-top: 10px;">
                            <a href="<?php echo admin_url('admin.php?page=asosiasi-settings'); ?>" class="button">
                                <?php _e('Pengaturan', 'asosiasi'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="welcome-panel-column">
                    <h3><?php _e('Shortcode', 'asosiasi'); ?></h3>
                    <p><?php _e('Gunakan shortcode berikut untuk menampilkan daftar anggota:', 'asosiasi'); ?></p>
                    <code>[asosiasi_member_list]</code>
                    <p class="description">
                        <?php _e('Opsi layout: [asosiasi_member_list layout="grid"]', 'asosiasi'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Member List Section -->
    <div class="member-list-section">
        <h2 class="wp-heading-inline"><?php _e('Daftar Anggota', 'asosiasi'); ?></h2>
        <a href="<?php echo admin_url('admin.php?page=asosiasi-add-member'); ?>" class="page-title-action">
            <?php _e('Tambah Baru', 'asosiasi'); ?>
        </a>
        
        <?php settings_errors('asosiasi_messages'); ?>

        <!-- Search Box -->
        <p class="search-box">
            <input type="search" id="member-search" name="s" 
                   placeholder="<?php esc_attr_e('Cari anggota...', 'asosiasi'); ?>">
        </p>

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
            <tbody id="member-list">
                <?php if ($members): ?>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td>
                                <strong>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=asosiasi-view-member&id=' . $member['id'])); ?>" 
                                       class="row-title">
                                        <?php echo esc_html($member['company_name']); ?>
                                    </a>
                                </strong>
                            </td>
                            <td><?php echo esc_html($member['contact_person']); ?></td>
                            <td>
                                <a href="mailto:<?php echo esc_attr($member['email']); ?>">
                                    <?php echo esc_html($member['email']); ?>
                                </a>
                            </td>
                            <td>
                                <?php if (!empty($member['phone'])): ?>
                                    <a href="tel:<?php echo esc_attr($member['phone']); ?>">
                                        <?php echo esc_html($member['phone']); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $member_services = $services->get_member_services($member['id']);
                                if ($member_services) {
                                    foreach ($member_services as $service_id) {
                                        $service = $services->get_service($service_id);
                                        if ($service) {
                                            echo sprintf(
                                                '<span class="service-tag" title="%s">%s</span> ',
                                                esc_attr($service['full_name']),
                                                esc_html($service['short_name'])
                                            );
                                        }
                                    }
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
</div>