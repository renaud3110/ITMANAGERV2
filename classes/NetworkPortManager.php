<?php

require_once 'config/Database.php';

class NetworkPortManager {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Ajouter le nombre de ports à un équipement et créer les ports
     */
    public function setEquipmentPorts($equipmentId, $portsCount, $portType = 'ethernet', $portSpeed = '1Gbps') {
        try {
            $this->db->getConnection()->beginTransaction();
            
            // Mettre à jour le compteur de ports de l'équipement
            $sql = "UPDATE network_equipments SET ports_count = ? WHERE id = ?";
            $this->db->query($sql, [$portsCount, $equipmentId]);
            
            // Supprimer les ports existants
            $this->deleteEquipmentPorts($equipmentId);
            
            // Appeler la procédure stockée pour créer les ports
            $sql = "CALL CreatePortsForEquipment(?, ?, ?, ?)";
            $this->db->query($sql, [$equipmentId, $portsCount, $portType, $portSpeed]);
            
            $this->db->getConnection()->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollback();
            throw new Exception("Erreur lors de la création des ports: " . $e->getMessage());
        }
    }
    
    /**
     * Obtenir tous les ports d'un équipement
     */
    public function getEquipmentPorts($equipmentId) {
        $sql = "SELECT * FROM network_ports WHERE equipment_id = ? ORDER BY port_number";
        return $this->db->fetchAll($sql, [$equipmentId]);
    }
    
    /**
     * Mettre à jour un port spécifique
     */
    public function updatePort($portId, $data) {
        $allowedFields = ['port_name', 'port_type', 'port_speed', 'port_status', 'connected_to_equipment_id', 'connected_to_port_id', 'vlan_id', 'description'];
        
        $setClause = [];
        $params = [];
        
        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $setClause[] = "{$field} = ?";
                $params[] = $value;
            }
        }
        
        if (empty($setClause)) {
            throw new Exception("Aucun champ valide à mettre à jour");
        }
        
        $params[] = $portId;
        $sql = "UPDATE network_ports SET " . implode(', ', $setClause) . " WHERE id = ?";
        
        return $this->db->query($sql, $params);
    }
    
    /**
     * Connecter deux ports ensemble
     */
    public function connectPorts($port1Id, $port2Id) {
        try {
            $this->db->getConnection()->beginTransaction();
            
            // Connecter le port 1 vers le port 2
            $sql = "UPDATE network_ports np1 
                    JOIN network_ports np2 ON np2.id = ?
                    SET np1.connected_to_equipment_id = np2.equipment_id,
                        np1.connected_to_port_id = np2.id,
                        np1.port_status = 'active'
                    WHERE np1.id = ?";
            $this->db->query($sql, [$port2Id, $port1Id]);
            
            // Connecter le port 2 vers le port 1
            $sql = "UPDATE network_ports np2 
                    JOIN network_ports np1 ON np1.id = ?
                    SET np2.connected_to_equipment_id = np1.equipment_id,
                        np2.connected_to_port_id = np1.id,
                        np2.port_status = 'active'
                    WHERE np2.id = ?";
            $this->db->query($sql, [$port1Id, $port2Id]);
            
            $this->db->getConnection()->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollback();
            throw new Exception("Erreur lors de la connexion des ports: " . $e->getMessage());
        }
    }
    
    /**
     * Déconnecter un port
     */
    public function disconnectPort($portId) {
        try {
            $this->db->getConnection()->beginTransaction();
            
            // Récupérer le port connecté
            $connectedPort = $this->db->fetch("SELECT connected_to_port_id FROM network_ports WHERE id = ?", [$portId]);
            
            // Déconnecter le port principal
            $sql = "UPDATE network_ports 
                    SET connected_to_equipment_id = NULL, 
                        connected_to_port_id = NULL, 
                        port_status = 'inactive' 
                    WHERE id = ?";
            $this->db->query($sql, [$portId]);
            
            // Déconnecter le port associé s'il existe
            if ($connectedPort && $connectedPort['connected_to_port_id']) {
                $this->db->query($sql, [$connectedPort['connected_to_port_id']]);
            }
            
            $this->db->getConnection()->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->getConnection()->rollback();
            throw new Exception("Erreur lors de la déconnexion du port: " . $e->getMessage());
        }
    }
    
    /**
     * Obtenir le résumé des ports pour tous les équipements
     */
    public function getEquipmentsPortsSummary() {
        return $this->db->fetchAll("SELECT * FROM v_equipment_ports_summary ORDER BY equipment_name");
    }
    
    /**
     * Obtenir toutes les connexions réseau
     */
    public function getNetworkConnections() {
        return $this->db->fetchAll("SELECT * FROM v_network_connections");
    }
    
    /**
     * Obtenir les ports disponibles d'un équipement
     */
    public function getAvailablePorts($equipmentId) {
        $sql = "SELECT * FROM network_ports 
                WHERE equipment_id = ? 
                AND port_status = 'inactive' 
                AND connected_to_equipment_id IS NULL 
                ORDER BY port_number";
        return $this->db->fetchAll($sql, [$equipmentId]);
    }
    
    /**
     * Supprimer tous les ports d'un équipement
     */
    private function deleteEquipmentPorts($equipmentId) {
        $sql = "DELETE FROM network_ports WHERE equipment_id = ?";
        return $this->db->query($sql, [$equipmentId]);
    }
    
    /**
     * Obtenir les statistiques des ports
     */
    public function getPortStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total_ports,
                    SUM(CASE WHEN port_status = 'active' THEN 1 ELSE 0 END) as active_ports,
                    SUM(CASE WHEN port_status = 'inactive' THEN 1 ELSE 0 END) as inactive_ports,
                    SUM(CASE WHEN port_status = 'disabled' THEN 1 ELSE 0 END) as disabled_ports,
                    SUM(CASE WHEN port_status = 'error' THEN 1 ELSE 0 END) as error_ports,
                    SUM(CASE WHEN connected_to_equipment_id IS NOT NULL THEN 1 ELSE 0 END) as connected_ports
                FROM network_ports";
        return $this->db->fetch($sql);
    }
    
    /**
     * Rechercher des ports par critères
     */
    public function searchPorts($criteria) {
        $sql = "SELECT np.*, ne.name as equipment_name, ne.type as equipment_type 
                FROM network_ports np 
                JOIN network_equipments ne ON np.equipment_id = ne.id 
                WHERE 1=1";
        $params = [];
        
        if (isset($criteria['equipment_id'])) {
            $sql .= " AND np.equipment_id = ?";
            $params[] = $criteria['equipment_id'];
        }
        
        if (isset($criteria['port_status'])) {
            $sql .= " AND np.port_status = ?";
            $params[] = $criteria['port_status'];
        }
        
        if (isset($criteria['port_type'])) {
            $sql .= " AND np.port_type = ?";
            $params[] = $criteria['port_type'];
        }
        
        if (isset($criteria['vlan_id'])) {
            $sql .= " AND np.vlan_id = ?";
            $params[] = $criteria['vlan_id'];
        }
        
        $sql .= " ORDER BY ne.name, np.port_number";
        
        return $this->db->fetchAll($sql, $params);
    }
}
?> 