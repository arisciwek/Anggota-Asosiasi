<?php
/**
 * Host DocGen Tab Handler
 *
 * @package     Host_DocGen
 * @subpackage  Core
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: includes/docgen/class-host-docgen-tab-handler.php
 * 
 * Timestampt: 2024:12:02
 * 
 * Description:
 * Handler untuk menambahkan tab DocGen ke settings page.
 * Mengintegrasikan dashboard, directory settings dan template 
 * settings sebagai tab dalam settings host plugin.
 * 
 * Filename Convention:
 * - Original  : class-host-docgen-tab-handler.php
 * - To Change : class-[plugin-name]-docgen-tab-handler.php
 * 
 * Class Name Convention:
 * - Original  : Host_DocGen_Tab_Handler
 * - To Change : [Plugin_Name]_DocGen_Tab_Handler
 * 
 * Dependencies:
 * - DocGen Implementation Plugin
 * - class-docgen-checker.php (untuk dependency check)
 * - class-host-docgen-adapter.php (untuk path ke DocGen Implementation)
 * - class-host-docgen-hooks.php (untuk DocGen hooks)
 * 
 * @author     arisciwek
 * @copyright  2024 Host Organization
 * @license    GPL-2.0+
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class Host_DocGen_Tab_Handler {
    /**
     * Settings manager instance
     * @var DocGen_Implementation_Settings_Manager
     */
    private $settings;

    /**
     * DocGen adapter instance
     * @var Host_DocGen_Adapter
     */
    private $adapter;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Get adapter instance untuk akses DocGen path
        $this->adapter = new Host_DocGen_Adapter();
        
        // Get settings manager jika DocGen Implementation aktif
        if (class_exists('DocGen_Implementation_Settings_Manager')) {
            $this->settings = DocGen_Implementation_Settings_Manager::get_instance();
            $this->init_hooks();
        }
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Add tabs to settings page
        add_filter('host_settings_tabs', array($this, 'add_docgen_tabs'));
        
        // Add tab content handlers
        add_action('host_render_tab_docgen_dashboard', array($this, 'render_dashboard_tab'));
        add_action('host_render_tab_docgen_directory', array($this, 'render_directory_tab')); 
        add_action('host_render_tab_docgen_templates', array($this, 'render_templates_tab'));
    }

    /**
     * Add DocGen tabs to settings
     * @param array $tabs Existing tabs
     * @return array Modified tabs
     */
    public function add_docgen_tabs($tabs) {
        $docgen_tabs = array(
            'docgen_dashboard' => __('DocGen Dashboard', 'host-docgen'),
            'docgen_directory' => __('DocGen Directory', 'host-docgen'),
            'docgen_templates' => __('DocGen Templates', 'host-docgen')
        );
        return array_merge($tabs, $docgen_tabs);
    }

    /**
     * Render dashboard tab content
     */
    public function render_dashboard_tab() {
        $modules = $this->get_modules();
        $system_info = $this->get_system_info();

        do_action('docgen_implementation_before_dashboard_content');
        
        // Get dashboard view path from adapter
        $view_path = $this->adapter->get_docgen_implementation_dir() . 'admin/views/dashboard-page.php';
        if (file_exists($view_path)) {
            include $view_path;
        } else {
            $this->render_error(__('Dashboard view not found', 'host-docgen'));
        }
        
        do_action('docgen_implementation_after_dashboard_content'); 
    }

    /**
     * Render directory settings tab content
     */
    public function render_directory_tab() {
        $settings = $this->get_directory_settings();
        
        do_action('docgen_implementation_before_directory_settings');
        
        $view_path = $this->adapter->get_docgen_implementation_dir() . 'admin/views/directory-settings.php';
        if (file_exists($view_path)) {
            include $view_path;
        } else {
            $this->render_error(__('Directory settings view not found', 'host-docgen'));
        }
        
        do_action('docgen_implementation_after_directory_settings');
    }

    /**
     * Render template settings tab content
     */
    public function render_templates_tab() {
        $settings = $this->get_template_settings();
        
        do_action('docgen_implementation_before_template_settings');
        
        $view_path = $this->adapter->get_docgen_implementation_dir() . 'admin/views/template-settings.php';
        if (file_exists($view_path)) {
            include $view_path;
        } else {
            $this->render_error(__('Template settings view not found', 'host-docgen'));
        }
        
        do_action('docgen_implementation_after_template_settings');
    }

    /**
     * Render error message
     * @param string $message Error message to display
     */
    private function render_error($message) {
        printf(
            '<div class="notice notice-error"><p>%s</p></div>',
            esc_html($message)
        );
    }

    /**
     * Get directory settings
     * @return array Directory settings
     */
    private function get_directory_settings() {
        $settings = array();
        if ($this->settings) {
            $settings = $this->settings->get_core_settings();
        }
        return apply_filters('host_docgen_directory_settings', $settings);
    }

    /**
     * Get template settings
     * @return array Template settings
     */
    private function get_template_settings() {
        $settings = array();
        if ($this->settings) {
            $settings = $this->settings->get_core_settings();
        }
        return apply_filters('host_docgen_template_settings', $settings);
    }

    /**
     * Get module information
     * @return array Module info
     */
    private function get_modules() {
        return apply_filters('docgen_implementation_modules', array());
    }

    /**
     * Get system information
     * @return array System info
     */
    private function get_system_info() {
        $upload_dir = wp_upload_dir();
        return array(
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'docgen_version' => defined('DOCGEN_IMPLEMENTATION_VERSION') ? DOCGEN_IMPLEMENTATION_VERSION : 'N/A',
            'temp_dir' => $this->settings->get_core_settings()['temp_dir'] ?? '',
            'template_dir' => $this->settings->get_core_settings()['template_dir'] ?? '',
            'upload_dir' => $upload_dir['basedir']
        );
    }
}
