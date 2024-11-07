<?php
/**
 * Fired during plugin activation
 *
 * @package Asosiasi
 * @version 1.3.0
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

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_members);
        dbDelta($sql_services);
        dbDelta($sql_member_services);

        add_option('asosiasi_version', ASOSIASI_VERSION);
        add_option('asosiasi_organization_name', '');
        add_option('asosiasi_contact_email', '');
    }
}