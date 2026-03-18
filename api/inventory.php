<?php
/**
 * API inventaire - Réception des données des agents Windows
 * POST avec JSON + header X-Api-Key
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Api-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

// Charger la config
if (!file_exists(__DIR__ . '/../config/api_config.php')) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'API non configurée. Créez config/api_config.php à partir de api_config.example.php']);
    exit;
}

require_once __DIR__ . '/../config/api_config.php';
require_once __DIR__ . '/../config/Database.php';

if (!defined('API_INVENTORY_ENABLED') || !API_INVENTORY_ENABLED) {
    http_response_code(503);
    echo json_encode(['success' => false, 'error' => 'API inventaire désactivée']);
    exit;
}

$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if (empty($apiKey) || $apiKey !== API_INVENTORY_KEY) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Clé API invalide']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'JSON invalide']);
    exit;
}

try {
    $processor = new InventoryProcessor(new Database());
    $deviceType = strtolower(trim($data['device_type'] ?? 'pc'));
    if ($deviceType === 'server') {
        $result = $processor->processServer($data);
        echo json_encode(['success' => true, 'server_id' => $result['server_id'], 'action' => $result['created'] ? 'created' : 'updated']);
    } else {
        $result = $processor->process($data);
        echo json_encode(['success' => true, 'pc_id' => $result['pc_id'], 'action' => $result['created'] ? 'created' : 'updated']);
    }
} catch (Exception $e) {
    error_log("API Inventaire: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

/**
 * Traitement des données d'inventaire
 */
class InventoryProcessor {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function process(array $data): array {
        $name = trim($data['hostname'] ?? $data['name'] ?? '');
        $serialNumber = trim($data['serial_number'] ?? '');

        if (empty($name)) {
            throw new Exception('hostname requis');
        }

        // Rejeter les requêtes du moniteur (télémétrie) envoyées par erreur ici
        if (!empty($data['monitor_agent']) || (isset($data['cpu_temp']) && !isset($data['disks']))) {
            throw new Exception('Requête moniteur détectée. Utilisez l\'endpoint /api/monitor_telemetry.php pour la télémétrie.');
        }

        $result = $this->findOrCreatePc($data, $name, $serialNumber);
        $pcId = $result['pc_id'];
        // Ne mettre à jour disques/logiciels que si des données sont fournies (évite d'effacer en cas de payload incomplet)
        if (isset($data['disks']) && is_array($data['disks']) && count($data['disks']) > 0) {
            $this->updateDisks($pcId, $data['disks']);
        }
        if (isset($data['software']) && is_array($data['software']) && count($data['software']) > 0) {
            $this->updateSoftware($pcId, $data['software']);
        }
        if (isset($data['gpus']) && is_array($data['gpus'])) {
            $this->updateGpus($pcId, $data['gpus']);
        }
        if (isset($data['monitors']) && is_array($data['monitors'])) {
            $this->updateMonitors($pcId, $data['monitors']);
        }
        if (isset($data['printers']) && is_array($data['printers'])) {
            $this->updatePrinters($pcId, $data['printers']);
        }
        if (isset($data['network_adapters']) && is_array($data['network_adapters'])) {
            $this->updateNetworkAdapters($pcId, $data['network_adapters']);
        }
        if (isset($data['windows_updates']) && is_array($data['windows_updates'])) {
            $this->updateWindowsUpdates($pcId, $data['windows_updates']);
        }
        if (isset($data['windows_services']) && is_array($data['windows_services'])) {
            $this->updateWindowsServices($pcId, $data['windows_services']);
        }
        if (isset($data['windows_startup']) && is_array($data['windows_startup'])) {
            $this->updateWindowsStartup($pcId, $data['windows_startup']);
        }
        if (isset($data['windows_shared']) && is_array($data['windows_shared'])) {
            $this->updateWindowsShared($pcId, $data['windows_shared']);
        }
        if (isset($data['windows_mapped']) && is_array($data['windows_mapped'])) {
            $this->updateWindowsMapped($pcId, $data['windows_mapped']);
        }
        if (isset($data['windows_users']) && is_array($data['windows_users'])) {
            $this->updateWindowsUsers($pcId, $data['windows_users']);
        }
        if (isset($data['windows_user_groups']) && is_array($data['windows_user_groups'])) {
            $this->updateWindowsUserGroups($pcId, $data['windows_user_groups']);
        }
        if (isset($data['windows_license'])) {
            $this->updateWindowsLicense($pcId, $data['windows_license']);
        }

        return ['pc_id' => $pcId, 'created' => $result['created']];
    }

    /**
     * Traitement inventaire serveur (device_type=server)
     */
    public function processServer(array $data): array {
        $name = trim($data['hostname'] ?? $data['name'] ?? '');
        if (empty($name)) {
            throw new Exception('hostname requis');
        }
        if (!empty($data['monitor_agent']) || (isset($data['cpu_temp']) && !isset($data['disks']))) {
            throw new Exception('Requête moniteur détectée.');
        }
        $result = $this->findOrCreateServer($data, $name);
        if (isset($data['software']) && is_array($data['software']) && count($data['software']) > 0) {
            $this->updateServerSoftware($result['server_id'], $data['software']);
        }
        if (isset($data['disks']) && is_array($data['disks']) && count($data['disks']) > 0) {
            $this->updateServerDisks($result['server_id'], $data['disks']);
        }
        if (isset($data['windows_updates']) && is_array($data['windows_updates'])) {
            $this->updateServerWindowsUpdates($result['server_id'], $data['windows_updates']);
        }
        if (isset($data['windows_services']) && is_array($data['windows_services'])) {
            $this->updateServerWindowsServices($result['server_id'], $data['windows_services']);
        }
        if (isset($data['windows_startup']) && is_array($data['windows_startup'])) {
            $this->updateServerWindowsStartup($result['server_id'], $data['windows_startup']);
        }
        if (isset($data['windows_shared']) && is_array($data['windows_shared'])) {
            $this->updateServerWindowsShared($result['server_id'], $data['windows_shared']);
        }
        if (isset($data['windows_mapped']) && is_array($data['windows_mapped'])) {
            $this->updateServerWindowsMapped($result['server_id'], $data['windows_mapped']);
        }
        if (isset($data['windows_users']) && is_array($data['windows_users'])) {
            $this->updateServerWindowsUsers($result['server_id'], $data['windows_users']);
        }
        if (isset($data['windows_user_groups']) && is_array($data['windows_user_groups'])) {
            $this->updateServerWindowsUserGroups($result['server_id'], $data['windows_user_groups']);
        }
        if (isset($data['windows_license'])) {
            $this->updateServerWindowsLicense($result['server_id'], $data['windows_license']);
        }
        return ['server_id' => $result['server_id'], 'created' => $result['created']];
    }

