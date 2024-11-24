<?php
/**
 * Class untuk menangani enqueue Certificate
 *
 * @package Asosiasi
 * @version 1.0.0
 * Path: includes/class-asosiasi-enqueue-certificate.php
 * 
 * Changelog:
 * 1.0.0 - 2024-11-21
 * - Initial version
 * - Added certificate scripts and styles
 * - Added strings localization
 */

class Asosiasi_Enqueue_Certificate {
    private $version;
    private $allowed_pages = array(
        'asosiasi-view-member'
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

        // Enqueue certificate styles
        wp_enqueue_style(
            'asosiasi-certificate',
            ASOSIASI_URL . 'assets/css/certificate-style.css',
            array(),
            $this->version
        );

        // Enqueue certificate scripts
        wp_enqueue_script(
            'asosiasi-certificate',
            ASOSIASI_URL . 'assets/js/certificate-handler.js',
            array('jquery'),
            $this->version,
            true
        );


        /**
         * Register and enqueue assets
         */
        //function asosiasi_register_certificate_assets() {
            // Only load on certificate settings tab
            //$current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'general';
            //if ($current_tab !== 'certificate') {
            //    return;
            //}

            // Register and enqueue CSS
            wp_register_style(
                'asosiasi-certificate-style',
                ASOSIASI_URL . 'admin/css/certificate-style.css',
                [],
                ASOSIASI_VERSION
            );

            // Register and enqueue JavaScript
            wp_register_script(
                'asosiasi-certificate-script',
                ASOSIASI_URL . 'admin/js/certificate-script.js',
                ['jquery'],
                ASOSIASI_VERSION,
                true
            );

            // Localize script
            wp_localize_script(
                'asosiasi-certificate-script',
                'asosiasi_certificate', [
                'nonce' => wp_create_nonce('asosiasi_certificate_settings'),
                'test_error' => __('Terjadi kesalahan saat mengecek direktori.', 'asosiasi'),
                'cleanup_error' => __('Terjadi kesalahan saat membersihkan file.', 'asosiasi'
                )
            ]);
        //}
        //add_action('admin_enqueue_scripts', 'asosiasi_register_certificate_assets');



        // Localize script
        wp_localize_script(
            'asosiasi-certificate',
            'asosiasiCertificate',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'certNonce' => wp_create_nonce('asosiasi_certificate_nonce'),
                'strings' => array(
                    'generating' => __('Generating certificate...', 'asosiasi'),
                    'errorGenerate' => __('Failed to generate certificate', 'asosiasi'),
                    'dismiss' => __('Dismiss this notice', 'asosiasi'),
                    'success' => __('Certificate generated successfully', 'asosiasi'),
                    'downloading' => __('Downloading certificate...', 'asosiasi'),
                    'retry' => __('Try again', 'asosiasi'),
                    'preparingDownload' => __('Preparing download...', 'asosiasi'),
                )
            )
        );
    }
}