<?php

class Nas extends BaseModel 
{
    public function getAll($tenantId = null, $siteId = null) 
    {
        $sql = "SELECT n.*, s.name as site_name, t.name as tenant_name, ip.ip_address 
                FROM nas n
                LEFT JOIN sites s ON n.site_id = s.id
                LEFT JOIN tenants t ON n.tenant_id = t.id
                LEFT JOIN ip_addresses ip ON n.ip_address_id = ip.id
                WHERE 1=1";
        $params = [];
        if ($tenantId && $tenantId !== 'all') {
            $sql .= " AND n.tenant_id = ?";
            $params[] = $tenantId;
        }
        if ($siteId && $siteId !== 'all') {
            $sql .= " AND n.site_id = ?";
            $params[] = $siteId;
        }
        $sql .= " ORDER BY n.name ASC";
        return $this->fetchAll($sql, $params);
    }

    public function getById($id) 
    {
        return $this->fetch(
            "SELECT n.*, s.name as site_name, t.name as tenant_name, ip.ip_address 
             FROM nas n
             LEFT JOIN sites s ON n.site_id = s.id
             LEFT JOIN tenants t ON n.tenant_id = t.id
             LEFT JOIN ip_addresses ip ON n.ip_address_id = ip.id
             WHERE n.id = ?",
            [$id]
        );
    }

    public function getCount($tenantId = null, $siteId = null) 
    {
        $sql = "SELECT COUNT(*) as c FROM nas n WHERE 1=1";
        $params = [];
        if ($tenantId && $tenantId !== 'all') {
            $sql .= " AND n.tenant_id = ?";
            $params[] = $tenantId;
        }
        if ($siteId && $siteId !== 'all') {
            $sql .= " AND n.site_id = ?";
            $params[] = $siteId;
        }
        $r = $this->fetch($sql, $params);
        return $r ? (int)$r['c'] : 0;
    }

    public function create($data) 
    {
        $this->query(
            "INSERT INTO nas (name, host, port, type, site_id, tenant_id, ip_address_id, description) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['name'],
                $data['host'],
                (int)($data['port'] ?? 5000),
                $data['type'] ?? 'synology',
                $data['site_id'] ?: null,
                $data['tenant_id'] ?: null,
                $data['ip_address_id'] ?: null,
                $data['description'] ?? null
            ]
        );
        return $this->getLastInsertId();
    }

    public function update($id, $data) 
    {
        return $this->query(
            "UPDATE nas SET name=?, host=?, port=?, type=?, site_id=?, tenant_id=?, ip_address_id=?, description=? 
             WHERE id = ?",
            [
                $data['name'],
                $data['host'],
                (int)($data['port'] ?? 5000),
                $data['type'] ?? 'synology',
                $data['site_id'] ?: null,
                $data['tenant_id'] ?: null,
                $data['ip_address_id'] ?: null,
                $data['description'] ?? null,
                $id
            ]
        );
    }

    public function delete($id) 
    {
        return $this->query("DELETE FROM nas WHERE id = ?", [$id]);
    }

    public function getLastDiscovery($nasId) 
    {
        return $this->fetch(
            "SELECT * FROM nas_discovery WHERE nas_id = ? ORDER BY discovered_at DESC LIMIT 1",
            [$nasId]
        );
    }

    public function saveDiscovery($nasId, $sharesJson, $volumesJson = null, $rawResponse = null, $error = null) 
    {
        return $this->query(
            "INSERT INTO nas_discovery (nas_id, shares_json, volumes_json, raw_response, error_message) 
             VALUES (?, ?, ?, ?, ?)",
            [$nasId, $sharesJson, $volumesJson, $rawResponse, $error]
        );
    }

    /** Enregistre un audit agent NAS : partages + volumes + disques + raid, raw = sortie brute */
    public function saveAuditDiscovery($nasId, $sharesJson, $volumesJson, $disksJson, $raidJson, $rawResponse) 
    {
        $raidJson = $raidJson ?? json_encode([]);
        try {
            $this->query(
                "INSERT INTO nas_discovery (nas_id, shares_json, volumes_json, disks_json, raid_json, raw_response, error_message) 
                 VALUES (?, ?, ?, ?, ?, ?, NULL)",
                [$nasId, $sharesJson, $volumesJson, $disksJson, $raidJson, $rawResponse]
            );
        } catch (Exception $e) {
            $this->query(
                "INSERT INTO nas_discovery (nas_id, shares_json, volumes_json, disks_json, raw_response, error_message) 
                 VALUES (?, ?, ?, ?, ?, NULL)",
                [$nasId, $sharesJson, $volumesJson, $disksJson, $rawResponse]
            );
        }
    }

    public function hasCredentials($nasId)
    {
        $r = $this->fetch("SELECT 1 FROM nas_credentials WHERE nas_id = ?", [$nasId]);
        return !empty($r);
    }

    public function getCredentialsUsername($nasId)
    {
        $r = $this->fetch("SELECT username FROM nas_credentials WHERE nas_id = ?", [$nasId]);
        return $r ? $r['username'] : '';
    }

    public function saveCredentials($nasId, $username, $password)
    {
        require_once __DIR__ . '/../config/credential_helper.php';
        $encrypted = nas_credential_encrypt($password);
        $this->query(
            "INSERT INTO nas_credentials (nas_id, username, password_encrypted) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE username = VALUES(username), password_encrypted = VALUES(password_encrypted)",
            [$nasId, $username, $encrypted]
        );
    }

}
