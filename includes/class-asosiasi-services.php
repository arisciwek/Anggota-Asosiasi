<?php
/**
 * Kelas untuk mengelola layanan
 *
 * @package Asosiasi
 * @version 1.0.0
 */

class Asosiasi_Services {
    private $table_services;
    private $table_member_services;

    public function __construct() {
        global $wpdb;
        $this->table_services = $wpdb->prefix . 'asosiasi_services';
        $this->table_member_services = $wpdb->prefix . 'asosiasi_member_services';
    }

    /**
     * Tambah layanan baru
     */
    public function add_service($data) {
        global $wpdb;
        
        return $wpdb->insert(
            $this->table_services,
            array(
                'short_name' => sanitize_text_field($data['short_name']),
                'full_name' => sanitize_text_field($data['full_name'])
            ),
            array('%s', '%s')
        );
    }

    /**
     * Update layanan
     */
    public function update_service($id, $data) {
        global $wpdb;
        
        return $wpdb->update(
            $this->table_services,
            array(
                'short_name' => sanitize_text_field($data['short_name']),
                'full_name' => sanitize_text_field($data['full_name'])
            ),
            array('id' => $id),
            array('%s', '%s'),
            array('%d')
        );
    }

    /**
     * Hapus layanan
     */
    public function delete_service($id) {
        global $wpdb;
        return $wpdb->delete($this->table_services, array('id' => $id), array('%d'));
    }

    /**
     * Ambil semua layanan
     */
    public function get_services() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$this->table_services} ORDER BY short_name ASC", ARRAY_A);
    }

    /**
     * Ambil satu layanan
     */
    public function get_service($id) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table_services} WHERE id = %d", $id),
            ARRAY_A
        );
    }

    /**
     * Tambah relasi anggota-layanan
     */
    public function add_member_services($member_id, $service_ids) {
        global $wpdb;
        
        // Hapus relasi yang ada
        $wpdb->delete(
            $this->table_member_services,
            array('member_id' => $member_id),
            array('%d')
        );

        // Tambah relasi baru
        foreach ($service_ids as $service_id) {
            $wpdb->insert(
                $this->table_member_services,
                array(
                    'member_id' => $member_id,
                    'service_id' => $service_id
                ),
                array('%d', '%d')
            );
        }
        
        return true;
    }

    /**
     * Ambil layanan untuk anggota tertentu
     */
    public function get_member_services($member_id) {
        global $wpdb;
        return $wpdb->get_col($wpdb->prepare(
            "SELECT service_id FROM {$this->table_member_services} WHERE member_id = %d",
            $member_id
        ));
    }
}