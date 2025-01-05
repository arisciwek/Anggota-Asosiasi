<?php

/**
 * Certificate Verification Handler
 *
 * @package     Asosiasi
 * @subpackage  DocGen/Modules/MemberCertificate/Verificatiton
 * @version     1.0.0
 * @author      arisciwek
 * @copyright  2024 Asosiasi Organization
 * @license    GPL-2.0+
 * 
 * Description:
 * Handler untuk memproses dan memverifikasi sertifikat anggota.
 * Menangani request verifikasi melalui QR code dan menampilkan hasil.
 * 
 * Path: includes/docgen/modules/member-certificate/verification/class-asosiasi-certificate-verification.php
 * Timestamp: 2024-12-29 11:00:00
 * 
 * Required Methods:
 * - get_instance()            : Singleton instance getter
 * - handle_verification()     : Proses verifikasi sertifikat
 * - register_query_vars()     : Register WordPress query variables
 * 
 * Dependencies:
 * - WordPress Core
 * - Asosiasi Member Certificate Provider
 * - Asosiasi Certificate Templates
 */

class Asosiasi_Certificate_Verification {
   
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Add handler untuk template_include
        add_filter('template_include', [$this, 'handle_verification']);
        
        // Register query vars
        add_filter('query_vars', [$this, 'register_query_vars']);
        
        // Debug code
        add_action('template_redirect', [$this, 'debug_verification']);
    }
    
    public function register_query_vars($vars) {
        $vars[] = 'certificate_verify';
        $vars[] = 'member_id';
        $vars[] = 'verify_code';
        return $vars;
    }
    
	public function handle_verification($template) {
	    if (!get_query_var('certificate_verify')) {
	        return $template;
	    }
	    
	    $member_id = get_query_var('member_id');
	    $verify_code = get_query_var('verify_code');
	    
	    if (!$member_id || !$verify_code) {
	        return $this->display_error('Invalid verification parameters');
	    }
	    
	    try {
	        require_once dirname(__FILE__) . '/../providers/class-asosiasi-docgen-member-certificate-provider.php';
	        
	        $provider = new Asosiasi_Docgen_Member_Certificate_Provider($member_id);
	        $data = $provider->get_data();
	        
	        // Tambahkan data organizational
	        $GLOBALS['verification_data'] = $data;
	        $GLOBALS['member'] = [
	            'organization_name' => get_option('asosiasi_organization_name'),
	            'website' => get_option('asosiasi_website')
	        ];
	        
	        return dirname(__FILE__) . '/templates/certificate-verification-template.php';
	        
	    } catch (Exception $e) {
	        return $this->display_error($e->getMessage());
	    }
	}

    private function display_error($message) {
        include dirname(__FILE__) . '/templates/certificate-verification-error.php';
        exit;
    }
    
    public function debug_verification() {
        if (get_query_var('certificate_verify')) {
            error_log('Certificate verification requested');
            error_log('Member ID: ' . get_query_var('member_id'));
            error_log('Verify Code: ' . get_query_var('verify_code'));
        }
    }
}

// Initialize the verification handler
Asosiasi_Certificate_Verification::get_instance();
