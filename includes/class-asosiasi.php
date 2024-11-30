<?php
/**
 * File inti plugin yang menangani semua fungsionalitas utama
 * 
 * @package Asosiasi
 * @version 1.2.0
 */

class Asosiasi {
    protected $version;

    public function __construct() {
        $this->version = ASOSIASI_VERSION;
    }

    public function run() {
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function define_admin_hooks() {
        //$plugin_admin = new Asosiasi_Admin($this->version);
        //add_action('admin_menu', array($plugin_admin, 'add_plugin_admin_menu'));
        //add_action('admin_init', array($plugin_admin, 'register_settings'));
        $plugin_admin = Asosiasi_Admin::get_instance($this->version);
    }
    
    /*
    private function define_public_hooks() {
        $plugin_public = new Asosiasi_Public($this->version);
        add_shortcode('asosiasi_member_list', array($plugin_public, 'display_member_list'));
    }
    */

    private function define_public_hooks() {
        $plugin_public = new Asosiasi_Public($this->version);
        add_shortcode('asosiasi_member_list', array($plugin_public, 'display_member_list'));
    }
    public function display_admin_dashboard() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'asosiasi'));
        }

        require_once ASOSIASI_DIR . 'admin/views/admin-menu-page.php';
    }
}
