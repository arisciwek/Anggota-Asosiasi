<?php
/**
 * Host DocGen Company Profile JSON Provider
 *
 * @package     Host_DocGen
 * @subpackage  Modules/Compro/Providers
 * @version     1.0.0
 * 
 * Description:
 * JSON data provider untuk Company Profile module.
 * Menangani konversi data dari JSON file menjadi format DocGen.
 * 
 * Filename Convention:
 * - Original  : class-host-docgen-compro-json-provider.php
 * - To Change : class-[plugin-name]-docgen-[module-name]-json-provider.php
 * 
 * Class Name Convention:
 * - Original  : Host_DocGen_Compro_JSON_Provider
 * - To Change : [Plugin_Name]_DocGen_[Module_Name]_JSON_Provider
 * 
 * Path: modules/compro/providers/class-host-docgen-compro-json-provider.php
 * Timestamp: 2024-11-29 10:35:00
 * 
 * Required JSON Structure:
 * {
 *   "company_name": "string",
 *   "address": {
 *     "street": "string",
 *     "city": "string",
 *     "postal_code": "string"
 *   },
 *   "contact": {
 *     "phone": "string",
 *     "email": "string",
 *     "website": "string"
 *   },
 *   "profile": {
 *     "vision": "string",
 *     "mission": ["string"],
 *     "values": ["string"]
 *   }
 * }
 * 
 * Dependencies:
 * - DocGen_Provider interface
 * - WP DocGen Plugin
 * 
 * @author     arisciwek 
 * @author     Host Developer
 * @copyright  2024 Host Organization
 * @license    GPL-2.0+
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class Host_DocGen_Compro_JSON_Provider implements DocGen_Provider {
    /**
     * JSON data
     * @var array
     */
    private $data;

    /**
     * Constructor
     */
    public function __construct() {
        $this->load_data();
    }

    /**
     * Load data from JSON file
     * @throws Exception if file not found or invalid JSON
     */
    private function load_data() {
        $json_file = dirname(dirname(__FILE__)) . '/data/compro-data.json';
        
        if (!file_exists($json_file)) {
            throw new Exception(__('JSON data file not found', 'host-docgen'));
        }

        $json_content = file_get_contents($json_file);
        $this->data = json_decode($json_content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception(
                sprintf(
                    __('Invalid JSON data: %s', 'host-docgen'),
                    json_last_error_msg()
                )
            );
        }
    }

    /**
     * Format bullet points for arrays
     * @param array $items Array of text items
     * @return string Formatted bullet points
     */
    private function format_bullet_points($items) {
        if (!is_array($items)) {
            return '';
        }

        return implode("\n", array_map(function($item) {
            return "• " . trim($item);
        }, $items));
    }

    /**
     * Get template path
     * @return string Template file path
     */
    public function get_template_path() {
        $settings = get_option('host_docgen_settings', []);
        $template_dir = $settings['template_dir'] ?? '';
        
        if (empty($template_dir)) {
            throw new Exception(__('Template directory not configured', 'host-docgen'));
        }

        $template_path = trailingslashit($template_dir) . 'compro-template.docx';
        
        if (!file_exists($template_path)) {
            throw new Exception(__('Template file not found', 'host-docgen'));
        }

        return $template_path;
    }

    /**
     * Get output filename
     * @return string Output filename
     */
    public function get_output_filename() {
        $company = sanitize_title($this->data['company_name'] ?? '');
        if (empty($company)) {
            $company = 'company';
        }

        return sprintf(
            '%s-profile-json-%s',
            $company,
            date('Ymd-His')
        );
    }

    /**
     * Get data for template
     * @return array Template data
     */
    public function get_data() {
        return [
            // Company Info
            'company_name' => $this->data['company_name'] ?? '',
            
            // Address
            'full_address' => sprintf(
                "%s\n%s %s",
                $this->data['address']['street'] ?? '',
                $this->data['address']['city'] ?? '',
                $this->data['address']['postal_code'] ?? ''
            ),

            // Contact
            'phone' => $this->data['contact']['phone'] ?? '',
            'email' => $this->data['contact']['email'] ?? '',
            'website' => $this->data['contact']['website'] ?? '',

            // Profile
            'vision' => $this->data['profile']['vision'] ?? '',
            'mission' => $this->format_bullet_points($this->data['profile']['mission'] ?? []),
            'values' => $this->format_bullet_points($this->data['profile']['values'] ?? []),

            // Meta
            'generated_date' => date_i18n(get_option('date_format')),
            'generated_by' => wp_get_current_user()->display_name,
            'source' => 'JSON Data'
        ];
    }
}