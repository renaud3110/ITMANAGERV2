-- Migration: Support Proxmox dans la section virtualisation (esxi_hosts)
-- mysql -u user -p itmanager < database/migration_proxmox.sql

USE itmanager;

-- Type d'hyperviseur : esxi (port 443) ou proxmox (port 8006)
ALTER TABLE esxi_hosts ADD COLUMN hypervisor_type VARCHAR(20) DEFAULT 'esxi' AFTER description;
UPDATE esxi_hosts SET hypervisor_type = 'esxi' WHERE hypervisor_type IS NULL;
