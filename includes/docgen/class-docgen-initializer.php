<?php
/**
 * Asset Registration for DocGen Modules
 *
 * @package     Asosiasi
 * @subpackage  DocGen
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: includes/docgen/class-docgen-initializer.php
 * 
 * Description: Handles registration of module-specific assets.
 *              Including global DocGen styles and scripts,
 *              and module-specific assets.
 * 
 * Changelog:
 * 1.0.0 - 2024-11-27 16:48:35
 * - Initial release
 * - Added global docgen styles registration
 * - Added company profile assets
 * - Added script localization
 * 
 * Dependencies:
 * - class-company-profile-module.php
 * - asosiasi-docgen-style.css
 * - company-profile-style.css
 * - company-profile-script.js
 * 
 * Usage:
 * Called internally by DocGen_Initializer
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class DocGen_Initializer {
    private static $instance = null;

    private function __construct() {
        $this->init_hooks();
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function init_hooks() {
        // Check dependencies
        add_action('admin_init', array($this, 'check_dependencies'));

        // Initialize modules setelah plugins loaded
        add_action('plugins_loaded', array($this, 'init_modules'), 20);
    }

    public function check_dependencies() {
        if (!class_exists('WP_DocGen')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                _e('WP DocGen plugin is required for document generation features.', 'asosiasi');
                echo '</p></div>';
            });
            return;
        }

        if (!class_exists('DocGen_Implementation_Module_Loader')) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>';
                _e('DocGen Implementation plugin is required for document generation features.', 'asosiasi');
                echo '</p></div>';
            });
            return;
        }
    }

    public function init_modules() {
        // Load base provider
        require_once ASOSIASI_DIR . 'includes/docgen/class-asosiasi-docgen-provider.php';
       
        // Initialize Company Profile module
        $this->init_company_profile_module();
    }

    private function init_company_profile_module() {
        // Load required files
        require_once ASOSIASI_DIR . 'includes/docgen/providers/class-company-profile-form-provider.php';
        require_once ASOSIASI_DIR . 'includes/docgen/providers/class-company-profile-json-provider.php';
        require_once ASOSIASI_DIR . 'includes/docgen/modules/class-company-profile-module.php';
        require_once ASOSIASI_DIR . 'includes/docgen/views/class-company-profile-view.php';

        // Initialize module
        CompanyProfile_Module::init();
        CompanyProfile_View::init();
    }
}