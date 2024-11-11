<?php
/**
 * Kelas untuk menangani fungsionalitas admin
 *
 * @package Asosiasi
 * @version 2.1.0
 * Path: includes/class-asosiasi-admin.php
 * 
 * Changelog:
 * 2.1.0 - 2024-03-14
 * - Added edit photos page registration
 * - Added hidden submenu for editing member photos
 * 2.0.0 - Initial version
 */

class Asosiasi_Admin {
    private $version;
    private $plugin_name;

    public function __construct($version) {
        $this->version = $version;
        $this->plugin_name = 'asosiasi';
        
        // Instead of checking capabilities here, we'll hook into WordPress init
        add_action('init', array($this, 'init_plugin'));
        add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
    }

    /**
     * Initialize plugin functionality after WordPress is fully loaded
     */
    public function init_plugin() {
        // Now it's safe to load dependencies
        $this->load_dependencies();
    }

    private function load_dependencies() {
        // Load any required dependencies here
    }

    public function add_plugin_admin_menu() {
        // Check user capabilities before adding menu items
        if (!current_user_can('manage_options')) {
            return;
        }

        // Menu utama - sekarang menggabungkan dashboard dan daftar anggota
        add_menu_page(
            __('Asosiasi', 'asosiasi'),
            __('Asosiasi', 'asosiasi'),
            'manage_options',
            'asosiasi',
            array($this, 'display_admin_dashboard'),
            'dashicons-groups',
            6
        );

        // Submenu Tambah Anggota
        add_submenu_page(
            'asosiasi',
            __('Tambah Anggota', 'asosiasi'),
            __('Tambah Anggota', 'asosiasi'),
            'manage_options',
            'asosiasi-add-member',
            array($this, 'display_add_member_page')
        );

        // Submenu View Anggota (hidden)
        add_submenu_page(
            null,
            __('Detail Anggota', 'asosiasi'),
            __('Detail Anggota', 'asosiasi'),
            'manage_options',
            'asosiasi-view-member',
            array($this, 'display_view_member_page')
        );

        // Submenu Edit Photos (hidden) - New
        add_submenu_page(
            null,
            __('Edit Member Photos', 'asosiasi'),
            __('Edit Member Photos', 'asosiasi'),
            'manage_options',
            'asosiasi-edit-photos',
            array($this, 'display_edit_photos_page')
        );

        // Submenu Pengaturan
        add_submenu_page(
            'asosiasi',
            __('Pengaturan', 'asosiasi'),
            __('Pengaturan', 'asosiasi'),
            'manage_options',
            'asosiasi-settings',
            array($this, 'display_settings_page')
        );
    }

    public function display_admin_dashboard() {
        // Verify user capabilities before displaying content
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'asosiasi'));
        }
        
        require_once ASOSIASI_DIR . 'admin/views/admin-menu-page.php';
    }

    public function display_add_member_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'asosiasi'));
        }
        
        require_once ASOSIASI_DIR . 'admin/views/admin-add-member-page.php';
    }

    public function display_view_member_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'asosiasi'));
        }
        
        require_once ASOSIASI_DIR . 'admin/views/admin-view-member-page.php';
    }

    public function display_edit_photos_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'asosiasi'));
        }
        
        require_once ASOSIASI_DIR . 'admin/views/admin-edit-member-images.php';
    }

    public function display_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'asosiasi'));
        }
        
        require_once ASOSIASI_DIR . 'admin/views/admin-settings-page.php';
    }

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
}