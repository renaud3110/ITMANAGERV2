<?php

class Computer extends BaseModel 
{
    public function __construct() 
    {
        parent::__construct();
    }

    public function getAll($tenantId = null, $siteId = null) 
    {
        $sql = "SELECT 
                    pc.*,
                    mon.cpu_temp as monitor_cpu_temp,
                    mon.gpu_temp as monitor_gpu_temp,
                    mon.last_seen as monitor_last_seen,
                    mon.logged_in as monitor_logged_in,
                    mon.last_logout_at as monitor_last_logout_at,
                    mon.logged_in_username as monitor_logged_in_username,
                    t.name as tenant_name,
                    s.name as site_name,
                    os.name as operating_system_name,
                    os.version as os_version_name,
                    ip.ip_address,
                    ip.dns_servers,
                    ip.gateway,
                    ip.subnet_mask,
                    m.name as model_name,
                    mf.name as model_brand,
                    l.username as account_username,
                    p.nom as person_nom,
                    p.prenom as person_prenom,
                    p.email as person_email,
                    pc.ram_total,
                    pc.ram_used
                FROM pcs_laptops pc
                LEFT JOIN tenants t ON pc.tenant_id = t.id
                LEFT JOIN sites s ON pc.site_id = s.id
                LEFT JOIN operating_systems os ON pc.operating_system_id = os.id
                LEFT JOIN ip_addresses ip ON pc.ip_address_id = ip.id
                LEFT JOIN models m ON pc.model_id = m.id
                LEFT JOIN manufacturers mf ON m.manufacturer_id = mf.id
                LEFT JOIN logins l ON pc.account_id = l.id
                LEFT JOIN persons p ON pc.person_id = p.id
                LEFT JOIN pc_monitor_status mon ON pc.id = mon.pc_id
                WHERE 1=1";
        
        $params = [];
        
        if ($tenantId && $tenantId !== 'all') {
            $sql .= " AND pc.tenant_id = ?";
            $params[] = $tenantId;
        }
        
        if ($siteId && $siteId !== 'all') {
            $sql .= " AND pc.site_id = ?";
            $params[] = $siteId;
        }
        
        $sql .= " ORDER BY pc.id DESC";
        
        return $this->fetchAll($sql, $params);
    }

    public function getById($id) 
    {
        $sql = "SELECT 
                    pc.*,
                    mon.cpu_temp as monitor_cpu_temp,
                    mon.gpu_temp as monitor_gpu_temp,
                    mon.last_seen as monitor_last_seen,
                    mon.logged_in as monitor_logged_in,
                    mon.last_logout_at as monitor_last_logout_at,
                    mon.logged_in_username as monitor_logged_in_username,
                    t.name as tenant_name,
                    s.name as site_name,
                    os.name as operating_system_name,
                    os.version as os_version_name,
                    ip.ip_address,
                    ip.dns_servers,
                    ip.gateway,
                    ip.subnet_mask,
                    m.name as model_name,
                    mf.name as model_brand,
                    l.username as account_username,
                    p.nom as person_nom,
                    p.prenom as person_prenom,
                    p.email as person_email,
                    pc.ram_total,
                    pc.ram_used
                FROM pcs_laptops pc
                LEFT JOIN tenants t ON pc.tenant_id = t.id
                LEFT JOIN sites s ON pc.site_id = s.id
                LEFT JOIN operating_systems os ON pc.operating_system_id = os.id
                LEFT JOIN ip_addresses ip ON pc.ip_address_id = ip.id
                LEFT JOIN models m ON pc.model_id = m.id
                LEFT JOIN manufacturers mf ON m.manufacturer_id = mf.id
                LEFT JOIN logins l ON pc.account_id = l.id
                LEFT JOIN persons p ON pc.person_id = p.id
                LEFT JOIN pc_monitor_status mon ON pc.id = mon.pc_id
                WHERE pc.id = ?";
        
        return $this->fetch($sql, [$id]);
    }

    public function create($data) 
    {
        $sql = "INSERT INTO pcs_laptops (
                    name, tenant_id, site_id, operating_system_id, ip_address_id, 
                    processor_model, teamviewer_id, rustdesk_id, model_id, status, 
                    account_id, last_account, serial_number
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->query($sql, [
            $data['name'] ?: null,
            $data['tenant_id'],
            $data['site_id'],
            $data['operating_system_id'] ?: null,
            $data['ip_address_id'] ?: null,
            $data['processor_model'] ?: null,
            $data['teamviewer_id'] ?: null,
            $data['rustdesk_id'] ?: null,
            $data['model_id'] ?: null,
            $data['status'] ?: 'free',
            $data['account_id'] ?: null,
            $data['last_account'] ?: null,
            $data['serial_number'] ?: null
        ]);
        
        return $stmt !== false;
    }

