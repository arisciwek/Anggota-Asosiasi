<?php
/**
* Handle AJAX operations untuk SKP Perusahaan
*
* @package Asosiasi
* @version 1.4.4
* Path: includes/class-asosiasi-ajax-skp-perusahaan.php
* 
* Changelog:
* 1.4.4 - 2024-03-17
* - Fixed HTTP 500 error on get_skp_perusahaan_list
* - Added proper error handling and logging
* - Added complete method implementations
* - Enhanced security checks
* - Added proper method documentation
* 
* 1.4.3 - 2024-03-16
* - Fixed nonce verification issue 
* - Added proper nonce existence checking
* - Updated nonce field name to match JavaScript implementation
* - Added fallback for direct nonce field access
*/

defined('ABSPATH') || exit;

class Asosiasi_Ajax_Perusahaan {
   
   private $nonce_action = 'asosiasi_skp_perusahaan_nonce';
   private $log_enabled = false;
   
   public function __construct() {
       $this->log_enabled = defined('WP_DEBUG') && WP_DEBUG;
       $this->init_hooks();
   }

   /**
    * Initialize WordPress hooks
    *
    * @return void
    */
   private function init_hooks() {
       add_action('wp_ajax_add_skp_perusahaan', array($this, 'add_skp_perusahaan'));
       add_action('wp_ajax_update_skp_perusahaan', array($this, 'update_skp_perusahaan'));
       add_action('wp_ajax_delete_skp_perusahaan', array($this, 'delete_skp_perusahaan'));
       add_action('wp_ajax_get_skp_perusahaan_list', array($this, 'get_skp_perusahaan_list'));
       add_action('wp_ajax_get_skp_perusahaan', array($this, 'get_skp_perusahaan'));
       add_action('wp_ajax_get_skp_pdf', array($this, 'get_skp_pdf'));
   }

   /**
    * Get SKP list
    *
    * @return void
    */
   public function get_skp_perusahaan_list() {
       try {
           if (empty($_GET['member_id'])) {
               throw new Exception(__('ID Anggota wajib diisi', 'asosiasi'));
           }

           $member_id = (int) $_GET['member_id'];
           
           // Basic capability check
           if (!current_user_can('manage_options')) {
               throw new Exception(__('Anda tidak memiliki izin untuk melihat data ini', 'asosiasi'));
           }

           $skp = new Asosiasi_SKP_Perusahaan();
           $skp_list = $skp->get_member_skp($member_id);

           if (!is_array($skp_list)) {
               throw new Exception(__('Gagal mengambil data SKP', 'asosiasi'));
           }

           wp_send_json_success(array(
               'skp_list' => $this->format_skp_list($skp_list)
           ));

       } catch (Exception $e) {
           $this->handle_error($e->getMessage(), 'get_list_error');
       }
   }

