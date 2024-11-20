<?php
/**
* Handle AJAX operations untuk Status SKP Perusahaan
*
* @package Asosiasi
* @version 1.0.2
* Path: includes/class-asosiasi-ajax-status-skp-perusahaan.php
* 
* Changelog:
* 1.0.2 - 2024-11-19 13:25 WIB
* - Added reason validation
* - Added reason save to history table
* - Improved error handling for status updates
* 
* 1.0.1 - Initial version with basic status handling
*/

defined('ABSPATH') || exit;

class Asosiasi_Ajax_Status_Skp_Perusahaan {

    private $nonce_action = 'asosiasi_skp_perusahaan_nonce';
    private $status_handler;

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
     */
    private function verify_request() {
        $nonce = isset($_REQUEST['nonce']) ? $_REQUEST['nonce'] : '';
        if (!wp_verify_nonce($nonce, $this->nonce_action)) {
            throw new Exception(__('Token keamanan tidak valid', 'asosiasi'));
        }

        if (!current_user_can('manage_options')) {
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
            if (empty($_POST['skp_id'])) {
                throw new Exception(__('ID SKP tidak valid', 'asosiasi'));
            }

            if (empty($_POST['old_status'])) {
                throw new Exception(__('Status awal tidak valid', 'asosiasi'));
            }

            if (empty($_POST['new_status'])) {
                throw new Exception(__('Status baru tidak valid', 'asosiasi'));
            }

            // Validate reason
            $reason = isset($_POST['reason']) ? sanitize_textarea_field($_POST['reason']) : '';
            if (empty($reason)) {
                throw new Exception(__('Alasan perubahan status wajib diisi', 'asosiasi'));
            }
            
            if (strlen($reason) > 1000) {
                throw new Exception(__('Alasan terlalu panjang (maksimal 1000 karakter)', 'asosiasi')); 
            }

            // Update status
            $result = $this->status_handler->update_status(
                intval($_POST['skp_id']),
                sanitize_text_field($_POST['new_status']),
                $reason
            );

            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }

            // Log history
            $history_data = array(
                'skp_id' => intval($_POST['skp_id']),
                'skp_type' => 'perusahaan',
                'old_status' => sanitize_text_field($_POST['old_status']),
                'new_status' => sanitize_text_field($_POST['new_status']),
                'reason' => $reason,
                'changed_by' => get_current_user_id(),
                'changed_at' => current_time('mysql')
            );

            global $wpdb;
            $inserted = $wpdb->insert(
                $wpdb->prefix . 'asosiasi_skp_status_history',
                $history_data,
                array('%d', '%s', '%s', '%s', '%s', '%d', '%s')
            );

            if (!$inserted) {
                throw new Exception(__('Gagal menyimpan riwayat perubahan status', 'asosiasi'));
            }

            wp_send_json_success(array(
                'message' => __('Status SKP berhasil diperbarui', 'asosiasi'),
                'status' => $_POST['new_status'],
                'history' => $this->get_formatted_history($history_data)
            ));

        } catch (Exception $e) {
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

            $history = $this->status_handler->get_status_history($skp_id);

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

    /**
     * Format history data for display
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