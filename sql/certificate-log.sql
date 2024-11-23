-- Path: sql/certificate-log.sql
-- Certificate generation log table
-- 
-- @package Asosiasi
-- @version 1.0.1
-- 
-- Changelog:
-- 1.0.1 - 2024-11-21
-- - Fixed generated_by column type to match WordPress users.ID
-- - Added UNSIGNED attribute for generated_by
-- 1.0.0 - Initial version

CREATE TABLE IF NOT EXISTS {table_certificate_log} (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    member_id mediumint(9) NOT NULL,
    cert_number varchar(50) NOT NULL, 
    generated_at datetime NOT NULL,
    generated_by bigint(20) UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    KEY member_id (member_id),
    KEY cert_number (cert_number),
    KEY generated_at (generated_at),
    FOREIGN KEY (member_id) REFERENCES {table_members}(id) ON DELETE CASCADE,
    FOREIGN KEY (generated_by) REFERENCES {wp_users_table}(ID) ON DELETE CASCADE
) {charset_collate};
