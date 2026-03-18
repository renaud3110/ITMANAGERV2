-- Table pour stocker le statut de surveillance (températures, en ligne)
-- Liée aux PCs par pc_id

CREATE TABLE IF NOT EXISTS pc_monitor_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pc_id INT NOT NULL,
    cpu_temp DECIMAL(5,2) NULL COMMENT 'Température CPU en °C',
    gpu_temp DECIMAL(5,2) NULL COMMENT 'Température GPU en °C',
    last_seen DATETIME NOT NULL,
    UNIQUE KEY uk_pc_monitor_pc (pc_id),
    KEY idx_monitor_last_seen (last_seen),
    CONSTRAINT fk_monitor_pc FOREIGN KEY (pc_id) 
        REFERENCES pcs_laptops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
