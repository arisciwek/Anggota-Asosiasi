<?php
/**
 * General Settings View
 *
 * @package     DocGen_WPClass
 * @subpackage  Admin/Views
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: admin/views/dip-format-settings.php
 * 
 * Description: Template untuk menampilkan form konfigurasi umum
 *              termasuk output format dan debug mode settings.
 *              File ini di-include dari class-dwpc-settings-page.php
 * 
 * Dependencies:
 * - class-dwpc-settings-page.php (parent)
 * 
 * Usage:
 * Dipanggil dari DocGen_WPClass_Settings_Page::render_dip_format_settings()
 * 
 * Variables yang tersedia:
 * - $settings (array) Settings plugin dari database
 * - $this     (object) Instance dari class DocGen_WPClass_Settings_Page
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}
?>

<table class="form-table">
    <!-- Output Format -->
    <tr>
        <th scope="row"><?php echo esc_html__('Default Output Format', 'docgen-implementation'); ?></th>
        <td>
            <select name="output_format">
                <option value="docx" <?php selected($settings['output_format'] ?? 'docx', 'docx'); ?>>
                    <?php echo esc_html__('DOCX', 'docgen-implementation'); ?>
                </option>
                <option value="pdf" <?php selected($settings['output_format'] ?? 'docx', 'pdf'); ?>>
                    <?php echo esc_html__('PDF', 'docgen-implementation'); ?>
                </option>
            </select>
        </td>
    </tr>

    <!-- Debug Mode -->
    <tr>
        <th scope="row"><?php echo esc_html__('Debug Mode', 'docgen-implementation'); ?></th>
        <td>
            <label>
                <input type="checkbox" 
                       name="debug_mode" 
                       value="1" 
                       <?php checked($settings['debug_mode'] ?? false, true); ?> />
                <?php echo esc_html__('Enable debug mode', 'docgen-implementation'); ?>
            </label>
        </td>
    </tr>
</table>
