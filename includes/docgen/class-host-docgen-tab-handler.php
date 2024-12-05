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
    public function __construct($adapter) {
        // Use passed adapter instance
        $this->adapter = $adapter;
        
        // Get settings manager if DocGen Implementation active
        if (class_exists('DocGen_Implementation_Settings_Manager')) {
            $this->settings = DocGen_Implementation_Settings_Manager::get_instance();
            error_log('DocGen Tab Handler constructed');
            $this->init_hooks();
        }
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        error_log('DocGen Tab Handler: Initializing hooks');
        
        // Add tabs to settings page
        add_filter('host_settings_tabs', array($this, 'add_docgen_tabs'));
        
        // Add tab content handlers sesuai dengan action di admin-settings-page.php
        add_action('host_render_settings_tab_docgen_dashboard', array($this, 'render_dashboard_tab'));
        add_action('host_render_settings_tab_docgen_directory', array($this, 'render_directory_tab')); 
        add_action('host_render_settings_tab_docgen_templates', array($this, 'render_templates_tab'));

        // Add filter for dashboard cards
        add_filter('docgen_implementation_dashboard_cards', array($this, 'modify_dashboard_cards'));

        // Tambahkan filter untuk modifikasi directory paths
        add_filter('docgen_implementation_directory_settings', array($this, 'modify_directory_paths'));

        error_log('DocGen Tab Handler: Hooks initialized with settings tab handlers');

    }

    /**
     * Add DocGen tabs to settings
     * @param array $tabs Existing tabs
     * @return array Modified tabs
     */
    public function add_docgen_tabs($tabs) {
        error_log('DocGen Tab Handler: Adding tabs');
        
        $new_tabs = array_merge($tabs, array(
            'docgen_dashboard' => __('DocGen Dashboard', 'host-docgen'),
            'docgen_directory' => __('DocGen Directory', 'host-docgen'),
            'docgen_templates' => __('DocGen Templates', 'host-docgen')
        ));
        
        return $new_tabs;
    }

    /**
     * Render dashboard tab content
     */
    

    public function render_dashboard_tab() {
        //$modules = $this->get_modules();
        //$system_info = $this->get_system_info();
        
        if (class_exists('DocGen_Implementation_Dashboard_Page')) {
            $dashboard_page = new DocGen_Implementation_Dashboard_Page();
            $dashboard_page->render();
        } else {
            $this->render_error(__('DocGen Dashboard not available', 'host-docgen'));
        }
        
        do_action('docgen_implementation_after_dashboard_content');
    }

    /**
     * Render directory settings tab content
     *
    public function render_directory_tab() {
        if (!$this->adapter) {
            $this->render_error(__('DocGen adapter not available', 'host-docgen'));
            return;
        }

        // Load required classes
        $docgen_dir = $this->adapter->get_docgen_implementation_dir();
        require_once $docgen_dir . 'admin/class-admin-page.php';
        require_once $docgen_dir . 'admin/class-settings-page.php';
        
        $settings_page = new DocGen_Implementation_Settings_Page();
        
        // Dapatkan settings dan modifikasi path
        $settings = $this->settings->get_core_settings();
        $plugin_slug = $this->adapter->get_current_plugin_slug();
        
        $settings['temp_dir'] = trailingslashit($settings['temp_dir']) . $plugin_slug;
        $settings['template_dir'] = trailingslashit($settings['template_dir']) . $plugin_slug;
        
        $settings_page->render_directory_settings_public($settings);
    }
    */
    

    public function render_directory_tab() {
        if (!$this->adapter) {
            $this->render_error(__('DocGen adapter not available', 'host-docgen'));
            return;
        }

        // Load required classes
        $docgen_dir = $this->adapter->get_docgen_implementation_dir();
        require_once $docgen_dir . 'admin/class-admin-page.php';
        require_once $docgen_dir . 'admin/class-settings-page.php';
        
        $settings_page = new DocGen_Implementation_Settings_Page();
        
        // Dapatkan settings dan modifikasi path
        $settings = $this->settings->get_core_settings();
        
        // Pastikan adapter diteruskan saat render
        $settings_page->render_directory_settings_public([
            'settings' => $settings,
            'adapter' => $this->adapter  // Pastikan ini diteruskan
        ]);
    }


    /**
     * Helper untuk load DocGen view dengan data
     */
    private function load_docgen_view($view, $data = array()) {
        $view_path = $this->adapter->get_docgen_implementation_dir() . 'admin/views/' . $view;
        
        if (!file_exists($view_path)) {
            $this->render_error(sprintf(__('View file not found: %s', 'host-docgen'), $view));
            return;
        }

        // Extract data ke variables tapi tidak include $this
        foreach ($data as $key => $value) {
            if ($key !== 'this') {
                $$key = $value;
            }
        }
        
        // Load view dengan $this dari settings_page
        if (isset($data['settings_page'])) {
            $data['settings_page']->render_directory_settings($settings);
        }
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
        $core_settings = $this->settings->get_core_settings();
        
        // Dapatkan plugin slug melalui method public
        $plugin_slug = $this->adapter->get_current_plugin_slug();
        
        // Tambahkan plugin slug sebagai subdirektori
        $temp_dir = isset($core_settings['temp_dir']) ? 
            trailingslashit($core_settings['temp_dir']) . $plugin_slug :
            '';

        return array(
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'docgen_version' => defined('DOCGEN_IMPLEMENTATION_VERSION') ? DOCGEN_IMPLEMENTATION_VERSION : 'N/A', 
            'temp_dir' => $temp_dir,
            'template_dir' => $core_settings['template_dir'] ?? '',
            'upload_dir' => $upload_dir['basedir']
        );
    }

    public function get_plugin_info_public() {
        return $this->get_plugin_info();
    }

    /**
     * Modifikasi path direktori untuk include plugin subdirektori
     */
    public function modify_dashboard_cards($cards) {
        if (isset($cards['system_info']['data'])) {
            $system_info = $cards['system_info']['data'];
            $cards['system_info']['data'] = array(
                'php_version' => $system_info['php_version'],
                'wp_version' => $system_info['wp_version'], 
                'docgen_version' => $system_info['docgen_version'],
                'implementation_version' => DOCGEN_IMPLEMENTATION_VERSION,
                'temp_dir' => $this->adapter->get_docgen_temp_path(),
                'template_dir' => $this->adapter->get_docgen_template_path()
            );
        }
        return $cards;
    }

    public function modify_directory_paths($settings) {
        if (!$this->adapter) {
            return $settings;
        }
        
        if (isset($settings['temp_dir'])) {
            $settings['temp_dir'] = $this->adapter->get_docgen_temp_path();
        }
        if (isset($settings['template_dir'])) {
            $settings['template_dir'] = $this->adapter->get_docgen_template_path();
        }
        
        return $settings;
    }

}
