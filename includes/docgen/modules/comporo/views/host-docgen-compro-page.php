<?php
/**
 * Host DocGen Company Profile Page View
 *
 * @package     Host_DocGen
 * @subpackage  Modules/Compro/Views
 * @version     1.0.0
 * 
 * Description:
 * View template untuk halaman Company Profile.
 * Menampilkan form input dan data JSON untuk generate dokumen.
 * 
 * Filename Convention:
 * - Original  : host-docgen-compro-page.php
 * - To Change : [plugin-name]-docgen-[module-name]-page.php
 * 
 * Path: modules/compro/views/host-docgen-compro-page.php
 * Timestamp: 2024-11-29 10:40:00
 * 
 * Variables Available:
 * - $json_data: array - Data dari JSON file jika ada
 * 
 * Dependencies:
 * - Bootstrap Admin CSS (WordPress)
 * - jQuery
 * - Module specific CSS/JS
 * 
 * @author     arisciwek
 * @author     Host Developer
 * @copyright  2024 Host Organization
 * @license    GPL-2.0+
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

// Load JSON data if exists
$json_file = dirname(dirname(__FILE__)) . '/data/compro-data.json';
$json_data = array();
if (file_exists($json_file)) {
    $json_content = file_get_contents($json_file);
    $json_data = json_decode($json_content, true);
}
?>

<div class="wrap">
    <h1><?php _e('Company Profile Generator', 'host-docgen'); ?></h1>

    <div class="docgen-panels">
        <!-- Left Panel - JSON Data -->
        <div class="docgen-panel-json">
            <div class="card">
                <h2><?php _e('Company Data from JSON', 'host-docgen'); ?></h2>
                
                <?php if (!empty($json_data)): ?>
                    <div class="json-data-display">
                        <!-- Basic Info -->
                        <div class="json-data-section">
                            <h4><?php _e('Basic Information', 'host-docgen'); ?></h4>
                            <p><strong><?php _e('Company Name:', 'host-docgen'); ?></strong> 
                               <?php echo esc_html($json_data['company_name'] ?? ''); ?></p>
                        </div>

                        <!-- Address -->
                        <div class="json-data-section">
                            <h4><?php _e('Address', 'host-docgen'); ?></h4>
                            <p><?php echo esc_html($json_data['address']['street'] ?? ''); ?></p>
                            <p><?php 
                                echo sprintf(
                                    '%s %s',
                                    esc_html($json_data['address']['city'] ?? ''),
                                    esc_html($json_data['address']['postal_code'] ?? '')
                                ); 
                            ?></p>
                        </div>

                        <!-- Contact -->
                        <div class="json-data-section">
                            <h4><?php _e('Contact', 'host-docgen'); ?></h4>
                            <p><strong><?php _e('Phone:', 'host-docgen'); ?></strong> 
                               <?php echo esc_html($json_data['contact']['phone'] ?? ''); ?></p>
                            <p><strong><?php _e('Email:', 'host-docgen'); ?></strong> 
                               <?php echo esc_html($json_data['contact']['email'] ?? ''); ?></p>
                            <p><strong><?php _e('Website:', 'host-docgen'); ?></strong> 
                               <?php echo esc_html($json_data['contact']['website'] ?? ''); ?></p>
                        </div>

                        <!-- Profile -->
                        <?php if (!empty($json_data['profile'])): ?>
                            <div class="json-data-section">
                                <h4><?php _e('Company Profile', 'host-docgen'); ?></h4>
                                
                                <?php if (!empty($json_data['profile']['vision'])): ?>
                                    <p><strong><?php _e('Vision:', 'host-docgen'); ?></strong><br>
                                       <?php echo esc_html($json_data['profile']['vision']); ?></p>
                                <?php endif; ?>

                                <?php if (!empty($json_data['profile']['mission'])): ?>
                                    <p><strong><?php _e('Mission:', 'host-docgen'); ?></strong></p>
                                    <ul>
                                        <?php foreach ($json_data['profile']['mission'] as $mission): ?>
                                            <li><?php echo esc_html($mission); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <p>
                        <button type="button" id="generate-json" class="button button-primary">
                            <?php _e('Generate from JSON', 'host-docgen'); ?>
                            <span class="spinner"></span>
                        </button>
                    </p>

                    <div id="json-result" style="display:none;">
                        <p class="description">
                            <?php _e('Your document has been generated:', 'host-docgen'); ?>
                            <a href="#" id="download-json" class="button">
                                <?php _e('Download Document', 'host-docgen'); ?>
                            </a>
                        </p>
                    </div>

                <?php else: ?>
                    <div class="notice notice-warning">
                        <p><?php _e('No JSON data found. Please check data/compro-data.json file.', 'host-docgen'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Panel - Form -->
        <div class="docgen-panel-form">
            <div class="card">
                <h2><?php _e('Generate from Form', 'host-docgen'); ?></h2>

                <form id="compro-form" method="post">
                    <table class="form-table">
                        <tr>
                            <th><?php _e('Company Name', 'host-docgen'); ?></th>
                            <td>
                                <input type="text" 
                                       name="company_name" 
                                       class="regular-text" 
                                       required />
                            </td>
                        </tr>

                        <tr>
                            <th><?php _e('Address', 'host-docgen'); ?></th>
                            <td>
                                <textarea name="address" 
                                          rows="3" 
                                          class="large-text" 
                                          required></textarea>
                            </td>
                        </tr>

                        <tr>
                            <th><?php _e('Phone', 'host-docgen'); ?></th>
                            <td>
                                <input type="tel" 
                                       name="phone" 
                                       class="regular-text" />
                            </td>
                        </tr>

                        <tr>
                            <th><?php _e('Email', 'host-docgen'); ?></th>
                            <td>
                                <input type="email" 
                                       name="email" 
                                       class="regular-text" />
                            </td>
                        </tr>

                        <tr>
                            <th><?php _e('Website', 'host-docgen'); ?></th>
                            <td>
                                <input type="url" 
                                       name="website" 
                                       class="regular-text" />
                            </td>
                        </tr>

                        <tr>
                            <th><?php _e('Description', 'host-docgen'); ?></th>
                            <td>
                                <?php 
                                wp_editor(
                                    '', 
                                    'description',
                                    array(
                                        'textarea_name' => 'description',
                                        'textarea_rows' => 10,
                                        'media_buttons' => false
                                    )
                                ); 
                                ?>
                            </td>
                        </tr>
                    </table>

                    <p class="submit">
                        <button type="submit" class="button button-primary">
                            <?php _e('Generate Document', 'host-docgen'); ?>
                            <span class="spinner"></span>
                        </button>
                    </p>
                </form>

                <div id="form-result" style="display:none;">
                    <p class="description">
                        <?php _e('Your document has been generated:', 'host-docgen'); ?>
                        <a href="#" id="download-form" class="button">
                            <?php _e('Download Document', 'host-docgen'); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
