<?php
/**
 * Template tab sertifikat di halaman settings
 *
 * @package Asosiasi
 * @version 1.0.0
 * Path: admin/views/tabs/tab-certificate.php
 * 
 * Changelog:
 * 1.0.0 - 2024-11-21 17:55 WIB
 * - Initial release
 * - Added template upload form
 * - Added current template info
 * - Added template preview
 */

defined('ABSPATH') || exit;

// Load helper if not loaded
if (!function_exists('asosiasi_get_template_path')) {
    require_once ASOSIASI_DIR . 'helpers/member-certificate-templates.php';
}

// Handle template upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_template') {
    if (!wp_verify_nonce($_POST['_wpnonce'], 'upload_certificate_template')) {
        wp_die(__('Invalid security token', 'asosiasi'));
    }

    if (!current_user_can('manage_options')) {
        wp_die(__('Insufficient permissions', 'asosiasi'));
    }

    // Check file upload
    if (!isset($_FILES['template_file']) || empty($_FILES['template_file']['tmp_name'])) {
        add_settings_error(
            'certificate_template',
            'no_file',
            __('Please select a template file to upload', 'asosiasi'),
            'error'
        );
    } else {
        $file = $_FILES['template_file'];
        
        // Validate file type
        $allowed_types = array('application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        if (!in_array($file['type'], $allowed_types)) {
            add_settings_error(
                'certificate_template',
                'invalid_type',
                __('Only DOCX files are allowed', 'asosiasi'),
                'error'
            );
        } else {
            // Move file to template directory
            $template_path = asosiasi_get_template_path();

            if (move_uploaded_file($file['tmp_name'], $template_path)) {
                add_settings_error(
                    'certificate_template',
                    'upload_success',
                    __('Certificate template updated successfully', 'asosiasi'),
                    'success'
                );
            } else {
                add_settings_error(
                    'certificate_template',
                    'upload_failed',
                    __('Failed to upload template file', 'asosiasi'),
                    'error'
                );
            }
        }
    }
}
?>

<div class="wrap certificate-settings">
    <?php settings_errors('certificate_template'); ?>

    <div class="card">
        <h3 class="title"><?php _e('Certificate Template', 'asosiasi'); ?></h3>
        
        <div class="inside">
            <!-- Current Template Info -->
            <div class="current-template">
                <h4><?php _e('Current Template', 'asosiasi'); ?></h4>
                <?php if (asosiasi_template_exists()): ?>
                    <p>
                        <span class="dashicons dashicons-media-document"></span>
                        <?php 
                        $template_path = asosiasi_get_template_path();
                        $template_size = size_format(filesize($template_path));
                        $template_date = date_i18n(
                            get_option('date_format') . ' ' . get_option('time_format'),
                            filemtime($template_path)
                        );
                        
                        printf(
                            /* translators: 1: File size 2: Modified date */
                            __('Template file exists (%1$s, last modified: %2$s)', 'asosiasi'),
                            $template_size,
                            $template_date
                        );
                        ?>
                    </p>
                <?php else: ?>
                    <p class="description">
                        <span class="dashicons dashicons-warning"></span>
                        <?php _e('No custom template uploaded. Using default template.', 'asosiasi'); ?>
                    </p>
                <?php endif; ?>
            </div>

            <!-- Upload Form -->
            <form method="post" enctype="multipart/form-data" class="template-upload-form">
                <?php wp_nonce_field('upload_certificate_template'); ?>
                <input type="hidden" name="action" value="upload_template">
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="template_file"><?php _e('Upload Template', 'asosiasi'); ?></label>
                        </th>
                        <td>
                            <input type="file" 
                                   name="template_file" 
                                   id="template_file"
                                   accept=".docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                   required>
                            <p class="description">
                                <?php _e('Select DOCX file for the certificate template.', 'asosiasi'); ?>
                            </p>
                            <p class="description">
                                <?php _e('Make sure your template includes required fields marked with ${field_name}', 'asosiasi'); ?>
                            </p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <?php _e('Upload Template', 'asosiasi'); ?>
                    </button>
                </p>
            </form>

            <!-- Template Fields Guide -->
            <div class="template-fields-guide">
                <h4><?php _e('Available Template Fields', 'asosiasi'); ?></h4>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Field', 'asosiasi'); ?></th>
                            <th><?php _e('Description', 'asosiasi'); ?></th>
                            <th><?php _e('Example', 'asosiasi'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>${cert_number}</code></td>
                            <td><?php _e('Certificate Number', 'asosiasi'); ?></td>
                            <td>CERT/2024/01/0001/001</td>
                        </tr>
                        <tr>
                            <td><code>${company_name}</code></td>
                            <td><?php _e('Company Name', 'asosiasi'); ?></td>
                            <td>PT Example Corporation</td>
                        </tr>
                        <tr>
                            <td><code>${tanggal:date:format}</code></td>
                            <td><?php _e('Date in Indonesian format', 'asosiasi'); ?></td>
                            <td>21 November 2024</td>
                        </tr>
                        <tr>
                            <td><code>${qr_data}</code></td>
                            <td><?php _e('Certificate QR Code', 'asosiasi'); ?></td>
                            <td>[QR Code Image]</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>