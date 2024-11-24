<?php
/**
 * Certificate AJAX handlers
 * Path: includes/ajax/certificate-ajax.php
 */

// Test directory AJAX handler
add_action('wp_ajax_test_temp_directory', function() {
    check_ajax_referer('asosiasi_certificate_settings');
    
    $upload_dir = wp_upload_dir();
    $folder = sanitize_text_field($_POST['folder']);
    $dir = $upload_dir['basedir'] . '/' . $folder;
    
    if (!file_exists($dir)) {
        wp_mkdir_p($dir);
    }
    
    if (is_writable($dir)) {
        wp_send_json_success(__('Direktori dapat diakses dan ditulis.', 'asosiasi'));
    } else {
        wp_send_json_error(__('Direktori tidak dapat ditulis.', 'asosiasi'));
    }
});

// Cleanup temp files AJAX handler
add_action('wp_ajax_cleanup_temp_files', function() {
    check_ajax_referer('asosiasi_certificate_settings');
    
    $upload_dir = wp_upload_dir();
    $folder = get_option('asosiasi_temp_folder', 'tmp');
    $dir = $upload_dir['basedir'] . '/' . $folder;
    
    if (!file_exists($dir)) {
        wp_send_json_success(__('Direktori sudah bersih.', 'asosiasi'));
        return;
    }
    
    $files = glob($dir . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    
    wp_send_json_success(__('File sementara berhasil dibersihkan.', 'asosiasi'));
});