    private function findOrCreateServer(array $data, string $name): array {
        $server = $this->db->fetch("SELECT id FROM servers WHERE hostname = ? OR name = ?", [$name, $name]);

        $osName = $this->normalizeWindowsName(trim($data['os_name'] ?? ''));
        $osVersion = $this->normalizeWindowsVersion(trim($data['os_version'] ?? ''));
        // Correction: "Windows" + version → Windows Server (quand l'agent n'envoie pas le nom complet)
        if (preg_match('/^Windows$/i', $osName) && $osVersion) {
            switch ($osVersion) {
                case '1607': $osName = 'Windows Server 2016'; break;
                case '1809': $osName = 'Windows Server 2019 Standard'; break;
                case '21H2': case '22H2': $osName = 'Windows Server 2022'; break;
                case '24H2': $osName = 'Windows Server 2025'; break;
                default: $osName = 'Windows Server 2019 Standard';
            }
        }
        $osId = $this->getOrCreateOs($osName ?: 'Windows Server', $osVersion);
        $modelId = $this->getOrCreateManufacturerModel($data['manufacturer'] ?? '', $data['model'] ?? '');
        $processorModel = $data['processor'] ?? $data['processor_model'] ?? null;
        $processorCores = isset($data['processor_cores']) && (int)$data['processor_cores'] > 0 ? (int)$data['processor_cores'] : null;
        $processorMhz = isset($data['processor_mhz']) && (float)$data['processor_mhz'] > 0 ? (float)$data['processor_mhz'] : null;
        $processorManufacturer = isset($data['processor_manufacturer']) ? trim($data['processor_manufacturer']) ?: null : null;
        $processorFamily = isset($data['processor_family']) ? trim($data['processor_family']) ?: null : null;
        $ramTotal = isset($data['ram_total_bytes']) ? (int)$data['ram_total_bytes'] : null;
        $ramUsed = isset($data['ram_used_bytes']) ? (int)$data['ram_used_bytes'] : null;
        $siteId = isset($data['site_id']) && (int)$data['site_id'] > 0 ? (int)$data['site_id'] : null;
        $tenantId = isset($data['tenant_id']) && (int)$data['tenant_id'] > 0 ? (int)$data['tenant_id'] : null;
        if ($siteId && !$tenantId) {
            $siteRow = $this->db->fetch("SELECT tenant_id FROM sites WHERE id = ?", [$siteId]);
            if ($siteRow && $siteRow['tenant_id']) {
                $tenantId = (int)$siteRow['tenant_id'];
            }
        }
        $ipAddressId = $this->getOrCreateIpAddress($data['network'] ?? [], $siteId, $tenantId);
        $rustdeskId = trim($data['rustdesk_id'] ?? '') ?: null;
        $avName = isset($data['antivirus_name']) ? trim($data['antivirus_name']) ?: null : null;
        // Fallback: détecter l'antivirus via logiciels installés si WMI n'a rien renvoyé (ex: ESET)
        if (empty($avName) && isset($data['software']) && is_array($data['software'])) {
            $avName = $this->detectAntivirusFromSoftware($data['software']);
        }
        $avEnabled = isset($data['antivirus_enabled']) ? (filter_var($data['antivirus_enabled'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0) : null;
        $avUpdated = isset($data['antivirus_updated']) ? (filter_var($data['antivirus_updated'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0) : null;
        $fwEnabled = isset($data['firewall_enabled']) ? (filter_var($data['firewall_enabled'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0) : null;
        $lastAccount = trim($data['last_account'] ?? '') ?: null;
        $lastAccountCreatedAt = null;
        if (!empty(trim($data['last_account_created_at'] ?? ''))) {
            $ts = strtotime($data['last_account_created_at']);
            $lastAccountCreatedAt = $ts ? gmdate('Y-m-d H:i:s', $ts) : null;
        }
        $vmUuid = !empty(trim($data['vm_uuid'] ?? '')) ? $this->normalizeVmUuid(trim($data['vm_uuid'])) : null;
        $serverType = $this->detectServerType($data['manufacturer'] ?? '', $data['model'] ?? '', $vmUuid);

        if ($server) {
            $this->db->query(
                "UPDATE servers SET name = ?, hostname = ?, type = ?, model_id = ?, processor_model = ?, processor_cores = ?, processor_speed_mhz = ?, processor_manufacturer = ?, processor_family = ?, ram_total = ?, ram_used = ?, operating_system_id = ?, site_id = ?, ip_address_id = ?, rustdesk_id = ?, antivirus_name = ?, antivirus_enabled = ?, antivirus_updated = ?, firewall_enabled = ?, last_account = ?, last_account_created_at = ?, vm_uuid = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                [$name, $name, $serverType, $modelId ?: null, $processorModel, $processorCores, $processorMhz, $processorManufacturer, $processorFamily, $ramTotal, $ramUsed, $osId ?: null, $siteId, $ipAddressId ?: null, $rustdeskId, $avName, $avEnabled, $avUpdated, $fwEnabled, $lastAccount, $lastAccountCreatedAt, $vmUuid, $server['id']]
            );
            return ['server_id' => (int)$server['id'], 'created' => false];
        }

        $this->db->query(
            "INSERT INTO servers (name, hostname, type, model_id, processor_model, processor_cores, processor_speed_mhz, processor_manufacturer, processor_family, ram_total, ram_used, operating_system_id, site_id, ip_address_id, rustdesk_id, antivirus_name, antivirus_enabled, antivirus_updated, firewall_enabled, last_account, last_account_created_at, vm_uuid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$name, $name, $serverType, $modelId ?: null, $processorModel, $processorCores, $processorMhz, $processorManufacturer, $processorFamily, $ramTotal, $ramUsed, $osId ?: null, $siteId, $ipAddressId ?: null, $rustdeskId, $avName, $avEnabled, $avUpdated, $fwEnabled, $lastAccount, $lastAccountCreatedAt, $vmUuid]
        );
        return ['server_id' => (int)$this->db->lastInsertId(), 'created' => true];
    }

    /** Numéros de série génériques (non uniques) — ne pas les utiliser pour identifier un PC */
    private static function isGenericSerial(string $serial): bool {
        $s = strtolower(trim($serial));
        if (empty($s)) return true;
        $generic = [
            'system serial number', 'default string', 'to be filled by o.e.m.', 'to be filled by oem',
            'none', 'n/a', 'na', 'default', 'unknown', '0', 'xxxxxxxx', 'xxxxxxxxxx',
            'chassis serial number', 'serial number', 'default serial'
        ];
        foreach ($generic as $g) {
            if ($s === $g || strpos($s, $g) !== false) return true;
        }
        return strlen($s) < 5; // Trop court pour être unique
    }

    private function findOrCreatePc(array $data, string $name, string $serialNumber): array {
        $pc = null;
        $useSerialForMatch = !empty($serialNumber) && !self::isGenericSerial($serialNumber);

        if ($useSerialForMatch) {
            $pc = $this->db->fetch("SELECT id FROM pcs_laptops WHERE serial_number = ?", [$serialNumber]);
        }
        if (!$pc && !empty($name)) {
            $pc = $this->db->fetch("SELECT id FROM pcs_laptops WHERE name = ?", [$name]);
        }

        $osName = $this->normalizeWindowsName(trim($data['os_name'] ?? ''));
        $osVersion = $this->normalizeWindowsVersion(trim($data['os_version'] ?? ''));
        $osId = $this->getOrCreateOs($osName ?: 'Windows', $osVersion);
        // Protection: ne pas écraser "Windows 11 Pro" par "Windows 11" si l'agent envoie un nom incomplet
        if ($pc && $pc['id'] && in_array($osName, ['Windows 10', 'Windows 11'], true)) {
            $current = $this->db->fetch("SELECT o.id, o.name FROM pcs_laptops p JOIN operating_systems o ON p.operating_system_id = o.id WHERE p.id = ?", [$pc['id']]);
            if ($current && $current['name'] && preg_match('/Pro|Home|Enterprise|Education|Workstation/i', $current['name'])) {
                $osId = (int)$current['id'];
            }
        }
        $modelId = $this->getOrCreateManufacturerModel($data['manufacturer'] ?? '', $data['model'] ?? '');
        $processorModel = $data['processor'] ?? $data['processor_model'] ?? null;
        $processorCores = isset($data['processor_cores']) && (int)$data['processor_cores'] > 0 ? (int)$data['processor_cores'] : null;
        $processorMhz = isset($data['processor_mhz']) && (float)$data['processor_mhz'] > 0 ? (float)$data['processor_mhz'] : null;
        $processorManufacturer = isset($data['processor_manufacturer']) ? trim($data['processor_manufacturer']) ?: null : null;
        $processorFamily = isset($data['processor_family']) ? trim($data['processor_family']) ?: null : null;
        $motherboardSerial = isset($data['motherboard_serial']) ? trim($data['motherboard_serial']) ?: null : null;
        $biosVersion = isset($data['bios_version']) ? trim($data['bios_version']) ?: null : null;
        $ramType = isset($data['ram_type']) ? trim($data['ram_type']) ?: null : null;
        $ramModel = isset($data['ram_model']) ? trim($data['ram_model']) ?: null : null;
        $ramFrequencyMhz = isset($data['ram_frequency_mhz']) && (int)$data['ram_frequency_mhz'] > 0 ? (int)$data['ram_frequency_mhz'] : null;
        $lastAccount = trim($data['last_account'] ?? '') ?: null;
        $lastAccountCreatedAt = null;
        if (!empty(trim($data['last_account_created_at'] ?? ''))) {
            $ts = strtotime($data['last_account_created_at']);
            $lastAccountCreatedAt = $ts ? date('Y-m-d H:i:s', $ts) : null;
        }
        $ramTotal = isset($data['ram_total_bytes']) ? (int)$data['ram_total_bytes'] : null;
        $ramUsed = isset($data['ram_used_bytes']) ? (int)$data['ram_used_bytes'] : null;
        $siteId = isset($data['site_id']) && (int)$data['site_id'] > 0 ? (int)$data['site_id'] : null;
        $tenantId = isset($data['tenant_id']) && (int)$data['tenant_id'] > 0 ? (int)$data['tenant_id'] : null;
        // Dériver le tenant du site si seul site_id est fourni
        if ($siteId && !$tenantId) {
            $siteRow = $this->db->fetch("SELECT tenant_id FROM sites WHERE id = ?", [$siteId]);
            if ($siteRow && $siteRow['tenant_id']) {
                $tenantId = (int)$siteRow['tenant_id'];
            }
        }

        $ipAddressId = $this->getOrCreateIpAddress($data['network'] ?? [], $siteId, $tenantId);
        $rustdeskId = trim($data['rustdesk_id'] ?? '') ?: null;

        $avName = isset($data['antivirus_name']) ? trim($data['antivirus_name']) ?: null : null;
        if (empty($avName) && isset($data['software']) && is_array($data['software'])) {
            $avName = $this->detectAntivirusFromSoftware($data['software']);
        }
        $avEnabled = isset($data['antivirus_enabled']) ? (filter_var($data['antivirus_enabled'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0) : null;
        $avUpdated = isset($data['antivirus_updated']) ? (filter_var($data['antivirus_updated'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0) : null;
        $fwEnabled = isset($data['firewall_enabled']) ? (filter_var($data['firewall_enabled'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0) : null;

        if ($pc) {
            $sql = "UPDATE pcs_laptops SET 
                name = ?, serial_number = ?, processor_model = ?, processor_cores = ?, processor_speed_mhz = ?, processor_manufacturer = ?, processor_family = ?,
                motherboard_serial = ?, bios_version = ?, ram_type = ?, ram_model = ?, ram_frequency_mhz = ?, model_id = ?,
                operating_system_id = ?, ram_total = ?, ram_used = ?, last_account = ?, last_account_created_at = ?,
                antivirus_name = ?, antivirus_enabled = ?, antivirus_updated = ?, firewall_enabled = ?,
                site_id = ?, tenant_id = ?, ip_address_id = ?,
                rustdesk_id = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?";
            $this->db->query($sql, [
                $name, $serialNumber ?: null, $processorModel, $processorCores, $processorMhz, $processorManufacturer, $processorFamily,
                $motherboardSerial, $biosVersion, $ramType, $ramModel, $ramFrequencyMhz, $modelId ?: null,
                $osId ?: null, $ramTotal, $ramUsed, $lastAccount, $lastAccountCreatedAt,
                $avName, $avEnabled, $avUpdated, $fwEnabled,
                $siteId, $tenantId, $ipAddressId ?: null,
                $rustdeskId, $pc['id']
            ]);
            return ['pc_id' => (int)$pc['id'], 'created' => false];
        }

        $sql = "INSERT INTO pcs_laptops (name, serial_number, processor_model, processor_cores, processor_speed_mhz, processor_manufacturer, processor_family, motherboard_serial, bios_version, ram_type, ram_model, ram_frequency_mhz, model_id, operating_system_id, ram_total, ram_used, last_account, last_account_created_at, antivirus_name, antivirus_enabled, antivirus_updated, firewall_enabled, site_id, tenant_id, ip_address_id, rustdesk_id, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'inventoried')";
        $this->db->query($sql, [
            $name, $serialNumber ?: null, $processorModel, $processorCores, $processorMhz, $processorManufacturer, $processorFamily, $motherboardSerial, $biosVersion, $ramType, $ramModel, $ramFrequencyMhz, $modelId ?: null, $osId ?: null, $ramTotal, $ramUsed,
            $lastAccount, $lastAccountCreatedAt, $avName, $avEnabled, $avUpdated, $fwEnabled,
            $siteId, $tenantId, $ipAddressId ?: null, $rustdeskId
        ]);
        return ['pc_id' => (int)$this->db->lastInsertId(), 'created' => true];
    }

    /**
     * Normalise la version Windows: "10.0.26200.7840" → "24H2", "10.0.17763" → "1809" (Server 2019)
     */
    private function normalizeWindowsVersion(string $version): string {
        if (empty($version) || preg_match('/^\d{2}H[12]$/i', $version) || preg_match('/^\d{4}$/', $version)) {
            return $version; // Déjà au bon format (23H2, 1809, 1607...)
        }
        if (preg_match('/10\.0\.(\d{5})/', $version, $m)) {
            $build = (int)$m[1];
            if ($build >= 26200) return '25H2';
            if ($build >= 26100) return '24H2';
            if ($build >= 22631) return '23H2';
            if ($build >= 22621) return '22H2';
            if ($build >= 22000) return '21H2';
            if ($build >= 20348) return '21H2'; // Server 2022
            if ($build >= 19045) return '22H2';
            if ($build >= 19044) return '21H2';
            if ($build >= 19043) return '21H1';
            if ($build >= 19042) return '20H2';
            if ($build >= 18363) return '19H2';
            if ($build >= 17763) return '1809'; // Server 2019 / Win10 1809
            if ($build >= 14393) return '1607'; // Server 2016 / Win10 1607
        }
        if (preg_match('/[Bb]uild\s*(\d{5})/', $version, $m)) {
            return $this->normalizeWindowsVersion('10.0.' . $m[1]);
        }
        return $version;
    }

    private function normalizeWindowsName(string $name): string {
        $name = trim($name);
        if (empty($name) || strtolower($name) === 'windows') {
            return 'Windows'; // Ne pas forcer Windows 11 — les builds anciens sont 10/Server
        }
        return $name;
    }

    /** Normalise l'UUID BIOS (format standard) pour stockage et comparaison avec esxi_vms. */
    private function normalizeVmUuid(string $uuid): string {
        $uuid = strtoupper(str_replace([' ', '-'], '', trim($uuid)));
        if (strlen($uuid) !== 32 || !ctype_xdigit($uuid)) {
            return '';
        }
        return substr($uuid, 0, 8) . '-' . substr($uuid, 8, 4) . '-' . substr($uuid, 12, 4) . '-' . substr($uuid, 16, 4) . '-' . substr($uuid, 20, 12);
    }

    /** Détecte si le serveur est une VM (ESXi, Hyper-V, VirtualBox, Proxmox, etc.) à partir du manufacturer/model ou vm_uuid. */
    private function detectServerType(string $manufacturer, string $model, ?string $vmUuid): string {
        if ($vmUuid !== null && $vmUuid !== '') {
            return 'Virtuel';
        }
        $m = strtolower(trim($manufacturer));
        $mod = strtolower(trim($model));
        $virtualIndicators = [
            'vmware', 'microsoft corporation', 'innotek', 'qemu', 'proxmox', 'xen', 'kvm',
            'virtual machine', 'virtual platform', 'virtualbox', 'hyper-v', 'bochs'
        ];
        foreach ($virtualIndicators as $ind) {
            if (strpos($m, $ind) !== false || strpos($mod, $ind) !== false) {
                return 'Virtuel';
            }
        }
        return 'Physique';
    }

    private function getOrCreateIpAddress(array $network, ?int $siteId, ?int $tenantId): ?int {
        $ip = trim($network['ip_address'] ?? '');
        if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return null;
        }
        $gateway = $this->filterIPv4(trim($network['gateway'] ?? ''));
        $subnetMask = trim($network['subnet_mask'] ?? '') ?: null;
        $dnsServers = $this->filterIPv4List(trim($network['dns_servers'] ?? ''));

        $existing = $this->db->fetch("SELECT id FROM ip_addresses WHERE ip_address = ?", [$ip]);
        if ($existing) {
            $this->db->query(
                "UPDATE ip_addresses SET gateway = COALESCE(?, gateway), subnet_mask = COALESCE(?, subnet_mask), dns_servers = COALESCE(?, dns_servers), site_id = COALESCE(?, site_id), tenant_id = COALESCE(?, tenant_id), updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                [$gateway, $subnetMask, $dnsServers, $siteId, $tenantId, $existing['id']]
            );
            return (int)$existing['id'];
        }

        $this->db->query(
            "INSERT INTO ip_addresses (ip_address, gateway, subnet_mask, dns_servers, site_id, tenant_id) VALUES (?, ?, ?, ?, ?, ?)",
            [$ip, $gateway, $subnetMask, $dnsServers, $siteId, $tenantId]
        );
        return (int)$this->db->lastInsertId();
    }

    private function filterIPv4(string $ip): ?string {
        $ip = trim($ip);
        if ($ip === '' || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return null;
        }
        return $ip;
    }

    private function filterIPv4List(string $list): ?string {
        $addrs = preg_split('/[\s,]+/', $list, -1, PREG_SPLIT_NO_EMPTY);
        $v4 = [];
        foreach ($addrs as $addr) {
            if (filter_var(trim($addr), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                $v4[] = trim($addr);
            }
        }
        return $v4 ? implode(', ', $v4) : null;
    }

    private function getOrCreateManufacturerModel(string $manufacturer, string $model): ?int {
        $manufacturer = trim($manufacturer);
        $model = trim($model);
        if (empty($manufacturer) && empty($model)) {
            return null;
        }
        if (empty($model)) {
            $model = 'Inconnu';
        }
        if (empty($manufacturer)) {
            $manufacturer = 'Inconnu';
        }

        $manufacturerId = null;
        $existingMf = $this->db->fetch("SELECT id FROM manufacturers WHERE name = ?", [$manufacturer]);
        if ($existingMf) {
            $manufacturerId = (int)$existingMf['id'];
        } else {
            $this->db->query("INSERT INTO manufacturers (name) VALUES (?)", [$manufacturer]);
            $manufacturerId = (int)$this->db->lastInsertId();
        }

        $existingModel = $this->db->fetch(
            "SELECT id FROM models WHERE name = ? AND manufacturer_id = ?",
            [$model, $manufacturerId]
        );
        if ($existingModel) {
            return (int)$existingModel['id'];
        }
        $this->db->query("INSERT INTO models (name, manufacturer_id) VALUES (?, ?)", [$model, $manufacturerId]);
        return (int)$this->db->lastInsertId();
    }

    private function getOrCreateOs(string $name, string $version): ?int {
        $name = trim($name) ?: 'Windows';
        $version = trim($version);

        $existing = $this->db->fetch(
            "SELECT id FROM operating_systems WHERE name = ? AND (version = ? OR (? = '' AND version IS NULL))",
            [$name, $version ?: null, $version]
        );
        if ($existing) {
            return (int)$existing['id'];
        }

        $this->db->query("INSERT INTO operating_systems (name, version) VALUES (?, ?)", [$name, $version ?: null]);
        return (int)$this->db->lastInsertId();
    }

    private function updateServerDisks(int $serverId, array $disks): void {
        $this->db->query("DELETE FROM physical_disks WHERE server_id = ?", [$serverId]);

        foreach ($disks as $disk) {
            $model = $disk['model'] ?? null;
            $serial = $disk['serial_number'] ?? null;
            $interfaceType = $disk['interface_type'] ?? $disk['interface'] ?? null;
            $sizeBytes = isset($disk['size_bytes']) ? (int)$disk['size_bytes'] : null;

            $this->db->query(
                "INSERT INTO physical_disks (server_id, model, serial_number, interface_type, size_bytes) VALUES (?, ?, ?, ?, ?)",
                [$serverId, $model, $serial, $interfaceType, $sizeBytes]
            );
            $physicalDiskId = (int)$this->db->lastInsertId();

            foreach ($disk['partitions'] ?? [] as $part) {
                $this->db->query(
                    "INSERT INTO disk_partitions (physical_disk_id, drive_letter, label, file_system, total_size_bytes, free_space_bytes) VALUES (?, ?, ?, ?, ?, ?)",
                    [
                        $physicalDiskId,
                        $part['drive_letter'] ?? null,
                        $part['label'] ?? null,
                        $part['file_system'] ?? null,
                        isset($part['total_size_bytes']) ? (int)$part['total_size_bytes'] : null,
                        isset($part['free_space_bytes']) ? (int)$part['free_space_bytes'] : null
                    ]
                );
            }
        }
    }

    private function updateDisks(int $pcId, array $disks): void {
        $this->db->query("DELETE FROM physical_disks WHERE pc_id = ?", [$pcId]);

        foreach ($disks as $disk) {
            $model = $disk['model'] ?? null;
            $serial = $disk['serial_number'] ?? null;
            $interfaceType = $disk['interface_type'] ?? $disk['interface'] ?? null;
            $sizeBytes = isset($disk['size_bytes']) ? (int)$disk['size_bytes'] : null;

            $this->db->query(
                "INSERT INTO physical_disks (pc_id, model, serial_number, interface_type, size_bytes) VALUES (?, ?, ?, ?, ?)",
                [$pcId, $model, $serial, $interfaceType, $sizeBytes]
            );
            $physicalDiskId = (int)$this->db->lastInsertId();

            foreach ($disk['partitions'] ?? [] as $part) {
                $this->db->query(
                    "INSERT INTO disk_partitions (physical_disk_id, drive_letter, label, file_system, total_size_bytes, free_space_bytes) VALUES (?, ?, ?, ?, ?, ?)",
                    [
                        $physicalDiskId,
                        $part['drive_letter'] ?? null,
                        $part['label'] ?? null,
                        $part['file_system'] ?? null,
                        isset($part['total_size_bytes']) ? (int)$part['total_size_bytes'] : null,
                        isset($part['free_space_bytes']) ? (int)$part['free_space_bytes'] : null
                    ]
                );
            }
        }
    }

    /**
     * Normalise une date d'installation vers YYYY-MM-DD ou null si format invalide.
     * Évite les erreurs SQL avec dates mal formées (ex: "29.1-2.-20" depuis le registre Windows).
     */
    private function normalizeInstallDate(?string $date): ?string {
        $date = trim($date ?? '');
        if ($date === '') return null;
        // Déjà au bon format MySQL
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return $date;
        // YYYYMMDD (ex: 20200129)
        if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $date, $m)) return $m[1] . '-' . $m[2] . '-' . $m[3];
        // DD/MM/YYYY ou DD.MM.YYYY (format européen courant)
        if (preg_match('/^(\d{1,2})[.\/](\d{1,2})[.\/](\d{4})$/', $date, $m)) return $m[3] . '-' . str_pad($m[2], 2, '0') . '-' . str_pad($m[1], 2, '0');
        return null;
    }

    /**
     * Détecte un antivirus à partir de la liste des logiciels installés (fallback si WMI ne renvoie rien).
     * Utile pour ESET, etc. qui peuvent ne pas toujours apparaître via SecurityCenter2.
     */
    private function detectAntivirusFromSoftware(array $softwareList): ?string {
        $patterns = ['ESET', 'Kaspersky', 'Norton', 'McAfee', 'Avast', 'AVG', 'Bitdefender', 'Malwarebytes', 'Trend Micro', 'Sophos', 'CrowdStrike', 'Microsoft Defender'];
        foreach ($softwareList as $sw) {
            $name = trim($sw['name'] ?? '');
            if (empty($name)) continue;
            foreach ($patterns as $p) {
                if (stripos($name, $p) !== false) {
                    return $name; // Retourner le nom exact du produit (ex: "ESET File Security")
                }
            }
        }
        return null;
    }

    private function updateServerSoftware(int $serverId, array $softwareList): void {
        $this->db->query("DELETE FROM server_installed_software WHERE server_id = ?", [$serverId]);
        foreach ($softwareList as $sw) {
            $name = trim($sw['name'] ?? '');
            $version = trim($sw['version'] ?? '');
            if (empty($name)) continue;
            $software = $this->db->fetch("SELECT id FROM software WHERE name = ? AND (version <=> ? OR (version IS NULL AND ? = ''))", [$name, $version ?: null, $version]);
            if (!$software) {
                $this->db->query("INSERT INTO software (name, version) VALUES (?, ?)", [$name, $version ?: null]);
                $softwareId = (int)$this->db->lastInsertId();
            } else {
                $softwareId = (int)$software['id'];
            }
            $installDate = $this->normalizeInstallDate($sw['install_date'] ?? '');
            $this->db->query(
                "INSERT INTO server_installed_software (server_id, software_id, installation_date) VALUES (?, ?, ?)",
                [$serverId, $softwareId, $installDate]
            );
        }
    }

    private function updateSoftware(int $pcId, array $softwareList): void {
        $this->db->query("DELETE FROM installed_software WHERE pc_id = ?", [$pcId]);

        foreach ($softwareList as $sw) {
            $name = trim($sw['name'] ?? '');
            $version = trim($sw['version'] ?? '');

            if (empty($name)) continue;

            $software = $this->db->fetch("SELECT id FROM software WHERE name = ? AND (version <=> ? OR (version IS NULL AND ? = ''))", 
                [$name, $version ?: null, $version]);
            if (!$software) {
                $this->db->query("INSERT INTO software (name, version) VALUES (?, ?)", [$name, $version ?: null]);
                $softwareId = (int)$this->db->lastInsertId();
            } else {
                $softwareId = (int)$software['id'];
            }

            $installDate = $this->normalizeInstallDate($sw['install_date'] ?? '');
            $this->db->query(
                "INSERT INTO installed_software (pc_id, software_id, installation_date) VALUES (?, ?, ?)",
                [$pcId, $softwareId, $installDate]
            );
        }
    }

    private function updateGpus(int $pcId, array $gpus): void {
        $this->db->query("DELETE FROM pc_gpus WHERE pc_id = ?", [$pcId]);
        foreach ($gpus as $g) {
            $model = isset($g['model']) ? trim($g['model']) : null;
            if ($model === '') $model = null;
            $vendor = isset($g['vendor']) ? trim($g['vendor']) : null;
            $driverVersion = isset($g['driver_version']) ? trim($g['driver_version']) : null;
            $vramBytes = isset($g['vram_bytes']) ? (int)$g['vram_bytes'] : null;
            $videoProcessor = isset($g['video_processor']) ? trim($g['video_processor']) : null;
            $this->db->query(
                "INSERT INTO pc_gpus (pc_id, model, vendor, driver_version, vram_bytes, video_processor) VALUES (?, ?, ?, ?, ?, ?)",
                [$pcId, $model, $vendor, $driverVersion, $vramBytes, $videoProcessor]
            );
        }
    }

    private function updateMonitors(int $pcId, array $monitors): void {
        $this->db->query("DELETE FROM pc_monitors WHERE pc_id = ?", [$pcId]);
        foreach ($monitors as $m) {
            $name = isset($m['name']) ? trim($m['name']) : null;
            if ($name === '') $name = null;
            $manufacturer = isset($m['manufacturer']) ? trim($m['manufacturer']) : null;
            $serialNumber = isset($m['serial_number']) ? trim($m['serial_number']) : null;
            $resolution = isset($m['resolution']) ? trim($m['resolution']) : null;
            $this->db->query(
                "INSERT INTO pc_monitors (pc_id, name, manufacturer, serial_number, resolution) VALUES (?, ?, ?, ?, ?)",
                [$pcId, $name, $manufacturer, $serialNumber, $resolution]
            );
        }
    }

    private function updatePrinters(int $pcId, array $printers): void {
        $this->db->query("DELETE FROM pc_printers WHERE pc_id = ?", [$pcId]);
        foreach ($printers as $p) {
            $name = trim($p['name'] ?? '');
            if ($name === '') continue;
            $driver = isset($p['driver']) ? trim($p['driver']) : null;
            $port = isset($p['port']) ? trim($p['port']) : null;
            $isDefault = !empty($p['default']) ? 1 : 0;
            $isShared = !empty($p['shared']) ? 1 : 0;
            $this->db->query(
                "INSERT INTO pc_printers (pc_id, name, driver, port, is_default, is_shared) VALUES (?, ?, ?, ?, ?, ?)",
                [$pcId, $name, $driver, $port, $isDefault, $isShared]
            );
        }
    }

    private function updateNetworkAdapters(int $pcId, array $adapters): void {
        $this->db->query("DELETE FROM pc_network_adapters WHERE pc_id = ?", [$pcId]);
        foreach ($adapters as $a) {
            $name = isset($a['name']) ? trim($a['name']) : null;
            $type = isset($a['type']) ? trim($a['type']) : null;
            $ipCidr = isset($a['ip_cidr']) ? trim($a['ip_cidr']) : null;
            $gateway = isset($a['gateway']) ? trim($a['gateway']) : null;
            $wifiSsid = isset($a['wifi_ssid']) ? trim($a['wifi_ssid']) : null;
            if ($ipCidr === '' && $name === '') continue;
            $this->db->query(
                "INSERT INTO pc_network_adapters (pc_id, name, type, ip_cidr, gateway, wifi_ssid) VALUES (?, ?, ?, ?, ?, ?)",
                [$pcId, $name, $type, $ipCidr, $gateway, $wifiSsid]
            );
        }
    }

    private function updateWindowsUpdates(int $pcId, array $updates): void {
        $this->db->query("DELETE FROM pc_windows_updates WHERE pc_id = ?", [$pcId]);
        foreach ($updates as $u) {
            $hotfixId = trim($u['hotfix_id'] ?? '');
            if ($hotfixId === '') continue;
            $description = isset($u['description']) ? trim($u['description']) : null;
            $installedOn = null;
            if (!empty(trim($u['installed_on'] ?? ''))) {
                $ts = strtotime($u['installed_on']);
                $installedOn = $ts ? date('Y-m-d', $ts) : null;
            }
            $this->db->query(
                "INSERT INTO pc_windows_updates (pc_id, hotfix_id, description, installed_on) VALUES (?, ?, ?, ?)",
                [$pcId, $hotfixId, $description, $installedOn]
            );
        }
    }

    private function updateWindowsServices(int $pcId, array $services): void {
        $this->db->query("DELETE FROM pc_windows_services WHERE pc_id = ?", [$pcId]);
        foreach ($services as $s) {
            $name = trim($s['name'] ?? '');
            if ($name === '') continue;
            $displayName = isset($s['display_name']) ? trim($s['display_name']) : null;
            $description = isset($s['description']) ? trim($s['description']) : null;
            $status = isset($s['status']) ? trim($s['status']) : null;
            $startType = isset($s['start_type']) ? trim($s['start_type']) : null;
            $this->db->query(
                "INSERT INTO pc_windows_services (pc_id, name, display_name, description, status, start_type) VALUES (?, ?, ?, ?, ?, ?)",
                [$pcId, $name, $displayName, $description, $status, $startType]
            );
        }
    }

    private function updateWindowsStartup(int $pcId, array $startup): void {
        $this->db->query("DELETE FROM pc_windows_startup WHERE pc_id = ?", [$pcId]);
        foreach ($startup as $s) {
            $name = isset($s['name']) ? trim($s['name']) : null;
            $command = isset($s['command']) ? trim($s['command']) : null;
            $location = isset($s['location']) ? trim($s['location']) : null;
            $this->db->query(
                "INSERT INTO pc_windows_startup (pc_id, name, command, location) VALUES (?, ?, ?, ?)",
                [$pcId, $name, $command, $location]
            );
        }
    }

    private function updateWindowsShared(int $pcId, array $shared): void {
        $this->db->query("DELETE FROM pc_windows_shared WHERE pc_id = ?", [$pcId]);
        foreach ($shared as $s) {
            $name = trim($s['name'] ?? '');
            if ($name === '') continue;
            $path = isset($s['path']) ? trim($s['path']) : null;
            $description = isset($s['description']) ? trim($s['description']) : null;
            $this->db->query(
                "INSERT INTO pc_windows_shared (pc_id, name, path, description) VALUES (?, ?, ?, ?)",
                [$pcId, $name, $path, $description]
            );
        }
    }

    private function updateWindowsMapped(int $pcId, array $mapped): void {
        $this->db->query("DELETE FROM pc_windows_mapped WHERE pc_id = ?", [$pcId]);
        foreach ($mapped as $m) {
            $driveLetter = isset($m['drive_letter']) ? trim($m['drive_letter']) : null;
            $path = isset($m['path']) ? trim($m['path']) : null;
            $label = isset($m['label']) ? trim($m['label']) : null;
            $this->db->query(
                "INSERT INTO pc_windows_mapped (pc_id, drive_letter, path, label) VALUES (?, ?, ?, ?)",
                [$pcId, $driveLetter, $path, $label]
            );
        }
    }

    private function updateWindowsUsers(int $pcId, array $users): void {
        $this->db->query("DELETE FROM pc_windows_users WHERE pc_id = ?", [$pcId]);
        foreach ($users as $u) {
            $username = trim($u['username'] ?? '');
            if ($username === '') continue;
            $fullName = isset($u['full_name']) ? trim($u['full_name']) : null;
            $lastLogin = null;
            if (!empty(trim($u['last_login'] ?? ''))) {
                $ts = strtotime($u['last_login']);
                $lastLogin = $ts ? date('Y-m-d H:i:s', $ts) : null;
            }
            $accountType = isset($u['account_type']) ? trim($u['account_type']) : null;
            $this->db->query(
                "INSERT INTO pc_windows_users (pc_id, username, full_name, last_login, account_type) VALUES (?, ?, ?, ?, ?)",
                [$pcId, $username, $fullName, $lastLogin, $accountType]
            );
        }
    }

    private function updateWindowsUserGroups(int $pcId, array $groups): void {
        $this->db->query("DELETE FROM pc_windows_user_groups WHERE pc_id = ?", [$pcId]);
        foreach ($groups as $g) {
            $groupName = trim((string)$g);
            if ($groupName === '') continue;
            $this->db->query(
                "INSERT INTO pc_windows_user_groups (pc_id, group_name) VALUES (?, ?)",
                [$pcId, $groupName]
            );
        }
    }

    private function updateWindowsLicense(int $pcId, $license): void {
        if (!is_array($license)) {
            return;
        }
        $description = isset($license['description']) ? trim($license['description']) : null;
        $status = isset($license['status']) ? trim($license['status']) : null;

        $existing = $this->db->fetch("SELECT id FROM pc_windows_license WHERE pc_id = ?", [$pcId]);
        if ($existing) {
            $this->db->query("UPDATE pc_windows_license SET description = ?, status = ? WHERE pc_id = ?", [$description, $status, $pcId]);
        } else {
            $this->db->query("INSERT INTO pc_windows_license (pc_id, description, status) VALUES (?, ?, ?)", [$pcId, $description, $status]);
        }
    }

    private function updateServerWindowsUpdates(int $serverId, array $updates): void {
        $this->db->query("DELETE FROM server_windows_updates WHERE server_id = ?", [$serverId]);
        foreach ($updates as $u) {
            $hotfixId = trim($u['hotfix_id'] ?? '');
            if ($hotfixId === '') continue;
            $description = isset($u['description']) ? trim($u['description']) : null;
            $installedOn = null;
            if (!empty(trim($u['installed_on'] ?? ''))) {
                $ts = strtotime($u['installed_on']);
                $installedOn = $ts ? date('Y-m-d', $ts) : null;
            }
            $this->db->query(
                "INSERT INTO server_windows_updates (server_id, hotfix_id, description, installed_on) VALUES (?, ?, ?, ?)",
                [$serverId, $hotfixId, $description, $installedOn]
            );
        }
    }

    private function updateServerWindowsServices(int $serverId, array $services): void {
        $this->db->query("DELETE FROM server_windows_services WHERE server_id = ?", [$serverId]);
        foreach ($services as $s) {
            $name = trim($s['name'] ?? '');
            if ($name === '') continue;
            $displayName = isset($s['display_name']) ? trim($s['display_name']) : null;
            $description = isset($s['description']) ? trim($s['description']) : null;
            $status = isset($s['status']) ? trim($s['status']) : null;
            $startType = isset($s['start_type']) ? trim($s['start_type']) : null;
            $this->db->query(
                "INSERT INTO server_windows_services (server_id, name, display_name, description, status, start_type) VALUES (?, ?, ?, ?, ?, ?)",
                [$serverId, $name, $displayName, $description, $status, $startType]
            );
        }
    }

    private function updateServerWindowsStartup(int $serverId, array $startup): void {
        $this->db->query("DELETE FROM server_windows_startup WHERE server_id = ?", [$serverId]);
        foreach ($startup as $s) {
            $name = isset($s['name']) ? trim($s['name']) : null;
            $command = isset($s['command']) ? trim($s['command']) : null;
            $location = isset($s['location']) ? trim($s['location']) : null;
            $this->db->query(
                "INSERT INTO server_windows_startup (server_id, name, command, location) VALUES (?, ?, ?, ?)",
                [$serverId, $name, $command, $location]
            );
        }
    }

    private function updateServerWindowsShared(int $serverId, array $shared): void {
        $this->db->query("DELETE FROM server_windows_shared WHERE server_id = ?", [$serverId]);
        foreach ($shared as $s) {
            $name = trim($s['name'] ?? '');
            if ($name === '') continue;
            $path = isset($s['path']) ? trim($s['path']) : null;
            $description = isset($s['description']) ? trim($s['description']) : null;
            $this->db->query(
                "INSERT INTO server_windows_shared (server_id, name, path, description) VALUES (?, ?, ?, ?)",
                [$serverId, $name, $path, $description]
            );
        }
    }

    private function updateServerWindowsMapped(int $serverId, array $mapped): void {
        $this->db->query("DELETE FROM server_windows_mapped WHERE server_id = ?", [$serverId]);
        foreach ($mapped as $m) {
            $driveLetter = isset($m['drive_letter']) ? trim($m['drive_letter']) : null;
            $path = isset($m['path']) ? trim($m['path']) : null;
            $label = isset($m['label']) ? trim($m['label']) : null;
            $this->db->query(
                "INSERT INTO server_windows_mapped (server_id, drive_letter, path, label) VALUES (?, ?, ?, ?)",
                [$serverId, $driveLetter, $path, $label]
            );
        }
    }

    private function updateServerWindowsUsers(int $serverId, array $users): void {
        $this->db->query("DELETE FROM server_windows_users WHERE server_id = ?", [$serverId]);
        foreach ($users as $u) {
            $username = trim($u['username'] ?? '');
            if ($username === '') continue;
            $fullName = isset($u['full_name']) ? trim($u['full_name']) : null;
            $lastLogin = null;
            if (!empty(trim($u['last_login'] ?? ''))) {
                $ts = strtotime($u['last_login']);
                $lastLogin = $ts ? date('Y-m-d H:i:s', $ts) : null;
            }
            $accountType = isset($u['account_type']) ? trim($u['account_type']) : null;
            $this->db->query(
                "INSERT INTO server_windows_users (server_id, username, full_name, last_login, account_type) VALUES (?, ?, ?, ?, ?)",
                [$serverId, $username, $fullName, $lastLogin, $accountType]
            );
        }
    }

    private function updateServerWindowsUserGroups(int $serverId, array $groups): void {
        $this->db->query("DELETE FROM server_windows_user_groups WHERE server_id = ?", [$serverId]);
        foreach ($groups as $g) {
            $groupName = trim((string)$g);
            if ($groupName === '') continue;
            $this->db->query(
                "INSERT INTO server_windows_user_groups (server_id, group_name) VALUES (?, ?)",
                [$serverId, $groupName]
            );
        }
    }

    private function updateServerWindowsLicense(int $serverId, $license): void {
        if (!is_array($license)) {
            return;
        }
        $description = isset($license['description']) ? trim($license['description']) : null;
        $status = isset($license['status']) ? trim($license['status']) : null;

        $existing = $this->db->fetch("SELECT id FROM server_windows_license WHERE server_id = ?", [$serverId]);
        if ($existing) {
            $this->db->query("UPDATE server_windows_license SET description = ?, status = ? WHERE server_id = ?", [$description, $status, $serverId]);
        } else {
            $this->db->query("INSERT INTO server_windows_license (server_id, description, status) VALUES (?, ?, ?)", [$serverId, $description, $status]);
        }
    }
}
