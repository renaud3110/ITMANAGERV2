<?php
/**
 * API découverte ESXi par agent sur site
 * GET : l'agent récupère les jobs en attente pour son site_id
 * POST : l'agent envoie le résultat de la découverte
 * Authentification : X-Api-Key (même clé que l'inventaire/monitor)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Api-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'])) {
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

$db = new Database();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $siteId = (int)($_GET['site_id'] ?? 0);
    if (!$siteId) {
        echo json_encode(['success' => false, 'error' => 'site_id requis']);
        exit;
    }

    $agentHostname = trim($_GET['agent_hostname'] ?? '');

    // Mode claim : avec plusieurs agents sur un site, chacun "réclame" atomiquement un job
    // Seul le premier agent à faire l'UPDATE réussit (WHERE status='pending')
    if ($agentHostname !== '') {
        $pending = $db->fetchAll(
            "SELECT j.id, j.esxi_host_id, e.name as esxi_name, e.host, e.port, COALESCE(e.hypervisor_type, 'esxi') as hypervisor_type
             FROM esxi_discovery_jobs j
             JOIN esxi_hosts e ON e.id = j.esxi_host_id
             WHERE j.site_id = ? AND j.status = 'pending'
             ORDER BY j.requested_at ASC
             LIMIT 10",
            [$siteId]
        );
        $claimed = [];
        foreach ($pending as $job) {
            $stmt = $db->query(
                "UPDATE esxi_discovery_jobs SET status = 'running', agent_hostname = ? WHERE id = ? AND status = 'pending'",
                [$agentHostname, (int)$job['id']]
            );
            if ($stmt->rowCount() > 0) {
                $claimed[] = $job;
            }
        }
        echo json_encode(['success' => true, 'jobs' => $claimed]);
        exit;
    }

    // Mode legacy (sans agent_hostname) : retourne tous les pending (compatibilité anciens agents)
    $jobs = $db->fetchAll(
        "SELECT j.id, j.esxi_host_id, e.name as esxi_name, e.host, e.port, COALESCE(e.hypervisor_type, 'esxi') as hypervisor_type
         FROM esxi_discovery_jobs j
         JOIN esxi_hosts e ON e.id = j.esxi_host_id
         WHERE j.site_id = ? AND j.status = 'pending'
         ORDER BY j.requested_at ASC
         LIMIT 10",
        [$siteId]
    );

    echo json_encode(['success' => true, 'jobs' => $jobs]);
    exit;
}

// POST : agent envoie le résultat
$input = file_get_contents('php://input');
$data = json_decode($input, true);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'JSON invalide']);
    exit;
}

$jobId = (int)($data['job_id'] ?? 0);
$success = !empty($data['success']);
$agentHostname = trim($data['agent_hostname'] ?? '');
$hostsJson = $data['hosts'] ?? [];
$vmsJson = $data['vms'] ?? [];
$datastoresJson = $data['datastores'] ?? [];
$errorMsg = trim($data['error_message'] ?? '');

if (!$jobId) {
    echo json_encode(['success' => false, 'error' => 'job_id requis']);
    exit;
}

$job = $db->fetch("SELECT id, esxi_host_id, site_id FROM esxi_discovery_jobs WHERE id = ? AND status IN ('pending','running')", [$jobId]);
if (!$job) {
    echo json_encode(['success' => false, 'error' => 'Job introuvable ou déjà traité']);
    exit;
}

$now = gmdate('Y-m-d H:i:s');
$status = $success ? 'done' : 'error';

$db->query(
    "UPDATE esxi_discovery_jobs SET status = ?, agent_hostname = ?, completed_at = ?, hosts_json = ?, vms_json = ?, datastores_json = ?, error_message = ? WHERE id = ?",
    [$status, $agentHostname ?: null, $now, json_encode($hostsJson), json_encode($vmsJson), json_encode($datastoresJson), $errorMsg ?: null, $jobId]
);

// Historique
try {
    $db->query(
        "INSERT INTO esxi_discovery (esxi_host_id, hosts_json, vms_json, datastores_json, error_message) VALUES (?, ?, ?, ?, ?)",
        [$job['esxi_host_id'], json_encode($hostsJson), json_encode($vmsJson), json_encode($datastoresJson), $errorMsg ?: null]
    );
} catch (Exception $e) {
    // Ignorer si table inexistante
}

// Mettre à jour esxi_vms avec les VMs découvertes
if ($success && !empty($vmsJson) && is_array($vmsJson)) {
    $esxiHostId = (int)$job['esxi_host_id'];
    // Sauvegarder les liaisons manuelles (vm_name -> server_id) avant suppression
    $existingLinks = $db->fetchAll("SELECT vm_name, server_id FROM esxi_vms WHERE esxi_host_id = ? AND server_id IS NOT NULL", [$esxiHostId]);
    $manualLinks = [];
    foreach ($existingLinks as $row) {
        $manualLinks[$row['vm_name']] = (int)$row['server_id'];
    }

    $oldVmIds = $db->fetchAll("SELECT id FROM esxi_vms WHERE esxi_host_id = ?", [$esxiHostId]);
    if (!empty($oldVmIds)) {
        $ids = array_column($oldVmIds, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $db->query("UPDATE servers SET esxi_vm_id = NULL WHERE esxi_vm_id IN ($placeholders)", $ids);
    }
    $db->query("DELETE FROM esxi_vms WHERE esxi_host_id = ?", [$esxiHostId]);

    foreach ($vmsJson as $vm) {
        $vmName = trim($vm['name'] ?? '');
        $vmUuid = !empty($vm['uuid'] ?? '') ? normalizeVmUuidForDb(trim($vm['uuid'])) : null;
        $vmMoRef = trim($vm['mo_ref'] ?? '') ?: null;
        $powerState = trim($vm['power_state'] ?? '') ?: null;
        $guestOs = trim($vm['guest_os'] ?? '') ?: null;
        $cpuCount = isset($vm['cpu_count']) ? (int)$vm['cpu_count'] : null;
        $ramMb = isset($vm['ram_mb']) ? (int)$vm['ram_mb'] : null;
        $autoStart = !empty($vm['auto_start']) ? 1 : 0;
        $disksJson = null;
        if (!empty($vm['disks']) && is_array($vm['disks'])) {
            $disksJson = json_encode($vm['disks']);
        }

        if ($vmName === '') continue;

        $serverId = null;
        if ($vmUuid) {
            $srv = $db->fetch("SELECT id FROM servers WHERE vm_uuid = ?", [$vmUuid]);
            if ($srv) {
                $serverId = (int)$srv['id'];
            }
        }
        if (!$serverId && !empty($manualLinks[$vmName])) {
            $serverId = $manualLinks[$vmName];
        }

        $db->query(
            "INSERT INTO esxi_vms (esxi_host_id, vm_name, vm_mo_ref, vm_uuid, power_state, guest_os, cpu_count, ram_mb, auto_start, disks_json, server_id, discovered_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$esxiHostId, $vmName, $vmMoRef, $vmUuid, $powerState, $guestOs, $cpuCount, $ramMb, $autoStart, $disksJson, $serverId, $now]
        );
        $newVmId = $db->lastInsertId();
        if ($serverId) {
            $db->query("UPDATE servers SET esxi_vm_id = ? WHERE id = ?", [$newVmId, $serverId]);
        }
    }
}

function normalizeVmUuidForDb($uuid)
{
    $uuid = strtoupper(str_replace([' ', '-'], '', trim($uuid)));
    if (strlen($uuid) !== 32 || !ctype_xdigit($uuid)) {
        return null;
    }
    return substr($uuid, 0, 8) . '-' . substr($uuid, 8, 4) . '-' . substr($uuid, 12, 4) . '-' . substr($uuid, 16, 4) . '-' . substr($uuid, 20, 12);
}

echo json_encode(['success' => true, 'job_id' => $jobId]);
exit;
