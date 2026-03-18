<?php
/**
 * API découverte par agent sur site
 * GET : l'agent récupère les jobs en attente pour son site_id
 * POST : l'agent envoie le résultat d'une découverte
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
    // Agent : récupérer les jobs en attente pour son site
    $siteId = (int)($_GET['site_id'] ?? 0);
    if (!$siteId) {
        echo json_encode(['success' => false, 'error' => 'site_id requis']);
        exit;
    }

    $agentHostname = trim($_GET['agent_hostname'] ?? '');

    // Mode claim : avec plusieurs agents sur un site, chacun "réclame" atomiquement un job
    if ($agentHostname !== '') {
        $pending = $db->fetchAll(
            "SELECT j.id, j.nas_id, n.name as nas_name, n.host, n.port, n.type
             FROM discovery_jobs j
             JOIN nas n ON n.id = j.nas_id
             WHERE j.site_id = ? AND j.status = 'pending'
             ORDER BY j.requested_at ASC
             LIMIT 10",
            [$siteId]
        );
        $claimed = [];
        foreach ($pending as $job) {
            $stmt = $db->query(
                "UPDATE discovery_jobs SET status = 'running', agent_hostname = ? WHERE id = ? AND status = 'pending'",
                [$agentHostname, (int)$job['id']]
            );
            if ($stmt->rowCount() > 0) {
                $claimed[] = $job;
            }
        }
        echo json_encode(['success' => true, 'jobs' => $claimed]);
        exit;
    }

    // Mode legacy (sans agent_hostname)
    $jobs = $db->fetchAll(
        "SELECT j.id, j.nas_id, n.name as nas_name, n.host, n.port, n.type
         FROM discovery_jobs j
         JOIN nas n ON n.id = j.nas_id
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
$shares = $data['shares'] ?? [];
$volumes = $data['volumes'] ?? [];
$disks = $data['disks'] ?? [];
$errorMsg = trim($data['error_message'] ?? '');

if (!$jobId) {
    echo json_encode(['success' => false, 'error' => 'job_id requis']);
    exit;
}

$job = $db->fetch("SELECT id, nas_id, site_id FROM discovery_jobs WHERE id = ? AND status IN ('pending','running')", [$jobId]);
if (!$job) {
    echo json_encode(['success' => false, 'error' => 'Job introuvable ou déjà traité']);
    exit;
}

$now = gmdate('Y-m-d H:i:s');
$status = $success ? 'done' : 'error';

$db->query(
    "UPDATE discovery_jobs SET status = ?, agent_hostname = ?, completed_at = ?, shares_json = ?, volumes_json = ?, error_message = ? WHERE id = ?",
    [$status, $agentHostname ?: null, $now, json_encode($shares), json_encode($volumes), $errorMsg ?: null, $jobId]
);

// Enregistrer dans nas_discovery pour l'historique
try {
    $db->query(
        "INSERT INTO nas_discovery (nas_id, shares_json, volumes_json, disks_json, error_message) VALUES (?, ?, ?, ?, ?)",
        [$job['nas_id'], json_encode($shares), json_encode($volumes), json_encode($disks), $errorMsg ?: null]
    );
} catch (Exception $e) {
    // Fallback si disks_json n'existe pas (migration non appliquée)
    $db->query(
        "INSERT INTO nas_discovery (nas_id, shares_json, volumes_json, error_message) VALUES (?, ?, ?, ?)",
        [$job['nas_id'], json_encode($shares), json_encode($volumes), $errorMsg ?: null]
    );
}

echo json_encode(['success' => true, 'job_id' => $jobId]);
exit;
