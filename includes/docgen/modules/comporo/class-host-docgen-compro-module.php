<?php
/**
 * Host DocGen Company Profile Module
 *
 * @package     Host_DocGen
 * @subpackage  Modules/Compro
 * @version     1.0.0
 * 
 * Description:
 * Blueprint module untuk pembuatan dokumen Company Profile.
 * Class ini menjadi contoh implementasi DocGen_Module untuk plugin lain.
 * 
 * Filename Convention:
 * - Original  : class-host-docgen-compro-module.php
 * - To Change : class-[plugin-name]-docgen-[module-name]-module.php
 * 
 * Class Name Convention:
 * - Original  : Host_DocGen_Compro_Module
 * - To Change : [Plugin_Name]_DocGen_[Module_Name]_Module
 * 
 * Directory Structure:
 * /modules/compro/
 * ├── class-host-docgen-compro-module.php
 * ├── providers/
 * │   ├── class-host-docgen-compro-form-provider.php
 * │   └── class-host-docgen-compro-json-provider.php
 * ├── assets/
 * │   ├── css/
 * │   │   └── host-docgen-compro-style.css
 * │   └── js/
 * │       └── host-docgen-compro-script.js
 * └── views/
 *     └── host-docgen-compro-page.php
 * 
 * Required Actions:
 * 1. Rename file dan class sesuai konvensi
 * 2. Sesuaikan $module_info dengan modul baru
 * 3. Implement providers sesuai kebutuhan
 * 4. Buat views dan assets yang diperlukan
 * 
 * Dependencies:
 * - DocGen Implementation Plugin
 * - DocGen_Module base class
 * - WP DocGen Plugin
 * 
 * @author     Host Developer
 * @copyright  2024 Host Organization
 * @license    GPL-2.0+
 * 
 * @link       https://example.com/host-docgen/modules/compro
 * @since      1.0.0
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class Host_DocGen_Compro_Module extends DocGen_Module {
    /**
     * Module info
     * @var array
     */
    protected $module_info = [
        'slug' => 'compro',
        'name' => 'Company Profile',
        'description' => 'Generate company profile documents',
        'version' => '1.0.0'
    ];

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct($this->module_info, [
            'slug' => 'host-docgen'
        ]);

        // Register AJAX handlers
        add_action('wp_ajax_generate_compro', [$this, 'handle_generate']);
    }

    /**
     * Enqueue module assets
     */
    public function enqueue_assets($hook) {
        if (strpos($hook, $this->module_info['slug']) === false) {
            return;
        }

        // Enqueue module specific CSS
        wp_enqueue_style(
            'host-docgen-compro',
            plugins_url('assets/css/host-docgen-compro-style.css', __FILE__),
            [],
            $this->module_info['version']
        );

        // Enqueue module specific JS
        wp_enqueue_script(
            'host-docgen-compro',
            plugins_url('assets/js/host-docgen-compro-script.js', __FILE__),
            ['jquery'],
            $this->module_info['version'],
            true
        );

        // Localize script
        wp_localize_script('host-docgen-compro', 'hostDocGenCompro', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('host-docgen-compro'),
            'strings' => [
                'generateSuccess' => __('Document generated successfully!', 'host-docgen'),
                'generateError' => __('Failed to generate document.', 'host-docgen')
            ]
        ]);
    }

    /**
     * Render module page
     */
    public function render_page() {
        require_once dirname(__FILE__) . '/views/host-docgen-compro-page.php';
    }

    /**
     * Handle document generation
     */
    public function handle_generate() {
        check_ajax_referer('host-docgen-compro');

        try {
            // Get provider based on source
            $source = sanitize_text_field($_POST['source'] ?? 'form');
            $provider = $this->get_provider($source);

            if (!$provider) {
                throw new Exception(__('Invalid data source', 'host-docgen'));
            }

            // Generate document
            $result = wp_docgen()->generate($provider);
            
            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }

            // Get URL for download
            $upload_dir = wp_upload_dir();
            $file_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $result);
            
            wp_send_json_success([
                'url' => $file_url,
                'file' => basename($result)
            ]);

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Get appropriate provider
     * @param string $source Data source (form|json)
     * @return DocGen_Provider|null Provider instance
     */
    private function get_provider($source) {
        require_once dirname(__FILE__) . '/providers/class-host-docgen-compro-form-provider.php';
        require_once dirname(__FILE__) . '/providers/class-host-docgen-compro-json-provider.php';

        switch ($source) {
            case 'form':
                return new Host_DocGen_Compro_Form_Provider($_POST['form_data'] ?? '');
            
            case 'json':
                return new Host_DocGen_Compro_JSON_Provider();
        }

        return null;
    }
}
