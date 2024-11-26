<?php
/**
* Main Class DocGen WPClass
*
* @package     DocGen_WPClass
* @version     1.0.0
* @author      arisciwek
* 
* Path: class-dwpc.php
* 
* Description: Main class untuk DocGen WPClass library.
*              Handles directory configuration, module loading,
*              dan integrasi dengan WP DocGen processor.
* 
* Changelog:
* 1.0.0 - 2024-11-24
* - Initial implementation
* - Directory handling & validation
* - Module system integration  
* - Setup configuration
* - Security measures
* 
* Dependencies:
* - WP DocGen plugin (document processor)
* - class-dwpc-directory-handler.php
* - class-dwpc-module-loader.php
* 
* Usage:
* Di plugin activator:
* docgen_wpclass()->setup([
*   'temp_dir' => 'custom-temp',
*   'template_dir' => 'custom-templates' 
* ]);
* 
* Di plugin modules:
* $dir_handler = docgen_wpclass()->get_directory_handler();
* $module = docgen_wpclass()->get_module('module-slug');
* 
* Security:
* - Path traversal prevention
* - Directory permission handling
* - WP DocGen dependency check
* 
* Notes:
* - Singleton pattern implementation
* - Supports module extensibility
* - Directory structure maintenance
*/

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class DocGen_WPClass {
    private static $instance = null;
    private $settings = [];
    private $dir_handler;
    private $module_loader;

    private function __construct() {
        $this->settings = get_option('docgen_wpclass_settings', []);
        require_once ASOSIASI_DIR . 'admin/class-dwpc-directory-handler.php';
        require_once ASOSIASI_DIR . 'includes/class-dwpc-module-loader.php';
                    
        $this->dir_handler = new DocGen_WPClass_Directory_Handler();
        $this->module_loader = new DocGen_WPClass_Module_Loader();
        
        $this->module_loader->discover_modules();
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setup($config = []) {
        if (!class_exists('WP_DocGen')) {
            throw new Exception('WP DocGen plugin is required');
        }

        $default_config = [
            'temp_dir' => 'docgen-temp',
            'template_dir' => 'docgen-templates',
            'output_format' => 'docx',
            'debug_mode' => false
        ];

        $config = wp_parse_args($config, $default_config);
        
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'];

        $dirs = [
            'temp_dir' => trailingslashit($base_dir) . $config['temp_dir'],
            'template_dir' => trailingslashit($base_dir) . $config['template_dir']
        ];

        foreach ($dirs as $dir) {
            $result = $this->dir_handler->create_directory($dir);
            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }
        }

        $this->settings = array_merge($config, ['dirs' => $dirs]);
        update_option('docgen_wpclass_settings', $this->settings);

        return true;
    }

    public function get_settings() {
        return $this->settings;
    }

    public function get_module($slug) {
        return $this->module_loader->get_module($slug);
    }

    public function register_module($module_file) {
        return $this->module_loader->register_module($module_file);
    }

    public function get_directory_handler() {
        return $this->dir_handler;
    }
}

function docgen_wpclass() {
    return DocGen_WPClass::get_instance();
}
