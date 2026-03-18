<?php
/**
 * API credentials pour découverte ESXi par agent
 * GET ?job_id=X&site_id=Y : l'agent récupère les identifiants d'un job
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: X-Api-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
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

$jobId = (int)($_GET['job_id'] ?? 0);
$siteId = (int)($_GET['site_id'] ?? 0);
if (!$jobId || !$siteId) {
    echo json_encode(['success' => false, 'error' => 'job_id et site_id requis']);
    exit;
}

$db = new Database();
$job = $db->fetch("SELECT id, esxi_host_id, site_id FROM esxi_discovery_jobs WHERE id = ? AND status IN ('pending','running')", [$jobId]);
if (!$job || (int)$job['site_id'] !== $siteId) {
    echo json_encode(['success' => false, 'error' => 'Job introuvable ou site non autorisé']);
    exit;
}

$cred = $db->fetch("SELECT username, password_encrypted FROM esxi_credentials WHERE esxi_host_id = ?", [$job['esxi_host_id']]);
if (!$cred) {
    echo json_encode(['success' => false, 'error' => 'Aucun identifiant enregistré pour cet hôte ESXi. Enregistrez-les dans la fiche ESXi.']);
    exit;
}

require_once __DIR__ . '/../config/credential_helper.php';
$password = esxi_credential_decrypt($cred['password_encrypted']);

echo json_encode([
    'success' => true,
    'username' => $cred['username'],
    'password' => $password
]);
exit;
