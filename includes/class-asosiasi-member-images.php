<?php
/**
 * Class untuk mengelola foto anggota
 *
 * @package Asosiasi
 * @version 2.1.0
 * Path: includes/class-asosiasi-member-images.php
 * 
 * Changelog:
 * 2.1.0 - 2024-03-13
 * - Initial release of member images feature
 * - Added support for mandatory and optional images
 * - Added image validation and processing
 */

class Asosiasi_Member_Images {
    private $table_images;
    private $upload_dir;
    private $allowed_types;
    private $max_size;

    public function __construct() {
        global $wpdb;
        $this->table_images = $wpdb->prefix . 'asosiasi_member_images';
        
        // Setup upload directory
        $upload_dir = wp_upload_dir();
        $this->upload_dir = $upload_dir['basedir'] . '/asosiasi-members/images';
        
        // Create directory if it doesn't exist
        if (!file_exists($this->upload_dir)) {
            wp_mkdir_p($this->upload_dir);
        }

        $this->allowed_types = array('image/jpeg', 'image/png');
        $this->max_size = 1.5 * 1024 * 1024; // 1.5MB in bytes
    }

    /**
     * Upload image
     *
     * @param int    $member_id   ID anggota
     * @param array  $file        $_FILES array
     * @param string $type        'mandatory' atau 'optional'
     * @param int    $order       Urutan untuk foto optional (1-3)
     * @return array|WP_Error
     */
    public function upload_image($member_id, $file, $type = 'optional', $order = 0) {
        // Validasi file
        if (!in_array($file['type'], $this->allowed_types)) {
            return new WP_Error('invalid_type', 'File harus berformat JPG atau PNG.');
        }

        if ($file['size'] > $this->max_size) {
            return new WP_Error('invalid_size', 'Ukuran file maksimal 1.5MB.');
        }

        // Generate nama file unik
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = sprintf(
            '%d-%s-%d-%s.%s',
            $member_id,
            $type,
            $order,
            uniqid(),
            $ext
        );

        // Pindahkan file
        $filepath = $this->upload_dir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            return new WP_Error('upload_failed', 'Gagal mengupload file.');
        }

        // Hapus foto lama jika ada
        $this->delete_image($member_id, $type, $order);

        // Simpan ke database
        global $wpdb;
        $result = $wpdb->insert(
            $this->table_images,
            array(
                'member_id' => $member_id,
                'image_type' => $type,
                'image_order' => $order,
                'file_name' => $filename,
                'file_path' => $filepath
            ),
            array('%d', '%s', '%d', '%s', '%s')
        );

        if (!$result) {
            @unlink($filepath);
            return new WP_Error('db_error', 'Gagal menyimpan data foto.');
        }

        return array(
            'id' => $wpdb->insert_id,
            'file_name' => $filename,
            'file_path' => $filepath,
            'url' => $this->get_image_url($filename)
        );
    }

    /**
     * Get image URL
     */
    public function get_image_url($filename) {
        $upload_dir = wp_upload_dir();
        return $upload_dir['baseurl'] . '/asosiasi-members/images/' . $filename;
    }

    /**
     * Delete image
     */
    public function delete_image($member_id, $type, $order = 0) {
        global $wpdb;
        
        // Get existing image
        $image = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_images} 
            WHERE member_id = %d AND image_type = %s AND image_order = %d",
            $member_id, $type, $order
        ));

        if ($image) {
            // Delete file
            @unlink($image->file_path);
            
            // Delete from database
            return $wpdb->delete(
                $this->table_images,
                array(
                    'member_id' => $member_id,
                    'image_type' => $type,
                    'image_order' => $order
                ),
                array('%d', '%s', '%d')
            );
        }

        return true;
    }

    /**
     * Get member images
     */
    public function get_member_images($member_id) {
        global $wpdb;
        
        $images = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_images} WHERE member_id = %d ORDER BY image_type DESC, image_order ASC",
            $member_id
        ));

        if (!$images) {
            return array();
        }

        $result = array(
            'mandatory' => null,
            'optional' => array()
        );

        foreach ($images as $image) {
            $image_data = array(
                'id' => $image->id,
                'file_name' => $image->file_name,
                'file_path' => $image->file_path,
                'url' => $this->get_image_url($image->file_name)
            );

            if ($image->image_type === 'mandatory') {
                $result['mandatory'] = $image_data;
            } else {
                $result['optional'][$image->image_order] = $image_data;
            }
        }

        return $result;
    }

    /**
     * Check if member has mandatory image
     */
    public function has_mandatory_image($member_id) {
        global $wpdb;
        return (bool) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->table_images} WHERE member_id = %d AND image_type = 'mandatory'",
            $member_id
        ));
    }
}