   /**
    * Get single SKP
    *
    * @return void
    */
   public function get_skp_perusahaan() {
       try {
           $this->verify_request();

           $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
           if (!$id) {
               throw new Exception(__('Invalid SKP ID', 'asosiasi'));
           }

           $skp = new Asosiasi_SKP_Perusahaan();
           $data = $skp->get_skp($id);

           if (!$data) {
               throw new Exception(__('SKP tidak ditemukan', 'asosiasi'));
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

       } catch (Exception $e) {
           $this->handle_error($e->getMessage(), 'get_skp_error');
       }
   }

   /**
    * Add new SKP
    *
    * @return void
    */
   public function add_skp_perusahaan() {
       try {
           $this->verify_request();

           $required_fields = array(
               'member_id' => __('ID Anggota', 'asosiasi'),
               'service_id' => __('Layanan', 'asosiasi'),
               'nomor_skp' => __('Nomor SKP', 'asosiasi'),
               'penanggung_jawab' => __('Penanggung Jawab', 'asosiasi'),
               'tanggal_terbit' => __('Tanggal Terbit', 'asosiasi'),
               'masa_berlaku' => __('Masa Berlaku', 'asosiasi')
           );

           foreach ($required_fields as $field => $label) {
               if (empty($_POST[$field])) {
                   throw new Exception(sprintf(__('Field %s wajib diisi', 'asosiasi'), $label));
               }
           }

           if (empty($_FILES['pdf_file'])) {
               throw new Exception(__('File PDF wajib diunggah', 'asosiasi'));
           }

           $skp = new Asosiasi_SKP_Perusahaan();
           $result = $skp->add_skp($_POST, $_FILES['pdf_file']);

           if (is_wp_error($result)) {
               throw new Exception($result->get_error_message());
           }

           wp_send_json_success(array(
               'message' => __('SKP berhasil ditambahkan', 'asosiasi'),
               'skp_id' => $result,
               'skp_list' => $this->format_skp_list($skp->get_member_skp($_POST['member_id']))
           ));

       } catch (Exception $e) {
           $this->handle_error($e->getMessage(), 'add_skp_error');
       }
   }

   /**
    * Update existing SKP
    *
    * @return void
    */
   public function update_skp_perusahaan() {
       try {
           $this->verify_request();

           if (empty($_POST['id'])) {
               throw new Exception(__('ID SKP tidak valid', 'asosiasi'));
           }

           $skp = new Asosiasi_SKP_Perusahaan();
           $file = !empty($_FILES['pdf_file']) ? $_FILES['pdf_file'] : null;
           $result = $skp->update_skp($_POST['id'], $_POST, $file);

           if (is_wp_error($result)) {
               throw new Exception($result->get_error_message());
           }

           wp_send_json_success(array(
               'message' => __('SKP berhasil diperbarui', 'asosiasi'),
               'skp_list' => $this->format_skp_list($skp->get_member_skp($_POST['member_id']))
           ));

       } catch (Exception $e) {
           $this->handle_error($e->getMessage(), 'update_skp_error');
       }
   }

   /**
    * Delete SKP
    *
    * @return void
    */
   public function delete_skp_perusahaan() {
       try {
           $this->verify_request();

           if (empty($_POST['id']) || empty($_POST['member_id'])) {
               throw new Exception(__('Parameter tidak valid', 'asosiasi'));
           }

           $skp = new Asosiasi_SKP_Perusahaan();
           $result = $skp->delete_skp((int) $_POST['id']);

           if (!$result) {
               throw new Exception(__('Gagal menghapus SKP', 'asosiasi'));
           }

           wp_send_json_success(array(
               'message' => __('SKP berhasil dihapus', 'asosiasi'),
               'skp_list' => $this->format_skp_list($skp->get_member_skp((int) $_POST['member_id']))
           ));

       } catch (Exception $e) {
           $this->handle_error($e->getMessage(), 'delete_skp_error');
       }
   }

   /**
    * Handle secure PDF file download
    *
    * @return void
    */
   public function get_skp_pdf() {
       try {
           $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
           
           if (!$id || !check_ajax_referer('view_skp_pdf_' . $id, 'nonce', false)) {
               throw new Exception(__('Permintaan tidak valid', 'asosiasi'));
           }

           if (!current_user_can('manage_options')) {
               throw new Exception(__('Akses tidak diizinkan', 'asosiasi'));
           }

           $skp = new Asosiasi_SKP_Perusahaan();
           $skp_data = $skp->get_skp($id);

           if (!$skp_data || empty($skp_data['file_path'])) {
               throw new Exception(__('File tidak ditemukan', 'asosiasi'));
           }

           $file_path = $skp->get_file_path($skp_data['file_path']);
           
           if (!file_exists($file_path)) {
               throw new Exception(__('File tidak ditemukan di server', 'asosiasi'));
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

       } catch (Exception $e) {
           wp_die($e->getMessage());
       }
   }

   /**
    * Verify AJAX request validity
    *
    * @throws Exception If request is invalid
    * @return void
    */
   private function verify_request() {
       if (!current_user_can('manage_options')) {
           throw new Exception(__('Anda tidak memiliki izin untuk melakukan operasi ini', 'asosiasi'));
       }

       // Get nonce from various possible sources
       $nonce = '';
       if (isset($_REQUEST['nonce'])) {
           $nonce = sanitize_text_field($_REQUEST['nonce']);
       } elseif (isset($_REQUEST['skp_nonce'])) {
           $nonce = sanitize_text_field($_REQUEST['skp_nonce']);
       } elseif (isset($_REQUEST['_wpnonce'])) {
           $nonce = sanitize_text_field($_REQUEST['_wpnonce']);
       }

       if (empty($nonce) || !wp_verify_nonce($nonce, $this->nonce_action)) {
           throw new Exception(__('Token keamanan tidak valid', 'asosiasi'));
       }
   }

   /**
    * Format SKP list for display
    *
    * @param array $list Raw SKP list
    * @return array Formatted SKP list
    */
   private function format_skp_list($list) {
       if (!is_array($list)) {
           return array();
       }

       return array_map(function($item) {
           return array(
               'id' => $item['id'],
               'service_id' => $item['service_id'],
               'service_short_name' => isset($item['service_short_name']) ? $item['service_short_name'] : '',
               'service_full_name' => isset($item['service_full_name']) ? $item['service_full_name'] : '',
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
    *
    * @param int $id SKP ID
    * @param string $file_path File path
    * @return string Secure URL
    */
   private function get_secure_pdf_url($id, $file_path) {
       return add_query_arg(array(
           'action' => 'get_skp_pdf',
           'id' => $id,
           'nonce' => wp_create_nonce('view_skp_pdf_' . $id)
       ), admin_url('admin-ajax.php'));
   }

   /**
    * Get status label
    *
    * @param string $status Status code
    * @return string Localized status label
    */
   private function get_status_label($status) {
       $labels = array(
           'active' => __('Aktif', 'asosiasi'),
           'expired' => __('Kadaluarsa', 'asosiasi'),
           'inactive' => __('Tidak Aktif', 'asosiasi')
       );

       return isset($labels[$status]) ? $labels[$status] : $status;
   }

   /**
    * Handle errors consistently
    *
    * @param string $message Error message
    * @param string $code Error code
    * @param array $context Additional context
    * @return void
    */
   private function handle_error($message, $code = '', $context = array()) {
       if ($this->log_enabled) {
           error_log(sprintf(
               '[Asosiasi SKP Error] %s | Code: %s | Context: %s',
               $message,
               $code,
               json_encode($context)
           ));
       }

       wp_send_json_error(array(
           'message' => $message,
           'code' => $code
       ));
   }
}
