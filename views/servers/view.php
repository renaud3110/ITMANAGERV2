<?php
$currentTab = $_GET['tab'] ?? 'home';
$validTabs = ['home', 'hardware', 'logiciel', 'reseau', 'applications', 'os', 'ressources'];
$installedSoftware = $installedSoftware ?? [];
$disks = $disks ?? [];
if (!in_array($currentTab, $validTabs)) $currentTab = 'home';
$currentWinTab = $_GET['wintab'] ?? 'update';
$validWinTabs = ['update', 'services', 'startup', 'license', 'shared', 'mapped', 'users', 'group'];
if (!in_array($currentWinTab, $validWinTabs)) $currentWinTab = 'update';
$lastSeen = $server['monitor_last_seen'] ?? null;
$monOnline = $lastSeen && (time() - strtotime($lastSeen . ' UTC')) < 60;
$ramTotalGB = isset($server['ram_total']) && $server['ram_total'] > 0
    ? ($server['ram_total_gb'] ?? round($server['ram_total'] / 1024 / 1024 / 1024, 2))
    : null;
$ramUsedGB = isset($server['ram_used']) && $server['ram_used'] > 0
    ? ($server['ram_used_gb'] ?? round($server['ram_used'] / 1024 / 1024 / 1024, 2))
    : null;
$ramUsagePct = ($ramTotalGB && $ramUsedGB && $ramTotalGB > 0) ? round(100 * $ramUsedGB / $ramTotalGB, 1) : 0;
?>
<div class="pc-detail-fullwidth">
<div class="pc-detail-header">
    <div class="pc-detail-title">
        <i class="fas fa-server"></i>
        <div>
            <h1 class="pc-name"><?= htmlspecialchars($server['name'] ?? $server['hostname'] ?? 'Sans nom') ?></h1>
            <span class="pc-subtitle">Serveur <?= htmlspecialchars($server['hostname'] ?? $server['name'] ?? '') ?></span>
            <span class="pc-updated">Mis à jour <?= $server['updated_at'] ? date('d M Y, H:i', strtotime($server['updated_at'])) : '—' ?></span>
        </div>
        <?php if (!empty($server['tenant_name']) || !empty($server['site_name'])): ?>
        <span class="pc-location-badge" title="Localisation">
            <i class="fas fa-map-marker-alt"></i>
            <?= htmlspecialchars(trim(($server['tenant_name'] ?? '') . ($server['site_name'] ? ' • ' . $server['site_name'] : ''))) ?: '—' ?>
        </span>
        <?php endif; ?>
    </div>
    <div class="pc-detail-badges">
        <span class="pc-badge badge-type" title="Type">
            <i class="fas fa-<?= ($server['type'] ?? '') === 'Virtuel' ? 'cloud' : 'hdd' ?>"></i>
            <?= htmlspecialchars($server['type'] ?? 'Physique') ?>
        </span>
        <a href="?page=servers&action=edit&id=<?= $server['id'] ?>" class="pc-btn-edit">
            <i class="fas fa-edit"></i> Modifier
        </a>
        <a href="?page=servers" class="pc-btn-back">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

<!-- Navigation par onglets -->
<nav class="pc-tabs">
    <a href="?page=servers&action=view&id=<?= $server['id'] ?>&tab=home" class="pc-tab <?= $currentTab === 'home' ? 'active' : '' ?>">
        <i class="fas fa-home"></i> Home
    </a>
    <a href="?page=servers&action=view&id=<?= $server['id'] ?>&tab=hardware" class="pc-tab <?= $currentTab === 'hardware' ? 'active' : '' ?>">
        <i class="fas fa-microchip"></i> Hardware
    </a>
    <a href="?page=servers&action=view&id=<?= $server['id'] ?>&tab=applications" class="pc-tab <?= $currentTab === 'applications' ? 'active' : '' ?>">
        <i class="fas fa-cube"></i> Applications
    </a>
    <a href="?page=servers&action=view&id=<?= $server['id'] ?>&tab=os" class="pc-tab <?= $currentTab === 'os' ? 'active' : '' ?>">
        <i class="fas fa-windows"></i> Windows
    </a>
    <a href="?page=servers&action=view&id=<?= $server['id'] ?>&tab=ressources" class="pc-tab <?= $currentTab === 'ressources' ? 'active' : '' ?>">
        <i class="fas fa-thermometer-half"></i> Ressources
    </a>
    <a href="?page=servers&action=view&id=<?= $server['id'] ?>&tab=reseau" class="pc-tab <?= $currentTab === 'reseau' ? 'active' : '' ?>">
        <i class="fas fa-network-wired"></i> Réseau
    </a>
</nav>

<div class="computer-details computer-details-grid" style="margin-top: 1.5rem;">

