<?php
/**
 * Host DocGen Hooks Handler
 *
 * @package     Host_DocGen
 * @subpackage  Core
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: includes/docgen/class-host-docgen-hooks.php
 * 
 * Timestamp: 2024-12-01:01:03:23
 * 
 * Description:
 * Handler untuk hooks yang disediakan oleh DocGen Implementation.
 * Menyediakan hooks untuk extend dashboard, directory management,
 * dan template customization.
 * 
 * Filename Convention:
 * - Original  : class-host-docgen-hooks.php
 * - To Change : class-[plugin-name]-docgen-hooks.php
 * 
 * Class Name Convention:
 * - Original  : Host_DocGen_Hooks
 * - To Change : [Plugin_Name]_DocGen_Hooks
 * 
 * Dependencies:
 * - DocGen Implementation Plugin
 * - class-docgen-checker.php (untuk dependency check)
 * - class-host-docgen-adapter.php (untuk plugin integration)
 * 
 * @author     arisciwek
 * @copyright  2024 Host Organization
 * @license    GPL-2.0+
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class Host_DocGen_Hooks {
    private static $instance = null;

    // Declare these properties
    private $adapter;
    private $hooks_initialized = false; // Initialize with default value
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        
    }

    private function init_hooks() {
        // Dashboard Extensions
        add_action('docgen_implementation_before_dashboard_content', array($this, 'add_dashboard_header'));
        add_filter('docgen_implementation_dashboard_cards', array($this, 'add_host_cards'));

        // Directory Handling
        add_filter('docgen_implementation_directory_validators', array($this, 'add_directory_validators'));
        add_action('docgen_implementation_after_directory_created', array($this, 'setup_directory_structure'));
        
        // Template Handling  
        add_filter('docgen_implementation_template_validators', array($this, 'add_template_validators'));
        add_action('docgen_implementation_before_template_upload', array($this, 'prepare_template_upload'));
        add_filter('docgen_implementation_template_fields', array($this, 'add_template_fields'));
    }

    // Add adapter setter
    public function set_adapter($adapter) {
        $this->adapter = $adapter;
        if (!$this->hooks_initialized) {
            $this->init_hooks();
            $this->hooks_initialized = true;
        }
    }

    /**
     * Add dashboard header content
     */
    public function add_dashboard_header() {
        echo '<div class="host-docgen-header">';
        echo '<h2>' . esc_html__('Document Generation', 'host-docgen') . '</h2>';
        echo '<p>' . esc_html__('Generate and manage documents using DocGen Implementation.', 'host-docgen') . '</p>';
        echo '</div>';
    }

    /**
     * Get available templates 
     * @return array Templates list
     */
    private function get_templates() {
        $settings = get_option('docgen_implementation_settings', array());
        $template_dir = $settings['template_dir'] ?? '';
        
        if (empty($template_dir) || !is_dir($template_dir)) {
            return array();
        }

        return glob($template_dir . '/*.docx');
    }

    /**
     * Add custom directory validators
     * @param array $validators Existing validators
     * @return array Modified validators array
     */
    public function add_directory_validators($validators) {
        $validators[] = array($this, 'validate_directory_permissions');
        return $validators;
    }

    /**
     * Validate directory permissions
     * @param string $path Directory path
     * @return bool|WP_Error True or error object
     */
    public function validate_directory_permissions($path) {
        if (!is_writable($path)) {
            return new WP_Error(
                'invalid_permissions',
                __('Directory must be writable', 'host-docgen')
            );
        }
        return true;
    }

    /**
     * Setup directory structure after creation
     * @param string $path Directory path
     */
    public function setup_directory_structure($path) {
        // Create required subdirectories
        $subdirs = array('temp', 'output');
        foreach ($subdirs as $dir) {
            $subdir_path = trailingslashit($path) . $dir;
            if (!file_exists($subdir_path)) {
                wp_mkdir_p($subdir_path);
            }
        }

        // Add .htaccess for security
        $htaccess = trailingslashit($path) . '.htaccess';
        if (!file_exists($htaccess)) {
            $content = "Options -Indexes\n";
            $content .= "<FilesMatch '\.(php|php\.|php3|php4|php5|php7|phtml|pl|py|jsp|asp|htm|shtml|sh|cgi)$'>\n";
            $content .= "Order Deny,Allow\n";
            $content .= "Deny from all\n";
            $content .= "</FilesMatch>\n";
            
            file_put_contents($htaccess, $content);
        }
    }

    /**
     * Add custom template validators
     * @param array $validators Existing validators
     * @return array Modified validators array 
     */
    public function add_template_validators($validators) {
        $validators[] = array($this, 'validate_template_structure');
        $validators[] = array($this, 'validate_template_fields');
        return $validators;
    }

    /**
     * Validate template file structure
     * @param string $file Template file path
     * @return bool|WP_Error True or error object
     */
    public function validate_template_structure($file) {
        // Check if file is valid DOCX
        $zip = new ZipArchive();
        if ($zip->open($file) !== true) {
            return new WP_Error(
                'invalid_template',
                __('File is not a valid DOCX document', 'host-docgen')
            );
        }

        // Check for required files
        $required_files = array(
            '[Content_Types].xml',
            'word/document.xml'
        );

        foreach ($required_files as $required) {
            if ($zip->locateName($required) === false) {
                $zip->close();
                return new WP_Error(
                    'missing_component',
                    sprintf(__('Template missing required component: %s', 'host-docgen'), $required)
                );
            }
        }

        $zip->close();
        return true;
    }

    /**
     * Validate template merge fields
     * @param string $file Template file path
     * @return bool|WP_Error True or error object
     */
    public function validate_template_fields($file) {
        $content = file_get_contents($file);
        
        // Check for required merge fields
        $required_fields = array(
            'company_name',
            'issue_date',
            'document_number'
        );

        $missing_fields = array();
        foreach ($required_fields as $field) {
            if (strpos($content, '{{' . $field . '}}') === false) {
                $missing_fields[] = $field;
            }
        }

        if (!empty($missing_fields)) {
            return new WP_Error(
                'missing_fields',
                sprintf(
                    __('Template missing required fields: %s', 'host-docgen'),
                    implode(', ', $missing_fields)
                )
            );
        }

        return true;
    }

    /**
     * Prepare template upload
     * @param array $template Template data
     */
    public function prepare_template_upload($template) {
        // Create temp directory if needed
        $temp_dir = sys_get_temp_dir() . '/host-docgen-temp';
        if (!file_exists($temp_dir)) {
            wp_mkdir_p($temp_dir);
        }

        // Cleanup old temp files
        $files = glob($temp_dir . '/*');
        foreach ($files as $file) {
            if (is_file($file) && time() - filemtime($file) > 3600) {
                @unlink($file);
            }
        }
    }

    /**
     * Add custom template fields
     * @param array $fields Default template fields
     * @return array Modified fields array
     */
    public function add_template_fields($fields) {
        // Add company profile fields
        $fields['company'] = array(
            'company_name' => __('Company Name', 'host-docgen'),
            'business_type' => __('Business Type', 'host-docgen'),
            'registration_number' => __('Registration Number', 'host-docgen')
        );
        
        // Add document fields
        $fields['document'] = array(
            'document_number' => __('Document Number', 'host-docgen'),
            'issue_date' => __('Issue Date', 'host-docgen'),
            'expiry_date' => __('Expiry Date', 'host-docgen')
        );

        return $fields;
    }

    /**
     * Render templates card for dashboard
     * @param array $data Card data
     */
    public function render_templates_card($data) {
        $templates = $data['templates'];

        echo '<div class="card">';
        echo '<h2>' . esc_html__('Available Templates', 'host-docgen') . '</h2>';

        if (!empty($templates)) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr>';
            echo '<th>' . esc_html__('Template Name', 'host-docgen') . '</th>';
            echo '<th>' . esc_html__('Modified', 'host-docgen') . '</th>';
            echo '<th>' . esc_html__('Size', 'host-docgen') . '</th>';
            echo '</tr></thead><tbody>';

            foreach ($templates as $template) {
                $name = basename($template);
                $modified = date_i18n(get_option('date_format'), filemtime($template));
                $size = size_format(filesize($template));

                echo '<tr>';
                echo '<td>' . esc_html($name) . '</td>';
                echo '<td>' . esc_html($modified) . '</td>';
                echo '<td>' . esc_html($size) . '</td>';
                echo '</tr>';
            }

            echo '</tbody></table>';
        } else {
            echo '<p>' . esc_html__('No templates available.', 'host-docgen') . '</p>';
        }

        echo '</div>';
    }

    /**
     * Add custom cards to dashboard
     * @param array $cards Existing dashboard cards
     * @return array Modified cards array
     */ 
    public function add_host_cards($cards) {
        error_log('DocGen: add_host_cards is called');
        error_log('DocGen: Original cards: ' . print_r($cards, true));
        
        if (isset($cards['system_info']) && isset($cards['system_info']['data'])) {
            error_log('DocGen: Found system_info card');
            $system_info = $cards['system_info']['data'];
            $plugin_slug = $this->adapter->get_current_plugin_slug();
            error_log('DocGen: Plugin slug: ' . $plugin_slug);
            
            // Pastikan strukturnya sama dengan aslinya
            $cards['system_info'] = array(
                'callback' => $cards['system_info']['callback'],
                'data' => array(
                    'php_version' => $system_info['php_version'],
                    'wp_version' => $system_info['wp_version'],
                    'docgen_version' => $system_info['docgen_version'],
                    'temp_dir' => trailingslashit($system_info['temp_dir']) . $plugin_slug,
                    'template_dir' => $system_info['template_dir'] . $plugin_slug,
                    'upload_dir' => $system_info['upload_dir']
                )
            );
            error_log('DocGen: Modified system_info card - new temp_dir: ' . trailingslashit($system_info['temp_dir']) . $plugin_slug);
        } else {
            error_log('DocGen: system_info card not found in cards array');
        }

        error_log('DocGen: Final modified cards: ' . print_r($cards, true));
        return $cards;
    }

    // ...
}