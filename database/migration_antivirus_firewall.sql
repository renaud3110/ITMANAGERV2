-- Colonnes antivirus et firewall pour pcs_laptops
-- Exécuter une seule fois. Si les colonnes existent déjà, ignorer les erreurs.

ALTER TABLE pcs_laptops ADD COLUMN antivirus_name VARCHAR(255) NULL AFTER last_account;
ALTER TABLE pcs_laptops ADD COLUMN antivirus_enabled TINYINT(1) NULL AFTER antivirus_name;
ALTER TABLE pcs_laptops ADD COLUMN antivirus_updated TINYINT(1) NULL AFTER antivirus_enabled;
ALTER TABLE pcs_laptops ADD COLUMN firewall_enabled TINYINT(1) NULL AFTER antivirus_updated;
