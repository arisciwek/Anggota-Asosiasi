<?php
/**
 *  
 * Host DocGen Adapter
 *
 * @package     Host_DocGen
 * @subpackage  Core
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Description:
 * Plugin specific adapter for integrating with DocGen Implementation.
 * Maps existing plugin structure to DocGen framework format.
 * 
 * Class ini adalah blueprint adapter class untuk integrasi plugin dengan DocGen Implementation.
 * Class ini bertugas sebagai jembatan antara plugin dan DocGen framework.
 * 
 * Filename Convention:
 * - Original  : class-host-docgen-adapter.php
 * - To Change : class-[plugin-name]-docgen-adapter.php
 * 
 * Class Name Convention:
 * - Original  : Host_DocGen_Adapter
 * - To Change : [Plugin_Name]_DocGen_Adapter
 * 
 * Usage:
 * 1. Rename file dan class sesuai nama plugin
 * 2. Sesuaikan get_plugin_info() dengan info plugin
 * 3. Sesuaikan map_settings() dengan struktur settings plugin
 * 4. Sesuaikan map_modules() jika plugin memiliki struktur module berbeda
 * 
 * Dependencies:
 * - DocGen Implementation Plugin
 * - DocGen_Plugin_Adapter class from DocGen Implementation
 * 
 * @author     arisciwek
 * @copyright  2024 Host Organization
 * @license    GPL-2.0+
 * 
 * @link       https://example.com/host-docgen
 * @since      1.0.0
 */
    
if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class Host_DocGen_Adapter extends DocGen_Adapter {

    private $hooks;
    private $tab_handler;
    private $plugin_file; // Tambahkan property

    public function __construct($plugin_file = null) {
        // Initialize hooks instance
        require_once dirname(__FILE__) . '/class-host-docgen-hooks.php';
        $this->hooks = Host_DocGen_Hooks::get_instance();
        $this->hooks->set_adapter($this); // Tambahkan ini

        // Initialize tab handler
        require_once dirname(__FILE__) . '/class-host-docgen-tab-handler.php';
        $this->tab_handler = new Host_DocGen_Tab_Handler($this);

        parent::__construct();
    }
    
    /**
     * Get DocGen Implementation directory path
     *
     * @since 1.0.0
     * @return string Full path to DocGen Implementation plugin directory
     */
    public function get_docgen_implementation_dir() {
        return $this->get_docgen_dir();
    }


// Mendapatkan slug plugin tertentu berdasarkan path file
function get_plugin_slug($file) {
    $path = dirname($file);
    $path = str_replace(WP_PLUGIN_DIR . '/', '', $path);

    // Pecah berdasarkan separator direktori '/'
    $path_parts = explode('/', $path);

    // Ambil bagian awal (direktori pertama)
    $plugin_dir = $path_parts[0]; 

    return $plugin_dir;
}
    /**
     * Get plugin info
     * @return array Plugin information
     */
    protected function get_plugin_info() {

        $plugin_slug = $this->get_plugin_slug(__FILE__);

        return [
            'slug' => $plugin_slug,
            //'slug' => 'host-docgen',
            'name' => 'Host DocGen',
            'version' => '1.0.0',
            'author' => 'Host Developer',
            'description' => 'Host Document Generation System',
            'settings' => [
                'temp_directory' => 'host-docgen/temp',
                'template_directory' => 'host-docgen/templates',
                'default_format' => 'docx'
            ]
        ];
    }

    /**
     * Map settings from existing format to DocGen format
     * @param array $settings Existing settings
     * @return array Mapped settings
     */
    protected function map_settings($settings) {
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'];

        return [
            'temp_dir' => trailingslashit($base_dir) . ($settings['temp_directory'] ?? 'host-docgen/temp'),
            'template_dir' => trailingslashit($base_dir) . ($settings['template_directory'] ?? 'host-docgen/templates'),
            'output_format' => $settings['default_format'] ?? 'docx',
            'organization' => [
                'name' => $settings['org_name'] ?? '',
                'address' => $settings['org_address'] ?? '',
                'phone' => $settings['org_phone'] ?? '',
                'email' => $settings['org_email'] ?? '',
                'website' => $settings['org_website'] ?? ''
            ]
        ];
    }

    /**
     * Map modules to DocGen format
     * @param array $modules Existing modules
     * @return array Mapped modules
     */
    protected function map_modules($modules) {
        if (empty($modules)) {
            return [];
        }

        return array_map(function($module) {
            return [
                'slug' => $module->get_slug(),
                'name' => $module->get_name(),
                'description' => $module->get_description(),
                'version' => $module->get_version(),
                'instance' => $module
            ];
        }, $modules);
    }

    /**
     * Get current plugin slug
     * @return string Plugin slug
     */
    public function get_current_plugin_slug() {
        $plugin_info = $this->get_plugin_info();
        return $plugin_info['slug'];
    }
    
    /*
    public function get_current_plugin_slug() {
        if ($this->plugin_file) {
            return dirname(plugin_basename($this->plugin_file)); 
        }
        // Fallback jika tidak ada plugin_file
        return dirname(plugin_basename(__FILE__));
    }
    */

    // Di class-host-docgen-adapter.php
    //public function get_current_plugin_slug() {
    //    return dirname(plugin_basename(__FILE__));  
    //}

    /*
    public function get_docgen_temp_path() {
        // Get DocGen settings untuk ambil nama folder temp
        $docgen_settings = get_option('docgen_implementation_settings', array());
        $temp_folder = basename($docgen_settings['temp_dir'] ?? 'docgen-temp');
        
        $upload_dir = wp_upload_dir();
        $plugin_slug = $this->get_current_plugin_slug(); // akan return 'asosiasi'
        
        return trailingslashit($upload_dir['basedir']) . $temp_folder . '/' . $plugin_slug;
    }
    */

    public function get_docgen_temp_path() {
        $docgen_settings = get_option('docgen_implementation_settings', array());
        $temp_folder = basename($docgen_settings['temp_dir'] ?? 'docgen-temp');

        $upload_dir = wp_upload_dir();

        $plugin_slug = $this->get_current_plugin_slug();

        // Saat ini path yang terbentuk masih termasuk /includes/docgen/
        // Perlu dibersihkan agar hanya sampai /plugin-slug/ saja
        $path = trailingslashit($upload_dir['basedir']) . $temp_folder . '/' . $plugin_slug;
        error_log('$path = ' . $path);
        return $path;
    }


    public function get_docgen_template_path() {
        // Get DocGen settings untuk ambil nama folder template
        $docgen_settings = get_option('docgen_implementation_settings', array());
        $template_folder = basename($docgen_settings['template_dir'] ?? 'docgen-templates');
        
        $upload_dir = wp_upload_dir();
        $plugin_slug = $this->get_current_plugin_slug();
        
        return trailingslashit($upload_dir['basedir']) . $template_folder . '/' . $plugin_slug;
    }
}