-- Logiciels installés sur serveurs (comme installed_software pour PCs)
CREATE TABLE IF NOT EXISTS server_installed_software (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    software_id INT NOT NULL,
    installation_date DATE NULL,
    KEY idx_server_installed_software_server (server_id),
    KEY idx_server_installed_software_software (software_id),
    CONSTRAINT fk_server_installed_software_server FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE,
    CONSTRAINT fk_server_installed_software_software FOREIGN KEY (software_id) REFERENCES software(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
