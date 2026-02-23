<?php

require_once 'config/Database.php';
require_once 'classes/NetworkPortManager.php';

class NetworkEquipment {
    private $db;
    private $portManager;
    
    public function __construct() {
        $this->db = new Database();
        $this->portManager = new NetworkPortManager();
    }
    
    public function getAll($siteId = null) {
        $sql = "SELECT ne.*, s.name as site_name, m.name as model_name, 
                       mf.name as manufacturer_name, ip.ip_address as ip_address
                FROM network_equipments ne
                LEFT JOIN sites s ON ne.site_id = s.id
                LEFT JOIN models m ON ne.model_id = m.id
                LEFT JOIN manufacturers mf ON ne.manufacturer_id = mf.id
                LEFT JOIN ip_addresses ip ON ne.ip_address_id = ip.id";
        
        $params = [];
        if ($siteId && $siteId !== 'all') {
            $sql .= " WHERE ne.site_id = ?";
            $params[] = $siteId;
        }
        
        $sql .= " ORDER BY ne.name";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getById($id) {
        $sql = "SELECT ne.*, s.name as site_name, m.name as model_name, 
                       mf.name as manufacturer_name, ip.ip_address as ip_address
                FROM network_equipments ne
                LEFT JOIN sites s ON ne.site_id = s.id
                LEFT JOIN models m ON ne.model_id = m.id
                LEFT JOIN manufacturers mf ON ne.manufacturer_id = mf.id
                LEFT JOIN ip_addresses ip ON ne.ip_address_id = ip.id
                WHERE ne.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }
    
    public function create($data) {
        $sql = "INSERT INTO network_equipments (name, type, model_id, site_id, manufacturer_id, ip_address_id, status, login_id, ports_count) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->query($sql, [
            $data['name'],
            $data['type'],
            $data['model_id'] ?: null,
            $data['site_id'],
            $data['manufacturer_id'] ?: null,
            $data['ip_address_id'] ?: null,
            $data['status'] ?: 'inactive',
            $data['login_id'] ?: null,
            $data['ports_count'] ?: 0
        ]);
        
        $equipmentId = $this->db->lastInsertId();
        
        // Créer les ports si spécifiés
        if (isset($data['ports_count']) && $data['ports_count'] > 0) {
            $this->portManager->setEquipmentPorts(
                $equipmentId, 
                $data['ports_count'], 
                $data['port_type'] ?? 'ethernet',
                $data['port_speed'] ?? '1Gbps'
            );
        }
        
        return $equipmentId;
    }
    
    public function update($id, $data) {
        $sql = "UPDATE network_equipments 
                SET name = ?, type = ?, model_id = ?, site_id = ?, manufacturer_id = ?, 
                    ip_address_id = ?, status = ?, login_id = ?, ports_count = ?
                WHERE id = ?";
        
        return $this->db->query($sql, [
            $data['name'],
            $data['type'],
            $data['model_id'] ?: null,
            $data['site_id'],
            $data['manufacturer_id'] ?: null,
            $data['ip_address_id'] ?: null,
            $data['status'] ?: 'inactive',
            $data['login_id'] ?: null,
            $data['ports_count'] ?: 0,
            $id
        ]);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM network_equipments WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
    
    public function getStatistics($siteId = null) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive,
                    SUM(CASE WHEN type = 'router' THEN 1 ELSE 0 END) as routers,
                    SUM(CASE WHEN type = 'switch' THEN 1 ELSE 0 END) as switches,
                    SUM(CASE WHEN type = 'wifiAP' THEN 1 ELSE 0 END) as wifi_ap,
                    SUM(CASE WHEN type = 'wifi infra' THEN 1 ELSE 0 END) as wifi_infra,
                    SUM(ports_count) as total_ports
                FROM network_equipments";
        
        $params = [];
        if ($siteId && $siteId !== 'all') {
            $sql .= " WHERE site_id = ?";
            $params[] = $siteId;
        }
        
        return $this->db->fetch($sql, $params);
    }
    
    public function getSites() {
        return $this->db->fetchAll("SELECT id, name FROM sites ORDER BY name");
    }
    
    public function getManufacturers() {
        return $this->db->fetchAll("SELECT id, name FROM manufacturers ORDER BY name");
    }
    
    public function getModels() {
        return $this->db->fetchAll("SELECT id, name FROM models ORDER BY name");
    }
    
    public function getIpAddresses() {
        return $this->db->fetchAll("SELECT id, ip_address as address FROM ip_addresses ORDER BY ip_address");
    }
    
    public function getLogins() {
        return $this->db->fetchAll("
            SELECT l.id, l.username, l.description, ls.nom as service_name 
            FROM logins l
            LEFT JOIN login_services ls ON l.service_id = ls.id
            ORDER BY l.username
        ");
    }
    
    public function getPortsForEquipment($equipmentId) {
        return $this->portManager->getEquipmentPorts($equipmentId);
    }
    
    public function getPortsSummary() {
        return $this->portManager->getEquipmentsPortsSummary();
    }
    
    public function getNetworkConnections() {
        return $this->portManager->getNetworkConnections();
    }
}
?> 