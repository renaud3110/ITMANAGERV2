<?php
/**
 * API télémétrie moniteur - Réception des températures CPU/GPU et statut en ligne
 * POST avec JSON + header X-Api-Key (même clé que l'inventaire)
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

if (!file_exists(__DIR__ . '/../config/api_config.php')) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Configuration manquante']);
    exit;
}

require_once __DIR__ . '/../config/api_config.php';
require_once __DIR__ . '/../config/Database.php';

$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if (empty($apiKey) || $apiKey !== API_INVENTORY_KEY) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Clé API invalide']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    error_log("Monitor API: JSON invalide ou absent");
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'JSON invalide']);
    exit;
}

$hostname = trim($data['hostname'] ?? $data['name'] ?? '');
$serialNumber = trim($data['serial_number'] ?? '');
$siteId = isset($data['site_id']) ? (int)$data['site_id'] : null;
$deviceType = strtolower(trim($data['device_type'] ?? ''));
$cpuTemp = isset($data['cpu_temp']) ? (float)$data['cpu_temp'] : null;
$gpuTemp = isset($data['gpu_temp']) ? (float)$data['gpu_temp'] : null;
$loggedIn = isset($data['logged_in']) ? (bool)$data['logged_in'] : null;
$loggedInUser = trim($data['logged_in_user'] ?? '') ?: null;

if (empty($hostname) && empty($serialNumber)) {
    error_log("Monitor API: hostname et serial_number vides");
    echo json_encode(['success' => false, 'error' => 'hostname ou serial_number requis']);
    exit;
}

// Numéros de série génériques (non uniques) — ne pas les utiliser pour identifier
function isGenericSerial(string $s): bool {
    $s = strtolower(trim($s));
    if (empty($s)) return true;
    $generic = ['system serial number', 'default string', 'to be filled by o.e.m.', 'none', 'n/a', 'default', 'unknown', '0', 'xxxxxxxx'];
    foreach ($generic as $g) {
        if ($s === $g || strpos($s, $g) !== false) return true;
    }
    return strlen($s) < 5;
}

try {
    $db = new Database();

    // Trouver le PC : hostname prioritaire si serial générique (évite collision entre machines)
    $pc = null;
    $server = null;
    $useSerialForMatch = !empty($serialNumber) && !isGenericSerial($serialNumber);
    if ($useSerialForMatch) {
        $pc = $db->fetch("SELECT id FROM pcs_laptops WHERE serial_number = ?", [$serialNumber]);
        if (!$pc) {
            $pc = $db->fetch("SELECT id FROM pcs_laptops WHERE LOWER(TRIM(serial_number)) = LOWER(?)", [trim($serialNumber)]);
        }
    }
    if (!$pc && !empty($hostname)) {
        $pc = $db->fetch("SELECT id FROM pcs_laptops WHERE name = ?", [$hostname]);
        if (!$pc) {
            $pc = $db->fetch("SELECT id FROM pcs_laptops WHERE LOWER(TRIM(name)) = LOWER(?)", [trim($hostname)]);
        }
    }
    // Si pas de PC trouvé, chercher un serveur (hostname)
    if (!$pc && !empty($hostname)) {
        $server = $db->fetch("SELECT id FROM servers WHERE hostname = ? OR name = ?", [$hostname, $hostname]);
        if (!$server) {
            $server = $db->fetch("SELECT id FROM servers WHERE LOWER(TRIM(hostname)) = LOWER(?) OR LOWER(TRIM(name)) = LOWER(?)", [trim($hostname), trim($hostname)]);
        }
        // Fallback : hostname court (ad04) si l'agent envoie FQDN (ad04.domain.local)
        if (!$server && strpos($hostname, '.') !== false) {
            $shortName = explode('.', trim($hostname))[0];
            if (!empty($shortName)) {
                $server = $db->fetch("SELECT id FROM servers WHERE LOWER(TRIM(hostname)) = LOWER(?) OR LOWER(TRIM(name)) = LOWER(?)", [$shortName, $shortName]);
            }
        }
    }
    // Si PC et serveur non trouvés : création auto du serveur si device_type=server + hostname + site_id
    if (!$pc && !$server) {
        if ($deviceType === 'server' && !empty($hostname) && $siteId > 0) {
            $name = trim($hostname);
            $db->query(
                "INSERT INTO servers (name, hostname, type, site_id) VALUES (?, ?, 'Physique', ?)",
                [$name, $name, $siteId]
            );
            $serverId = (int)$db->lastInsertId();
            if ($serverId > 0) {
                $server = ['id' => $serverId];
                error_log("Monitor API: serveur créé auto — hostname=" . json_encode($hostname) . " site_id=$siteId");
            }
        }
        if (!$pc && !$server) {
            $samples = $db->fetchAll("SELECT id, name, serial_number FROM pcs_laptops LIMIT 3");
            error_log("Monitor API: PC/Serveur non trouvé — hostname=" . json_encode($hostname) . " serial=" . json_encode($serialNumber) . " | Exemples: " . json_encode($samples));
            echo json_encode([
                'success' => false,
                'error' => 'PC ou Serveur non trouvé (hostname/serial inconnu)',
                'debug' => 'Vérifiez que le nom dans IT Manager correspond au hostname de cette machine'
            ]);
            exit;
        }
    }

    $pcId = ($pc && isset($pc['id'])) ? (int)$pc['id'] : null;
    $serverId = ($server && isset($server['id'])) ? (int)$server['id'] : null;
    $now = gmdate('Y-m-d H:i:s'); // UTC

    if ($pcId > 0) {
        // ========== PC ==========
        $tableExists = $db->fetch("SHOW TABLES LIKE 'pc_monitor_status'");
        if (!$tableExists) {
            $db->query("CREATE TABLE IF NOT EXISTS pc_monitor_status (
                id INT AUTO_INCREMENT PRIMARY KEY,
                pc_id INT NOT NULL,
                cpu_temp DECIMAL(5,2) NULL,
                gpu_temp DECIMAL(5,2) NULL,
                last_seen DATETIME NOT NULL,
                UNIQUE KEY uk_pc_monitor_pc (pc_id),
                CONSTRAINT fk_monitor_pc FOREIGN KEY (pc_id) REFERENCES pcs_laptops(id) ON DELETE CASCADE
            )");
        }
        $lastLogoutAt = (!$loggedIn && $loggedIn !== null) ? $now : null;
        $logCols = $db->fetch("SHOW COLUMNS FROM pc_monitor_status LIKE 'logged_in'");
        $logUserCols = $db->fetch("SHOW COLUMNS FROM pc_monitor_status LIKE 'logged_in_username'");
        if ($logCols && $loggedIn !== null) {
            if ($logUserCols) {
                $db->query(
                    "INSERT INTO pc_monitor_status (pc_id, cpu_temp, gpu_temp, last_seen, logged_in, last_logout_at, logged_in_username)
                     VALUES (?, ?, ?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE cpu_temp = VALUES(cpu_temp), gpu_temp = VALUES(gpu_temp), last_seen = VALUES(last_seen),
                       logged_in = VALUES(logged_in), last_logout_at = IF(VALUES(logged_in)=0, VALUES(last_logout_at), NULL),
                       logged_in_username = VALUES(logged_in_username)",
                    [$pcId, $cpuTemp ?: null, $gpuTemp ?: null, $now, $loggedIn ? 1 : 0, $lastLogoutAt, $loggedInUser]
                );
            } else {
                $db->query(
                    "INSERT INTO pc_monitor_status (pc_id, cpu_temp, gpu_temp, last_seen, logged_in, last_logout_at)
                     VALUES (?, ?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE cpu_temp = VALUES(cpu_temp), gpu_temp = VALUES(gpu_temp), last_seen = VALUES(last_seen),
                       logged_in = VALUES(logged_in), last_logout_at = IF(VALUES(logged_in)=0, VALUES(last_logout_at), NULL)",
                    [$pcId, $cpuTemp ?: null, $gpuTemp ?: null, $now, $loggedIn ? 1 : 0, $lastLogoutAt]
                );
            }
        } else {
            $db->query(
                "INSERT INTO pc_monitor_status (pc_id, cpu_temp, gpu_temp, last_seen)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE cpu_temp = VALUES(cpu_temp), gpu_temp = VALUES(gpu_temp), last_seen = VALUES(last_seen)",
                [$pcId, $cpuTemp ?: null, $gpuTemp ?: null, $now]
            );
        }
        $historyExists = $db->fetch("SHOW TABLES LIKE 'pc_monitor_history'");
        if (!$historyExists) {
            $db->query("CREATE TABLE IF NOT EXISTS pc_monitor_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                pc_id INT NOT NULL,
                cpu_temp DECIMAL(5,2) NULL,
                gpu_temp DECIMAL(5,2) NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                KEY idx_monitor_history_pc (pc_id),
                KEY idx_monitor_history_created (created_at),
                CONSTRAINT fk_monitor_history_pc FOREIGN KEY (pc_id) REFERENCES pcs_laptops(id) ON DELETE CASCADE
            )");
        }
        $db->query(
            "INSERT INTO pc_monitor_history (pc_id, cpu_temp, gpu_temp, created_at) VALUES (?, ?, ?, ?)",
            [$pcId, $cpuTemp ?: null, $gpuTemp ?: null, $now]
        );
        error_log("Monitor API: OK pc_id=$pcId hostname=" . json_encode($hostname) . " cpu=" . ($cpuTemp ?? 'null') . " gpu=" . ($gpuTemp ?? 'null'));
        echo json_encode(['success' => true, 'pc_id' => $pcId]);
    } elseif ($serverId > 0) {
        // ========== SERVEUR ==========
        $tbl = $db->fetch("SHOW TABLES LIKE 'server_monitor_status'");
        if (!$tbl) {
            $db->query("CREATE TABLE IF NOT EXISTS server_monitor_status (
                id INT AUTO_INCREMENT PRIMARY KEY,
                server_id INT NOT NULL,
                cpu_temp DECIMAL(5,2) NULL,
                gpu_temp DECIMAL(5,2) NULL,
                last_seen DATETIME NOT NULL,
                logged_in TINYINT(1) NULL,
                last_logout_at DATETIME NULL,
                UNIQUE KEY uk_server_monitor (server_id),
                CONSTRAINT fk_server_monitor_status FOREIGN KEY (server_id) REFERENCES servers(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }
        $srvLogCols = $db->fetch("SHOW COLUMNS FROM server_monitor_status LIKE 'logged_in'");
        $srvLogUserCols = $db->fetch("SHOW COLUMNS FROM server_monitor_status LIKE 'logged_in_username'");
        $lastLogoutAt = (!$loggedIn && $loggedIn !== null) ? $now : null;
        if ($srvLogCols && $loggedIn !== null) {
            if ($srvLogUserCols) {
                $db->query(
                    "INSERT INTO server_monitor_status (server_id, cpu_temp, gpu_temp, last_seen, logged_in, last_logout_at, logged_in_username)
                     VALUES (?, ?, ?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE cpu_temp = VALUES(cpu_temp), gpu_temp = VALUES(gpu_temp), last_seen = VALUES(last_seen),
                       logged_in = VALUES(logged_in), last_logout_at = IF(VALUES(logged_in)=0, VALUES(last_logout_at), NULL),
                       logged_in_username = VALUES(logged_in_username)",
                    [$serverId, $cpuTemp ?: null, $gpuTemp ?: null, $now, $loggedIn ? 1 : 0, $lastLogoutAt, $loggedInUser]
                );
            } else {
                $db->query(
                    "INSERT INTO server_monitor_status (server_id, cpu_temp, gpu_temp, last_seen, logged_in, last_logout_at)
                     VALUES (?, ?, ?, ?, ?, ?)
                     ON DUPLICATE KEY UPDATE cpu_temp = VALUES(cpu_temp), gpu_temp = VALUES(gpu_temp), last_seen = VALUES(last_seen),
                       logged_in = VALUES(logged_in), last_logout_at = IF(VALUES(logged_in)=0, VALUES(last_logout_at), NULL)",
                    [$serverId, $cpuTemp ?: null, $gpuTemp ?: null, $now, $loggedIn ? 1 : 0, $lastLogoutAt]
                );
            }
        } else {
            $db->query(
                "INSERT INTO server_monitor_status (server_id, cpu_temp, gpu_temp, last_seen)
                 VALUES (?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE cpu_temp = VALUES(cpu_temp), gpu_temp = VALUES(gpu_temp), last_seen = VALUES(last_seen)",
                [$serverId, $cpuTemp ?: null, $gpuTemp ?: null, $now]
            );
        }
        error_log("Monitor API: OK server_id=$serverId hostname=" . json_encode($hostname) . " cpu=" . ($cpuTemp ?? 'null') . " gpu=" . ($gpuTemp ?? 'null'));
        echo json_encode(['success' => true, 'server_id' => $serverId]);
    } else {
        error_log("Monitor API: état incohérent — pcId=" . ($pcId ?? 'null') . " serverId=" . ($serverId ?? 'null'));
        echo json_encode(['success' => false, 'error' => 'Erreur interne: identifiant manquant']);
    }
} catch (Exception $e) {
    error_log("API Monitor: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
