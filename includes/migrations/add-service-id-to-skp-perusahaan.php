<?php

/**
 * Database migration for adding service_id to SKP Perusahaan
 * 
 * @package Asosiasi
 * @version 1.0.0
 * Path: includes/migrations/add-service-id-to-skp-perusahaan.php
 */

function asosiasi_migrate_add_service_id() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'asosiasi_skp_perusahaan';
    
    // Check if service_id column exists
    $column = $wpdb->get_results($wpdb->prepare(
        "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS 
         WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = 'service_id'",
        DB_NAME,
        $table_name
    ));
    
    if (empty($column)) {
        // Add service_id column
        $wpdb->query("ALTER TABLE {$table_name} 
                     ADD COLUMN service_id mediumint(9) NOT NULL AFTER member_id,
                     ADD KEY service_id (service_id),
                     ADD CONSTRAINT fk_skp_service 
                     FOREIGN KEY (service_id) 
                     REFERENCES {$wpdb->prefix}asosiasi_services(id) 
                     ON DELETE CASCADE");
                     
        // Get default service for existing SKPs
        $default_service = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}asosiasi_services ORDER BY id LIMIT 1");
        
        if ($default_service) {
            // Update existing records with default service
            $wpdb->query($wpdb->prepare(
                "UPDATE {$table_name} SET service_id = %d WHERE service_id = 0",
                $default_service
            ));
        }
    }
}