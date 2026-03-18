-- S/N carte mère et version BIOS
ALTER TABLE pcs_laptops ADD COLUMN motherboard_serial VARCHAR(255) NULL;
ALTER TABLE pcs_laptops ADD COLUMN bios_version VARCHAR(255) NULL;
