-- Path: sql/member-images.sql
-- Description: Member images table for mandatory and optional images
-- 
-- @package Asosiasi
-- @version 1.0.0
-- Changelog:
-- 1.0.0 - 2024-03-13
-- - Initial creation

CREATE TABLE IF NOT EXISTS {table_member_images} (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    member_id mediumint(9) NOT NULL,
    image_type enum('mandatory','optional') NOT NULL,
    image_order tinyint NOT NULL DEFAULT 0,
    file_name varchar(255) NOT NULL,
    file_path varchar(255) NOT NULL,
    uploaded_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY member_id (member_id),
    KEY image_type (image_type),
    FOREIGN KEY (member_id) REFERENCES {table_members}(id) ON DELETE CASCADE
) {charset_collate};