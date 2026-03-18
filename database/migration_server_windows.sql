-- Migration: Tables Windows pour serveurs (mirror des pc_windows_*)
-- mysql -u renaud -p itmanager < database/migration_server_windows.sql

USE itmanager;

CREATE TABLE IF NOT EXISTS server_windows_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    hotfix_id VARCHAR(100) NOT NULL,
    description VARCHAR(500) NULL,
    installed_on DATE NULL,
    KEY idx_server_windows_updates_server (server_id),
    CONSTRAINT fk_server_windows_updates FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS server_windows_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    display_name VARCHAR(500) NULL,
    description TEXT NULL,
    status VARCHAR(100) NULL,
    start_type VARCHAR(100) NULL,
    KEY idx_server_windows_services_server (server_id),
    CONSTRAINT fk_server_windows_services FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS server_windows_startup (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    name VARCHAR(500) NULL,
    command VARCHAR(2000) NULL,
    location VARCHAR(500) NULL,
    KEY idx_server_windows_startup_server (server_id),
    CONSTRAINT fk_server_windows_startup FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS server_windows_shared (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    path VARCHAR(1000) NULL,
    description TEXT NULL,
    KEY idx_server_windows_shared_server (server_id),
    CONSTRAINT fk_server_windows_shared FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS server_windows_mapped (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    drive_letter VARCHAR(10) NULL,
    path VARCHAR(1000) NULL,
    label VARCHAR(255) NULL,
    KEY idx_server_windows_mapped_server (server_id),
    CONSTRAINT fk_server_windows_mapped FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS server_windows_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    username VARCHAR(255) NOT NULL,
    full_name VARCHAR(500) NULL,
    last_login DATETIME NULL,
    account_type VARCHAR(50) NULL,
    KEY idx_server_windows_users_server (server_id),
    CONSTRAINT fk_server_windows_users FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS server_windows_user_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    group_name VARCHAR(500) NOT NULL,
    KEY idx_server_windows_user_groups_server (server_id),
    CONSTRAINT fk_server_windows_user_groups FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS server_windows_license (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL UNIQUE,
    description VARCHAR(500) NULL,
    status VARCHAR(100) NULL,
    KEY idx_server_windows_license_server (server_id),
    CONSTRAINT fk_server_windows_license FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
