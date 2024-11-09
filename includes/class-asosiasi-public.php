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
        $this->init_hooks();
    }

    /**
     * Initialize all hooks
     * 
     * @since 1.1.0
     */
    private function init_hooks() {
        // Shortcodes
        add_shortcode('asosiasi_member_list', array($this, 'display_member_list'));
        
        // AJAX handlers if needed
        add_action('wp_ajax_nopriv_asosiasi_public_action', array($this, 'handle_public_ajax'));
        add_action('wp_ajax_asosiasi_public_action', array($this, 'handle_public_ajax'));
    }

    /**
     * Shortcode untuk menampilkan daftar anggota
     *
     * @since    1.1.0
     * @param    array     $atts    Atribut shortcode
     * @return   string    HTML output
     */
    public function display_member_list($atts) {
        // Sanitize and validate attributes
        $atts = shortcode_atts(
            array(
                'limit' => -1,
                'orderby' => 'company_name',
                'order' => 'ASC',
                'layout' => 'list',
                'services' => '',  // Filter by services
                'search' => '',    // Search term
            ),
            $atts,
            'asosiasi_member_list'
        );

        // Validate input
        $atts['limit'] = intval($atts['limit']);
        $atts['order'] = in_array(strtoupper($atts['order']), array('ASC', 'DESC')) ? strtoupper($atts['order']) : 'ASC';
        $atts['layout'] = in_array($atts['layout'], array('list', 'grid')) ? $atts['layout'] : 'list';
        
        ob_start();

        try {
            $crud = new Asosiasi_CRUD();
            $members = $crud->get_members($atts);

            if (empty($members)) {
                throw new Exception(__('No members found.', 'asosiasi'));
            }

            // Load template
            $template_path = ASOSIASI_DIR . 'public/views/public-member-list.php';
            
            if (!file_exists($template_path)) {
                throw new Exception(__('Template file not found.', 'asosiasi'));
            }

            include $template_path;

        } catch (Exception $e) {
            if (WP_DEBUG) {
                error_log('Asosiasi Plugin Error: ' . $e->getMessage());
            }
            echo '<div class="asosiasi-error">' . esc_html($e->getMessage()) . '</div>';
        }

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