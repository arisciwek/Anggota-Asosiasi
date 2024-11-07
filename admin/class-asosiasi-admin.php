<?php
/**
 * Kelas untuk menangani fungsionalitas admin
 *
 * @package Asosiasi
 * @version 1.1.0
 */

class Asosiasi_Admin {

    /**
     * Version plugin
     *
     * @since    1.1.0
     * @access   private
     * @var      string    $version    Version plugin
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.1.0
     * @param    string    $version    Version plugin
     */
    public function __construct($version) {
        $this->version = $version;
    }

    /**
     * Register menu dan submenu plugin di admin
     *
     * @since    1.1.0
     */
    public function add_plugin_admin_menu() {
        add_menu_page(
            __('Asosiasi Settings', 'asosiasi'),
            __('Asosiasi', 'asosiasi'),
            'manage_options',
            'asosiasi',
            array($this, 'display_admin_dashboard'),
            'dashicons-groups',
            6
        );

        add_submenu_page(
            'asosiasi',
            __('Dashboard', 'asosiasi'),
            __('Dashboard', 'asosiasi'),
            'manage_options',
            'asosiasi',
            array($this, 'display_admin_dashboard')
        );

        add_submenu_page(
            'asosiasi',
            __('Daftar Anggota', 'asosiasi'),
            __('Daftar Anggota', 'asosiasi'),
            'manage_options',
            'asosiasi-list-members',
            array($this, 'display_list_members_page')
        );

        add_submenu_page(
            'asosiasi',
            __('Tambah Anggota', 'asosiasi'),
            __('Tambah Anggota', 'asosiasi'),
            'manage_options',
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
    }

    /**
     * Register pengaturan plugin
     *
     * @since    1.1.0
     */
    public function register_settings() {
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
            'asosiasi_contact_email',
            array(
                'type' => 'string',
                'sanitize_callback' => 'sanitize_email',
                'default' => ''
            )
        );
    }

    /**
     * Display dashboard page
     *
     * @since    1.1.0
     */
    public function display_admin_dashboard() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'asosiasi'));
        }
        require_once ASOSIASI_DIR . 'admin/views/admin-menu-page.php';
    }

    /**
     * Display member list page
     *
     * @since    1.1.0
     */
    public function display_list_members_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'asosiasi'));
        }
        require_once ASOSIASI_DIR . 'admin/views/admin-list-members-page.php';
    }

    /**
     * Display add member page
     *
     * @since    1.1.0
     */
    public function display_add_member_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'asosiasi'));
        }
        require_once ASOSIASI_DIR . 'admin/views/admin-add-member-page.php';
    }

    /**
     * Display settings page
     *
     * @since    1.1.0
     */
    public function display_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'asosiasi'));
        }
        require_once ASOSIASI_DIR . 'admin/views/admin-settings-page.php';
    }
}