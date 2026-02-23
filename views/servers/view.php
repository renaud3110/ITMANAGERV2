<div class="page-header">
    <h1 class="page-title">Détails du serveur</h1>
    <div class="page-actions">
        <a href="?page=servers&action=edit&id=<?= $server['id'] ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i>
            Modifier
        </a>
        <a href="?page=hardware&section=servers" class="btn btn-secondary">
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
                <i class="fas fa-server"></i>
                Informations générales
            </h2>
        </div>
        <div class="card-body">
            <div class="details-grid">
                <div class="detail-item">
                    <label>Nom du serveur</label>
                    <value><?= htmlspecialchars($server['name'] ?? 'Non défini') ?></value>
                </div>
                
                <div class="detail-item">
                    <label>Type</label>
                    <value>
                        <span class="badge badge-<?= $server['type'] === 'Virtuel' ? 'info' : 'success' ?>">
                            <?= htmlspecialchars($server['type'] ?? 'Non défini') ?>
                        </span>
                    </value>
                </div>
                
                <div class="detail-item">
                    <label>Hostname</label>
                    <value><?= htmlspecialchars($server['hostname'] ?? 'Non défini') ?></value>
                </div>
                
                <div class="detail-item">
                    <label>TeamViewer ID</label>
                    <value>
                        <?php if ($server['teamviewer_id']): ?>
                            <a href="https://start.teamviewer.com/<?= htmlspecialchars($server['teamviewer_id']) ?>" 
                               target="_blank" 
                               class="teamviewer-link"
                               title="Se connecter via TeamViewer">
                                <span class="teamviewer-id">
                                    <i class="fas fa-desktop"></i>
                                    <?= htmlspecialchars($server['teamviewer_id']) ?>
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
                    <value><?= htmlspecialchars($server['tenant_name'] ?? 'Non défini') ?></value>
                </div>
                
                <div class="detail-item">
                    <label>Site</label>
                    <value><?= htmlspecialchars($server['site_name'] ?? 'Non défini') ?></value>
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
                    <value><?= htmlspecialchars($server['model_brand'] ?? 'Non définie') ?></value>
                </div>
                
                <div class="detail-item">
                    <label>Modèle</label>
                    <value><?= htmlspecialchars($server['model_name'] ?? 'Non défini') ?></value>
                </div>
                
                <div class="detail-item">
                    <label>Processeur</label>
                    <value><?= htmlspecialchars($server['processor_model'] ?? 'Non défini') ?></value>
                </div>
                
                <div class="detail-item">
                    <label>Mémoire RAM</label>
                    <value>
                        <?php if ($server['ram_total'] && $server['ram_total'] > 0): ?>
                            <div class="ram-info">
                                <?php 
                                    // RAM total en GB
                                    $ramTotalGB = $server['ram_total_gb'] ?? ($server['ram_total'] / 1024 / 1024 / 1024);
                                ?>
                                <div class="ram-total">
                                    <strong>Total: <?= number_format($ramTotalGB, 2) ?> GB</strong>
                                </div>
                                <?php if ($server['ram_used'] && $server['ram_used'] > 0): ?>
                                    <?php 
                                        $ramUsedGB = $server['ram_used_gb'] ?? ($server['ram_used'] / 1024 / 1024 / 1024);
                                        $ramFreeGB = $ramTotalGB - $ramUsedGB;
                                        $usagePercent = ($ramUsedGB / $ramTotalGB) * 100;
                                    ?>
                                    <div class="ram-usage">
                                        <small>
                                            Utilisée: <?= number_format($ramUsedGB, 2) ?> GB 
                                            (<?= number_format($usagePercent, 1) ?>%)
                                        </small>
                                        <br>
                                        <small>
                                            Libre: <?= number_format($ramFreeGB, 2) ?> GB 
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
                        <?php if ($server['operating_system_name']): ?>
                            <div class="os-info">
                                <strong><?= htmlspecialchars($server['operating_system_name']) ?></strong>
                                <?php if ($server['os_version_name']): ?>
                                    <br><small><?= htmlspecialchars($server['os_version_name']) ?></small>
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
                    <value><?= htmlspecialchars($server['ip_address'] ?? 'Non définie') ?></value>
                </div>
                
                <div class="detail-item">
                    <label>Passerelle</label>
                    <value><?= htmlspecialchars($server['gateway'] ?? 'Non définie') ?></value>
                </div>
                
                <div class="detail-item">
                    <label>Masque de sous-réseau</label>
                    <value><?= htmlspecialchars($server['subnet_mask'] ?? 'Non défini') ?></value>
                </div>
                
                <div class="detail-item">
                    <label>Serveurs DNS</label>
                    <value><?= htmlspecialchars($server['dns_servers'] ?? 'Non définis') ?></value>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations système -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-info-circle"></i>
                Informations système
            </h2>
        </div>
        <div class="card-body">
            <div class="details-grid">
                <div class="detail-item">
                    <label>Date de création</label>
                    <value>
                        <?php if ($server['created_at']): ?>
                            <?= date('d/m/Y à H:i', strtotime($server['created_at'])) ?>
                        <?php else: ?>
                            <span class="text-muted">Non définie</span>
                        <?php endif; ?>
                    </value>
                </div>
                
                <div class="detail-item">
                    <label>Dernière mise à jour</label>
                    <value>
                        <?php if ($server['updated_at']): ?>
                            <?= date('d/m/Y à H:i', strtotime($server['updated_at'])) ?>
                        <?php else: ?>
                            <span class="text-muted">Non définie</span>
                        <?php endif; ?>
                    </value>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e5e7eb;
}

.page-title {
    font-size: 1.875rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
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
    overflow: hidden;
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
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.detail-item value {
    color: #1f2937;
    font-size: 1rem;
    line-height: 1.5;
}

.badge {
    display: inline-block;
    padding: 0.375rem 0.75rem;
    font-size: 0.75rem;
    font-weight: 500;
    line-height: 1;
    color: #fff;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 0.375rem;
}

.badge-info {
    background-color: #3b82f6;
}

.badge-success {
    background-color: #10b981;
}

.teamviewer-link {
    text-decoration: none;
    color: #3b82f6;
    transition: color 0.2s;
}

.teamviewer-link:hover {
    color: #1d4ed8;
    text-decoration: none;
}

.teamviewer-id {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
}

.external-icon {
    font-size: 0.75rem;
    opacity: 0.7;
}

.ram-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.ram-total {
    font-size: 1rem;
}

.ram-usage {
    font-size: 0.875rem;
    color: #6b7280;
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
    background: linear-gradient(90deg, #10b981 0%, #f59e0b 50%, #ef4444 100%);
    transition: width 0.3s ease;
}

.os-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.text-muted {
    color: #9ca3af;
    font-style: italic;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.2s;
    border: 1px solid transparent;
}

.btn-primary {
    background-color: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background-color: #1d4ed8;
    text-decoration: none;
    color: white;
}

.btn-secondary {
    background-color: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background-color: #4b5563;
    text-decoration: none;
    color: white;
}
</style> 