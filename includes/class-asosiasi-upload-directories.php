<?php
/**
 * Class untuk mengelola direktori upload plugin
 *
 * @package Asosiasi
 * @version 1.0.0
 * Path: includes/class-asosiasi-upload-directories.php
 * 
 * Changelog:
 * 1.0.0 - 2024-11-18
 * - Initial release
 * - Extracted from class-asosiasi-activator.php
 * - Added directory validation and error handling
 * - Added directory status checking methods
 */

defined('ABSPATH') || exit;

class Asosiasi_Upload_Directories {
    /**
     * Default paths
     */
    private $base_upload_dir;
    private $skp_dir;
    private $images_dir;

    /**
     * Constructor
     */
    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->base_upload_dir = $upload_dir['basedir'];
        $this->skp_dir = $this->base_upload_dir . '/asosiasi-skp/perusahaan';
        $this->images_dir = $this->base_upload_dir . '/asosiasi-members/images';
    }

    /**
     * Create all required directories
     *
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function create_directories() {
        try {
            // Create SKP directory
            $result = $this->create_skp_directory();
            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }

            // Create images directory
            $result = $this->create_images_directory();
            if (is_wp_error($result)) {
                throw new Exception($result->get_error_message());
            }

            return true;
        } catch (Exception $e) {
            return new WP_Error(
                'directory_creation_failed',
                sprintf(__('Failed to create directories: %s', 'asosiasi'), $e->getMessage())
            );
        }
    }

    /**
     * Create SKP directory with protection
     *
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    private function create_skp_directory() {
        // Create directory if it doesn't exist
        if (!file_exists($this->skp_dir)) {
            if (!wp_mkdir_p($this->skp_dir)) {
                return new WP_Error(
                    'skp_dir_creation_failed',
                    __('Failed to create SKP directory', 'asosiasi')
                );
            }

            // Add protection files
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "<FilesMatch '\.(pdf)$'>\n";
            $htaccess_content .= "    Order Allow,Deny\n";
            $htaccess_content .= "    Deny from all\n";
            $htaccess_content .= "</FilesMatch>\n";

            $index_content = "<?php\n// Silence is golden";

            // Write protection files
            $result = file_put_contents($this->skp_dir . '/.htaccess', $htaccess_content);
            if ($result === false) {
                return new WP_Error(
                    'htaccess_write_failed',
                    __('Failed to create .htaccess in SKP directory', 'asosiasi')
                );
            }

            $result = file_put_contents($this->skp_dir . '/index.php', $index_content);
            if ($result === false) {
                return new WP_Error(
                    'index_write_failed',
                    __('Failed to create index.php in SKP directory', 'asosiasi')
                );
            }
        }

        return true;
    }

    /**
     * Create images directory with protection
     *
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    private function create_images_directory() {
        // Create directory if it doesn't exist
        if (!file_exists($this->images_dir)) {
            if (!wp_mkdir_p($this->images_dir)) {
                return new WP_Error(
                    'images_dir_creation_failed',
                    __('Failed to create images directory', 'asosiasi')
                );
            }

            // Add protection files
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "Order Allow,Deny\n";
            $htaccess_content .= "Deny from all\n";
            $htaccess_content .= "<FilesMatch '\.(jpg|jpeg|png)$'>\n";
            $htaccess_content .= "    Allow from all\n";
            $htaccess_content .= "</FilesMatch>\n";

            $index_content = "<?php\n// Silence is golden";

            // Write protection files
            $result = file_put_contents($this->images_dir . '/.htaccess', $htaccess_content);
            if ($result === false) {
                return new WP_Error(
                    'htaccess_write_failed',
                    __('Failed to create .htaccess in images directory', 'asosiasi')
                );
            }

            $result = file_put_contents($this->images_dir . '/index.php', $index_content);
            if ($result === false) {
                return new WP_Error(
                    'index_write_failed',
                    __('Failed to create index.php in images directory', 'asosiasi')
                );
            }
        }

        return true;
    }

    /**
     * Check if all required directories exist and are writable
     *
     * @return bool|WP_Error True if everything is OK, WP_Error if there are issues
     */
    public function check_directories() {
        $errors = array();

        // Check base upload directory
        if (!is_writable($this->base_upload_dir)) {
            $errors[] = __('Base upload directory is not writable', 'asosiasi');
        }

        // Check SKP directory
        if (file_exists($this->skp_dir)) {
            if (!is_writable($this->skp_dir)) {
                $errors[] = __('SKP directory exists but is not writable', 'asosiasi');
            }
        } else {
            $errors[] = __('SKP directory is missing', 'asosiasi');
        }

        // Check images directory
        if (file_exists($this->images_dir)) {
            if (!is_writable($this->images_dir)) {
                $errors[] = __('Images directory exists but is not writable', 'asosiasi');
            }
        } else {
            $errors[] = __('Images directory is missing', 'asosiasi');
        }

        if (!empty($errors)) {
            return new WP_Error(
                'directory_check_failed',
                implode('. ', $errors)
            );
        }

        return true;
    }

    /**
     * Get SKP directory path
     *
     * @return string
     */
    public function get_skp_dir() {
        return $this->skp_dir;
    }

    /**
     * Get images directory path
     *
     * @return string
     */
    public function get_images_dir() {
        return $this->images_dir;
    }

    /**
     * Delete all plugin upload directories
     * Use with caution - this will delete all uploaded files
     *
     * @return bool|WP_Error True on success, WP_Error on failure
     */
    public function delete_directories() {
        // Only allow in development/testing
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return new WP_Error(
                'operation_not_allowed',
                __('Directory deletion is only allowed in debug mode', 'asosiasi')
            );
        }

        $errors = array();

        // Delete SKP directory
        if (file_exists($this->skp_dir)) {
            if (!$this->recursive_delete($this->skp_dir)) {
                $errors[] = __('Failed to delete SKP directory', 'asosiasi');
            }
        }

        // Delete images directory
        if (file_exists($this->images_dir)) {
            if (!$this->recursive_delete($this->images_dir)) {
                $errors[] = __('Failed to delete images directory', 'asosiasi');
            }
        }

        if (!empty($errors)) {
            return new WP_Error(
                'directory_deletion_failed',
                implode('. ', $errors)
            );
        }

        return true;
    }

    /**
     * Recursively delete a directory
     *
     * @param string $dir Directory path
     * @return bool True on success, false on failure
     */
    private function recursive_delete($dir) {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->recursive_delete($path) : unlink($path);
        }

        return rmdir($dir);
    }
}