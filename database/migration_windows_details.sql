-- Migration: Détails Windows (services, startup, shared, mapped, users, groups, license)
-- Exécuter: mysql -u renaud -p itmanager < database/migration_windows_details.sql

USE itmanager;

-- Services Windows
CREATE TABLE IF NOT EXISTS pc_windows_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pc_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    display_name VARCHAR(500) NULL,
    description VARCHAR(1000) NULL,
    status VARCHAR(100) NULL,
    start_type VARCHAR(100) NULL,
    KEY idx_pc_windows_services_pc (pc_id),
    KEY idx_pc_windows_services_status (status),
    CONSTRAINT fk_pc_windows_services_pc FOREIGN KEY (pc_id)
        REFERENCES pcs_laptops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Programmes au démarrage
CREATE TABLE IF NOT EXISTS pc_windows_startup (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pc_id INT NOT NULL,
    name VARCHAR(500) NULL,
    command VARCHAR(2000) NULL,
    location VARCHAR(500) NULL,
    KEY idx_pc_windows_startup_pc (pc_id),
    CONSTRAINT fk_pc_windows_startup_pc FOREIGN KEY (pc_id)
        REFERENCES pcs_laptops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Partages (ce que ce PC partage)
CREATE TABLE IF NOT EXISTS pc_windows_shared (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pc_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    path VARCHAR(1000) NULL,
    description VARCHAR(500) NULL,
    KEY idx_pc_windows_shared_pc (pc_id),
    CONSTRAINT fk_pc_windows_shared_pc FOREIGN KEY (pc_id)
        REFERENCES pcs_laptops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lecteurs réseau mappés
CREATE TABLE IF NOT EXISTS pc_windows_mapped (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pc_id INT NOT NULL,
    drive_letter VARCHAR(10) NULL,
    path VARCHAR(1000) NULL,
    label VARCHAR(255) NULL,
    KEY idx_pc_windows_mapped_pc (pc_id),
    CONSTRAINT fk_pc_windows_mapped_pc FOREIGN KEY (pc_id)
        REFERENCES pcs_laptops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Utilisateurs (local, AD, cloud) avec dernière connexion
CREATE TABLE IF NOT EXISTS pc_windows_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pc_id INT NOT NULL,
    username VARCHAR(255) NOT NULL,
    full_name VARCHAR(500) NULL,
    last_login DATETIME NULL,
    account_type VARCHAR(50) NULL,
    KEY idx_pc_windows_users_pc (pc_id),
    KEY idx_pc_windows_users_last_login (last_login),
    CONSTRAINT fk_pc_windows_users_pc FOREIGN KEY (pc_id)
        REFERENCES pcs_laptops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Groupes du dernier utilisateur connecté
CREATE TABLE IF NOT EXISTS pc_windows_user_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pc_id INT NOT NULL,
    group_name VARCHAR(500) NOT NULL,
    KEY idx_pc_windows_user_groups_pc (pc_id),
    CONSTRAINT fk_pc_windows_user_groups_pc FOREIGN KEY (pc_id)
        REFERENCES pcs_laptops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Licence Windows (une ligne par PC)
CREATE TABLE IF NOT EXISTS pc_windows_license (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pc_id INT NOT NULL UNIQUE,
    description VARCHAR(500) NULL,
    status VARCHAR(100) NULL,
    KEY idx_pc_windows_license_pc (pc_id),
    CONSTRAINT fk_pc_windows_license_pc FOREIGN KEY (pc_id)
        REFERENCES pcs_laptops(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
