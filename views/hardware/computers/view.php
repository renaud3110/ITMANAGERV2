<?php
$currentTab = $_GET['tab'] ?? 'home';
$validTabs = ['home', 'hardware', 'applications', 'os', 'ressources'];
if (!in_array($currentTab, $validTabs)) $currentTab = 'home';
$currentWinTab = $_GET['wintab'] ?? 'update';
$validWinTabs = ['update', 'services', 'startup', 'license', 'shared', 'mapped', 'users', 'group'];
if (!in_array($currentWinTab, $validWinTabs)) $currentWinTab = 'update';
$lastSeen = $computer['monitor_last_seen'] ?? null;
$monOnline = $lastSeen && (time() - strtotime($lastSeen . ' UTC')) < 60;
$osName = $computer['operating_system_name'] ?? 'Windows';
$isLinux = (stripos($osName, 'Linux') !== false || stripos($osName, 'Ubuntu') !== false || stripos($osName, 'Mint') !== false);
?>
<div class="pc-detail-fullwidth">
<div class="pc-detail-header">
    <div class="pc-detail-title">
        <i class="fas fa-desktop"></i>
        <div>
            <h1 class="pc-name"><?= htmlspecialchars($computer['name'] ?? 'Sans nom') ?></h1>
            <span class="pc-subtitle">Ordinateur <?= htmlspecialchars($computer['name'] ?? '') ?></span>
            <span class="pc-updated">Mis à jour <?= $computer['updated_at'] ? date('d M Y, H:i', strtotime($computer['updated_at'])) : '—' ?></span>
        </div>
        <?php if (!empty($computer['tenant_name']) || !empty($computer['site_name'])): ?>
        <span class="pc-location-badge" title="Localisation">
            <i class="fas fa-map-marker-alt"></i>
            <?= htmlspecialchars(trim(($computer['tenant_name'] ?? '') . ($computer['site_name'] ? ' • ' . $computer['site_name'] : ''))) ?: '—' ?>
        </span>
        <?php endif; ?>
    </div>
    <div class="pc-detail-badges">
        <span class="pc-badge <?= $monOnline ? 'badge-online' : 'badge-offline' ?>" title="Statut">
            <i class="fas fa-<?= $monOnline ? 'check-circle' : 'circle' ?>"></i>
            <?= $monOnline ? 'En ligne' : 'Hors ligne' ?>
        </span>
        <span class="pc-badge badge-owner" title="Propriétaire">
            <i class="fas fa-user"></i>
            <?php if ($computer['person_prenom'] || $computer['person_nom']): ?>
                <?= htmlspecialchars(trim(($computer['person_prenom'] ?? '') . ' ' . ($computer['person_nom'] ?? ''))) ?>
            <?php else: ?>
                Non attribué
            <?php endif; ?>
        </span>
        <a href="?page=hardware&section=computers&action=edit&id=<?= $computer['id'] ?>" class="pc-btn-edit">
            <i class="fas fa-edit"></i> Modifier
        </a>
        <a href="?page=hardware&section=computers" class="pc-btn-back">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

<!-- Navigation par onglets -->
<nav class="pc-tabs">
    <a href="?page=hardware&section=computers&action=view&id=<?= $computer['id'] ?>&tab=home" class="pc-tab <?= $currentTab === 'home' ? 'active' : '' ?>">
        <i class="fas fa-home"></i> Home
    </a>
    <a href="?page=hardware&section=computers&action=view&id=<?= $computer['id'] ?>&tab=hardware" class="pc-tab <?= $currentTab === 'hardware' ? 'active' : '' ?>">
        <i class="fas fa-microchip"></i> Hardware
    </a>
    <a href="?page=hardware&section=computers&action=view&id=<?= $computer['id'] ?>&tab=applications" class="pc-tab <?= $currentTab === 'applications' ? 'active' : '' ?>">
        <i class="fas fa-cube"></i> Applications
    </a>
    <a href="?page=hardware&section=computers&action=view&id=<?= $computer['id'] ?>&tab=os" class="pc-tab <?= $currentTab === 'os' ? 'active' : '' ?>">
        <i class="fas fa-<?= $isLinux ? 'linux' : 'windows' ?>"></i> <?= $isLinux ? 'Linux' : 'Windows' ?>
    </a>
    <a href="?page=hardware&section=computers&action=view&id=<?= $computer['id'] ?>&tab=ressources" class="pc-tab <?= $currentTab === 'ressources' ? 'active' : '' ?>">
        <i class="fas fa-thermometer-half"></i> Ressources
    </a>
</nav>

<div class="computer-details computer-details-grid" style="margin-top: 1.5rem;">

