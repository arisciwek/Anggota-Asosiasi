<?php
/**
 * SKP Tenaga Ahli Handler Class
 *
 * @package     Asosiasi
 * @subpackage  Includes/SKP_Tenaga_Ahli
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /asosiasi/includes/skp-tenaga-ahli/class-asosiasi-skp-tenaga-ahli.php
 *
 * Description: Menangani operasi CRUD untuk SKP Tenaga Ahli termasuk
 *              file handling dan validasi data
 *
 * Changelog:
 * 1.0.0 - 2024-11-22
 * - Initial creation
 * - Added CRUD operations
 * - Added file upload handling
 * - Added data validation
 */

class Asosiasi_SKP_Tenaga_Ahli {
    /**
     * Nama tabel database
     */
    private $table_name;
    private $upload_dir;
    private $upload_url;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'asosiasi_skp_tenaga_ahli';
        
        // Set upload paths
        $upload = wp_upload_dir();
        $this->upload_dir = $upload['basedir'] . '/asosiasi-skp/tenaga-ahli';
        $this->upload_url = $upload['baseurl'] . '/asosiasi-skp/tenaga-ahli';
        
        // Create upload directory if it doesn't exist
        if (!file_exists($this->upload_dir)) {
            wp_mkdir_p($this->upload_dir);
            // Protect directory
            $this->protect_upload_directory();
        }
    }

    /**
     * Get absolute file path
     */
    public function get_file_path($filename) {
        return trailingslashit($this->upload_dir) . basename($filename);
    }

    /**
     * Get file URL (for admin use only)
     */
    public function get_file_url($filename) {
        return trailingslashit($this->upload_url) . basename($filename);
    }

    /**
     * Protect upload directory
     */
    private function protect_upload_directory() {
        $htaccess_content = "Options -Indexes\n";
        $htaccess_content .= "<FilesMatch '\.(pdf)$'>\n";
        $htaccess_content .= "    Order Allow,Deny\n";
        $htaccess_content .= "    Deny from all\n";
        $htaccess_content .= "</FilesMatch>\n";
        
        @file_put_contents($this->upload_dir . '/.htaccess', $htaccess_content);

        // Also add index.php for extra security
        $index_content = "<?php\n// Silence is golden";
        @file_put_contents($this->upload_dir . '/index.php', $index_content);
    }

    /**
     * Add new SKP Tenaga Ahli
     */
    public function add_skp($data, $file) {
        global $wpdb;

        // Validate file
        if (!$this->validate_file($file)) {
            return new WP_Error('invalid_file', __('Invalid file type. Only PDF files are allowed.', 'asosiasi'));
        }

        // Handle file upload
        $file_path = $this->handle_file_upload($file);
        if (is_wp_error($file_path)) {
            return $file_path;
        }

        // Sanitize input
        $data = $this->sanitize_skp_data($data);

        // Insert SKP data
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'member_id' => $data['member_id'],
                'service_id' => $data['service_id'],
                'nomor_skp' => $data['nomor_skp'],
                'nama_tenaga_ahli' => $data['nama_tenaga_ahli'],
                'penanggung_jawab' => $data['penanggung_jawab'],
                'tanggal_terbit' => $data['tanggal_terbit'],
                'masa_berlaku' => $data['masa_berlaku'],
                'file_path' => $file_path,
                'status' => 'active'
            ),
            array('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        if ($result === false) {
            // Delete uploaded file if database insert fails
            @unlink($this->get_file_path($file_path));
            return new WP_Error('db_insert_error', __('Failed to save SKP data.', 'asosiasi'));
        }

        return $wpdb->insert_id;
    }

    /**
     * Get SKP by member ID
     */
    public function get_member_skp($member_id) {
        global $wpdb;
        $services_table = $wpdb->prefix . 'asosiasi_services';
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT s.*, srv.short_name as service_short_name, srv.full_name as service_full_name 
                FROM {$this->table_name} s 
                LEFT JOIN {$services_table} srv ON s.service_id = srv.id 
                WHERE s.member_id = %d 
                ORDER BY s.created_at DESC",
                $member_id
            ),
            ARRAY_A
        );
    }

    /**
     * Get single SKP
     */
    public function get_skp($id) {
        global $wpdb;
        $services_table = $wpdb->prefix . 'asosiasi_services';
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT s.*, srv.short_name as service_short_name, srv.full_name as service_full_name 
                FROM {$this->table_name} s 
                LEFT JOIN {$services_table} srv ON s.service_id = srv.id 
                WHERE s.id = %d",
                $id
            ),
            ARRAY_A
        );
    }

    /**
     * Update SKP
     */
    public function update_skp($id, $data, $file = null) {
        global $wpdb;

        // Sanitize input
        $data = $this->sanitize_skp_data($data);
        $update_data = array(
            'nomor_skp' => $data['nomor_skp'],
            'service_id' => $data['service_id'],
            'nama_tenaga_ahli' => $data['nama_tenaga_ahli'],
            'penanggung_jawab' => $data['penanggung_jawab'],
            'tanggal_terbit' => $data['tanggal_terbit'],
            'masa_berlaku' => $data['masa_berlaku']
        );
        $update_format = array('%s', '%d', '%s', '%s', '%s', '%s');

        // Add status if present and user has capability
        if (isset($data['status']) && 
            (current_user_can('manage_options') || current_user_can('manage_skp_status'))) {
            $update_data['status'] = $data['status'];
            $update_format[] = '%s';
        }

        // Handle file upload if new file is provided
        if ($file && !empty($file['tmp_name'])) {
            if (!$this->validate_file($file)) {
                return new WP_Error('invalid_file', __('Invalid file type. Only PDF files are allowed.', 'asosiasi'));
            }

            $file_path = $this->handle_file_upload($file);
            if (is_wp_error($file_path)) {
                return $file_path;
            }

            // Get old file path to delete after successful update
            $old_skp = $this->get_skp($id);
            $old_file = $old_skp['file_path'];

            $update_data['file_path'] = $file_path;
            $update_format[] = '%s';
        }

        $result = $wpdb->update(
            $this->table_name,
            $update_data,
            array('id' => $id),
            $update_format,
            array('%d')
        );

        if ($result === false) {
            // Delete newly uploaded file if update fails
            if (isset($file_path)) {
                @unlink($this->get_file_path($file_path));
            }
            return new WP_Error('db_update_error', __('Failed to update SKP data.', 'asosiasi'));
        }

        // Delete old file if new file was uploaded successfully
        if (isset($old_file)) {
            @unlink($this->get_file_path($old_file));
        }

        return true;
    }

    /**
     * Delete SKP
     */
    public function delete_skp($id) {
        global $wpdb;

        // Get file path before deleting record
        $skp = $this->get_skp($id);
        if (!$skp) {
            return false;
        }

        $result = $wpdb->delete(
            $this->table_name,
            array('id' => $id),
            array('%d')
        );

        if ($result) {
            // Delete file after successful record deletion
            @unlink($this->get_file_path($skp['file_path']));
            return true;
        }

        return false;
    }

    /**
     * Validate file
     */
    private function validate_file($file) {
        $allowed_types = array('application/pdf');
        return in_array($file['type'], $allowed_types);
    }

    /**
     * Handle file upload
     */
    private function handle_file_upload($file) {
        // Generate unique filename
        $filename = uniqid('skp_tenaga_ahli_') . '.pdf';
        $filepath = $this->get_file_path($filename);

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return new WP_Error('upload_error', __('Failed to upload file.', 'asosiasi'));
        }

        return $filename;
    }

    /**
     * Sanitize SKP data
     */
