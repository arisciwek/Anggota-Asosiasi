-- Path: sql/services.sql
-- Description: Services and member-services relation tables
-- 
-- @package Asosiasi
-- @version 1.0.0
-- Changelog:
-- 1.0.0 - 2024-03-13
-- - Initial creation

-- Services table

CREATE TABLE IF NOT EXISTS {table_services} (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    short_name varchar(50) NOT NULL,
    full_name varchar(255) NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) {charset_collate};
*