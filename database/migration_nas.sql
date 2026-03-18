-- Migration: Table NAS (stockage réseau)
-- mysql -u renaud -p itmanager < database/migration_nas.sql

USE itmanager;

CREATE TABLE IF NOT EXISTS nas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    host VARCHAR(255) NOT NULL,
    port INT DEFAULT 5000,
    type VARCHAR(50) DEFAULT 'synology',
    site_id INT NULL,
    tenant_id INT NULL,
    ip_address_id INT NULL,
    description TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_nas_site (site_id),
    KEY idx_nas_tenant (tenant_id),
    CONSTRAINT fk_nas_site FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE SET NULL,
    CONSTRAINT fk_nas_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL,
    CONSTRAINT fk_nas_ip FOREIGN KEY (ip_address_id) REFERENCES ip_addresses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS nas_discovery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nas_id INT NOT NULL,
    discovered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    shares_json TEXT NULL,
    volumes_json TEXT NULL,
    raw_response TEXT NULL,
    error_message VARCHAR(500) NULL,
    KEY idx_nas_discovery_nas (nas_id),
    CONSTRAINT fk_nas_discovery_nas FOREIGN KEY (nas_id) REFERENCES nas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
