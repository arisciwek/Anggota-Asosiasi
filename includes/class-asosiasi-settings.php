<?php
/**
 * Class untuk mengelola pengaturan plugin
 *
 * @package Asosiasi
 * @version 1.0.0
 * Path: includes/class-asosiasi-settings.php
 * 
 * Changelog:
 * 1.0.0 - 2024-11-19 15:45 WIB
 * - Initial release
 * - Added organization settings registration
 * - Added field sanitization
 */

defined('ABSPATH') || exit;

class Asosiasi_Settings {
    
    public function __construct() {
        $this->init();
    }

    public function init() {
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Register all plugin settings
     */
    public function register_settings() {
        // Register organization settings
        register_setting(
            'asosiasi_settings_group',
            'asosiasi_organization_name',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        register_setting(
            'asosiasi_settings_group',
            'asosiasi_ketua_umum',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        register_setting(
            'asosiasi_settings_group',
            'asosiasi_sekretaris_umum',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );

        register_setting(
            'asosiasi_settings_group',
            'asosiasi_contact_email',
            array(
                'type' => 'string',
                'sanitize_callback' => array($this, 'sanitize_email_field'),
                'default' => ''
            )
        );
            
        // Certificate settings
        register_setting(
            'asosiasi_settings_group',
            'asosiasi_certificate_header',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );
        
        register_setting(
            'asosiasi_settings_group', 
            'asosiasi_certificate_footer',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => ''
            )
        );
    }

    /**
     * Sanitize email field
     * 
     * @param string $input Email input to sanitize
     * @return string Sanitized email
     */
    public function sanitize_email_field($input) {
        $email = sanitize_email($input);
        
        if (!is_email($email)) {
            add_settings_error(
                'asosiasi_contact_email',
                'invalid_email',
                __('Format email tidak valid', 'asosiasi')
            );
            // Return old value if invalid
            return get_option('asosiasi_contact_email'); 
        }

        return $email;
    }

    /**
     * Get plugin settings
     * 
     * @return array Array of all plugin settings
     */
    public static function get_settings() {
        return array(
            'organization_name' => get_option('asosiasi_organization_name', ''),
            'ketua_umum' => get_option('asosiasi_ketua_umum', ''),
            'sekretaris_umum' => get_option('asosiasi_sekretaris_umum', ''), 
            'contact_email' => get_option('asosiasi_contact_email', '')
        );
    }
}