<!-- ========== ONGLET HOME ========== -->
<div class="tab-panel tab-home" style="display: <?= $currentTab === 'home' ? 'block' : 'none' ?>; grid-column: 1 / -1;">
    <!-- Première ligne : 4 blocs Connectivité, Antivirus, Firewall, Santé -->
    <div class="home-status-row">
    <div class="card card-block home-status-card">
        <div class="card-header"><span class="card-block-icon"><i class="fas fa-wifi"></i></span><h2 class="card-title">Connectivité</h2></div>
        <div class="card-body">
            <?php if ($lastSeen || isset($computer['monitor_cpu_temp'])): ?>
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
                <small class="text-muted">Installez l'agent moniteur sur ce PC</small>
            <?php endif; ?>
        </div>
    </div>
    <div class="card card-block home-status-card">
        <div class="card-header"><span class="card-block-icon"><i class="fas fa-shield-alt"></i></span><h2 class="card-title">Antivirus</h2></div>
        <div class="card-body">
            <?php
            $avName = $computer['antivirus_name'] ?? null;
            $avEnabled = isset($computer['antivirus_enabled']) ? (bool)$computer['antivirus_enabled'] : null;
            $avUpdated = isset($computer['antivirus_updated']) ? (bool)$computer['antivirus_updated'] : null;
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
            $fwEnabled = isset($computer['firewall_enabled']) ? (bool)$computer['firewall_enabled'] : null;
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
            $cpuT = isset($computer['monitor_cpu_temp']) && $computer['monitor_cpu_temp'] !== null ? (float)$computer['monitor_cpu_temp'] : null;
            $gpuT = isset($computer['monitor_gpu_temp']) && $computer['monitor_gpu_temp'] !== null ? (float)$computer['monitor_gpu_temp'] : null;
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
    <!-- Deuxième ligne : Logiciel, Hardware, RustDesk, Info Users -->
    <div class="home-status-row">
    <div class="card card-block home-status-card">
        <div class="card-header"><span class="card-block-icon"><i class="fas fa-cog"></i></span><h2 class="card-title">Logiciel</h2></div>
        <div class="card-body">
            <p class="home-status-value"><strong><?= htmlspecialchars($computer['operating_system_name'] ?? 'Non défini') ?></strong> <?= htmlspecialchars($computer['os_version_name'] ?? '') ?></p>
            <small class="text-muted">Logiciels installés : <?= count($installedSoftware ?? []) ?></small>
        </div>
    </div>
    <div class="card card-block home-status-card">
        <div class="card-header"><span class="card-block-icon"><i class="fas fa-microchip"></i></span><h2 class="card-title">Hardware</h2></div>
        <div class="card-body">
            <p class="home-status-value"><strong><?= htmlspecialchars($computer['model_brand'] ?? '') ?></strong> <?= htmlspecialchars($computer['model_name'] ?? '') ?></p>
            <small><?= htmlspecialchars($computer['processor_model'] ?? '') ?></small>
            <?php if ($computer['ram_total'] && $computer['ram_total'] > 0): ?>
                <?php $ramGB = $computer['ram_total'] > 1e9 ? $computer['ram_total']/1024/1024/1024 : $computer['ram_total']/1024; ?>
                <p class="mb-0 mt-1"><i class="fas fa-memory"></i> <?= number_format($ramGB, 1) ?> GB RAM</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="card card-block home-status-card">
        <div class="card-header"><span class="card-block-icon"><i class="fas fa-desktop"></i></span><h2 class="card-title">RustDesk</h2></div>
        <div class="card-body">
            <?php if ($computer['rustdesk_id']): ?>
                <p class="home-status-value"><strong>ID <?= htmlspecialchars($computer['rustdesk_id']) ?></strong></p>
                <a href="<?= ($rustdeskProtocol ?? 'rustdesk') ?>://<?= htmlspecialchars($computer['rustdesk_id']) ?>" class="rustdesk-link btn btn-sm btn-outline-secondary" title="Ouvrir avec RustDesk/supportrgd. Installez le client sur cette machine pour vous connecter."><i class="fas fa-desktop"></i> Ouvrir</a>
                <div class="rustdesk-status mt-2" data-rustdesk-id="<?= htmlspecialchars($computer['rustdesk_id']) ?>">
                    <span id="rustdesk-status-badge" class="badge badge-secondary">Non vérifié</span>
                    <button type="button" class="btn btn-outline-primary btn-sm ms-1" id="btn-check-online" title="Vérifier"><i class="fas fa-sync-alt"></i></button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="btn-refresh-page" title="Rafraîchir"><i class="fas fa-redo"></i></button>
                </div>
            <?php else: ?>
                <p class="home-status-value status-unknown"><i class="fas fa-question-circle"></i> <strong>Non configuré</strong></p>
                <small class="text-muted">Configurez RustDesk sur ce PC</small>
            <?php endif; ?>
        </div>
    </div>
    <div class="card card-block home-status-card">
        <div class="card-header"><span class="card-block-icon"><i class="fas fa-users"></i></span><h2 class="card-title">Info Users</h2></div>
        <div class="card-body">
            <p class="home-status-value"><strong>Dernier compte :</strong> <?= htmlspecialchars($computer['last_account'] ?? '—') ?></p>
            <?php if (!empty($computer['last_account_created_at'])): ?>
                <small class="text-muted">Profil créé le <?= date('d/m/Y', strtotime($computer['last_account_created_at'])) ?></small><br>
            <?php endif; ?>
            <?php
            $monLoggedIn = isset($computer['monitor_logged_in']) ? (int)$computer['monitor_logged_in'] : null;
            $monLogoutAt = $computer['monitor_last_logout_at'] ?? null;
            if ($monLoggedIn === 1): ?>
                <p class="mb-1 status-ok"><i class="fas fa-check-circle"></i> Toujours connecté<?= !empty($computer['monitor_logged_in_username']) ? ' : <strong>' . htmlspecialchars($computer['monitor_logged_in_username']) . '</strong>' : '' ?></p>
            <?php elseif ($monLoggedIn === 0 && $monLogoutAt): ?>
                <p class="mb-1 status-warn"><i class="fas fa-sign-out-alt"></i> Déconnecté depuis <?= date('d/m/Y H:i', strtotime($monLogoutAt)) ?></p>
            <?php elseif ($monLoggedIn === 0): ?>
                <p class="mb-1 status-warn"><i class="fas fa-sign-out-alt"></i> Déconnecté</p>
            <?php else: ?>
                <p class="mb-1 text-muted"><i class="fas fa-minus-circle"></i> Non détecté</p>
            <?php endif; ?>
            <hr class="my-2">
            <p class="mb-0"><strong>Attribué à :</strong><br>
            <?php if ($computer['person_nom'] || $computer['person_prenom']): ?>
                <?= htmlspecialchars(trim(($computer['person_prenom'] ?? '') . ' ' . ($computer['person_nom'] ?? ''))) ?>
                <?php if ($computer['person_email']): ?><br><small><?= htmlspecialchars($computer['person_email']) ?></small><?php endif; ?>
            <?php else: ?><span class="text-muted">Non attribué</span><?php endif; ?>
            </p>
        </div>
    </div>
    </div>
</div>

