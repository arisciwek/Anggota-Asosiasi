<?php
/**
 * Class untuk menangani semua enqueue scripts dan styles
 *
 * @package Asosiasi
 * @version 2.1.0
 * Path: includes/class-asosiasi-enqueue.php
 * 
 * Changelog:
 * 2.1.0 - 2024-11-19
 * - Refaktor untuk memisahkan enqueue ke subclass terpisah
 * - Pertahankan enqueue global untuk admin dan public
 * - Perbaikan path file sesuai struktur plugin
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
    }

    public function enqueue_admin_assets($hook) {
        // Global admin styles
        wp_enqueue_style(
            'asosiasi-admin-global',
            ASOSIASI_URL . 'admin/css/admin-global.css',
            array(),
            $this->version
        );

        // Global admin scripts
        wp_enqueue_script(
            'asosiasi-admin-global',
            ASOSIASI_URL . 'admin/js/admin-global.js',
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

        // Dashboard specific assets
        if (isset($_GET['page']) && $_GET['page'] === 'asosiasi') {
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
}