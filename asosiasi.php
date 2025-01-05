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
    // Validate parameters
    if (!is_string($dir) || !is_string($file)) {
        return false;
    }

    if (!is_dir($dir)) {
        return false;
    }

    // Sanitize directory and file paths
    $dir = rtrim(str_replace('\\', '/', $dir), '/');
    $file = ltrim($file, '/');

    // Cek file di current directory
    $full_path = $dir . '/' . $file;
    if (file_exists($full_path)) {
        return $full_path;
    }

    // Scan subdirectories with error handling
    try {
        $files = @scandir($dir);
        if ($files === false) {
            return false;
        }
    } catch (Exception $e) {
        error_log('Error scanning directory: ' . $e->getMessage());
        return false;
    }

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
/*
// Autoloader untuk regular classes
spl_autoload_register(function ($class) {
    // Debug log untuk melihat nilai $class yang masuk
    error_log('Autoloader called with $class: ' . var_export($class, true));

    // Validate class parameter
    if (!is_string($class)) {
        error_log('Autoloader: $class is not a string. Type: ' . gettype($class));
        return;
    }

    // Base prefix untuk semua class di plugin
    $prefix = 'Asosiasi';

    // Jika class name tidak mulai dengan prefix kita, skip
    if (strpos($class, $prefix) !== 0) {
        return;
    }

    // Pastikan class name valid sebelum melakukan str_replace
    $class_name = (string)$class; // Explicit conversion to string
    if (empty($class_name)) {
        error_log('Autoloader: $class_name is empty after conversion');
        return;
    }

    // Log sebelum melakukan str_replace
    error_log('About to str_replace on class_name: ' . var_export($class_name, true));

    // Convert class name ke file path dengan pengecekan tambahan
    try {
        $file_name = 'class-' . strtolower(
            str_replace('_', '-', $class_name)
        ) . '.php';
        
        // Log hasil konversi
        error_log('Generated file_name: ' . $file_name);
    } catch (Exception $e) {
        error_log('Error in autoloader string conversion: ' . $e->getMessage());
        return;
    }

    $base_dir = ASOSIASI_DIR . 'includes';
    
    // Cari file secara rekursif
    $file = asosiasi_find_class_file($base_dir, $file_name);
    if ($file) {
        error_log('Found file at: ' . $file);
        require $file;
        return;
    }

    error_log("Not found: " . $file_name);
});
*/

// Autoloader untuk regular classes

spl_autoload_register(function ($class) {
    // Validate class parameter
    if (!is_string($class)) {
        return;
    }

    // Base prefix untuk semua class di plugin
    $prefix = 'Asosiasi';

    // Jika class name tidak mulai dengan prefix kita, skip
    if (strpos($class, $prefix) !== 0) {
        return;
    }

    // Pastikan class name valid sebelum melakukan str_replace
    $class_name = (string)$class; // Explicit conversion to string
    if (empty($class_name)) {
        return;
    }

    // Convert class name ke file path dengan pengecekan tambahan
    try {
        $file_name = 'class-' . strtolower(
            str_replace('_', '-', $class_name)
        ) . '.php';
    } catch (Exception $e) {
        error_log('Error in autoloader string conversion: ' . $e->getMessage());
        return;
    }

    $base_dir = ASOSIASI_DIR . 'includes';
    
    // Cari file secara rekursif
    $file = asosiasi_find_class_file($base_dir, $file_name);
    if ($file) {
        require $file;
        return;
    }

    error_log("Not found: " . $file_name);
});

require_once plugin_dir_path(__FILE__) . 'includes/docgen/modules/member-certificate/verification/class-asosiasi-certificate-verification.php';


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

/**
 * Initialize dan run Asosiasi plugin
 * Menggunakan static flag untuk mencegah multiple initialization
 */
