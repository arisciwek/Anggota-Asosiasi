<?php

/**
 * Class untuk mengelola sertifikat anggota
 *
 * @package Asosiasi
 * @version 1.0.1
 * Path: includes/class-asosiasi-certificate.php
 * 
 * Changelog:
 * 1.0.1 - 2024-11-21
 * - Added certificate logging
 * - Added status validation
 * - Added file cleanup
 * - Added proper error handling
 * 
 * 1.0.0 - Initial release
 */

// Check if WP DocGen is active
if (!function_exists('wp_docgen')) {
    return;
}


class Asosiasi_Certificate implements WP_DocGen_Provider {
    
    private $template_id = 'asosiasi-member-certificate';
    private $upload_dir;
    private $allowed_statuses = array('active', 'activated');
    private $member_id = 0;
    
    public function __construct() {
        // Load helper if not loaded
        if (!function_exists('asosiasi_get_certificate_dir')) {
            require_once ASOSIASI_DIR . 'helpers/member-certificate-templates.php';
        }
        
        // Setup certificate directory
        $base_dir = wp_upload_dir();
        $this->upload_dir = $base_dir['basedir'] . '/asosiasi-certificates';
        
        // Create directory if needed
        if (!file_exists($this->upload_dir)) {
            wp_mkdir_p($this->upload_dir);
            $this->protect_directory();
        }
        
        $this->init_hooks();
    }
    
    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Register template path
        add_filter('wp_docgen_template_path', array($this, 'register_template'), 10, 2);
        
        // Register template data
        add_filter('wp_docgen_template_data', array($this, 'prepare_certificate_data'), 10, 2);
        
        // Set output path
        add_filter('wp_docgen_output_path', array($this, 'set_output_path'), 10, 2);
        
        // Set output filename
        add_filter('wp_docgen_output_filename', array($this, 'set_output_filename'), 10, 2);
        
        // Add certificate download button
        add_action('asosiasi_after_member_info', array($this, 'add_download_button'));
        
        // Handle certificate generation
        add_action('wp_ajax_generate_member_certificate', array($this, 'ajax_generate_certificate'));

