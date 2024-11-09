1. File yang perlu dipindahkan:
   - class-asosiasi.php → includes/class-asosiasi.php
   - class-asosiasi-activator.php → includes/class-asosiasi-activator.php
   - class-asosiasi-deactivator.php → includes/class-asosiasi-deactivator.php
   - class-asosiasi-crud.php → includes/class-asosiasi-crud.php
   - class-asosiasi-admin.php → admin/class-asosiasi-admin.php
   - class-asosiasi-public.php → public/class-asosiasi-public.php
   - asosiasi-public.css → public/css/asosiasi-public.css
   - asosiasi-public.js → public/js/asosiasi-public.js
   - public-member-list.php → public/views/public-member-list.php
   - admin-menu-page.php → admin/views/admin-menu-page.php
   - admin-list-members-page.php → admin/views/admin-list-members-page.php
   - admin-settings-page.php → admin/views/admin-settings-page.php

2. File yang perlu dibuat:
   - assets/css/admin-style.css
   - assets/css/public-style.css
   - assets/js/admin-script.js
   - assets/js/public-script.js
   - assets/images/logo.png
   - readme.txt

3. Path yang perlu diperbarui dalam file:
   - asosiasi.php: Update semua require_once paths
   - class-asosiasi-admin.php: Update include paths untuk view files
   - class-asosiasi.php: Update include paths


Mari saya telusuri file-file yang memiliki deklarasi enqueue berdasarkan tree.md:

1. class-asosiasi-admin.php (v1.1.0)
```markdown
public function enqueue_admin_assets()
- Mengenqueue admin-global.css
- Mengenqueue admin-global.js 
```

2. class-asosiasi-public.php (v1.1.0)
```markdown
public function enqueue_styles()
- Mengenqueue public-style.css

public function enqueue_scripts() 
- Mengenqueue public-script.js
```

3. asosiasi.php
```markdown
function asosiasi_enqueue_scripts()
- Mengenqueue admin-style.css (untuk admin)
- Mengenqueue admin-script.js (untuk admin)
```

Sekarang saya akan buatkan class baru untuk centralized enqueue:



```php
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
    public function enqueue_admin_assets() {
        // Global admin styles
        wp_enqueue_style(
            'asosiasi-admin-global',
            ASOSIASI_URL . 'assets/css/admin-style.css',
            array(),
            $this->version
        );

        // Dashboard specific styles
        wp_enqueue_style(
            'asosiasi-dashboard',
            ASOSIASI_URL . 'admin/css/dashboard-style.css',
            array(),
            $this->version
        );

        // Global admin scripts
        wp_enqueue_script(
            'asosiasi-admin-global',
            ASOSIASI_URL . 'assets/js/admin-script.js',
            array('jquery'),
            $this->version,
            true
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
        // Styles
        wp_enqueue_style(
            'asosiasi-public',
            ASOSIASI_URL . 'assets/css/public-style.css',
            array(),
            $this->version
        );

        // Scripts
        wp_enqueue_script(
            'asosiasi-public',
            ASOSIASI_URL . 'assets/js/public-script.js',
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
            'nonce' => wp_create_nonce('asosiasi-public-nonce')
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
}

```

Fitur-fitur dari class Asosiasi_Enqueue ini:

1. Centralized Management:
- Semua asset dikelola di satu tempat
- Terorganisir berdasarkan context (admin/public)
- Conditional loading untuk asset spesifik (contoh: SKP hanya di halaman member view)

2. Organized Localization:
- Data localize terpisah untuk admin dan public
- Mudah menambah/mengubah string dan konfigurasi
- Nonce management terpusat

3. Clean Structure:
- Methods yang jelas dan terpisah untuk setiap fungsi
- Helper methods untuk kondisi loading
- Mudah ditambah untuk fitur baru

4. Improved Maintainability:
- Satu tempat untuk update version
- Dependency management terpusat
- Lebih mudah debug masalah asset

Langkah selanjutnya setelah ini adalah:
1. Menempatkan file ini di struktur yang tepat
2. Menghapus enqueue dari file-file lama
3. Mengintegrasikan class ini ke plugin utama

Apakah Anda ingin melanjutkan dengan salah satu langkah tersebut?