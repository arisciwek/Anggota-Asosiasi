<?php
/**
 * Template untuk Company Profile Generator
 *
 * @package     Asosiasi
 * @subpackage  DocGen
 * @version     1.0.0
 * 
 * Path: includes/docgen/views/templates/company-profile-page.php
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

// Get current data
$data = $this->view_data;
?>

<div class="wrap">
    <h1><?php _e('Company Profile Generator', 'asosiasi'); ?></h1>

    <div class="docgen-panels">
        <!-- Left Panel - JSON Data -->
        <div class="docgen-panel-json">
            <div class="card">
                <h2><?php _e('Company Data', 'asosiasi'); ?></h2>
                <div class="json-data-display">
                    <div class="json-data-section">
                        <h4><?php _e('Basic Information', 'asosiasi'); ?></h4>
                        <p><strong>Company Name:</strong> <?php echo esc_html($data['company_name']); ?></p>
                        <p><strong>Legal Name:</strong> <?php echo esc_html($data['legal_name']); ?></p>
                        <p><strong>Tagline:</strong> <?php echo esc_html($data['tagline']); ?></p>
                    </div>

                    <div class="json-data-section">
                        <h4><?php _e('Address', 'asosiasi'); ?></h4>
                        <p><?php echo esc_html($data['address']['street']); ?></p>
                        <p><?php echo sprintf(
                            '%s, %s %s',
                            esc_html($data['address']['city']),
                            esc_html($data['address']['province']),
                            esc_html($data['address']['postal_code'])
                        ); ?></p>
                        <p><?php echo esc_html($data['address']['country']); ?></p>
                    </div>

                    <div class="json-data-section">
                        <h4><?php _e('Contact', 'asosiasi'); ?></h4>
                        <p><strong>Phone:</strong> <?php echo esc_html($data['contact']['phone']); ?></p>
                        <p><strong>Email:</strong> <?php echo esc_html($data['contact']['email']); ?></p>
                        <p><strong>Website:</strong> <?php echo esc_html($data['contact']['website']); ?></p>
                    </div>
                </div>

                <p>
                    <button type="button" id="generate-profile-json" class="button button-primary">
                        <?php _e('Generate from Data', 'asosiasi'); ?>
                        <span class="spinner"></span>
                    </button>
                </p>
                <div id="generation-result-json" style="display:none;">
                    <p class="description">
                        <?php _e('Your document has been generated:', 'asosiasi'); ?>
                        <a href="#" id="download-profile-json" class="button">
                            <?php _e('Download Document', 'asosiasi'); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Panel - Form -->
        <div class="docgen-panel-form">
            <form id="company-profile-form" method="post">
                <div class="card">
                    <h2><?php _e('Company Information Form', 'asosiasi'); ?></h2>
                    
                    <table class="form-table">
                        <!-- Company Info -->
                        <tr>
                            <th><?php _e('Company Name', 'asosiasi'); ?></th>
                            <td>
                                <input type="text"
                                       name="company_name"
                                       class="regular-text"
                                       value="<?php echo esc_attr($data['company_name']); ?>" />
                            </td>
                        </tr>
                        
                        <!-- Address Fields -->
                        <tr>
                            <th><?php _e('Street Address', 'asosiasi'); ?></th>
                            <td>
                                <input type="text"
                                       name="address[street]"
                                       class="large-text"
                                       value="<?php echo esc_attr($data['address']['street']); ?>" />
                            </td>
                        </tr>
                        
                        <!-- Contact Fields -->
                        <tr>
                            <th><?php _e('Phone', 'asosiasi'); ?></th>
                            <td>
                                <input type="tel"
                                       name="contact[phone]"
                                       class="regular-text"
                                       value="<?php echo esc_attr($data['contact']['phone']); ?>" />
                            </td>
                        </tr>
                    </table>

                    <p class="submit">
                        <button type="submit" id="generate-profile-form" class="button button-primary">
                            <?php _e('Generate from Form', 'asosiasi'); ?>
                            <span class="spinner"></span>
                        </button>
                    </p>

                    <div id="generation-result-form" style="display:none;">
                        <p class="description">
                            <?php _e('Your document has been generated:', 'asosiasi'); ?>
                            <a href="#" id="download-profile-form" class="button">
                                <?php _e('Download Document', 'asosiasi'); ?>
                            </a>
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>