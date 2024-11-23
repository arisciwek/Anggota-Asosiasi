<?php
/**
 * Helper functions untuk certificate template
 * 
 * @package Asosiasi
 * @version 1.0.2
 * Path: includes/helpers/certificate-templates.php
 * 
 * Changelog:
 * 1.0.2 - 2024-11-21 17:45 WIB
 * - Changed template directory to wp-content/uploads/asosiasi-certificates/templates
 * - Fixed path resolution using wp_upload_dir()
 * - Improved directory structure creation
 * 
 * 1.0.1 - Added debug logs
 * 1.0.0 - Initial release
 */

if (!defined('ABSPATH')) {
    exit;
}

function getMemberId() {
    // Try from hidden input first
    var memberId = $('#member_id').val();
    
    if (!memberId) {
        // Try from URL params
        var urlParams = new URLSearchParams(window.location.search);
        memberId = urlParams.get('id');
    }
    
    if (!memberId) {
        // Try from form data attribute
        memberId = $('#member-form').data('member-id');
    }

    console.log('Getting member ID:', memberId);
    return memberId;
}

// Lalu gunakan dalam form submit handler:
setTimeout(function() {
    var memberId = getMemberId();
    if (typeof AsosiasiSKP !== 'undefined' && typeof AsosiasiSKP.reloadTable === 'function' && memberId) {
        try {
            console.log('Reloading SKP table for member:', memberId);
            AsosiasiSKP.reloadTable(memberId);
        } catch (error) {
            console.error('Error reloading SKP table:', error);
        }
    }
    $submitButton.prop('disabled', false);
}, 500);


/**
 * Get certificate template directory
 */
function asosiasi_get_template_dir() {
    $upload_dir = wp_upload_dir();
    $template_dir = $upload_dir['basedir'] . '/asosiasi-certificates/templates/';
    
    // Debug log
    if (WP_DEBUG) {
        error_log('Certificate template directory: ' . $template_dir);
    }
    
    return $template_dir;
}

/**
 * Get certificate template path
 */
function asosiasi_get_template_path() {
    $template = asosiasi_get_template_dir() . 'certificate-template.docx';
    
    // Debug log
    if (WP_DEBUG) {
        error_log('Looking for template at: ' . $template);
        error_log('Template exists: ' . (file_exists($template) ? 'Yes' : 'No'));
    }
    
    return $template;
}

/**
 * Check if certificate template exists
 */
function asosiasi_template_exists() {
    $exists = file_exists(asosiasi_get_template_path());
    
    // Debug log
    if (WP_DEBUG) {
        error_log('Template exists check: ' . ($exists ? 'Yes' : 'No'));
    }
    
    return $exists;
}

/**
 * Copy default template to templates directory
 */
function asosiasi_copy_default_template() {
    // Source template in plugin
    $source = ASOSIASI_DIR . 'assets/templates/certificate-template.docx';
    
    // Destination path
    $dest = asosiasi_get_template_path();
    
    // Create templates directory structure
    $template_dir = asosiasi_get_template_dir();
    if (!file_exists($template_dir)) {
        wp_mkdir_p($template_dir);
        
        // Debug log
        if (WP_DEBUG) {
            error_log('Created template directory: ' . $template_dir);
        }
    }
    
    // Verify source template
    if (!file_exists($source)) {
        if (WP_DEBUG) {
            error_log('Source template not found at: ' . $source);
        }
        return false;
    }
    
    // Copy template
    $result = copy($source, $dest);
    
    // Debug log
    if (WP_DEBUG) {
        error_log('Template copy result: ' . ($result ? 'Success' : 'Failed'));
        if (!$result) {
            error_log('Copy failed from ' . $source . ' to ' . $dest);
        }
    }
    
    return $result;
}

/**
 * Verify and setup certificate template
 */
function asosiasi_verify_template() {
    if (!asosiasi_template_exists()) {
        if (WP_DEBUG) {
            error_log('Template missing, attempting to copy default');
        }
        return asosiasi_copy_default_template();
    }
    return true;
}

/**
 * Get absolute path to certificate output directory
 */
function asosiasi_get_certificate_dir() {
    $upload_dir = wp_upload_dir();
    return $upload_dir['basedir'] . '/asosiasi-certificates/';
}

/**
 * Create and secure certificate directories
 *
function asosiasi_setup_certificate_dirs() {
    // Create main certificate directory
    $cert_dir = asosiasi_get_certificate_dir();
    if (!file_exists($cert_dir)) {
        wp_mkdir_p($cert_dir);
    }

    // Create templates subdirectory
    $template_dir = asosiasi_get_template_dir();
    if (!file_exists($template_dir)) {
        wp_mkdir_p($template_dir);
    }

    // Add .htaccess protection
    $htaccess = $cert_dir . '.htaccess';
    if (!file_exists($htaccess)) {
        $content = "Options -Indexes\n";
        $content .= "<FilesMatch '\.(php|php\.|php3|php4|php5|php7|phtml|pl|py|jsp|asp|htm|shtml|sh|cgi)$'>\n";
        $content .= "Order Deny,Allow\n";
        $content .= "Deny from all\n";
        $content .= "</FilesMatch>\n";
        file_put_contents($htaccess, $content);
    }

    // Add index.php
    $index = $cert_dir . 'index.php';
    if (!file_exists($index)) {
        file_put_contents($index, '<?php // Silence is golden');
    }

    return true;

}
*/

