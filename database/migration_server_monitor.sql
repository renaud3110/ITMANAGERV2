-- Télémétrie moniteur pour serveurs (températures)
CREATE TABLE IF NOT EXISTS server_monitor_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    cpu_temp DECIMAL(5,2) NULL,
    gpu_temp DECIMAL(5,2) NULL,
    last_seen DATETIME NOT NULL,
    UNIQUE KEY uk_server_monitor (server_id),
    CONSTRAINT fk_server_monitor_status FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
