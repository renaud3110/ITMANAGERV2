<?php
/**
 * API - Vérifier si une machine est joignable (ping)
 * GET ?ip=192.168.1.10
 * Retourne JSON: {"online": true|false, "latency_ms": null|int}
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['online' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

$ip = trim($_GET['ip'] ?? '');
if (empty($ip) || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
    echo json_encode(['online' => false, 'error' => 'IP invalide']);
    exit;
}

// Sécurité: pas de ping vers localhost/réseaux privés sensibles si nécessaire
$blocked = in_array($ip, ['127.0.0.1', '0.0.0.0']);
if ($blocked) {
    echo json_encode(['online' => false, 'error' => 'IP non autorisée']);
    exit;
}

$online = false;
$latencyMs = null;

// Méthode 1: ping (Linux/Windows)
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $cmd = 'ping -n 1 -w 2000 ' . escapeshellarg($ip) . ' 2>nul';
} else {
    $cmd = 'ping -c 1 -W 2 ' . escapeshellarg($ip) . ' 2>/dev/null';
}

$output = [];
@exec($cmd, $output, $returnCode);
if ($returnCode === 0 && !empty($output)) {
    $online = true;
    // Extraire le temps si présent (ex: "time=12.3 ms")
    $outputStr = implode(' ', $output);
    if (preg_match('/time[=<>](\d+(?:\.\d+)?)\s*ms/i', $outputStr, $m)) {
        $latencyMs = (int) round((float) $m[1]);
    }
}

// Méthode 2 (fallback): tentative connexion TCP port 80 ou 445
if (!$online && function_exists('fsockopen')) {
    $ports = [80, 445, 135, 22];
    foreach ($ports as $port) {
        $fp = @fsockopen($ip, $port, $errno, $errstr, 2);
        if ($fp) {
            fclose($fp);
            $online = true;
            break;
        }
    }
}

echo json_encode([
    'online' => $online,
    'latency_ms' => $latencyMs,
    'ip' => $ip
]);
