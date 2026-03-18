-- Type, modèle et fréquence RAM
ALTER TABLE pcs_laptops ADD COLUMN ram_type VARCHAR(50) NULL;
ALTER TABLE pcs_laptops ADD COLUMN ram_model VARCHAR(255) NULL;
ALTER TABLE pcs_laptops ADD COLUMN ram_frequency_mhz INT NULL;