<!-- ========== ONGLET HARDWARE ========== -->
<div class="tab-panel tab-hardware" style="display: <?= $currentTab === 'hardware' ? 'grid' : 'none' ?>; grid-column: 1 / -1; grid-template-columns: repeat(4, 1fr); gap: 1rem;">
    <!-- Ligne 1 : CPU, RAM, Stockage, GPU -->
    <!-- CPU -->
    <div class="card card-block hw-card hw-card-cpu hw-card-block">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-microchip"></i> CPU</h2></div>
        <div class="card-body">
            <?php
            $procModel = $computer['processor_model'] ?? '';
            $procCores = isset($computer['processor_cores']) && $computer['processor_cores'] > 0 ? (int)$computer['processor_cores'] : null;
            $procMhz = isset($computer['processor_speed_mhz']) && $computer['processor_speed_mhz'] > 0 ? (float)$computer['processor_speed_mhz'] : null;
            $procSpeedGhz = $procMhz ? round($procMhz / 1000, 2) : null;
            // Fallback: extraire GHz du processor_model (ex: "@ 2.80GHz") si pas en BDD
            if (!$procSpeedGhz && $procModel && preg_match('/@\s*([\d.]+)\s*GHz/i', $procModel, $m)) {
                $procSpeedGhz = (float)$m[1];
            }
            $procVendor = $computer['processor_manufacturer'] ?? null;
            $procFamily = $computer['processor_family'] ?? null;
            // Ligne principale : modèle (contient souvent @ X.XXGHz) + x N cores si dispo
            $mainLine = $procModel ?: 'Non détecté';
            if ($procModel && $procCores && strpos($procModel, ' x ') === false && strpos($procModel, ' cores') === false) {
                $mainLine .= ' x ' . $procCores . ' cores';
            }
            ?>
            <p class="hw-cpu-model"><?= htmlspecialchars($mainLine) ?></p>
            <?php if ($procSpeedGhz): ?>
            <p class="hw-cpu-speed"><span class="hw-cpu-speed-value"><?= number_format($procSpeedGhz, 2) ?></span> <span class="hw-cpu-speed-unit">GHz</span></p>
            <?php endif; ?>
            <div class="hw-cpu-details">
                <div class="hw-cpu-detail-item">
                    <span class="hw-cpu-detail-label">Manufacturer</span>
                    <span class="hw-cpu-detail-value"><?= htmlspecialchars($procVendor ?: '—') ?></span>
                </div>
                <div class="hw-cpu-detail-item">
                    <span class="hw-cpu-detail-label">Family</span>
                    <span class="hw-cpu-detail-value"><?= htmlspecialchars($procFamily ?: '—') ?></span>
                </div>
            </div>
        </div>
    </div>
    <!-- RAM -->
    <div class="card card-block hw-card hw-card-block">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-memory"></i> RAM</h2></div>
        <div class="card-body">
            <?php if ($computer['ram_total'] && $computer['ram_total'] > 0): ?>
                <?php 
                $ramTotalGB = $computer['ram_total'] > 1e9 ? $computer['ram_total']/1024/1024/1024 : $computer['ram_total']/1024;
                $ramUsedGB = ($computer['ram_used'] && $computer['ram_used'] > 0) ? ($computer['ram_used'] > 1e9 ? $computer['ram_used']/1024/1024/1024 : $computer['ram_used']/1024) : 0;
                $usagePct = $ramUsedGB > 0 ? ($ramUsedGB/$ramTotalGB)*100 : 0;
                $ramType = $computer['ram_type'] ?? null;
                $ramModel = $computer['ram_model'] ?? null;
                $ramFreq = isset($computer['ram_frequency_mhz']) && $computer['ram_frequency_mhz'] > 0 ? (int)$computer['ram_frequency_mhz'] : null;
                $ramMainLine = $ramType ? $ramType : 'Mémoire';
                ?>
                <p class="hw-block-model"><?= htmlspecialchars($ramMainLine) ?></p>
                <p class="hw-block-highlight"><span class="hw-block-highlight-value"><?= number_format($ramTotalGB, 1) ?></span> <span class="hw-block-highlight-unit">GB</span></p>
                <?php if ($ramUsedGB > 0): ?>
                <div class="ram-bar"><div class="ram-bar-fill" style="width:<?= $usagePct ?>%"></div></div>
                <small class="hw-block-extra">Utilisé <?= number_format($ramUsedGB, 1) ?> GB (<?= round($usagePct, 0) ?>%)</small>
                <?php endif; ?>
                <div class="hw-details">
                    <?php 
                    $ramTypeFreq = trim(($ramType ?: '') . ($ramType && $ramFreq ? ' · ' : '') . ($ramFreq ? $ramFreq . ' MHz' : ''));
                    if ($ramTypeFreq !== ''): ?><div class="hw-detail-item"><span class="hw-detail-label">Type</span><span class="hw-detail-value"><?= htmlspecialchars($ramTypeFreq) ?></span></div><?php endif; ?>
                    <?php if ($ramModel): ?><div class="hw-detail-item"><span class="hw-detail-label">Modèle</span><span class="hw-detail-value"><?= htmlspecialchars($ramModel) ?></span></div><?php endif; ?>
                </div>
            <?php else: ?><span class="text-muted">Non détecté</span><?php endif; ?>
        </div>
    </div>
    <!-- Stockage (disques + partitions) -->
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
            <?php else: ?><span class="text-muted">Aucun disque</span><?php endif; ?>
        </div>
    </div>
    <!-- GPU -->
    <div class="card card-block hw-card hw-card-block">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-video"></i> Carte(s) graphique(s)</h2></div>
        <div class="card-body">
            <?php if (!empty($gpus)): ?>
                <?php foreach ($gpus as $idx => $gpu): ?>
                <?php if ($idx > 0): ?><hr class="hw-block-sep"><?php endif; ?>
                <p class="hw-block-model"><?= htmlspecialchars($gpu['model'] ?? '—') ?></p>
                <?php 
                $vramGB = !empty($gpu['vram_bytes']) && $gpu['vram_bytes'] > 0 ? round($gpu['vram_bytes'] / (1024*1024*1024), 2) : null;
                if ($vramGB !== null): 
                ?><p class="hw-block-highlight"><span class="hw-block-highlight-value"><?= number_format($vramGB, 2) ?></span> <span class="hw-block-highlight-unit">GB VRAM</span></p><?php endif; ?>
                <div class="hw-details">
                    <?php if (!empty($gpu['vendor'])): ?><div class="hw-detail-item"><span class="hw-detail-label">Constructeur</span><span class="hw-detail-value"><?= htmlspecialchars($gpu['vendor']) ?></span></div><?php endif; ?>
                    <?php if (!empty($gpu['driver_version'])): ?><div class="hw-detail-item"><span class="hw-detail-label">Pilote</span><span class="hw-detail-value"><?= htmlspecialchars($gpu['driver_version']) ?></span></div><?php endif; ?>
                    <?php if (!empty($gpu['video_processor'])): ?><div class="hw-detail-item"><span class="hw-detail-label">Processeur vidéo</span><span class="hw-detail-value"><?= htmlspecialchars($gpu['video_processor']) ?></span></div><?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php elseif (isset($computer['monitor_gpu_temp']) && $computer['monitor_gpu_temp']): ?>
                <span class="text-muted"><?= round($computer['monitor_gpu_temp'], 0) ?>°C (moniteur)</span>
            <?php else: ?>
                <span class="text-muted">Non détecté</span>
            <?php endif; ?>
        </div>
    </div>
    <!-- Ligne 2 : Carte mère, Réseau, Monitors, Imprimantes -->
    <!-- Carte mère -->
    <div class="card card-block hw-card hw-card-block">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-microchip"></i> Carte mère</h2></div>
        <div class="card-body">
            <?php $mbModel = trim(($computer['model_brand'] ?? '') . ' ' . ($computer['model_name'] ?? '')); ?>
            <p class="hw-block-model"><?= htmlspecialchars($mbModel ?: '—') ?></p>
            <div class="hw-details">
                <div class="hw-detail-item"><span class="hw-detail-label">S/N carte mère</span><span class="hw-detail-value"><?= htmlspecialchars($computer['motherboard_serial'] ?? $computer['serial_number'] ?? '—') ?></span></div>
                <div class="hw-detail-item"><span class="hw-detail-label">BIOS</span><span class="hw-detail-value"><?= htmlspecialchars($computer['bios_version'] ?? '—') ?></span></div>
            </div>
        </div>
    </div>
    <!-- Réseau -->
    <div class="card card-block hw-card hw-card-block">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-network-wired"></i> Réseau</h2></div>
        <div class="card-body">
            <?php if (!empty($networkAdapters)): ?>
                <?php
                $is169 = function($ip) { return strpos($ip ?? '', '169.') === 0; };
                usort($networkAdapters, function($a, $b) use ($is169) {
                    $a169 = $is169($a['ip_cidr'] ?? '');
                    $b169 = $is169($b['ip_cidr'] ?? '');
                    if ($a169 === $b169) return 0;
                    return $a169 ? 1 : -1;
                });
                $firstAdapter = $networkAdapters[0];
                ?>
                <p class="hw-block-model"><?= htmlspecialchars($firstAdapter['name'] ?? '—') ?><?= count($networkAdapters) > 1 ? ' + ' . (count($networkAdapters)-1) . ' autre(s)' : '' ?></p>
                <?php if (!empty($firstAdapter['ip_cidr']) && !$is169($firstAdapter['ip_cidr'])): ?>
                <p class="hw-block-highlight"><span class="hw-block-highlight-value hw-block-highlight-ip"><?= htmlspecialchars($firstAdapter['ip_cidr']) ?></span></p>
                <?php elseif (!empty($firstAdapter['ip_cidr'])): ?>
                <p class="hw-block-highlight"><span class="hw-block-highlight-value hw-block-highlight-ip text-muted">Non connectée</span></p>
                <?php endif; ?>
                <ul class="list-unstyled mb-0 network-adapters-list hw-details-list">
                <?php foreach ($networkAdapters as $adapter): 
                    $adapter169 = !empty($adapter['ip_cidr']) && $is169($adapter['ip_cidr']);
                ?>
                    <li class="network-adapter-item hw-detail-row <?= $adapter169 ? 'network-adapter-not-connected' : '' ?>">
                        <span class="adapter-type-badge badge <?= (strtolower($adapter['type'] ?? '') === 'wi-fi') ? 'badge-info' : 'badge-secondary' ?>"><?= htmlspecialchars($adapter['type'] ?? 'Réseau') ?></span>
                        <span class="hw-detail-value"><?= htmlspecialchars($adapter['name'] ?? '—') ?></span>
                        <?php if (!empty($adapter['ip_cidr'])): ?><span class="adapter-ip"><?= htmlspecialchars($adapter['ip_cidr']) ?></span><?php if ($adapter169): ?><span class="badge badge-warning badge-sm">Non connectée</span><?php endif; ?><?php endif; ?>
                        <?php if (!empty($adapter['gateway']) && !$adapter169): ?><small class="hw-detail-label">Passerelle <?= htmlspecialchars($adapter['gateway']) ?></small><?php endif; ?>
                        <?php if (!empty($adapter['wifi_ssid']) && !$adapter169): ?><small><i class="fas fa-wifi"></i> <?= htmlspecialchars($adapter['wifi_ssid']) ?></small><?php endif; ?>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="hw-block-model"><?= htmlspecialchars($computer['ip_address'] ?? '—') ?></p>
                <div class="hw-details"><div class="hw-detail-item"><span class="hw-detail-label">Passerelle</span><span class="hw-detail-value"><?= htmlspecialchars($computer['gateway'] ?? '—') ?></span></div></div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Monitors (écrans) -->
    <div class="card card-block hw-card hw-card-block">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-tv"></i> Moniteurs</h2></div>
        <div class="card-body">
            <?php if (!empty($monitors)): ?>
                <?php $firstMon = $monitors[0]; ?>
                <p class="hw-block-model"><?= htmlspecialchars($firstMon['name'] ?? '—') ?><?= count($monitors) > 1 ? ' + ' . (count($monitors)-1) . ' autre(s)' : '' ?></p>
                <?php if (!empty($firstMon['resolution'])): ?>
                <p class="hw-block-highlight"><span class="hw-block-highlight-value"><?= htmlspecialchars($firstMon['resolution']) ?></span></p>
                <?php endif; ?>
                <div class="hw-details">
                    <?php foreach ($monitors as $mon): ?>
                    <div class="hw-detail-item">
                        <span class="hw-detail-label"><?= htmlspecialchars($mon['name'] ?? '—') ?></span>
                        <span class="hw-detail-value"><?= htmlspecialchars($mon['resolution'] ?? '—') ?><?= !empty($mon['manufacturer']) ? ' · ' . htmlspecialchars($mon['manufacturer']) : '' ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <span class="text-muted">Aucun moniteur</span>
            <?php endif; ?>
        </div>
    </div>
    <!-- Imprimantes -->
    <div class="card card-block hw-card hw-card-block">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-print"></i> Imprimantes</h2></div>
        <div class="card-body">
            <?php if (!empty($printers)): ?>
                <?php $firstPrn = $printers[0]; ?>
                <p class="hw-block-model"><?= htmlspecialchars($firstPrn['name']) ?><?= count($printers) > 1 ? ' + ' . (count($printers)-1) . ' autre(s)' : '' ?></p>
                <p class="hw-block-highlight"><span class="hw-block-highlight-value"><?= count($printers) ?></span> <span class="hw-block-highlight-unit">imprimante(s)</span></p>
                <div class="hw-details">
                    <?php foreach ($printers as $prn): ?>
                    <div class="hw-detail-item">
                        <span class="hw-detail-label"><?= htmlspecialchars($prn['name']) ?><?php if (!empty($prn['is_default'])): ?> <span class="badge badge-primary badge-sm">Défaut</span><?php endif; ?><?php if (!empty($prn['is_shared'])): ?> <span class="badge badge-secondary badge-sm">Partagée</span><?php endif; ?></span>
                        <span class="hw-detail-value"><?= htmlspecialchars(trim(($prn['driver'] ?? '') . (!empty($prn['driver']) && !empty($prn['port']) ? ' — ' : '') . ($prn['port'] ?? ''))) ?: '—' ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <span class="text-muted">Aucune imprimante</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ========== ONGLET RESSOURCES (températures) ========== -->