/**
     * Sanitize SKP data
     */
    private function sanitize_skp_data($data) {
        $sanitized = array(
            'member_id' => isset($data['member_id']) ? absint($data['member_id']) : 0,
            'service_id' => isset($data['service_id']) ? absint($data['service_id']) : 0,
            'nomor_skp' => isset($data['nomor_skp']) ? sanitize_text_field($data['nomor_skp']) : '',
            'nama_tenaga_ahli' => isset($data['nama_tenaga_ahli']) ? sanitize_text_field($data['nama_tenaga_ahli']) : '',
            'penanggung_jawab' => isset($data['penanggung_jawab']) ? sanitize_text_field($data['penanggung_jawab']) : '',
            'tanggal_terbit' => isset($data['tanggal_terbit']) ? sanitize_text_field($data['tanggal_terbit']) : '',
            'masa_berlaku' => isset($data['masa_berlaku']) ? sanitize_text_field($data['masa_berlaku']) : ''
        );

        // Add status if present and user has capability
        if (isset($data['status']) && 
            (current_user_can('manage_options') || current_user_can('manage_skp_status'))) {
            // Validate status value
            $valid_statuses = array('active', 'activated', 'inactive');
            $status = sanitize_text_field($data['status']);
            $sanitized['status'] = in_array($status, $valid_statuses) ? $status : 'active';
        }

        return $sanitized;
    }
}
