<?php
/**
 * Tab pengaturan sertifikat
 * 
 * @package Asosiasi
 * @version 2.2.0
 * Path: admin/views/tabs/tab-certificate.php
 */

if (!defined('ABSPATH')) {
    die;
}

// Get WordPress upload directory info
$upload_dir = wp_upload_dir();
$base_path = $upload_dir['basedir'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_certificate_settings') {
    if (!check_admin_referer('asosiasi_certificate_settings')) {
        wp_die(__('Invalid security token sent.', 'asosiasi'));
    }

    // Update temporary directory folder name only
    $temp_folder = sanitize_text_field(trim($_POST['temp_folder'], '/'));
    update_option('asosiasi_temp_folder', $temp_folder);

    // Update template directory folder name only
    $template_folder = sanitize_text_field(trim($_POST['template_folder'], '/'));
    update_option('asosiasi_template_folder', $template_folder);

    // Update output format
    $output_format = sanitize_text_field($_POST['output_format']);
    update_option('asosiasi_output_format', $output_format);

    // Update debug mode
    $debug_mode = isset($_POST['debug_mode']) ? 1 : 0;
    update_option('asosiasi_debug_mode', $debug_mode);

    add_settings_error(
        'asosiasi_messages', 
        'settings_updated', 
        __('Pengaturan sertifikat berhasil diperbarui.', 'asosiasi'), 
        'success'
    );
}

// Get current settings
$temp_folder = get_option('asosiasi_temp_folder', 'tmp');
$template_folder = get_option('asosiasi_template_folder', 'uploads');
$output_format = get_option('asosiasi_output_format', 'DOCX');
$debug_mode = get_option('asosiasi_debug_mode', 0);

// Enqueue scripts and styles
wp_enqueue_style('asosiasi-certificate-style');
wp_enqueue_script('asosiasi-certificate-script');
?>

<div class="wrap">
    <form method="post" action="<?php echo add_query_arg('tab', 'certificate'); ?>">
        <?php wp_nonce_field('asosiasi_certificate_settings'); ?>
        <input type="hidden" name="action" value="update_certificate_settings">

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="temp_folder"><?php _e('Temporary Directory', 'asosiasi'); ?></label>
                </th>
                <td>
                    <div class="directory-input">
                        <span class="base-path"><?php echo esc_html($base_path); ?>/</span>
                        <input type="text" id="temp_folder" name="temp_folder" 
                               value="<?php echo esc_attr($temp_folder); ?>" 
                               placeholder="tmp"
                               class="folder-input">
                    </div>
                    <p class="description">
                        <?php _e('Folder for temporary files. Must be writable.', 'asosiasi'); ?>
                    </p>
                    <div class="button-group">
                        <button type="button" class="button test-directory" 
                                data-folder="<?php echo esc_attr($temp_folder); ?>">
                            <?php _e('Test Directory', 'asosiasi'); ?>
                        </button>
                        <button type="button" class="button cleanup-temp-files">
                            <?php _e('Cleanup Temp Files', 'asosiasi'); ?>
                        </button>
                    </div>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="template_folder"><?php _e('Template Directory', 'asosiasi'); ?></label>
                </th>
                <td>
                    <div class="directory-input">
                        <span class="base-path"><?php echo esc_html($base_path); ?>/</span>
                        <input type="text" id="template_folder" name="template_folder" 
                               value="<?php echo esc_attr($template_folder); ?>" 
                               placeholder="uploads"
                               class="folder-input">
                    </div>
                    <p class="description">
                        <?php _e('Folder for template files (DOCX/ODT).', 'asosiasi'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="output_format"><?php _e('Default Output Format', 'asosiasi'); ?></label>
                </th>
                <td>
                    <select name="output_format" id="output_format">
                        <option value="DOCX" <?php selected($output_format, 'DOCX'); ?>>DOCX</option>
                        <option value="ODT" <?php selected($output_format, 'ODT'); ?>>ODT</option>
                        <option value="PDF" <?php selected($output_format, 'PDF'); ?>>PDF</option>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Debug Mode', 'asosiasi'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="debug_mode" value="1" 
                               <?php checked($debug_mode, 1); ?>>
                        <?php _e('Enable debug mode', 'asosiasi'); ?>
                    </label>
                </td>
            </tr>
        </table>

        <?php submit_button(__('Simpan Perubahan', 'asosiasi')); ?>
    </form>
</div>
