<?php
/**
 * API - Statut en ligne RustDesk Pro
 * GET ?id=123456789 (RustDesk ID)
 * Interroge l'API RustDesk Pro pour savoir si le client est en ligne.
 * Retourne JSON: {"online": true|false, "last_online": "YYYY-MM-DDTHH:MM:SS"|null, "source": "rustdesk_pro"}
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['online' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

$id = trim($_GET['id'] ?? '');
if (empty($id)) {
    echo json_encode(['online' => false, 'error' => 'ID RustDesk requis']);
    exit;
}

// Validation basique (ID RustDesk = chiffres, 9 caractères typiquement)
if (!preg_match('/^\d+$/', $id) || strlen($id) > 20) {
    echo json_encode(['online' => false, 'error' => 'ID RustDesk invalide']);
    exit;
}

if (!file_exists(__DIR__ . '/../config/api_config.php')) {
    echo json_encode(['online' => false, 'error' => 'Configuration manquante']);
    exit;
}

require_once __DIR__ . '/../config/api_config.php';

if (!defined('RUSTDESK_API_URL') || !defined('RUSTDESK_API_TOKEN') || empty(RUSTDESK_API_TOKEN)) {
    echo json_encode(['online' => false, 'error' => 'RustDesk Pro non configuré']);
    exit;
}

$baseUrl = rtrim(RUSTDESK_API_URL, '/');
$url = $baseUrl . '/api/devices?id=' . urlencode($id) . '&pageSize=10&current=1';

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . RUSTDESK_API_TOKEN,
        'Accept: application/json',
    ],
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(['online' => false, 'error' => 'Erreur réseau: ' . $curlError]);
    exit;
}

if ($httpCode !== 200) {
    echo json_encode(['online' => false, 'error' => 'API RustDesk: HTTP ' . $httpCode]);
    exit;
}

$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
    echo json_encode(['online' => false, 'error' => 'Réponse API invalide']);
    exit;
}

if (isset($data['error'])) {
    echo json_encode(['online' => false, 'error' => $data['error']]);
    exit;
}

$devices = $data['data'] ?? [];
$device = null;
foreach ($devices as $d) {
    if (isset($d['id']) && (string)$d['id'] === (string)$id) {
        $device = $d;
        break;
    }
}

if (!$device) {
    echo json_encode([
        'online' => false,
        'last_online' => null,
        'source' => 'rustdesk_pro',
        'message' => 'Appareil non trouvé sur le serveur RustDesk Pro'
    ]);
    exit;
}

$lastOnline = $device['last_online'] ?? null;
$online = false;

if ($lastOnline) {
    try {
        // last_online en UTC (comme dans devices.py RustDesk)
        $str = preg_replace('/\.\d+Z?$/', '', $lastOnline);
        $dt = new DateTime($str, new DateTimeZone('UTC'));
        $now = new DateTime('now', new DateTimeZone('UTC'));
        // En ligne seulement si dernière activité < 30 secondes
        $online = ($now->getTimestamp() - $dt->getTimestamp()) < 30;
    } catch (Exception $e) {
        $online = false;
    }
}

echo json_encode([
    'online' => $online,
    'last_online' => $lastOnline,
    'source' => 'rustdesk_pro',
]);
