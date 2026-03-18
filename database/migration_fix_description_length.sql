-- Fix: colonnes description trop courtes (descriptions Windows parfois tres longues)
-- mysql -u renaud -p itmanager < database/migration_fix_description_length.sql

USE itmanager;

ALTER TABLE pc_windows_services MODIFY COLUMN description TEXT NULL;
ALTER TABLE pc_windows_shared MODIFY COLUMN description TEXT NULL;
ALTER TABLE pc_windows_updates MODIFY COLUMN description TEXT NULL;
ALTER TABLE pc_windows_license MODIFY COLUMN description TEXT NULL;
