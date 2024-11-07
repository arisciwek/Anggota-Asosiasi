<?php
/**
 * Kelas untuk menangani operasi CRUD anggota asosiasi
 *
 * @package Asosiasi
 * @version 1.1.0
 */

class Asosiasi_CRUD {

    /**
     * Nama tabel database
     *
     * @since    1.1.0
     * @access   private
     * @var      string    $table_name    Nama tabel database dengan prefix
     */
    private $table_name;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.1.0
     */
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'asosiasi_members';
    }

    /**
     * Create new member.
     *
     * @since    1.1.0
     * @param    array    $data    Data anggota yang akan ditambahkan
     * @return   int|false         ID dari data yang ditambahkan atau false jika gagal
     */
    public function create_member($data) {
        global $wpdb;
        
        $result = $wpdb->insert( 
            $this->table_name, 
            array(
                'company_name' => sanitize_text_field($data['company_name']),
                'contact_person' => sanitize_text_field($data['contact_person']),
                'email' => sanitize_email($data['email']),
                'phone' => sanitize_text_field($data['phone']),
            ),
            array('%s', '%s', '%s', '%s')
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Get all members.
     *
     * @since    1.1.0
     * @return   array    Daftar semua anggota
     */
    public function get_members() {
        global $wpdb;
        return $wpdb->get_results(
            "SELECT * FROM {$this->table_name} ORDER BY created_at DESC",
            ARRAY_A
        );
    }

    /**
     * Get single member by ID.
     *
     * @since    1.1.0
     * @param    int      $id    ID anggota
     * @return   array    Data anggota
     */
    public function get_member($id) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id),
            ARRAY_A
        );
    }

    /**
     * Update member data.
     *
     * @since    1.1.0
     * @param    int      $id      ID anggota
     * @param    array    $data    Data yang akan diupdate
     * @return   bool              Status update
     */
    public function update_member($id, $data) {
        global $wpdb;
        
        // Log for debugging
        if (WP_DEBUG) {
            error_log('Updating member with ID: ' . $id);
            error_log('Update data: ' . print_r($data, true));
        }

        $updated = $wpdb->update(
            $this->table_name,
            array(
                'company_name' => sanitize_text_field($data['company_name']),
                'contact_person' => sanitize_text_field($data['contact_person']),
                'email' => sanitize_email($data['email']),
                'phone' => sanitize_text_field($data['phone']),
            ),
            array('id' => $id),
            array('%s', '%s', '%s', '%s'),
            array('%d')
        );

        if (WP_DEBUG) {
            error_log('Update result: ' . ($updated !== false ? 'success' : 'failed'));
            if ($updated === false) {
                error_log('Database error: ' . $wpdb->last_error);
            }
        }

        return $updated !== false;
    }

    /**
     * Delete member.
     *
     * @since    1.1.0
     * @param    int      $id    ID anggota
     * @return   bool           Status delete
     */
    public function delete_member($id) {
        global $wpdb;
        return $wpdb->delete(
            $this->table_name,
            array('id' => $id),
            array('%d')
        );
    }
}