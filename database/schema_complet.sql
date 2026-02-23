-- ============================================================
-- IT Manager System - Schéma de base de données
-- Régénéré à partir de l'analyse des modèles PHP
-- Base: itmanager | Charset: utf8mb4
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Créer la base si elle n'existe pas
CREATE DATABASE IF NOT EXISTS itmanager 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;
USE itmanager;

-- ------------------------------------------------------------
-- Tables sans dépendances (à créer en premier)
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    domain VARCHAR(255) NULL,
    description TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS manufacturers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS operating_systems (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    version VARCHAR(100) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ip_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    dns_servers VARCHAR(255) NULL,
    gateway VARCHAR(45) NULL,
    subnet_mask VARCHAR(45) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    description TEXT NULL,
    logo VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_types (
    id INT PRIMARY KEY,
    name VARCHAR(100) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Tables dépendantes
-- ------------------------------------------------------------

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    tenant_id INT NULL,
    is_global_admin TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_users_email (email),
    KEY idx_users_tenant (tenant_id),
    CONSTRAINT fk_users_tenant FOREIGN KEY (tenant_id) 
        REFERENCES tenants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address VARCHAR(500) NULL,
    tenant_id INT NOT NULL,
    is_default TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_sites_tenant (tenant_id),
    CONSTRAINT fk_sites_tenant FOREIGN KEY (tenant_id) 
        REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS persons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    tenant_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_persons_tenant (tenant_id),
    CONSTRAINT fk_persons_tenant FOREIGN KEY (tenant_id) 
        REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS models (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    manufacturer_id INT NULL,
    KEY idx_models_manufacturer (manufacturer_id),
    CONSTRAINT fk_models_manufacturer FOREIGN KEY (manufacturer_id) 
        REFERENCES manufacturers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS logins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    person_id INT NULL,
    username VARCHAR(255) NOT NULL,
    password TEXT NULL,
    service_id INT NOT NULL,
    tenant_id INT NOT NULL,
    site_id INT NULL,
    description TEXT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_logins_person (person_id),
    KEY idx_logins_service (service_id),
    KEY idx_logins_tenant (tenant_id),
    KEY idx_logins_site (site_id),
    CONSTRAINT fk_logins_person FOREIGN KEY (person_id) 
        REFERENCES persons(id) ON DELETE SET NULL,
    CONSTRAINT fk_logins_service FOREIGN KEY (service_id) 
        REFERENCES login_services(id) ON DELETE RESTRICT,
    CONSTRAINT fk_logins_tenant FOREIGN KEY (tenant_id) 
        REFERENCES tenants(id) ON DELETE CASCADE,
    CONSTRAINT fk_logins_site FOREIGN KEY (site_id) 
        REFERENCES sites(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS servers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NULL,
    type VARCHAR(50) DEFAULT 'Physique',
    site_id INT NULL,
    model_id INT NULL,
    processor_model VARCHAR(255) NULL,
    ram_total BIGINT NULL,
    ram_used BIGINT NULL,
    operating_system_id INT NULL,
    ip_address_id INT NULL,
    hostname VARCHAR(255) NULL,
    teamviewer_id VARCHAR(100) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_servers_site (site_id),
    KEY idx_servers_model (model_id),
    KEY idx_servers_os (operating_system_id),
    KEY idx_servers_ip (ip_address_id),
    CONSTRAINT fk_servers_site FOREIGN KEY (site_id) 
        REFERENCES sites(id) ON DELETE SET NULL,
    CONSTRAINT fk_servers_model FOREIGN KEY (model_id) 
        REFERENCES models(id) ON DELETE SET NULL,
    CONSTRAINT fk_servers_os FOREIGN KEY (operating_system_id) 
        REFERENCES operating_systems(id) ON DELETE SET NULL,
    CONSTRAINT fk_servers_ip FOREIGN KEY (ip_address_id) 
        REFERENCES ip_addresses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pcs_laptops (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NULL,
    tenant_id INT NOT NULL,
    site_id INT NOT NULL,
    operating_system_id INT NULL,
    ip_address_id INT NULL,
    processor_model VARCHAR(255) NULL,
    teamviewer_id VARCHAR(100) NULL,
    model_id INT NULL,
    status VARCHAR(50) DEFAULT 'free',
    account_id INT NULL,
    person_id INT NULL,
    last_account VARCHAR(255) NULL,
    serial_number VARCHAR(255) NULL,
    ram_total BIGINT NULL,
    ram_used BIGINT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_pcs_tenant (tenant_id),
    KEY idx_pcs_site (site_id),
    KEY idx_pcs_os (operating_system_id),
    KEY idx_pcs_ip (ip_address_id),
    KEY idx_pcs_model (model_id),
    KEY idx_pcs_account (account_id),
    KEY idx_pcs_person (person_id),
    CONSTRAINT fk_pcs_tenant FOREIGN KEY (tenant_id) 
        REFERENCES tenants(id) ON DELETE CASCADE,
    CONSTRAINT fk_pcs_site FOREIGN KEY (site_id) 
        REFERENCES sites(id) ON DELETE CASCADE,
    CONSTRAINT fk_pcs_os FOREIGN KEY (operating_system_id) 
        REFERENCES operating_systems(id) ON DELETE SET NULL,
    CONSTRAINT fk_pcs_ip FOREIGN KEY (ip_address_id) 
        REFERENCES ip_addresses(id) ON DELETE SET NULL,
    CONSTRAINT fk_pcs_model FOREIGN KEY (model_id) 
        REFERENCES models(id) ON DELETE SET NULL,
    CONSTRAINT fk_pcs_account FOREIGN KEY (account_id) 
        REFERENCES logins(id) ON DELETE SET NULL,
    CONSTRAINT fk_pcs_person FOREIGN KEY (person_id) 
        REFERENCES persons(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS physical_disks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pc_id INT NOT NULL,
    model VARCHAR(255) NULL,
    serial_number VARCHAR(255) NULL,
    interface_type VARCHAR(50) NULL,
    size_bytes BIGINT NULL,
    KEY idx_physical_disks_pc (pc_id),
    CONSTRAINT fk_physical_disks_pc FOREIGN KEY (pc_id) 
        REFERENCES pcs_laptops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS disk_partitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    physical_disk_id INT NOT NULL,
    drive_letter VARCHAR(10) NULL,
    label VARCHAR(255) NULL,
    file_system VARCHAR(50) NULL,
    total_size_bytes BIGINT NULL,
    free_space_bytes BIGINT NULL,
    KEY idx_disk_partitions_disk (physical_disk_id),
    CONSTRAINT fk_disk_partitions_disk FOREIGN KEY (physical_disk_id) 
        REFERENCES physical_disks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cpu_temperatures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pc_id INT NOT NULL,
    temperature DECIMAL(5,2) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY idx_cpu_temperatures_pc (pc_id),
    CONSTRAINT fk_cpu_temperatures_pc FOREIGN KEY (pc_id) 
        REFERENCES pcs_laptops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS software (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    version VARCHAR(100) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS installed_software (
    id INT AUTO_INCREMENT PRIMARY KEY,
    software_id INT NOT NULL,
    pc_id INT NOT NULL,
    installation_date DATE NULL,
    KEY idx_installed_software_software (software_id),
    KEY idx_installed_software_pc (pc_id),
    CONSTRAINT fk_installed_software_software FOREIGN KEY (software_id) 
        REFERENCES software(id) ON DELETE CASCADE,
    CONSTRAINT fk_installed_software_pc FOREIGN KEY (pc_id) 
        REFERENCES pcs_laptops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
