<?php
/**
 * Plugin Name: Asosiasi
 * Plugin URI: http://example.com
 * Description: Plugin CRUD untuk anggota asosiasi yang berupa perusahaan.
 * Version: 2.2.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: Nama Penulis
 * Author URI: http://example.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: asosiasi
 * Domain Path: /languages
 * 
 * @package Asosiasi
 * 
 * Changelog:
 * 2.2.0 - 2024-11-17
 * - Added SKP status management feature
 * - Added status history tracking
 * - Enhanced SKP management interface
 * 
 * 2.1.0 - 2024-03-13
 * - Added member images feature
 * - Added image management system
 * - Enhanced member profile view
 * 
 * 2.0.0 - 2024-03-09
 * - Added SKP Perusahaan feature
 * - Added SKP management interface
 * - Added AJAX handlers for SKP operations
 * - Improved file organization and structure
 * 
 * 1.2.1 - 2024-03-08
 * - Fixed member listing display
 * - Added service filtering
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

// Plugin version
define('ASOSIASI_VERSION', '2.2.0');

// Plugin constants
define('ASOSIASI_FILE', __FILE__);
define('ASOSIASI_DIR', plugin_dir_path(__FILE__));
define('ASOSIASI_URL', plugin_dir_url(__FILE__));
define('ASOSIASI_BASENAME', plugin_basename(__FILE__));

// Minimum requirements
define('ASOSIASI_MIN_WP_VERSION', '5.8');
define('ASOSIASI_MIN_PHP_VERSION', '7.4');

/**
 * Check minimum requirements before loading the plugin
 */
function asosiasi_check_requirements() {
    $errors = array();

    if (version_compare(PHP_VERSION, ASOSIASI_MIN_PHP_VERSION, '<')) {
        $errors[] = sprintf(
            /* translators: 1: Current PHP version 2: Required PHP version */
            __('Asosiasi requires PHP version %2$s or higher. Your current version is %1$s', 'asosiasi'),
            PHP_VERSION,
            ASOSIASI_MIN_PHP_VERSION
        );
    }

    if (version_compare(get_bloginfo('version'), ASOSIASI_MIN_WP_VERSION, '<')) {
        $errors[] = sprintf(
            /* translators: 1: Current WordPress version 2: Required WordPress version */
            __('Asosiasi requires WordPress version %2$s or higher. Your current version is %1$s', 'asosiasi'),
            get_bloginfo('version'),
            ASOSIASI_MIN_WP_VERSION
        );
    }

    return $errors;
}

// Fungsi helper untuk mencari file secara rekursif
function asosiasi_find_class_file($dir, $file) {
    if (!is_dir($dir)) {
        return false;
    }

    // Cek file di current directory
    $full_path = $dir . '/' . $file;
    if (file_exists($full_path)) {
        return $full_path;
    }

    // Scan subdirectories
    $files = scandir($dir);
    foreach ($files as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }

        $full_item_path = $dir . '/' . $item;
        if (is_dir($full_item_path)) {
            $found = asosiasi_find_class_file($full_item_path, $file);
            if ($found) {
                return $found;
            }
        }
    }

    return false;
}

// Autoloader untuk regular classes
spl_autoload_register(function ($class) {
    // Base prefix untuk semua class di plugin
    $prefix = 'Asosiasi';

    // Jika class name tidak mulai dengan prefix kita, skip
    if (strpos($class, $prefix) !== 0) {
        return;
    }

    // Convert class name ke file path
    $file_name = 'class-' . strtolower(
        str_replace('_', '-', $class)
    ) . '.php';

    $base_dir = ASOSIASI_DIR . 'includes';
    
    // Cari file secara rekursif
    $file = asosiasi_find_class_file($base_dir, $file_name);
    if ($file) {
        require $file;
        return;
    }

    error_log("Not found: " . $file_name);
});



// Only load the plugin if requirements are met
if (empty(asosiasi_check_requirements())) {
    // Activation/Deactivation hooks
    register_activation_hook(__FILE__, array('Asosiasi_Activator', 'activate'));
    register_deactivation_hook(__FILE__, array('Asosiasi_Deactivator', 'deactivate'));

    // SKP Cron hook
    add_action('asosiasi_daily_skp_check', array('Asosiasi_SKP_Cron', 'check_skp_status'));

    /**
     * Load plugin text domain for translations
     */
    function asosiasi_load_textdomain() {
        load_plugin_textdomain(
            'asosiasi',
            false,
            dirname(ASOSIASI_BASENAME) . '/languages/'
        );
    }

    add_action('plugins_loaded', 'asosiasi_load_textdomain');
    
    function run_asosiasi() {
    $plugin = new Asosiasi();
    
    add_action('plugins_loaded', function() {
        // Include admin functions
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        
        if (file_exists(ASOSIASI_DIR . 'includes/docgen/class-docgen-checker.php')) {
            require_once ASOSIASI_DIR . 'includes/docgen/class-docgen-checker.php';
            
            if (Host_DocGen_Checker::check_dependencies('Asosiasi')) {
                // Inisialisasi module tanpa require
                new Asosiasi_DocGen_Member_Certificate_Module();
            } else {
                error_log('DocGen Implementation not properly initialized');
            }
        }

    }, 15);
    
    // Continue with regular plugin initialization...
    new Asosiasi_Settings();
    new Asosiasi_Enqueue_Member(ASOSIASI_VERSION);
    new Asosiasi_Enqueue_Settings(ASOSIASI_VERSION);

    new Asosiasi_Enqueue_SKP_Perusahaan(ASOSIASI_VERSION);
    new Asosiasi_Enqueue_SKP_Tenaga_Ahli(ASOSIASI_VERSION);

    // Initialize SKP Perusahaan handlers    
    new Asosiasi_Ajax_SKP_Perusahaan();
    new Asosiasi_Ajax_Status_Skp_Perusahaan();

    // Initialize SKP Tenaga Ahli handlers
    new Asosiasi_Ajax_Skp_Tenaga_Ahli();
    new Asosiasi_Ajax_Status_Skp_Tenaga_Ahli();
    
    $plugin->run();
}


    // Start the plugin
    run_asosiasi();
}
