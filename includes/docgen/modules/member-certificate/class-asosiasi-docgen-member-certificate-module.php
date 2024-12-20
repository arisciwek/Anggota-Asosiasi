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
     * Module info
     */
    protected $module_info = [
        'slug' => 'member-certificate',
        'name' => 'Member Certificate',
        'description' => 'Generate member certificates',
        'version' => '1.0.0'
    ];

    /**
     * Constructor
     */
    public function __construct() {
        // Cek WP DocGen
        if (!class_exists('WP_DocGen')) {
            add_action('admin_notices', function() {
                $message = __('WP DocGen plugin is required for certificate generation.', 'asosiasi');
                echo '<div class="notice notice-error"><p>' . esc_html($message) . '</p></div>';
            });
            return;
        }

        // Register AJAX handler dan button
        add_action('wp_ajax_generate_member_certificate', [$this, 'handle_generate']);
        add_action('asosiasi_after_member_info', [$this, 'add_member_certificate_button']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    /**
     * Button untuk generate di member page
     */
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

    /**
     * Generate sertifikat menggunakan WP DocGen
     */

    public function handle_generate() {
        check_ajax_referer('asosiasi-docgen-certificate');

        try {
            $member_id = absint($_POST['member_id'] ?? 0);
            if (!$member_id) {
                throw new Exception(__('Invalid member ID', 'asosiasi'));
            }

            // Load provider yang implements WP_DocGen_Provider
            require_once dirname(__FILE__) . '/providers/class-asosiasi-docgen-member-certificate-provider.php';
            $provider = new Asosiasi_Docgen_Member_Certificate_Provider($member_id);

            // Generate dengan WP DocGen
            $generator = new WP_DocGen();
            $result = $generator->generate($provider); // Passing provider object langsung
            
            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }

            // Get URL for download
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

    /**
     * Enqueue assets
     */
    public function enqueue_assets($hook) {
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
                'generateError' => __('Failed to generate certificate.', 'asosiasi')
            ]
        ]);
    }
}