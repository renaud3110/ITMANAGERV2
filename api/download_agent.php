<?php
/**
 * Téléchargement des agents IT Manager
 * GET ?file=itmanager-unified.exe|itmanager-monitor.exe|itmanager-agent.exe
 *
 * Les fichiers doivent être copiés dans agent-releases/ par scripts/deploy_agents.sh
 */
header('Content-Type: application/octet-stream');
header('Cache-Control: no-cache, must-revalidate');

$allowed = ['itmanager-unified.exe', 'itmanager-monitor.exe', 'itmanager-agent.exe', 'itmanager-monitor-32.exe', 'supportrgd.exe'];
$file = $_GET['file'] ?? '';

if (!in_array($file, $allowed, true)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Fichier invalide. Utilisez: itmanager-unified.exe, itmanager-monitor.exe, itmanager-agent.exe, supportrgd.exe']);
    exit;
}

$baseDir = dirname(__DIR__);
$path = $baseDir . '/agent-releases/' . $file;

if (!is_file($path)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Fichier non disponible. Exécutez scripts/deploy_agents.sh après compilation.']);
    exit;
}

header('Content-Disposition: attachment; filename="' . $file . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
exit;