<!-- ========== ONGLET HOME ========== -->
<div class="tab-panel tab-home" style="display: <?= $currentTab === 'home' ? 'block' : 'none' ?>; grid-column: 1 / -1;">
    <!-- Première ligne : Connectivité, Antivirus, Firewall, Santé -->
    <div class="home-status-row">
        <div class="card card-block home-status-card">
            <div class="card-header"><span class="card-block-icon"><i class="fas fa-wifi"></i></span><h2 class="card-title">Connectivité</h2></div>
            <div class="card-body">
                <?php if ($lastSeen || isset($server['monitor_cpu_temp'])): ?>
                    <p class="home-status-value <?= $monOnline ? 'status-ok' : 'status-error' ?>">
                        <i class="fas fa-<?= $monOnline ? 'check-circle' : 'times-circle' ?>"></i>
                        <strong><?= $monOnline ? 'En ligne' : 'Hors ligne' ?></strong>
                    </p>
                    <small class="text-muted"><?php
                        if (!$lastSeen) echo 'Agent moniteur jamais connecté';
                        else {
                            $mins = round((time()-strtotime($lastSeen . ' UTC'))/60);
                            echo $monOnline ? 'Agent moniteur en ligne' : 'Agent perdu depuis ' . $mins . ' min';
                        }
                    ?></small>
                    <?php if ($lastSeen): ?>
                        <br><small class="text-muted">Dernière activité : <?= date('d/m/Y H:i', strtotime($lastSeen . ' UTC')) ?></small>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="home-status-value status-unknown"><i class="fas fa-question-circle"></i> <strong>Aucune donnée</strong></p>
                    <small class="text-muted">Installez l'agent moniteur sur ce serveur</small>
                <?php endif; ?>
            </div>
        </div>
        <div class="card card-block home-status-card">
            <div class="card-header"><span class="card-block-icon"><i class="fas fa-shield-alt"></i></span><h2 class="card-title">Antivirus</h2></div>
            <div class="card-body">
                <?php
                $avName = $server['antivirus_name'] ?? null;
                $avEnabled = isset($server['antivirus_enabled']) ? (bool)$server['antivirus_enabled'] : null;
                $avUpdated = isset($server['antivirus_updated']) ? (bool)$server['antivirus_updated'] : null;
                if ($avName): ?>
                    <p class="home-status-value <?= $avEnabled ? 'status-ok' : 'status-warn' ?>">
                        <i class="fas fa-<?= $avEnabled ? 'check-circle' : 'exclamation-triangle' ?>"></i>
                        <strong><?= htmlspecialchars($avName) ?></strong>
                    </p>
                    <small class="text-muted"><?= $avEnabled ? 'Activé' : 'Désactivé' ?></small>
                    <?php if ($avUpdated !== null): ?><br><small class="text-muted"><?= $avUpdated ? 'À jour' : 'Mise à jour requise' ?></small><?php endif; ?>
                <?php else: ?>
                    <p class="home-status-value status-unknown"><i class="fas fa-question-circle"></i> <strong>Non détecté</strong></p>
                    <small class="text-muted">L'agent n'a pas détecté d'antivirus</small>
                <?php endif; ?>
            </div>
        </div>
        <div class="card card-block home-status-card">
            <div class="card-header"><span class="card-block-icon"><i class="fas fa-fire-alt"></i></span><h2 class="card-title">Firewall</h2></div>
            <div class="card-body">
                <?php
                $fwEnabled = isset($server['firewall_enabled']) ? (bool)$server['firewall_enabled'] : null;
                if ($fwEnabled !== null): ?>
                    <p class="home-status-value <?= $fwEnabled ? 'status-ok' : 'status-warn' ?>">
                        <i class="fas fa-<?= $fwEnabled ? 'check-circle' : 'times-circle' ?>"></i>
                        <strong><?= $fwEnabled ? 'Activé' : 'Désactivé' ?></strong>
                    </p>
                    <small class="text-muted"><?= $fwEnabled ? 'Le pare-feu Windows est actif' : 'Le pare-feu est désactivé' ?></small>
                <?php else: ?>
                    <p class="home-status-value status-unknown"><i class="fas fa-question-circle"></i> <strong>Non détecté</strong></p>
                    <small class="text-muted">L'agent n'a pas vérifié le firewall</small>
                <?php endif; ?>
            </div>
        </div>
        <div class="card card-block home-status-card">
            <div class="card-header"><span class="card-block-icon"><i class="fas fa-heartbeat"></i></span><h2 class="card-title">Santé</h2></div>
            <div class="card-body">
                <?php
                $cpuT = isset($server['monitor_cpu_temp']) && $server['monitor_cpu_temp'] !== null ? (float)$server['monitor_cpu_temp'] : null;
                $gpuT = isset($server['monitor_gpu_temp']) && $server['monitor_gpu_temp'] !== null ? (float)$server['monitor_gpu_temp'] : null;
                $hasTemps = $cpuT !== null || $gpuT !== null;
                $tempOk = !$hasTemps || (($cpuT === null || $cpuT < 90) && ($gpuT === null || $gpuT < 90));
                if ($hasTemps): ?>
                    <p class="home-status-value <?= $tempOk ? 'status-ok' : 'status-warn' ?>">
                        <i class="fas fa-thermometer-half"></i>
                        <strong>Dernières températures</strong>
                    </p>
                    <?php if ($cpuT !== null): ?><p class="mb-1"><i class="fas fa-microchip"></i> CPU <?= round($cpuT, 1) ?>°C</p><?php endif; ?>
                    <?php if ($gpuT !== null): ?><p class="mb-0"><i class="fas fa-video"></i> GPU <?= round($gpuT, 1) ?>°C</p><?php endif; ?>
                    <small class="text-muted"><?= $lastSeen ? 'Relevé le ' . date('d/m H:i', strtotime($lastSeen . ' UTC')) : '' ?></small>
                <?php else: ?>
                    <p class="home-status-value status-unknown"><i class="fas fa-question-circle"></i> <strong>Aucune donnée</strong></p>
                    <small class="text-muted">Températures non disponibles</small>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Deuxième ligne : Logiciel, Hardware, Localisation, Réseau -->
    <div class="home-status-row">
        <div class="card card-block home-status-card">
            <div class="card-header"><span class="card-block-icon"><i class="fas fa-cog"></i></span><h2 class="card-title">Logiciel</h2></div>
            <div class="card-body">
                <p class="home-status-value"><strong><?= htmlspecialchars($server['operating_system_name'] ?? 'Non défini') ?></strong> <?= htmlspecialchars($server['os_version_name'] ?? '') ?></p>
                <small class="text-muted"><?= count($installedSoftware) ?> logiciels installés</small>
            </div>
        </div>
        <div class="card card-block home-status-card">
            <div class="card-header"><span class="card-block-icon"><i class="fas fa-microchip"></i></span><h2 class="card-title">Hardware</h2></div>
            <div class="card-body">
                <p class="home-status-value"><strong><?= htmlspecialchars($server['model_brand'] ?? '') ?></strong> <?= htmlspecialchars($server['model_name'] ?? '') ?></p>
                <small><?= htmlspecialchars($server['processor_model'] ?? '') ?></small>
                <?php if ($ramTotalGB): ?>
                    <p class="mb-0 mt-1"><i class="fas fa-memory"></i> <?= number_format($ramTotalGB, 1) ?> GB RAM</p>
                <?php endif; ?>
            </div>
        </div>
        <?php if (!empty($server['esxi_host'])): ?>
        <div class="card card-block home-status-card">
            <div class="card-header"><span class="card-block-icon"><i class="fas fa-cube"></i></span><h2 class="card-title">Hôte virtualisation</h2></div>
            <div class="card-body">
                <p class="home-status-value"><i class="fas fa-server"></i> <strong><?= htmlspecialchars($server['esxi_host']['name']) ?></strong></p>
                <small class="text-muted"><?= htmlspecialchars($server['esxi_host']['host']) ?>:<?= (int)($server['esxi_host']['port'] ?? 443) ?></small>
                <p class="mb-0 mt-2">
                    <a href="?page=hardware&section=esxi&expand=<?= (int)$server['esxi_host']['id'] ?>#esxi-vms-<?= (int)$server['esxi_host']['id'] ?>" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-list"></i> Voir les VMs de cet hôte
                    </a>
                </p>
            </div>
        </div>
        <?php endif; ?>
        <div class="card card-block home-status-card">
            <div class="card-header"><span class="card-block-icon"><i class="fas fa-users"></i></span><h2 class="card-title">Info Users</h2></div>
            <div class="card-body">
                <p class="home-status-value"><strong>Dernier compte :</strong> <?= htmlspecialchars($server['last_account'] ?? '—') ?></p>
                <?php if (!empty($server['last_account_created_at'])): ?>
                    <small class="text-muted">Profil créé le <?= date('d/m/Y', strtotime($server['last_account_created_at'])) ?></small><br>
                <?php endif; ?>
                <?php
                $monLoggedIn = isset($server['monitor_logged_in']) ? (int)$server['monitor_logged_in'] : null;
                $monLogoutAt = $server['monitor_last_logout_at'] ?? null;
                if ($monLoggedIn === 1): ?>
                    <p class="mb-1 status-ok"><i class="fas fa-check-circle"></i> Toujours connecté<?= !empty($server['monitor_logged_in_username']) ? ' : <strong>' . htmlspecialchars($server['monitor_logged_in_username']) . '</strong>' : '' ?></p>
                <?php elseif ($monLoggedIn === 0 && $monLogoutAt): ?>
                    <p class="mb-1 status-warn"><i class="fas fa-sign-out-alt"></i> Déconnecté depuis <?= date('d/m/Y H:i', strtotime($monLogoutAt)) ?></p>
                <?php elseif ($monLoggedIn === 0): ?>
                    <p class="mb-1 status-warn"><i class="fas fa-sign-out-alt"></i> Déconnecté</p>
                <?php else: ?>
                    <p class="mb-1 text-muted"><i class="fas fa-minus-circle"></i> Non détecté</p>
                <?php endif; ?>
                <hr class="my-2">
                <p class="mb-0"><strong>Attribué à :</strong><br>
                <?php if (!empty($server['person_nom']) || !empty($server['person_prenom'])): ?>
                    <?= htmlspecialchars(trim(($server['person_prenom'] ?? '') . ' ' . ($server['person_nom'] ?? ''))) ?>
                    <?php if (!empty($server['person_email'])): ?><br><small><?= htmlspecialchars($server['person_email']) ?></small><?php endif; ?>
                <?php else: ?><span class="text-muted">Non attribué</span><?php endif; ?>
                </p>
            </div>
        </div>
        <?php if (!empty($server['rustdesk_id'])): ?>
        <div class="card card-block home-status-card">
            <div class="card-header"><span class="card-block-icon"><i class="fas fa-desktop"></i></span><h2 class="card-title">RustDesk</h2></div>
            <div class="card-body">
                <p class="home-status-value"><strong>ID <?= htmlspecialchars($server['rustdesk_id']) ?></strong></p>
                <a href="<?= ($rustdeskProtocol ?? 'rustdesk') ?>://<?= htmlspecialchars($server['rustdesk_id']) ?>" class="rustdesk-link btn btn-sm btn-outline-secondary" title="Ouvrir avec RustDesk/supportrgd. Installez le client sur cette machine pour vous connecter."><i class="fas fa-desktop"></i> Ouvrir</a>
                <div class="rustdesk-status mt-2" data-rustdesk-id="<?= htmlspecialchars($server['rustdesk_id']) ?>">
                    <span id="rustdesk-status-badge" class="badge badge-secondary">Non vérifié</span>
                    <button type="button" class="btn btn-outline-primary btn-sm ms-1" id="btn-check-online" title="Vérifier"><i class="fas fa-sync-alt"></i></button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-refresh-page" title="Rafraîchir"><i class="fas fa-redo"></i></button>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($server['teamviewer_id'])): ?>
        <div class="card card-block home-status-card">
            <div class="card-header"><span class="card-block-icon"><i class="fas fa-desktop"></i></span><h2 class="card-title">TeamViewer</h2></div>
            <div class="card-body">
                <a href="https://start.teamviewer.com/<?= htmlspecialchars($server['teamviewer_id']) ?>" target="_blank" class="teamviewer-link">
                    <span class="teamviewer-id"><i class="fas fa-desktop"></i> <?= htmlspecialchars($server['teamviewer_id']) ?> <i class="fas fa-external-link-alt external-icon"></i></span>
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ========== ONGLET HARDWARE ========== -->
<div class="tab-panel tab-hardware" style="display: <?= $currentTab === 'hardware' ? 'grid' : 'none' ?>; grid-column: 1 / -1; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
    <!-- CPU -->
    <div class="card card-block hw-card hw-card-cpu hw-card-block">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-microchip"></i> CPU</h2></div>
        <div class="card-body">
            <?php
            $procModel = $server['processor_model'] ?? '';
            $procCores = isset($server['processor_cores']) && $server['processor_cores'] > 0 ? (int)$server['processor_cores'] : null;
            $procMhz = isset($server['processor_speed_mhz']) && $server['processor_speed_mhz'] > 0 ? (float)$server['processor_speed_mhz'] : null;
            $procSpeedGhz = $procMhz ? round($procMhz / 1000, 2) : null;
            if (!$procSpeedGhz && $procModel && preg_match('/@\s*([\d.]+)\s*GHz/i', $procModel, $m)) {
                $procSpeedGhz = (float)$m[1];
            }
            $procVendor = $server['processor_manufacturer'] ?? null;
            $procFamily = $server['processor_family'] ?? null;
            $mainLine = $procModel ?: 'Non détecté';
            if ($procModel && $procCores && strpos($procModel, ' x ') === false && strpos($procModel, ' cores') === false) {
                $mainLine .= ' x ' . $procCores . ' cores';
            }
            ?>
            <p class="hw-cpu-model"><?= htmlspecialchars($mainLine) ?></p>
            <?php if ($procSpeedGhz): ?>
            <p class="hw-cpu-speed"><span class="hw-cpu-speed-value"><?= number_format($procSpeedGhz, 2) ?></span> <span class="hw-cpu-speed-unit">GHz</span></p>
            <?php endif; ?>
            <?php if ($procVendor || $procFamily): ?>
            <div class="hw-cpu-details">
                <?php if ($procVendor): ?><div class="hw-cpu-detail-item"><span class="hw-cpu-detail-label">Manufacturer</span><span class="hw-cpu-detail-value"><?= htmlspecialchars($procVendor) ?></span></div><?php endif; ?>
                <?php if ($procFamily): ?><div class="hw-cpu-detail-item"><span class="hw-cpu-detail-label">Family</span><span class="hw-cpu-detail-value"><?= htmlspecialchars($procFamily) ?></span></div><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- RAM -->
    <div class="card card-block hw-card hw-card-block">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-memory"></i> RAM</h2></div>
        <div class="card-body">
            <?php if ($ramTotalGB): ?>
                <p class="hw-block-model">Mémoire</p>
                <p class="hw-block-highlight"><span class="hw-block-highlight-value"><?= number_format($ramTotalGB, 1) ?></span> <span class="hw-block-highlight-unit">GB</span></p>
                <?php if ($ramUsedGB > 0): ?>
                <div class="ram-bar"><div class="ram-bar-fill" style="width:<?= min(100, $ramUsagePct) ?>%"></div></div>
                <small class="hw-block-extra">Utilisé <?= number_format($ramUsedGB, 1) ?> GB (<?= $ramUsagePct ?>%)</small>
                <?php endif; ?>
            <?php else: ?>
                <span class="text-muted">Non défini</span>
            <?php endif; ?>
        </div>
    </div>
    <!-- Stockage -->
    <div class="card card-block hw-card hw-card-block hw-card-storage">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-hdd"></i> Stockage</h2></div>
        <div class="card-body">
            <?php if (!empty($disks)): ?>
                <?php
                $totalStorageGB = array_sum(array_column($disks, 'size_gb'));
                $firstDisk = $disks[0];
                ?>
                <p class="hw-block-model"><?= htmlspecialchars($firstDisk['model'] ?? 'Disque') ?><?= count($disks) > 1 ? ' + ' . (count($disks)-1) . ' autre(s)' : '' ?></p>
                <p class="hw-block-highlight"><span class="hw-block-highlight-value"><?= number_format($totalStorageGB, 1) ?></span> <span class="hw-block-highlight-unit">GB total</span></p>
                <div class="hw-details hw-details-storage">
                    <?php foreach ($disks as $disk): ?>
                    <div class="disk-block-compact">
                        <div class="hw-detail-item"><span class="hw-detail-label"><?= htmlspecialchars($disk['model'] ?? 'Disque') ?></span><span class="hw-detail-value"><?= number_format($disk['size_gb'], 1) ?> GB</span></div>
                        <?php foreach ($disk['partitions'] ?? [] as $p): $pct = $p['usage_percentage'] ?? 0; ?>
                        <div class="partition-line">
                            <span class="p-drive"><?= htmlspecialchars($p['drive_letter']) ?></span>
                            <span class="p-pct"><?= $pct ?>%</span>
                            <div class="p-bar"><div class="p-bar-fill" style="width:<?= min(100, $pct) ?>%"></div></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <span class="text-muted">Non détecté</span>
                <p class="hw-block-extra mt-1">Relancez l'agent d'inventaire sur ce serveur pour collecter les disques.</p>
            <?php endif; ?>
        </div>
    </div>
    <!-- Carte mère -->
    <div class="card card-block hw-card hw-card-block">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-microchip"></i> Carte mère</h2></div>
        <div class="card-body">
            <p class="hw-block-model"><?= htmlspecialchars(trim(($server['model_brand'] ?? '') . ' ' . ($server['model_name'] ?? '')) ?: '—') ?></p>
        </div>
    </div>
    <!-- Réseau -->
    <div class="card card-block hw-card hw-card-block">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-network-wired"></i> Réseau</h2></div>
        <div class="card-body">
            <p class="hw-block-model">Adresse IP</p>
            <p class="hw-block-highlight"><span class="hw-block-highlight-value hw-block-highlight-ip"><?= htmlspecialchars($server['ip_address'] ?? '—') ?></span></p>
            <div class="hw-details">
                <div class="hw-detail-item"><span class="hw-detail-label">Passerelle</span><span class="hw-detail-value"><?= htmlspecialchars($server['gateway'] ?? '—') ?></span></div>
                <div class="hw-detail-item"><span class="hw-detail-label">Masque</span><span class="hw-detail-value"><?= htmlspecialchars($server['subnet_mask'] ?? '—') ?></span></div>
                <div class="hw-detail-item"><span class="hw-detail-label">DNS</span><span class="hw-detail-value"><?= htmlspecialchars($server['dns_servers'] ?? '—') ?></span></div>
            </div>
        </div>
    </div>
