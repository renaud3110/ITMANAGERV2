-- Colonnes antivirus et firewall pour servers (comme pcs_laptops)
ALTER TABLE servers ADD COLUMN antivirus_name VARCHAR(255) NULL;
ALTER TABLE servers ADD COLUMN antivirus_enabled TINYINT(1) NULL;
ALTER TABLE servers ADD COLUMN antivirus_updated TINYINT(1) NULL;
ALTER TABLE servers ADD COLUMN firewall_enabled TINYINT(1) NULL;
