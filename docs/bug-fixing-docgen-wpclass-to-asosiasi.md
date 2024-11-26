# Bug Fixing DocGen WPClass to Asosiasi

// DocGen Implementation
require_once ASOSIASI_DIR . 'includes/class-dwpc.php';
require_once ASOSIASI_DIR . 'admin/class-dwpc-admin-menu.php';
require_once ASOSIASI_DIR . 'admin/class-dwpc-admin-page.php';
require_once ASOSIASI_DIR . 'admin/class-dwpc-settings-page.php';
require_once ASOSIASI_DIR . 'admin/class-dwpc-directory-handler.php';
require_once ASOSIASI_DIR . 'admin/class-dwpc-directory-handler.php';

=============================
ada error:
[26-Nov-2024 05:08:34 UTC] PHP Fatal error:  Uncaught Error: Class "DocGen_WPClass_Directory_Migration" not found in /home/mkt01/Public/wppm/public_html/wp-content/plugins/asosiasi/admin/class-dwpc-admin-page.php:42

=============================
ada error:
[26-Nov-2024 05:16:18 UTC] PHP Deprecated:  Creation of dynamic property DocGen_WPClass_Settings_Page::$template_handler is deprecated in /home/mkt01/Public/wppm/public_html/wp-content/plugins/asosiasi/admin/class-dwpc-settings-page.php on line 66

class DocGen_WPClass_Settings_Page extends DocGen_WPClass_Admin_Page {
    /**
     * DirectoryHandler instance
     * @var DocGen_WPClass_Directory_Handler
     */
    private $directory_handler;

    /**
     * Template handler instance
     * @var DocGen_WPClass_Directory_Handler
     */
    private $template_handler;  // Tambahkan deklarasi properti ini

    /**
     * Constructor
     */
    public function __construct() {
        $this->page_slug = 'docgen-implementation-settings';
        $this->directory_handler = new DocGen_WPClass_Directory_Handler();
        
        // Initialize template handler
        $this->template_handler = new DocGen_WPClass_Directory_Handler();
        $this->template_handler->set_directory_type('Template Directory');
        
        parent::__construct();

        add_action('wp_ajax_upload_template', array($this, 'ajax_handle_template_upload'));
        add_action('wp_ajax_test_directory', array($this, 'ajax_test_directory'));
        add_action('wp_ajax_test_template_dir', array($this, 'ajax_test_template_dir'));
        add_action('wp_ajax_get_directory_stats', array($this, 'ajax_get_directory_stats'));
    }

    // ... rest of the code ...
}

