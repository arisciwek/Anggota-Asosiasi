<?php
/**
 * Fired during plugin activation
 *
 * @package Asosiasi
 * @version 1.4.0
 * Path: includes/class-asosiasi-activator.php
 * 
 * Changelog:
 * 1.4.0 - Added SKP Perusahaan table
 * 1.3.0 - Initial version
 */

class Asosiasi_Activator {

    public static function activate() {
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

        // Tabel SKP Perusahaan (New)
        $table_skp_perusahaan = $wpdb->prefix . 'asosiasi_skp_perusahaan';
        $sql_skp_perusahaan = "CREATE TABLE IF NOT EXISTS $table_skp_perusahaan (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            member_id mediumint(9) NOT NULL,
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
            KEY status (status),
            FOREIGN KEY (member_id) REFERENCES $table_members(id) ON DELETE CASCADE
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_members);
        dbDelta($sql_services);
        dbDelta($sql_member_services);
        dbDelta($sql_skp_perusahaan);

        // Create upload directory for SKP files
        $upload_dir = wp_upload_dir();
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

        add_option('asosiasi_version', ASOSIASI_VERSION);
        add_option('asosiasi_organization_name', '');
        add_option('asosiasi_contact_email', '');

        // Schedule SKP status check
        Asosiasi_SKP_Cron::schedule_events();

        // Flush rewrite rules untuk endpoint baru
        flush_rewrite_rules();
    }

}
