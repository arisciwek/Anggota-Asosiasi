<?php
/**
 * Fired during plugin activation
 *
 * @package Asosiasi
 * @version 2.3.0
 * Path: includes/class-asosiasi-activator.php
 * 
 * Changelog:
 * 
 * 2.0.0* 2024-12-08
 * Added 'code' column to provinces table
 * Added database upgrade mechanism
 * Added unique constraint for province code
 * Updated table creation structure
 *
 * 2.3.0 - 2024-11-17 10:10:08
 * - Moved all SQL to separate files
 * - Added SQL file loader
 * - Added status history support
 * 2.2.0 - 2024-03-15
 * - Added service_id column to SKP Perusahaan table
 * 2.1.0 - 2024-03-13
 * - Added member_images table
 */

class Asosiasi_Activator {

    public static function activate() {
        global $wpdb;

        $current_db_version = get_option('asosiasi_db_version', '0');

        self::create_initial_tables();
        self::upgradeDatabase(); // Tambahkan ini
        //self::addVersion();
        
        // Set default options
        self::setup_default_options();

        // Schedule cron if needed
        if (class_exists('Asosiasi_SKP_Cron')) {
            Asosiasi_SKP_Cron::schedule_events();
        }

        flush_rewrite_rules();
    }

    /**
     * Migration untuk menambah field member baru
     */
    private static function migrate_member_fields() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'asosiasi_members';

