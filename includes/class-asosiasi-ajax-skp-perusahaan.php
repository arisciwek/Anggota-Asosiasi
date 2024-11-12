<?php

/**
* Handle AJAX operations untuk SKP Perusahaan
*
* @package Asosiasi
* @version 1.4.1
* Path: includes/class-asosiasi-ajax-skp-perusahaan.php
* 
* Changelog:
* 1.4.1 - 2024-03-15
* - Rollback changes that caused list display issues
* - Removed premature service validations 
* - Fixed SKP list formatting
* 1.4.0 - 2024-03-15
* - Added service handling in AJAX operations
* - Updated response format to include service info
* - Added service validation
* 1.3.0 - 2024-03-12
* - Fixed nonce verification with proper constant
* - Added proper error handling
* - Improved security checks
* - Enhanced response formatting
* - Added validation for file uploads
* 1.2.0 - Fixed get_skp_perusahaan endpoint
* 1.1.0 - Initial enhancement version
*/

defined('ABSPATH') || exit;

class Asosiasi_Ajax_Perusahaan {
   
   private $nonce_action = 'asosiasi_skp_perusahaan_nonce';
   
   public function __construct() {
       $this->init_hooks();
   }

   private function init_hooks() {
       // SKP CRUD operations
       add_action('wp_ajax_add_skp_perusahaan', array($this, 'add_skp_perusahaan'));
       add_action('wp_ajax_update_skp_perusahaan', array($this, 'update_skp_perusahaan'));
       add_action('wp_ajax_delete_skp_perusahaan', array($this, 'delete_skp_perusahaan'));
       add_action('wp_ajax_get_skp_perusahaan_list', array($this, 'get_skp_perusahaan_list'));
       add_action('wp_ajax_get_skp_perusahaan', array($this, 'get_skp_perusahaan'));
       
       // File handling
       add_action('wp_ajax_get_skp_pdf', array($this, 'get_skp_pdf'));
   }

   /**
    * Verify nonce and user capabilities
    */
   private function verify_request($nonce_value) {
       if (!check_ajax_referer($this->nonce_action, 'nonce', false)) {
           wp_send_json_error(array(
               'message' => __('Security check failed', 'asosiasi'),
               'code' => 'invalid_nonce'
           ));
       }

       if (!current_user_can('manage_options')) {
           wp_send_json_error(array(
               'message' => __('Unauthorized access', 'asosiasi'),
               'code' => 'unauthorized'
           ));
       }

       return true;
   }

   /**
    * Get single SKP for editing
    */
   public function get_skp_perusahaan() {
       $this->verify_request($_REQUEST['nonce']);

       $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
       if (!$id) {
           wp_send_json_error(array(
               'message' => __('Invalid SKP ID', 'asosiasi'),
               'code' => 'invalid_id'
           ));
       }

       $skp = new Asosiasi_SKP_Perusahaan();
       $data = $skp->get_skp($id);

       if (!$data) {
           wp_send_json_error(array(
               'message' => __('SKP tidak ditemukan', 'asosiasi'),
               'code' => 'not_found'
           ));
       }

       // Format dates for form
       $data['tanggal_terbit'] = date('Y-m-d', strtotime($data['tanggal_terbit']));
       $data['masa_berlaku'] = date('Y-m-d', strtotime($data['masa_berlaku']));

       // Add file URL if needed
       if (!empty($data['file_path'])) {
           $data['file_url'] = $this->get_secure_pdf_url($id, $data['file_path']);
           $data['file_name'] = basename($data['file_path']);
       }

       wp_send_json_success(array('skp' => $data));
   }
   
   /**
    * Add new SKP
    */
   public function add_skp_perusahaan() {
       $this->verify_request($_POST['nonce']);

       // Validate required fields
       $required_fields = array(
           'member_id' => __('ID Anggota', 'asosiasi'),
           'nomor_skp' => __('Nomor SKP', 'asosiasi'),
           'penanggung_jawab' => __('Penanggung Jawab', 'asosiasi'),
           'tanggal_terbit' => __('Tanggal Terbit', 'asosiasi'),
           'masa_berlaku' => __('Masa Berlaku', 'asosiasi')
       );

       foreach ($required_fields as $field => $label) {
           if (empty($_POST[$field])) {
               wp_send_json_error(array(
                   'message' => sprintf(__('Field %s wajib diisi', 'asosiasi'), $label),
                   'field' => $field
               ));
           }
       }

       // Validate file upload
       if (empty($_FILES['pdf_file'])) {
           wp_send_json_error(array(
               'message' => __('File PDF wajib diunggah', 'asosiasi'),
               'field' => 'pdf_file'
           ));
       }

       $skp = new Asosiasi_SKP_Perusahaan();
       $result = $skp->add_skp($_POST, $_FILES['pdf_file']);

       if (is_wp_error($result)) {
           wp_send_json_error(array(
               'message' => $result->get_error_message(),
               'code' => $result->get_error_code()
           ));
       }

       wp_send_json_success(array(
           'message' => __('SKP berhasil ditambahkan', 'asosiasi'),
           'skp_id' => $result,
           'skp_list' => $this->format_skp_list($skp->get_member_skp($_POST['member_id']))
       ));
   }

