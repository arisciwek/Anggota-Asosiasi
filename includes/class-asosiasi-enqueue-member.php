<?php
/**
 * Class untuk menangani enqueue Member
 *
 * @package Asosiasi
 * @version 1.0.2
 * Path: includes/class-asosiasi-enqueue-member.php
 * 
 * Changelog:
 * 1.0.2 - 2024-11-19
 * - Menambahkan dashboard-style.css untuk halaman utama dashboard
 * 
 * 1.0.1 - 2024-11-19
 * - Menghapus enqueue untuk file yang tidak ada (member.js dan member-photos.js)
 * - Mempertahankan struktur class dan fungsi lainnya
 * 
 * 1.0.0 - Initial version
 */

class Asosiasi_Enqueue_Member {
    private $version;
    private $allowed_pages = array(
        'asosiasi-add-member',
        'asosiasi',
        'asosiasi-view-member',
        'asosiasi-edit-photos'
    );

    public function __construct($version) {
        $this->version = $version;
        $this->init();
    }

    public function init() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function enqueue_assets($hook) {
        if (!isset($_GET['page']) || !in_array($_GET['page'], $this->allowed_pages)) {
            return;
        }

        // Style untuk halaman dashboard utama
        if ($_GET['page'] === 'asosiasi') {
            wp_enqueue_style(
                'asosiasi-dashboard',
                ASOSIASI_URL . 'admin/css/dashboard-style.css',
                array(),
                $this->version
            );
        }

        // Media uploader untuk halaman edit foto
        if ($_GET['page'] === 'asosiasi-edit-photos' || $_GET['page'] === 'asosiasi-view-member') {
            wp_enqueue_media();
        }

        wp_localize_script(
            'asosiasi-admin-global',
            'asosiasiMember',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'memberNonce' => wp_create_nonce('asosiasi_member_nonce'),
                'strings' => array(
                    'savingMember' => __('Menyimpan data anggota...', 'asosiasi'),
                    'memberSaved' => __('Data anggota berhasil disimpan', 'asosiasi'),
                    'saveError' => __('Gagal menyimpan data anggota', 'asosiasi'),
                    'uploadingPhoto' => __('Mengunggah foto...', 'asosiasi'),
                    'photoUploaded' => __('Foto berhasil diunggah', 'asosiasi'),
                    'uploadError' => __('Gagal mengunggah foto', 'asosiasi'),
                    'deletingPhoto' => __('Menghapus foto...', 'asosiasi'),
                    'photoDeleted' => __('Foto berhasil dihapus', 'asosiasi'),
                    'deleteError' => __('Gagal menghapus foto', 'asosiasi'),
                    'confirmDelete' => __('Yakin ingin menghapus foto ini?', 'asosiasi')
                )
            )
        );
    }
}
