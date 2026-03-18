<?php
/**
 * API - Statut en ligne RustDesk Pro (batch)
 * GET ?ids=123,456,789 (IDs RustDesk séparés par des virgules)
 * Retourne JSON: {"123": {"online": true, "last_online": "..."}, "456": {"online": false, ...}, ...}
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$idsParam = trim($_GET['ids'] ?? '');
$ids = array_filter(array_map('trim', explode(',', $idsParam)));
$ids = array_unique(array_filter($ids, function ($id) {
    return preg_match('/^\d+$/', $id) && strlen($id) <= 20;
}));

if (empty($ids)) {
    echo json_encode([]);
    exit;
}

if (!file_exists(__DIR__ . '/../config/api_config.php')) {
    echo json_encode([]);
    exit;
}

require_once __DIR__ . '/../config/api_config.php';

if (!defined('RUSTDESK_API_URL') || !defined('RUSTDESK_API_TOKEN') || empty(RUSTDESK_API_TOKEN)) {
    echo json_encode([]);
    exit;
}

$baseUrl = rtrim(RUSTDESK_API_URL, '/');
$idsMap = array_fill_keys($ids, ['online' => false, 'last_online' => null]);
$pageSize = 500;
$current = 1;
$totalFetched = 0;

do {
    $url = $baseUrl . '/api/devices?pageSize=' . $pageSize . '&current=' . $current;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . RUSTDESK_API_TOKEN,
            'Accept: application/json',
        ],
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        break;
    }

    $data = json_decode($response, true);
    if (!is_array($data) || isset($data['error'])) {
        break;
    }

    $devices = $data['data'] ?? [];
    $total = (int)($data['total'] ?? 0);

    foreach ($devices as $d) {
        $id = isset($d['id']) ? (string)$d['id'] : null;
        if ($id && isset($idsMap[$id])) {
            $lastOnline = $d['last_online'] ?? null;
            $online = false;
            if ($lastOnline) {
                try {
                    // last_online est en UTC (comme datetime.utcnow dans devices.py RustDesk)
                    $str = preg_replace('/\.\d+Z?$/', '', $lastOnline); // enlever millisecondes
                    $dt = new DateTime($str, new DateTimeZone('UTC'));
                    $now = new DateTime('now', new DateTimeZone('UTC'));
                    // En ligne seulement si dernière activité < 30 secondes
                    $online = ($now->getTimestamp() - $dt->getTimestamp()) < 30;
                } catch (Exception $e) {}
            }
            $idsMap[$id] = ['online' => $online, 'last_online' => $lastOnline];
        }
    }

    $totalFetched += count($devices);
    if (count($devices) < $pageSize || $totalFetched >= $total) {
        break;
    }
    $current++;
} while (true);

echo json_encode($idsMap);
