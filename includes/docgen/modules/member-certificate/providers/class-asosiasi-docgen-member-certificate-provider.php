<?php
/**
 * Asosiasi DocGen Member Certificate Provider
 *
 * @package     Asosiasi
 * @subpackage  Modules/Certificate/Providers
 * @version     1.0.0
 * @author      arisciwek
 * @copyright  2024 Asosiasi Organization
 * @license    GPL-2.0+
 * 
 * Description:
 * Provider untuk generate sertifikat anggota.
 * Menangani konversi data dari database menjadi format DocGen.
 * 
 * Filename Convention:
 * - Current  : class-asosiasi-docgen-certificate-provider.php
 * - Blueprint : class-[plugin-name]-docgen-[module-name]-provider.php
 * 
 * Class Name Convention:
 * - Current  : Asosiasi_DocGen_Member_Certificate_Provider
 * - Blueprint : [Plugin_Name]_DocGen_[Module_Name]_Provider
 * 
 * 
 * Path: modules/member-certificate/providers/class-asosiasi-docgen-member-certificate-provider.php
 * Timestamp: 2024-12-20 11:00:00
 * 
 * Required Methods:
 * - get_template_path()  : Path ke template DOCX
 * - get_output_filename(): Nama file output
 * - get_data()          : Data untuk template
 * 
 * Dependencies:
 * - DocGen_Provider interface
 * - WP DocGen Plugin
 * - Asosiasi CRUD Class
 */


if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class Asosiasi_Docgen_Member_Certificate_Provider implements WP_DocGen_Provider {
    private $member_id;
    private $data;
    
    public function __construct($member_id) {
        // ID didapat dari AJAX parameter
        $this->member_id = $member_id;
        // Load single member data
        $this->load_member_data(); 
    }

    private function load_member_data() {
        global $wpdb;
        
        // Get single member by ID
        $member = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}asosiasi_members WHERE id = %d",
            $this->member_id
        ), ARRAY_A);

        if (!$member) {
            throw new Exception('Member not found');
        }

        $this->data = $member;
    }

    /**
     * Get template path
     * @return string Template file path
     */
    public function get_template_path() {
        $upload_dir = wp_upload_dir();
        return trailingslashit($upload_dir['basedir']) . 
               'docgen-templates/asosiasi/member-certificate.docx';
    }

    /**
     * Get output filename
     * @return string Output filename
     */
    public function get_output_filename() {
        return sprintf(
            'sertifikat-%s-%s',
            sanitize_title($this->data['company_name']),
            date('Ymd-His')
        );
    }

    /**
     * Get output format
     * @return string Output format (docx, pdf, etc)
     */
    public function get_output_format() {
        return 'docx';
    }

    /**
     * Get temporary directory path
     * @return string Temp directory path
     */
    public function get_temp_dir() {
        $upload_dir = wp_upload_dir();
        $temp_dir = trailingslashit($upload_dir['basedir']) . 'docgen-temp/asosiasi';
        
        if (!file_exists($temp_dir)) {
            wp_mkdir_p($temp_dir);
        }
        
        return $temp_dir;
    }

    /**
     * Get data for template
     * @return array Template data
     */
    public function get_data() {
        // Check & update certificate info first
        $this->maybe_update_certificate_info();
        
        // Generate data for single member
        return array(
            'nomor_sertifikat' => $this->data['nomor_sertifikat'],
            'company_name' => $this->data['company_name'],
            'company_leader' => $this->data['company_leader'],
            'leader_position' => $this->data['leader_position'],
            'business_field' => $this->data['business_field'],
            'city' => $this->data['city'],
            'company_address' => $this->data['company_address'],
            'npwp' => $this->data['npwp'],
            'issue_date' => date_i18n('j F Y', strtotime($this->data['tanggal_cetak'])),
            'qr_data' => json_encode([
                'cert_number' => $this->data['nomor_sertifikat'],
                'company' => $this->data['company_name'],
                'issued' => $this->data['tanggal_cetak']
            ])
        );
    }

    private function maybe_update_certificate_info() {
        global $wpdb;
        
        if (empty($this->data['nomor_sertifikat'])) {
            // Generate new certificate number and update
            $cert_number = $this->generate_certificate_number();
            $wpdb->update(
                $wpdb->prefix . 'asosiasi_members',
                array(
                    'nomor_sertifikat' => $cert_number,
                    'tanggal_cetak' => current_time('mysql')
                ),
                array('id' => $this->member_id)
            );
            
            // Update local data
            $this->data['nomor_sertifikat'] = $cert_number;
            $this->data['tanggal_cetak'] = current_time('mysql');
        } else {
            // Only update print date
            $wpdb->update(
                $wpdb->prefix . 'asosiasi_members',
                array('tanggal_cetak' => current_time('mysql')),
                array('id' => $this->member_id)
            );
            
            // Update local data
            $this->data['tanggal_cetak'] = current_time('mysql');
        }
    }

    private function generate_certificate_number() {
        // Format: CERT/[Running Number]/[Year]/[Member ID]
        $prefix = 'CERT';
        $year = date('Y');
        
        global $wpdb;
        
        // Get running number for current year
        $running_number = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) + 1 
             FROM {$wpdb->prefix}asosiasi_members 
             WHERE YEAR(tanggal_cetak) = %d",
            $year
        ));

        return sprintf(
            '%s/%03d/%s/%04d',
            $prefix,           // CERT
            $running_number,   // Running number, padded to 3 digits
            $year,            // Current year
            $this->member_id  // Member ID, padded to 4 digits
        );
    }

}
