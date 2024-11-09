<?php
/**
 * Class untuk menangani semua enqueue scripts dan styles
 *
 * @package Asosiasi
 * @version 1.0.0
 */

class Asosiasi_Enqueue {
    /**
     * Version plugin
     */
    private $version;

    /**
     * Initialize the class
     */
    public function __construct($version) {
        $this->version = $version;
        $this->init();
    }

    /**
     * Initialize hooks
     */
    public function init() {
        // Admin assets
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // Public assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_public_assets'));
    }

    /**
     * Register dan enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Global admin styles
        wp_enqueue_style(
            'asosiasi-admin-global',
            ASOSIASI_URL . 'assets/css/admin-style.css',
            array(),
            $this->version
        );

        // Dashboard specific styles
        if (strpos($hook, 'asosiasi') !== false) {
            wp_enqueue_style(
                'asosiasi-dashboard',
                ASOSIASI_URL . 'admin/css/dashboard-style.css',
                array(),
                $this->version
            );

            // Dashboard specific scripts
            wp_enqueue_script(
                'asosiasi-dashboard',
                ASOSIASI_URL . 'admin/js/dashboard-script.js',
                array('jquery'),
                $this->version,
                true
            );

            // SKP Perusahaan assets
            if ($this->is_member_view_page()) {
                wp_enqueue_style(
                    'asosiasi-skp-perusahaan',
                    ASOSIASI_URL . 'assets/css/skp-perusahaan.css',
                    array(),
                    $this->version
                );

                wp_enqueue_script(
                    'asosiasi-skp-perusahaan',
                    ASOSIASI_URL . 'assets/js/skp-perusahaan.js',
                    array('jquery'),
                    $this->version,
                    true
                );
            }
        }

        // Global admin scripts
        wp_enqueue_script(
            'asosiasi-admin-global',
            ASOSIASI_URL . 'assets/js/admin-script.js',
            array('jquery'),
            $this->version,
            true
        );

        // Localize admin scripts
        wp_localize_script(
            'asosiasi-admin-global',
            'asosiasiAdmin',
            $this->get_admin_localize_data()
        );
    }

    /**
     * Register dan enqueue public assets
     */
    public function enqueue_public_assets() {
        $min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
        
        // Styles
        wp_enqueue_style(
            'asosiasi-public',
            ASOSIASI_URL . "public/css/asosiasi-public{$min}.css",
            array(),
            $this->version,
            'all'
        );

        // Scripts
        wp_enqueue_script(
            'asosiasi-public',
            ASOSIASI_URL . "public/js/asosiasi-public{$min}.js",
            array('jquery'),
            $this->version,
            true
        );

        // Localize public scripts
        wp_localize_script(
            'asosiasi-public',
            'asosiasiPublic',
            $this->get_public_localize_data()
        );
    }

    /**
     * Get admin localize data
     */
    private function get_admin_localize_data() {
        return array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('asosiasi_admin_nonce'),
            'skpNonce' => wp_create_nonce('asosiasi_skp_nonce'),
            'strings' => array(
                'confirmDelete' => __('Yakin ingin menghapus?', 'asosiasi'),
                'deletingMember' => __('Menghapus anggota...', 'asosiasi'),
                'memberDeleted' => __('Anggota berhasil dihapus', 'asosiasi'),
                'error' => __('Terjadi kesalahan', 'asosiasi'),
                'loading' => __('Loading SKP data...', 'asosiasi'),
                'saving' => __('Menyimpan...', 'asosiasi'),
                'save' => __('Simpan', 'asosiasi'),
                'edit' => __('Edit', 'asosiasi'),
                'delete' => __('Hapus', 'asosiasi'),
                'view' => __('Lihat', 'asosiasi'),
                'noSKP' => __('Belum ada SKP yang terdaftar', 'asosiasi'),
                'saveError' => __('Gagal menyimpan SKP', 'asosiasi'),
                'deleteError' => __('Gagal menghapus SKP', 'asosiasi'),
                'loadError' => __('Gagal memuat data SKP', 'asosiasi')
            )
        );
    }

    /**
     * Get public localize data
     */
    private function get_public_localize_data() {
        return array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('asosiasi-public-nonce'),
            'strings' => array(
                'loadingText' => __('Loading...', 'asosiasi'),
                'errorText' => __('Something went wrong. Please try again.', 'asosiasi'),
                'noResults' => __('No members found.', 'asosiasi')
            )
        );
    }

    /**
     * Check if current page is member view
     */
    private function is_member_view_page() {
        global $pagenow;
        return $pagenow === 'admin.php' && 
               isset($_GET['page']) && 
               $_GET['page'] === 'asosiasi-view-member';
    }
    // Add this to your asosiasi.php
	function asosiasi_enqueue_skp_assets() {
	    // Only load on member view page
	    $screen = get_current_screen();
	    if ($screen && $screen->base === 'admin_page_asosiasi-view-member') {
	        
	        wp_enqueue_style(
	            'asosiasi-skp-perusahaan',
	            ASOSIASI_URL . 'assets/css/skp-perusahaan.css',
	            array(),
	            ASOSIASI_VERSION
	        );

	        wp_enqueue_script(
	            'asosiasi-skp-perusahaan',
	            ASOSIASI_URL . 'assets/js/skp-perusahaan.js',
	            array('jquery'),
	            ASOSIASI_VERSION,
	            true
	        );

	        // Localize script with proper nonce
	        wp_localize_script(
	            'asosiasi-skp-perusahaan',
	            'asosiasiAdmin',
	            array(
	                'ajaxurl' => admin_url('admin-ajax.php'),
	                'skpNonce' => wp_create_nonce('asosiasi_skp_nonce'),
	                'strings' => array(
	                    'loading' => __('Loading SKP data...', 'asosiasi'),
	                    'noSKP' => __('No SKP found', 'asosiasi'),
	                    'confirmDelete' => __('Are you sure you want to delete this SKP?', 'asosiasi'),
	                    'saveError' => __('Failed to save SKP', 'asosiasi'),
	                    'deleteError' => __('Failed to delete SKP', 'asosiasi'),
	                    'loadError' => __('Failed to load SKP list', 'asosiasi'),
	                )
	            )
	        );
	    }
	add_action('admin_enqueue_scripts', 'asosiasi_enqueue_skp_assets');
	}
	
}