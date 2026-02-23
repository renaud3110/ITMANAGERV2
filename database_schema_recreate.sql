-- =============================================================================
-- Script de reconstitution complète de la base de données IT Manager
-- Généré à partir de l'analyse des modèles PHP du projet
-- Base: itmanager | Charset: utf8mb4
-- =============================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Créer la base si elle n'existe pas
CREATE DATABASE IF NOT EXISTS itmanager 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;
USE itmanager;

-- =============================================================================
-- 1. TABLES DE BASE (sans dépendances)
-- =============================================================================

-- Table des tenants (clients/organisations)
CREATE TABLE IF NOT EXISTS tenants (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    domain VARCHAR(255) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    contact_email VARCHAR(255) DEFAULT NULL,
    nakivo_customer_name VARCHAR(255) DEFAULT NULL COMMENT 'Nom client dans Nakivo Backup',
    dsd_customer_name VARCHAR(255) DEFAULT NULL COMMENT 'Nom client dans DSD Factures',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_tenants_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des sites (localisations)
CREATE TABLE IF NOT EXISTS sites (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    address TEXT DEFAULT NULL,
    tenant_id INT(11) DEFAULT NULL,
    is_default TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_sites_tenant (tenant_id),
    CONSTRAINT fk_sites_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des utilisateurs de l'application (authentification)
CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    tenant_id INT(11) DEFAULT NULL,
    is_global_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_users_email (email),
    KEY idx_users_tenant (tenant_id),
    CONSTRAINT fk_users_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des fabricants
CREATE TABLE IF NOT EXISTS manufacturers (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des modèles de matériel
CREATE TABLE IF NOT EXISTS models (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    manufacturer_id INT(11) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_models_manufacturer (manufacturer_id),
    CONSTRAINT fk_models_manufacturer FOREIGN KEY (manufacturer_id) REFERENCES manufacturers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des systèmes d'exploitation
CREATE TABLE IF NOT EXISTS operating_systems (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    version VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des services de connexion (pour les comptes)
CREATE TABLE IF NOT EXISTS login_services (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nom VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    logo VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 2. TABLES AVEC DÉPENDANCES
-- =============================================================================

-- Table des adresses IP
CREATE TABLE IF NOT EXISTS ip_addresses (
    id INT(11) NOT NULL AUTO_INCREMENT,
    ip_address VARCHAR(45) NOT NULL,
    description VARCHAR(255) DEFAULT NULL,
    subnet_mask VARCHAR(45) DEFAULT NULL,
    gateway VARCHAR(45) DEFAULT NULL,
    dns1 VARCHAR(45) DEFAULT NULL,
    dns2 VARCHAR(45) DEFAULT NULL,
    dns_servers VARCHAR(255) DEFAULT NULL COMMENT 'Alternatif: DNS combinés',
    vlan_id VARCHAR(50) DEFAULT NULL,
    tenant_id INT(11) DEFAULT NULL,
    site_id INT(11) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_ip_address (ip_address),
    KEY idx_ip_tenant (tenant_id),
    KEY idx_ip_site (site_id),
    CONSTRAINT fk_ip_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL,
    CONSTRAINT fk_ip_site FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des personnes (employés/utilisateurs des tenants)
CREATE TABLE IF NOT EXISTS persons (
    id INT(11) NOT NULL AUTO_INCREMENT,
    nom VARCHAR(255) NOT NULL,
    prenom VARCHAR(255) NOT NULL,
    email VARCHAR(255) DEFAULT NULL,
    tenant_id INT(11) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_persons_tenant (tenant_id),
    KEY idx_persons_email (email),
    CONSTRAINT fk_persons_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des comptes de connexion (logins techniques et utilisateurs)
CREATE TABLE IF NOT EXISTS logins (
    id INT(11) NOT NULL AUTO_INCREMENT,
    person_id INT(11) DEFAULT NULL,
    username VARCHAR(255) NOT NULL,
    password TEXT DEFAULT NULL COMMENT 'Mot de passe chiffré',
    service_id INT(11) NOT NULL,
    tenant_id INT(11) DEFAULT NULL,
    site_id INT(11) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_logins_person (person_id),
    KEY idx_logins_service (service_id),
    KEY idx_logins_tenant (tenant_id),
    KEY idx_logins_site (site_id),
    CONSTRAINT fk_logins_person FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE SET NULL,
    CONSTRAINT fk_logins_service FOREIGN KEY (service_id) REFERENCES login_services(id) ON DELETE RESTRICT,
    CONSTRAINT fk_logins_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL,
    CONSTRAINT fk_logins_site FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des équipements réseau
CREATE TABLE IF NOT EXISTS network_equipments (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    type ENUM('router', 'switch', 'wifiAP', 'wifi infra', 'firewall', 'nas', 'other') DEFAULT 'switch',
    model_id INT(11) DEFAULT NULL,
    site_id INT(11) DEFAULT NULL,
    manufacturer_id INT(11) DEFAULT NULL,
    ip_address_id INT(11) DEFAULT NULL,
    status ENUM('active', 'inactive', 'maintenance', 'retired') DEFAULT 'inactive',
    login_id INT(11) DEFAULT NULL,
    ports_count INT(11) DEFAULT 0 COMMENT 'Nombre total de ports',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ne_site (site_id),
    KEY idx_ne_model (model_id),
    KEY idx_ne_manufacturer (manufacturer_id),
    KEY idx_ne_ip (ip_address_id),
    KEY idx_ne_login (login_id),
    CONSTRAINT fk_ne_site FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE SET NULL,
    CONSTRAINT fk_ne_model FOREIGN KEY (model_id) REFERENCES models(id) ON DELETE SET NULL,
    CONSTRAINT fk_ne_manufacturer FOREIGN KEY (manufacturer_id) REFERENCES manufacturers(id) ON DELETE SET NULL,
    CONSTRAINT fk_ne_ip FOREIGN KEY (ip_address_id) REFERENCES ip_addresses(id) ON DELETE SET NULL,
    CONSTRAINT fk_ne_login FOREIGN KEY (login_id) REFERENCES logins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des ports réseau (gestion des connexions)
CREATE TABLE IF NOT EXISTS network_ports (
    id INT(11) NOT NULL AUTO_INCREMENT,
    equipment_id INT(11) NOT NULL,
    port_number INT(11) NOT NULL,
    port_name VARCHAR(50) NOT NULL,
    port_type ENUM('ethernet', 'fiber', 'serial', 'console', 'management', 'power', 'sfp', 'qsfp') DEFAULT 'ethernet',
    port_speed VARCHAR(20) DEFAULT NULL,
    port_status ENUM('active', 'inactive', 'disabled', 'error') DEFAULT 'inactive',
    connected_to_equipment_id INT(11) DEFAULT NULL,
    connected_to_port_id INT(11) DEFAULT NULL,
    vlan_id VARCHAR(20) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_equipment_port (equipment_id, port_number),
    UNIQUE KEY unique_equipment_port_name (equipment_id, port_name),
    KEY idx_equipment_id (equipment_id),
    KEY idx_port_status (port_status),
    KEY idx_connected_equipment (connected_to_equipment_id),
    CONSTRAINT fk_np_equipment FOREIGN KEY (equipment_id) REFERENCES network_equipments(id) ON DELETE CASCADE,
    CONSTRAINT fk_np_connected_equipment FOREIGN KEY (connected_to_equipment_id) REFERENCES network_equipments(id) ON DELETE SET NULL,
    CONSTRAINT fk_np_connected_port FOREIGN KEY (connected_to_port_id) REFERENCES network_ports(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des serveurs
CREATE TABLE IF NOT EXISTS servers (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) DEFAULT NULL,
    type VARCHAR(50) DEFAULT 'Physique',
    site_id INT(11) DEFAULT NULL,
    model_id INT(11) DEFAULT NULL,
    processor_model VARCHAR(255) DEFAULT NULL,
    ram_total BIGINT DEFAULT NULL COMMENT 'En octets',
    ram_used BIGINT DEFAULT NULL COMMENT 'En octets',
    operating_system_id INT(11) DEFAULT NULL,
    ip_address_id INT(11) DEFAULT NULL,
    hostname VARCHAR(255) DEFAULT NULL,
    teamviewer_id VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_servers_site (site_id),
    KEY idx_servers_model (model_id),
    KEY idx_servers_os (operating_system_id),
    KEY idx_servers_ip (ip_address_id),
    CONSTRAINT fk_servers_site FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE SET NULL,
    CONSTRAINT fk_servers_model FOREIGN KEY (model_id) REFERENCES models(id) ON DELETE SET NULL,
    CONSTRAINT fk_servers_os FOREIGN KEY (operating_system_id) REFERENCES operating_systems(id) ON DELETE SET NULL,
    CONSTRAINT fk_servers_ip FOREIGN KEY (ip_address_id) REFERENCES ip_addresses(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des PC/portables
CREATE TABLE IF NOT EXISTS pcs_laptops (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) DEFAULT NULL,
    tenant_id INT(11) DEFAULT NULL,
    site_id INT(11) DEFAULT NULL,
    operating_system_id INT(11) DEFAULT NULL,
    ip_address_id INT(11) DEFAULT NULL,
    processor_model VARCHAR(255) DEFAULT NULL,
    teamviewer_id VARCHAR(50) DEFAULT NULL,
    model_id INT(11) DEFAULT NULL,
    status VARCHAR(50) DEFAULT 'free',
    account_id INT(11) DEFAULT NULL,
    person_id INT(11) DEFAULT NULL,
    last_account VARCHAR(255) DEFAULT NULL,
    serial_number VARCHAR(100) DEFAULT NULL,
    ram_total BIGINT DEFAULT NULL,
    ram_used BIGINT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_pc_tenant (tenant_id),
    KEY idx_pc_site (site_id),
    KEY idx_pc_os (operating_system_id),
    KEY idx_pc_ip (ip_address_id),
    KEY idx_pc_model (model_id),
    KEY idx_pc_account (account_id),
    KEY idx_pc_person (person_id),
    CONSTRAINT fk_pc_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL,
    CONSTRAINT fk_pc_site FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE SET NULL,
    CONSTRAINT fk_pc_os FOREIGN KEY (operating_system_id) REFERENCES operating_systems(id) ON DELETE SET NULL,
    CONSTRAINT fk_pc_ip FOREIGN KEY (ip_address_id) REFERENCES ip_addresses(id) ON DELETE SET NULL,
    CONSTRAINT fk_pc_model FOREIGN KEY (model_id) REFERENCES models(id) ON DELETE SET NULL,
    CONSTRAINT fk_pc_account FOREIGN KEY (account_id) REFERENCES logins(id) ON DELETE SET NULL,
    CONSTRAINT fk_pc_person FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des disques physiques
CREATE TABLE IF NOT EXISTS physical_disks (
    id INT(11) NOT NULL AUTO_INCREMENT,
    pc_id INT(11) NOT NULL,
    model VARCHAR(255) DEFAULT NULL,
    serial_number VARCHAR(100) DEFAULT NULL,
    interface_type VARCHAR(50) DEFAULT NULL,
    size_bytes BIGINT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_pd_pc (pc_id),
    CONSTRAINT fk_pd_pc FOREIGN KEY (pc_id) REFERENCES pcs_laptops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des partitions disque
CREATE TABLE IF NOT EXISTS disk_partitions (
    id INT(11) NOT NULL AUTO_INCREMENT,
    physical_disk_id INT(11) NOT NULL,
    drive_letter VARCHAR(10) DEFAULT NULL,
    label VARCHAR(255) DEFAULT NULL,
    file_system VARCHAR(50) DEFAULT NULL,
    total_size_bytes BIGINT DEFAULT NULL,
    free_space_bytes BIGINT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_dp_disk (physical_disk_id),
    CONSTRAINT fk_dp_disk FOREIGN KEY (physical_disk_id) REFERENCES physical_disks(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des températures CPU
CREATE TABLE IF NOT EXISTS cpu_temperatures (
    id INT(11) NOT NULL AUTO_INCREMENT,
    pc_id INT(11) NOT NULL,
    temperature DECIMAL(5,2) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ct_pc (pc_id),
    CONSTRAINT fk_ct_pc FOREIGN KEY (pc_id) REFERENCES pcs_laptops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des logiciels (catalogue)
CREATE TABLE IF NOT EXISTS software (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    version VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des logiciels installés par PC
CREATE TABLE IF NOT EXISTS installed_software (
    id INT(11) NOT NULL AUTO_INCREMENT,
    pc_id INT(11) NOT NULL,
    software_id INT(11) NOT NULL,
    installation_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ins_pc (pc_id),
    KEY idx_ins_software (software_id),
    CONSTRAINT fk_ins_pc FOREIGN KEY (pc_id) REFERENCES pcs_laptops(id) ON DELETE CASCADE,
    CONSTRAINT fk_ins_software FOREIGN KEY (software_id) REFERENCES software(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 3. TABLES MÉTIER (Domaines, Licences, Backup, Factures, M365)
-- =============================================================================

-- Table des domaines
CREATE TABLE IF NOT EXISTS domains (
    id INT(11) NOT NULL AUTO_INCREMENT,
    domain_name VARCHAR(255) NOT NULL,
    tenant_id INT(11) DEFAULT NULL,
    is_managed TINYINT(1) DEFAULT 1,
    expiry_date DATE DEFAULT NULL,
    hosting_provider VARCHAR(255) DEFAULT NULL,
    auto_renewal TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_domains_tenant (tenant_id),
    UNIQUE KEY uk_domain_tenant (domain_name, tenant_id),
    CONSTRAINT fk_domains_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des licences
CREATE TABLE IF NOT EXISTS licenses (
    id INT(11) NOT NULL AUTO_INCREMENT,
    tenant_id INT(11) DEFAULT NULL,
    license_name VARCHAR(255) NOT NULL,
    login VARCHAR(255) DEFAULT NULL,
    password TEXT DEFAULT NULL COMMENT 'Chiffré AES-256',
    license_count INT(11) DEFAULT 1,
    expiry_date DATE DEFAULT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_licenses_tenant (tenant_id),
    CONSTRAINT fk_licenses_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des rapports Nakivo Backup
CREATE TABLE IF NOT EXISTS nakivo_backup_reports (
    id INT(11) NOT NULL AUTO_INCREMENT,
    client_name VARCHAR(255) DEFAULT NULL,
    report_date DATE DEFAULT NULL,
    total_jobs INT(11) DEFAULT 0,
    total_vms INT(11) DEFAULT 0,
    total_data_gb DECIMAL(10,2) DEFAULT NULL,
    duration_seconds INT(11) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des jobs Nakivo Backup
CREATE TABLE IF NOT EXISTS nakivo_backup_jobs (
    id INT(11) NOT NULL AUTO_INCREMENT,
    report_id INT(11) NOT NULL,
    started_at DATETIME DEFAULT NULL,
    status VARCHAR(50) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_nbj_report (report_id),
    CONSTRAINT fk_nbj_report FOREIGN KEY (report_id) REFERENCES nakivo_backup_reports(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des VMs dans les jobs Nakivo
CREATE TABLE IF NOT EXISTS nakivo_backup_vms (
    id INT(11) NOT NULL AUTO_INCREMENT,
    job_id INT(11) NOT NULL,
    vm_name VARCHAR(255) DEFAULT NULL,
    status VARCHAR(50) DEFAULT NULL,
    data_processed_gb DECIMAL(10,2) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_nbv_job (job_id),
    CONSTRAINT fk_nbv_job FOREIGN KEY (job_id) REFERENCES nakivo_backup_jobs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table du stockage cible Nakivo
CREATE TABLE IF NOT EXISTS nakivo_target_storage (
    id INT(11) NOT NULL AUTO_INCREMENT,
    report_id INT(11) NOT NULL,
    storage_name VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_nts_report (report_id),
    CONSTRAINT fk_nts_report FOREIGN KEY (report_id) REFERENCES nakivo_backup_reports(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des factures DSD
CREATE TABLE IF NOT EXISTS factures (
    id INT(11) NOT NULL AUTO_INCREMENT,
    received_date DATE DEFAULT NULL,
    subject VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des licences par facture
CREATE TABLE IF NOT EXISTS licences_facture (
    id INT(11) NOT NULL AUTO_INCREMENT,
    facture_id INT(11) NOT NULL,
    client VARCHAR(255) DEFAULT NULL,
    license_name VARCHAR(255) DEFAULT NULL,
    quantity INT(11) DEFAULT 0,
    total_price DECIMAL(10,2) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_lf_facture (facture_id),
    KEY idx_lf_client (client),
    CONSTRAINT fk_lf_facture FOREIGN KEY (facture_id) REFERENCES factures(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des SKU Microsoft 365 souscrits (tenant_id_ref = tenants.id)
CREATE TABLE IF NOT EXISTS m365_subscribed_skus (
    id INT(11) NOT NULL AUTO_INCREMENT,
    tenant_id_ref INT(11) NOT NULL,
    sku_part_number VARCHAR(100) DEFAULT NULL,
    commercial_name VARCHAR(255) DEFAULT NULL,
    consumed_units INT(11) DEFAULT 0,
    enabled_units INT(11) DEFAULT 0,
    suspended_units INT(11) DEFAULT 0,
    warning_units INT(11) DEFAULT 0,
    renewal_date DATE DEFAULT NULL,
    last_updated DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_m365_tenant (tenant_id_ref),
    CONSTRAINT fk_m365_skus_tenant FOREIGN KEY (tenant_id_ref) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des licences utilisateurs Microsoft 365
CREATE TABLE IF NOT EXISTS m365_user_licenses (
    id INT(11) NOT NULL AUTO_INCREMENT,
    tenant_id_ref INT(11) NOT NULL,
    user_id VARCHAR(255) DEFAULT NULL,
    display_name VARCHAR(255) DEFAULT NULL,
    user_principal_name VARCHAR(255) DEFAULT NULL,
    sku_part_number VARCHAR(100) DEFAULT NULL,
    commercial_name VARCHAR(255) DEFAULT NULL,
    assigned_date DATE DEFAULT NULL,
    state VARCHAR(50) DEFAULT NULL,
    last_updated DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_m365ul_tenant (tenant_id_ref),
    KEY idx_m365ul_user (user_id),
    CONSTRAINT fk_m365ul_tenant FOREIGN KEY (tenant_id_ref) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- 4. VUES ET DONNÉES INITIALES
-- =============================================================================

-- Vues pour les ports réseau (si network_ports existe)
-- Note: Ces vues nécessitent que network_ports soit créée
DROP VIEW IF EXISTS v_equipment_ports_summary;
CREATE VIEW v_equipment_ports_summary AS
SELECT 
    ne.id as equipment_id,
    ne.name as equipment_name,
    ne.type as equipment_type,
    ne.ports_count as total_ports,
    COUNT(np.id) as configured_ports,
    SUM(CASE WHEN np.port_status = 'active' THEN 1 ELSE 0 END) as active_ports,
    SUM(CASE WHEN np.port_status = 'inactive' THEN 1 ELSE 0 END) as inactive_ports,
    SUM(CASE WHEN np.connected_to_equipment_id IS NOT NULL THEN 1 ELSE 0 END) as connected_ports
FROM network_equipments ne
LEFT JOIN network_ports np ON ne.id = np.equipment_id
GROUP BY ne.id, ne.name, ne.type, ne.ports_count;

DROP VIEW IF EXISTS v_network_connections;
CREATE VIEW v_network_connections AS
SELECT 
    np1.id as port_id,
    eq1.name as equipment_name,
    np1.port_name as port_name,
    np1.port_status,
    eq2.name as connected_to_equipment,
    np2.port_name as connected_to_port,
    np1.vlan_id,
    np1.description
FROM network_ports np1
INNER JOIN network_equipments eq1 ON np1.equipment_id = eq1.id
LEFT JOIN network_equipments eq2 ON np1.connected_to_equipment_id = eq2.id  
LEFT JOIN network_ports np2 ON np1.connected_to_port_id = np2.id
WHERE np1.connected_to_equipment_id IS NOT NULL
ORDER BY eq1.name, np1.port_number;

-- =============================================================================
-- 5. DONNÉES INITIALES
-- =============================================================================

-- Utilisateur admin par défaut (mot de passe: admin123 - À CHANGER!)
INSERT IGNORE INTO users (name, email, password, tenant_id, is_global_admin) VALUES
('Administrateur', 'admin@itmanager.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, 1);

-- Services de connexion par défaut
INSERT IGNORE INTO login_services (nom, description, logo) VALUES
('Windows', 'Comptes Windows / Active Directory', NULL),
('Microsoft 365', 'Comptes Microsoft 365 / Azure AD', NULL),
('Google Workspace', 'Comptes Google', NULL),
('SSH', 'Accès SSH / Linux', NULL),
('VPN', 'Connexions VPN', NULL),
('Autre', 'Autres types de comptes', NULL);

-- Systèmes d'exploitation de base
INSERT IGNORE INTO operating_systems (name, version) VALUES
('Windows', '10'),
('Windows', '11'),
('Windows Server', '2019'),
('Windows Server', '2022'),
('Ubuntu', '22.04 LTS'),
('Debian', '12'),
('macOS', 'Sonoma');

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- FIN DU SCRIPT
-- =============================================================================
-- Exécuter avec: mysql -u renaud -p < database_schema_recreate.sql
-- Ou: mysql -u renaud -p itmanager < database_schema_recreate.sql
-- =============================================================================
