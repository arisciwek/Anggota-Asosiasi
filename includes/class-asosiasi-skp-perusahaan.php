<?php
/**
 * Kelas untuk menangani operasi CRUD SKP Perusahaan
 *
 * @package Asosiasi
 * @version 1.2.0
 * Path: includes/class-asosiasi-skp-perusahaan.php
 * 
 * Changelog:
 * 1.2.0 - 2024-03-15
 * - Added service_id handling in add_skp and update_skp methods
 * - Updated get_skp and get_member_skp to include service info
 * 1.1.0 - Added secure file handling methods
 * 1.0.0 - Initial version
 */

class Asosiasi_SKP_Perusahaan {
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
        $this->table_name = $wpdb->prefix . 'asosiasi_skp_perusahaan';
        
        // Set upload paths
        $upload = wp_upload_dir();
        $this->upload_dir = $upload['basedir'] . '/asosiasi-skp/perusahaan';
        $this->upload_url = $upload['baseurl'] . '/asosiasi-skp/perusahaan';
        
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
        $index_content = "<?php\n// Silence is golden.";
        @file_put_contents($this->upload_dir . '/index.php', $index_content);
    }

    /**
     * Add new SKP Perusahaan
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
                'penanggung_jawab' => $data['penanggung_jawab'],
                'tanggal_terbit' => $data['tanggal_terbit'],
                'masa_berlaku' => $data['masa_berlaku'],
                'file_path' => $file_path,
                'status' => 'active'
            ),
            array('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s')
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
            'penanggung_jawab' => $data['penanggung_jawab'],
            'tanggal_terbit' => $data['tanggal_terbit'],
            'masa_berlaku' => $data['masa_berlaku']
        );
        $update_format = array('%s', '%d', '%s', '%s', '%s');

        // Handle file upload if new file is provided
        if ($file) {
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

        // Delete old file if new file was uploaded
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
        $filename = uniqid('skp_perusahaan_') . '.pdf';
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
    private function sanitize_skp_data($data) {
        return array(
            'member_id' => absint($data['member_id']),
            'service_id' => absint($data['service_id']),
            'nomor_skp' => sanitize_text_field($data['nomor_skp']),
            'penanggung_jawab' => sanitize_text_field($data['penanggung_jawab']),
            'tanggal_terbit' => sanitize_text_field($data['tanggal_terbit']),
            'masa_berlaku' => sanitize_text_field($data['masa_berlaku'])
        );
    }
}