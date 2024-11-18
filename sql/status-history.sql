-- Path: sql/status-history.sql
-- Description: SKP status change history tracking
-- 
-- @package Asosiasi
-- @version 1.0.0
-- Changelog:
-- 1.0.0 - 2024-11-17
-- - Initial creation

CREATE TABLE IF NOT EXISTS {table_status_history} (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    skp_id mediumint(9) NOT NULL,
    skp_type varchar(20) NOT NULL,
    old_status varchar(20) NOT NULL,
    new_status varchar(20) NOT NULL,
    reason text NOT NULL,
    changed_by bigint(20) unsigned NOT NULL,
    changed_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY skp_id (skp_id),
    KEY changed_by (changed_by),
    KEY changed_at (changed_at),
    FOREIGN KEY (skp_id) REFERENCES {table_skp_perusahaan}(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES {wp_users_table}(ID) ON DELETE CASCADE
) {charset_collate};