<?php
/**
 * Class untuk menangani enqueue SKP Tenaga Ahli
 *
 * @package     Asosiasi
 * @subpackage  Includes/Enqueue 
 * @version     1.0.0
 * @author      arisciwek
 *
 * Path: includes/class-asosiasi-enqueue-skp-tenaga-ahli.php
 *
 * Description: Menangani loading assets khusus untuk 
 *              SKP Tenaga Ahli di halaman member view
 *
 * Changelog:
 * 1.0.0 - 2024-11-22
 * - Initial creation
 * - Added tenaga ahli specific styles and scripts
 * - Added script localization
 */

class Asosiasi_Enqueue_SKP_Tenaga_Ahli {
    private $version;
    private $allowed_pages = array(
        'asosiasi-view-member'
    );

    public function __construct($version) {
        $this->version = $version;
        $this->init();
    }

    public function init() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
    }

    public function enqueue_assets($hook) {
        
        if (!isset($_GET['page']) || !in_array($_GET['page'], $this->allowed_pages)) {
            return;
        }

        // SKP styles
        wp_enqueue_style(
            'asosiasi-skp-tenaga-ahli',
            ASOSIASI_URL . 'assets/css/skp-tenaga-ahli/skp-tenaga-ahli.css',
            array(),
            $this->version
        );

        // SKP scripts in correct order with proper dependencies
        wp_enqueue_script(
            'asosiasi-skp-utils',
            ASOSIASI_URL . 'assets/js/skp-perusahaan/skp-perusahaan-utils.js',
            array('jquery'),
            $this->version,
            true
        );


        wp_enqueue_script(
            'asosiasi-skp-tenaga-ahli',
            ASOSIASI_URL . 'assets/js/skp-tenaga-ahli/skp-tenaga-ahli.js',
            array('jquery', 'asosiasi-skp-utils'),
            $this->version,
            true
        );

        wp_enqueue_script(
            'asosiasi-skp-tenaga-ahli-modal',
            ASOSIASI_URL . 'assets/js/skp-tenaga-ahli/skp-tenaga-ahli-modal.js',
            array('jquery', 'asosiasi-skp-utils', 'asosiasi-skp-tenaga-ahli'),
            $this->version,
            true
        );
        
        wp_enqueue_script( 'asosiasi-skp-tenaga-ahli-utils', 
            ASOSIASI_URL . 'assets/js/skp-tenaga-ahli/skp-tenaga-ahli-utils.js', 
            array( 'jquery' ), 
            ASOSIASI_VERSION, 
            true 
        );

        // Load utils SEBELUM file lain yang membutuhkannya
        wp_enqueue_script( 'asosiasi-skp-tenaga-ahli-status', 
            ASOSIASI_URL . 'assets/js/skp-tenaga-ahli/skp-tenaga-ahli-status.js',
            array( 'jquery', 'asosiasi-skp-tenaga-ahli-utils' ),
            ASOSIASI_VERSION, 
            true 
        );

        // Localize script
        wp_localize_script(
            'asosiasi-skp-tenaga-ahli',
            'asosiasiSKPTenagaAhli',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'skpNonce' => wp_create_nonce('asosiasi_skp_tenaga_ahli_nonce'),
                'strings' => array(
                    'loading' => __('Memuat data SKP...', 'asosiasi'),
                    'noActiveSKP' => __('Tidak ada SKP aktif', 'asosiasi'),
                    'noInactiveSKP' => __('Tidak ada SKP tidak aktif', 'asosiasi'),
                    'addTitle' => __('Tambah SKP Tenaga Ahli', 'asosiasi'),
                    'editTitle' => __('Edit SKP Tenaga Ahli', 'asosiasi'),
                    'save' => __('Simpan SKP', 'asosiasi'),
                    'update' => __('Update SKP', 'asosiasi'),
                    'saving' => __('Menyimpan...', 'asosiasi'),
                    'adding' => __('Menambahkan...', 'asosiasi'),
                    'confirmDelete' => __('Yakin ingin menghapus SKP ini?', 'asosiasi'),
                    'saveError' => __('Gagal menyimpan SKP', 'asosiasi'),
                    'deleteError' => __('Gagal menghapus SKP', 'asosiasi'),
                    'loadError' => __('Gagal memuat data SKP', 'asosiasi'),
                    'statusChangeError' => __('Gagal mengubah status SKP', 'asosiasi'),
                    'selectStatus' => __('Pilih Status', 'asosiasi'),
                    'changeStatus' => __('Ubah Status', 'asosiasi'),
                    'statusChangeSuccess' => __('Status SKP berhasil diubah', 'asosiasi'),
                    'statusChangeConfirm' => __('Yakin ingin mengubah status SKP ini?', 'asosiasi'),
                    'currentFile' => __('File saat ini:', 'asosiasi'),
                    'view' => __('Lihat PDF', 'asosiasi'),
                    'edit' => __('Edit', 'asosiasi'),
                    'delete' => __('Hapus', 'asosiasi'),
                    'fieldRequired' => __('Field %s wajib diisi', 'asosiasi')
                )
            )
        );
    }
}
