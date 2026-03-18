-- Migration: Support des disques pour serveurs (physical_disks)
-- mysql -u renaud -p itmanager < database/migration_physical_disks_servers.sql
-- Permet de stocker les disques des serveurs dans physical_disks (comme les PC)

ALTER TABLE physical_disks MODIFY pc_id INT NULL;
ALTER TABLE physical_disks ADD COLUMN server_id INT NULL AFTER pc_id;
ALTER TABLE physical_disks ADD KEY idx_physical_disks_server (server_id);
ALTER TABLE physical_disks ADD CONSTRAINT fk_physical_disks_server 
    FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE;
