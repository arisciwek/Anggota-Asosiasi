<?php
/**
 * Base Provider Class untuk DocGen di Asosiasi
 *
 * @package     Asosiasi
 * @subpackage  DocGen
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: includes/docgen/class-asosiasi-docgen-provider.php
 * 
 * Description: Base provider class yang menjadi jembatan antara 
 *              Asosiasi plugin dengan DocGen Implementation.
 *              Menghandle template dan document processing.
 * 
 * Dependencies:
 * - WP DocGen
 * - DocGen Implementation Plugin
 * 
 * Usage:
 * Extend class ini untuk membuat provider spesifik:
 * class CompanyProfile_Form_Provider extends Asosiasi_DocGen_Provider
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

abstract class Asosiasi_DocGen_Provider implements WP_DocGen_Provider {
    /**
     * Data untuk dokumen
     * @var array
     */
    protected $data = [];

    /**
     * Get template path dari DocGen Implementation
     * @return string
     * @throws Exception jika template tidak ditemukan
     */
    public function get_template_path() {
        // Get template directory dari DocGen Implementation
        $settings = get_option('docgen_implementation_settings', array());
        $template_dir = $settings['template_dir'] ?? '';
        
        if (empty($template_dir)) {
            throw new Exception('Template directory not configured in DocGen Implementation');
        }

        $template_path = trailingslashit($template_dir) . 'template.docx';
        
        if (!file_exists($template_path)) {
            throw new Exception('Template file not found at: ' . $template_path);
        }

        return $template_path;
    }

    /**
     * Get output filename 
     * @return string
     */
    public function get_output_filename() {
        // Get identifier atau default
        $identifier = !empty($this->data['identifier']) ? 
            sanitize_title($this->data['identifier']) : 
            'document';

        // Format timestamp
        $timestamp = date('Ymd-His');

        // Construct filename
        return sprintf(
            '%s-%s-%s',
            $identifier,
            $this->get_source_identifier(),
            $timestamp
        );
    }

    /**
     * Get output format dari DocGen Implementation settings
     * @return string
     */
    public function get_output_format() {
        $settings = get_option('docgen_implementation_settings', array());
        return $settings['output_format'] ?? 'docx';
    }

    /**
     * Get temporary directory dari DocGen Implementation
     * @return string
     * @throws Exception jika direktori tidak valid
     */
    public function get_temp_dir() {
        $settings = get_option('docgen_implementation_settings', array());
        $temp_dir = $settings['temp_dir'] ?? '';
        
        if (empty($temp_dir)) {
            throw new Exception('Temporary directory not configured in DocGen Implementation');
        }

        $doc_temp_dir = trailingslashit($temp_dir) . 'asosiasi-documents';
        
        if (!file_exists($doc_temp_dir)) {
            wp_mkdir_p($doc_temp_dir);
        }
        
        if (!is_writable($doc_temp_dir)) {
            throw new Exception('Temporary directory is not writable');
        }

        return $doc_temp_dir;
    }

    /**
     * Format array menjadi bullet points
     * @param array $items Array item
     * @return string Formatted bullet points
     */
    protected function format_bullet_points($items) {
        if (!is_array($items)) {
            return '';
        }
        
        return implode("\n", array_map(function($item) {
            return "• " . trim($item);
        }, $items));
    }

    /**
     * Get identifier untuk source type
     * @return string
     */
    abstract protected function get_source_identifier();

    /**
     * Get data untuk template
     * @return array
     */
    abstract public function get_data();
}