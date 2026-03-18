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
                    ROUND(s.ram_used / 1024 / 1024 / 1024, 2) as ram_used_gb,
                    mon.cpu_temp as monitor_cpu_temp,
                    mon.gpu_temp as monitor_gpu_temp,
                    mon.last_seen as monitor_last_seen
                FROM servers s
                LEFT JOIN sites si ON s.site_id = si.id
                LEFT JOIN server_monitor_status mon ON s.id = mon.server_id
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
                    ROUND(s.ram_used / 1024 / 1024 / 1024, 2) as ram_used_gb,
                    mon.cpu_temp as monitor_cpu_temp,
                    mon.gpu_temp as monitor_gpu_temp,
                    mon.last_seen as monitor_last_seen,
                    mon.logged_in as monitor_logged_in,
                    mon.last_logout_at as monitor_last_logout_at,
                    mon.logged_in_username as monitor_logged_in_username,
                    p.prenom as person_prenom,
                    p.nom as person_nom,
                    p.email as person_email
                FROM servers s
                LEFT JOIN sites si ON s.site_id = si.id
                LEFT JOIN tenants t ON si.tenant_id = t.id
                LEFT JOIN operating_systems os ON s.operating_system_id = os.id
                LEFT JOIN ip_addresses ip ON s.ip_address_id = ip.id
                LEFT JOIN models m ON s.model_id = m.id
                LEFT JOIN manufacturers mf ON m.manufacturer_id = mf.id
                LEFT JOIN server_monitor_status mon ON s.id = mon.server_id
                LEFT JOIN persons p ON s.person_id = p.id
                WHERE s.id = ?";
        
        $server = $this->fetch($sql, [$id]);
        if ($server && !empty($server['esxi_vm_id'])) {
            $server['esxi_host'] = $this->getEsxiHostByVmId($server['esxi_vm_id']);
        }
        return $server;
    }

    /** Retourne l'hôte ESXi associé à une VM (quand le serveur est lié via esxi_vm_id) */
    public function getEsxiHostByVmId($esxiVmId)
    {
        return $this->fetch(
            "SELECT eh.id, eh.name, eh.host, eh.port
             FROM esxi_vms ev
             JOIN esxi_hosts eh ON eh.id = ev.esxi_host_id
             WHERE ev.id = ?",
            [$esxiVmId]
        );
    }

    public function create($data) 
    {
        $sql = "INSERT INTO servers (
                    name, type, site_id, model_id, processor_model, 
                    ram_total, ram_used, operating_system_id, ip_address_id, 
                    hostname, teamviewer_id, rustdesk_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
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
            $data['rustdesk_id'] ?: null
        ]);
        
        return $stmt !== false;
    }

    public function update($id, $data) 
    {
        $sql = "UPDATE servers SET 
                    name = ?, type = ?, site_id = ?, model_id = ?, 
                    processor_model = ?, ram_total = ?, ram_used = ?, 
                    operating_system_id = ?, ip_address_id = ?, hostname = ?, 
                    teamviewer_id = ?, rustdesk_id = ?, person_id = ?, updated_at = CURRENT_TIMESTAMP
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
            $data['rustdesk_id'] ?: null,
            $data['person_id'] ?: null,
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

    public function getDisksWithPartitions($serverId) 
    {
        $sql = "SELECT 
                    pd.id as disk_id,
                    pd.model as disk_model,
                    pd.serial_number as disk_serial,
                    pd.interface_type as disk_interface,
                    pd.size_bytes as disk_size_bytes,
                    dp.id as partition_id,
                    dp.drive_letter,
                    dp.label as partition_label,
                    dp.file_system,
                    dp.total_size_bytes as partition_total_bytes,
                    dp.free_space_bytes as partition_free_bytes
                FROM physical_disks pd
                LEFT JOIN disk_partitions dp ON pd.id = dp.physical_disk_id
                WHERE pd.server_id = ?
                ORDER BY pd.id, dp.drive_letter";
        
        $results = $this->fetchAll($sql, [$serverId]);
        
        $disks = [];
        foreach ($results as $row) {
            $diskId = $row['disk_id'];
            
            if (!isset($disks[$diskId])) {
                $disks[$diskId] = [
                    'id' => $row['disk_id'],
                    'model' => $row['disk_model'],
                    'serial_number' => $row['disk_serial'],
                    'interface_type' => $row['disk_interface'],
                    'size_bytes' => $row['disk_size_bytes'],
                    'size_gb' => $row['disk_size_bytes'] ? round($row['disk_size_bytes'] / (1024*1024*1024), 2) : 0,
                    'partitions' => []
                ];
            }
            
            if ($row['partition_id']) {
                $disks[$diskId]['partitions'][] = [
                    'id' => $row['partition_id'],
                    'drive_letter' => $row['drive_letter'],
                    'label' => $row['partition_label'],
                    'file_system' => $row['file_system'],
                    'total_size_bytes' => $row['partition_total_bytes'],
                    'free_space_bytes' => $row['partition_free_bytes'],
                    'total_size_gb' => $row['partition_total_bytes'] ? round($row['partition_total_bytes'] / (1024*1024*1024), 2) : 0,
                    'free_space_gb' => $row['partition_free_bytes'] ? round($row['partition_free_bytes'] / (1024*1024*1024), 2) : 0,
                    'used_space_gb' => $row['partition_total_bytes'] && $row['partition_free_bytes'] ? 
                        round(($row['partition_total_bytes'] - $row['partition_free_bytes']) / (1024*1024*1024), 2) : 0,
                    'usage_percentage' => $row['partition_total_bytes'] && $row['partition_free_bytes'] ? 
                        round((($row['partition_total_bytes'] - $row['partition_free_bytes']) / $row['partition_total_bytes']) * 100, 1) : 0
                ];
            }
        }
        
        return array_values($disks);
    }

    public function getInstalledSoftware($serverId) 
    {
        $sql = "SELECT s.name, s.version, ins.installation_date
                FROM software s
                JOIN server_installed_software ins ON s.id = ins.software_id
                WHERE ins.server_id = ?
                ORDER BY s.name ASC";
        return $this->fetchAll($sql, [$serverId]);
    }

    public function getWindowsUpdates($serverId) {
        return $this->fetchAll("SELECT id, hotfix_id, description, installed_on FROM server_windows_updates WHERE server_id = ? ORDER BY installed_on DESC, hotfix_id", [$serverId]);
    }

    public function getWindowsServices($serverId) {
        return $this->fetchAll("SELECT id, name, display_name, description, status, start_type FROM server_windows_services WHERE server_id = ? ORDER BY name", [$serverId]);
    }

    public function getWindowsStartup($serverId) {
        return $this->fetchAll("SELECT id, name, command, location FROM server_windows_startup WHERE server_id = ? ORDER BY name", [$serverId]);
    }

    public function getWindowsShared($serverId) {
        return $this->fetchAll("SELECT id, name, path, description FROM server_windows_shared WHERE server_id = ? ORDER BY name", [$serverId]);
    }

    public function getWindowsMapped($serverId) {
        return $this->fetchAll("SELECT id, drive_letter, path, label FROM server_windows_mapped WHERE server_id = ? ORDER BY drive_letter", [$serverId]);
    }

    public function getWindowsUsers($serverId) {
        return $this->fetchAll("SELECT id, username, full_name, last_login, account_type FROM server_windows_users WHERE server_id = ? ORDER BY last_login DESC, username", [$serverId]);
    }

    public function getWindowsUserGroups($serverId) {
        return $this->fetchAll("SELECT id, group_name FROM server_windows_user_groups WHERE server_id = ? ORDER BY group_name", [$serverId]);
    }

    public function getWindowsLicense($serverId) {
        return $this->fetch("SELECT id, description, status FROM server_windows_license WHERE server_id = ?", [$serverId]);
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