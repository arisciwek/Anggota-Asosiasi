-- Path: sql/members.sql
-- Description: Core members table
-- 
-- @package Asosiasi
-- @version 1.0.0
-- Changelog:
-- 1.0.0 - 2024-03-13 09:09:01
-- - Initial creation

CREATE TABLE IF NOT EXISTS {table_members} (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    company_name varchar(255) NOT NULL,
    contact_person varchar(255) NOT NULL,
    email varchar(100) NOT NULL,
    phone varchar(20),
    created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) {charset_collate};

ALTER TABLE {table_members} ADD COLUMN (
    company_leader VARCHAR(100),
    leader_position VARCHAR(100),
    company_address TEXT,
    postal_code VARCHAR(10),
    business_field VARCHAR(100),
    ahu_number VARCHAR(100), 
    city VARCHAR(100),
    npwp VARCHAR(50)
);
