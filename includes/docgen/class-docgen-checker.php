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
     * Check DocGen Implementation dependencies
     * 
     * @param string $plugin_name Plugin name untuk ditampilkan di error message
     * @return bool True jika semua dependencies terpenuhi
     */
    public static function check_dependencies($plugin_name) {
        if (!did_action('docgen_implementation_loaded')) {
            add_action('admin_notices', function() use ($plugin_name) {
                // Tampilkan notice
            });
            return false;
        }

        $required_classes = [
            'DocGen_Implementation_Adapter' => 'Core adapter class',
            'DocGen_Implementation_Module' => 'Base module class',
            'DocGen_Implementation_Settings_Manager' => 'Settings management system',
            'DocGen_Implementation_Module_Loader' => 'Module loading system'
        ];
        
        $missing_classes = [];
        
        foreach ($required_classes as $class => $description) {
            if (!class_exists($class)) {
                $missing_classes[$class] = $description;
            }
        }
        
        if (!empty($missing_classes)) {
            add_action('admin_notices', function() use ($missing_classes, $plugin_name) {
                $message = sprintf(
                    __('%s requires DocGen Implementation Plugin to be installed and activated.', 'host-docgen'),
                    '<strong>' . esc_html($plugin_name) . '</strong>'
                );
                
                $message .= '<br/><br/>' . __('Missing components:', 'host-docgen');
                $message .= '<ul style="list-style-type: disc; margin-left: 20px;">';
                
                foreach ($missing_classes as $class => $description) {
                    $message .= sprintf(
                        '<li>%s (%s)</li>',
                        esc_html($class),
                        esc_html($description)
                    );
                }
                
                $message .= '</ul>';
                
                echo '<div class="notice notice-error"><p>' . wp_kses_post($message) . '</p></div>';
            });
            return false;
        }
        
        return true;
    }
}