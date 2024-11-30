<?php
/**
 * Host DocGen Dependencies Checker
 *
 * @package     Host_DocGen
 * @subpackage  Core
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: docgen/class-docgen-checker.php
 * 
 * Description:
 * Utility class untuk mengecek ketersediaan DocGen Implementation
 * dan komponen-komponennya. Digunakan oleh plugin yang ingin
 * mengintegrasikan dengan DocGen Implementation.
 * 
 * Filename Convention:
 * - Original  : class-docgen-checker.php
 * - To Change : class-[plugin-name]-docgen-checker.php
 * 
 * Usage:
 * if (!Host_DocGen_Checker::check_dependencies('Plugin Name')) {
 *     return;
 * }
 * 
 * Dependencies:
 * - DocGen Implementation Plugin
 * 
 * @author     arisciwek
 * @copyright  2024 Host Organization
 * @license    GPL-2.0+
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class Host_DocGen_Checker {
    /**
     * Flag to track if we've already checked
     */
    private static $is_checked = false;
    
    /**
     * Cache the check result
     */
    private static $check_result = false;

    /**
     * Check DocGen Implementation dependencies
     */
    public static function check_dependencies($plugin_name) {
        // Return cached result if already checked
        if (self::$is_checked) {
            return self::$check_result;
        }

        self::$is_checked = true;

        // Check if DocGen Implementation is loaded
        if (!class_exists('DocGen_Adapter')) {
            add_action('admin_notices', function() use ($plugin_name) {
                $message = sprintf(
                    __('%s: DocGen Implementation Plugin is required but not properly loaded.', 'asosiasi'),
                    '<strong>' . esc_html($plugin_name) . '</strong>'
                );
                echo '<div class="notice notice-error is-dismissible"><p>' . wp_kses_post($message) . '</p></div>';
            });
            
            self::$check_result = false;
            return false;
        }

        // Check required directories
        $upload_dir = wp_upload_dir();
        $base_dir = $upload_dir['basedir'];
        
        $required_dirs = [
            'docgen-temp' => trailingslashit($base_dir) . 'docgen-temp',
            'docgen-templates' => trailingslashit($base_dir) . 'docgen-templates'
        ];

        $missing_dirs = [];
        foreach ($required_dirs as $name => $dir) {
            if (!file_exists($dir)) {
                $missing_dirs[] = $name;
            } elseif (!wp_is_writable($dir)) {
                $missing_dirs[] = "{$name} (not writable)";
            }
        }

        if (!empty($missing_dirs)) {
            add_action('admin_notices', function() use ($plugin_name, $missing_dirs) {
                $message = sprintf(
                    __('%s: The following DocGen directories are required:', 'asosiasi'),
                    '<strong>' . esc_html($plugin_name) . '</strong>'
                );
                
                $message .= '<ul style="list-style-type: disc; margin-left: 20px;">';
                foreach ($missing_dirs as $dir) {
                    $message .= '<li>' . esc_html($dir) . '</li>';
                }
                $message .= '</ul>';
                
                echo '<div class="notice notice-warning is-dismissible"><p>' . wp_kses_post($message) . '</p></div>';
            });
            self::$check_result = false;
            return false;
        }

        self::$check_result = true;
        return true;
    }
}
