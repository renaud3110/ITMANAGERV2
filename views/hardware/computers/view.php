<div class="page-header">
    <h1 class="page-title">Détails de l'ordinateur</h1>
    <div class="page-actions">
        <a href="?page=hardware&section=computers&action=edit&id=<?= $computer['id'] ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i>
            Modifier
        </a>
        <a href="?page=hardware&section=computers" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Retour à la liste
        </a>
    </div>
</div>

<div class="computer-details">
    <!-- Informations générales -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-desktop"></i>
                Informations générales
            </h2>
        </div>
        <div class="card-body">
            <div class="details-grid">
                <div class="detail-item">
                    <label>Nom de l'ordinateur</label>
                    <value><?= htmlspecialchars($computer['name'] ?? 'Non défini') ?></value>
                </div>
                
                <div class="detail-item">
                    <label>Numéro de série</label>
                    <value><?= htmlspecialchars($computer['serial_number'] ?? 'Non défini') ?></value>
                </div>
                
                <div class="detail-item">
                    <label>Statut</label>
                    <value>
                        <span class="badge <?= $computer['status'] === 'used' ? 'badge-warning' : 'badge-success' ?>">
                            <?= $computer['status'] === 'used' ? 'Utilisé' : 'Libre' ?>
                        </span>
                    </value>
                </div>
                
                <div class="detail-item">
                    <label>TeamViewer ID</label>
                    <value>
                        <?php if ($computer['teamviewer_id']): ?>
                            <a href="https://start.teamviewer.com/<?= htmlspecialchars($computer['teamviewer_id']) ?>" 
                               target="_blank" 
                               class="teamviewer-link"
                               title="Se connecter via TeamViewer">
                                <span class="teamviewer-id">
                                    <i class="fas fa-desktop"></i>
                                    <?= htmlspecialchars($computer['teamviewer_id']) ?>
                                    <i class="fas fa-external-link-alt external-icon"></i>
                                </span>
                            </a>
                        <?php else: ?>
                            <span class="text-muted">Non défini</span>
                        <?php endif; ?>
                    </value>
                </div>
            </div>
        </div>
    </div>

    <!-- Localisation -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-map-marker-alt"></i>
                Localisation
            </h2>
        </div>
        <div class="card-body">
            <div class="details-grid">
                <div class="detail-item">
                    <label>Tenant</label>
                    <value><?= htmlspecialchars($computer['tenant_name'] ?? 'Non défini') ?></value>
                </div>
                
                <div class="detail-item">
                    <label>Site</label>
                    <value><?= htmlspecialchars($computer['site_name'] ?? 'Non défini') ?></value>
                </div>
            </div>
        </div>
    </div>

    <!-- Spécifications techniques -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-microchip"></i>
                Spécifications techniques
            </h2>
        </div>
        <div class="card-body">
            <div class="details-grid">
                <div class="detail-item">
                    <label>Marque</label>
                    <value><?= htmlspecialchars($computer['model_brand'] ?? 'Non définie') ?></value>
                </div>
                
                <div class="detail-item">
                    <label>Modèle</label>
                    <value><?= htmlspecialchars($computer['model_name'] ?? 'Non défini') ?></value>
                </div>
                
                <div class="detail-item">
                    <label>Processeur</label>
                    <value><?= htmlspecialchars($computer['processor_model'] ?? 'Non défini') ?></value>
                </div>
                
                <div class="detail-item">
                    <label>Mémoire RAM</label>
                    <value>
                        <?php if ($computer['ram_total'] && $computer['ram_total'] > 0): ?>
                            <div class="ram-info">
                                <?php 
                                    // Déterminer l'unité (MB ou bytes) basé sur la taille de la valeur
                                    $ramTotalGB = $computer['ram_total'] > 1000000000 ? 
                                        $computer['ram_total'] / 1024 / 1024 / 1024 : // bytes vers GB
                                        $computer['ram_total'] / 1024; // MB vers GB
                                ?>
                                <div class="ram-total">
                                    <strong>Total: <?= number_format($ramTotalGB, 1) ?> GB</strong>
                                </div>
                                <?php if ($computer['ram_used'] && $computer['ram_used'] > 0): ?>
                                    <?php 
                                        $ramUsedGB = $computer['ram_used'] > 1000000000 ? 
                                            $computer['ram_used'] / 1024 / 1024 / 1024 : // bytes vers GB
                                            $computer['ram_used'] / 1024; // MB vers GB
                                        $ramFreeGB = $ramTotalGB - $ramUsedGB;
                                        $usagePercent = ($ramUsedGB / $ramTotalGB) * 100;
                                    ?>
                                    <div class="ram-usage">
                                        <small>
                                            Utilisée: <?= number_format($ramUsedGB, 1) ?> GB 
                                            (<?= number_format($usagePercent, 1) ?>%)
                                        </small>
                                        <br>
                                        <small>
                                            Libre: <?= number_format($ramFreeGB, 1) ?> GB 
                                            (<?= number_format(100 - $usagePercent, 1) ?>%)
                                        </small>
                                    </div>
                                    <div class="ram-bar">
                                        <div class="ram-bar-fill" style="width: <?= number_format($usagePercent, 1) ?>%"></div>
                                    </div>
                                <?php else: ?>
                                    <div class="ram-usage">
                                        <small class="text-muted">Utilisation non disponible</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <span class="text-muted">Non définie</span>
                        <?php endif; ?>
                    </value>
                </div>
                
                <div class="detail-item">
                    <label>Système d'exploitation</label>
                    <value>
                        <?php if ($computer['operating_system_name']): ?>
                            <div class="os-info">
                                <strong><?= htmlspecialchars($computer['operating_system_name']) ?></strong>
                                <?php if ($computer['os_version_name']): ?>
                                    <br><small><?= htmlspecialchars($computer['os_version_name']) ?></small>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <span class="text-muted">Non défini</span>
                        <?php endif; ?>
                    </value>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuration réseau -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-network-wired"></i>
                Configuration réseau
            </h2>
        </div>
        <div class="card-body">
            <div class="details-grid">
                <div class="detail-item">
                    <label>Adresse IP</label>
                    <value><?= htmlspecialchars($computer['ip_address'] ?? 'Non définie') ?></value>
                </div>
                
                <div class="detail-item">
                    <label>Passerelle</label>
                    <value><?= htmlspecialchars($computer['gateway'] ?? 'Non définie') ?></value>
                </div>
                
                <div class="detail-item">
                    <label>Masque de sous-réseau</label>
                    <value><?= htmlspecialchars($computer['subnet_mask'] ?? 'Non défini') ?></value>
                </div>
                
                <div class="detail-item">
                    <label>Serveurs DNS</label>
                    <value><?= htmlspecialchars($computer['dns_servers'] ?? 'Non définis') ?></value>
                </div>
            </div>
        </div>
    </div>

    <!-- Disques et partitions -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-hdd"></i>
                Disques et partitions
            </h2>
        </div>
        <div class="card-body">
            <?php if (!empty($disks)): ?>
                <div class="disks-container">
                    <?php foreach ($disks as $disk): ?>
                        <div class="disk-item">
                            <div class="disk-header">
                                <div class="disk-info">
                                    <h4 class="disk-title">
                                        <i class="fas fa-hdd"></i>
                                        <?= htmlspecialchars($disk['model'] ?? 'Disque inconnu') ?>
                                    </h4>
                                    <div class="disk-details">
                                        <span class="disk-size"><?= $disk['size_gb'] ?> GB</span>
                                        <?php if ($disk['interface_type']): ?>
                                            <span class="disk-interface"><?= htmlspecialchars($disk['interface_type']) ?></span>
                                        <?php endif; ?>
                                        <?php if ($disk['serial_number']): ?>
                                            <span class="disk-serial">S/N: <?= htmlspecialchars($disk['serial_number']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($disk['partitions'])): ?>
                                <div class="partitions-container">
                                    <?php foreach ($disk['partitions'] as $partition): ?>
                                        <div class="partition-item">
                                            <div class="partition-header">
                                                <div class="partition-drive">
                                                    <span class="drive-letter"><?= htmlspecialchars($partition['drive_letter']) ?>:</span>
                                                    <?php if ($partition['label']): ?>
                                                        <span class="partition-label"><?= htmlspecialchars($partition['label']) ?></span>
                                                    <?php endif; ?>
                                                    <span class="file-system"><?= htmlspecialchars($partition['file_system']) ?></span>
                                                </div>
                                                <div class="partition-size">
                                                    <?= $partition['total_size_gb'] ?> GB
                                                </div>
                                            </div>
                                            
                                            <div class="partition-usage">
                                                <div class="usage-info">
                                                    <span class="used-space">Utilisé: <?= $partition['used_space_gb'] ?> GB</span>
                                                    <span class="free-space">Libre: <?= $partition['free_space_gb'] ?> GB</span>
                                                    <span class="usage-percent"><?= $partition['usage_percentage'] ?>%</span>
                                                </div>
                                                <div class="usage-bar">
                                                    <div class="usage-bar-fill" style="width: <?= $partition['usage_percentage'] ?>%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="no-partitions">
                                    <small class="text-muted">Aucune partition détectée</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-disks">
                    <i class="fas fa-info-circle"></i>
                    <span>Aucun disque détecté pour cet ordinateur</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Personnes -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-users"></i>
                Personnes
            </h2>
        </div>
        <div class="card-body">
            <div class="details-grid">
                <div class="detail-item">
                    <label>Personne attribuée</label>
                    <value>
                        <?php if ($computer['person_nom'] && $computer['person_prenom']): ?>
                            <strong><?= htmlspecialchars($computer['person_prenom'] . ' ' . $computer['person_nom']) ?></strong>
                            <?php if ($computer['person_email']): ?>
                                <br><small class="text-muted"><?= htmlspecialchars($computer['person_email']) ?></small>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">Aucune personne attribuée</span>
                        <?php endif; ?>
                    </value>
                </div>
                
                <div class="detail-item">
                    <label>Dernier compte utilisé</label>
                    <value><?= htmlspecialchars($computer['last_account'] ?? 'Aucun') ?></value>
                </div>
            </div>
        </div>
    </div>

    <!-- Dates -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-calendar"></i>
                Informations de suivi
            </h2>
        </div>
        <div class="card-body">
            <div class="details-grid">
                <div class="detail-item">
                    <label>Date de création</label>
                    <value>
                        <?php if ($computer['created_at']): ?>
                            <?= date('d/m/Y H:i', strtotime($computer['created_at'])) ?>
                        <?php else: ?>
                            <span class="text-muted">Non définie</span>
                        <?php endif; ?>
                    </value>
                </div>
                
                <div class="detail-item">
                    <label>Dernière modification</label>
                    <value>
                        <?php if ($computer['updated_at']): ?>
                            <?= date('d/m/Y H:i', strtotime($computer['updated_at'])) ?>
                        <?php else: ?>
                            <span class="text-muted">Non définie</span>
                        <?php endif; ?>
                    </value>
                </div>
            </div>
        </div>
    </div>

    <!-- Ajout du graphique de température CPU -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">
                <i class="fas fa-thermometer-half"></i>
                Température CPU
            </h5>
        </div>
        <div class="card-body">
            <canvas id="temperatureChart" height="200"></canvas>
        </div>
    </div>

    <!-- Champ de recherche global pour les logiciels (toujours visible) -->
    <?php if (!empty($installedSoftware)): ?>
    <div class="mb-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" class="form-control" id="globalSoftwareSearch" placeholder="Rechercher dans les logiciels installés...">
                </div>
            </div>
            <div class="col-md-6">
                <button type="button" class="btn btn-outline-primary" id="toggleSoftwareBtn" onclick="toggleSoftwareSection()">
                    <i class="fas fa-download"></i>
                    Logiciels Installés
                    <span class="badge bg-primary ms-2"><?= count($installedSoftware) ?></span>
                    <i class="fas fa-chevron-down ms-2" id="toggleIcon"></i>
                </button>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Bouton pour afficher/masquer les logiciels installés -->
    <div class="mb-3">
        <button type="button" class="btn btn-outline-primary" id="toggleSoftwareBtn" onclick="toggleSoftwareSection()">
            <i class="fas fa-download"></i>
            Logiciels Installés
            <span class="badge bg-secondary ms-2">0</span>
            <i class="fas fa-chevron-down ms-2" id="toggleIcon"></i>
        </button>
    </div>
    <?php endif; ?>

    <!-- Section logiciels installés (initialement masquée) -->
    <div class="card mb-4" id="softwareSection" style="display: none;">
        <div class="card-body">
            <?php if (empty($installedSoftware)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-download fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">Aucun logiciel installé trouvé</h6>
                    <p class="text-muted">Aucun logiciel n'a été détecté sur ce PC ou les données ne sont pas disponibles.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-cube"></i> Nom du logiciel</th>
                                <th><i class="fas fa-tag"></i> Version</th>
                                <th><i class="fas fa-calendar-alt"></i> Date d'installation</th>
                            </tr>
                        </thead>
                        <tbody id="softwareTableBody">
                            <?php foreach ($installedSoftware as $software): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($software['name']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?= htmlspecialchars($software['version']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($software['installation_date']): ?>
                                            <?= date('d/m/Y', strtotime($software['installation_date'])) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Non disponible</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Compteur de logiciels -->
                <div class="mt-3 text-end">
                    <small class="text-muted">
                        Affichés : <strong id="softwareCount"><?= count($installedSoftware) ?></strong> sur <?= count($installedSoftware) ?> logiciels
                    </small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
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

.computer-details {
    display: grid;
    gap: 1.5rem;
}

.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
}

.card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 1.5rem;
}

.card-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.card-body {
    padding: 1.5rem;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
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

.text-muted {
    color: #9ca3af;
    font-style: italic;
}

/* Responsive design */
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
        padding: 1rem;
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

.disk-item {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #f9fafb;
    overflow: hidden;
}

.disk-header {
    padding: 1rem;
    background: #f3f4f6;
    border-bottom: 1px solid #e5e7eb;
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
    console.log('Initialisation du graphique de température pour PC ID:', <?= $computer['id'] ?>);
    
    const ctx = document.getElementById('temperatureChart').getContext('2d');
    let temperatureChart = null;

    function loadTemperatureData() {
        const url = `?page=hardware&action=getCpuTemperatures&pc_id=<?= $computer['id'] ?>`;
        console.log('Chargement des données depuis:', url);
        
        fetch(url)
            .then(response => {
                console.log('Réponse reçue:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Données reçues:', data);
                
                if (data.error) {
                    console.error('Erreur de l\'API:', data.error);
                    document.getElementById('temperatureChart').parentNode.innerHTML = 
                        '<div class="alert alert-warning">Erreur: ' + data.error + '</div>';
                    return;
                }
                
                if (!data.labels || !data.temperatures || data.temperatures.length === 0) {
                    console.warn('Aucune donnée de température disponible');
                    document.getElementById('temperatureChart').parentNode.innerHTML = 
                        '<div class="alert alert-info">Aucune donnée de température disponible pour ce PC.</div>';
                    return;
                }

                if (temperatureChart) {
                    temperatureChart.destroy();
                }

                temperatureChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Température CPU (°C)',
                            data: data.temperatures,
                            borderColor: 'rgb(75, 192, 192)',
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            tension: 0.1,
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: false,
                                title: {
                                    display: true,
                                    text: 'Température (°C)'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Heure'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            title: {
                                display: true,
                                text: 'Évolution de la température CPU'
                            }
                        }
                    }
                });
                
                console.log('Graphique créé avec succès');
            })
            .catch(error => {
                console.error('Erreur lors du chargement des températures:', error);
                document.getElementById('temperatureChart').parentNode.innerHTML = 
                    '<div class="alert alert-danger">Erreur lors du chargement des données: ' + error.message + '</div>';
            });
    }

    // Charger les données initiales
    loadTemperatureData();

    // Rafraîchir les données toutes les 5 minutes
    setInterval(loadTemperatureData, 5 * 60 * 1000);
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
</script> 