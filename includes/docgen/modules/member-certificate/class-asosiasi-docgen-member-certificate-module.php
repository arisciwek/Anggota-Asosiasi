<?php
/**
 * Asosiasi DocGen Member Certificate Module
 *
 * @package     Asosiasi
 * @subpackage  DocGen/Modules/Certificate
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: modules/member-certificate/class-asosiasi-docgen-member-certificate-module.php
 * 
 * Description: Module untuk generate sertifikat anggota asosiasi.
 *              Menangani integrasi dengan DocGen Implementation untuk
 *              pembuatan sertifikat dari data member yang tersimpan.
 *              Includes AJAX handlers, button integration ke member page,
 *              dan asset management untuk certificate generation.
 * 
 * Filename Convention: class-asosiasi-docgen-certificate-module.php
 * 
 * Class Name Convention: Asosiasi_DocGen_Certificate_Module
 * 
 * Required Methods:
 * - handle_generate()    : AJAX handler untuk generate sertifikat
 * - enqueue_assets()     : Register & enqueue assets
 * - get_provider_class() : Return provider class name
 * 
 * Dependencies:
 * - DocGen Implementation Plugin
 * - DocGen_Module base class
 * - WP DocGen Plugin
 * 
 * Changelog:
 * 1.0.0 - 2024-12-20
 * - Initial release
 * - Added certificate generation handler
 * - Added member page integration
 * - Added AJAX support
 * - Added asset management
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class Asosiasi_DocGen_Member_Certificate_Module {
    protected $module_info = [
        'slug' => 'member-certificate',
        'name' => 'Member Certificate',
        'description' => 'Generate member certificates',
        'version' => '1.0.0'
    ];

    public function __construct() {
        // Check WP DocGen
        if (!class_exists('WP_DocGen')) {
            add_action('admin_notices', function() {
                $message = __('WP DocGen plugin is required for certificate generation.', 'asosiasi');
                echo '<div class="notice notice-error"><p>' . esc_html($message) . '</p></div>';
            });
            return;
        }

        // Register handlers
        add_action('wp_ajax_generate_member_certificate_docx', [$this, 'handle_member_certificate_docx']);
        add_action('wp_ajax_generate_member_certificate_pdf', [$this, 'handle_member_certificate_pdf']);
        add_action('asosiasi_after_member_info', [$this, 'add_member_certificate_button']);
        add_action('asosiasi_after_member_info', [$this, 'add_pdf_certificate_button']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);   // Add download handler
        add_action('wp_ajax_download_member_certificate', [$this, 'handle_certificate_download']);

    }

    public function add_member_certificate_button($member_id) {
        ?>
        <button type="button" 
                id="generate-certificate" 
                class="button button-secondary" 
                data-member="<?php echo esc_attr($member_id); ?>">
            <?php _e('Download Sertifikat', 'asosiasi'); ?>
            <span class="spinner"></span>
        </button>
        <?php
    }

    public function add_pdf_certificate_button($member_id) {
        ?>
        <button type="button" 
                id="generate-pdf-certificate" 
                class="button button-secondary" 
                data-member="<?php echo esc_attr($member_id); ?>">
            <?php _e('Download PDF', 'asosiasi'); ?>
            <span class="spinner"></span>
        </button>
        <?php
    }

    public function handle_member_certificate_docx() {
        check_ajax_referer('asosiasi-docgen-certificate');

        try {
            $member_id = absint($_POST['member_id'] ?? 0);
            if (!$member_id) {
                throw new Exception(__('Invalid member ID', 'asosiasi'));
            }

            require_once dirname(__FILE__) . '/providers/class-asosiasi-docgen-member-certificate-provider.php';
            $provider = new Asosiasi_Docgen_Member_Certificate_Provider($member_id);

            $generator = new WP_DocGen();
            $result = $generator->generate($provider);
            
            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }

            $upload_dir = wp_upload_dir();if (!is_string($result)) {
                throw new Exception(__('Invalid generation result', 'asosiasi')); 
            }

            $base_dir = $upload_dir['basedir'] ?? '';
            $base_url = $upload_dir['baseurl'] ?? '';

            if (empty($base_dir) || empty($base_url)) {
                throw new Exception(__('Invalid upload directory configuration', 'asosiasi'));
            }

            $file_url = str_replace($base_dir, $base_url, $result);

            wp_send_json_success([
                'url' => $file_url,
                'file' => basename($result)
            ]);

        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

public function handle_member_certificate_pdf() {
    check_ajax_referer('asosiasi-docgen-certificate');

    try {
        $member_id = absint($_POST['member_id'] ?? 0);
        if (!$member_id) {
            throw new Exception(__('Invalid member ID', 'asosiasi'));
        }

        // Setup temp directory
        $paths = WP_MPDF_Activator::get_mpdf_paths();
        
        // First generate DOCX
        require_once dirname(__FILE__) . '/providers/class-asosiasi-docgen-member-certificate-provider.php';
        $provider = new Asosiasi_Docgen_Member_Certificate_Provider($member_id);
        
        $generator = new WP_DocGen();
        $docx_result = $generator->generate($provider);
        
        if (is_wp_error($docx_result)) {
            throw new Exception($docx_result->get_error_message());
        }

        // Load the generated DOCX
        $phpWord = \PhpOffice\PhpWord\IOFactory::load($docx_result);

        // Generate PDF filename
        if (!is_string($docx_result)) {
            throw new Exception(__('Invalid DOCX generation result', 'asosiasi'));
        }
        $pdf_filename = str_replace('.docx', '.pdf', $docx_result);

        // Convert to PDF - Pastikan $pdf_result didefinisikan
        $pdf_result = $this->convert_to_pdf($phpWord, $pdf_filename);

        if (is_wp_error($pdf_result)) {
            throw new Exception($pdf_result->get_error_message());
        }

        // Verifikasi file exists setelah $pdf_result terdefinisi
        if (!$pdf_result || !file_exists($pdf_result)) {
            throw new Exception('PDF file was not created successfully');
        }

        if (filesize($pdf_result) === 0) {
            throw new Exception('PDF file is empty');
        }

        // Clean up DOCX file
        @unlink($docx_result);

        // Get URL for PDF
        $upload_dir = wp_upload_dir();

        if (!is_string($pdf_result)) {
            throw new Exception(__('Invalid PDF generation result', 'asosiasi'));
        }

        $base_dir = $upload_dir['basedir'] ?? '';
        $base_url = $upload_dir['baseurl'] ?? '';

        if (empty($base_dir) || empty($base_url)) {
            throw new Exception(__('Invalid upload directory configuration', 'asosiasi'));
        }

        $file_url = str_replace($base_dir, $base_url, $pdf_result);

        $response_data = [
            'url' => $file_url,
            'file' => basename($pdf_result),
            'download_url' => add_query_arg([
                'action' => 'download_member_certificate',
                'file' => base64_encode($pdf_result),
                'filename' => basename($pdf_result),
                'nonce' => wp_create_nonce('download_certificate')
            ], admin_url('admin-ajax.php'))
        ];

        // Return direct file URL instead of download handler
        wp_send_json_success([
            'url' => $file_url,
            'file' => basename($pdf_result),
            'direct_download' => true // tambahkan flag ini
        ]);

    } catch (Exception $e) {
        error_log('Handle PDF Generation Error: ' . $e->getMessage());
        error_log('Error trace: ' . $e->getTraceAsString());
        wp_send_json_error($e->getMessage());
    }
}
    // Add new method for handling downloads
    public function handle_certificate_download() {
        // Verify nonce
        if (!check_admin_referer('download_certificate')) {
            wp_die('Invalid request');
        }

        // Get file path from request
        $file_path = base64_decode($_GET['file']);
        $filename = sanitize_file_name($_GET['filename']);

        // Verify file exists
        if (!file_exists($file_path)) {
            wp_die('File not found');
        }

        // Set headers for download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');

        // Clear output buffer
        ob_clean();
        flush();

        // Output file
        readfile($file_path);
        exit;
    }


private function convert_to_pdf($phpWord, $output_file) {
    try {
        // Get paths from WP_MPDF_Activator
        $paths = WP_MPDF_Activator::get_mpdf_paths();
        
        // Create temp directory if not exists
        if (!file_exists($paths['temp_path'])) {
            wp_mkdir_p($paths['temp_path']);
        }
        
        if (!is_writable($paths['temp_path'])) {
            throw new Exception('Temporary directory is not writable: ' . $paths['temp_path']);
        }

        // Set PDF renderer
        \PhpOffice\PhpWord\Settings::setPdfRendererPath(WP_MPDF::get_mpdf_src_path());
        \PhpOffice\PhpWord\Settings::setPdfRendererName(\PhpOffice\PhpWord\Settings::PDF_RENDERER_MPDF);

        // Use custom MPDF writer
        $pdfWriter = new Asosiasi_DocGen_MPDF_Writer($phpWord);
        $pdfWriter->save($output_file);

        return $output_file;

    } catch (Exception $e) {
        error_log('PDF Generation Error in ' . __FILE__ . ': ' . $e->getMessage());
        error_log('Error trace: ' . $e->getTraceAsString());
        return new WP_Error('pdf_generation_failed', $e->getMessage());
    }
}

    /**
     * Verifikasi WP mPDF plugin dan dependencies
     */
    private function verify_wp_mpdf() {

        // Get mPDF settings
        $mpdf_settings = wp_mpdf_get_pdf_settings();
        
        // Debug: Log settings
        error_log('mPDF Settings: ' . print_r($mpdf_settings, true));

        // Check plugin functions
        $required_functions = [
            'wp_mpdf_verify_library',
            'wp_mpdf_get_library_path',
            'wp_mpdf_get_pdf_settings'
        ];

        foreach ($required_functions as $function) {
            if (!function_exists($function)) {
                throw new Exception(
                    sprintf(
                        'Required WP mPDF function %s is not available.',
                        $function
                    )
                );
            }
        }

        // Verify library
        if (!wp_mpdf_verify_library()) {
            throw new Exception('WP mPDF library verification failed.');
        }

        // Get and verify settings
        $settings = wp_mpdf_get_pdf_settings();
        if (!$settings) {
            throw new Exception('Failed to get WP mPDF settings.');
        }

        // Verify paths
        $required_paths = [
            'library_path',
            'src_path',
            'temp_path',
            'font_path',
            'cache_path'
        ];

        foreach ($required_paths as $path) {
            if (empty($settings[$path]) || !file_exists($settings[$path])) {
                throw new Exception(
                    sprintf(
                        'Required WP mPDF path %s is missing or invalid.',
                        $path
                    )
                );
            }

            if (!is_writable($settings[$path])) {
                throw new Exception(
                    sprintf(
                        'WP mPDF path %s is not writable.',
                        $path
                    )
                );
            }
        }

        return true;
    }

    /**
     * Helper untuk membersihkan direktori
     */
    private function cleanup_directory($dir) {
        if (!is_dir($dir)) {
            error_log('Cleanup: Directory does not exist: ' . $dir);
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->cleanup_directory($path);
            } else {
                if (unlink($path)) {
                    error_log('Cleanup: Deleted file: ' . $path);
                } else {
                    error_log('Cleanup: Failed to delete file: ' . $path);
                }
            }
        }
        
        if (rmdir($dir)) {
            error_log('Cleanup: Removed directory: ' . $dir);
        } else {
            error_log('Cleanup: Failed to remove directory: ' . $dir);
        }
    }

    public function enqueue_assets($hook) {
        if (!is_string($hook) || empty($hook)) {
            return;
        }
        if (strpos($hook, 'asosiasi') === false) {
            return;
        }

        wp_enqueue_style(
            'asosiasi-docgen-member-certificate',
            plugins_url('assets/css/asosiasi-docgen-member-certificate-style.css', __FILE__),
            [],
            $this->module_info['version']
        );

        wp_enqueue_script(
            'asosiasi-docgen-member-certificate',
            plugins_url('assets/js/asosiasi-docgen-member-certificate-script.js', __FILE__),
            ['jquery'],
            $this->module_info['version'],
            true
        );

        wp_localize_script('asosiasi-docgen-member-certificate', 'asosiasiDocGenCert', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('asosiasi-docgen-certificate'),
            'strings' => [
                'generateSuccess' => __('Certificate generated successfully!', 'asosiasi'),
                'generateError' => __('Failed to generate certificate.', 'asosiasi'),
                'pdfSuccess' => __('PDF generated successfully!', 'asosiasi'),
                'pdfError' => __('Failed to generate PDF.', 'asosiasi')
            ]
        ]);
    }
}