<div class="tab-panel tab-ressources" style="display: <?= $currentTab === 'ressources' ? 'block' : 'none' ?>; grid-column: 1 / -1;">
    <div class="card card-block card-block-full">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-thermometer-half"></i> Températures CPU / GPU</h2></div>
        <div class="card-body">
            <div id="temperatureChartContainer"><canvas id="temperatureChart" height="200"></canvas></div>
            <div id="temperatureHistoryTable" class="mt-4" style="display: none;">
                <h6 class="mb-3"><i class="fas fa-history"></i> Historique des relevés</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead><tr><th>Date / Heure</th><th><i class="fas fa-microchip"></i> CPU (°C)</th><th><i class="fas fa-video"></i> GPU (°C)</th></tr></thead>
                        <tbody id="temperatureHistoryBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========== ONGLET APPLICATIONS ========== -->
<div class="tab-panel tab-applications" style="display: <?= $currentTab === 'applications' ? 'block' : 'none' ?>; grid-column: 1 / -1;">
    <div class="card card-block card-block-full">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-cube"></i> Logiciels installés (<?= count($installedSoftware ?? []) ?>)</h2></div>
        <div class="card-body">
            <div class="mb-3">
                <input type="text" class="form-control form-control-sm" id="globalSoftwareSearch" placeholder="Rechercher..." style="max-width:300px;">
            </div>
            <?php if (!empty($installedSoftware)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead><tr><th>Nom</th><th>Version</th><th>Date d'installation</th></tr></thead>
                    <tbody id="softwareTableBody">
                        <?php foreach ($installedSoftware as $sw): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($sw['name']) ?></strong></td>
                            <td><span class="badge bg-info"><?= htmlspecialchars($sw['version']) ?></span></td>
                            <td><?= $sw['installation_date'] ? date('d/m/Y', strtotime($sw['installation_date'])) : '—' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-2 text-end"><small class="text-muted">Affichés : <strong id="softwareCount"><?= count($installedSoftware) ?></strong> sur <?= count($installedSoftware) ?></small></div>
            <?php else: ?>
            <p class="text-muted">Aucun logiciel détecté.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ========== ONGLET WINDOWS / LINUX ========== -->
<div class="tab-panel tab-os" style="display: <?= $currentTab === 'os' ? 'block' : 'none' ?>; grid-column: 1 / -1;">
    <p class="os-version-line"><i class="fas fa-<?= $isLinux ? 'linux' : 'windows' ?>"></i> <strong><?= htmlspecialchars(trim(($computer['operating_system_name'] ?? '') . ' ' . ($computer['os_version_name'] ?? ''))) ?: '—' ?></strong></p>
    <?php if (!$isLinux): ?>
    <?php
    $baseWinUrl = '?page=hardware&section=computers&action=view&id=' . (int)($computer['id'] ?? 0) . '&tab=os';
    ?>
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

    <!-- Update (Hotfix) -->
    <div class="win-subpanel card card-block" data-wintab="update" style="display: <?= $currentWinTab === 'update' ? 'block' : 'none' ?>;">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-sync-alt"></i> Hotfix<?= !empty($windowsUpdates ?? []) ? ' (' . count($windowsUpdates ?? []) . ')' : '' ?></h2></div>
        <div class="card-body">
            <?php if (!empty($windowsUpdates)): ?>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead><tr><th>KB / Hotfix</th><th>Description</th><th>Date d'installation</th></tr></thead>
                    <tbody>
                        <?php foreach ($windowsUpdates as $wu): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($wu['hotfix_id']) ?></code></td>
                            <td><?= htmlspecialchars($wu['description'] ?? '—') ?></td>
                            <td><?= $wu['installed_on'] ? date('d/m/Y', strtotime($wu['installed_on'])) : '—' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted mb-0">Aucune donnée. Relancez l'agent d'inventaire sur ce PC avec la dernière version.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Services -->
    <div class="win-subpanel card card-block" data-wintab="services" style="display: <?= $currentWinTab === 'services' ? 'block' : 'none' ?>;">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-cogs"></i> Services Windows<?= !empty($windowsServices ?? []) ? ' (' . count($windowsServices ?? []) . ')' : '' ?></h2></div>
        <div class="card-body">
            <div class="mb-3">
                <input type="text" class="form-control form-control-sm" id="winServicesSearch" placeholder="Rechercher sur la description ou le statut..." style="max-width: 350px;">
            </div>
            <?php if (!empty($windowsServices)): ?>
            <div class="table-responsive">
                <table class="table table-sm table-striped" id="winServicesTable">
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
                </table>
            </div>
            <div class="mt-2 text-end"><small class="text-muted">Affichés : <strong id="winServicesCount"><?= count($windowsServices) ?></strong> sur <?= count($windowsServices) ?></small></div>
            <?php else: ?>
            <p class="text-muted mb-0">Aucune donnée. Relancez l'agent d'inventaire sur ce PC avec la dernière version.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Startup -->
    <div class="win-subpanel card card-block" data-wintab="startup" style="display: <?= $currentWinTab === 'startup' ? 'block' : 'none' ?>;">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-play"></i> Programmes au démarrage<?= !empty($windowsStartup ?? []) ? ' (' . count($windowsStartup ?? []) . ')' : '' ?></h2></div>
        <div class="card-body">
            <?php if (!empty($windowsStartup)): ?>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead><tr><th>Nom</th><th>Commande</th><th>Emplacement</th></tr></thead>
                    <tbody>
                        <?php foreach ($windowsStartup as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['name'] ?? '—') ?></td>
                            <td><code class="small"><?= htmlspecialchars($s['command'] ?? '—') ?></code></td>
                            <td><?= htmlspecialchars($s['location'] ?? '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted mb-0">Aucun programme détecté. Relancez l'agent d'inventaire sur ce PC.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- License -->
    <div class="win-subpanel card card-block" data-wintab="license" style="display: <?= $currentWinTab === 'license' ? 'block' : 'none' ?>;">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-key"></i> Licence Windows</h2></div>
        <div class="card-body">
            <?php if (!empty($windowsLicense) && ($windowsLicense['description'] || $windowsLicense['status'])): ?>
            <div class="details-grid">
                <div class="detail-item"><label>Description</label><value><?= htmlspecialchars($windowsLicense['description'] ?? '—') ?></value></div>
                <div class="detail-item"><label>Statut</label><value><span class="badge bg-success"><?= htmlspecialchars($windowsLicense['status'] ?? '—') ?></span></value></div>
            </div>
            <?php else: ?>
            <p class="text-muted mb-0">Aucune licence détectée. Relancez l'agent d'inventaire sur ce PC.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Shared -->
    <div class="win-subpanel card card-block" data-wintab="shared" style="display: <?= $currentWinTab === 'shared' ? 'block' : 'none' ?>;">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-share-alt"></i> Partages sortants<?= !empty($windowsShared ?? []) ? ' (' . count($windowsShared ?? []) . ')' : '' ?></h2></div>
        <div class="card-body">
            <?php if (!empty($windowsShared)): ?>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead><tr><th>Nom</th><th>Chemin</th><th>Description</th></tr></thead>
                    <tbody>
                        <?php foreach ($windowsShared as $s): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                            <td><code><?= htmlspecialchars($s['path'] ?? '—') ?></code></td>
                            <td><?= htmlspecialchars($s['description'] ?? '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted mb-0">Aucun partage ou l'agent n'a pas les droits. Relancez l'agent d'inventaire sur ce PC.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Mapped -->
    <div class="win-subpanel card card-block" data-wintab="mapped" style="display: <?= $currentWinTab === 'mapped' ? 'block' : 'none' ?>;">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-hdd"></i> Lecteurs réseau mappés<?= !empty($windowsMapped ?? []) ? ' (' . count($windowsMapped ?? []) . ')' : '' ?></h2></div>
        <div class="card-body">
            <?php if (!empty($windowsMapped)): ?>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead><tr><th>Lecteur</th><th>Chemin</th><th>Libellé</th></tr></thead>
                    <tbody>
                        <?php foreach ($windowsMapped as $m): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($m['drive_letter'] ?? '—') ?></strong></td>
                            <td><code><?= htmlspecialchars($m['path'] ?? '—') ?></code></td>
                            <td><?= htmlspecialchars($m['label'] ?? '—') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted mb-0">Aucun lecteur mappé (ou agent exécuté sous un autre compte). Relancez l'agent d'inventaire.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Users -->
    <div class="win-subpanel card card-block" data-wintab="users" style="display: <?= $currentWinTab === 'users' ? 'block' : 'none' ?>;">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-users"></i> Utilisateurs<?= !empty($windowsUsers ?? []) ? ' (' . count($windowsUsers ?? []) . ')' : '' ?></h2></div>
        <div class="card-body">
            <?php if (!empty($windowsUsers)): ?>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
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
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted mb-0">Aucun utilisateur local détecté. Relancez l'agent d'inventaire sur ce PC.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Group -->
    <div class="win-subpanel card card-block" data-wintab="group" style="display: <?= $currentWinTab === 'group' ? 'block' : 'none' ?>;">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-users-cog"></i> Groupes (dernier utilisateur connecté)<?= !empty($windowsUserGroups ?? []) ? ' (' . count($windowsUserGroups ?? []) . ')' : '' ?></h2></div>
        <div class="card-body">
            <?php if (!empty($windowsUserGroups)): ?>
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead><tr><th>Groupe</th></tr></thead>
                    <tbody>
                        <?php foreach ($windowsUserGroups as $g): ?>
                        <tr>
                            <td><?= htmlspecialchars($g['group_name']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted mb-0">Aucun groupe. Les groupes affichés sont ceux du compte exécutant l'agent. Relancez l'agent.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <div class="card card-block">
        <div class="card-header"><h2 class="card-title"><i class="fas fa-plug"></i> Accès à distance</h2></div>
        <div class="card-body">
            <?php if ($computer['teamviewer_id'] ?? null): ?>
            <a href="https://start.teamviewer.com/<?= htmlspecialchars($computer['teamviewer_id']) ?>" target="_blank" class="teamviewer-link"><i class="fas fa-desktop"></i> TeamViewer <?= htmlspecialchars($computer['teamviewer_id']) ?></a><br>
            <?php endif; ?>
            <?php if ($computer['rustdesk_id'] ?? null): ?>
            <a href="<?= ($rustdeskProtocol ?? 'rustdesk') ?>://<?= htmlspecialchars($computer['rustdesk_id']) ?>" class="rustdesk-link"><i class="fas fa-desktop"></i> RustDesk <?= htmlspecialchars($computer['rustdesk_id']) ?></a>
            <?php endif; ?>
            <?php if (empty($computer['teamviewer_id']) && empty($computer['rustdesk_id'])): ?><span class="text-muted">Aucun</span><?php endif; ?>
        </div>
    </div>
</div>

<!-- Fin du contenu par onglets -->
</div>
</div><!-- .pc-detail-fullwidth -->

<style>
/* Vue détail PC en pleine largeur - rectangles horizontaux sans espaces latéraux */
.main-layout:has(.pc-detail-fullwidth) { max-width: none; width: 100%; }
.content:has(.pc-detail-fullwidth) { padding-left: 1rem; padding-right: 1rem; max-width: none; }
.pc-detail-fullwidth { width: 100%; }

.pc-detail-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding: 1rem 0;
    border-bottom: 1px solid #e5e7eb;
}
.pc-detail-title { display: flex; align-items: flex-start; gap: 1rem; }
.pc-detail-title i { font-size: 2rem; color: #667eea; margin-top: 0.25rem; }
.pc-name { margin: 0 0 0.25rem 0; font-size: 1.5rem; font-weight: 600; }
.pc-subtitle, .pc-updated { display: block; color: #6b7280; font-size: 0.875rem; }
.pc-location-badge { margin-left: 1rem; display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.35rem 0.75rem; background: #f0fdf4; color: #166534; border-radius: 6px; font-size: 0.8rem; align-self: center; }
.pc-location-badge i { color: #22c55e; }
.pc-detail-badges { display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; }
.pc-badge { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.35rem 0.75rem; background: #f3f4f6; border-radius: 6px; font-size: 0.8rem; color: #374151; }
.pc-badge.badge-online { background: #dcfce7; color: #166534; }
.pc-badge.badge-offline { background: #fef2f2; color: #b91c1c; }
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

/* Blocs Home avec logo */
.home-status-card .card-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.card-block-icon {
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.card-block-icon i {
    font-size: 1rem;
    color: white;
}
.home-status-card .card-title { margin: 0; }

/* Grille blocs Home */
.home-status-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
@media (max-width: 900px) { .home-status-row { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 500px) { .home-status-row { grid-template-columns: 1fr; } }
.home-rest-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.25rem; }
.home-status-card .home-status-value { margin: 0 0 0.35rem 0; font-size: 1rem; }
.home-status-card .home-status-value.status-ok { color: #166534; }
.home-status-card .home-status-value.status-ok i { color: #22c55e; }
.home-status-card .home-status-value.status-warn { color: #b45309; }
.home-status-card .home-status-value.status-warn i { color: #f59e0b; }
.home-status-card .home-status-value.status-error { color: #b91c1c; }
.home-status-card .home-status-value.status-error i { color: #dc2626; }
.home-status-card .home-status-value.status-unknown { color: #6b7280; }
.home-status-card .home-status-value.status-unknown i { color: #9ca3af; }

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.page-actions {
    display: flex;
    gap: 0.5rem;
}

/* Grille deux colonnes de blocs */
.computer-details-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem;
    align-items: start;
}

.computer-details-grid .card-block {
    height: 100%;
}

.computer-details-grid .card-block-full {
    grid-column: 1 / -1;
}

.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

/* En-tête bloc avec dégradé */
.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1rem 1.25rem;
    border-bottom: none;
}

.card-title {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: white;
}

.card-title i {
    color: rgba(255, 255, 255, 0.9);
    font-size: 0.9rem;
}

.card-body {
    padding: 1.25rem;
}

.details-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}

.card-block .details-grid {
    gap: 0.75rem;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.detail-item label {
    font-weight: 500;
    color: #374151;
    font-size: 0.875rem;
}

.detail-item value {
    color: #1f2937;
    font-size: 1rem;
}

.teamviewer-link {
    text-decoration: none;
    transition: all 0.2s ease;
}

.teamviewer-link:hover .teamviewer-id {
    background: #e0f2fe;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.teamviewer-id {
    background: #f3f4f6;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-family: monospace;
    font-size: 0.875rem;
    color: #374151;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: 1px solid #e5e7eb;
    transition: all 0.2s ease;
}

.teamviewer-id i {
    color: #6b7280;
}

.teamviewer-link:hover .teamviewer-id {
    color: #0891b2;
    border-color: #0891b2;
}

.teamviewer-link:hover .teamviewer-id i {
    color: #0891b2;
}

.external-icon {
    font-size: 0.75rem;
    opacity: 0.7;
}

.rustdesk-link {
    text-decoration: none;
    color: #0891b2;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.35rem 0.75rem;
    border-radius: 6px;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    font-family: monospace;
    transition: all 0.2s ease;
}
.rustdesk-link:hover {
    background: #e0f2fe;
    color: #0e7490;
    border-color: #0891b2;
}
.rustdesk-pw {
    margin-left: 0.5rem;
    padding: 0.2rem 0.5rem;
    background: #fef3c7;
    border-radius: 4px;
    font-size: 0.85rem;
}

.badge {
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
    display: inline-block;
}

.badge-success {
    background-color: #dcfce7;
    color: #166534;
}

.badge-warning {
    background-color: #fef3c7;
    color: #92400e;
}

.os-info strong {
    color: #1f2937;
}

.os-info small {
    color: #6b7280;
}

.ram-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.ram-total {
    color: #1f2937;
}

.ram-usage {
    color: #6b7280;
    line-height: 1.4;
}

.hw-ram-details { font-size: 0.85rem; color: #374151; margin: 0.5rem 0 0 0; }
.hw-ram-details p { margin: 0.25rem 0; }
.ram-bar {
    width: 100%;
    height: 8px;
    background-color: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 0.5rem;
}

.ram-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981 0%, #f59e0b 70%, #ef4444 90%);
    border-radius: 4px;
    transition: width 0.3s ease;
}

/* Carte CPU style détaillé */
.hw-card-cpu .hw-cpu-model {
    color: #374151;
    font-size: 0.95rem;
    line-height: 1.4;
    margin-bottom: 0.5rem;
}
.hw-card-cpu .hw-cpu-speed {
    margin: 0.5rem 0;
    font-size: 1.75rem;
    font-weight: 600;
    color: #667eea;
}
.hw-card-cpu .hw-cpu-speed-value { font-size: 1.75rem; color: #667eea; }
.hw-card-cpu .hw-cpu-speed-unit { font-size: 1rem; color: #667eea; font-weight: 500; margin-left: 0.15rem; }
.hw-card-cpu .hw-cpu-details {
    display: flex;
    gap: 1.5rem;
    margin-top: 1rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e5e7eb;
}
.hw-card-cpu .hw-cpu-detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.hw-card-cpu .hw-cpu-detail-label {
    font-size: 0.75rem;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
.hw-card-cpu .hw-cpu-detail-value {
    font-size: 0.9rem;
    color: #374151;
}

/* Style unifié des blocs hardware (comme CPU) */
.hw-card-block .hw-block-model {
    color: #374151;
    font-size: 0.95rem;
    line-height: 1.4;
    margin-bottom: 0.5rem;
}
.hw-card-block .hw-block-highlight {
    margin: 0.5rem 0;
    font-size: 1.75rem;
    font-weight: 600;
    color: #667eea;
}
.hw-card-block .hw-block-highlight-value { font-size: 1.75rem; color: #667eea; }
.hw-card-block .hw-block-highlight-value.hw-block-highlight-ip { font-size: 1.1rem; font-family: monospace; }
.hw-card-block .hw-block-highlight-unit { font-size: 1rem; color: #667eea; font-weight: 500; margin-left: 0.15rem; }
.hw-card-block .hw-block-extra { font-size: 0.85rem; color: #6b7280; display: block; margin-top: 0.25rem; }
.hw-card-block .hw-block-sep { border: none; border-top: 1px solid #e5e7eb; margin: 1rem 0; }
.hw-card-block .hw-details {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem 1.5rem;
    margin-top: 1rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e5e7eb;
}
.hw-card-block .hw-details-storage { flex-direction: column; gap: 0.5rem; }
.hw-card-block .hw-details-list { border-top: 1px solid #e5e7eb; padding-top: 0.75rem; margin-top: 1rem; }
.hw-card-block .hw-detail-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.hw-card-block .hw-detail-row { display: flex; flex-wrap: wrap; align-items: center; gap: 0.35rem; }
.hw-card-block .hw-detail-label {
    font-size: 0.75rem;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
.hw-card-block .hw-detail-value {
    font-size: 0.9rem;
    color: #374151;
}

.text-muted {
    color: #9ca3af;
    font-style: italic;
}

/* Responsive design - Hardware tab */
@media (max-width: 1200px) {
    .tab-hardware { grid-template-columns: repeat(2, 1fr) !important; }
}
@media (max-width: 640px) {
    .tab-hardware { grid-template-columns: 1fr !important; }
}

@media (max-width: 992px) {
    .computer-details-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .details-grid {
        grid-template-columns: 1fr;
    }
    
    .detail-item {
        padding: 0.75rem;
        background: #f9fafb;
        border-radius: 8px;
    }
}

@media (max-width: 640px) {
    .card-header {
        padding: 1rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .details-grid {
        gap: 1rem;
    }
}

/* Styles pour les disques et partitions */
.disks-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.disk-block {
    margin-bottom: 1.5rem;
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #f9fafb;
}
.disk-block:last-child { margin-bottom: 0; }
.disk-block .disk-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e5e7eb;
}
.disk-capacity {
    font-size: 0.9rem;
    color: #6b7280;
}
.partition-block {
    margin: 0.75rem 0 0 1rem;
    padding: 0.75rem;
    background: white;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}
.partition-block:first-of-type { margin-top: 0.5rem; }
.partition-info {
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}
.partition-letter {
    font-weight: 600;
    color: #667eea;
    min-width: 2.5rem;
}
.partition-stats {
    font-size: 0.875rem;
    color: #374151;
}
.partition-stats em { color: #6b7280; }
.partition-bar {
    height: 8px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
}
.partition-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981 0%, #f59e0b 70%, #ef4444 90%);
    border-radius: 4px;
    transition: width 0.3s ease;
}
.partition-empty { margin: 0.5rem 0 0 1rem; font-size: 0.875rem; }

/* Stockage compact (même taille que les autres blocs) */
.network-adapters-list .network-adapter-item { margin-bottom: 0.6rem; padding-bottom: 0.5rem; border-bottom: 1px solid #eee; }
.network-adapters-list .network-adapter-item:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
.network-adapters-list .adapter-type-badge { font-size: 0.7rem; }
.network-adapters-list .adapter-ip { font-family: monospace; margin-left: 0.25rem; }
.network-adapters-list .network-adapter-not-connected { opacity: 0.85; }
.network-adapters-list .network-adapter-not-connected .adapter-ip { color: #9ca3af; }
.hw-card-storage .disk-block-compact { margin-bottom: 0.75rem; }
.hw-card-storage .disk-block-compact:last-child { margin-bottom: 0; }
.hw-card-storage .disk-title-compact { font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 0.35rem; }
.hw-card-storage .partition-line { display: flex; align-items: center; gap: 0.35rem; margin: 0.25rem 0 0 0.5rem; font-size: 0.75rem; }
.hw-card-storage .p-drive { font-weight: 600; color: #667eea; min-width: 2rem; }
.hw-card-storage .p-pct { color: #6b7280; min-width: 2.2rem; }
.hw-card-storage .p-bar { flex: 1; height: 8px; background: #e5e7eb; border-radius: 4px; overflow: hidden; }
.hw-card-storage .p-bar-fill { height: 100%; background: linear-gradient(90deg, #10b981 0%, #f59e0b 70%, #ef4444 90%); border-radius: 4px; transition: width 0.3s ease; }

.disk-item {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #f9fafb;
    overflow: hidden;
}


.disk-title {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    font-weight: 600;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.disk-title i {
    color: #6b7280;
}

.disk-details {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    font-size: 0.875rem;
}

.disk-size {
    background: #dbeafe;
    color: #1e40af;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-weight: 500;
}

.disk-interface {
    background: #f3e8ff;
    color: #7c3aed;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-weight: 500;
}

.disk-serial {
    background: #f0f9ff;
    color: #0369a1;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-family: monospace;
    font-size: 0.75rem;
}

.partitions-container {
    padding: 0;
}

.partition-item {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    background: white;
}

.partition-item:last-child {
    border-bottom: none;
}

.partition-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.partition-drive {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.drive-letter {
    background: #1f2937;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-weight: 600;
    font-family: monospace;
}

.partition-label {
    background: #f0fdf4;
    color: #166534;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
}

.file-system {
    background: #fef3c7;
    color: #92400e;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 500;
}

.partition-size {
    font-weight: 600;
    color: #374151;
}

.partition-usage {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.usage-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.875rem;
    color: #6b7280;
}

.usage-percent {
    font-weight: 600;
    color: #374151;
}

.usage-bar {
    width: 100%;
    height: 6px;
    background-color: #e5e7eb;
    border-radius: 3px;
    overflow: hidden;
}

.usage-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #10b981 0%, #f59e0b 70%, #ef4444 90%);
    border-radius: 3px;
    transition: width 0.3s ease;
}

.no-partitions {
    padding: 1rem;
    text-align: center;
    color: #9ca3af;
    background: white;
}

.no-disks {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 2rem;
    color: #9ca3af;
    text-align: center;
}

.no-disks i {
    font-size: 1.25rem;
}

/* Responsive pour les disques */
@media (max-width: 768px) {
    .partition-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .usage-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
    
    .disk-details {
        flex-direction: column;
        gap: 0.5rem;
    }
}

/* Styles pour la section logiciels */
.table-striped tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.1);
}

.badge.bg-info {
    background-color: #17a2b8 !important;
}

#softwareSearch {
    transition: box-shadow 0.15s ease-in-out;
}

#softwareSearch:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
}

.text-center.py-4 {
    padding: 3rem 0;
}

.text-center.py-4 i {
    opacity: 0.5;
}

/* Styles pour le bouton toggle des logiciels */
#toggleSoftwareBtn {
    transition: all 0.3s ease;
    font-weight: 500;
}

#toggleSoftwareBtn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.2);
}

#toggleIcon {
    transition: transform 0.3s ease;
}

#softwareSection {
    transition: all 0.3s ease;
}

.btn .badge {
    font-size: 0.75em;
}

/* Styles pour le champ de recherche global */
#globalSoftwareSearch {
    transition: box-shadow 0.15s ease-in-out;
}

#globalSoftwareSearch:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    border-color: #007bff;
}

.row.align-items-center {
    align-items: center !important;
}
</style>

<!-- Ajout de Chart.js et du script pour le graphique -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chartEl = document.getElementById('temperatureChart');
    if (!chartEl) return;
    const ctx = chartEl.getContext('2d');
    let temperatureChart = null;

    function loadTemperatureData() {
        const url = `?page=hardware&action=getCpuTemperatures&pc_id=<?= $computer['id'] ?>`;
        
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    document.getElementById('temperatureChartContainer').innerHTML = 
                        '<div class="alert alert-warning">Erreur: ' + data.error + '</div>';
                    return;
                }
                
                const labels = data.labels || [];
                const cpuTemps = data.cpu_temperatures || data.temperatures || [];
                const gpuTemps = data.gpu_temperatures || [];
                const hasData = labels.length > 0 && (cpuTemps.some(t => t != null) || gpuTemps.some(t => t != null));

                if (!hasData) {
                    document.getElementById('temperatureChartContainer').innerHTML = 
                        '<div class="alert alert-info">Aucune donnée de température disponible pour ce PC. L\'agent moniteur enverra des relevés toutes les 20 à 60 secondes.</div>';
                    document.getElementById('temperatureHistoryTable').style.display = 'none';
                    return;
                }

                if (temperatureChart) temperatureChart.destroy();

                const datasets = [];
                if (cpuTemps.some(t => t != null)) {
                    datasets.push({
                        label: 'CPU (°C)',
                        data: cpuTemps,
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.1,
                        fill: false
                    });
                }
                if (gpuTemps.some(t => t != null)) {
                    datasets.push({
                        label: 'GPU (°C)',
                        data: gpuTemps,
                        borderColor: 'rgb(255, 99, 132)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.1,
                        fill: false
                    });
                }

                const chartContainer = document.getElementById('temperatureChartContainer');
                if (!chartContainer.querySelector('canvas')) {
                    chartContainer.innerHTML = '<canvas id="temperatureChart" height="200"></canvas>';
                }
                const chartCtx = document.getElementById('temperatureChart').getContext('2d');

                temperatureChart = new Chart(chartCtx, {
                    type: 'line',
                    data: { labels, datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: false,
                                title: { display: true, text: 'Température (°C)' }
                            },
                            x: {
                                title: { display: true, text: 'Heure' },
                                ticks: { maxTicksLimit: 12 }
                            }
                        },
                        plugins: {
                            legend: { display: true, position: 'top' },
                            title: { display: true, text: 'Évolution des températures CPU / GPU' }
                        }
                    }
                });

                // Historique : tableau des derniers relevés (ordre antéchronologique)
                const history = data.history || [];
                const historyBody = document.getElementById('temperatureHistoryBody');
                const historyTable = document.getElementById('temperatureHistoryTable');
                if (history.length > 0) {
                    historyBody.innerHTML = '';
                    [...history].reverse().slice(0, 50).forEach(row => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = '<td>' + (row.created_at ? new Date(row.created_at).toLocaleString('fr-FR', { day:'2-digit', month:'2-digit', hour:'2-digit', minute:'2-digit' }) : '-') + '</td>' +
                            '<td>' + (row.cpu_temp != null ? Math.round(row.cpu_temp) + ' °C' : '—') + '</td>' +
                            '<td>' + (row.gpu_temp != null ? Math.round(row.gpu_temp) + ' °C' : '—') + '</td>';
                        historyBody.appendChild(tr);
                    });
                    historyTable.style.display = 'block';
                } else {
                    historyTable.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des températures:', error);
                const c = document.getElementById('temperatureChartContainer');
                if (c) c.innerHTML = '<div class="alert alert-danger">Erreur: ' + error.message + '</div>';
            });
    }

    // Charger les données initiales
    loadTemperatureData();

    // Rafraîchir les données toutes les 5 minutes
    setInterval(loadTemperatureData, 5 * 60 * 1000);
});

// Script pour la recherche dans les services Windows
document.addEventListener('DOMContentLoaded', function() {
    const winServicesSearch = document.getElementById('winServicesSearch');
    const winServicesTable = document.getElementById('winServicesTable');
    const winServicesCount = document.getElementById('winServicesCount');
    const totalWinServices = <?= count($windowsServices ?? []) ?>;
    if (winServicesSearch && winServicesTable && winServicesTable.tBodies[0]) {
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

// Script pour la recherche dans les logiciels installés
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('globalSoftwareSearch');
    const tableBody = document.getElementById('softwareTableBody');
    const countElement = document.getElementById('softwareCount');
    const totalSoftware = <?= count($installedSoftware ?? []) ?>;
    
    if (searchInput && tableBody) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const rows = tableBody.getElementsByTagName('tr');
            let visibleCount = 0;
            
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const softwareName = row.cells[0].textContent.toLowerCase();
                const version = row.cells[1].textContent.toLowerCase();
                
                if (softwareName.includes(searchTerm) || version.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            }
            
            if (countElement) {
                countElement.textContent = visibleCount;
            }
            
            // Ouvrir automatiquement la section si on recherche
            if (searchTerm.length > 0) {
                const section = document.getElementById('softwareSection');
                const icon = document.getElementById('toggleIcon');
                const button = document.getElementById('toggleSoftwareBtn');
                
                if (section.style.display === 'none') {
                    section.style.display = 'block';
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                    button.classList.remove('btn-outline-primary');
                    button.classList.add('btn-primary');
                }
            }
        });
    }
});

// Fonction pour afficher/masquer la section logiciels
function toggleSoftwareSection() {
    const section = document.getElementById('softwareSection');
    const icon = document.getElementById('toggleIcon');
    const button = document.getElementById('toggleSoftwareBtn');
    
    if (section.style.display === 'none') {
        section.style.display = 'block';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
        button.classList.remove('btn-outline-primary');
        button.classList.add('btn-primary');
    } else {
        section.style.display = 'none';
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
        button.classList.remove('btn-primary');
        button.classList.add('btn-outline-primary');
    }
}

// Vérification statut en ligne (RustDesk Pro + ping IP)
(function() {
    const container = document.querySelector('.rustdesk-status');
    if (!container) return;
    const rustdeskId = container.dataset.rustdeskId;
    const badge = document.getElementById('rustdesk-status-badge');
    const lastOnlineEl = document.getElementById('rustdesk-last-online');
    const btnCheck = document.getElementById('btn-check-online');
    const btnRefresh = document.getElementById('btn-refresh-page');
    if (!badge || !btnCheck || !btnRefresh) return;

    function setState(text, cls, hideExtras) {
        badge.textContent = text;
        badge.className = 'badge ' + (cls || 'badge-secondary');
        if (lastOnlineEl) { lastOnlineEl.textContent = ''; lastOnlineEl.classList.add('d-none'); }
    }

    function checkRustDeskPro() {
        if (!rustdeskId) return;
        btnCheck.disabled = true;
        setState('Vérification…', 'badge-secondary');
        const pathDir = window.location.pathname.replace(/\/[^/]*$/, '') || '/';
        const apiBase = pathDir.endsWith('/') ? pathDir : pathDir + '/';
        fetch(apiBase + 'api/rustdesk_status.php?id=' + encodeURIComponent(rustdeskId))
            .then(r => r.json())
            .then(function(data) {
                if (data.error) {
                    badge.textContent = data.error;
                    badge.className = 'badge badge-warning';
                } else if (data.online) {
                    badge.textContent = 'En ligne (RustDesk Pro)';
                    badge.className = 'badge badge-success';
                    if (data.last_online && lastOnlineEl) {
                        lastOnlineEl.textContent = '(dernière activité: ' + data.last_online.replace('T', ' ').substr(0, 19) + ')';
                        lastOnlineEl.classList.remove('d-none');
                    }
                } else {
                    badge.textContent = 'Hors ligne';
                    badge.className = 'badge badge-danger';
                    if (data.last_online && lastOnlineEl) {
                        lastOnlineEl.textContent = '(dernière activité: ' + data.last_online.replace('T', ' ').substr(0, 19) + ')';
                        lastOnlineEl.classList.remove('d-none');
                    } else if (data.message && lastOnlineEl) {
                        lastOnlineEl.textContent = data.message;
                        lastOnlineEl.classList.remove('d-none');
                    }
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
    // Vérification automatique au chargement de la page
    checkRustDeskPro();
})();
</script>
    checkRustDeskPro();
})();
</script>