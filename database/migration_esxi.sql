-- Migration: Inventaire ESXi (hosts, VMs, découverte par agent)
-- mysql -u user -p itmanager < database/migration_esxi.sql
-- Compatible ESXi 6.5 et 7.0

USE itmanager;

-- Hosts ESXi (vCenter ou standalone)
CREATE TABLE IF NOT EXISTS esxi_hosts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    host VARCHAR(255) NOT NULL,
    port INT DEFAULT 443,
    site_id INT NULL,
    tenant_id INT NULL,
    ip_address_id INT NULL,
    description TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_esxi_hosts_site (site_id),
    KEY idx_esxi_hosts_tenant (tenant_id),
    CONSTRAINT fk_esxi_hosts_site FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE SET NULL,
    CONSTRAINT fk_esxi_hosts_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL,
    CONSTRAINT fk_esxi_hosts_ip FOREIGN KEY (ip_address_id) REFERENCES ip_addresses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Identifiants ESXi (chiffrés, pour découverte par l'agent)
CREATE TABLE IF NOT EXISTS esxi_credentials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    esxi_host_id INT NOT NULL,
    username VARCHAR(255) NOT NULL,
    password_encrypted TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_esxi_credentials_host (esxi_host_id),
    CONSTRAINT fk_esxi_credentials_host FOREIGN KEY (esxi_host_id) REFERENCES esxi_hosts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Jobs de découverte ESXi (comme discovery_jobs NAS)
CREATE TABLE IF NOT EXISTS esxi_discovery_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    esxi_host_id INT NOT NULL,
    site_id INT NOT NULL,
    status ENUM('pending','running','done','error') DEFAULT 'pending',
    agent_hostname VARCHAR(255) NULL,
    requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    hosts_json TEXT NULL,
    vms_json TEXT NULL,
    datastores_json TEXT NULL,
    error_message VARCHAR(500) NULL,
    KEY idx_esxi_discovery_site_status (site_id, status),
    KEY idx_esxi_discovery_host (esxi_host_id),
    CONSTRAINT fk_esxi_discovery_host FOREIGN KEY (esxi_host_id) REFERENCES esxi_hosts(id) ON DELETE CASCADE,
    CONSTRAINT fk_esxi_discovery_site FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Historique des découvertes ESXi
CREATE TABLE IF NOT EXISTS esxi_discovery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    esxi_host_id INT NOT NULL,
    discovered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    hosts_json TEXT NULL,
    vms_json TEXT NULL,
    datastores_json TEXT NULL,
    error_message VARCHAR(500) NULL,
    KEY idx_esxi_discovery_history_host (esxi_host_id),
    CONSTRAINT fk_esxi_discovery_history_host FOREIGN KEY (esxi_host_id) REFERENCES esxi_hosts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- VMs découvertes sur les hosts ESXi (snapshot de la dernière découverte)
CREATE TABLE IF NOT EXISTS esxi_vms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    esxi_host_id INT NOT NULL,
    vm_name VARCHAR(255) NOT NULL,
    vm_mo_ref VARCHAR(100) NULL,
    vm_uuid VARCHAR(64) NULL,
    power_state VARCHAR(50) NULL,
    guest_os VARCHAR(255) NULL,
    cpu_count INT NULL,
    ram_mb BIGINT NULL,
    server_id INT NULL,
    discovered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_esxi_vms_host (esxi_host_id),
    KEY idx_esxi_vms_uuid (vm_uuid),
    KEY idx_esxi_vms_server (server_id),
    UNIQUE KEY uk_esxi_vms_host_name (esxi_host_id, vm_name),
    CONSTRAINT fk_esxi_vms_host FOREIGN KEY (esxi_host_id) REFERENCES esxi_hosts(id) ON DELETE CASCADE,
    CONSTRAINT fk_esxi_vms_server FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lien serveur ↔ VM ESXi : vm_uuid (auto-match si agent envoie) + esxi_vm_id (lien manuel)
-- Exécuter manuellement si colonnes existent déjà (ignorer l'erreur)
-- ALTER TABLE servers ADD COLUMN vm_uuid VARCHAR(64) NULL AFTER hostname;
-- ALTER TABLE servers ADD COLUMN esxi_vm_id INT NULL AFTER vm_uuid;

-- Alternative : migration séparée (sans erreur si déjà appliqué)
