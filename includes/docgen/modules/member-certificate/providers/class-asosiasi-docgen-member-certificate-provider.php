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

        // Tambahkan di konstruktor atau init plugin
        add_filter('query_vars', function($vars) {
            $vars[] = 'certificate_verify';
            $vars[] = 'member_id';
            $vars[] = 'verify_code';
            return $vars;
        });
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
        return isset($_POST['format']) && $_POST['format'] === 'pdf' ? 'pdf' : 'docx';
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
    * Get template data untuk sertifikat anggota
    * 
    * Data yang disediakan terdiri dari 2 jenis:
    * 1. Regular fields - Langsung digunakan di template tanpa processing
    * 2. Custom fields - Memerlukan processing oleh WP_DocGen_Template
    * 
    * Regular Fields Format:
    * - nomor_sertifikat    : Nomor sertifikat anggota
    * - company_name        : Nama perusahaan
    * - company_leader      : Nama pimpinan perusahaan  
    * - leader_position     : Jabatan pimpinan
    * - business_field      : Bidang usaha
    * - city               : Kota
    * - company_address     : Alamat lengkap
    * - npwp               : NPWP perusahaan
    * - issue_date         : Tanggal cetak (formatted)
    * - qr_data            : Verification URL untuk QR code
    * 
    * Custom Fields Format:
    * - date:field:format  : Format tanggal customize 
    *   Example: 'date:issue_date:j F Y H:i' => $this->data['tanggal_cetak']
    * 
    * - image:name         : Path file gambar (hanya path)
    *   Example: 'image:logo' => '/path/to/logo.png'
    *   Template control: ${image:logo:50:50:center:middle}
    * 
    * - user:field         : Data user WordPress
    *   Example: 'user:display_name' => wp_get_current_user()->display_name
    * 
    * - site:field         : Info site WordPress
    *   Example: 'site:domain' => parse_url(home_url(), PHP_URL_HOST)
    * 
    * - qrcode:text        : URL/text untuk QR code (hanya text)
    *   Example: 'qrcode:qr_data' => $verification_url
    *   Template control: ${qrcode:qr_data:50:M}
    *
    * @since 1.0.0
    * @since 1.0.2 Simplifikasi format custom fields (image & qrcode)
    * 
    * @access public
    * @return array Associative array berisi data untuk template
    * @throws Exception Jika member tidak ditemukan
    */

    public function get_data() {
        // Generate verification URL dengan format yang valid
        $verification_code = base64_encode($this->member_id . '_' . time());
        $verification_url = add_query_arg([
            'certificate_verify' => 1,
            'member_id' => $this->member_id,
            'verify_code' => $verification_code
        ], home_url());



        // 1. Membuat URL untuk QR Code
        $qrcode_verification_code = base64_encode($this->member_id . '_' . time());
        $qrcode_verification_url = add_query_arg([
            'certificate_verify' => 1,
            'member_id' => $this->member_id,
            'verify_code' => $qrcode_verification_code
        ], home_url());

        // 2. Encode URL untuk menghindari karakter-karakter yang tidak diinginkan
        // $data['qr_data'] = urldecode($qrcode_verification_url);  // Menghapus rawurlencode() dan memastikan URL aman

        // Pastikan certificate info updated
        $this->maybe_update_certificate_info();
        
        error_log('QR Data URL: ' . $verification_url);

        $data = [
            'nomor_sertifikat' => $this->data['nomor_sertifikat'],
            'company_name' => $this->data['company_name'],
            'contact_person' => $this->data['contact_person'],  // Tambahan
            'email' => $this->data['email'],                    // Tambahan
            'phone' => $this->data['phone'],                    // Tambahan
            'company_leader' => $this->data['company_leader'],
            'leader_position' => $this->data['leader_position'],
            'business_field' => $this->data['business_field'], 
            'city' => $this->data['city'],
            'company_address' => $this->data['company_address'],
            'postal_code' => $this->data['postal_code'],        // Tambahan
            'ahu_number' => $this->data['ahu_number'],
            'npwp' => $this->data['npwp'],
            'valid_until' => date_i18n('j F Y', $this->data['valid_until']),
            'issue_date' => date_i18n('j F Y, H:i:s', strtotime($this->data['tanggal_cetak'])),

            //'qr_data' => urldecode($qrcode_verification_url)
            'qr_data' => $qrcode_verification_url

        ];

        // Khusus custom fields yang butuh processing, gunakan WP_DocGen
        $custom_fields = [

                // Date
                'date:tanggal_cetak:j F Y H:i' => $this->data['tanggal_cetak'],
                
                // Image
                'image:logo' => wp_upload_dir()['basedir'] . '/asosiasi/logo-rui-02.png',
                
                // Image
                'image:logo_k3' => wp_upload_dir()['basedir'] . '/asosiasi/logo-k3.png',
                
                // User 
                'user:display_name' => wp_get_current_user()->display_name,
                
                // Site
                'site:domain' => parse_url(home_url(), PHP_URL_HOST),
                
                // QR Code
                'qrcode:qr_data' => wp_kses_post($verification_url)

                // ... custom fields lainnya
        ];
            

        // Ambil semua setting yang ada
        $organization_name = get_option('asosiasi_organization_name');
        $ketua_umum = get_option('asosiasi_ketua_umum');
        $sekretaris_umum = get_option('asosiasi_sekretaris_umum');
        $contact_email = get_option('asosiasi_contact_email');
        $certificate_header = get_option('asosiasi_certificate_header');
        $certificate_footer = get_option('asosiasi_certificate_footer');
        $website = get_option('asosiasi_website'); // Pengaturan Website


        // Settings Fields (mengambil data pengaturan yang sudah disimpan)
        $settings_fields = [
            'organization_name' => $organization_name,
            'ketua_umum' => $ketua_umum,
            'sekretaris_umum' => $sekretaris_umum,
            'contact_email' => $contact_email,
            //'certificate_header' => $certificate_header,
            //'certificate_footer' => $certificate_footer,
            'website' => $website,  // Website
        ];

        // Ambil services yang dimiliki oleh member
        $services = $this->get_member_services($this->member_id);

        // Gabungkan layanan menjadi satu string
        $services_data = [];
        foreach ($services as $service) {
            $services_data[] = $service['short_name'] . ' - ' . $service['full_name'];
        }

        // Gabungkan semua layanan menjadi satu string terpisah oleh koma
        $services_data_string = implode(', ', $services_data);

        $final_data = array_merge($data, $settings_fields, $custom_fields, ['services' => $services_data_string]);

        error_log('Final Data for Template: ' . print_r($final_data, true));
        return $final_data;

    }

    /**
     * Mendapatkan semua layanan yang dimiliki oleh member
     * 
     * @param int $member_id ID member
     * @return array Daftar layanan yang dimiliki oleh member
     */
    private function get_member_services($member_id) {
        global $wpdb;

        // Ambil service_id yang dimiliki oleh member
        $services = $wpdb->get_results($wpdb->prepare(
            "SELECT s.short_name, s.full_name
            FROM {$wpdb->prefix}asosiasi_member_services ms
            JOIN {$wpdb->prefix}asosiasi_services s ON ms.service_id = s.id
            WHERE ms.member_id = %d",
            $member_id
        ), ARRAY_A);

        return $services;
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