    public function update($id, $data) 
    {
        $sql = "UPDATE pcs_laptops SET 
                    name = ?, tenant_id = ?, site_id = ?, operating_system_id = ?, 
                    ip_address_id = ?, processor_model = ?, teamviewer_id = ?, 
                    rustdesk_id = ?,
                    model_id = ?, status = ?, account_id = ?, person_id = ?,
                    last_account = ?, serial_number = ?
                WHERE id = ?";
        
        $stmt = $this->query($sql, [
            $data['name'] ?: null,
            $data['tenant_id'],
            $data['site_id'],
            $data['operating_system_id'] ?: null,
            $data['ip_address_id'] ?: null,
            $data['processor_model'] ?: null,
            $data['teamviewer_id'] ?: null,
            $data['rustdesk_id'] ?: null,
            $data['model_id'] ?: null,
            $data['status'] ?: 'free',
            $data['account_id'] ?: null,
            $data['person_id'] ?: null,
            $data['last_account'] ?: null,
            $data['serial_number'] ?: null,
            $id
        ]);
        
        return $stmt !== false;
    }

    public function delete($id) 
    {
        $sql = "DELETE FROM pcs_laptops WHERE id = ?";
        $stmt = $this->query($sql, [$id]);
        return $stmt !== false;
    }

    public function getCount($tenantId = null, $siteId = null) 
    {
        $sql = "SELECT COUNT(*) FROM pcs_laptops WHERE 1=1";
        $params = [];
        
        if ($tenantId && $tenantId !== 'all') {
            $sql .= " AND tenant_id = ?";
            $params[] = $tenantId;
        }
        
        if ($siteId && $siteId !== 'all') {
            $sql .= " AND site_id = ?";
            $params[] = $siteId;
        }
        
        $result = $this->fetch($sql, $params);
        return $result ? (int)$result['COUNT(*)'] : 0;
    }

    public function getDisksWithPartitions($pcId) 
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
                WHERE pd.pc_id = ?
                ORDER BY pd.id, dp.drive_letter";
        
        $results = $this->fetchAll($sql, [$pcId]);
        
        // Organiser les résultats par disque
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
            
            // Ajouter la partition si elle existe
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

    public function getGpus($pcId)
    {
        $sql = "SELECT id, model, vendor, driver_version, vram_bytes, video_processor FROM pc_gpus WHERE pc_id = ? ORDER BY id";
        return $this->fetchAll($sql, [$pcId]);
    }

    public function getMonitors($pcId)
    {
        $sql = "SELECT id, name, manufacturer, serial_number, resolution FROM pc_monitors WHERE pc_id = ? ORDER BY id";
        return $this->fetchAll($sql, [$pcId]);
    }

    public function getPrinters($pcId)
    {
        $sql = "SELECT id, name, driver, port, is_default, is_shared FROM pc_printers WHERE pc_id = ? ORDER BY name";
        return $this->fetchAll($sql, [$pcId]);
    }

    public function getNetworkAdapters($pcId)
    {
        $sql = "SELECT id, name, type, ip_cidr, gateway, wifi_ssid FROM pc_network_adapters WHERE pc_id = ? ORDER BY type, name";
        return $this->fetchAll($sql, [$pcId]);
    }

    public function getWindowsUpdates($pcId)
    {
        $sql = "SELECT id, hotfix_id, description, installed_on FROM pc_windows_updates WHERE pc_id = ? ORDER BY installed_on DESC, hotfix_id";
        return $this->fetchAll($sql, [$pcId]);
    }

    public function getWindowsServices($pcId)
    {
        $sql = "SELECT id, name, display_name, description, status, start_type FROM pc_windows_services WHERE pc_id = ? ORDER BY name";
        return $this->fetchAll($sql, [$pcId]);
    }

    public function getWindowsStartup($pcId)
    {
        $sql = "SELECT id, name, command, location FROM pc_windows_startup WHERE pc_id = ? ORDER BY name";
        return $this->fetchAll($sql, [$pcId]);
    }

    public function getWindowsShared($pcId)
    {
        $sql = "SELECT id, name, path, description FROM pc_windows_shared WHERE pc_id = ? ORDER BY name";
        return $this->fetchAll($sql, [$pcId]);
    }

    public function getWindowsMapped($pcId)
    {
        $sql = "SELECT id, drive_letter, path, label FROM pc_windows_mapped WHERE pc_id = ? ORDER BY drive_letter";
        return $this->fetchAll($sql, [$pcId]);
    }

    public function getWindowsUsers($pcId)
    {
        $sql = "SELECT id, username, full_name, last_login, account_type FROM pc_windows_users WHERE pc_id = ? ORDER BY last_login DESC, username";
        return $this->fetchAll($sql, [$pcId]);
    }

    public function getWindowsUserGroups($pcId)
    {
        $sql = "SELECT id, group_name FROM pc_windows_user_groups WHERE pc_id = ? ORDER BY group_name";
        return $this->fetchAll($sql, [$pcId]);
    }

    public function getWindowsLicense($pcId)
    {
        $sql = "SELECT id, description, status FROM pc_windows_license WHERE pc_id = ?";
        return $this->fetch($sql, [$pcId]);
    }
} 