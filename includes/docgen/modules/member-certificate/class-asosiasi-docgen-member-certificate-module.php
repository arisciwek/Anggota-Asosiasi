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

        // Add new direct PDF generation button
        add_action('asosiasi_after_member_info', [$this, 'create_pdf_certificate_button']);
        
        // Add new AJAX handler
        add_action('wp_ajax_create_member_certificate_pdf', [$this, 'handle_direct_pdf_generation']);

    }

    public function create_pdf_certificate_button($member_id) {
        $can_edit = Asosiasi_Permission_Helper::can_edit_member($member_id);
        if (!$can_edit) {
            return;
        }
        ?>
        <button type="button" 
                id="create-pdf-certificate" 
                class="button button-secondary" 
                data-member="<?php echo esc_attr($member_id); ?>">
            <?php _e('Generate PDF', 'asosiasi'); ?>
            <span class="spinner"></span>
        </button>
        <?php
    }

    public function handle_direct_pdf_generation() {
        check_ajax_referer('asosiasi-docgen-certificate');

        try {
            error_log('=== START PDF CERTIFICATE DIRECT GENERATION ===');
            
            $member_id = absint($_POST['member_id'] ?? 0);
            error_log('Member ID: ' . $member_id);
            
            if (!$member_id) {
                throw new Exception(__('Invalid member ID', 'asosiasi'));
            }

            // Setup temp directory
            error_log('Getting mPDF paths...');
            $paths = WP_MPDF_Activator::get_mpdf_paths();
            error_log('mPDF paths: ' . print_r($paths, true));

            // Get member data
            require_once dirname(__FILE__) . '/providers/class-asosiasi-docgen-member-certificate-provider.php';
            $provider = new Asosiasi_Docgen_Member_Certificate_Provider($member_id);
            $data = $provider->get_data();

            // Generate QR Code
            $qrCode = new \Mpdf\QrCode\QrCode($data['qr_data'], 'L');
            
            // Dari:
            $qrOutput = new \Mpdf\QrCode\Output\Png();
            $qrImage = $qrOutput->output($qrCode, 300);
            $base64QRCode = base64_encode($qrImage);
            $base64QRCode = base64_encode($qrImage);
            $data['base64QRCode'] = $base64QRCode;
            
            // Initialize mPDF
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4-L',
                'orientation' => 'L',
                'tempDir' => $paths['temp_path'],
                'fontDir' => [
                    WP_MPDF_DIR . 'libs/mpdf/ttfonts',
                    $paths['font_path']
                ],
                'fontCache' => $paths['cache_path'],
                'default_font' => 'dejavusans',
                'margin_top' => 3,
                'margin_right' => 3,
                'margin_bottom' => 3,
                'margin_left' => 3,
                'charset_in' => 'UTF-8',
                'allow_charset_conversion' => true,
                'debug' => true
            ]);

            // Get template content
            ob_start();
            include dirname(__FILE__) . '/templates/certificate-template.php';
            $html = ob_get_clean();

            // Generate PDF
            $mpdf->WriteHTML($html);
            
            // Save to output directory
            $output_dir = $provider->get_temp_dir();
            $filename = 'sertifikat-' . sanitize_title($data['company_name']) . '-' . date('Ymd-His') . '.pdf';
            $output_path = $output_dir . '/' . $filename;
            
            error_log('Saving PDF to: ' . $output_path);
            
            $mpdf->Output($output_path, 'F');

            // Get URL for response
            $upload_dir = wp_upload_dir();
            $file_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $output_path);
            error_log('PDF URL: ' . $file_url);

            wp_send_json_success([
                'url' => $file_url,
                'file' => $filename,
                'direct_download' => true
            ]);

            error_log('=== END PDF CERTIFICATE DIRECT GENERATION ===');

        } catch (Exception $e) {
            error_log('Direct PDF Generation Error: ' . $e->getMessage());
            error_log('Error trace: ' . $e->getTraceAsString());
            wp_send_json_error($e->getMessage());
        }
    }

    public function add_member_certificate_button($member_id) {
        if (!current_user_can('manage_options')) {
            return;
        }

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
        if (!current_user_can('manage_options')) {
            return;
        }
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

    // Di class-asosiasi-docgen-member-certificate-module.php, update method:
    public function handle_member_certificate_docx() {
        check_ajax_referer('asosiasi-docgen-certificate');

        try {
            $member_id = absint($_POST['member_id'] ?? 0);
            if (!$member_id) {
                throw new Exception(__('Invalid member ID', 'asosiasi'));
            }

            require_once dirname(__FILE__) . '/providers/class-asosiasi-docgen-member-certificate-provider.php';
            $provider = new Asosiasi_Docgen_Member_Certificate_Provider($member_id);
            $processor = new WP_DocGen_Processor();
            
            // Use processor untuk generate dokumen
            $result = $processor->generate($provider);
            
            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }

            $upload_dir = wp_upload_dir();
            $file_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $result);

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
            error_log('=== START PDF CERTIFICATE GENERATION ===');
            
            $member_id = absint($_POST['member_id'] ?? 0);
            error_log('Member ID: ' . $member_id);
            
            if (!$member_id) {
                throw new Exception(__('Invalid member ID', 'asosiasi'));
            }

            // Setup temp directory
            error_log('Getting mPDF paths...');
            $paths = WP_MPDF_Activator::get_mpdf_paths();
            error_log('mPDF paths: ' . print_r($paths, true));
            
            // Generate DOCX first
            error_log('Initializing provider for DOCX generation...');
            $provider = new Asosiasi_Docgen_Member_Certificate_Provider($member_id);
            
            error_log('Starting DOCX generation...');
            $generator = new WP_DocGen();
            $docx_result = $generator->generate($provider);
            
            if (is_wp_error($docx_result)) {
                error_log('DOCX generation failed: ' . $docx_result->get_error_message());
                throw new Exception($docx_result->get_error_message());
            }
            error_log('DOCX generated at: ' . $docx_result);

            // Load generated DOCX
            error_log('Loading DOCX into PHPWord...');
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($docx_result);
            $pdf_filename = str_replace('.docx', '.pdf', $docx_result);
            error_log('PDF will be saved as: ' . $pdf_filename);

            // Convert to PDF using custom writer
            error_log('Starting PDF conversion...');
            $pdf_writer = new Asosiasi_DocGen_MPDF_Writer($phpWord);
            $pdf_writer->save($pdf_filename);
            error_log('PDF saved successfully');

            // Clean up DOCX
            error_log('Cleaning up temporary DOCX...');
            @unlink($docx_result);

            // Get URL for PDF
            error_log('Preparing PDF URL...');
            $upload_dir = wp_upload_dir();
            $file_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $pdf_filename);
            error_log('PDF URL: ' . $file_url);

            wp_send_json_success([
                'url' => $file_url,
                'file' => basename($pdf_filename),
                'direct_download' => true
            ]);

            error_log('=== END PDF CERTIFICATE GENERATION ===');

        } catch (Exception $e) {
            error_log('PDF Generation Error: ' . $e->getMessage());
            error_log('Error trace: ' . $e->getTraceAsString());
            wp_send_json_error($e->getMessage());
        }
    }

