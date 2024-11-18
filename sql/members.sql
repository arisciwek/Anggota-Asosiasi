-- Path: sql/members.sql
-- Description: Core members table
-- 
-- @package Asosiasi
-- @version 1.0.0
-- Changelog:
-- 1.0.0 - 2024-03-13
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