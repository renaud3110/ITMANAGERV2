-- Script de modification pour la gestion des ports des équipements réseau
-- Auteur: Modification demandée pour CMDB
-- Date: 2025-01-27

-- 1. Ajouter le champ ports_count à la table network_equipments
ALTER TABLE network_equipments 
ADD COLUMN ports_count INT(11) DEFAULT 0 COMMENT 'Nombre total de ports disponibles sur cet équipement';

-- 2. Créer la table network_ports pour gérer les ports individuellement
CREATE TABLE network_ports (
    id INT(11) NOT NULL AUTO_INCREMENT,
    equipment_id INT(11) NOT NULL,
    port_number INT(11) NOT NULL,
    port_name VARCHAR(50) NOT NULL,
    port_type ENUM('ethernet', 'fiber', 'serial', 'console', 'management', 'power', 'sfp', 'qsfp') DEFAULT 'ethernet',
    port_speed VARCHAR(20) DEFAULT NULL COMMENT 'Exemple: 1Gbps, 10Gbps, 100Mbps',
    port_status ENUM('active', 'inactive', 'disabled', 'error') DEFAULT 'inactive',
    connected_to_equipment_id INT(11) DEFAULT NULL COMMENT 'ID de l\'équipement connecté',
    connected_to_port_id INT(11) DEFAULT NULL COMMENT 'ID du port de l\'équipement connecté', 
    vlan_id VARCHAR(20) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    PRIMARY KEY (id),
    UNIQUE KEY unique_equipment_port (equipment_id, port_number),
    UNIQUE KEY unique_equipment_port_name (equipment_id, port_name),
    FOREIGN KEY (equipment_id) REFERENCES network_equipments(id) ON DELETE CASCADE,
    FOREIGN KEY (connected_to_equipment_id) REFERENCES network_equipments(id) ON DELETE SET NULL,
    FOREIGN KEY (connected_to_port_id) REFERENCES network_ports(id) ON DELETE SET NULL,
    
    INDEX idx_equipment_id (equipment_id),
    INDEX idx_port_status (port_status),
    INDEX idx_connected_equipment (connected_to_equipment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Table des ports des équipements réseau';

-- 3. Créer une procédure stockée pour initialiser les ports d'un équipement
DELIMITER //
CREATE PROCEDURE CreatePortsForEquipment(
    IN equipment_id INT,
    IN ports_count INT,
    IN port_type VARCHAR(20),
    IN port_speed VARCHAR(20)
)
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE port_prefix VARCHAR(10);
    
    -- Déterminer le préfixe selon le type d'équipement
    SELECT 
        CASE 
            WHEN type = 'switch' THEN 'Gi'
            WHEN type = 'router' THEN 'Fa'
            WHEN type = 'wifiAP' THEN 'Port'
            WHEN type = 'wifi infra' THEN 'Port'
            ELSE 'Port'
        END INTO port_prefix
    FROM network_equipments 
    WHERE id = equipment_id;
    
    -- Créer les ports
    WHILE i <= ports_count DO
        INSERT INTO network_ports (
            equipment_id, 
            port_number, 
            port_name, 
            port_type, 
            port_speed,
            port_status
        ) VALUES (
            equipment_id, 
            i, 
            CONCAT(port_prefix, '0/', i),
            COALESCE(port_type, 'ethernet'),
            port_speed,
            'inactive'
        );
        SET i = i + 1;
    END WHILE;
    
    -- Mettre à jour le compteur de ports de l'équipement
    UPDATE network_equipments 
    SET ports_count = ports_count 
    WHERE id = equipment_id;
END //
DELIMITER ;

-- 4. Créer une vue pour avoir un aperçu des équipements avec leurs ports
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

-- 5. Créer une vue détaillée des connexions entre équipements
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

-- 6. Fonction pour obtenir les ports libres d'un équipement
DELIMITER //
CREATE FUNCTION GetAvailablePorts(equipment_id INT) 
RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE available_count INT;
    
    SELECT COUNT(*) INTO available_count
    FROM network_ports 
    WHERE equipment_id = equipment_id 
    AND port_status = 'inactive' 
    AND connected_to_equipment_id IS NULL;
    
    RETURN available_count;
END //
DELIMITER ;

-- Exemple d'utilisation :
-- Pour créer les ports d'un équipement existant :
-- CALL CreatePortsForEquipment(1, 24, 'ethernet', '1Gbps');

-- Pour voir le résumé des ports :
-- SELECT * FROM v_equipment_ports_summary;

-- Pour voir les connexions :
-- SELECT * FROM v_network_connections;

-- Pour compter les ports disponibles :
-- SELECT GetAvailablePorts(1); 