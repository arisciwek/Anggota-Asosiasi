<?php
/**
 * Kelas untuk menangani operasi CRUD SKP Perusahaan
 *
 * @package Asosiasi
 * @version 1.0.0
 */

class Asosiasi_SKP_Perusahaan {
    /**
     * Nama tabel database
     */
    private $table_name;
    private $upload_dir;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'asosiasi_skp_perusahaan';
        
        // Set upload directory
        $upload_dir = wp_upload_dir();
        $this->upload_dir = $upload_dir['basedir'] . '/asosiasi-skp/perusahaan';
        
        // Create upload directory if it doesn't exist
        if (!file_exists($this->upload_dir)) {
            wp_mkdir_p($this->upload_dir);
            // Protect directory
            $this->protect_upload_directory();
        }
    }

    /**
     * Create tables on plugin activation
     */
    public static function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        $table_name = $wpdb->prefix . 'asosiasi_skp_perusahaan';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            member_id mediumint(9) NOT NULL,
            nomor_skp varchar(100) NOT NULL,
            penanggung_jawab varchar(255) NOT NULL,
            tanggal_terbit date NOT NULL,
            masa_berlaku date NOT NULL,
            file_path varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY member_id (member_id),
            FOREIGN KEY (member_id) REFERENCES {$wpdb->prefix}asosiasi_members(id) ON DELETE CASCADE
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
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

        // Insert SKP data
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'member_id' => intval($data['member_id']),
                'nomor_skp' => sanitize_text_field($data['nomor_skp']),
                'penanggung_jawab' => sanitize_text_field($data['penanggung_jawab']),
                'tanggal_terbit' => $data['tanggal_terbit'],
                'masa_berlaku' => $data['masa_berlaku'],
                'file_path' => $file_path
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s')
        );

        if ($result === false) {
            // Delete uploaded file if database insert fails
            @unlink($this->upload_dir . '/' . basename($file_path));
            return new WP_Error('db_insert_error', __('Failed to save SKP data.', 'asosiasi'));
        }

        return $wpdb->insert_id;
    }

    /**
     * Get SKP by member ID
     */
    public function get_member_skp($member_id) {
        global $wpdb;
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE member_id = %d ORDER BY created_at DESC",
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
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE id = %d",
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

        $update_data = array(
            'nomor_skp' => sanitize_text_field($data['nomor_skp']),
            'penanggung_jawab' => sanitize_text_field($data['penanggung_jawab']),
            'tanggal_terbit' => $data['tanggal_terbit'],
            'masa_berlaku' => $data['masa_berlaku']
        );
        $update_format = array('%s', '%s', '%s', '%s');

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
                @unlink($this->upload_dir . '/' . basename($file_path));
            }
            return new WP_Error('db_update_error', __('Failed to update SKP data.', 'asosiasi'));
        }

        // Delete old file if new file was uploaded
        if (isset($old_file)) {
            @unlink($this->upload_dir . '/' . basename($old_file));
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
            @unlink($this->upload_dir . '/' . basename($skp['file_path']));
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
        $filepath = $this->upload_dir . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return new WP_Error('upload_error', __('Failed to upload file.', 'asosiasi'));
        }

        return $filename;
    }

    /**
     * Clean up expired SKPs
     */
    public function cleanup_expired_skp() {
        global $wpdb;
        
        $expired_skps = $wpdb->get_results(
            "SELECT * FROM {$this->table_name} WHERE masa_berlaku < CURDATE()",
            ARRAY_A
        );

        foreach ($expired_skps as $skp) {
            $this->delete_skp($skp['id']);
        }
    }

    /**
     * Get file URL
     */
    public function get_file_url($filename) {
        $upload_dir = wp_upload_dir();
        return $upload_dir['baseurl'] . '/asosiasi-skp/perusahaan/' . $filename;
    }
}