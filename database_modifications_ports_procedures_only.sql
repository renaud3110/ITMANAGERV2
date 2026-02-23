-- Extrait: Procédure et fonction uniquement (tables/vues déjà dans schema)
-- Pour éviter les conflits avec database_schema_recreate.sql

USE itmanager;

DROP PROCEDURE IF EXISTS CreatePortsForEquipment;
DROP FUNCTION IF EXISTS GetAvailablePorts;

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
    
    UPDATE network_equipments 
    SET ports_count = ports_count 
    WHERE id = equipment_id;
END //
DELIMITER ;

DELIMITER //
CREATE FUNCTION GetAvailablePorts(equipment_id INT) 
RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE available_count INT;
    
    SELECT COUNT(*) INTO available_count
    FROM network_ports 
    WHERE network_ports.equipment_id = equipment_id 
    AND port_status = 'inactive' 
    AND connected_to_equipment_id IS NULL;
    
    RETURN available_count;
END //
DELIMITER ;
