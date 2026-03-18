-- Colonnes personne attribuée et dernier compte pour serveurs (comme pcs_laptops)
-- mysql -u renaud -p itmanager < database/migration_servers_person_last_account.sql

USE itmanager;

ALTER TABLE servers ADD COLUMN person_id INT NULL AFTER rustdesk_id;
ALTER TABLE servers ADD COLUMN last_account VARCHAR(255) NULL AFTER person_id;
ALTER TABLE servers ADD COLUMN last_account_created_at DATETIME NULL AFTER last_account;
ALTER TABLE servers ADD CONSTRAINT fk_servers_person FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE SET NULL;
