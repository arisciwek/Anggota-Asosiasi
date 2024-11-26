<?php
/**
 * Company Profile Module Class
 *
 * @package     Asosiasi
 * @subpackage  DocGen
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: includes/docgen/modules/class-company-profile-module.php
 * 
 * Description: Modul untuk generate company profile document.
 *              Mengintegrasikan form dan JSON provider dengan
 *              DocGen Implementation.
 * 
 * Dependencies:
 * - class-asosiasi-docgen-provider.php
 * - class-company-profile-form-provider.php
 * - class-company-profile-json-provider.php
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class CompanyProfile_Module {
    /**
     * Module instance
     * @var self|null
     */
    private static $instance = null;

    /**
     * Constructor
     */
    private function __construct() {
        // Register module
        add_filter('asosiasi_modules', array($this, 'register_module'));
        
        // Add menu item - Menggunakan admin_menu langsung
        add_action('admin_menu', array($this, 'add_menu_item'));
        
        // Register AJAX handlers
        add_action('wp_ajax_generate_company_profile', array($this, 'handle_generate_profile'));
        
        // Load assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    /**
     * Add menu item with adaptive registration
     */
    public function add_menu_item() {
        global $menu;
        
        // Cek apakah ada menu plugin/tema lain yang ingin kita gabungkan
        // Misal: menu untuk document generator, content tools, dll
        $parent_menu = $this->detect_compatible_parent_menu();

        if($parent_menu) {
            // Tambahkan sebagai submenu jika ada parent yang cocok
            add_submenu_page(
                $parent_menu,
                __('Company Profile Generator', 'company-profile'),
                __('Company Profile', 'company-profile'),
                'manage_options',
                'company-profile-generator',
                array($this, 'render_page')
            );
        } else {
            // Buat sebagai menu independen
            add_menu_page(
                __('Company Profile Generator', 'company-profile'),
                __('Company Profile', 'company-profile'),
                'manage_options',
                'company-profile-generator',
                array($this, 'render_page'),
                'dashicons-media-document',
                30
            );
        }
    }

    /**
     * Detect compatible parent menu
     * @return string|null Menu slug if found, null otherwise
     */
    private function detect_compatible_parent_menu() {
        // List of potential parent menus
        $compatible_menus = array(
            'document-generator',
            'content-tools',
            'wp-docgen'
            // Bisa ditambahkan menu lain yang relevan
        );

        global $menu;
        foreach($menu as $menu_item) {
            if(isset($menu_item[2]) && in_array($menu_item[2], $compatible_menus)) {
                return $menu_item[2];
            }
        }

        return null;
    }

    /**
     * Get module instance
     * @return self
     */
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Handle profile generation with provider selection
     */
    public function handle_generate_profile() {
        check_ajax_referer('asosiasi_docgen');

        try {
            // Get source type from request
            $source = sanitize_text_field($_POST['source'] ?? 'json');
            
            // Initialize appropriate provider based on source
            switch ($source) {
                case 'form':
                    $form_data = $_POST['form_data'] ?? '';
                    $provider = new CompanyProfile_Form_Provider($form_data);
                    break;
                    
                case 'json':
                default:
                    $provider = new CompanyProfile_JSON_Provider();
                    break;
            }            

            // Get WP DocGen instance
            if (!class_exists('WP_DocGen')) {
                throw new Exception('WP DocGen plugin is required');
            }
            
            // Generate document
            $result = wp_docgen()->generate($provider);
            
            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }

            // Get URL for download
            $upload_dir = wp_upload_dir();
            $file_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $result);
            
            wp_send_json_success(array(
                'url' => $file_url,
                'file' => basename($result)
            ));

        } catch (Exception $e) {
            error_log('Asosiasi DocGen Error: ' . $e->getMessage());
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Initialize module
     */
    public static function init() {
        return self::get_instance();
    }
}