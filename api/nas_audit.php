<?php
/**
 * API : envoi d'audit NAS par l'agent installé sur le NAS (script agent-nas/nas_audit_agent.sh)
 * POST avec X-Api-Key, body : nas_id (int) + audit_text (string ou fichier uploadé)
 */
$nasAuditLogFile = __DIR__ . '/../logs/nas_audit_error.log';
register_shutdown_function(function () use ($nasAuditLogFile) {
    $e = error_get_last();
    if ($e && in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        @file_put_contents($nasAuditLogFile, date('Y-m-d H:i:s') . " FATAL: {$e['message']} in {$e['file']}:{$e['line']}\n", FILE_APPEND);
    }
});

header('Content-Type: application/json; charset=utf-8');
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
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false && $input !== '') {
    $data = json_decode($input, true);
    $nasId = isset($data['nas_id']) ? (int)$data['nas_id'] : 0;
    $auditText = isset($data['audit_text']) ? trim((string)$data['audit_text']) : '';
} else {
    $nasId = (int)($_POST['nas_id'] ?? 0);
    if (!empty($_FILES['audit_text']['tmp_name']) && is_uploaded_file($_FILES['audit_text']['tmp_name'])) {
        $auditText = trim((string)file_get_contents($_FILES['audit_text']['tmp_name']));
    } elseif (!empty($_FILES['audit_text']['error']) && $_FILES['audit_text']['error'] !== UPLOAD_ERR_OK) {
        $err = $_FILES['audit_text']['error'];
        $msgs = [1=>'Fichier trop volumineux (upload_max_filesize)', 2=>'Fichier trop volumineux (MAX_FILE_SIZE)', 3=>'Upload partiel', 4=>'Aucun fichier', 6=>'Dossier temp manquant', 7=>'Échec écriture'];
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Upload échoué: ' . ($msgs[$err] ?? 'code ' . $err)]);
        exit;
    } else {
        $auditText = trim($_POST['audit_text'] ?? '');
    }
}

if (!$nasId || $auditText === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'nas_id et audit_text requis']);
    exit;
}

require_once __DIR__ . '/../models/BaseModel.php';
require_once __DIR__ . '/../models/Nas.php';
require_once __DIR__ . '/../config/syno_audit_parser.php';

try {
    $nasModel = new Nas();
    $nas = $nasModel->getById($nasId);
    if (!$nas) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'NAS introuvable']);
        exit;
    }

    $parsed = parse_syno_audit_output($auditText);
    $sharesJson = json_encode($parsed['shares'] ?? []);
    $volumesJson = json_encode($parsed['volumes']);
    $disksJson = json_encode($parsed['disks']);
    $raidJson = json_encode($parsed['raid'] ?? []);
    $nasModel->saveAuditDiscovery($nasId, $sharesJson, $volumesJson, $disksJson, $raidJson, $auditText);

    echo json_encode([
        'success' => true,
        'message' => 'Audit enregistré',
        'shares' => count($parsed['shares'] ?? []),
        'volumes' => count($parsed['volumes']),
        'disks' => count($parsed['disks']),
        'raid' => count($parsed['raid'] ?? []),
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Erreur serveur',
        'detail' => $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
    ], JSON_UNESCAPED_UNICODE);
}
