-- Migration: Détails CPU pour serveurs (cores, vitesse, manufacturer, family)
-- mysql -u renaud -p itmanager < database/migration_servers_cpu_details.sql

ALTER TABLE servers ADD COLUMN processor_cores INT NULL AFTER processor_model;
ALTER TABLE servers ADD COLUMN processor_speed_mhz DECIMAL(10,2) NULL AFTER processor_cores;
ALTER TABLE servers ADD COLUMN processor_manufacturer VARCHAR(255) NULL AFTER processor_speed_mhz;
ALTER TABLE servers ADD COLUMN processor_family VARCHAR(255) NULL AFTER processor_manufacturer;