</div>

<!-- ========== ONGLET LOGICIEL ========== -->
<div class="tab-panel tab-logiciel" style="display: <?= $currentTab === 'logiciel' ? 'block' : 'none' ?>; grid-column: 1 / -1;">
    <div class="card card-block card-block-full">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-box"></i> Logiciels installés<?= !empty($installedSoftware) ? ' (' . count($installedSoftware) . ')' : '' ?></h2></div>
        <div class="card-body">
            <?php if (!empty($installedSoftware)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead><tr><th>Nom</th><th>Version</th><th>Date d'installation</th></tr></thead>
                    <tbody>
                        <?php foreach ($installedSoftware as $sw): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($sw['name']) ?></strong></td>
                            <td><span class="badge bg-info"><?= htmlspecialchars($sw['version'] ?? '—') ?></span></td>
                            <td><?= !empty($sw['installation_date']) ? date('d/m/Y', strtotime($sw['installation_date'])) : '—' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted">Aucun logiciel détecté. Relancez l'agent d'inventaire sur ce serveur pour collecter les logiciels installés.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ========== ONGLET APPLICATIONS ========== -->
<div class="tab-panel tab-applications" style="display: <?= $currentTab === 'applications' ? 'block' : 'none' ?>; grid-column: 1 / -1;">
    <div class="card card-block card-block-full">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-cube"></i> Logiciels installés<?= !empty($installedSoftware) ? ' (' . count($installedSoftware) . ')' : '' ?></h2></div>
        <div class="card-body">
            <?php if (!empty($installedSoftware)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead><tr><th>Nom</th><th>Version</th><th>Date d'installation</th></tr></thead>
                    <tbody>
                        <?php foreach ($installedSoftware as $sw): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($sw['name']) ?></strong></td>
                            <td><span class="badge bg-info"><?= htmlspecialchars($sw['version'] ?? '—') ?></span></td>
                            <td><?= !empty($sw['installation_date']) ? date('d/m/Y', strtotime($sw['installation_date'])) : '—' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted">Aucun logiciel détecté. Relancez l'agent d'inventaire sur ce serveur avec device_type=server pour collecter les logiciels installés.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ========== ONGLET WINDOWS ========== -->
<?php
$windowsUpdates = $windowsUpdates ?? [];
$windowsServices = $windowsServices ?? [];
$windowsStartup = $windowsStartup ?? [];
$windowsShared = $windowsShared ?? [];
$windowsMapped = $windowsMapped ?? [];
$windowsUsers = $windowsUsers ?? [];
$windowsUserGroups = $windowsUserGroups ?? [];
$windowsLicense = $windowsLicense ?? null;
$baseWinUrl = '?page=servers&action=view&id=' . (int)($server['id'] ?? 0) . '&tab=os';
?>
<div class="tab-panel tab-os" style="display: <?= $currentTab === 'os' ? 'block' : 'none' ?>; grid-column: 1 / -1;">
    <p class="os-version-line"><i class="fas fa-windows"></i> <strong><?= htmlspecialchars(trim(($server['operating_system_name'] ?? '') . ' ' . ($server['os_version_name'] ?? ''))) ?: '—' ?></strong></p>
    <nav class="pc-tabs pc-wintabs" style="margin-top: 1rem;">
        <a href="<?= $baseWinUrl ?>&wintab=update" class="pc-tab <?= $currentWinTab === 'update' ? 'active' : '' ?>"><i class="fas fa-sync-alt"></i> Update</a>
        <a href="<?= $baseWinUrl ?>&wintab=services" class="pc-tab <?= $currentWinTab === 'services' ? 'active' : '' ?>"><i class="fas fa-cogs"></i> Services</a>
        <a href="<?= $baseWinUrl ?>&wintab=startup" class="pc-tab <?= $currentWinTab === 'startup' ? 'active' : '' ?>"><i class="fas fa-play"></i> Startup</a>
        <a href="<?= $baseWinUrl ?>&wintab=license" class="pc-tab <?= $currentWinTab === 'license' ? 'active' : '' ?>"><i class="fas fa-key"></i> License</a>
        <a href="<?= $baseWinUrl ?>&wintab=shared" class="pc-tab <?= $currentWinTab === 'shared' ? 'active' : '' ?>"><i class="fas fa-share-alt"></i> Shared</a>
        <a href="<?= $baseWinUrl ?>&wintab=mapped" class="pc-tab <?= $currentWinTab === 'mapped' ? 'active' : '' ?>"><i class="fas fa-hdd"></i> Mapped</a>
        <a href="<?= $baseWinUrl ?>&wintab=users" class="pc-tab <?= $currentWinTab === 'users' ? 'active' : '' ?>"><i class="fas fa-users"></i> Users</a>
        <a href="<?= $baseWinUrl ?>&wintab=group" class="pc-tab <?= $currentWinTab === 'group' ? 'active' : '' ?>"><i class="fas fa-users-cog"></i> Group</a>
    </nav>

    <div class="win-subpanel card card-block" data-wintab="update" style="display: <?= $currentWinTab === 'update' ? 'block' : 'none' ?>;">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-sync-alt"></i> Hotfix<?= !empty($windowsUpdates) ? ' (' . count($windowsUpdates) . ')' : '' ?></h2></div>
        <div class="card-body">
            <?php if (!empty($windowsUpdates)): ?>
            <div class="table-responsive"><table class="table table-sm table-striped">
                <thead><tr><th>KB / Hotfix</th><th>Description</th><th>Date d'installation</th></tr></thead>
                <tbody>
                    <?php foreach ($windowsUpdates as $wu): ?>
                    <tr><td><code><?= htmlspecialchars($wu['hotfix_id']) ?></code></td><td><?= htmlspecialchars($wu['description'] ?? '—') ?></td><td><?= $wu['installed_on'] ? date('d/m/Y', strtotime($wu['installed_on'])) : '—' ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
            <?php else: ?><p class="text-muted mb-0">Aucune donnée. Configurez l'agent avec device_type=server et relancez l'inventaire.</p><?php endif; ?>
        </div>
    </div>

    <div class="win-subpanel card card-block" data-wintab="services" style="display: <?= $currentWinTab === 'services' ? 'block' : 'none' ?>;">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-cogs"></i> Services Windows<?= !empty($windowsServices) ? ' (' . count($windowsServices) . ')' : '' ?></h2></div>
        <div class="card-body">
            <div class="mb-3"><input type="text" class="form-control form-control-sm" id="winServicesSearch" placeholder="Rechercher..." style="max-width: 350px;"></div>
            <?php if (!empty($windowsServices)): ?>
            <div class="table-responsive"><table class="table table-sm table-striped" id="winServicesTable">
                <thead><tr><th>Nom</th><th>Nom affiché</th><th>Description</th><th>Statut</th><th>Démarrage</th></tr></thead>
                <tbody>
                    <?php foreach ($windowsServices as $s): ?>
                    <tr data-search="<?= htmlspecialchars(strtolower(($s['description'] ?? '') . ' ' . ($s['status'] ?? '') . ' ' . ($s['name'] ?? '') . ' ' . ($s['display_name'] ?? ''))) ?>">
                        <td><code><?= htmlspecialchars($s['name']) ?></code></td>
                        <td><?= htmlspecialchars($s['display_name'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($s['description'] ?? '—') ?></td>
                        <td><span class="badge badge-<?= strtolower($s['status'] ?? '') === 'running' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($s['status'] ?? '—') ?></span></td>
                        <td><?= htmlspecialchars($s['start_type'] ?? '—') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
            <div class="mt-2 text-end"><small class="text-muted">Affichés : <strong id="winServicesCount"><?= count($windowsServices) ?></strong> sur <?= count($windowsServices) ?></small></div>
            <?php else: ?><p class="text-muted mb-0">Aucune donnée. Configurez l'agent avec device_type=server.</p><?php endif; ?>
        </div>
    </div>

    <div class="win-subpanel card card-block" data-wintab="startup" style="display: <?= $currentWinTab === 'startup' ? 'block' : 'none' ?>;">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-play"></i> Programmes au démarrage<?= !empty($windowsStartup) ? ' (' . count($windowsStartup) . ')' : '' ?></h2></div>
        <div class="card-body">
            <?php if (!empty($windowsStartup)): ?>
            <div class="table-responsive"><table class="table table-sm table-striped">
                <thead><tr><th>Nom</th><th>Commande</th><th>Emplacement</th></tr></thead>
                <tbody>
                    <?php foreach ($windowsStartup as $s): ?>
                    <tr><td><?= htmlspecialchars($s['name'] ?? '—') ?></td><td><code class="small"><?= htmlspecialchars($s['command'] ?? '—') ?></code></td><td><?= htmlspecialchars($s['location'] ?? '—') ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
            <?php else: ?><p class="text-muted mb-0">Aucun programme détecté.</p><?php endif; ?>
        </div>
    </div>

    <div class="win-subpanel card card-block" data-wintab="license" style="display: <?= $currentWinTab === 'license' ? 'block' : 'none' ?>;">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-key"></i> Licence Windows</h2></div>
        <div class="card-body">
            <?php if (!empty($windowsLicense) && ($windowsLicense['description'] || $windowsLicense['status'])): ?>
            <div class="details-grid">
                <div class="detail-item"><label>Description</label><value><?= htmlspecialchars($windowsLicense['description'] ?? '—') ?></value></div>
                <div class="detail-item"><label>Statut</label><value><span class="badge bg-success"><?= htmlspecialchars($windowsLicense['status'] ?? '—') ?></span></value></div>
            </div>
            <?php else: ?><p class="text-muted mb-0">Aucune licence détectée.</p><?php endif; ?>
        </div>
    </div>

    <div class="win-subpanel card card-block" data-wintab="shared" style="display: <?= $currentWinTab === 'shared' ? 'block' : 'none' ?>;">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-share-alt"></i> Partages sortants<?= !empty($windowsShared) ? ' (' . count($windowsShared) . ')' : '' ?></h2></div>
        <div class="card-body">
            <?php if (!empty($windowsShared)): ?>
            <div class="table-responsive"><table class="table table-sm table-striped">
                <thead><tr><th>Nom</th><th>Chemin</th><th>Description</th></tr></thead>
                <tbody>
                    <?php foreach ($windowsShared as $s): ?>
                    <tr><td><strong><?= htmlspecialchars($s['name']) ?></strong></td><td><code><?= htmlspecialchars($s['path'] ?? '—') ?></code></td><td><?= htmlspecialchars($s['description'] ?? '—') ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
            <?php else: ?><p class="text-muted mb-0">Aucun partage détecté.</p><?php endif; ?>
        </div>
    </div>

    <div class="win-subpanel card card-block" data-wintab="mapped" style="display: <?= $currentWinTab === 'mapped' ? 'block' : 'none' ?>;">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-hdd"></i> Lecteurs réseau mappés<?= !empty($windowsMapped) ? ' (' . count($windowsMapped) . ')' : '' ?></h2></div>
        <div class="card-body">
            <?php if (!empty($windowsMapped)): ?>
            <div class="table-responsive"><table class="table table-sm table-striped">
                <thead><tr><th>Lecteur</th><th>Chemin</th><th>Libellé</th></tr></thead>
                <tbody>
                    <?php foreach ($windowsMapped as $m): ?>
                    <tr><td><strong><?= htmlspecialchars($m['drive_letter'] ?? '—') ?></strong></td><td><code><?= htmlspecialchars($m['path'] ?? '—') ?></code></td><td><?= htmlspecialchars($m['label'] ?? '—') ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
            <?php else: ?><p class="text-muted mb-0">Aucun lecteur mappé.</p><?php endif; ?>
        </div>
    </div>

    <div class="win-subpanel card card-block" data-wintab="users" style="display: <?= $currentWinTab === 'users' ? 'block' : 'none' ?>;">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-users"></i> Utilisateurs<?= !empty($windowsUsers) ? ' (' . count($windowsUsers) . ')' : '' ?></h2></div>
        <div class="card-body">
            <?php if (!empty($windowsUsers)): ?>
            <div class="table-responsive"><table class="table table-sm table-striped">
                <thead><tr><th>Utilisateur</th><th>Nom complet</th><th>Type</th><th>Dernière connexion</th></tr></thead>
                <tbody>
                    <?php foreach ($windowsUsers as $u): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($u['username']) ?></strong></td>
                        <td><?= htmlspecialchars($u['full_name'] ?? '—') ?></td>
                        <td><span class="badge bg-<?= ($u['account_type'] ?? '') === 'AD' ? 'primary' : 'secondary' ?>"><?= htmlspecialchars($u['account_type'] ?? 'Local') ?></span></td>
                        <td><?= !empty($u['last_login']) ? date('d/m/Y H:i', strtotime($u['last_login'])) : '—' ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
            <?php else: ?><p class="text-muted mb-0">Aucun utilisateur détecté.</p><?php endif; ?>
        </div>
    </div>

    <div class="win-subpanel card card-block" data-wintab="group" style="display: <?= $currentWinTab === 'group' ? 'block' : 'none' ?>;">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-users-cog"></i> Groupes<?= !empty($windowsUserGroups) ? ' (' . count($windowsUserGroups) . ')' : '' ?></h2></div>
        <div class="card-body">
            <?php if (!empty($windowsUserGroups)): ?>
            <div class="table-responsive"><table class="table table-sm table-striped">
                <thead><tr><th>Groupe</th></tr></thead>
                <tbody>
                    <?php foreach ($windowsUserGroups as $g): ?>
                    <tr><td><?= htmlspecialchars($g['group_name']) ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table></div>
            <?php else: ?><p class="text-muted mb-0">Aucun groupe.</p><?php endif; ?>
        </div>
    </div>

    <div class="card card-block">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-plug"></i> Accès à distance</h2></div>
        <div class="card-body">
            <?php if (!empty($server['teamviewer_id'])): ?>
            <a href="https://start.teamviewer.com/<?= htmlspecialchars($server['teamviewer_id']) ?>" target="_blank" class="teamviewer-link"><i class="fas fa-desktop"></i> TeamViewer <?= htmlspecialchars($server['teamviewer_id']) ?></a><br>
            <?php endif; ?>
            <?php if (!empty($server['rustdesk_id'])): ?>
            <a href="<?= ($rustdeskProtocol ?? 'rustdesk') ?>://<?= htmlspecialchars($server['rustdesk_id']) ?>" class="rustdesk-link"><i class="fas fa-desktop"></i> RustDesk <?= htmlspecialchars($server['rustdesk_id']) ?></a>
            <?php endif; ?>
            <?php if (empty($server['teamviewer_id']) && empty($server['rustdesk_id'])): ?><span class="text-muted">Aucun</span><?php endif; ?>
        </div>
    </div>
</div>

<!-- ========== ONGLET RESSOURCES ========== -->
<div class="tab-panel tab-ressources" style="display: <?= $currentTab === 'ressources' ? 'block' : 'none' ?>; grid-column: 1 / -1;">
    <div class="card card-block card-block-full">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-thermometer-half"></i> CPU / RAM / Températures</h2></div>
        <div class="card-body">
            <div class="details-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                <?php if ($ramTotalGB): ?>
                <div class="detail-item">
                    <label>RAM</label>
                    <value><?= number_format($ramTotalGB, 1) ?> GB<?= $ramUsedGB > 0 ? ' (utilisé ' . number_format($ramUsedGB, 1) . ' GB, ' . $ramUsagePct . '%)' : '' ?></value>
                    <?php if ($ramUsedGB > 0): ?>
                    <div class="ram-bar mt-1"><div class="ram-bar-fill" style="width:<?= min(100, $ramUsagePct) ?>%"></div></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php
                $cpuT = isset($server['monitor_cpu_temp']) && $server['monitor_cpu_temp'] !== null ? (float)$server['monitor_cpu_temp'] : null;
                $gpuT = isset($server['monitor_gpu_temp']) && $server['monitor_gpu_temp'] !== null ? (float)$server['monitor_gpu_temp'] : null;
                ?>
                <?php if ($cpuT !== null): ?>
                <div class="detail-item"><label>CPU</label><value><i class="fas fa-microchip"></i> <?= round($cpuT, 1) ?>°C</value></div>
                <?php endif; ?>
                <?php if ($gpuT !== null): ?>
                <div class="detail-item"><label>GPU</label><value><i class="fas fa-video"></i> <?= round($gpuT, 1) ?>°C</value></div>
                <?php endif; ?>
                <?php if ($lastSeen && ($cpuT === null && $gpuT === null)): ?>
                <div class="detail-item"><label>Agent moniteur</label><value>Dernier relevé : <?= date('d/m H:i', strtotime($lastSeen . ' UTC')) ?></value></div>
                <?php endif; ?>
            </div>
            <?php if (!$ramTotalGB && $cpuT === null && $gpuT === null): ?>
            <p class="text-muted mb-0">Aucune donnée. Installez l'agent moniteur et l'agent d'inventaire (device_type=server) sur ce serveur.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ========== ONGLET RÉSEAU ========== -->
<div class="tab-panel tab-reseau" style="display: <?= $currentTab === 'reseau' ? 'block' : 'none' ?>; grid-column: 1 / -1;">
    <div class="card card-block card-block-full">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-network-wired"></i> Configuration réseau</h2></div>
        <div class="card-body">
            <div class="details-grid">
                <div class="detail-item"><label>Adresse IP</label><value><?= htmlspecialchars($server['ip_address'] ?? '—') ?></value></div>
                <div class="detail-item"><label>Passerelle</label><value><?= htmlspecialchars($server['gateway'] ?? '—') ?></value></div>
                <div class="detail-item"><label>Masque de sous-réseau</label><value><?= htmlspecialchars($server['subnet_mask'] ?? '—') ?></value></div>
                <div class="detail-item"><label>Serveurs DNS</label><value><?= htmlspecialchars($server['dns_servers'] ?? '—') ?></value></div>
            </div>
        </div>
    </div>
</div>

</div>
</div><!-- .pc-detail-fullwidth -->

<style>
.main-layout:has(.pc-detail-fullwidth) { max-width: none; width: 100%; }
.content:has(.pc-detail-fullwidth) { padding-left: 1rem; padding-right: 1rem; max-width: none; }
.pc-detail-fullwidth { width: 100%; }
.pc-detail-header { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; padding: 1rem 0; border-bottom: 1px solid #e5e7eb; }
.pc-detail-title { display: flex; align-items: flex-start; gap: 1rem; }
.pc-detail-title i { font-size: 2rem; color: #667eea; margin-top: 0.25rem; }
.pc-name { margin: 0 0 0.25rem 0; font-size: 1.5rem; font-weight: 600; }
.pc-subtitle, .pc-updated { display: block; color: #6b7280; font-size: 0.875rem; }
.pc-location-badge { margin-left: 1rem; display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.75rem; background: #f0fdf4; color: #166534; border-radius: 6px; font-size: 0.8rem; align-self: center; }
.pc-location-badge i { color: #22c55e; }
.pc-detail-badges { display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; }
.pc-badge { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.75rem; background: #f3f4f6; border-radius: 6px; font-size: 0.8rem; color: #374151; }
.pc-badge.badge-type { background: #e0e7ff; color: #4338ca; }
.pc-btn-edit, .pc-btn-back { padding: 0.4rem 0.9rem; font-size: 0.875rem; border-radius: 6px; text-decoration: none; }
.pc-btn-edit { background: #667eea; color: white; }
.pc-btn-edit:hover { background: #5568d3; color: white; }
.pc-btn-back { background: #f3f4f6; color: #374151; }
.pc-btn-back:hover { background: #e5e7eb; color: #1f2937; }
.pc-tabs { display: flex; gap: 0; border-bottom: 2px solid #e5e7eb; margin-bottom: 0; }
.pc-tab { padding: 0.75rem 1.25rem; text-decoration: none; color: #6b7280; font-weight: 500; border-bottom: 3px solid transparent; margin-bottom: -2px; transition: all 0.2s; display: flex; align-items: center; gap: 0.5rem; }
.pc-tab:hover { color: #374151; }
.pc-tab.active { color: #667eea; border-bottom-color: #667eea; }
.pc-wintabs { flex-wrap: wrap; }
.pc-wintabs .pc-tab { padding: 0.5rem 0.9rem; font-size: 0.85rem; }
.os-version-line { margin: 0 0 1rem 0; padding: 0.5rem 0; font-size: 1rem; color: #374151; }
.os-version-line i { color: #667eea; margin-right: 0.5rem; }
.home-status-card .card-header { display: flex; align-items: center; gap: 0.75rem; }
.card-block-icon { width: 2.25rem; height: 2.25rem; border-radius: 50%; background: rgba(255, 255, 255, 0.25); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.card-block-icon i { font-size: 1rem; color: white; }
.home-status-card .card-title { margin: 0; }
.home-status-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
@media (max-width: 900px) { .home-status-row { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 500px) { .home-status-row { grid-template-columns: 1fr; } }
.home-status-card .home-status-value { margin: 0 0 0.35rem 0; font-size: 1rem; }
.home-status-card .home-status-value.status-ok { color: #28a745; }
.home-status-card .home-status-value.status-warn { color: #f59e0b; }
.home-status-card .home-status-value.status-unknown { color: #6b7280; }
.home-status-card .home-status-value.status-unknown i { color: #9ca3af; }
.computer-details-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; align-items: start; }
.card { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08); border: 1px solid #e5e7eb; overflow: hidden; }
.card-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem 1.25rem; border-bottom: none; }
.card-title { margin: 0; font-size: 1rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; color: white; }
.card-title i { color: rgba(255, 255, 255, 0.9); font-size: 0.9rem; }
.card-body { padding: 1.25rem; }
.details-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; }
.detail-item { display: flex; flex-direction: column; gap: 0.5rem; }
.detail-item label { font-weight: 500; color: #374151; font-size: 0.875rem; }
.detail-item value { color: #1f2937; font-size: 1rem; }
.hw-card-cpu .hw-cpu-model { color: #374151; font-size: 0.95rem; line-height: 1.4; margin-bottom: 0.5rem; }
.hw-card-cpu .hw-cpu-speed { margin: 0.5rem 0; font-size: 1.75rem; font-weight: 600; color: #667eea; }
.hw-card-cpu .hw-cpu-speed-value { font-size: 1.75rem; color: #667eea; }
.hw-card-cpu .hw-cpu-speed-unit { font-size: 1rem; color: #667eea; font-weight: 500; margin-left: 0.15rem; }
.hw-card-cpu .hw-cpu-details { display: flex; gap: 1.5rem; margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid #e5e7eb; }
.hw-card-cpu .hw-cpu-detail-item { display: flex; flex-direction: column; gap: 0.25rem; }
.hw-card-cpu .hw-cpu-detail-label { font-size: 0.75rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.02em; }
.hw-card-cpu .hw-cpu-detail-value { font-size: 0.9rem; color: #374151; }
.hw-card-block .hw-block-model { color: #374151; font-size: 0.95rem; margin-bottom: 0.5rem; }
.hw-card-block .hw-block-highlight { margin: 0.5rem 0; font-size: 1.75rem; font-weight: 600; color: #667eea; }
.hw-card-block .hw-block-highlight-value { font-size: 1.75rem; color: #667eea; }
.hw-card-block .hw-block-highlight-value.hw-block-highlight-ip { font-size: 1.1rem; font-family: monospace; }
.hw-card-block .hw-block-highlight-unit { font-size: 1rem; color: #667eea; font-weight: 500; margin-left: 0.15rem; }
.hw-card-block .hw-block-extra { font-size: 0.85rem; color: #6b7280; display: block; margin-top: 0.25rem; }
.hw-card-block .hw-details { display: flex; flex-wrap: wrap; gap: 1rem 1.5rem; margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid #e5e7eb; }
.hw-card-block .hw-detail-item { display: flex; flex-direction: column; gap: 0.25rem; }
.hw-card-block .hw-detail-label { font-size: 0.75rem; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.02em; }
.hw-card-block .hw-detail-value { font-size: 0.9rem; color: #374151; }
.mt-1 { margin-top: 0.25rem; }
.ram-bar { width: 100%; height: 8px; background-color: #e5e7eb; border-radius: 4px; overflow: hidden; margin-top: 0.5rem; }
.ram-bar-fill { height: 100%; background: linear-gradient(90deg, #10b981 0%, #f59e0b 70%, #ef4444 90%); border-radius: 4px; transition: width 0.3s ease; }
.hw-card-block .hw-details-storage { flex-direction: column; gap: 0.5rem; }
.hw-card-storage .disk-block-compact { margin-bottom: 0.75rem; }
.hw-card-storage .disk-block-compact:last-child { margin-bottom: 0; }
.hw-card-storage .partition-line { display: flex; align-items: center; gap: 0.35rem; margin: 0.25rem 0 0 0.5rem; font-size: 0.75rem; }
.hw-card-storage .p-drive { font-weight: 600; color: #667eea; min-width: 2rem; }
.hw-card-storage .p-pct { color: #6b7280; min-width: 2.2rem; }
.hw-card-storage .p-bar { flex: 1; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden; }
.hw-card-storage .p-bar-fill { height: 100%; background: linear-gradient(90deg, #10b981 0%, #f59e0b 70%, #ef4444 90%); border-radius: 4px; transition: width 0.3s ease; }
.rustdesk-link { text-decoration: none; color: #0891b2; display: inline-flex; align-items: center; gap: 0.5rem; }
.rustdesk-link:hover { color: #0e7490; text-decoration: none; }
.teamviewer-link { text-decoration: none; }
.teamviewer-id { background: #f3f4f6; padding: 0.5rem 1rem; border-radius: 6px; font-family: monospace; display: inline-flex; align-items: center; gap: 0.5rem; color: #374151; }
.teamviewer-link:hover .teamviewer-id { color: #0891b2; }
.external-icon { font-size: 0.75rem; opacity: 0.7; }
.text-muted { color: #9ca3af; }
.badge { padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.75rem; font-weight: 500; display: inline-block; }
.card-block-full { grid-column: 1 / -1; }
@media (max-width: 1200px) { .tab-hardware { grid-template-columns: repeat(2, 1fr) !important; } }
@media (max-width: 640px) { .tab-hardware { grid-template-columns: 1fr !important; } }
</style>

<script>
(function() {
    const container = document.querySelector('.rustdesk-status');
    if (!container) return;
    const rustdeskId = container.dataset.rustdeskId;
    const badge = document.getElementById('rustdesk-status-badge');
    const btnCheck = document.getElementById('btn-check-online');
    const btnRefresh = document.getElementById('btn-refresh-page');
    if (!badge || !btnCheck || !btnRefresh) return;

    function checkRustDeskPro() {
        if (!rustdeskId) return;
        btnCheck.disabled = true;
        badge.textContent = 'Vérification…';
        badge.className = 'badge badge-secondary';
        const pathDir = window.location.pathname.replace(/\/[^/]*$/, '') || '/';
        const apiBase = pathDir.endsWith('/') ? pathDir : pathDir + '/';
        fetch(apiBase + 'api/rustdesk_status.php?id=' + encodeURIComponent(rustdeskId))
            .then(r => r.json())
            .then(function(data) {
                if (data.error) {
                    badge.textContent = data.error;
                    badge.className = 'badge badge-warning';
                    badge.title = data.error;
                } else if (data.online) {
                    badge.textContent = 'En ligne (RustDesk Pro)';
                    badge.className = 'badge badge-success';
                } else {
                    badge.textContent = 'Hors ligne';
                    badge.className = 'badge badge-danger';
                }
            })
            .catch(function() {
                badge.textContent = 'Erreur réseau';
                badge.className = 'badge badge-warning';
            })
            .finally(function() { btnCheck.disabled = false; });
    }
    btnCheck.addEventListener('click', checkRustDeskPro);
    btnRefresh.addEventListener('click', function() { window.location.reload(); });
    checkRustDeskPro();
})();

document.addEventListener('DOMContentLoaded', function() {
    const winServicesSearch = document.getElementById('winServicesSearch');
    const winServicesTable = document.getElementById('winServicesTable');
    const winServicesCount = document.getElementById('winServicesCount');
    if (winServicesSearch && winServicesTable && winServicesTable.tBodies[0]) {
        const totalWinServices = winServicesTable.tBodies[0].rows.length;
        winServicesSearch.addEventListener('keyup', function() {
            const term = this.value.toLowerCase().trim();
            const rows = winServicesTable.tBodies[0].rows;
            let n = 0;
            for (let i = 0; i < rows.length; i++) {
                const matches = !term || (rows[i].dataset.search || '').includes(term);
                rows[i].style.display = matches ? '' : 'none';
                if (matches) n++;
            }
            if (winServicesCount) winServicesCount.textContent = n;
        });
    }
});
</script>
