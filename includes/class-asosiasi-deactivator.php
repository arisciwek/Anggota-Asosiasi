<?php
/**
 * Fired during plugin deactivation
 *
 * @package Asosiasi
 * @version 1.1.0
 */

class Asosiasi_Deactivator {

    /**
     * Deaktivasi plugin
     *
     * @since    1.1.0
     */
    public static function deactivate() {
        // Remove scheduled events if any
        wp_clear_scheduled_hook('asosiasi_daily_cleanup');
        
        // Clear any transients we've set
        delete_transient('asosiasi_members_count');
        
        // Clear permalinks
        flush_rewrite_rules();
    }
}