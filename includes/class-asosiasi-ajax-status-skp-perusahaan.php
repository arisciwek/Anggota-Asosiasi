<?php
/**
* Handle AJAX operations untuk Status SKP Perusahaan
*
* @package Asosiasi
* @version 1.0.4 
* Path: includes/class-asosiasi-ajax-status-skp-perusahaan.php
* 
* Changelog:
* 1.0.4 - 2024-11-19 14:55 WIB
* - Changed verify_request() visibility to protected
* - Modified get_skp_status_history for member_id support
* - Added nomor_skp to history data
* - Fixed private method visibility error
* 
* 1.0.3 - Added reason validation 
* 1.0.2 - Improved error handling
* 1.0.1 - Initial version with basic status handling
*/

defined('ABSPATH') || exit;

class Asosiasi_Ajax_Status_Skp_Perusahaan {

   private static $instance = null;
   private $nonce_action = 'asosiasi_skp_perusahaan_nonce';
   private $status_handler;

    public static function get_instance() {
        if (null === self::$instance) {
            if (WP_DEBUG && !defined('DOING_AJAX')) {
                error_log('SKP Perusahaan Status handler initialized');
            }
            self::$instance = new self();
        }
        return self::$instance;
    }

   public function __construct() {
       $this->status_handler = new Asosiasi_Status_Skp_Perusahaan();
       $this->init_hooks();
   }

   private function init_hooks() {
       add_action('wp_ajax_update_skp_status', array($this, 'update_skp_status'));
       add_action('wp_ajax_get_skp_status_history', array($this, 'get_skp_status_history'));
   }

   /**
    * Verify AJAX request
    * Changed to protected for inheritance access
    */
   protected function verify_request() {
       $nonce = '';
       if (isset($_REQUEST['nonce'])) {
           $nonce = $_REQUEST['nonce'];
       } elseif (isset($_REQUEST['status_nonce'])) {
           $nonce = $_REQUEST['status_nonce'];
       }

       if (empty($nonce)) {
           throw new Exception(__('Token keamanan tidak ditemukan', 'asosiasi'));
       }

       if (!wp_verify_nonce($nonce, $this->nonce_action)) {
           throw new Exception(__('Token keamanan tidak valid', 'asosiasi'));
       }

       if (!current_user_can('manage_options') && !current_user_can('manage_skp_status')) {
           throw new Exception(__('Anda tidak memiliki izin untuk operasi ini', 'asosiasi'));
       }

       return true;
   }

   /**
    * Handle SKP status update
    */
   public function update_skp_status() {
       try {

           $this->verify_request();

           // Validate required fields
           $required = array('skp_id', 'old_status', 'new_status', 'reason');
           foreach ($required as $field) {
               if (empty($_POST[$field])) {
                   throw new Exception(
                       sprintf(__('Field %s wajib diisi', 'asosiasi'), $field)
                   );
               }
           }

           // Validate reason
           $reason = isset($_POST['reason']) ? sanitize_textarea_field($_POST['reason']) : '';
           if (empty($reason)) {
               throw new Exception(__('Alasan perubahan status wajib diisi', 'asosiasi'));
           }
           
           if (strlen($reason) > 1000) {
               throw new Exception(__('Alasan terlalu panjang (maksimal 1000 karakter)', 'asosiasi')); 
           }

           $result = $this->status_handler->update_status(
               intval($_POST['skp_id']),
               sanitize_text_field($_POST['new_status']),
               $reason
           );

           if (is_wp_error($result)) {
               throw new Exception($result->get_error_message());
           }

           wp_send_json_success(array(
               'message' => __('Status SKP berhasil diperbarui', 'asosiasi'),
               'status' => $_POST['new_status']
           ));

       } catch (Exception $e) {
           wp_send_json_error(array(
               'message' => $e->getMessage(),
               'code' => 'status_update_error'
           ));
       }
   }

   /**
    * Get SKP status history by member ID
    */
   public function get_skp_status_history() {
       try {
           $this->verify_request();

           $member_id = isset($_GET['member_id']) ? intval($_GET['member_id']) : 0;
           if (!$member_id) {
               throw new Exception(__('ID Member tidak valid', 'asosiasi'));
           }

           global $wpdb;
           $history = $wpdb->get_results($wpdb->prepare(
               "SELECT h.*, s.nomor_skp 
                FROM {$wpdb->prefix}asosiasi_skp_status_history h
                JOIN {$wpdb->prefix}asosiasi_skp_perusahaan s ON h.skp_id = s.id
                JOIN {$wpdb->prefix}asosiasi_members m ON s.member_id = m.id
                WHERE m.id = %d
                ORDER BY h.changed_at DESC",
               $member_id
           ), ARRAY_A);

           wp_send_json_success(array(
               'history' => array_map(array($this, 'get_formatted_history'), $history)
           ));

       } catch (Exception $e) {

           wp_send_json_error(array(
               'message' => $e->getMessage(),
               'code' => 'get_history_error'
           ));
       }
   }

   private function get_formatted_history($item) {
       $user_info = get_userdata($item['changed_by']);
       return array(
           'id' => isset($item['id']) ? $item['id'] : 0,
           'nomor_skp' => $item['nomor_skp'],
           'old_status' => $this->status_handler->get_status_label($item['old_status']),
           'new_status' => $this->status_handler->get_status_label($item['new_status']),
           'reason' => $item['reason'],
           'changed_by' => $user_info ? $user_info->display_name : __('User tidak ditemukan', 'asosiasi'),
           'changed_at' => mysql2date(
               get_option('date_format') . ' ' . get_option('time_format'), 
               $item['changed_at']
           )
       );
   }
}
