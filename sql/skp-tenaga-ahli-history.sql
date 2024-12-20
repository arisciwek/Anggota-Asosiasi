CREATE TABLE IF NOT EXISTS {table_skp_tenaga_ahli_history} (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    skp_id mediumint(9) NOT NULL,
    skp_type varchar(20) NOT NULL DEFAULT 'tenaga_ahli',
    old_status varchar(20) NOT NULL,
    new_status varchar(20) NOT NULL,
    reason text NOT NULL,
    changed_by bigint(20) unsigned NOT NULL,
    changed_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY skp_id (skp_id),
    KEY changed_by (changed_by),
    FOREIGN KEY (skp_id) REFERENCES {table_skp_tenaga_ahli}(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES {wp_users_table}(ID) ON DELETE CASCADE
) {charset_collate};