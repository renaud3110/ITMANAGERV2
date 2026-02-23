<?php

class Server extends BaseModel 
{
    public function __construct() 
    {
        parent::__construct();
    }

    public function getAll($tenantId = null, $siteId = null) 
    {
        $sql = "SELECT 
                    s.*,
                    si.name as site_name,
                    t.name as tenant_name,
                    os.name as operating_system_name,
                    os.version as os_version_name,
                    ip.ip_address,
                    ip.dns_servers,
                    ip.gateway,
                    ip.subnet_mask,
                    m.name as model_name,
                    mf.name as model_brand,
                    ROUND(s.ram_total / 1024 / 1024 / 1024, 2) as ram_total_gb,
                    ROUND(s.ram_used / 1024 / 1024 / 1024, 2) as ram_used_gb
                FROM servers s
                LEFT JOIN sites si ON s.site_id = si.id
                LEFT JOIN tenants t ON si.tenant_id = t.id
                LEFT JOIN operating_systems os ON s.operating_system_id = os.id
                LEFT JOIN ip_addresses ip ON s.ip_address_id = ip.id
                LEFT JOIN models m ON s.model_id = m.id
                LEFT JOIN manufacturers mf ON m.manufacturer_id = mf.id
                WHERE 1=1";
        
        $params = [];
        
        if ($tenantId && $tenantId !== 'all') {
            $sql .= " AND si.tenant_id = ?";
            $params[] = $tenantId;
        }
        
        if ($siteId && $siteId !== 'all') {
            $sql .= " AND s.site_id = ?";
            $params[] = $siteId;
        }
        
        $sql .= " ORDER BY s.id DESC";
        
        return $this->fetchAll($sql, $params);
    }

    public function getById($id) 
    {
        $sql = "SELECT 
                    s.*,
                    si.name as site_name,
                    t.name as tenant_name,
                    os.name as operating_system_name,
                    os.version as os_version_name,
                    ip.ip_address,
                    ip.dns_servers,
                    ip.gateway,
                    ip.subnet_mask,
                    m.name as model_name,
                    mf.name as model_brand,
                    ROUND(s.ram_total / 1024 / 1024 / 1024, 2) as ram_total_gb,
                    ROUND(s.ram_used / 1024 / 1024 / 1024, 2) as ram_used_gb
                FROM servers s
                LEFT JOIN sites si ON s.site_id = si.id
                LEFT JOIN tenants t ON si.tenant_id = t.id
                LEFT JOIN operating_systems os ON s.operating_system_id = os.id
                LEFT JOIN ip_addresses ip ON s.ip_address_id = ip.id
                LEFT JOIN models m ON s.model_id = m.id
                LEFT JOIN manufacturers mf ON m.manufacturer_id = mf.id
                WHERE s.id = ?";
        
        return $this->fetch($sql, [$id]);
    }

    public function create($data) 
    {
        $sql = "INSERT INTO servers (
                    name, type, site_id, model_id, processor_model, 
                    ram_total, ram_used, operating_system_id, ip_address_id, 
                    hostname, teamviewer_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        // Convertir GB en octets pour le stockage
        $ramTotalBytes = isset($data['ram_total_gb']) ? $data['ram_total_gb'] * 1024 * 1024 * 1024 : null;
        $ramUsedBytes = isset($data['ram_used_gb']) ? $data['ram_used_gb'] * 1024 * 1024 * 1024 : null;
        
        $stmt = $this->query($sql, [
            $data['name'] ?: null,
            $data['type'] ?: 'Physique',
            $data['site_id'] ?: null,
            $data['model_id'] ?: null,
            $data['processor_model'] ?: null,
            $ramTotalBytes,
            $ramUsedBytes,
            $data['operating_system_id'] ?: null,
            $data['ip_address_id'] ?: null,
            $data['hostname'] ?: null,
            $data['teamviewer_id'] ?: null
        ]);
        
        return $stmt !== false;
    }

    public function update($id, $data) 
    {
        $sql = "UPDATE servers SET 
                    name = ?, type = ?, site_id = ?, model_id = ?, 
                    processor_model = ?, ram_total = ?, ram_used = ?, 
                    operating_system_id = ?, ip_address_id = ?, hostname = ?, 
                    teamviewer_id = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";
        
        // Convertir GB en octets pour le stockage
        $ramTotalBytes = isset($data['ram_total_gb']) ? $data['ram_total_gb'] * 1024 * 1024 * 1024 : null;
        $ramUsedBytes = isset($data['ram_used_gb']) ? $data['ram_used_gb'] * 1024 * 1024 * 1024 : null;
        
        $stmt = $this->query($sql, [
            $data['name'] ?: null,
            $data['type'] ?: 'Physique',
            $data['site_id'] ?: null,
            $data['model_id'] ?: null,
            $data['processor_model'] ?: null,
            $ramTotalBytes,
            $ramUsedBytes,
            $data['operating_system_id'] ?: null,
            $data['ip_address_id'] ?: null,
            $data['hostname'] ?: null,
            $data['teamviewer_id'] ?: null,
            $id
        ]);
        
        return $stmt !== false;
    }

    public function delete($id) 
    {
        $sql = "DELETE FROM servers WHERE id = ?";
        $stmt = $this->query($sql, [$id]);
        return $stmt !== false;
    }

    public function getCount($tenantId = null, $siteId = null) 
    {
        $sql = "SELECT COUNT(*) FROM servers s
                LEFT JOIN sites si ON s.site_id = si.id
                WHERE 1=1";
        $params = [];
        
        if ($tenantId && $tenantId !== 'all') {
            $sql .= " AND si.tenant_id = ?";
            $params[] = $tenantId;
        }
        
        if ($siteId && $siteId !== 'all') {
            $sql .= " AND s.site_id = ?";
            $params[] = $siteId;
        }
        
        $result = $this->fetch($sql, $params);
        return $result ? (int)$result['COUNT(*)'] : 0;
    }
}
?> 