-- Path: sql/member-services.sql
-- Description: Member-services relation tables
-- 
-- @package Asosiasi
-- @version 1.0.0
-- Changelog:
-- 1.0.0 - 2024-03-13
-- - Initial creation

-- Services table

CREATE TABLE IF NOT EXISTS {table_member_services} (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    member_id mediumint(9) NOT NULL,
    service_id mediumint(9) NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY member_service (member_id, service_id),
    FOREIGN KEY (member_id) REFERENCES {table_members}(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES {table_services}(id) ON DELETE CASCADE
) {charset_collate};
