-- Migration: Ajout de RustDesk (ID et mot de passe) à côté de TeamViewer
-- Exécuter: mysql -u renaud -p itmanager < database/migration_rustdesk.sql

USE itmanager;

ALTER TABLE pcs_laptops 
    ADD COLUMN rustdesk_id VARCHAR(100) NULL AFTER teamviewer_id,
    ADD COLUMN rustdesk_password VARCHAR(100) NULL AFTER rustdesk_id;

ALTER TABLE servers 
    ADD COLUMN rustdesk_id VARCHAR(100) NULL AFTER teamviewer_id,
    ADD COLUMN rustdesk_password VARCHAR(100) NULL AFTER rustdesk_id;
