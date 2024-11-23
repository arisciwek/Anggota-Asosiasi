<?php
/**
 * Helper functions untuk member certificate template
 * 
 * @package Asosiasi
 * @version 1.0.1
 * Path: includes/helpers/member-certificate-templates.php
 * 
 * Changelog:
 * 1.0.1 - 2024-11-21 16:05 WIB
 * - Changed template location to uploads directory
 * - Added get_upload_template_dir()
 * - Added template migration during init
 * 1.0.0 - Initial release
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get base upload directory for certificates
 */
function asosiasi_get_certificate_dir() {
    $upload_dir = wp_upload_dir();
    return ASOSIASI_DIR . 'templates/';
}

/**
 * Get template upload directory
 */
function asosiasi_get_upload_template_dir() {
    return asosiasi_get_certificate_dir() . 'templates/';
}

/**
 * Get certificate template directory
 */
function asosiasi_get_template_dir() {
    return ASOSIASI_DIR . 'templates/';
}

/**
 * Get certificate template path
 */
function asosiasi_get_template_path() {
    return asosiasi_get_template_dir() . 'member-certificate-template.docx';
}

/**
 * Check if certificate template exists
 */
function asosiasi_template_exists() {
    return file_exists(asosiasi_get_template_path());
}

/**
 * Get path to default template in plugin
 */
function asosiasi_get_default_template() {
    return ASOSIASI_DIR . 'assets/templates/default-member-certificate.docx';
}

/**
 * Copy default template to templates directory
 */
function asosiasi_copy_default_template() {
    // Source template in plugin
    $source = asosiasi_get_default_template();
    
    // Destination path
    $dest = asosiasi_get_template_path();
    
    // Create templates directory if not exists
    if (!file_exists(asosiasi_get_template_dir())) {
        wp_mkdir_p(asosiasi_get_template_dir());
    }
    
    // Copy template if source exists
    if (file_exists($source)) {
        copy($source, $dest);
        return true;
    }
    
    return false;
}

/**
 * Verify and setup certificate template
 */
function asosiasi_verify_template() {
    if (!asosiasi_template_exists()) {
        return asosiasi_copy_default_template();
    }
    return true;
}

/**
 * Create template directory structure 
 */
function asosiasi_create_template_directories() {
    $template_dir = asosiasi_get_upload_template_dir();
    
    // Create directory if not exists
    if (!file_exists($template_dir)) {
        wp_mkdir_p($template_dir);
        
        // Add .htaccess for security
        $htaccess = $template_dir . '.htaccess';
        if (!file_exists($htaccess)) {
            $content = "Deny from all\n";
            file_put_contents($htaccess, $content);
        }
        
        // Add index.php
        $index = $template_dir . 'index.php';
        if (!file_exists($index)) {
            file_put_contents($index, '<?php // Silence is golden');
        }
    }
}