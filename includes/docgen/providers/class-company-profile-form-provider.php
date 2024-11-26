<?php
/**
 * Form Provider untuk Company Profile
 *
 * @package     Asosiasi
 * @subpackage  DocGen
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: includes/docgen/providers/class-company-profile-form-provider.php
 * 
 * Description: Provider untuk memproses data dari form submission
 *              dan menyiapkannya untuk template docgen.
 * 
 * Dependencies:
 * - class-asosiasi-docgen-provider.php
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class CompanyProfile_Form_Provider extends Asosiasi_DocGen_Provider {
    /**
     * Constructor
     * @param array $form_data Data dari form submission
     */
    public function __construct($form_data) {
        $this->data = $this->sanitize_form_data($form_data);
    }

    /**
     * Sanitize form data
     * @param array $form_data Raw form data
     * @return array Sanitized data
     */
    private function sanitize_form_data($form_data) {
        // Parse form data jika string
        if (is_string($form_data)) {
            parse_str($form_data, $parsed_data);
            $form_data = $parsed_data;
        }

        return array(
            'identifier' => 'company-profile',
            'company_name' => sanitize_text_field($form_data['company_name'] ?? ''),
            'legal_name' => sanitize_text_field($form_data['legal_name'] ?? ''),
            'tagline' => sanitize_text_field($form_data['tagline'] ?? ''),
            'address' => array(
                'street' => sanitize_text_field($form_data['address']['street'] ?? ''),
                'city' => sanitize_text_field($form_data['address']['city'] ?? ''),
                'province' => sanitize_text_field($form_data['address']['province'] ?? ''),
                'postal_code' => sanitize_text_field($form_data['address']['postal_code'] ?? ''),
                'country' => sanitize_text_field($form_data['address']['country'] ?? '')
            ),
            'contact' => array(
                'phone' => sanitize_text_field($form_data['contact']['phone'] ?? ''),
                'email' => sanitize_email($form_data['contact']['email'] ?? ''),
                'website' => esc_url_raw($form_data['contact']['website'] ?? '')
            ),
            'business' => array(
                'main_services' => array_map('sanitize_text_field', 
                    $this->parse_textarea_lines($form_data['business']['main_services'] ?? '')),
                'industries' => array_map('sanitize_text_field',
                    $this->parse_textarea_lines($form_data['business']['industries'] ?? '')),
                'employee_count' => sanitize_text_field($form_data['business']['employee_count'] ?? ''),
                'office_locations' => array_map('sanitize_text_field',
                    $this->parse_textarea_lines($form_data['business']['office_locations'] ?? ''))
            )
        );
    }

    /**
     * Parse textarea lines ke array
     * @param string $text Textarea content
     * @return array Lines sebagai array
     */
    private function parse_textarea_lines($text) {
        if (empty($text)) {
            return array();
        }
        return array_filter(array_map('trim', explode("\n", $text)));
    }

    /**
     * Get source identifier
     * @return string
     */
    protected function get_source_identifier() {
        return 'form';
    }

    /**
     * Get data untuk template
     * @return array
     */
    public function get_data() {
        return array(
            // Info dasar perusahaan
            'company_name' => $this->data['company_name'],
            'legal_name' => $this->data['legal_name'],
            'tagline' => $this->data['tagline'],
            
            // Alamat lengkap
            'address' => sprintf(
                "%s\n%s, %s %s\n%s",
                $this->data['address']['street'],
                $this->data['address']['city'],
                $this->data['address']['province'],
                $this->data['address']['postal_code'],
                $this->data['address']['country']
            ),
            
            // Kontak
            'phone' => $this->data['contact']['phone'],
            'email' => $this->data['contact']['email'],
            'website' => $this->data['contact']['website'],

            // Informasi bisnis
            'main_services' => $this->format_bullet_points($this->data['business']['main_services']),
            'industries' => $this->format_bullet_points($this->data['business']['industries']),
            'employee_count' => $this->data['business']['employee_count'],
            'office_locations' => $this->format_bullet_points($this->data['business']['office_locations']),

            // Metadata
            'generated_date' => '${date:' . date('Y-m-d H:i:s') . ':j F Y H:i}',
            'generated_by' => '${user:display_name}',
            'generated_by_email' => '${user:user_email}',
            'source' => 'Form Data'
        );
    }
}