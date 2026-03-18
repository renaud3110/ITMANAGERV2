-- Colonnes pour détails CPU (cores, vitesse, fabricant, famille)
-- Exécuter une seule fois. Si les colonnes existent déjà, ignorer les erreurs.
ALTER TABLE pcs_laptops ADD COLUMN processor_cores INT NULL AFTER processor_model;
ALTER TABLE pcs_laptops ADD COLUMN processor_speed_mhz DECIMAL(10,2) NULL AFTER processor_cores;
ALTER TABLE pcs_laptops ADD COLUMN processor_manufacturer VARCHAR(100) NULL AFTER processor_speed_mhz;
ALTER TABLE pcs_laptops ADD COLUMN processor_family VARCHAR(100) NULL AFTER processor_manufacturer;
