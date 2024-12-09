<?php
/**
 * AJAX Status Handler Class untuk SKP Tenaga Ahli
 *
 * @package     Asosiasi  
 * @subpackage  Includes/SKP_Tenaga_Ahli
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /asosiasi/includes/skp-tenaga-ahli/class-asosiasi-ajax-status-skp-tenaga-ahli.php
 *
 * Description: Menangani semua AJAX request terkait perubahan status 
 *              dan riwayat status SKP Tenaga Ahli
 *
 * Changelog:
 * 1.0.0 - 2024-11-22
 * - Initial creation
 * - Added status update handling
 * - Added history retrieval
 * - Added security checks
 */

defined('ABSPATH') || exit;

class Asosiasi_Ajax_Status_Skp_Tenaga_Ahli {

   private $nonce_action = 'asosiasi_skp_tenaga_ahli_nonce';
   private $status_handler;

   public function __construct() {
       $this->status_handler = new Asosiasi_Status_Skp_Tenaga_Ahli();
       $this->init_hooks();
   }

   private function init_hooks() {
       add_action('wp_ajax_update_skp_tenaga_ahli_status', array($this, 'update_skp_status'));
       add_action('wp_ajax_get_skp_tenaga_ahli_status_history', array($this, 'get_skp_status_history'));
       
       if (WP_DEBUG) {
           error_log('SKP Tenaga Ahli Status handler initialized');
       }
   }

   /**
    * Verify AJAX request
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
           if (WP_DEBUG) {
               error_log('Attempting SKP tenaga ahli status update');
               error_log('POST data: ' . print_r($_POST, true));
           }

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

           if (WP_DEBUG) {
               error_log('Calling update_status with params:');
               error_log('SKP ID: ' . intval($_POST['skp_id']));
               error_log('New Status: ' . sanitize_text_field($_POST['new_status']));
               error_log('Reason: ' . $reason);
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
           if (WP_DEBUG) {
               error_log('SKP Tenaga Ahli Status update error: ' . $e->getMessage());
           }
           wp_send_json_error(array(
               'message' => $e->getMessage(),
               'code' => 'status_update_error'
           ));
       }
   }

   /**
    * Get SKP status history
    */
   public function get_skp_status_history() {
       try {
           $this->verify_request();

           $skp_id = isset($_GET['skp_id']) ? intval($_GET['skp_id']) : 0;
           if (!$skp_id) {
               throw new Exception(__('ID SKP tidak valid', 'asosiasi'));
           }

           if (WP_DEBUG) {
               error_log('Getting history for tenaga ahli SKP ID: ' . $skp_id);
           }

           $history = $this->status_handler->get_status_history($skp_id);

           if (WP_DEBUG) {
               error_log('Found history records: ' . count($history));
               error_log('History data: ' . print_r($history, true));
           }

           wp_send_json_success(array(
               'history' => array_map(array($this, 'get_formatted_history'), $history)
           ));

       } catch (Exception $e) {
           if (WP_DEBUG) {
               error_log('Get history error: ' . $e->getMessage());
           }
           wp_send_json_error(array(
               'message' => $e->getMessage(),
               'code' => 'get_history_error'
           ));
       }
   }

   /**
    * Format history record for display
    */
   private function get_formatted_history($item) {
       $user_info = get_userdata($item['changed_by']);
       return array(
           'id' => isset($item['id']) ? $item['id'] : 0,
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
