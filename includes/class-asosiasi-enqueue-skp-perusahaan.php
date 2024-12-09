<?php
/**
* Class untuk menangani enqueue SKP Perusahaan
*
* @package Asosiasi
* @version 2.1.0
* Path: includes/class-asosiasi-enqueue-skp-perusahaan.php
* 
* Changelog:
* 2.1.0 - 2024-11-19
* - Menyesuaikan dengan pemecahan file JS dan penambahan namespace
* - Modifikasi dependency dan urutan load scripts
* - Optimasi lokalisasi strings ke file masing-masing
*/

class Asosiasi_Enqueue_SKP_Perusahaan {
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
           'asosiasi-skp-perusahaan',
           ASOSIASI_URL . 'assets/css/skp-perusahaan.css',
           array(),
           $this->version
       );

       wp_enqueue_style(
           'asosiasi-skp-modal',
           ASOSIASI_URL . 'assets/css/skp-modal.css',
           array(),
           $this->version
       );

       // Dashicons for PDF icon
       wp_enqueue_style('dashicons');

       // SKP scripts in correct order with proper dependencies
       wp_enqueue_script(
           'asosiasi-skp-utils',
           ASOSIASI_URL . 'assets/js/skp-perusahaan/skp-perusahaan-utils.js',
           array('jquery'),
           $this->version,
           true
       );

       wp_enqueue_script(
           'asosiasi-skp-perusahaan',
           ASOSIASI_URL . 'assets/js/skp-perusahaan/skp-perusahaan.js',
           array('jquery', 'asosiasi-skp-utils'),
           $this->version,
           true
       );

       wp_enqueue_script(
           'asosiasi-skp-perusahaan-modal',
           ASOSIASI_URL . 'assets/js/skp-perusahaan/skp-perusahaan-modal.js',
           array('jquery', 'asosiasi-skp-utils', 'asosiasi-skp-perusahaan'),
           $this->version,
           true
       );

       wp_enqueue_script(
           'asosiasi-skp-perusahaan-status',
           ASOSIASI_URL . 'assets/js/skp-perusahaan/skp-perusahaan-status.js',
           array('jquery', 'asosiasi-skp-utils', 'asosiasi-skp-perusahaan'),
           $this->version,
           true
       );
       
       wp_localize_script(
           'asosiasi-skp-perusahaan',
           'asosiasiSKPPerusahaan',
           array(
               'ajaxurl' => admin_url('admin-ajax.php'),
               'skpPerusahaanNonce' => wp_create_nonce('asosiasi_skp_perusahaan_nonce'),
               'strings' => array(
                   'loading' => __('Memuat data SKP...', 'asosiasi'),
                   'noSKP' => __('Belum ada SKP yang terdaftar', 'asosiasi'),
                   'addTitle' => __('Tambah SKP', 'asosiasi'),
                   'editTitle' => __('Edit SKP', 'asosiasi'),
                   'save' => __('Simpan SKP', 'asosiasi'),
                   'update' => __('Update SKP', 'asosiasi'),
                   'saving' => __('Menyimpan...', 'asosiasi'),
                   'confirmDelete' => __('Yakin ingin menghapus SKP ini?', 'asosiasi'),
                   'saveError' => __('Gagal menyimpan SKP', 'asosiasi'),
                   'deleteError' => __('Gagal menghapus SKP', 'asosiasi'),
                   'loadError' => __('Gagal memuat data SKP', 'asosiasi'),
                   'statusActive' => __('Aktif', 'asosiasi'),
                   'statusInactive' => __('Tidak Aktif', 'asosiasi'),
                   'statusExpired' => __('Kadaluarsa', 'asosiasi'),
                   'statusActivated' => __('Diaktifkan', 'asosiasi'),
                   'selectStatus' => __('Pilih Status', 'asosiasi'),
                   'changeStatus' => __('Ubah Status', 'asosiasi'),
                   'statusChangeSuccess' => __('Status SKP berhasil diubah', 'asosiasi'),
                   'statusChangeError' => __('Gagal mengubah status SKP', 'asosiasi'),
                   'statusChangeReason' => __('Alasan perubahan status', 'asosiasi'),
                   'statusChangeConfirm' => __('Yakin ingin mengubah status SKP ini?', 'asosiasi'),
                   'noActiveSKP' => __('Tidak ada SKP aktif', 'asosiasi'),
                   'noInactiveSKP' => __('Tidak ada SKP tidak aktif', 'asosiasi'),
                   'edit' => __('Edit', 'asosiasi'),
                   'delete' => __('Hapus', 'asosiasi'),
                   'view' => __('Lihat PDF', 'asosiasi'),
                   'dismissNotice' => __('Tutup notifikasi', 'asosiasi'),
                   'fieldRequired' => __('Field %s wajib diisi', 'asosiasi')
               )
           )
       );
   }
}