   /**
    * Update existing SKP
    */
   public function update_skp_perusahaan() {
       $this->verify_request($_POST['nonce']);
       
       if (empty($_POST['id'])) {
           wp_send_json_error(array(
               'message' => __('ID SKP tidak valid', 'asosiasi'),
               'code' => 'invalid_id'
           ));
       }

       $skp = new Asosiasi_SKP_Perusahaan();
       $file = !empty($_FILES['pdf_file']) ? $_FILES['pdf_file'] : null;
       $result = $skp->update_skp($_POST['id'], $_POST, $file);

       if (is_wp_error($result)) {
           wp_send_json_error(array(
               'message' => $result->get_error_message(),
               'code' => $result->get_error_code()
           ));
       }

       wp_send_json_success(array(
           'message' => __('SKP berhasil diperbarui', 'asosiasi'),
           'skp_list' => $this->format_skp_list($skp->get_member_skp($_POST['member_id']))
       ));
   }

   /**
    * Delete SKP
    */
   public function delete_skp_perusahaan() {
       $this->verify_request($_POST['nonce']);

       if (empty($_POST['id']) || empty($_POST['member_id'])) {
           wp_send_json_error(array(
               'message' => __('Parameter tidak valid', 'asosiasi'),
               'code' => 'invalid_params'
           ));
       }

       $skp = new Asosiasi_SKP_Perusahaan();
       $result = $skp->delete_skp((int)$_POST['id']);

       if (!$result) {
           wp_send_json_error(array(
               'message' => __('Gagal menghapus SKP', 'asosiasi'),
               'code' => 'delete_failed'
           ));
       }

       wp_send_json_success(array(
           'message' => __('SKP berhasil dihapus', 'asosiasi'),
           'skp_list' => $this->format_skp_list($skp->get_member_skp((int)$_POST['member_id']))
       ));
   }

   /**
    * Get SKP list
    */
   public function get_skp_perusahaan_list() {
       $this->verify_request($_GET['nonce']);

       if (empty($_GET['member_id'])) {
           wp_send_json_error(array(
               'message' => __('ID Anggota wajib diisi', 'asosiasi'),
               'code' => 'missing_member_id'
           ));
       }

       $skp = new Asosiasi_SKP_Perusahaan();
       $skp_list = $skp->get_member_skp((int)$_GET['member_id']);

       wp_send_json_success(array(
           'skp_list' => $this->format_skp_list($skp_list)
       ));
   }

   /**
    * Format SKP list for display
    */
   private function format_skp_list($list) {
       return array_map(function($item) {
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
       }, $list);
   }

   /**
    * Generate secure URL for PDF access
    */
   private function get_secure_pdf_url($id, $file_path) {
       return add_query_arg(array(
           'action' => 'get_skp_pdf',
           'id' => $id,
           'nonce' => wp_create_nonce('view_skp_pdf_' . $id)
       ), admin_url('admin-ajax.php'));
   }

   /**
    * Handle secure PDF file download
    */
   public function get_skp_pdf() {
       $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
       
       if (!$id || !check_ajax_referer('view_skp_pdf_' . $id, 'nonce', false)) {
           wp_die(__('Permintaan tidak valid', 'asosiasi'));
       }

       if (!current_user_can('manage_options')) {
           wp_die(__('Akses tidak diizinkan', 'asosiasi'));
       }

       $skp = new Asosiasi_SKP_Perusahaan();
       $skp_data = $skp->get_skp($id);

       if (!$skp_data || empty($skp_data['file_path'])) {
           wp_die(__('File tidak ditemukan', 'asosiasi'));
       }

       $file_path = $skp->get_file_path($skp_data['file_path']);
       
       if (!file_exists($file_path)) {
           wp_die(__('File tidak ditemukan', 'asosiasi'));
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

   /**
    * Get status label
    */
   private function get_status_label($status) {
       $labels = array(
           'active' => __('Aktif', 'asosiasi'),
           'expired' => __('Kadaluarsa', 'asosiasi'),
           'inactive' => __('Tidak Aktif', 'asosiasi')
       );

       return isset($labels[$status]) ? $labels[$status] : $status;
   }
}