/*
    public function handle_member_certificate_pdf() {
        check_ajax_referer('asosiasi-docgen-certificate');

        try {
            $member_id = absint($_POST['member_id'] ?? 0);
            if (!$member_id) {
                throw new Exception(__('Invalid member ID', 'asosiasi'));
            }

            // Setup temp directory
            $paths = WP_MPDF_Activator::get_mpdf_paths();
            
            // Generate DOCX first
            $provider = new Asosiasi_Docgen_Member_Certificate_Provider($member_id);
            $generator = new WP_DocGen();
            $docx_result = $generator->generate($provider);
            
            if (is_wp_error($docx_result)) {
                throw new Exception($docx_result->get_error_message());
            }

            // Load generated DOCX
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($docx_result);
            $pdf_filename = str_replace('.docx', '.pdf', $docx_result);

            // Convert to PDF menggunakan custom writer
            $pdf_writer = new Asosiasi_DocGen_MPDF_Writer($phpWord);
            $pdf_writer->save($pdf_filename);

            // Clean up DOCX
            @unlink($docx_result);

            // Get URL for PDF
            $upload_dir = wp_upload_dir();
            $file_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $pdf_filename);

            wp_send_json_success([
                'url' => $file_url,
                'file' => basename($pdf_filename),
                'direct_download' => true
            ]);

        } catch (Exception $e) {
            error_log('PDF Generation Error: ' . $e->getMessage());
            wp_send_json_error($e->getMessage());
        }
    }
*/
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

    private function convert_to_pdf($phpWord, $output_file, $member_id) {
        try {
            // Setup mPDF writer
            $pdfWriter = new Asosiasi_DocGen_MPDF_Writer($phpWord);
            
            // Simpan file
            $pdfWriter->save($output_file);

            return $output_file;

        } catch (Exception $e) {
            error_log('PDF Generation Error: ' . $e->getMessage());
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