        try {
            // Array of new columns and their definitions
            $new_columns = array(
                'company_leader' => 'VARCHAR(100)',
                'leader_position' => 'VARCHAR(100)', 
                'company_address' => 'TEXT',
                'postal_code' => 'VARCHAR(10)',
                'business_field' => 'VARCHAR(100)',
                'ahu_number' => 'VARCHAR(100)',
                'city' => 'VARCHAR(100)',
                'npwp' => 'VARCHAR(50)'
            );

            // Add each column if it doesn't exist
            foreach ($new_columns as $column => $definition) {
                $check_column = $wpdb->get_results($wpdb->prepare(
                    "SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
                     WHERE TABLE_SCHEMA = %s 
                     AND TABLE_NAME = %s 
                     AND COLUMN_NAME = %s",
                    DB_NAME,
                    $table_name,
                    $column
                ));

                if (empty($check_column)) {
                    $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN {$column} {$definition}");
                    
                    if (WP_DEBUG) {
                        error_log("Added column {$column} to members table");
                    }
                }
            }

            if (WP_DEBUG) {
                error_log('Member fields migration completed successfully');
            }

            return true;

        } catch (Exception $e) {
            if (WP_DEBUG) {
                error_log('Member fields migration failed: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Load SQL from file and replace placeholders
     *
     * @param string $filename SQL filename without extension
     * @param array $replacements Key-value pairs for replacements
     * @return string|WP_Error SQL query string or WP_Error on failure
     */
    private static function load_sql_file($filename, $replacements = array()) {
        $sql_file = ASOSIASI_DIR . 'sql/' . $filename . '.sql';
        
        if (!file_exists($sql_file)) {
            return new WP_Error(
                'sql_missing',
                sprintf(__('SQL file %s not found', 'asosiasi'), $sql_file)
            );
        }

        $sql = file_get_contents($sql_file);
        if ($sql === false) {
            return new WP_Error(
                'sql_read_error',
                sprintf(__('Failed to read SQL file %s', 'asosiasi'), $sql_file)
            );
        }

        // Default replacements
        global $wpdb;
        $default_replacements = array(
            '{charset_collate}' => $wpdb->get_charset_collate(),
            '{wp_prefix}' => $wpdb->prefix,
            '{table_members}' => $wpdb->prefix . 'asosiasi_members',
            '{table_services}' => $wpdb->prefix . 'asosiasi_services',
            '{table_member_services}' => $wpdb->prefix . 'asosiasi_member_services',
            '{table_skp_perusahaan}' => $wpdb->prefix . 'asosiasi_skp_perusahaan',
            '{table_skp_tenaga_ahli}' => $wpdb->prefix . 'asosiasi_skp_tenaga_ahli',
            '{table_member_images}' => $wpdb->prefix . 'asosiasi_member_images',
            '{table_status_history}' => $wpdb->prefix . 'asosiasi_skp_status_history',
            '{table_skp_tenaga_ahli_history}' => $wpdb->prefix . 'asosiasi_skp_tenaga_ahli_history',
            '{table_certificate_log}' => $wpdb->prefix . 'asosiasi_certificate_log',
            '{wp_users_table}' => $wpdb->users
        );

        $replacements = array_merge($default_replacements, $replacements);
        return strtr($sql, $replacements);
    }

    /**
     * Create all database tables
     */
    private static function create_initial_tables() {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Table creation order is important for foreign keys
        $tables = array(
            'members',        // Base table
            'services',       // Contains both services and member_services
            'member-images',  // Depends on members
            'skp-perusahaan', // Depends on members and services
            'skp-tenaga-ahli', // Depends on members and services
            'status-history',  // Depends on skp_perusahaan
            'skp-tenaga-ahli-history', // Tambahkan ini untuk history Tenaga Ahli
            'certificate-log'    // Depends on members and users
        );
        
        foreach ($tables as $table) {
            $sql = self::load_sql_file($table);
            if (is_wp_error($sql)) {
                error_log(sprintf(
                    '[Asosiasi] Failed to load SQL file: %s - %s',
                    $table,
                    $sql->get_error_message()
                ));
                continue;
            }
            dbDelta($sql);
        }
    }

    /**
     * Create certificate related tables
     */
    private static function create_certificate_tables() {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        // Create upload directory
        $upload_dir = wp_upload_dir();
        $cert_dir = $upload_dir['basedir'] . '/asosiasi-certificates';
        
        if (!file_exists($cert_dir)) {
            wp_mkdir_p($cert_dir);
            
            // Add .htaccess for security
            //$htaccess = $cert_dir . '/.htaccess';
            $htaccess =  $cert_dir . '/templates/.htaccess';

            if (!file_exists($htaccess)) {
                $content = "Options -Indexes\n";
                $content .= "<FilesMatch '\.(php|php\.|php3|php4|php5|php7|phtml|pl|py|jsp|asp|htm|shtml|sh|cgi)$'>\n";
                $content .= "Order Deny,Allow\n";
                $content .= "Deny from all\n";
                $content .= "</FilesMatch>\n";
                
                file_put_contents($htaccess, $content);
            }

            // Add index.php
            $index = $cert_dir . '/index.php';
            if (!file_exists($index)) {
                file_put_contents($index, '<?php // Silence is golden');
            }
        }
    }

    /**
     * Migration untuk menambahkan status 'activated' ke enum
     */
    private static function migrate_skp_status_enum() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'asosiasi_skp_perusahaan';
        
        // Log untuk debugging
        if (WP_DEBUG) {
            error_log('Starting SKP status enum migration...');
        }

        try {
            // Jalankan ALTER TABLE langsung
            $wpdb->query("ALTER TABLE {$table_name} 
                         MODIFY COLUMN status 
                         ENUM('active', 'expired', 'inactive', 'activated') 
                         NOT NULL DEFAULT 'active'");

            if (WP_DEBUG) {
                error_log('SKP status enum migration completed successfully');
            }
            
            return true;
        } catch (Exception $e) {
            if (WP_DEBUG) {
                error_log('SKP status enum migration failed: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Create required upload directories
     */
    private static function setup_upload_directories() {
        $directories = new Asosiasi_Upload_Directories();
        $result = $directories->create_directories();
        
        if (is_wp_error($result)) {
            error_log(sprintf(
                '[Asosiasi] Failed to setup upload directories: %s',
                $result->get_error_message()
            ));
        }
    }

    /**
     * Set up default plugin options
     */
    private static function setup_default_options() {
        add_option('asosiasi_version', ASOSIASI_VERSION);
        add_option('asosiasi_organization_name', '');
        add_option('asosiasi_contact_email', '');
    }

    /**
     * Handle database upgrades between versions
     * Called during plugin activation
     */
    private static function upgradeDatabase() {
        $current_db_version = get_option('asosiasi_db_version', '0');

        // Run specific migrations based on version
        if (version_compare($current_db_version, '2.3.0', '<')) {
            self::migrate_to_2_3_0();
            update_option('asosiasi_db_version', '2.3.0');
        }

        // Add 'activated' status to SKP enum
        if (version_compare($current_db_version, '2.3.1', '<')) {
            self::migrate_skp_status_enum();
            update_option('asosiasi_db_version', '2.3.1');
        }

        // Add new member fields
        if (version_compare($current_db_version, '2.4.0', '<')) {
            self::migrate_member_fields();
            update_option('asosiasi_db_version', '2.4.0');
        }

        // Create SKP Tenaga Ahli table
        if (version_compare($current_db_version, '2.5.0', '<')) {
            self::create_skp_tenaga_ahli_table();
            update_option('asosiasi_db_version', '2.5.0');
        }

    }

}
