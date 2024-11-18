-- Path: sql/skp-perusahaan.sql
-- Description: SKP Perusahaan table with status enum
-- 
-- @package Asosiasi
-- @version 1.1.0
-- Changelog:
-- 1.1.0 - 2024-11-17
-- - Added 'activated' to status enum
-- 1.0.0 - 2024-03-15
-- - Initial creation with service_id support

CREATE TABLE IF NOT EXISTS {table_skp_perusahaan} (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    member_id mediumint(9) NOT NULL,
    service_id mediumint(9) NOT NULL,
    nomor_skp varchar(100) NOT NULL,
    penanggung_jawab varchar(255) NOT NULL,
    tanggal_terbit date NOT NULL,
    masa_berlaku date NOT NULL,
    file_path varchar(255) NOT NULL,
    status enum('active', 'expired', 'inactive', 'activated') NOT NULL DEFAULT 'active',
    status_changed_at datetime DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY member_id (member_id),
    KEY service_id (service_id),
    KEY status (status),
    FOREIGN KEY (member_id) REFERENCES {table_members}(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES {table_services}(id) ON DELETE CASCADE
) {charset_collate};

-- Ensure status enum includes 'activated'
ALTER TABLE {table_skp_perusahaan} 
MODIFY COLUMN status enum('active', 'expired', 'inactive', 'activated') 
NOT NULL DEFAULT 'active';