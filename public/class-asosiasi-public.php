<?php
/**
 * Kelas untuk menangani fungsionalitas publik
 *
 * @package Asosiasi
 * @version 1.1.0
 */

class Asosiasi_Public {

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
    public function __construct($version = '1.0.0') {
        $this->version = $version;
        add_shortcode('asosiasi_member_list', array($this, 'display_member_list'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Register stylesheet untuk tampilan publik
     *
     * @since    1.1.0
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'asosiasi-public',
            ASOSIASI_URL . 'assets/css/public-style.css',
            array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register javascript untuk tampilan publik
     *
     * @since    1.1.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'asosiasi-public',
            ASOSIASI_URL . 'assets/js/public-script.js',
            array('jquery'),
            $this->version,
            true
        );

        wp_localize_script(
            'asosiasi-public',
            'asosiasiAjax',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('asosiasi-public-nonce')
            )
        );
    }

    /**
     * Shortcode untuk menampilkan daftar anggota
     *
     * @since    1.1.0
     * @param    array     $atts    Atribut shortcode
     * @return   string    HTML output
     */
    public function display_member_list($atts) {
        // Parse attributes
        $atts = shortcode_atts(
            array(
                'limit' => -1,
                'orderby' => 'company_name',
                'order' => 'ASC',
                'layout' => 'list' // list atau grid
            ),
            $atts,
            'asosiasi_member_list'
        );

        ob_start();
        
        $crud = new Asosiasi_CRUD();
        $members = $crud->get_members();

        // Load template
        require_once ASOSIASI_DIR . 'public/views/public-member-list.php';

        return ob_get_clean();
    }

    /**
     * Helper function untuk sanitize output
     *
     * @since    1.1.0
     * @param    string    $string    String yang akan di-sanitize
     * @return   string    String yang sudah di-sanitize
     */
    private function sanitize_output($string) {
        return esc_html(stripslashes($string));
    }
}