-- Migration: Windows Updates installés (par PC)
-- Exécuter: mysql -u renaud -p itmanager < database/migration_windows_updates.sql

USE itmanager;

CREATE TABLE IF NOT EXISTS pc_windows_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pc_id INT NOT NULL,
    hotfix_id VARCHAR(50) NOT NULL,
    description VARCHAR(500) NULL,
    installed_on DATE NULL,
    KEY idx_pc_windows_updates_pc (pc_id),
    KEY idx_pc_windows_updates_hotfix (hotfix_id),
    CONSTRAINT fk_pc_windows_updates_pc FOREIGN KEY (pc_id)
        REFERENCES pcs_laptops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
