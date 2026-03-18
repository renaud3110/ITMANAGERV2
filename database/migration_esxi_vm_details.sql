-- Migration: auto_start et disques pour les VMs ESXi
-- mysql -u user -p itmanager < database/migration_esxi_vm_details.sql

USE itmanager;

ALTER TABLE esxi_vms ADD COLUMN auto_start TINYINT(1) DEFAULT 0 COMMENT '1=demarrage auto (powerOn)';
ALTER TABLE esxi_vms ADD COLUMN disks_json TEXT NULL COMMENT 'JSON: [{label, capacity_gb, datastore, filename}]';
