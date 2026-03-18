-- Historique des températures CPU/GPU (une ligne par envoi du moniteur)
CREATE TABLE IF NOT EXISTS pc_monitor_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pc_id INT NOT NULL,
    cpu_temp DECIMAL(5,2) NULL,
    gpu_temp DECIMAL(5,2) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_monitor_history_pc (pc_id),
    KEY idx_monitor_history_created (created_at),
    CONSTRAINT fk_monitor_history_pc FOREIGN KEY (pc_id) 
        REFERENCES pcs_laptops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
