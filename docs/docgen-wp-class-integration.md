# DocGen WPClass Integration

1. Pertama, kita perlu memastikan bahwa class DocGen_WPClass_Settings_Page terintegrasi dengan benar. Di file utama plugin atau di file yang menyertakan dependensis.

Tambahkan kode berikut di file tersebut setelah require class-dwpc.php:

```php
// DocGen Implementation
require_once ASOSIASI_DIR . 'includes/class-dwpc.php';
require_once ASOSIASI_DIR . 'admin/class-dwpc-admin-menu.php';
require_once ASOSIASI_DIR . 'admin/class-dwpc-admin-page.php';
require_once ASOSIASI_DIR . 'admin/class-dwpc-settings-page.php';
require_once ASOSIASI_DIR . 'admin/class-dwpc-directory-handler.php';
```

2. Modifikasi fungsi run_asosiasi() untuk menginisialisasi menu DocGen:

```php
function run_asosiasi() {
    // Initialize main plugin class
    $plugin = new Asosiasi();
    
    // Initialize settings handler
    new Asosiasi_Settings();
    
    // Initialize DocGen WPClass Admin Menu
    DocGen_WPClass_Admin_Menu::get_instance();
    
    // Initialize context-specific enqueuers 
    new Asosiasi_Enqueue_Member(ASOSIASI_VERSION);
    new Asosiasi_Enqueue_Settings(ASOSIASI_VERSION);
    new Asosiasi_Enqueue_SKP_Perusahaan(ASOSIASI_VERSION);
    
    // Initialize AJAX handlers
    new Asosiasi_Ajax_Perusahaan();
    new Asosiasi_Ajax_Status_Skp_Perusahaan();

    // Generate Sertifikat 
    new Asosiasi_Enqueue_Certificate(ASOSIASI_VERSION);
    
    // Run the plugin
    $plugin->run();

    // Load SKP functionality if needed
    if (is_admin() && class_exists('Asosiasi_SKP_Cron')) {
        Asosiasi_SKP_Cron::schedule_events();
    }
}
```

3. Modifikasi class-dwpc-admin-menu.php untuk memastikan menu settings terintegrasi dengan benar:

```php
public function register_menus() {
    // Add main menu
    add_menu_page(
        __('DocGen Implementation', 'docgen-implementation'),
        __('DocGen Impl', 'docgen-implementation'),
        'manage_options',
        $this->parent_slug,
        array($this->dashboard_page, 'render'),
        'dashicons-media-document',
        30
    );

    // Add dashboard submenu
    add_submenu_page(
        $this->parent_slug,
        __('Dashboard', 'docgen-implementation'),
        __('Dashboard', 'docgen-implementation'),
        'manage_options',
        $this->parent_slug,
        array($this->dashboard_page, 'render')
    );

    // Add settings submenu
    add_submenu_page(
        $this->parent_slug,
        __('Settings', 'docgen-implementation'),
        __('Settings', 'docgen-implementation'),
        'manage_options',
        $this->parent_slug . '-settings',
        array($this->settings_page, 'render')
    );

    do_action('docgen_wpclass_register_admin_menu');
}
```

4. Pastikan juga struktur direktori sudah benar:

```
asosiasi/
├── admin/
│   ├── class-dwpc-admin-menu.php
│   ├── class-dwpc-admin-page.php
│   ├── class-dwpc-settings-page.php
│   ├── class-dwpc-directory-handler.php
│   └── views/
│       ├── dip-directory-settings.php
│       ├── dip-format-settings.php
│       └── dip-template-settings.php
└── includes/
    └── class-dwpc.php
```

5. Tambahkan CSS yang diperlukan. Buat file baru `assets/css/dwpc-admin.css`.

```css
.nav-tab-wrapper {
    margin-bottom: 20px;
}

.settings-section {
    display: none;
}

.settings-section.active {
    display: block;
}

.form-table th {
    width: 200px;
}
```

Pastikan CSS ini dimuat dengan menambahkan di class-dwpc-settings-page.php:

```php
protected function enqueue_page_assets() {
    wp_enqueue_style(
        'docgen-admin',
        plugin_dir_url(dirname(__FILE__)) . 'assets/css/dwpc-admin.css',
        array(),
        DOCGEN_WPCLASS_VERSION
    );
}
```

Dengan perubahan-perubahan ini, menu settings dari DocGen Implementation WPClass seharusnya muncul dengan benar dan tab-tabnya berfungsi sebagaimana mestinya.
