<?php
/*
Plugin Name: Asosiasi
Plugin URI: http://example.com
Description: Plugin CRUD untuk anggota asosiasi yang berupa perusahaan.
@version 1.2.1
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

// Memuat class utama
require_once ASOSIASI_DIR . 'includes/class-asosiasi-enqueue.php';
require_once ASOSIASI_DIR . 'includes/class-asosiasi-crud.php';
require_once ASOSIASI_DIR . 'includes/class-asosiasi-services.php';
require_once ASOSIASI_DIR . 'includes/class-asosiasi.php';
require_once ASOSIASI_DIR . 'includes/class-asosiasi-admin.php';
require_once ASOSIASI_DIR . 'includes/class-asosiasi-public.php';
require_once ASOSIASI_DIR . 'includes/class-asosiasi-logger.php';

register_activation_hook(__FILE__, array('Asosiasi_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('Asosiasi_Deactivator', 'deactivate'));

// Hook untuk SKP cron
add_action('asosiasi_daily_skp_check', array('Asosiasi_SKP_Cron', 'check_skp_status'));

// Load text domain for internationalization
function asosiasi_load_textdomain() {
    load_plugin_textdomain(
        'asosiasi',
        false,
        dirname(ASOSIASI_BASENAME) . '/languages/'
    );
}
add_action('plugins_loaded', 'asosiasi_load_textdomain');

// Initialize plugin
function run_asosiasi() {
    $plugin = new Asosiasi();
    
    // Initialize enqueue handler
    new Asosiasi_Enqueue(ASOSIASI_VERSION);
    
    $plugin->run();
}

// Run plugin
run_asosiasi();