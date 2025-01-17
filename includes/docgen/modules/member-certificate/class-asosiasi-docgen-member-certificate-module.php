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
    /**
     * Instance mPDF yang akan digunakan di seluruh class
     */
    private $mpdf = null;

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
        add_action('wp_ajax_download_member_certificate', [$this, 'handle_certificate_download']);
        add_action('wp_ajax_create_member_certificate_pdf', [$this, 'handle_member_certificate_generation']);
        add_action('wp_ajax_generate_member_card', [$this, 'handle_member_card_generation']);

        add_action('asosiasi_after_member_info', [$this, 'add_member_certificate_button']);
        add_action('asosiasi_after_member_info', [$this, 'add_pdf_certificate_button']);

        add_action('asosiasi_after_member_info', [$this, 'create_member_card_button']);
        add_action('asosiasi_after_member_info', [$this, 'create_pdf_certificate_button']);
        

        
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function create_member_card_button($member_id) {
        $can_edit = Asosiasi_Permission_Helper::can_edit_member($member_id);
        if (!$can_edit) {
            return;
        }
        ?>
        <button type="button" 
                id="generate-member-card" 
                class="button button-secondary" 
                data-member="<?php echo esc_attr($member_id); ?>">
            <?php _e('Generate Kartu Anggota', 'asosiasi'); ?>
            <span class="spinner"></span>
        </button>
        <?php
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
            <?php _e('Generate Sertifikat Anggota', 'asosiasi'); ?>
            <span class="spinner"></span>
        </button>
        <?php
    }

    /**
     * Get atau initialize mPDF instance
     * Memastikan hanya ada satu instance mPDF
     */
    private function get_mpdf_instance($config = []) {
        if ($this->mpdf === null) {
            try {
                // Get mPDF paths sekali saja
                $paths = WP_MPDF_Activator::get_mpdf_paths();
                
                // Default config
                $default_config = [
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
                ];

                // Merge dengan custom config
                $final_config = array_merge($default_config, $config);
                
                // Buat instance mPDF
                $this->mpdf = new \Mpdf\Mpdf($final_config);
            } catch (Exception $e) {
                error_log('mPDF initialization error: ' . $e->getMessage());
                throw $e;
            }
        }
        
        return $this->mpdf;
    }

    // Tambahkan method baru untuk generate kartu member
    public function handle_member_card_generation() {
        check_ajax_referer('asosiasi-docgen-certificate');

        try {
            // Initialize wp-mpdf first
            if (!function_exists('wp_mpdf_init')) {
                throw new Exception('WP mPDF plugin is required');
            }
            wp_mpdf_init();

            $member_id = absint($_POST['member_id'] ?? 0);
            if (!$member_id) {
                throw new Exception(__('Invalid member ID', 'asosiasi'));
            }

            // Get member data
            require_once dirname(__FILE__) . '/providers/class-asosiasi-docgen-member-certificate-provider.php';
            $provider = new Asosiasi_Docgen_Member_Certificate_Provider($member_id);
            $data = $provider->get_data();

            // Generate QR Code
            $qrCode = new \Mpdf\QrCode\QrCode($data['qr_data'], 'L');
            $qrOutput = new \Mpdf\QrCode\Output\Png();
            $qrImage = $qrOutput->output($qrCode, 200);
            $data['base64QRCode'] = base64_encode($qrImage);

            // Get mPDF instance dengan config default untuk A4
            $mpdf = $this->get_mpdf_instance([
                'format' => 'A4',
                'orientation' => 'P', // Portrait untuk kartu
                'margin_top' => 10,
                'margin_right' => 10,
                'margin_bottom' => 10,
                'margin_left' => 10
            ]);

            $upload_dir = wp_upload_dir();

            // Get paths untuk watermark
            $paths = WP_MPDF_Activator::get_mpdf_paths();
            $watermark_path = $upload_dir['basedir'] . '/asosiasi/watermark-card-pattern.svg';            
            // Set watermark jika diperlukan
            if (file_exists($watermark_path)) {
                $watermark_image = new \Mpdf\WatermarkImage(
                    $watermark_path,
                    \Mpdf\WatermarkImage::SIZE_FIT_PAGE,
                    \Mpdf\WatermarkImage::POSITION_CENTER_PAGE,
                    0.5,
                    true
                );
                
                $mpdf->SetWatermarkImage(
                    $watermark_path,
                    0.5,
                    $watermark_image->getSize(),
                    $watermark_image->getPosition()
                );
                $mpdf->showWatermarkImage = true;
            }

            // Generate PDF content
            ob_start();
            include dirname(__FILE__) . '/templates/member-card-template.php';
            $html = ob_get_clean();

            $mpdf->WriteHTML($html);

            // Save output
            $output_dir = $provider->get_temp_dir();
            $filename = 'kartu-anggota-' . sanitize_title($data['company_name']) . '-' . date('Ymd-His') . '.pdf';
            $output_path = $output_dir . '/' . $filename;

            $mpdf->Output($output_path, 'F');

            // Get URL for response
            $file_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $output_path);

            wp_send_json_success([
                'url' => $file_url,
                'file' => $filename,
                'direct_download' => true
            ]);

        } catch (Exception $e) {
            error_log('Member Card Generation Error: ' . $e->getMessage());
            error_log('Error trace: ' . $e->getTraceAsString());
            wp_send_json_error($e->getMessage());
        }
    }

    public function handle_member_certificate_generation() {
        check_ajax_referer('asosiasi-docgen-certificate');

        try {
            $member_id = absint($_POST['member_id'] ?? 0);
            
            // Get data
            require_once dirname(__FILE__) . '/providers/class-asosiasi-docgen-member-certificate-provider.php';
            $provider = new Asosiasi_Docgen_Member_Certificate_Provider($member_id);
            
            $data = $provider->get_data();

            // Validasi data
            if (!$member_id) {
                throw new Exception('Invalid member ID');
            }

            if (empty($data['valid_until'])) {
                throw new Exception('Empty valid_until date');
            }

            if (strpos($data['valid_until'], '-0001') !== false) {
                error_log('Main Process Error: Invalid valid_until date: ' . $data['valid_until']);
                wp_send_json_error([
                    'success' => false,
                    'data' => 'Data tidak valid. Silakan cek tanggal berlaku keanggotaan.'
                ]);
                exit();
            }

            // Jika validasi berhasil, lanjut ke generate PDF
            try {
                if (!function_exists('wp_mpdf_init')) {
                    throw new Exception('WP mPDF plugin is required');
                }
                wp_mpdf_init();

                    // Lanjutkan dengan kode existing untuk generate PDF



                    $member_id = absint($_POST['member_id'] ?? 0);
                    if (!$member_id) {
                        throw new Exception(__('Invalid member ID', 'asosiasi'));
                    }

                    // Get member data
                    require_once dirname(__FILE__) . '/providers/class-asosiasi-docgen-member-certificate-provider.php';
                    $provider = new Asosiasi_Docgen_Member_Certificate_Provider($member_id);
                    $data = $provider->get_data();

                    // Cek kelengkapan data
                    foreach($data as $key => $value) {
                        if(empty($value)) {
                            throw new Exception("Data {$key} masih kosong");
                        }
                    }

                    // Generate QR Code
                    $qrCode = new \Mpdf\QrCode\QrCode($data['qr_data'], 'L');
                    $qrOutput = new \Mpdf\QrCode\Output\Png();
                    $qrImage = $qrOutput->output($qrCode, 300);
                    $base64QRCode = base64_encode($qrImage);
                    $data['base64QRCode'] = $base64QRCode;

                    // Get mPDF instance dengan config default
                    $mpdf = $this->get_mpdf_instance();

                    $upload_dir = wp_upload_dir();

                    // Get paths untuk watermark
                    $paths = WP_MPDF_Activator::get_mpdf_paths();
                    $watermark_path = $upload_dir['basedir'] . '/asosiasi/watermark-pattern.svg';

                    // Get paths untuk watermark
                    //$paths = WP_MPDF_Activator::get_mpdf_paths();
                    //$watermark_path = $paths['temp_path'] . '/watermark-pattern.svg';
                    
                    // Set watermark jika diperlukan
                    if (file_exists($watermark_path)) {
                        $watermark_image = new \Mpdf\WatermarkImage(
                            $watermark_path,
                            \Mpdf\WatermarkImage::SIZE_FIT_PAGE,
                            \Mpdf\WatermarkImage::POSITION_CENTER_PAGE,
                            0.5,
                            true
                        );
                        
                        $mpdf->SetWatermarkImage(
                            $watermark_path,
                            0.5,
                            $watermark_image->getSize(),
                            $watermark_image->getPosition()
                        );
                        $mpdf->showWatermarkImage = true;
                    }

                    // Generate PDF content
                    ob_start();
                    include dirname(__FILE__) . '/templates/certificate-template.php';
                    $html = ob_get_clean();

                    // Load translations sebelum generate PDF
                    // load_plugin_textdomain('asosiasi', false, dirname(plugin_basename(__FILE__)) . '/languages');
                    
                    $mpdf->WriteHTML($html);

                    // Save output
                    $output_dir = $provider->get_temp_dir();
                    $filename = 'sertifikat-' . sanitize_title($data['company_name']) . '-' . date('Ymd-His') . '.pdf';
                    $output_path = $output_dir . '/' . $filename;

                    // Gunakan 'D' untuk force download atau 'F' untuk save ke file system
                    $mpdf->Output($output_path, 'F');

                    $file_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $output_path);

                    wp_send_json_success([
                        'url' => $file_url,
                        'file' => $filename,
                        'direct_download' => true
                    ]);


                    // ... rest of your existing code ...

            } catch (Exception $pdfError) {
                error_log('PDF Generation Error: ' . $pdfError->getMessage());
                wp_send_json_error([
                    'success' => false,
                    'data' => 'Mohon maaf, terjadi kesalahan teknis dalam pembuatan PDF.'
                ]);
                wp_die();
            }








        } catch (Exception $mainError) {
            error_log('Main Process Error: ' . $mainError->getMessage());
            wp_send_json_error([
                'success' => false,
                'data' => 'Terjadi kesalahan sistem. Silakan hubungi administrator.'
            ]);
            wp_die();
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
            //error_log('=== START PDF CERTIFICATE GENERATION ===');
            
            $member_id = absint($_POST['member_id'] ?? 0);
            //error_log('Member ID: ' . $member_id);
            
            if (!$member_id) {
                throw new Exception(__('Invalid member ID', 'asosiasi'));
            }

            // Setup temp directory
            //error_log('Getting mPDF paths...');
            $paths = WP_MPDF_Activator::get_mpdf_paths();
            //error_log('mPDF paths: ' . print_r($paths, true));
            
            // Generate DOCX first
            //error_log('Initializing provider for DOCX generation...');
            $provider = new Asosiasi_Docgen_Member_Certificate_Provider($member_id);
            
            //error_log('Starting DOCX generation...');
            $generator = new WP_DocGen();
            $docx_result = $generator->generate($provider);
            
            if (is_wp_error($docx_result)) {
                error_log('DOCX generation failed: ' . $docx_result->get_error_message());
                throw new Exception($docx_result->get_error_message());
            }
            //error_log('DOCX generated at: ' . $docx_result);

            // Load generated DOCX
            //error_log('Loading DOCX into PHPWord...');
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($docx_result);
            $pdf_filename = str_replace('.docx', '.pdf', $docx_result);
            //error_log('PDF will be saved as: ' . $pdf_filename);

            // Convert to PDF using custom writer
            //error_log('Starting PDF conversion...');
            $pdf_writer = new Asosiasi_DocGen_MPDF_Writer($phpWord);
            $pdf_writer->save($pdf_filename);
            //error_log('PDF saved successfully');

            // Clean up DOCX
            //error_log('Cleaning up temporary DOCX...');
            @unlink($docx_result);

            // Get URL for PDF
            //error_log('Preparing PDF URL...');
            $upload_dir = wp_upload_dir();
            $file_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $pdf_filename);
            //error_log('PDF URL: ' . $file_url);

            wp_send_json_success([
                'url' => $file_url,
                'file' => basename($pdf_filename),
                'direct_download' => true
            ]);

            //error_log('=== END PDF CERTIFICATE GENERATION ===');

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
        // Convert null to empty string and ensure we have a string
        $hook = (string)($hook ?? '');
        
        if (empty($hook)) {
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
                'pdfError' => __('Failed to generate PDF.', 'asosiasi'),
                // Tambahkan string baru untuk kartu member
                'cardSuccess' => __('Member card generated successfully!', 'asosiasi'),
                'cardError' => __('Failed to generate member card.', 'asosiasi')
            ]
        ]);

    }
}