function run_asosiasi() {
    // Static flag untuk tracking initialization state
    static $initialized = false;
    
    try {
        // Skip jika sudah diinisialisasi
        if ($initialized) {
            return;
        }

        $plugin = new Asosiasi();

        // Inisialisasi DocGen hanya sekali dengan priority yang tepat
        add_action('plugins_loaded', function() {
            // Pastikan WP mPDF sudah terinisialisasi terlebih dahulu
            if (!class_exists('WP_MPDF')) {
                return;
            }

            // Include admin functions jika diperlukan
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
            
            // Check dan load DocGen module
            $docgen_checker = ASOSIASI_DIR . 'includes/docgen/class-asosiasi-docgen-checker.php';
            if (file_exists($docgen_checker)) {
                require_once $docgen_checker;
                
                // Static flag untuk modul certificate
                static $cert_module_loaded = false;
                
                if (!$cert_module_loaded && Asosiasi_DocGen_Checker::check_dependencies('Asosiasi')) {
                    new Asosiasi_DocGen_Member_Certificate_Module();
                    $cert_module_loaded = true;
                }
            }
        }, 20); // Priority 20 untuk memastikan WP mPDF (15) sudah load
        
        // Inisialisasi kelas-kelas inti yang tidak bergantung pada mPDF
        new Asosiasi_Settings();
        new Asosiasi_Enqueue_Member(ASOSIASI_VERSION);
        new Asosiasi_Enqueue_Settings(ASOSIASI_VERSION);
        new Asosiasi_Enqueue_SKP_Perusahaan(ASOSIASI_VERSION);
        new Asosiasi_Enqueue_SKP_Tenaga_Ahli(ASOSIASI_VERSION);

        // Initialize SKP handlers
        new Asosiasi_Ajax_SKP_Perusahaan();
        new Asosiasi_Ajax_Status_Skp_Perusahaan();
        new Asosiasi_Ajax_Skp_Tenaga_Ahli();
        new Asosiasi_Ajax_Status_Skp_Tenaga_Ahli();
            
        $plugin->run();

        // Set flag initialized
        $initialized = true;

    } catch (Exception $e) {
        error_log('Asosiasi plugin initialization error: ' . $e->getMessage());
        add_action('admin_notices', function() use ($e) {
            $message = sprintf(
                __('Error initializing Asosiasi plugin: %s', 'asosiasi'),
                esc_html($e->getMessage())
            );
            echo '<div class="notice notice-error"><p>' . $message . '</p></div>';
        });
    }
}
    

    // Debug code - tambahkan sebelum kode plugin lainnya
    add_action('template_redirect', function() {
        if (get_query_var('certificate_verify')) {
            error_log('Certificate verification requested');
            error_log('Member ID: ' . get_query_var('member_id'));
            error_log('Verify Code: ' . get_query_var('verify_code'));
        }
    });

    /*
     * http://wppm.local/wp-admin/contribute.php
     * http://wppm.local/wp-admin/about.php
     * https://wordpress.org/
     * https://wordpress.org/documentation/
     * https://wordpress.org/support/forums/
     * https://wordpress.org/support/forum/requests-and-feedback
    */


    function disable_wp_admin_menus() {
        // Menghapus menu 'Dashboard' dan submenu terkait (about.php, updates, etc.)
        remove_menu_page('index.php'); // Dashboard
        remove_submenu_page('index.php', 'about.php'); // About
        remove_submenu_page('index.php', 'contribute.php'); // Contribute
        remove_submenu_page('index.php', 'update-core.php'); // Updates
        remove_submenu_page('index.php', 'themes.php'); // Themes
        remove_submenu_page('index.php', 'plugin-install.php'); // Plugin Installation
        remove_submenu_page('index.php', 'themes.php?page=theme-editor'); // Theme Editor
        remove_submenu_page('index.php', 'options-general.php?page=reading'); // Settings Reading

        // Menghapus menu 'Posts', 'Media', 'Pages', dan lainnya jika perlu
        //remove_menu_page('edit.php'); // Posts
        //remove_menu_page('upload.php'); // Media
        //remove_menu_page('edit.php?post_type=page'); // Pages

        // Menghapus menu WordPress News, Documentation, Support, dan lainnya yang mengarah ke WordPress.org
        remove_menu_page('tools.php'); // Tools (jika ada submenu WordPress.org di sini)
        remove_submenu_page('tools.php', 'site-health.php'); // Site Health
    }
    add_action('admin_menu', 'disable_wp_admin_menus', 999);


    function redirect_forbidden_pages() {
        // Cek apakah halaman yang diakses adalah about.php atau contribute.php
        $forbidden_pages = array('about.php', 'contribute.php');

        if (isset($_GET['page']) && in_array($_GET['page'], $forbidden_pages)) {
            wp_redirect(admin_url()); // Redirect ke halaman Dashboard
            exit;
        }
    }
    add_action('admin_init', 'redirect_forbidden_pages');


    // Start the plugin
    run_asosiasi();
}
