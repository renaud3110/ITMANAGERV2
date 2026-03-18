-- Colonnes pour statut utilisateur connecté (moniteur)
-- logged_in: 1 = quelqu'un est connecté, 0 = déconnecté, NULL = non détecté
-- last_logout_at: date/heure de la dernière déconnexion (rempli par l'API lors de la transition)

ALTER TABLE pc_monitor_status ADD COLUMN logged_in TINYINT(1) NULL AFTER last_seen;
ALTER TABLE pc_monitor_status ADD COLUMN last_logout_at DATETIME NULL AFTER logged_in;
