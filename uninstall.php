<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

// Hapus data asosiasi dari database
global $wpdb;
$table_name = $wpdb->prefix . "asosiasi_members";
$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
