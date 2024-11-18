<?php
/**
* Handle AJAX operations untuk Status SKP Perusahaan
*
* @package Asosiasi
* @version 1.0.1
* Path: includes/class-asosiasi-ajax-status-skp-perusahaan.php
* 
* Changelog:
* 1.0.1 - 2024-11-17
* - Updated to use Asosiasi_Status_Skp_Perusahaan class
* - Improved error handling
* - Added better validation
* 
* 1.0.0 - Initial version
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
        
        if (WP_DEBUG) {
            error_log('SKP Status handler initialized');
        }
    }

    /**
     * Verify AJAX request
     * 
     * @return bool|WP_Error
     */

    private function verify_request() {
        // Check nonce from various possible sources
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
                error_log('Attempting SKP status update');
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

            if (WP_DEBUG) {
                error_log('Creating SKP Status handler');
            }

            $status_handler = new Asosiasi_Status_Skp_Perusahaan();
            
            if (WP_DEBUG) {
                error_log('Calling update_status with params:');
                error_log('SKP ID: ' . intval($_POST['skp_id']));
                error_log('New Status: ' . sanitize_text_field($_POST['new_status']));
                error_log('Reason: ' . sanitize_textarea_field($_POST['reason']));
            }

            $result = $status_handler->update_status(
                intval($_POST['skp_id']),
                sanitize_text_field($_POST['new_status']),
                sanitize_textarea_field($_POST['reason'])
            );

            if (WP_DEBUG) {
                error_log('Update result: ' . print_r($result, true));
            }

            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }

            wp_send_json_success(array(
                'message' => __('Status SKP berhasil diperbarui', 'asosiasi'),
                'status' => $_POST['new_status']
            ));

        } catch (Exception $e) {
            if (WP_DEBUG) {
                error_log('SKP Status update error: ' . $e->getMessage());
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

            $history = $this->status_handler->get_status_history($skp_id);

            // Format history data for display
            $formatted_history = array_map(function($item) {
                return array(
                    'id' => $item['id'],
                    'old_status' => $this->status_handler->get_status_label($item['old_status']),
                    'new_status' => $this->status_handler->get_status_label($item['new_status']),
                    'reason' => $item['reason'],
                    'changed_by' => $item['changed_by_name'],
                    'changed_at' => mysql2date(
                        get_option('date_format') . ' ' . get_option('time_format'), 
                        $item['changed_at']
                    )
                );
            }, $history);

            wp_send_json_success(array(
                'history' => $formatted_history
            ));

        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage(),
                'code' => 'get_history_error'
            ));
        }
    }
}
