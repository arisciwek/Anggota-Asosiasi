<?php
/**
* Class untuk menangani enqueue Member
*
* @package Asosiasi
* @version 1.0.3
* Path: includes/class-asosiasi-enqueue-member.php
* 
* Changelog:
* 1.0.3 - 2024-11-19 15:35 WIB
* - Added member-form-style.css and member-images-style.css for edit photos page
* 
* 1.0.2 - Added dashboard-style.css for main dashboard
* 1.0.1 - Removed unused file enqueues
*/

class Asosiasi_Enqueue_Member {
   private $version;
   private $allowed_pages = array(
       'asosiasi-add-member',
       'asosiasi',
       'asosiasi-view-member',
       'asosiasi-edit-photos'
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

       // Style untuk halaman dashboard utama 
       if ($_GET['page'] === 'asosiasi') {
           wp_enqueue_style(
               'asosiasi-dashboard',
               ASOSIASI_URL . 'admin/css/dashboard-style.css',
               array(),
               $this->version
           );
       }

       // Style untuk halaman add edit member
       if ($_GET['page'] === 'asosiasi-add-member') {
           wp_enqueue_style(
               'asosiasi-member-form',
               ASOSIASI_URL . 'admin/css/member-form-style.css',
               array(),
               $this->version
           );
       }

       // Style untuk halaman edit foto
       if ($_GET['page'] === 'asosiasi-edit-photos') {
           wp_enqueue_style(
               'asosiasi-member-images', 
               ASOSIASI_URL . 'admin/css/member-images-style.css',
               array(),
               $this->version
           );
       }

       // Style untuk halaman edit foto
       if ($_GET['page'] === 'asosiasi-view-member') {
           wp_enqueue_style(
               'asosiasi-member-images', 
               ASOSIASI_URL . 'admin/css/view-member-style.css',
               array(),
               $this->version
           );
       }

       wp_localize_script(
           'asosiasi-admin-global',
           'asosiasiMember',
           array(
               'ajaxurl' => admin_url('admin-ajax.php'),
               'memberNonce' => wp_create_nonce('asosiasi_member_nonce'),
               'strings' => array(
                   'savingMember' => __('Menyimpan data anggota...', 'asosiasi'),
                   'memberSaved' => __('Data anggota berhasil disimpan', 'asosiasi'),
                   'saveError' => __('Gagal menyimpan data anggota', 'asosiasi'),
                   'uploadingPhoto' => __('Mengunggah foto...', 'asosiasi'),
                   'photoUploaded' => __('Foto berhasil diunggah', 'asosiasi'),
                   'uploadError' => __('Gagal mengunggah foto', 'asosiasi'),
                   'deletingPhoto' => __('Menghapus foto...', 'asosiasi'),
                   'photoDeleted' => __('Foto berhasil dihapus', 'asosiasi'),
                   'deleteError' => __('Gagal menghapus foto', 'asosiasi'),
                   'confirmDelete' => __('Yakin ingin menghapus foto ini?', 'asosiasi')
               )
           )
       );
   }
}
