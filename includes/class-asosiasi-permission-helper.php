<?php
/**
 * Permission Helper Class
 *
 * @package     Asosiasi
 * @subpackage  Includes/Helpers
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: includes/class-asosiasi-permission-helper.php
 */

if (!defined('ABSPATH')) {
    exit;
}

class Asosiasi_Permission_Helper {

    /**
     * Cek apakah user adalah administrator
     * 
     * @return bool True jika user adalah administrator
     */
    public static function is_administrator() {
        return current_user_can('administrator');
    }

    /**
     * Cek apakah user bisa mengedit member
     * 
     * @param int|array $member Member ID atau array data member
     * @return bool True jika user bisa mengedit, false jika tidak
     */
    public static function can_edit_member($member) {
        //error_log('=== START Permission Check can_edit_member ===');
        
        // Administrator bypass semua permission check
        if (self::is_administrator()) {
            //error_log('User is administrator - Access GRANTED');
            return true;
        }

        $current_user = wp_get_current_user();
        if (!$current_user) {
            error_log('No current user found - Access DENIED');
            return false;
        }
        //error_log('Current User ID: ' . $current_user->ID);

        // Jika user punya capability penuh
        if (current_user_can('edit_asosiasi_members')) {
            //error_log('User has full edit_asosiasi_members capability - Access GRANTED');
            return true;
        }

        // Get member data if ID provided
        if (is_numeric($member)) {
            //error_log('Getting member data for ID: ' . $member);
            $crud = new Asosiasi_CRUD();
            $member = $crud->get_member($member);
            if (!$member) {
                //error_log('Member not found - Access DENIED');
                return false;
            }
            //error_log('Member data found: ' . print_r($member, true));  // Log semua data member
        }

        // Cek apakah user adalah creator dari member
        $has_own_capability = current_user_can('edit_own_asosiasi_members');
        //error_log('Has edit_own_asosiasi_members capability: ' . ($has_own_capability ? 'YES' : 'NO'));
        
        $creator_id = isset($member['created_by']) ? $member['created_by'] : null;
        //error_log('Creator ID from member data: ' . var_export($creator_id, true));
        
        $is_creator = $creator_id !== null && $creator_id == $current_user->ID;
        //error_log('Is creator of the member: ' . ($is_creator ? 'YES' : 'NO'));

        if ($has_own_capability && $is_creator) {
            //error_log('User can edit own members and is creator - Access GRANTED');
            return true;
        }

        //error_log('No matching permissions found - Access DENIED');
        //error_log('=== END Permission Check can_edit_member ===');
        return false;
    }
    /**
     * Cek apakah user bisa melihat member
     * 
     * @param int|array $member Member ID atau array data member
     * @return bool True jika user bisa melihat, false jika tidak
     */
    public static function can_view_member($member) {
        // Administrator bypass semua permission check
        if (self::is_administrator()) {
            return true;
        }

        if (current_user_can('view_asosiasi_members')) {
            return true;
        }

        // Get member data if ID provided
        if (is_numeric($member)) {
            $crud = new Asosiasi_CRUD();
            $member = $crud->get_member($member);
            if (!$member) {
                return false;
            }
        }

        $current_user = wp_get_current_user();
        if (current_user_can('view_own_asosiasi_members') && 
            isset($member['created_by']) && 
            $member['created_by'] == $current_user->ID) {
            return true;
        }

        return false;
    }

    /**
     * Cek apakah user bisa menghapus member
     * 
     * @param int|array $member Member ID atau array data member
     * @return bool True jika user bisa menghapus, false jika tidak
     */
    public static function can_delete_member($member) {
        // Administrator bypass semua permission check
        if (self::is_administrator()) {
            return true;
        }

        if (current_user_can('delete_asosiasi_members')) {
            return true;
        }

        // Get member data if ID provided
        if (is_numeric($member)) {
            $crud = new Asosiasi_CRUD();
            $member = $crud->get_member($member);
            if (!$member) {
                return false;
            }
        }

        $current_user = wp_get_current_user();
        if (current_user_can('delete_own_asosiasi_members') && 
            isset($member['created_by']) && 
            $member['created_by'] == $current_user->ID) {
            return true;
        }

        return false;
    }

    /**
     * Cek apakah user bisa mengelola pengaturan
     * 
     * @return bool True jika user bisa mengelola pengaturan
     */
    public static function can_manage_settings() {
        return self::is_administrator() || current_user_can('manage_options');
    }

    /**
     * Cek apakah user bisa mengakses SKP
     * 
     * @param int|array $member Member ID atau array data member
     * @return bool True jika user bisa mengakses SKP
     */
    public static function can_manage_skp($member) {
        // Administrator bypass semua permission check
        if (self::is_administrator()) {
            return true;
        }

        if (current_user_can('manage_skp_status')) {
            return true;
        }

        // Cek jika user bisa mengedit member ini
        return self::can_edit_member($member);
    }

}
