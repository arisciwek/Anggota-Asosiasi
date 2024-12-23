<?php
/**
 * AJAX Handler Class untuk SKP Tenaga Ahli
 *
 * @package     Asosiasi
 * @subpackage  Includes/SKP_Tenaga_Ahli
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: /asosiasi/includes/skp-tenaga-ahli/class-asosiasi-ajax-skp-tenaga-ahli.php
 *
 * Description: Menangani semua AJAX requests untuk operasi SKP Tenaga Ahli
 *              termasuk CRUD operations dan file handling
 *
 * Changelog:
 * 1.0.0 - 2024-11-22
 * - Initial creation
 * - Added CRUD operation handlers
 * - Added file upload handling
 * - Added security checks and validation
 */

defined('ABSPATH') || exit;

class Asosiasi_Ajax_Skp_Tenaga_Ahli {
   
    private $nonce_action = 'asosiasi_skp_tenaga_ahli_nonce';
   
    public function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        // SKP CRUD operations
        add_action('wp_ajax_add_skp_tenaga_ahli', array($this, 'add_skp_tenaga_ahli'));
        add_action('wp_ajax_update_skp_tenaga_ahli', array($this, 'update_skp_tenaga_ahli'));
        add_action('wp_ajax_delete_skp_tenaga_ahli', array($this, 'delete_skp_tenaga_ahli'));
        add_action('wp_ajax_get_skp_tenaga_ahli_list', array($this, 'get_skp_tenaga_ahli_list'));
        add_action('wp_ajax_get_skp_tenaga_ahli', array($this, 'get_skp_tenaga_ahli'));
        add_action('wp_ajax_get_skp_pdf', array($this, 'get_skp_pdf'));
    }
    
    private function verify_request() {
        // Check nonce dari parameter yang konsisten
        $nonce = '';
        if (isset($_REQUEST['skp_tenaga_ahli_nonce'])) {
            $nonce = $_REQUEST['skp_tenaga_ahli_nonce'];
        } elseif (isset($_REQUEST['nonce'])) {
            $nonce = $_REQUEST['nonce'];
        }

        if (empty($nonce)) {
            error_log('Missing nonce in request');
            wp_send_json_error(array(
                'message' => __('Token keamanan tidak ditemukan', 'asosiasi'),
                'code' => 'missing_nonce'
            ));
        }

        if (!current_user_can('add_asosiasi_members')) {
            wp_send_json_error(array(
                'message' => __('Anda tidak memiliki izin untuk melakukan operasi ini', 'asosiasi'),
                'code' => 'insufficient_permissions'
            ));
        }

        return true;
    }

    /**
     * Get SKP list
     */
    public function get_skp_tenaga_ahli_list() {
        try {
            $this->verify_request();

            if (empty($_GET['member_id'])) {
                throw new Exception(__('ID Anggota wajib diisi', 'asosiasi'));
            }

            // Get status filter from request
            $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'active';
            
            $skp = new Asosiasi_SKP_Tenaga_Ahli();
            $skp_list = $skp->get_member_skp((int)$_GET['member_id']);

            // Filter berdasarkan status
            $filtered_list = array_filter($skp_list, function($item) use ($status_filter) {
                if ($status_filter === 'active') {
                    // Tampilkan status active dan activated di tab aktif
                    return in_array($item['status'], ['active', 'activated']);
                } else {
                    // Untuk tab tidak aktif, tampilkan yang expired dan inactive
                    return in_array($item['status'], ['expired', 'inactive']);
                }
            });

            // Reset array keys after filtering
            $filtered_list = array_values($filtered_list);

            wp_send_json_success(array(
                'skp_list' => $this->format_skp_list($filtered_list)
            ));

        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage(),
                'code' => 'get_list_error'
            ));
        }
    }

    /**
     * Get single SKP
     */
    public function get_skp_tenaga_ahli() {
        try {
            $this->verify_request();

            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if (!$id) {
                throw new Exception(__('Invalid SKP ID', 'asosiasi'));
            }

            $skp = new Asosiasi_SKP_Tenaga_Ahli();
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
            wp_send_json_error(array(
                'message' => $e->getMessage(),
                'code' => 'get_skp_error'
            ));
        }
    }

    /**
     * Add new SKP
     */
    public function add_skp_tenaga_ahli() {
        try {
                    
            $this->verify_request();

            // Validate file upload first
            if (empty($_FILES['pdf_file'])) {
                wp_send_json_error(array(
                    'message' => __('File PDF wajib diunggah', 'asosiasi'),
                    'field' => 'pdf_file'
                ));
            }

            // Validate required fields
            $required_fields = array(
                'member_id' => __('ID Anggota', 'asosiasi'),
                'nomor_skp' => __('Nomor SKP', 'asosiasi'),
                'nama_tenaga_ahli' => __('Nama Tenaga Ahli', 'asosiasi'),
                'penanggung_jawab' => __('Penanggung jawab', 'asosiasi'),
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

            $skp = new Asosiasi_SKP_Tenaga_Ahli();
            $result = $skp->add_skp($_POST, $_FILES['pdf_file']);

            if (is_wp_error($result)) {
                wp_send_json_error(array(
                    'message' => $result->get_error_message(),
                    'code' => $result->get_error_code()
                ));
            }

            wp_send_json_success(array(
                'message' => __('SKP berhasil ditambahkan', 'asosiasi'),
                'skp_id' => $result
            ));
        } catch (Exception $e) {
            error_log('Error in add_skp_tenaga_ahli: ' . $e->getMessage());
            wp_send_json_error(array(
                'message' => $e->getMessage(),
                'code' => 'add_error'
            ));
        }
    }

    /**
     * Update SKP
     */
    public function update_skp_tenaga_ahli() {
        $this->verify_request();
        
        if (empty($_POST['id'])) {
            wp_send_json_error(array(
                'message' => __('ID SKP tidak valid', 'asosiasi'),
                'code' => 'invalid_id'
            ));
        }

        $skp = new Asosiasi_SKP_Tenaga_Ahli();
        $file = !empty($_FILES['pdf_file']) ? $_FILES['pdf_file'] : null;
        $result = $skp->update_skp($_POST['id'], $_POST, $file);

        if (is_wp_error($result)) {
            wp_send_json_error(array(
                'message' => $result->get_error_message(),
                'code' => $result->get_error_code()
            ));
        }

        wp_send_json_success(array(
            'message' => __('SKP berhasil diperbarui', 'asosiasi')
        ));
    }

    /**
     * Delete SKP
     */
    public function delete_skp_tenaga_ahli() {
        $this->verify_request();

        if (empty($_POST['id'])) {
            wp_send_json_error(array(
                'message' => __('Parameter tidak valid', 'asosiasi'),
                'code' => 'invalid_params'
            ));
        }

        $skp = new Asosiasi_SKP_Tenaga_Ahli();
        $result = $skp->delete_skp((int)$_POST['id']);

        if (!$result) {
            wp_send_json_error(array(
                'message' => __('Gagal menghapus SKP', 'asosiasi'),
                'code' => 'delete_failed'
            ));
        }

        wp_send_json_success(array(
            'message' => __('SKP berhasil dihapus', 'asosiasi')
        ));
    }

    /**
     * Format SKP list for display
     */
    private function format_skp_list($list) {
        if (!is_array($list)) {
            return array();
        }
        
        return array_map(function($item) {
            // Basic data
            $formatted = array(
                'id' => $item['id'],
                'member_id' => $item['member_id'],
                'service_id' => $item['service_id'],
                'service_short_name' => isset($item['service_short_name']) ? $item['service_short_name'] : '',
                'service_full_name' => isset($item['service_full_name']) ? $item['service_full_name'] : '',
                'nomor_skp' => $item['nomor_skp'],
                'nama_tenaga_ahli' => $item['nama_tenaga_ahli'],
                'penanggung_jawab' => $item['penanggung_jawab'],
                'status' => $item['status'],
            );

            // Format dates
            $formatted['tanggal_terbit'] = mysql2date(get_option('date_format'), $item['tanggal_terbit']);
            $formatted['masa_berlaku'] = mysql2date(get_option('date_format'), $item['masa_berlaku']);

            // Add status label
            $formatted['status_label'] = $this->get_status_label($item['status']);

            // Add file URL
            if (!empty($item['file_path'])) {
                $formatted['file_url'] = $this->get_secure_pdf_url($item['id'], $item['file_path']);
            }

            // Add edit permission flag
            $formatted['can_edit'] = $item['status'] === 'active';

            return $formatted;
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

        if (!current_user_can('add_asosiasi_members')) {
            wp_die(__('Akses tidak diizinkan', 'asosiasi'));
        }

        $skp = new Asosiasi_SKP_Tenaga_Ahli();
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
            'activated' => __('Diaktifkan', 'asosiasi'),
            'expired' => __('Kadaluarsa', 'asosiasi'),
            'inactive' => __('Tidak Aktif', 'asosiasi')
        );

        return isset($labels[$status]) ? $labels[$status] : $status;
    }
}
