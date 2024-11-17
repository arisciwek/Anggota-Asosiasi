<?php

/**
 * Fired during plugin activation
 *
 * @package Asosiasi
 * @version 2.2.0
 * Path: includes/class-asosiasi-activator.php
 * 
 * Changelog:
 * 2.2.0 - 2024-03-15
 * - Added service_id column to SKP Perusahaan table
 * - Added foreign key constraint for service_id
 * 2.1.0 - 2024-03-13
 * - Added member_images table
 * - Added images upload directory creation
 * 1.4.0 - Added SKP Perusahaan table
 * 1.3.0 - Initial version
 */

class Asosiasi_Activator {

    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $current_db_version = get_option('asosiasi_db_version', '0');

        // Create tables
        self::create_initial_tables($charset_collate);

        // Run migrations if needed
        if (version_compare($current_db_version, '2.2.0', '<')) {
            self::migrate_add_service_id();
            update_option('asosiasi_db_version', '2.2.0');
        }

        // Other activations
        add_option('asosiasi_version', ASOSIASI_VERSION);
        add_option('asosiasi_organization_name', '');
        add_option('asosiasi_contact_email', '');

        if (class_exists('Asosiasi_SKP_Cron')) {
            Asosiasi_SKP_Cron::schedule_events();
        }

        flush_rewrite_rules();
    }

    private static function create_initial_tables($charset_collate) {
        global $wpdb;
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Tabel anggota
        $table_members = $wpdb->prefix . 'asosiasi_members';
        $sql_members = "CREATE TABLE IF NOT EXISTS $table_members (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            company_name varchar(255) NOT NULL,
            contact_person varchar(255) NOT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20),
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Tabel layanan
        $table_services = $wpdb->prefix . 'asosiasi_services';
        $sql_services = "CREATE TABLE IF NOT EXISTS $table_services (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            short_name varchar(50) NOT NULL,
            full_name varchar(255) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Tabel relasi anggota-layanan
        $table_member_services = $wpdb->prefix . 'asosiasi_member_services';
        $sql_member_services = "CREATE TABLE IF NOT EXISTS $table_member_services (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            member_id mediumint(9) NOT NULL,
            service_id mediumint(9) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY member_service (member_id, service_id),
            FOREIGN KEY (member_id) REFERENCES $table_members(id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES $table_services(id) ON DELETE CASCADE
        ) $charset_collate;";

        // Tabel SKP Perusahaan updated
        $table_skp_perusahaan = $wpdb->prefix . 'asosiasi_skp_perusahaan';
        $sql_skp_perusahaan = "CREATE TABLE IF NOT EXISTS $table_skp_perusahaan (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            member_id mediumint(9) NOT NULL,
            service_id mediumint(9) NOT NULL,         /* New column */
            nomor_skp varchar(100) NOT NULL,
            penanggung_jawab varchar(255) NOT NULL,
            tanggal_terbit date NOT NULL,
            masa_berlaku date NOT NULL,
            file_path varchar(255) NOT NULL,
            status enum('active', 'expired', 'inactive') NOT NULL DEFAULT 'active',
            status_changed_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY member_id (member_id),
            KEY service_id (service_id),              /* New index */
            KEY status (status),
            FOREIGN KEY (member_id) REFERENCES $table_members(id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES $table_services(id) ON DELETE CASCADE  /* New foreign key */
        ) $charset_collate;";

        // Tabel Member Images (New)
        $table_member_images = $wpdb->prefix . 'asosiasi_member_images';
        $sql_member_images = "CREATE TABLE IF NOT EXISTS $table_member_images (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            member_id mediumint(9) NOT NULL,
            image_type enum('mandatory','optional') NOT NULL,
            image_order tinyint NOT NULL DEFAULT 0,
            file_name varchar(255) NOT NULL,
            file_path varchar(255) NOT NULL,
            uploaded_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY member_id (member_id),
            KEY image_type (image_type),
            FOREIGN KEY (member_id) REFERENCES $table_members(id) ON DELETE CASCADE
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_members);
        dbDelta($sql_services);
        dbDelta($sql_member_services);
        dbDelta($sql_skp_perusahaan);
        dbDelta($sql_member_images);

        // Create upload directories
        $upload_dir = wp_upload_dir();
        
        // For SKP files
        $skp_dir = $upload_dir['basedir'] . '/asosiasi-skp/perusahaan';
        if (!file_exists($skp_dir)) {
            wp_mkdir_p($skp_dir);
            
            // Protect directory
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "<FilesMatch '\.(pdf)$'>\n";
            $htaccess_content .= "    Order Allow,Deny\n";
            $htaccess_content .= "    Deny from all\n";
            $htaccess_content .= "</FilesMatch>\n";
            
            @file_put_contents($skp_dir . '/.htaccess', $htaccess_content);
        }

        // For member images
        $images_dir = $upload_dir['basedir'] . '/asosiasi-members/images';
        if (!file_exists($images_dir)) {
            wp_mkdir_p($images_dir);
            
            // Protect directory from direct access
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "Order Allow,Deny\n";
            $htaccess_content .= "Deny from all\n";
            $htaccess_content .= "<FilesMatch '\.(jpg|jpeg|png)$'>\n";
            $htaccess_content .= "    Allow from all\n";
            $htaccess_content .= "</FilesMatch>\n";
            
            @file_put_contents($images_dir . '/.htaccess', $htaccess_content);
        }

        add_option('asosiasi_version', ASOSIASI_VERSION);
        add_option('asosiasi_organization_name', '');
        add_option('asosiasi_contact_email', '');

        // Schedule SKP status check
        if (class_exists('Asosiasi_SKP_Cron')) {
            Asosiasi_SKP_Cron::schedule_events();
        }

        // Flush rewrite rules
        flush_rewrite_rules();
    
    }

    private static function migrate_add_service_id() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'asosiasi_skp_perusahaan';
        
        // Check if column exists
        $column = $wpdb->get_results($wpdb->prepare(
            "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
             WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'service_id'",
            DB_NAME,
            $table_name
        ));

        if (empty($column)) {
            // First add column without constraints
            $wpdb->query("ALTER TABLE {$table_name} 
                         ADD COLUMN service_id mediumint(9) NULL AFTER member_id");

            // Get default service
            $default_service = $wpdb->get_var(
                "SELECT id FROM {$wpdb->prefix}asosiasi_services ORDER BY id LIMIT 1"
            );

            if ($default_service) {
                // Update existing records
                $wpdb->query($wpdb->prepare(
                    "UPDATE {$table_name} SET service_id = %d WHERE service_id IS NULL",
                    $default_service
                ));
            }

            // Now add constraints
            $wpdb->query("ALTER TABLE {$table_name}
                         MODIFY COLUMN service_id mediumint(9) NOT NULL,
                         ADD KEY service_id (service_id),
                         ADD CONSTRAINT fk_skp_service 
                         FOREIGN KEY (service_id) 
                         REFERENCES {$wpdb->prefix}asosiasi_services(id) 
                         ON DELETE CASCADE");
        }
    }

}
