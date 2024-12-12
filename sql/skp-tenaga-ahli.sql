-- Path: sql/skp-tenaga-ahli.sql
-- Description: SKP Tenaga Ahli table with status enum
-- 
-- @package Asosiasi
-- @version 1.0.0
-- @since 2.3.0
-- 
-- Changelog:
-- 1.0.0 - 2024-11-22
-- - Initial creation with basic fields
-- - Added status enum similar to SKP Perusahaan
-- - Added foreign key constraints

CREATE TABLE IF NOT EXISTS {table_skp_tenaga_ahli} (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    member_id mediumint(9) NOT NULL,
    service_id mediumint(9) NOT NULL,
    nomor_skp varchar(100) NOT NULL,
    nama_tenaga_ahli varchar(255) NOT NULL,
    penanggung_jawab varchar(100) NOT NULL,
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
    KEY nama_tenaga_ahli (nama_tenaga_ahli),
    FOREIGN KEY (member_id) REFERENCES {table_members}(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES {table_services}(id) ON DELETE CASCADE
) {charset_collate};

-- Ensure status enum includes 'activated'
ALTER TABLE {table_skp_tenaga_ahli} 
MODIFY COLUMN status enum('active', 'expired', 'inactive', 'activated') 
NOT NULL DEFAULT 'active';