<?php
/**
 * Class untuk menangani operasi CRUD
 *
 * @package Asosiasi
 * @version 2.4.1
 * Path: includes/class-asosiasi-crud.php 
 * 
 * Changelog:
 * 2.4.1 - 2024-11-21
 * - Fixed get_members() method query
 * - Added proper table name reference
 * - Added error logging
 * 
 * 2.4.0 - 2024-11-19
 * - Added support for new member fields
 * - Enhanced data sanitization
 * - Added field validation
 */

class Asosiasi_CRUD {

    private $table_members;

    public function __construct() {
        global $wpdb;
        $this->table_members = $wpdb->prefix . 'asosiasi_members';
    }

    /**
     * Get all members with caching
     * 
     * @return array Array of member records
     */
    public function get_members() {
        global $wpdb;

        // Query langsung tanpa prepare karena tidak ada parameter
        $results = $wpdb->get_results(
            "SELECT * FROM {$this->table_members} ORDER BY created_at DESC",
            ARRAY_A
        );

        if ($wpdb->last_error) {
            if (WP_DEBUG) {
                error_log('Database error in get_members(): ' . $wpdb->last_error);
            }
            return array();
        }

        return $results ?: array();
    }
    

    /**
     * Get single member by ID
     * 
     * @param int $id Member ID
     * @return array|false Member data array or false if not found
     */
    public function get_member($id) {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT * FROM {$this->table_members} WHERE id = %d",
            $id
        );

        $result = $wpdb->get_row($query, ARRAY_A);

        if ($wpdb->last_error) {
            if (WP_DEBUG) {
                error_log('Database error in get_member(): ' . $wpdb->last_error);
            }
            return false;
        }

        return $result ?: false;
    }

    public function create_member($data) {
        global $wpdb;

        $defaults = array(
            'company_name' => '',
            'contact_person' => '',
            'email' => '',
            'phone' => '',
            // New fields with defaults
            'company_leader' => '',
            'leader_position' => '',
            'company_address' => '',
            'postal_code' => '',
            'business_field' => '',
            'ahu_number' => '',
            'city' => '',
            'npwp' => ''
        );

        $data = wp_parse_args($data, $defaults);

        $member_data = array(
            'company_name' => sanitize_text_field($data['company_name']),
            'contact_person' => sanitize_text_field($data['contact_person']),
            'email' => sanitize_email($data['email']),
            'phone' => sanitize_text_field($data['phone']),
            // New fields sanitization
            'company_leader' => sanitize_text_field($data['company_leader']),
            'leader_position' => sanitize_text_field($data['leader_position']),
            'company_address' => sanitize_textarea_field($data['company_address']),
            'postal_code' => sanitize_text_field($data['postal_code']),
            'business_field' => sanitize_text_field($data['business_field']),
            'ahu_number' => sanitize_text_field($data['ahu_number']),
            'city' => sanitize_text_field($data['city']),
            'npwp' => sanitize_text_field($data['npwp'])
        );

        $result = $wpdb->insert(
            $this->table_members,
            $member_data,
            array(
                '%s', '%s', '%s', '%s',  // Existing fields
                '%s', '%s', '%s', '%s',  // New fields part 1
                '%s', '%s', '%s', '%s'   // New fields part 2
            )
        );

        if ($result === false) {
            if (WP_DEBUG) {
                error_log('Failed to create member: ' . $wpdb->last_error);
            }
            return false;
        }

        return $wpdb->insert_id;
    }

    public function update_member($id, $data) {
        global $wpdb;

        $member_data = array(
            // Existing fields
            'company_name' => sanitize_text_field($data['company_name']),
            'contact_person' => sanitize_text_field($data['contact_person']),
            'email' => sanitize_email($data['email']),
            'phone' => sanitize_text_field($data['phone']),
            
            // New fields
            'company_leader' => sanitize_text_field($data['company_leader']),
            'leader_position' => sanitize_text_field($data['leader_position']),
            'company_address' => sanitize_textarea_field($data['company_address']),
            'postal_code' => sanitize_text_field($data['postal_code']),
            'business_field' => sanitize_text_field($data['business_field']),
            'ahu_number' => sanitize_text_field($data['ahu_number']),
            'city' => sanitize_text_field($data['city']),
            'npwp' => sanitize_text_field($data['npwp'])
        );

        $result = $wpdb->update(
            $this->table_members,
            $member_data,
            array('id' => $id),
            array(
                '%s', '%s', '%s', '%s',  // Existing fields format
                '%s', '%s', '%s', '%s',  // New fields format part 1
                '%s', '%s', '%s', '%s'   // New fields format part 2
            ),
            array('%d')
        );

        if ($wpdb->last_error) {
            if (WP_DEBUG) {
                error_log('Update query error: ' . $wpdb->last_error);
                error_log('Last query: ' . $wpdb->last_query);
            }
            return false;
        }

        return $result !== false;
    }

    /**
     * Delete member by ID
     * 
     * @param int $id Member ID to delete
     * @return bool True on success, false on failure
     */
    public function delete_member($id) {
        global $wpdb;

        $result = $wpdb->delete(
            $this->table_members,
            array('id' => $id),
            array('%d')
        );

        if ($wpdb->last_error) {
            if (WP_DEBUG) {
                error_log('Database error in delete_member(): ' . $wpdb->last_error);
            }
            return false;
        }

        return $result !== false;
    }
}
