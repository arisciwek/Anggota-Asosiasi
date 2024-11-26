<?php
/**
 * Company Profile View Class
 *
 * @package     Asosiasi
 * @subpackage  DocGen
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: includes/docgen/views/class-company-profile-view.php
 * 
 * Description: Manages view logic for company profile generation.
 *              Handles data preparation and rendering for the UI.
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class CompanyProfile_View {
    /**
     * View instance
     * @var self|null
     */
    private static $instance = null;

    /**
     * View data
     * @var array
     */
    private $view_data = array();

    /**
     * Constructor
     */
    private function __construct() {
        $this->prepare_view_data();
    }

    /**
     * Get view instance
     * @return self
     */
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize view
     */
    public static function init() {
        return self::get_instance();
    }

    /**
     * Prepare data for view
     */
    private function prepare_view_data() {
        // Load data from JSON
        $json_file = ASOSIASI_DIR . 'includes/docgen/data/company-profile-data.json';
        if (file_exists($json_file)) {
            $json_content = file_get_contents($json_file);
            $this->view_data = json_decode($json_content, true);
        }

        // Set default values if needed
        $defaults = array(
            'company_name' => '',
            'legal_name' => '',
            'tagline' => '',
            'address' => array(
                'street' => '',
                'city' => '',
                'province' => '',
                'postal_code' => '',
                'country' => ''
            ),
            'contact' => array(
                'phone' => '',
                'email' => '',
                'website' => ''
            ),
            'business' => array(
                'main_services' => array(),
                'industries' => array(),
                'employee_count' => '',
                'office_locations' => array()
            )
        );

        $this->view_data = wp_parse_args($this->view_data, $defaults);
    }

    /**
     * Render main view
     */
    public function render() {
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'asosiasi'));
        }

        // Pass data to template
        $data = $this->view_data;
        
        // Include template
        require ASOSIASI_DIR . 'includes/docgen/views/templates/company-profile-page.php';
    }

    /**
     * Get view data
     * @return array
     */
    public function get_view_data() {
        return $this->view_data;
    }

    /**
     * Update view data
     * @param array $data New data to update
     */
    public function update_data($data) {
        $this->view_data = wp_parse_args($data, $this->view_data);
    }

    /**
     * Render JSON data section
     */
    public function render_json_section() {
        ?>
        <div class="json-data-section">
            <h4><?php _e('Company Information', 'asosiasi'); ?></h4>
            <p><strong><?php _e('Name:', 'asosiasi'); ?></strong> 
                <?php echo esc_html($this->view_data['company_name']); ?>
            </p>
            <p><strong><?php _e('Legal Name:', 'asosiasi'); ?></strong>
                <?php echo esc_html($this->view_data['legal_name']); ?>
            </p>
            <p><strong><?php _e('Tagline:', 'asosiasi'); ?></strong>
                <?php echo esc_html($this->view_data['tagline']); ?>
            </p>
        </div>
        <?php
    }

    /**
     * Render form section
     */
    public function render_form_section() {
        ?>
        <form id="company-profile-form" method="post">
            <?php wp_nonce_field('company_profile_generate'); ?>
            
            <table class="form-table">
                <tr>
                    <th><label for="company_name"><?php _e('Company Name', 'asosiasi'); ?></label></th>
                    <td>
                        <input type="text" 
                               id="company_name" 
                               name="company_name" 
                               class="regular-text"
                               value="<?php echo esc_attr($this->view_data['company_name']); ?>" />
                    </td>
                </tr>
                <!-- Add more form fields as needed -->
            </table>

            <p class="submit">
                <button type="submit" class="button button-primary">
                    <?php _e('Generate Document', 'asosiasi'); ?>
                </button>
            </p>
        </form>
        <?php
    }

    /**
     * Render preview section if needed
     */
    public function render_preview() {
        if (empty($this->view_data)) {
            return;
        }
        ?>
        <div class="preview-section">
            <h3><?php _e('Document Preview', 'asosiasi'); ?></h3>
            <div class="preview-content">
                <!-- Add preview content structure -->
            </div>
        </div>
        <?php
    }
}