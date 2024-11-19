<?php
/**
 * Class untuk menangani enqueue Settings
 *
 * @package Asosiasi
 * @version 2.2.0
 * Path: includes/class-asosiasi-enqueue-settings.php
 * 
 * Changelog:
 * 2.2.0 - 2024-11-19
 * - Penyederhanaan enqueue hanya untuk file settings
 * - Menghapus enqueue file lama karena kode sudah dipindahkan
 * 
 * 2.1.0 - Versi awal
 */

class Asosiasi_Enqueue_Settings {
    private $version;
    private $allowed_pages = array(
        'asosiasi-settings'
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

        // Settings specific styles
        wp_enqueue_style(
            'asosiasi-settings',
            ASOSIASI_URL . 'admin/css/settings-style.css',
            array(),
            $this->version
        );

        // Settings specific script
        wp_enqueue_script(
            'asosiasi-settings',
            ASOSIASI_URL . 'admin/js/settings-script.js',
            array('jquery'),
            $this->version,
            true
        );

        // Localize script
        wp_localize_script(
            'asosiasi-settings',
            'asosiasiSettings',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'settingsNonce' => wp_create_nonce('asosiasi_settings_nonce'),
                'strings' => array(
                    'saving' => __('Menyimpan pengaturan...', 'asosiasi'),
                    'saved' => __('Pengaturan berhasil disimpan', 'asosiasi'),
                    'saveError' => __('Gagal menyimpan pengaturan', 'asosiasi'),
                    'confirmDelete' => __('Yakin ingin menghapus layanan ini?', 'asosiasi'),
                    'deleting' => __('Menghapus layanan...', 'asosiasi'),
                    'deleted' => __('Layanan berhasil dihapus', 'asosiasi'),
                    'deleteError' => __('Gagal menghapus layanan', 'asosiasi')
                )
            )
        );
    }
}