        // Cleanup old files
        add_action('wp_scheduled_delete', array($this, 'cleanup_old_certificates'));
    }

    /**
     * Protect upload directory
     */
    private function protect_directory() {
        $htaccess = $this->upload_dir . '/.htaccess';
        if (!file_exists($htaccess)) {
            $content = "Options -Indexes\n";
            $content .= "<FilesMatch '\.(php|php\.|php3|php4|php5|php7|phtml|pl|py|jsp|asp|htm|shtml|sh|cgi)$'>\n";
            $content .= "Order Deny,Allow\n";
            $content .= "Deny from all\n";
            $content .= "</FilesMatch>\n";
            
            file_put_contents($htaccess, $content);
        }

        $index = $this->upload_dir . '/index.php';
        if (!file_exists($index)) {
            file_put_contents($index, '<?php // Silence is golden');
        }
    }
    
    /**
     * Register certificate template path
     */
    public function register_template($template_path, $template_id) {
        if ($template_id === $this->template_id) {
            //return ASOSIASI_DIR . 'templates/certificate-template.docx';
            return asosiasi_get_template_path();
        }
        return $template_path;
    }
    
    /**
     * Prepare certificate data
     */
    public function prepare_certificate_data($data, $template_id) {
        if ($template_id !== $this->template_id) {
            return $data;
        }
        
        $member_id = isset($data['member_id']) ? intval($data['member_id']) : 0;
        if (!$member_id) {
            return $data;
        }
        
        try {
            // Get member data
            $crud = new Asosiasi_CRUD();
            $member = $crud->get_member($member_id);
            
            if (!$member) {
                throw new Exception(__('Member not found', 'asosiasi'));
            }
            
            // Check if member has active SKP
            if (!$this->has_active_skp($member_id)) {
                throw new Exception(__('Member does not have active SKP', 'asosiasi'));
            }
            
            // Get member services
            $services = new Asosiasi_Services();
            $member_services = $services->get_member_services($member_id);
            $service_names = array();
            
            if ($member_services) {
                foreach ($member_services as $service_id) {
                    $service = $services->get_service($service_id);
                    if ($service) {
                        $service_names[] = $service['full_name'];
                    }
                }
            }
            
            // Get organization info
            $org_name = get_option('asosiasi_organization_name', '');
            
            // Generate certificate number
            $cert_number = $this->generate_certificate_number($member_id);
            
            // Log certificate generation
            $this->log_certificate_generation($member_id, $cert_number);
            
            // Return certificate data
            return array(
                'org_name' => $org_name,
                'cert_number' => $cert_number,
                'company_name' => $member['company_name'],
                'company_leader' => $member['company_leader'],
                'leader_position' => $member['leader_position'],
                'business_field' => $member['business_field'],
                'city' => $member['city'],
                'issue_date' => $this->format_date(current_time('mysql')),
                'services' => implode(", ", $service_names),
                'qr_data' => $this->generate_qr_data($member, $cert_number)
            );
            
        } catch (Exception $e) {
            if (WP_DEBUG) {
                error_log('Certificate data preparation failed: ' . $e->getMessage());
            }
            return new WP_Error('cert_data_error', $e->getMessage());
        }
    }
    
    /**
     * Check if member has active SKP
     */
    private function has_active_skp($member_id) {
        global $wpdb;
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}asosiasi_skp_perusahaan 
             WHERE member_id = %d AND status IN ('active','activated')",
            $member_id
        ));
        
        return (bool) $count;
    }
    
    /**
     * Format date to Indonesian format
     */
    private function format_date($mysql_date) {
        $months = array(
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        );
        
        $timestamp = strtotime($mysql_date);
        $day = date('j', $timestamp);
        $month = $months[date('n', $timestamp) - 1];
        $year = date('Y', $timestamp);
        
        return "$day $month $year";
    }
    
    /**
     * Generate certificate number
     */
    private function generate_certificate_number($member_id) {
        $year = current_time('Y');
        
        // Get sequential number for this year
        $seq_number = $this->get_next_sequence_number($year);
        
        return sprintf('CERT/%s/%04d/%04d', 
            $year,
            $member_id,
            $seq_number
        );
    }
    
    /**
     * Get next sequence number for certificates
     */
    private function get_next_sequence_number($year) {
        $option_name = 'asosiasi_cert_seq_' . $year;
        $current = get_option($option_name, 0);
        $next = $current + 1;
        update_option($option_name, $next);
        return $next;
    }
    
    /**
     * Generate QR code data
     */
    private function generate_qr_data($member, $cert_number) {
        return json_encode(array(
            'type' => 'MEMBER_CERT',
            'number' => $cert_number,
            'company' => $member['company_name'],
            'id' => $member['id'],
            'timestamp' => current_time('timestamp')
        ));
    }
    
    /**
     * Set certificate output path
     */
    public function set_output_path($output_path, $template_id) {
        if ($template_id === $this->template_id) {
            return $this->upload_dir;
        }
        return $output_path;
    }
    
    /**
     * Set certificate filename
     */
    public function set_output_filename($filename, $template_id) {
        if ($template_id === $this->template_id && !empty($_REQUEST['member_id'])) {
            $member_id = intval($_REQUEST['member_id']);
            return sprintf('certificate-%d-%s', $member_id, time());
        }
        return $filename;
    }
    
    /**
     * Add download button to member view
     */
    public function add_download_button($member_id) {
        if (!$member_id || !$this->has_active_skp($member_id)) {
            return;
        }
        
        ?>
        <div class="certificate-download">
            <button type="button" 
                    class="button button-primary generate-certificate" 
                    data-member="<?php echo esc_attr($member_id); ?>"
                    data-nonce="<?php echo wp_create_nonce('generate_certificate_' . $member_id); ?>">
                <span class="dashicons dashicons-download"></span>
                <?php _e('Download Certificate', 'asosiasi'); ?>
            </button>
            <p class="description">
                <?php _e('Download member certificate in PDF format.', 'asosiasi'); ?>
            </p>
        </div>
        <?php
    }
    
    /**
     * Handle AJAX certificate generation
     */
    public function ajax_generate_certificate() {
        try {
            if (!isset($_POST['nonce']) || !isset($_POST['member_id'])) {
                throw new Exception(__('Invalid request', 'asosiasi'));
            }
            
            $member_id = intval($_POST['member_id']);
            
            if (!wp_verify_nonce($_POST['nonce'], 'generate_certificate_' . $member_id)) {
                throw new Exception(__('Security check failed', 'asosiasi'));
            }
            
            if (!function_exists('wp_docgen')) {
                throw new Exception(__('Document generator not available', 'asosiasi'));
            }
            
            if (!$this->has_active_skp($member_id)) {
                throw new Exception(__('Member must have active SKP to generate certificate', 'asosiasi'));
            }
            
            // Generate certificate
            $result = wp_docgen()->generate($this->template_id, array(
                'member_id' => $member_id
            ));
            
            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }
            
            // Get download URL
            $upload_dir = wp_upload_dir();
            $file_url = str_replace(
                $upload_dir['basedir'],
                $upload_dir['baseurl'],
                $result
            );
            
            wp_send_json_success(array(
                'message' => __('Certificate generated successfully', 'asosiasi'),
                'file_url' => $file_url
            ));
            
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage()
            ));
        }
    }
    
    /**
     * Log certificate generation
     */
    private function log_certificate_generation($member_id, $cert_number) {
        global $wpdb;
        
        $wpdb->insert(
            $wpdb->prefix . 'asosiasi_certificate_log',
            array(
                'member_id' => $member_id,
                'cert_number' => $cert_number,
                'generated_at' => current_time('mysql'),
                'generated_by' => get_current_user_id()
            ),
            array('%d', '%s', '%s', '%d')
        );
    }
    
    /**
     * Cleanup old certificates
     * Runs via wp_scheduled_delete hook
     */
    public function cleanup_old_certificates() {
        $files = glob($this->upload_dir . '/certificate-*');
        $now = time();
        
        foreach ($files as $file) {
            // Delete files older than 24 hours
            if ($now - filemtime($file) >= 24 * 3600) {
                @unlink($file);
            }
        }
    }

    /*
     *
     *
     */


    /**
     * Get data for document template
     * 
     * @return array|WP_Error Template data or error
     */
    public function get_data() {
        try {
            if (!$this->member_id) {
                throw new Exception(__('Invalid member ID', 'asosiasi'));
            }

            // Re-use existing data preparation
            $data = $this->prepare_certificate_data(array(), $this->template_id);
            
            if (is_wp_error($data)) {
                throw new Exception($data->get_error_message());
            }

            // Add WP DocGen specific fields
            return array_merge($data, array(
                'tanggal' => '${tanggal:' . current_time('mysql') . ':j F Y}',
                'qr_data' => '${qrcode:' . $data['qr_data'] . ':150}'
            ));

        } catch (Exception $e) {
            return new WP_Error('cert_data_error', $e->getMessage());
        }
    }

    /**
     * Get template path using helper
     * 
     * @return string Template file path
     */
    public function get_template_path() {
        // Helper handles template verification
        if (!asosiasi_template_exists()) {
            if (!asosiasi_copy_default_template()) {
                return false;
            }
        }
        
        return asosiasi_get_template_path();
    }

    /**
     * Get output filename
     * 
     * @return string Output filename
     */
    public function get_output_filename() {
        $cert_number = $this->generate_certificate_number($this->member_id);
        return sanitize_file_name('sertifikat-' . $cert_number);
    }

    /**
     * Get output format
     * 
     * @return string Output format (pdf)
     */
    public function get_output_format() {
        return 'pdf';
    }

    /**
     * Get temporary directory
     * 
     * @return string Temp directory path
     */
    public function get_temp_dir() {
        return asosiasi_get_certificate_dir() . 'temp';
    }

    /**
     * Set member ID for generation
     * Helper method for WP DocGen usage
     * 
     * @param int $member_id Member ID
     */
    public function set_member_id($member_id) {
        $this->member_id = absint($member_id);
    }
    
}
