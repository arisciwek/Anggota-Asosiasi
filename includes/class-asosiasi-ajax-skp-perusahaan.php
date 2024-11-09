<?php
/**
 * Handle AJAX operations untuk SKP Perusahaan
 *
 * @package Asosiasi
 * @version 1.2.0
 * Path: includes/class-asosiasi-ajax-perusahaan.php
 * 
 * Changelog:
 * 1.2.0 - Fixed get_skp_perusahaan endpoint
 * 1.1.0 - Added secure PDF handling
 * 1.0.0 - Initial version
 */

defined('ABSPATH') || exit;

class Asosiasi_Ajax_Perusahaan {
    
    public function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        // Existing hooks
        add_action('wp_ajax_add_skp_perusahaan', array($this, 'add_skp_perusahaan'));
        add_action('wp_ajax_update_skp_perusahaan', array($this, 'update_skp_perusahaan'));
        add_action('wp_ajax_delete_skp_perusahaan', array($this, 'delete_skp_perusahaan'));
        add_action('wp_ajax_get_skp_perusahaan_list', array($this, 'get_skp_perusahaan_list'));
        add_action('wp_ajax_get_skp_pdf', array($this, 'get_skp_pdf'));

        // Add hook for get single SKP
        add_action('wp_ajax_get_skp_perusahaan', array($this, 'get_skp_perusahaan'));
    }

    /**
     * Get single SKP for editing
     */
    public function get_skp_perusahaan() {
        check_ajax_referer('asosiasi_skp_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized access', 'asosiasi')));
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if (!$id) {
            wp_send_json_error(array('message' => __('Invalid SKP ID', 'asosiasi')));
        }

        $skp = new Asosiasi_SKP_Perusahaan();
        $data = $skp->get_skp($id);

        if (!$data) {
            wp_send_json_error(array('message' => __('SKP not found', 'asosiasi')));
        }

        // Format dates for form
        $data['tanggal_terbit'] = date('Y-m-d', strtotime($data['tanggal_terbit']));
        $data['masa_berlaku'] = date('Y-m-d', strtotime($data['masa_berlaku']));

        // Add file URL if needed
        if (!empty($data['file_path'])) {
            $data['file_url'] = $this->get_secure_pdf_url($id, $data['file_path']);
        }

        wp_send_json_success(array('skp' => $data));
    }
    
    public function add_skp_perusahaan() {
        check_ajax_referer('asosiasi_skp_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized access', 'asosiasi')));
        }

        $skp = new Asosiasi_SKP_Perusahaan();
        
        // Validate required fields
        $required_fields = array(
            'member_id', 
            'nomor_skp', 
            'penanggung_jawab', 
            'tanggal_terbit', 
            'masa_berlaku'
        );

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(array(
                    'message' => sprintf(__('Field %s is required', 'asosiasi'), $field)
                ));
            }
        }

        // Check for file upload
        if (empty($_FILES['pdf_file'])) {
            wp_send_json_error(array('message' => __('PDF file is required', 'asosiasi')));
        }

        $result = $skp->add_skp($_POST, $_FILES['pdf_file']);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array(
            'message' => __('SKP has been added successfully', 'asosiasi'),
            'skp_list' => $skp->get_member_skp($_POST['member_id'])
        ));
    }

    public function update_skp_perusahaan() {
        check_ajax_referer('asosiasi_skp_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized access', 'asosiasi')));
        }

        $skp = new Asosiasi_SKP_Perusahaan();
        
        // Validate required fields
        $required_fields = array(
            'id',
            'member_id',
            'nomor_skp',
            'penanggung_jawab',
            'tanggal_terbit',
            'masa_berlaku'
        );

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(array(
                    'message' => sprintf(__('Field %s is required', 'asosiasi'), $field)
                ));
            }
        }

        $file = !empty($_FILES['pdf_file']) ? $_FILES['pdf_file'] : null;
        $result = $skp->update_skp($_POST['id'], $_POST, $file);

        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }

        wp_send_json_success(array(
            'message' => __('SKP has been updated successfully', 'asosiasi'),
            'skp_list' => $skp->get_member_skp($_POST['member_id'])
        ));
    }

    public function delete_skp_perusahaan() {
        check_ajax_referer('asosiasi_skp_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized access', 'asosiasi')));
        }

        if (empty($_POST['id']) || empty($_POST['member_id'])) {
            wp_send_json_error(array('message' => __('Invalid request', 'asosiasi')));
        }

        $skp = new Asosiasi_SKP_Perusahaan();
        $result = $skp->delete_skp((int)$_POST['id']);

        if (!$result) {
            wp_send_json_error(array('message' => __('Failed to delete SKP', 'asosiasi')));
        }

        wp_send_json_success(array(
            'message' => __('SKP has been deleted successfully', 'asosiasi'),
            'skp_list' => $skp->get_member_skp((int)$_POST['member_id'])
        ));
    }

    public function get_skp_perusahaan_list() {
        check_ajax_referer('asosiasi_skp_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Unauthorized access', 'asosiasi')));
        }

        if (empty($_GET['member_id'])) {
            wp_send_json_error(array('message' => __('Member ID is required', 'asosiasi')));
        }

        $skp = new Asosiasi_SKP_Perusahaan();
        $skp_list = $skp->get_member_skp((int)$_GET['member_id']);

        // Format dates and add status information
        $formatted_list = array_map(function($item) {
            return array(
                'id' => $item['id'],
                'nomor_skp' => $item['nomor_skp'],
                'penanggung_jawab' => $item['penanggung_jawab'],
                'tanggal_terbit' => date_i18n(get_option('date_format'), strtotime($item['tanggal_terbit'])),
                'masa_berlaku' => date_i18n(get_option('date_format'), strtotime($item['masa_berlaku'])),
                'status' => $item['status'],
                'status_label' => $this->get_status_label($item['status']),
                'file_url' => $this->get_secure_pdf_url($item['id'], $item['file_path']),
                'can_edit' => $item['status'] === 'active'
            );
        }, $skp_list);

        wp_send_json_success(array('skp_list' => $formatted_list));
    }

    /**
     * Generate secure URL for PDF access
     *
     * @param int $id SKP ID
     * @param string $file_path Original file path
     * @return string Secure URL
     */
    private function get_secure_pdf_url($id, $file_path) {
        $nonce = wp_create_nonce('view_skp_pdf_' . $id);
        return add_query_arg(array(
            'action' => 'get_skp_pdf',
            'id' => $id,
            'nonce' => $nonce
        ), admin_url('admin-ajax.php'));
    }

    /**
     * Handle secure PDF file download
     */
    public function get_skp_pdf() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        if (!$id || !check_ajax_referer('view_skp_pdf_' . $id, 'nonce', false)) {
            wp_die(__('Invalid request', 'asosiasi'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access', 'asosiasi'));
        }

        $skp = new Asosiasi_SKP_Perusahaan();
        $skp_data = $skp->get_skp($id);

        if (!$skp_data || empty($skp_data['file_path'])) {
            wp_die(__('File not found', 'asosiasi'));
        }

        $file_path = $skp->get_file_path($skp_data['file_path']);
        
        if (!file_exists($file_path)) {
            wp_die(__('File not found', 'asosiasi'));
        }

        // Send file
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        ob_clean();
        flush();
        readfile($file_path);
        exit;
    }

    private function get_status_label($status) {
        $labels = array(
            'active' => __('Active', 'asosiasi'),
            'expired' => __('Expired', 'asosiasi'),
            'inactive' => __('Inactive', 'asosiasi')
        );

        return isset($labels[$status]) ? $labels[$status] : $status;
    }
}

// Initialize AJAX handler
new Asosiasi_Ajax_Perusahaan();