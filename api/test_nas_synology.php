<?php
/**
 * Test connexion Synology - vérifier que les identifiants décryptés fonctionnent
 * GET ?nas_id=X avec header X-Api-Key
 */
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'GET uniquement']);
    exit;
}

$apiKey = $_SERVER['HTTP_X_API_KEY'] ?? '';
if (!file_exists(__DIR__ . '/../config/api_config.php')) {
    echo json_encode(['success' => false, 'error' => 'Config manquante']);
    exit;
}
require_once __DIR__ . '/../config/api_config.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/credential_helper.php';

if (empty($apiKey) || $apiKey !== API_INVENTORY_KEY) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Clé API invalide']);
    exit;
}

$nasId = (int)($_GET['nas_id'] ?? 0);
if (!$nasId) {
    echo json_encode(['success' => false, 'error' => 'nas_id requis']);
    exit;
}

$db = new Database();
$nas = $db->fetch("SELECT id, name, host, port FROM nas WHERE id = ?", [$nasId]);
if (!$nas) {
    echo json_encode(['success' => false, 'error' => 'NAS introuvable']);
    exit;
}

$cred = $db->fetch("SELECT username, password_encrypted FROM nas_credentials WHERE nas_id = ?", [$nasId]);
if (!$cred) {
    echo json_encode(['success' => false, 'error' => 'Aucun identifiant enregistré']);
    exit;
}

$password = nas_credential_decrypt($cred['password_encrypted']);
$username = $cred['username'];

// Test longueur décryptée (sans exposer le mot de passe)
$passLen = strlen($password);
$userLen = strlen($username);

$port = (int)($nas['port'] ?? 5000);
$useHttps = ($port == 5001 || $port == 443);
$scheme = $useHttps ? 'https' : 'http';
$baseUrl = "{$scheme}://{$nas['host']}:{$port}";
$loginUrl = $baseUrl . '/webapi/auth.cgi';

$postData = http_build_query([
    'api' => 'SYNO.API.Auth',
    'version' => '6',
    'method' => 'login',
    'account' => $username,
    'passwd' => $password,
    'format' => 'sid',
    'session' => 'FileStation',
]);

$ctx = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
        'content' => $postData,
        'timeout' => 15,
    ],
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
]);

$response = @file_get_contents($loginUrl, false, $ctx);

if ($response === false) {
    $err = error_get_last();
    echo json_encode([
        'success' => false,
        'error' => 'Connexion impossible',
        'detail' => $err['message'] ?? 'Unknown',
        'url' => $loginUrl,
        'debug' => ['user_len' => $userLen, 'pass_len' => $passLen],
    ]);
    exit;
}

$data = json_decode($response, true);
$sid = $data['data']['sid'] ?? '';
$errCode = $data['error']['code'] ?? 0;

if ($sid !== '') {
    echo json_encode([
        'success' => true,
        'message' => 'Connexion Synology OK',
        'nas' => $nas['name'],
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Identifiants refusés par Synology',
        'synology_code' => $errCode,
        'nas' => $nas['name'],
        'debug' => ['user_len' => $userLen, 'pass_len' => $passLen],
    ]);
}
