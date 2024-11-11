<?php
/**
 * Plugin Name: Asosiasi
 * Plugin URI: http://example.com
 * Description: Plugin CRUD untuk anggota asosiasi yang berupa perusahaan.
 * Version: 2.1.0
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
define('ASOSIASI_VERSION', '2.1.0');

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

/**
 * Display admin notices for requirement errors
 */
function asosiasi_display_requirement_errors() {
    $errors = asosiasi_check_requirements();
    
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo '<div class="notice notice-error"><p>' . esc_html($error) . '</p></div>';
        }
        // Deactivate plugin
        deactivate_plugins(plugin_basename(__FILE__));
    }
}
add_action('admin_notices', 'asosiasi_display_requirement_errors');

// Only load the plugin if requirements are met
if (empty(asosiasi_check_requirements())) {
    // Core classes
    require_once ASOSIASI_DIR . 'includes/class-asosiasi-activator.php';
    require_once ASOSIASI_DIR . 'includes/class-asosiasi-deactivator.php';
    require_once ASOSIASI_DIR . 'includes/class-asosiasi-enqueue.php';
    require_once ASOSIASI_DIR . 'includes/class-asosiasi-crud.php';
    require_once ASOSIASI_DIR . 'includes/class-asosiasi-services.php';
    require_once ASOSIASI_DIR . 'includes/class-asosiasi.php';
    require_once ASOSIASI_DIR . 'includes/class-asosiasi-admin.php';
    require_once ASOSIASI_DIR . 'includes/class-asosiasi-public.php';
    require_once ASOSIASI_DIR . 'includes/class-asosiasi-member-images.php'; // Add this line

    // SKP related classes
    require_once ASOSIASI_DIR . 'includes/class-asosiasi-skp-perusahaan.php';
    require_once ASOSIASI_DIR . 'includes/class-asosiasi-skp-cron.php';
    require_once ASOSIASI_DIR . 'includes/class-asosiasi-ajax-skp-perusahaan.php';

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

    /**
     * Initialize plugin
     */
    function run_asosiasi() {
        // Initialize main plugin class
        $plugin = new Asosiasi();
        
        // Initialize asset handler
        new Asosiasi_Enqueue(ASOSIASI_VERSION);
        
        // Initialize AJAX handler
        new Asosiasi_Ajax_Perusahaan();
        
        // Run the plugin
        $plugin->run();

        // Load SKP functionality if needed
        if (is_admin()) {
            // Initialize SKP features
            if (class_exists('Asosiasi_SKP_Cron')) {
                Asosiasi_SKP_Cron::schedule_events();
            }
        }
    }

    // Start the plugin
    run_asosiasi();
}