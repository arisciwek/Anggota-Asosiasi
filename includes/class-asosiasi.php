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
        $plugin_admin = new Asosiasi_Admin($this->version);
        add_action('admin_menu', array($plugin_admin, 'add_plugin_admin_menu'));
        add_action('admin_init', array($plugin_admin, 'register_settings'));
    }

    private function define_public_hooks() {
        $plugin_public = new Asosiasi_Public($this->version);
        add_shortcode('asosiasi_member_list', array($plugin_public, 'display_member_list'));
    }
}