<?php

require_once 'config/Database.php';

class IpAddress 
{
    private $db;
    
    public function __construct() 
    {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Récupère toutes les adresses IP avec informations des sites
     */
    public function getAll($tenant_id = null, $site_id = null) 
    {
        $query = "SELECT ip.*, 
                         s.name as site_name,
                         t.name as tenant_name,
                         CASE WHEN ne.ip_address_id IS NOT NULL THEN 1 ELSE 0 END as is_used
                  FROM ip_addresses ip
                  LEFT JOIN sites s ON ip.site_id = s.id
                  LEFT JOIN tenants t ON ip.tenant_id = t.id
                  LEFT JOIN network_equipments ne ON ne.ip_address_id = ip.id
                  WHERE 1=1";
        
        $params = [];
        
        if ($tenant_id && $tenant_id !== 'all') {
            $query .= " AND (ip.tenant_id = ? OR ip.tenant_id IS NULL)";
            $params[] = $tenant_id;
        }
        
        if ($site_id && $site_id !== 'all') {
            $query .= " AND ip.site_id = ?";
            $params[] = $site_id;
        }
        
        $query .= " ORDER BY INET_ATON(ip.ip_address) ASC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère une adresse IP par ID
     */
    public function getById($id) 
    {
        $query = "SELECT ip.*, 
                         s.name as site_name,
                         t.name as tenant_name
                  FROM ip_addresses ip
                  LEFT JOIN sites s ON ip.site_id = s.id
                  LEFT JOIN tenants t ON ip.tenant_id = t.id
                  WHERE ip.id = ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crée une nouvelle adresse IP
     */
    public function create($data) 
    {
        $query = "INSERT INTO ip_addresses (ip_address, description, subnet_mask, gateway, 
                         dns1, dns2, vlan_id, tenant_id, site_id, created_at, updated_at) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            $data['ip_address'],
            $data['description'] ?? null,
            $data['subnet_mask'] ?? null,
            $data['gateway'] ?? null,
            $data['dns1'] ?? null,
            $data['dns2'] ?? null,
            $data['vlan_id'] ?? null,
            $data['tenant_id'] ?? null,
            $data['site_id'] ?? null
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Met à jour une adresse IP
     */
    public function update($id, $data) 
    {
        $query = "UPDATE ip_addresses 
                  SET ip_address = ?, description = ?, subnet_mask = ?, gateway = ?,
                      dns1 = ?, dns2 = ?, vlan_id = ?, tenant_id = ?, site_id = ?,
                      updated_at = NOW()
                  WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $data['ip_address'],
            $data['description'] ?? null,
            $data['subnet_mask'] ?? null,
            $data['gateway'] ?? null,
            $data['dns1'] ?? null,
            $data['dns2'] ?? null,
            $data['vlan_id'] ?? null,
            $data['tenant_id'] ?? null,
            $data['site_id'] ?? null,
            $id
        ]);
    }
    
    /**
     * Supprime une adresse IP
     */
    public function delete($id) 
    {
        // Vérifier si l'adresse IP est utilisée
        $checkQuery = "SELECT COUNT(*) as count FROM network_equipments WHERE ip_address_id = ?";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->execute([$id]);
        $result = $checkStmt->fetch();
        
        if ($result['count'] > 0) {
            throw new Exception("Cette adresse IP est utilisée par un équipement réseau et ne peut pas être supprimée.");
        }
        
        $query = "DELETE FROM ip_addresses WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }
    
    /**
     * Compte total des adresses IP
     */
    public function getTotalCount($tenant_id = null, $site_id = null) 
    {
        $query = "SELECT COUNT(*) as count FROM ip_addresses WHERE 1=1";
        $params = [];
        
        if ($tenant_id && $tenant_id !== 'all') {
            $query .= " AND (tenant_id = ? OR tenant_id IS NULL)";
            $params[] = $tenant_id;
        }
        
        if ($site_id && $site_id !== 'all') {
            $query .= " AND site_id = ?";
            $params[] = $site_id;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    /**
     * Compte des adresses IP utilisées
     */
    public function getUsedCount($tenant_id = null, $site_id = null) 
    {
        $query = "SELECT COUNT(DISTINCT ne.ip_address_id) as count 
                  FROM network_equipments ne
                  INNER JOIN ip_addresses ip ON ne.ip_address_id = ip.id
                  WHERE ne.ip_address_id IS NOT NULL";
        $params = [];
        
        if ($tenant_id && $tenant_id !== 'all') {
            $query .= " AND (ip.tenant_id = ? OR ip.tenant_id IS NULL)";
            $params[] = $tenant_id;
        }
        
        if ($site_id && $site_id !== 'all') {
            $query .= " AND ip.site_id = ?";
            $params[] = $site_id;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    /**
     * Compte des adresses IP disponibles
     */
    public function getAvailableCount($tenant_id = null, $site_id = null) 
    {
        return $this->getTotalCount($tenant_id, $site_id) - $this->getUsedCount($tenant_id, $site_id);
    }
    
    /**
     * Compte des sous-réseaux uniques
     */
    public function getSubnetCount($tenant_id = null, $site_id = null) 
    {
        $query = "SELECT COUNT(DISTINCT subnet_mask) as count 
                  FROM ip_addresses 
                  WHERE subnet_mask IS NOT NULL AND subnet_mask != '' AND subnet_mask != '0.0.0.0'";
        $params = [];
        
        if ($tenant_id && $tenant_id !== 'all') {
            $query .= " AND (tenant_id = ? OR tenant_id IS NULL)";
            $params[] = $tenant_id;
        }
        
        if ($site_id && $site_id !== 'all') {
            $query .= " AND site_id = ?";
            $params[] = $site_id;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'];
    }
    
    /**
     * Vérifier si une adresse IP existe déjà
     */
    public function exists($ip_address, $excludeId = null) 
    {
        $query = "SELECT COUNT(*) as count FROM ip_addresses WHERE ip_address = ?";
        $params = [$ip_address];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }
} 