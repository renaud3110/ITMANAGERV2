<?php

class EsxiHost extends BaseModel
{
    public function getAll($tenantId = null, $siteId = null)
    {
        $sql = "SELECT e.*, s.name as site_name, t.name as tenant_name, ip.ip_address
                FROM esxi_hosts e
                LEFT JOIN sites s ON e.site_id = s.id
                LEFT JOIN tenants t ON e.tenant_id = t.id
                LEFT JOIN ip_addresses ip ON e.ip_address_id = ip.id
                WHERE 1=1";
        $params = [];
        if ($tenantId && $tenantId !== 'all') {
            $sql .= " AND e.tenant_id = ?";
            $params[] = $tenantId;
        }
        if ($siteId && $siteId !== 'all') {
            $sql .= " AND e.site_id = ?";
            $params[] = $siteId;
        }
        $sql .= " ORDER BY e.name ASC";
        return $this->fetchAll($sql, $params);
    }

    public function getById($id)
    {
        return $this->fetch(
            "SELECT e.*, s.name as site_name, t.name as tenant_name, ip.ip_address
             FROM esxi_hosts e
             LEFT JOIN sites s ON e.site_id = s.id
             LEFT JOIN tenants t ON e.tenant_id = t.id
             LEFT JOIN ip_addresses ip ON e.ip_address_id = ip.id
             WHERE e.id = ?",
            [$id]
        );
    }

    public function getCount($tenantId = null, $siteId = null)
    {
        $sql = "SELECT COUNT(*) as c FROM esxi_hosts e WHERE 1=1";
        $params = [];
        if ($tenantId && $tenantId !== 'all') {
            $sql .= " AND e.tenant_id = ?";
            $params[] = $tenantId;
        }
        if ($siteId && $siteId !== 'all') {
            $sql .= " AND e.site_id = ?";
            $params[] = $siteId;
        }
        $r = $this->fetch($sql, $params);
        return $r ? (int)$r['c'] : 0;
    }

    public function create($data)
    {
        $hypervisor = in_array($data['hypervisor_type'] ?? '', ['proxmox', 'esxi']) ? $data['hypervisor_type'] : 'esxi';
        $defaultPort = $hypervisor === 'proxmox' ? 8006 : 443;
        $this->query(
            "INSERT INTO esxi_hosts (name, host, port, site_id, tenant_id, ip_address_id, description, hypervisor_type, discovery_interval_hours)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['name'],
                $data['host'],
                (int)($data['port'] ?? $defaultPort),
                $data['site_id'] ?: null,
                $data['tenant_id'] ?: null,
                $data['ip_address_id'] ?: null,
                $data['description'] ?? null,
                $hypervisor,
                (int)($data['discovery_interval_hours'] ?? 1)
            ]
        );
        return $this->getLastInsertId();
    }

    public function update($id, $data)
    {
        $hypervisor = in_array($data['hypervisor_type'] ?? '', ['proxmox', 'esxi']) ? $data['hypervisor_type'] : 'esxi';
        $defaultPort = $hypervisor === 'proxmox' ? 8006 : 443;
        return $this->query(
            "UPDATE esxi_hosts SET name=?, host=?, port=?, site_id=?, tenant_id=?, ip_address_id=?, description=?, hypervisor_type=?, discovery_interval_hours=?
             WHERE id = ?",
            [
                $data['name'],
                $data['host'],
                (int)($data['port'] ?? $defaultPort),
                $data['site_id'] ?: null,
                $data['tenant_id'] ?: null,
                $data['ip_address_id'] ?: null,
                $data['description'] ?? null,
                $hypervisor,
                (int)($data['discovery_interval_hours'] ?? 1),
                $id
            ]
        );
    }

    public function delete($id)
    {
        return $this->query("DELETE FROM esxi_hosts WHERE id = ?", [$id]);
    }

    public function getLastDiscovery($esxiHostId)
    {
        return $this->fetch(
            "SELECT * FROM esxi_discovery WHERE esxi_host_id = ? ORDER BY discovered_at DESC LIMIT 1",
            [$esxiHostId]
        );
    }

    public function getVms($esxiHostId)
    {
        return $this->fetchAll(
            "SELECT v.*, s.name as server_name, s.hostname as server_hostname
             FROM esxi_vms v
             LEFT JOIN servers s ON v.server_id = s.id
             WHERE v.esxi_host_id = ?
             ORDER BY v.vm_name ASC",
            [$esxiHostId]
        );
    }

    public function hasCredentials($esxiHostId)
    {
        $r = $this->fetch("SELECT 1 FROM esxi_credentials WHERE esxi_host_id = ?", [$esxiHostId]);
        return !empty($r);
    }

    public function getCredentialsUsername($esxiHostId)
    {
        $r = $this->fetch("SELECT username FROM esxi_credentials WHERE esxi_host_id = ?", [$esxiHostId]);
        return $r ? $r['username'] : '';
    }

    public function saveCredentials($esxiHostId, $username, $password)
    {
        require_once __DIR__ . '/../config/credential_helper.php';
        $encrypted = esxi_credential_encrypt($password);
        $this->query(
            "INSERT INTO esxi_credentials (esxi_host_id, username, password_encrypted) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE username = VALUES(username), password_encrypted = VALUES(password_encrypted)",
            [$esxiHostId, $username, $encrypted]
        );
    }

    public function createDiscoveryJob($esxiHostId)
    {
        $host = $this->getById($esxiHostId);
        if (!$host || !$host['site_id']) {
            throw new Exception('Hôte ESXi introuvable ou sans site associé. Associez un site à l\'hôte.');
        }
        if (!$this->hasCredentials($esxiHostId)) {
            throw new Exception('Aucun identifiant enregistré. Enregistrez les identifiants dans la fiche ESXi (modifier).');
        }
        $this->query(
            "INSERT INTO esxi_discovery_jobs (esxi_host_id, site_id, status) VALUES (?, ?, 'pending')",
            [$esxiHostId, $host['site_id']]
        );
        return $this->getLastInsertId();
    }

    /** Associe manuellement un serveur à une VM ESXi */
    public function linkVmToServer($vmId, $serverId)
    {
        $vm = $this->fetch("SELECT id, server_id FROM esxi_vms WHERE id = ?", [$vmId]);
        if (!$vm) {
            return false;
        }
        $oldServerId = $vm['server_id'] ? (int)$vm['server_id'] : null;
        $this->query("UPDATE esxi_vms SET server_id = ? WHERE id = ?", [$serverId ?: null, $vmId]);
        if ($oldServerId) {
            $this->query("UPDATE servers SET esxi_vm_id = NULL WHERE id = ?", [$oldServerId]);
        }
        if ($serverId) {
            $this->query("UPDATE servers SET esxi_vm_id = ? WHERE id = ?", [$vmId, $serverId]);
        }
        return true;
    }
}
