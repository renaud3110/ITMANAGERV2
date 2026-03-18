-- Migration: planification découverte ESXi (manuel / horaire)
-- mysql -u user -p itmanager < database/migration_esxi_discovery_schedule.sql
-- 0 = manuel uniquement, 1 = toutes les heures, 2 = toutes les 2h, etc.

USE itmanager;

ALTER TABLE esxi_hosts ADD COLUMN discovery_interval_hours TINYINT UNSIGNED DEFAULT 1 
  COMMENT '0=manuel, 1=1h, 2=2h...';
