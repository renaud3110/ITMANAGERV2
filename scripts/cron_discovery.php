#!/usr/bin/env php
<?php
/**
 * Script cron : crée des jobs de découverte ESXi (et autres) pour la planification.
 * À exécuter toutes les heures via crontab :
 *   0 * * * * cd /var/www/itmanager && php scripts/cron_discovery.php
 *
 * Pour chaque hôte ESXi avec discovery_interval_hours > 0, identifiants et site :
 * - crée un job 'pending' si aucune découverte récente (dernière > interval)
 * - évite les doublons (pas de job pending/running existant)
 */

if (php_sapi_name() !== 'cli') {
    die("Ce script doit être exécuté en ligne de commande.\n");
}

$baseDir = dirname(__DIR__);
chdir($baseDir);

define('BASE_PATH', $baseDir);
require_once BASE_PATH . '/config/Database.php';

$db = new Database();

// Hôtes ESXi à découvrir (interval > 0, identifiants, site)
$hosts = $db->fetchAll(
    "SELECT e.id, e.name, e.site_id, e.discovery_interval_hours,
            (SELECT MAX(completed_at) FROM esxi_discovery_jobs WHERE esxi_host_id = e.id AND status IN ('done','error')) as last_completed,
            (SELECT COUNT(*) FROM esxi_discovery_jobs WHERE esxi_host_id = e.id AND status IN ('pending','running')) as pending_count,
            (SELECT 1 FROM esxi_credentials WHERE esxi_host_id = e.id LIMIT 1) as has_creds
     FROM esxi_hosts e
     WHERE e.site_id IS NOT NULL
       AND COALESCE(e.discovery_interval_hours, 1) > 0"
);
$hosts = array_filter($hosts, fn($h) => !empty($h['has_creds']));

$now = time();
$created = 0;

foreach ($hosts as $h) {
    $hostId = (int)$h['id'];
    $intervalHours = max(1, (int)($h['discovery_interval_hours'] ?? 1));
    $pendingCount = (int)($h['pending_count'] ?? 0);

    if ($pendingCount > 0) {
        continue; // Job déjà en attente ou en cours
    }

    $lastCompleted = $h['last_completed'] ?? null;
    $due = true;
    if ($lastCompleted) {
        $lastTs = strtotime($lastCompleted);
        $minIntervalSeconds = $intervalHours * 3600 * 0.9; // 90% de l'intervalle (éviter chevauchements)
        if (($now - $lastTs) < $minIntervalSeconds) {
            $due = false;
        }
    }

    if (!$due) {
        continue;
    }

    try {
        $db->query(
            "INSERT INTO esxi_discovery_jobs (esxi_host_id, site_id, status) VALUES (?, ?, 'pending')",
            [$hostId, (int)$h['site_id']]
        );
        $created++;
        error_log(sprintf("cron_discovery: job créé pour ESXi %s (id=%d)", $h['name'], $hostId));
    } catch (Exception $e) {
        error_log(sprintf("cron_discovery: erreur pour ESXi %s: %s", $h['name'], $e->getMessage()));
    }
}

if ($created > 0) {
    echo date('Y-m-d H:i') . " - $created job(s) de découverte ESXi créé(s)\n";
}
