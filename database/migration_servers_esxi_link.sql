-- Migration: colonnes serveurs pour lien ESXi VM
-- mysql -u user -p itmanager < database/migration_servers_esxi_link.sql
-- Si erreur "Duplicate column" : déjà appliqué, ignorer.

USE itmanager;

ALTER TABLE servers ADD COLUMN vm_uuid VARCHAR(64) NULL AFTER hostname;
ALTER TABLE servers ADD COLUMN esxi_vm_id INT NULL AFTER vm_uuid;
