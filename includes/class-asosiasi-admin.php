<?php
/**
 * Admin-specific functionality
 *
 * @package Asosiasi
 * @version 2.2.0
 * Path: includes/class-asosiasi-admin.php
 * 
 * Changelog:
 * 2.2.0 - 2024-11-19 17:00 WIB
 * - Added settings property for integration with Asosiasi_Settings
 * - Declared property in class definition for PHP 8.2 compatibility
 * - Maintained existing register_settings method
 * 
 * 2.1.0 - 2024-03-13
 * - Added image management support
 * - Improved admin notices
 * 
 * 2.0.0 - Initial version with member management
 */

class Asosiasi_Admin {

    private static $instance = null;
    /**
     * The current version of the plugin.
     *
     * @var string
     */
    private $version;

    /**
     * The settings handler instance.
     *
     * @var Asosiasi_Settings
     */
    private $settings;

    public static function get_instance($version) {
        if (null === self::$instance) {
            self::$instance = new self($version);
        }
        return self::$instance;
    }

    public function __construct($version) {
        $this->version = $version;
        $this->settings = new Asosiasi_Settings();
        $this->init();
    }

    public function init() {
        add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

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
                'sanitize_callback' => 'sanitize_email',
                'default' => ''
            )
        );
    }

    public function add_plugin_admin_menu() {
        add_menu_page(
            __('Asosiasi', 'asosiasi'),
            __('Asosiasi', 'asosiasi'),
            'list_asosiasi_members', // Capability yang diperlukan
            'asosiasi',
            array($this, 'display_admin_page'),
            'dashicons-groups',
            30
        );

        add_submenu_page(
            'asosiasi',
            __('Daftar Anggota', 'asosiasi'),
            __('Daftar Anggota', 'asosiasi'),
            'list_asosiasi_members', // Capability yang diperlukan
            'asosiasi',
            array($this, 'display_admin_page')
        );

        add_submenu_page(
            'asosiasi',
            __('Tambah Anggota', 'asosiasi'),
            __('Tambah Anggota', 'asosiasi'),
            'add_asosiasi_members',
            'asosiasi-add-member',
            array($this, 'display_add_member_page')
        );

        add_submenu_page(
            'asosiasi',
            __('Pengaturan', 'asosiasi'),
            __('Pengaturan', 'asosiasi'),
            'manage_options',
            'asosiasi-settings',
            array($this, 'display_settings_page')
        );

        // Hidden pages
        add_submenu_page(
            null,
            __('Lihat Anggota', 'asosiasi'),
            __('Lihat Anggota', 'asosiasi'),
            'view_asosiasi_members',
            'asosiasi-view-member',
            array($this, 'display_view_member_page')
        );

        add_submenu_page(
            null,
            __('Edit Foto', 'asosiasi'),
            __('Edit Foto', 'asosiasi'),
            'edit_own_asosiasi_members',
            'asosiasi-edit-photos',
            array($this, 'display_edit_photos_page')
        );
    }

    public function display_admin_page() {
        require_once ASOSIASI_DIR . 'admin/views/admin-menu-page.php';
    }

    public function display_add_member_page() {
        require_once ASOSIASI_DIR . 'admin/views/admin-add-member-page.php';
    }

    public function display_view_member_page() {
        require_once ASOSIASI_DIR . 'admin/views/admin-view-member-page.php';
    }

    public function display_edit_photos_page() {
        require_once ASOSIASI_DIR . 'admin/views/admin-edit-member-images.php';
    }

    public function display_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Anda tidak memiliki izin untuk mengakses halaman ini.', 'asosiasi'));
        }
        require_once ASOSIASI_DIR . 'admin/views/admin-settings-page.php';
    }
}
