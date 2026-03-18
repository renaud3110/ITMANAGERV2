-- Migration: Cartes réseau (Ethernet, Wi-Fi) par PC - IP/masque CIDR, gateway, SSID
-- Exécuter: mysql -u renaud -p itmanager < database/migration_network_adapters.sql

USE itmanager;

CREATE TABLE IF NOT EXISTS pc_network_adapters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pc_id INT NOT NULL,
    name VARCHAR(255) NULL,
    type VARCHAR(50) NULL,
    ip_cidr VARCHAR(50) NULL,
    gateway VARCHAR(45) NULL,
    wifi_ssid VARCHAR(255) NULL,
    KEY idx_pc_network_adapters_pc (pc_id),
    CONSTRAINT fk_pc_network_adapters_pc FOREIGN KEY (pc_id)
        REFERENCES pcs_laptops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
