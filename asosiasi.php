<?php
/*
Plugin Name: Asosiasi
Plugin URI: http://example.com
Description: Plugin CRUD untuk anggota asosiasi yang berupa perusahaan.
Version: 1.1.0
Author: Nama Penulis
Author URI: http://example.com
License: GPL2
Text Domain: asosiasi
Domain Path: /languages
*/

// Mencegah akses langsung ke file
if (!defined('ABSPATH')) {
    die;
}

// Definisikan konstanta plugin
define('ASOSIASI_VERSION', '1.1.0');
define('ASOSIASI_DIR', plugin_dir_path(__FILE__));
define('ASOSIASI_URL', plugin_dir_url(__FILE__));
define('ASOSIASI_BASENAME', plugin_basename(__FILE__));

// Aktivasi dan Deaktivasi Plugin
require_once ASOSIASI_DIR . 'includes/class-asosiasi-activator.php';
require_once ASOSIASI_DIR . 'includes/class-asosiasi-deactivator.php';

register_activation_hook(__FILE__, array('Asosiasi_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('Asosiasi_Deactivator', 'deactivate'));

// Memuat inti plugin
require_once ASOSIASI_DIR . 'includes/class-asosiasi-crud.php';
require_once ASOSIASI_DIR . 'includes/class-asosiasi-services.php'; // Tambahkan ini
require_once ASOSIASI_DIR . 'includes/class-asosiasi.php';
require_once ASOSIASI_DIR . 'admin/class-asosiasi-admin.php';
require_once ASOSIASI_DIR . 'public/class-asosiasi-public.php';

// Fungsi untuk memuat assets
function asosiasi_enqueue_scripts() {
    if (is_admin()) {
        // Enqueue admin styles
        wp_enqueue_style(
            'asosiasi-admin', 
            plugin_dir_url(__FILE__) . 'assets/css/admin-style.css',
            array(),
            ASOSIASI_VERSION
        );
        
        // Enqueue admin scripts
        wp_enqueue_script(
            'asosiasi-admin', 
            plugin_dir_url(__FILE__) . 'assets/js/admin-script.js',
            array('jquery'),
            ASOSIASI_VERSION,
            true
        );

        // Add localization for admin scripts
        wp_localize_script(
            'asosiasi-admin',
            'asosiasiAdmin',
            array(
                'deleteConfirmText' => __('Are you sure you want to delete this item?', 'asosiasi'),
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('asosiasi-admin-nonce')
            )
        );
    }
}

// Hook untuk admin scripts
add_action('admin_enqueue_scripts', 'asosiasi_enqueue_scripts');
add_action('wp_enqueue_scripts', 'asosiasi_enqueue_scripts');
add_action('admin_enqueue_scripts', 'asosiasi_enqueue_scripts');

// Load text domain for internationalization
function asosiasi_load_textdomain() {
    load_plugin_textdomain(
        'asosiasi',
        false,
        dirname(ASOSIASI_BASENAME) . '/languages/'
    );
}
add_action('plugins_loaded', 'asosiasi_load_textdomain');

// Memulai Plugin
function run_asosiasi() {
    $plugin = new Asosiasi();
    $plugin->run();
}

// Inisialisasi plugin
run_asosiasi();