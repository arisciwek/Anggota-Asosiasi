<?php
/**
 * Host DocGen Company Profile Form Provider
 *
 * @author      arisciwek
 * @package     Host_DocGen
 * @subpackage  Modules/Compro/Providers
 * @version     1.0.0
 * 
 * Description:
 * Form data provider untuk Company Profile module.
 * Menangani konversi data dari form input menjadi format DocGen.
 * 
 * Filename Convention:
 * - Original  : class-host-docgen-compro-form-provider.php
 * - To Change : class-[plugin-name]-docgen-[module-name]-form-provider.php
 * 
 * Class Name Convention:
 * - Original  : Host_DocGen_Compro_Form_Provider
 * - To Change : [Plugin_Name]_DocGen_[Module_Name]_Form_Provider
 * 
 * Path: modules/compro/providers/class-host-docgen-compro-form-provider.php
 * Timestamp: 2024-11-29 10:30:00
 * 
 * Required Methods:
 * - get_template_path()  : Path ke template DOCX
 * - get_output_filename(): Nama file output
 * - get_data()          : Data untuk template
 * 
 * Dependencies:
 * - DocGen_Provider interface
 * - WP DocGen Plugin
 * 
 * @author     arisciwek
 * @copyright  2024 Host Organization
 * @license    GPL-2.0+
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class Host_DocGen_Compro_Form_Provider implements DocGen_Provider {
    /**
     * Form data
     * @var array
     */
    private $data;

    /**
     * Constructor
     * @param string $form_data Serialized form data
     */
    public function __construct($form_data) {
        $this->data = $this->parse_form_data($form_data);
    }

    /**
     * Parse and sanitize form data
     * @param string $form_data Raw form data
     * @return array Sanitized data
     */
    private function parse_form_data($form_data) {
        $parsed = [];
        parse_str($form_data, $parsed);

        return [
            'company_name' => sanitize_text_field($parsed['company_name'] ?? ''),
            'address'      => sanitize_textarea_field($parsed['address'] ?? ''),
            'phone'        => sanitize_text_field($parsed['phone'] ?? ''),
            'email'        => sanitize_email($parsed['email'] ?? ''),
            'website'      => esc_url_raw($parsed['website'] ?? ''),
            'description'  => wp_kses_post($parsed['description'] ?? '')
        ];
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
        $company = sanitize_title($this->data['company_name']);
        if (empty($company)) {
            $company = 'company';
        }

        return sprintf(
            '%s-profile-%s',
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
            'company_name' => $this->data['company_name'],
            'address'      => $this->data['address'],
            'phone'        => $this->data['phone'],
            'email'        => $this->data['email'],
            'website'      => $this->data['website'],
            'description'  => $this->data['description'],
            
            // Meta fields
            'generated_date' => date_i18n(get_option('date_format')),
            'generated_by'   => wp_get_current_user()->display_name
        ];
    }
}