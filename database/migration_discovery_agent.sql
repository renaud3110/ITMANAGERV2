-- Migration: Jobs de découverte + credentials NAS pour l'agent sur site
-- mysql -u renaud -p itmanager < database/migration_discovery_agent.sql

USE itmanager;

-- Identifiants NAS stockés de façon chiffrée (pour découverte par l'agent)
CREATE TABLE IF NOT EXISTS nas_credentials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nas_id INT NOT NULL,
    username VARCHAR(255) NOT NULL,
    password_encrypted TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_nas_credentials_nas (nas_id),
    CONSTRAINT fk_nas_credentials_nas FOREIGN KEY (nas_id) REFERENCES nas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Jobs de découverte : créés par l'UI, exécutés par l'agent sur le site
CREATE TABLE IF NOT EXISTS discovery_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nas_id INT NOT NULL,
    site_id INT NOT NULL,
    status ENUM('pending','running','done','error') DEFAULT 'pending',
    agent_hostname VARCHAR(255) NULL,
    requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME NULL,
    shares_json TEXT NULL,
    volumes_json TEXT NULL,
    error_message VARCHAR(500) NULL,
    KEY idx_discovery_jobs_site_status (site_id, status),
    KEY idx_discovery_jobs_nas (nas_id),
    CONSTRAINT fk_discovery_jobs_nas FOREIGN KEY (nas_id) REFERENCES nas(id) ON DELETE CASCADE,
    CONSTRAINT fk_discovery_jobs_site FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
