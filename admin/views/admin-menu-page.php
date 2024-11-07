<?php
/**
 * Tampilan halaman dashboard admin
 *
 * @package Asosiasi
 * @version 1.2.0
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die;
}

$crud = new Asosiasi_CRUD();
$total_members = count($crud->get_members());
?>

<div class="wrap">
    <h1><?php _e('Dashboard Asosiasi', 'asosiasi'); ?></h1>
    
    <div class="welcome-panel">
        <div class="welcome-panel-content">
            <h2><?php _e('Selamat Datang di Plugin Asosiasi!', 'asosiasi'); ?></h2>
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
                        <li>
                            <a href="<?php echo admin_url('admin.php?page=asosiasi-list-members'); ?>" class="button">
                                <?php _e('Lihat Semua Anggota', 'asosiasi'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="welcome-panel-column">
                    <h3><?php _e('Shortcode', 'asosiasi'); ?></h3>
                    <p><?php _e('Gunakan shortcode berikut untuk menampilkan daftar anggota:', 'asosiasi'); ?></p>
                    <code>[asosiasi_member_list]</code>
                </div>
            </div>
        </div>
    </div>
</div>