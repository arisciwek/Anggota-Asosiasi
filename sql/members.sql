-- Path: sql/members.sql
-- Description: Table structure for members
-- 
-- @package Asosiasi
-- @version 2.4.0
-- Changelog:
-- 2.4.0 - 2024-11-19
-- - Added new member info fields
-- - Added company and address fields

CREATE TABLE IF NOT EXISTS {table_members} (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    company_name varchar(200) NOT NULL,
    contact_person varchar(100) NOT NULL,
    email varchar(100) NOT NULL,
    phone varchar(20),
    company_leader varchar(100),
    leader_position varchar(100),
    company_address text,
    postal_code varchar(10),
    business_field varchar(100),
    ahu_number varchar(100),
    city varchar(100),
    npwp varchar(50),
    -- New fields for certificate
    nomor_sertifikat varchar(100) DEFAULT NULL UNIQUE,
    tanggal_cetak datetime DEFAULT NULL,
    -- End new fields
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY  (id),
    KEY idx_email (email),
    KEY idx_company (company_name),
    KEY idx_cert_number (nomor_sertifikat)
) {charset_collate};
