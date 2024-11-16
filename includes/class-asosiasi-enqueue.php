<?php
/**
 * Class untuk menangani semua enqueue scripts dan styles
 *
 * @package Asosiasi
 * @version 1.4.2
 * Path: includes/class-asosiasi-enqueue.php
 * 
 * Changelog:
 * 1.4.2 - 2024-03-20
 * - Added member SKP table reload script with proper dependencies
 * - Fixed script loading sequence for SKP functionality
 * 
 * 1.4.1 - Fixed path issues and script loading order
 * 1.4.0 - Added SKP table reload functionality
 * 1.3.0 - Added modal and form handling improvements
 */

class Asosiasi_Enqueue {
    private $version;

    public function __construct($version) {
        $this->version = $version;
        $this->init();
    }

    public function init() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
        
        // Special handling for SKP assets
        add_action('admin_enqueue_scripts', array($this, 'maybe_load_skp_assets'));
    }

    public function enqueue_admin_assets($hook) {
        // Global admin styles
        wp_enqueue_style(
            'asosiasi-admin-global',
            ASOSIASI_URL . 'assets/css/admin-style.css',
            array(),
            $this->version
        );

        // Dashboard specific assets
        if (strpos($hook, 'asosiasi') !== false) {
            wp_enqueue_style(
                'asosiasi-dashboard',
                ASOSIASI_URL . 'admin/css/dashboard-style.css',
                array(),
                $this->version
            );

            wp_enqueue_script(
                'asosiasi-dashboard',
                ASOSIASI_URL . 'admin/js/dashboard-script.js',
                array('jquery'),
                $this->version,
                true
            );
        }

        // Global admin scripts
        wp_enqueue_script(
            'asosiasi-admin-global',
            ASOSIASI_URL . 'assets/js/admin-script.js',
            array('jquery'),
            $this->version,
            true
        );

        wp_localize_script(
            'asosiasi-admin-global',
            'asosiasiAdmin',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'adminNonce' => wp_create_nonce('asosiasi_admin_nonce'),
                'strings' => array(
                    'confirmDelete' => __('Yakin ingin menghapus?', 'asosiasi'),
                    'deletingMember' => __('Menghapus anggota...', 'asosiasi'),
                    'memberDeleted' => __('Anggota berhasil dihapus', 'asosiasi'),
                    'error' => __('Terjadi kesalahan', 'asosiasi'),
                    'success' => __('Berhasil', 'asosiasi'),
                    'loading' => __('Memuat...', 'asosiasi')
                )
            )
        );
    }

    public function enqueue_public_assets() {
        $min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
        
        wp_enqueue_style(
            'asosiasi-public',
            ASOSIASI_URL . "public/css/asosiasi-public{$min}.css",
            array(),
            $this->version
        );

        wp_enqueue_script(
            'asosiasi-public',
            ASOSIASI_URL . "public/js/asosiasi-public{$min}.js",
            array('jquery'),
            $this->version,
            true
        );

        wp_localize_script(
            'asosiasi-public',
            'asosiasiPublic',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'publicNonce' => wp_create_nonce('asosiasi-public-nonce'),
                'strings' => array(
                    'loadingText' => __('Memuat...', 'asosiasi'),
                    'errorText' => __('Terjadi kesalahan. Silakan coba lagi.', 'asosiasi'),
                    'noResults' => __('Tidak ada anggota yang ditemukan.', 'asosiasi')
                )
            )
        );
    }

    public function maybe_load_skp_assets($hook) {
        // Only load on member view page
        if (!$this->is_member_view_page()) {
            return;
        }

        // Enqueue SKP styles
        wp_enqueue_style(
            'asosiasi-skp-perusahaan',
            ASOSIASI_URL . 'assets/css/skp-perusahaan.css',
            array(),
            $this->version
        );

        // Dashicons for PDF icon
        wp_enqueue_style('dashicons');

        // Enqueue SKP scripts in correct order
        wp_enqueue_script(
            'asosiasi-skp-perusahaan',
            ASOSIASI_URL . 'assets/js/skp-perusahaan.js',
            array('jquery'),
            $this->version,
            true
        );

        // Enqueue member SKP table reload script
        wp_enqueue_script(
            'asosiasi-member-skp-table-reload',
            ASOSIASI_URL . 'assets/js/member-skp-table-reload.js',
            array('jquery', 'asosiasi-skp-perusahaan'),
            $this->version,
            true
        );

        // Localize script
        wp_localize_script(
            'asosiasi-skp-perusahaan',
            'asosiasiSKPPerusahaan',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'skpPerusahaanNonce' => wp_create_nonce('asosiasi_skp_perusahaan_nonce'),
                'strings' => array(
                    'loading' => __('Memuat data SKP...', 'asosiasi'),
                    'noSKP' => __('Belum ada SKP yang terdaftar', 'asosiasi'),
                    'addTitle' => __('Tambah SKP', 'asosiasi'),
                    'editTitle' => __('Edit SKP', 'asosiasi'),
                    'save' => __('Simpan SKP', 'asosiasi'),
                    'update' => __('Update SKP', 'asosiasi'),
                    'saving' => __('Menyimpan...', 'asosiasi'),
                    'confirmDelete' => __('Yakin ingin menghapus SKP ini?', 'asosiasi'),
                    'saveError' => __('Gagal menyimpan SKP', 'asosiasi'),
                    'deleteError' => __('Gagal menghapus SKP', 'asosiasi'),
                    'loadError' => __('Gagal memuat data SKP', 'asosiasi'),
                    'edit' => __('Edit', 'asosiasi'),
                    'delete' => __('Hapus', 'asosiasi'),
                    'view' => __('Lihat PDF', 'asosiasi'),
                    'close' => __('Tutup', 'asosiasi'),
                    'cancel' => __('Batal', 'asosiasi'),
                    'dismiss' => __('Tutup notifikasi', 'asosiasi')
                )
            )
        );
    }

    private function is_member_view_page() {
        global $pagenow;
        return $pagenow === 'admin.php' && 
               isset($_GET['page']) && 
               $_GET['page'] === 'asosiasi-view-member';
    }
}
