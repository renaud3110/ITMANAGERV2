-- Nom de l'utilisateur connecté (envoyé par l'agent moniteur)
-- mysql -u renaud -p itmanager < database/migration_monitor_logged_in_username.sql

USE itmanager;

-- PC (last_logout_at existe via migration_monitor_logged_in)
ALTER TABLE pc_monitor_status ADD COLUMN logged_in_username VARCHAR(255) NULL AFTER last_logout_at;

-- Serveurs
ALTER TABLE server_monitor_status ADD COLUMN logged_in_username VARCHAR(255) NULL AFTER last_logout_at;
