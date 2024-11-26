<?php
/**
 * JSON Provider untuk Company Profile
 *
 * @package     Asosiasi
 * @subpackage  DocGen
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: includes/docgen/providers/class-company-profile-json-provider.php
 * 
 * Description: Provider untuk memproses data dari JSON file
 *              dan menyiapkannya untuk template docgen.
 * 
 * Dependencies:
 * - class-asosiasi-docgen-provider.php
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class CompanyProfile_JSON_Provider extends Asosiasi_DocGen_Provider {
    /**
     * Constructor
     */
    public function __construct() {
        $this->load_data();
    }

    /**
     * Load data dari JSON file
     */
    private function load_data() {
        $json_file = dirname(dirname(dirname(__FILE__))) . '/data/company-profile-data.json';
        
        if (!file_exists($json_file)) {
            throw new Exception('JSON data file not found');
        }

        $json_content = file_get_contents($json_file);
        $this->data = json_decode($json_content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Error parsing JSON: ' . json_last_error_msg());
        }

        // Add identifier
        $this->data['identifier'] = 'company-profile';
    }

    /**
     * Get source identifier
     * @return string
     */
    protected function get_source_identifier() {
        return 'json';
    }

    /**
     * Get data untuk template
     * @return array
     */
    public function get_data() {
        if (empty($this->data)) {
            return [];
        }

        return array(
            // Info dasar perusahaan
            'company_name' => $this->data['company_name'] ?? '',
            'legal_name' => $this->data['legal_name'] ?? '',
            'tagline' => $this->data['tagline'] ?? '',
            
            // Alamat lengkap
            'address' => sprintf(
                "%s\n%s, %s %s\n%s",
                $this->data['address']['street'] ?? '',
                $this->data['address']['city'] ?? '',
                $this->data['address']['province'] ?? '',
                $this->data['address']['postal_code'] ?? '',
                $this->data['address']['country'] ?? ''
            ),
            
            // Kontak
            'phone' => $this->data['contact']['phone'] ?? '',
            'email' => $this->data['contact']['email'] ?? '',
            'website' => $this->data['contact']['website'] ?? '',

            // Registrasi
            'company_id' => $this->data['registration']['company_id'] ?? '',
            'tax_id' => $this->data['registration']['tax_id'] ?? '',
            'established_date' => isset($this->data['registration']['established_date']) ? 
                '${date:' . $this->data['registration']['established_date'] . ':j F Y}' : '',

            // Profile perusahaan
            'vision' => $this->data['profile']['vision'] ?? '',
            'mission' => isset($this->data['profile']['mission']) ? 
                $this->format_bullet_points($this->data['profile']['mission']) : '',
            'values' => isset($this->data['profile']['values']) ? 
                $this->format_bullet_points($this->data['profile']['values']) : '',

            // Informasi bisnis
            'main_services' => isset($this->data['business']['main_services']) ? 
                $this->format_bullet_points($this->data['business']['main_services']) : '',
            'industries' => isset($this->data['business']['industries']) ?
                $this->format_bullet_points($this->data['business']['industries']) : '',
            'employee_count' => $this->data['business']['employee_count'] ?? '',
            'office_locations' => isset($this->data['business']['office_locations']) ?
                $this->format_bullet_points($this->data['business']['office_locations']) : '',

            // Metadata
            'generated_date' => '${date:' . date('Y-m-d H:i:s') . ':j F Y H:i}',
            'generated_by' => '${user:display_name}',
            'generated_by_email' => '${user:user_email}',
            'source' => 'JSON Data'
        );
    }
}