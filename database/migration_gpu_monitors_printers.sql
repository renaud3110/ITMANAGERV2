-- Migration: Cartes graphiques, moniteurs, imprimantes (inventaire PC)
-- Exécuter: mysql -u renaud -p itmanager < database/migration_gpu_monitors_printers.sql

USE itmanager;

-- Cartes graphiques (par PC)
CREATE TABLE IF NOT EXISTS pc_gpus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pc_id INT NOT NULL,
    model VARCHAR(500) NULL,
    vendor VARCHAR(255) NULL,
    driver_version VARCHAR(255) NULL,
    vram_bytes BIGINT UNSIGNED NULL,
    video_processor VARCHAR(255) NULL,
    KEY idx_pc_gpus_pc (pc_id),
    CONSTRAINT fk_pc_gpus_pc FOREIGN KEY (pc_id)
        REFERENCES pcs_laptops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Moniteurs connectés (par PC)
CREATE TABLE IF NOT EXISTS pc_monitors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pc_id INT NOT NULL,
    name VARCHAR(500) NULL,
    manufacturer VARCHAR(255) NULL,
    serial_number VARCHAR(255) NULL,
    resolution VARCHAR(100) NULL,
    KEY idx_pc_monitors_pc (pc_id),
    CONSTRAINT fk_pc_monitors_pc FOREIGN KEY (pc_id)
        REFERENCES pcs_laptops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Imprimantes disponibles (par PC)
CREATE TABLE IF NOT EXISTS pc_printers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pc_id INT NOT NULL,
    name VARCHAR(500) NOT NULL,
    driver VARCHAR(255) NULL,
    port VARCHAR(255) NULL,
    is_default TINYINT(1) DEFAULT 0,
    is_shared TINYINT(1) DEFAULT 0,
    KEY idx_pc_printers_pc (pc_id),
    CONSTRAINT fk_pc_printers_pc FOREIGN KEY (pc_id)
        REFERENCES pcs_laptops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
