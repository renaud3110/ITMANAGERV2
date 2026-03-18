-- logged_in et last_logout_at pour serveurs (comme pc_monitor_status)
-- mysql -u renaud -p itmanager < database/migration_server_monitor_logged_in.sql

USE itmanager;

ALTER TABLE server_monitor_status ADD COLUMN logged_in TINYINT(1) NULL AFTER last_seen;
ALTER TABLE server_monitor_status ADD COLUMN last_logout_at DATETIME NULL AFTER logged_in;
