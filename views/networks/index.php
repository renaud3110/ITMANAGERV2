<div class="page-header">
    <h1 class="page-title">Gestion des Réseaux</h1>
    <a href="?page=networks&action=create" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Nouvel Équipement
    </a>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <i class="fas fa-network-wired stat-icon"></i>
        <div class="stat-number"><?= $stats['total'] ?? 0 ?></div>
        <div class="stat-label">Équipements Total</div>
    </div>
    <div class="stat-card">
        <i class="fas fa-check-circle stat-icon text-success"></i>
        <div class="stat-number"><?= $stats['active'] ?? 0 ?></div>
        <div class="stat-label">Actifs</div>
    </div>
    <div class="stat-card">
        <i class="fas fa-ethernet stat-icon"></i>
        <div class="stat-number"><?= $stats['total_ports'] ?? 0 ?></div>
        <div class="stat-label">Ports Total</div>
    </div>
    <div class="stat-card">
        <i class="fas fa-router stat-icon"></i>
        <div class="stat-number"><?= ($stats['routers'] ?? 0) + ($stats['switches'] ?? 0) ?></div>
        <div class="stat-label">Équipements Actifs</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Équipements Réseau</h2>
        <div class="card-actions">
            <a href="?page=networks&action=connections" class="btn btn-secondary">
                <i class="fas fa-project-diagram"></i>
                Connexions
            </a>
            <a href="?page=networks&action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Ajouter
            </a>
        </div>
    </div>
    
    <?php if (empty($equipments)): ?>
        <div class="empty-state">
            <i class="fas fa-network-wired empty-icon"></i>
            <h3 class="empty-title">Aucun équipement réseau</h3>
            <p class="empty-text">Ajoutez vos équipements réseau pour commencer la gestion des ports.</p>
            <a href="?page=networks&action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Ajouter un équipement
            </a>
        </div>
    <?php else: ?>
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-modern" id="equipmentsTable">
                    <thead>
                        <tr>
                            <th><i class="fas fa-tag"></i> Nom</th>
                            <th><i class="fas fa-cogs"></i> Type</th>
                            <th><i class="fas fa-map-marker-alt"></i> Site</th>
                            <th><i class="fas fa-ethernet"></i> Ports</th>
                            <th><i class="fas fa-network-wired"></i> IP Management</th>
                            <th><i class="fas fa-power-off"></i> Statut</th>
                            <th><i class="fas fa-tools"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($equipments as $equipment): ?>
                            <tr class="equipment-row">
                                <td class="equipment-name">
                                    <div class="name-wrapper">
                                        <strong><?= htmlspecialchars($equipment['name']) ?></strong>
                                        <?php if (!empty($equipment['manufacturer_name'])): ?>
                                            <span class="manufacturer"><?= htmlspecialchars($equipment['manufacturer_name']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="equipment-badge equipment-<?= $equipment['type'] ?>">
                                        <i class="fas fa-<?= $equipment['type'] === 'router' ? 'route' : ($equipment['type'] === 'switch' ? 'network-wired' : 'wifi') ?>"></i>
                                        <?= ucfirst($equipment['type']) ?>
                                    </span>
                                </td>
                                <td class="site-info">
                                    <?= htmlspecialchars($equipment['site_name'] ?? 'Non défini') ?>
                                </td>
                                <td class="ports-info">
                                    <?php 
                                    $portSummary = null;
                                    foreach ($portsSummary as $summary) {
                                        if ($summary['equipment_id'] == $equipment['id']) {
                                            $portSummary = $summary;
                                            break;
                                        }
                                    }
                                    ?>
                                    <?php if ($portSummary): ?>
                                        <div class="ports-summary">
                                            <div class="ports-total">
                                                <strong><?= $portSummary['total_ports'] ?></strong> ports
                                            </div>
                                            <div class="ports-details">
                                                <span class="ports-active"><?= $portSummary['active_ports'] ?> actifs</span>
                                                <span class="ports-separator">•</span>
                                                <span class="ports-free"><?= $portSummary['inactive_ports'] ?> libres</span>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <span class="no-ports">Aucun port</span>
                                    <?php endif; ?>
                                </td>
                                <td class="ip-info">
                                    <?php if (!empty($equipment['ip_address'])): ?>
                                        <code class="ip-address"><?= htmlspecialchars($equipment['ip_address']) ?></code>
                                    <?php else: ?>
                                        <span class="no-ip">Non définie</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="status-indicator status-<?= $equipment['status'] ?>">
                                        <i class="fas fa-circle"></i>
                                        <?= ucfirst($equipment['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($equipment['ports_count'] > 0): ?>
                                            <a href="?page=networks&action=ports&equipment_id=<?= $equipment['id'] ?>" 
                                               class="btn-action btn-ports" title="Gérer les ports">
                                                <i class="fas fa-ethernet"></i>
                                                <span class="btn-text">Ports</span>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($equipment['login_id'])): ?>
                                            <button class="btn-action btn-credentials" 
                                                   data-equipment-id="<?= $equipment['id'] ?>"
                                                   data-equipment-name="<?= htmlspecialchars($equipment['name']) ?>"
                                                   title="Afficher les identifiants">
                                                <i class="fas fa-key"></i>
                                                <span class="btn-text">Identifiants</span>
                                            </button>
                                        <?php endif; ?>
                                        <a href="?page=networks&action=edit&id=<?= $equipment['id'] ?>" 
                                           class="btn-action btn-edit" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                            <span class="btn-text">Modifier</span>
                                        </a>
                                        <a href="?page=networks&action=delete&id=<?= $equipment['id'] ?>" 
                                           class="btn-action btn-delete" title="Supprimer"
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet équipement et tous ses ports ?')">
                                            <i class="fas fa-trash"></i>
                                            <span class="btn-text">Supprimer</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Légende des statuts -->
        <div class="status-legend">
            <h4><i class="fas fa-info-circle"></i> Légende des statuts</h4>
            <div class="legend-items">
                <div class="legend-item">
                    <span class="status-indicator status-active"><i class="fas fa-circle"></i> Actif</span>
                    <small>Équipement opérationnel</small>
                </div>
                <div class="legend-item">
                    <span class="status-indicator status-inactive"><i class="fas fa-circle"></i> Inactif</span>
                    <small>Équipement hors service</small>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal pour afficher les identifiants de connexion -->
<div class="modal fade" id="credentialsModal" tabindex="-1" aria-labelledby="credentialsModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="credentialsModalLabel">
                    <i class="fas fa-key"></i>
                    Identifiants de connexion
                </h5>
                <button type="button" class="close-modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="credentials-container">
                    <div class="equipment-info">
                        <h6><i class="fas fa-server"></i> Équipement</h6>
                        <div class="info-row">
                            <strong>Nom:</strong> <span id="modalEquipmentName">-</span>
                        </div>
                        <div class="info-row">
                            <strong>Type:</strong> <span id="modalEquipmentType">-</span>
                        </div>
                        <div class="info-row">
                            <strong>Adresse IP:</strong> <code id="modalIpAddress">-</code>
                        </div>
                    </div>
                    
                    <div class="login-info">
                        <h6><i class="fas fa-user"></i> Informations de connexion</h6>
                        <div class="info-row">
                            <strong>Nom d'utilisateur:</strong> 
                            <code id="modalUsername">-</code>
                            <button id="copyUsername" class="btn-copy" title="Copier le nom d'utilisateur">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <div class="info-row">
                            <strong>Service:</strong> <span id="modalService">-</span>
                        </div>
                        <div class="info-row">
                            <strong>Description:</strong> <span id="modalDescription">-</span>
                        </div>
                    </div>
                    
                    <div class="password-section">
                        <h6><i class="fas fa-lock"></i> Mot de passe</h6>
                        <div class="password-field">
                            <input type="password" id="modalPassword" value="••••••••" readonly class="form-control">
                            <button id="togglePassword" class="btn-toggle" title="Afficher/Masquer">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button id="copyPassword" class="btn-copy" title="Copier le mot de passe">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <small class="text-muted">Cliquez sur l'œil pour révéler le mot de passe</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<style>
/* ===== STYLES PRINCIPAUX ===== */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

/* ===== STATISTIQUES ===== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    padding: 2rem;
    border-radius: 1rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    text-align: center;
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #6366f1, #8b5cf6);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.stat-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: #6366f1;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: #1f2937;
    margin-bottom: 0.5rem;
    line-height: 1;
}

.stat-label {
    color: #6b7280;
    font-size: 0.9rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* ===== CARTE PRINCIPALE ===== */
.card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 2rem;
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border-bottom: 1px solid #e5e7eb;
}

.card-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
}

.card-actions {
    display: flex;
    gap: 0.75rem;
}

/* ===== ÉTAT VIDE ===== */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-icon {
    font-size: 5rem;
    color: #d1d5db;
    margin-bottom: 1.5rem;
}

.empty-title {
    font-size: 1.5rem;
    color: #6b7280;
    margin-bottom: 1rem;
    font-weight: 600;
}

.empty-text {
    color: #9ca3af;
    margin-bottom: 2rem;
    font-size: 1.1rem;
}

/* ===== TABLEAU ===== */
.table-container {
    background: #fff;
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
    padding: 0;
}

.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table-modern {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.9rem;
    margin: 0;
}

.table-modern thead th {
    background: linear-gradient(135deg, #f8fafc 0%, #e5e7eb 100%);
    color: #374151;
    font-weight: 700;
    padding: 1.25rem 1rem;
    border: none;
    text-align: left;
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #d1d5db;
}

.table-modern thead th i {
    margin-right: 0.5rem;
    color: #6366f1;
}

.table-modern tbody td {
    padding: 1.25rem 1rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
    background-color: #fff;
}

.table-modern tbody tr:last-child td {
    border-bottom: none;
}

.equipment-row {
    transition: all 0.2s ease;
}

.equipment-row:hover {
    background-color: #f8fafc;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* ===== STYLES SPÉCIFIQUES AUX CELLULES ===== */
.equipment-name .name-wrapper {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.equipment-name strong {
    color: #1f2937;
    font-weight: 600;
}

.manufacturer {
    font-size: 0.8rem;
    color: #6b7280;
    font-style: italic;
}

.equipment-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.8rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.equipment-router {
    background-color: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.equipment-switch {
    background-color: #eff6ff;
    color: #2563eb;
    border: 1px solid #bfdbfe;
}

.equipment-wifiAP, .equipment-wifi {
    background-color: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}

.site-info {
    color: #374151;
    font-weight: 500;
}

.ports-summary {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.ports-total {
    font-size: 0.9rem;
}

.ports-details {
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.ports-active {
    color: #059669;
    font-weight: 500;
}

.ports-free {
    color: #6b7280;
}

.ports-separator {
    color: #d1d5db;
}

.no-ports {
    color: #9ca3af;
    font-style: italic;
}

.ip-address {
    background-color: #f3f4f6;
    color: #374151;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.8rem;
}

.no-ip {
    color: #9ca3af;
    font-style: italic;
}

.status-indicator {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    font-size: 0.8rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-active {
    background-color: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.status-inactive {
    background-color: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
}

.status-active i, .status-inactive i {
    font-size: 0.6rem;
}

/* ===== BOUTONS D'ACTION ===== */
.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.375rem;
    padding: 0;
    border: 1px solid;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
    width: 2.5rem;
    height: 2.5rem;
    flex-shrink: 0;
}

.btn-ports {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    border-color: #3b82f6;
}

.btn-ports:hover {
    background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-edit {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    border-color: #f59e0b;
}

.btn-edit:hover {
    background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}

.btn-delete {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    border-color: #ef4444;
}

.btn-delete:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

.btn-text {
    display: none;
}

.btn-action i {
    font-size: 1rem;
}

/* Tous les boutons d'action ont les mêmes dimensions */

/* ===== BOUTONS PRINCIPAUX ===== */
.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: 1px solid transparent;
    border-radius: 0.5rem;
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    text-transform: none;
}

.btn-primary {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    box-shadow: 0 2px 4px rgba(99, 102, 241, 0.2);
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(99, 102, 241, 0.3);
}

.btn-secondary {
    background-color: #f8fafc;
    color: #374151;
    border-color: #d1d5db;
}

.btn-secondary:hover {
    background-color: #f1f5f9;
    border-color: #9ca3af;
}

/* ===== LÉGENDE ===== */
.status-legend {
    margin-top: 2rem;
    padding: 1.5rem;
    background: #f8fafc;
    border-radius: 0.5rem;
    border-top: 1px solid #e5e7eb;
}

.status-legend h4 {
    margin: 0 0 1rem 0;
    color: #374151;
    font-size: 1rem;
    font-weight: 600;
}

.legend-items {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.legend-item small {
    color: #6b7280;
    font-size: 0.8rem;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .card-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .card-actions {
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .page-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .table-modern th,
    .table-modern td {
        padding: 0.75rem 0.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-text {
        display: inline;
    }
    
    .legend-items {
        flex-direction: column;
        gap: 1rem;
    }
}

@media (max-width: 640px) {
    .stat-card {
        padding: 1.5rem;
    }
    
    .stat-number {
        font-size: 2rem;
    }
    
    .empty-state {
        padding: 2rem 1rem;
    }
    
    .equipment-badge,
    .status-indicator {
        font-size: 0.7rem;
        padding: 0.375rem 0.75rem;
    }
}

/* ===== COULEURS SPÉCIALES ===== */
.text-success { color: #059669 !important; }
.text-muted { color: #6b7280 !important; }

/* ===== ANIMATION DE CHARGEMENT ===== */
.table-loading {
    position: relative;
}

.table-loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    z-index: 1;
}

/* ===== ACCESSIBILITÉ ===== */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* ===== CONTRÔLES DATATABLE ===== */
.datatable-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    flex-wrap: wrap;
    gap: 1rem;
}

.datatable-length label,
.datatable-filter label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
    color: #374151;
    font-size: 0.9rem;
}

.datatable-length select,
.datatable-filter input {
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    background: white;
    transition: border-color 0.2s ease;
}

.datatable-length select:focus,
.datatable-filter input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.datatable-footer {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 1.5rem;
    background: #f8fafc;
    border-top: 1px solid #e5e7eb;
    flex-wrap: wrap;
    gap: 2rem;
}

.datatable-info {
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
    text-align: center;
    padding: 0.5rem;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 0.375rem;
    min-width: 200px;
}

.datatable-pagination {
    display: flex;
    justify-content: center;
    align-items: center;
}

/* Styles pour les boutons de pagination */
.dataTables_paginate {
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    gap: 0.5rem;
}

.dataTables_paginate .paginate_button {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    min-width: 2.5rem;
    height: 2.5rem;
    padding: 0.5rem !important;
    margin: 0 0.125rem !important;
    border: 1px solid #d1d5db !important;
    border-radius: 0.375rem !important;
    background: white !important;
    color: #374151 !important;
    font-weight: 500 !important;
    font-size: 0.875rem !important;
    text-decoration: none !important;
    transition: all 0.2s ease !important;
    cursor: pointer !important;
}

.dataTables_paginate .paginate_button:hover {
    background: #f3f4f6 !important;
    border-color: #9ca3af !important;
    color: #1f2937 !important;
    transform: translateY(-1px);
}

.dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%) !important;
    border-color: #6366f1 !important;
    color: white !important;
    box-shadow: 0 2px 4px rgba(99, 102, 241, 0.2);
}

.dataTables_paginate .paginate_button.disabled {
    background: #f9fafb !important;
    border-color: #e5e7eb !important;
    color: #9ca3af !important;
    cursor: not-allowed !important;
}

.dataTables_paginate .paginate_button.disabled:hover {
    background: #f9fafb !important;
    border-color: #e5e7eb !important;
    color: #9ca3af !important;
    transform: none;
}

.dataTables_paginate .paginate_button.previous,
.dataTables_paginate .paginate_button.next {
    font-weight: 600 !important;
}

/* Responsive pour les contrôles DataTable */
@media (max-width: 768px) {
    .datatable-header {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }
    
    .datatable-footer {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .dataTables_paginate {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .dataTables_paginate .paginate_button {
        min-width: 2rem;
        height: 2rem;
        font-size: 0.75rem !important;
    }
}

/* Amélioration de l'affichage du sélecteur de longueur */
.dataTables_length select {
    min-width: 4rem;
}

/* Amélioration du champ de recherche */
.dataTables_filter input {
    min-width: 200px;
}

@media (max-width: 640px) {
    .dataTables_filter input {
        min-width: 150px;
    }
}

/* ===== IMPRESSION ===== */
@media print {
    .btn, .card-actions, .action-buttons, .datatable-header, .datatable-footer {
        display: none !important;
    }
    
    .card {
        box-shadow: none;
        border: 1px solid #000;
    }
    
    .table-modern {
        font-size: 0.8rem;
    }
}

/* ===== STYLES POUR LE BOUTON IDENTIFIANTS ===== */
.btn-credentials {
    background-color: #ecfdf5;
    color: #10b981;
    border-color: #a7f3d0;
}

.btn-credentials:hover {
    background-color: #10b981;
    color: white;
}

.btn-credentials:active {
    transform: translateY(0);
}

.btn-credentials:disabled {
    background: #9ca3af;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* ===== STYLES POUR LE MODAL DES IDENTIFIANTS ===== */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1050;
    overflow-y: auto;
    backdrop-filter: blur(4px);
}

.modal-dialog {
    position: relative;
    width: auto;
    margin: 1.75rem auto;
    max-width: 600px;
    pointer-events: none;
}

.modal-dialog-centered {
    display: flex;
    align-items: center;
    min-height: calc(100% - 3.5rem);
}

.modal-content {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    pointer-events: auto;
    background-color: #fff;
    border: 1px solid rgba(0, 0, 0, 0.2);
    border-radius: 0.75rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    animation: modalSlideIn 0.2s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 0.75rem 0.75rem 0 0;
}

.modal-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.close-modal {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #6b7280;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
}

.close-modal:hover {
    color: #dc2626;
    background: #fef2f2;
}

.modal-body {
    padding: 1.5rem;
}

.credentials-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.equipment-info,
.login-info,
.password-section {
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    background: #f9fafb;
}

.equipment-info h6,
.login-info h6,
.password-section h6 {
    margin: 0 0 1rem 0;
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
    flex-wrap: wrap;
}

.info-row:last-child {
    margin-bottom: 0;
}

.info-row strong {
    min-width: 120px;
    font-weight: 600;
    color: #374151;
}

.info-row code {
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 0.25rem;
    padding: 0.25rem 0.5rem;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.875rem;
    color: #1f2937;
}

.password-field {
    display: flex;
    align-items: stretch;
    gap: 0;
    margin-bottom: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    overflow: hidden;
    background: #fff;
}

.password-field input {
    flex: 1;
    padding: 0.75rem;
    border: none;
    background: transparent;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
    font-size: 0.875rem;
    outline: none;
}

.password-field input:focus {
    outline: none;
    box-shadow: none;
}

.password-field:focus-within {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.btn-toggle,
.btn-copy {
    background: #f8fafc;
    border: none;
    border-left: 1px solid #e5e7eb;
    padding: 0;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #374151;
    width: 2.75rem;
    height: 2.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.btn-toggle:hover,
.btn-copy:hover {
    background: #f1f5f9;
    color: #1f2937;
}

.btn-toggle:active,
.btn-copy:active {
    background: #e2e8f0;
}

.btn-copy.copied {
    background: #10b981;
    color: white;
    border-left-color: #10b981;
}

/* Style spécifique pour les boutons de copie dans les info-row */
.info-row .btn-copy {
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    padding: 0;
    margin-left: 0.5rem;
    width: 2.75rem;
    height: 2.75rem;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #374151;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.875rem;
    flex-shrink: 0;
}

.info-row .btn-copy:hover {
    background: #e5e7eb;
    border-color: #9ca3af;
}

.info-row .btn-copy.copied {
    background: #10b981;
    color: white;
    border-color: #10b981;
}

.modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
    border-radius: 0 0 0.75rem 0.75rem;
}

.modal-open {
    overflow: hidden;
}

/* Responsive */
@media (max-width: 640px) {
    .modal-dialog {
        margin: 1rem;
        max-width: calc(100% - 2rem);
    }
    
    .modal-content {
        border-radius: 0.5rem;
    }
    
    .modal-header,
    .modal-body,
    .modal-footer {
        padding: 1rem;
    }
    
    .info-row {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .info-row strong {
        min-width: auto;
    }
    
    .password-field {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .password-field input {
        width: 100%;
    }
    
    .btn-toggle,
    .btn-copy {
        width: 100%;
    }
}
</style>

<script>
$(document).ready(function() {
    // Configuration DataTables avec options avancées
    if ($.fn.DataTable && $('#equipmentsTable').length) {
        $('#equipmentsTable').DataTable({
            responsive: {
                details: {
                    type: 'column',
                    target: 'tr'
                }
            },
                         language: {
                 url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json',
                 paginate: {
                     first: '« Premier',
                     last: 'Dernier »',
                     next: 'Suivant ›',
                     previous: '‹ Précédent'
                 },
                 info: 'Affichage de _START_ à _END_ sur _TOTAL_ équipements',
                 infoEmpty: 'Aucun équipement à afficher',
                 infoFiltered: '(filtré de _MAX_ équipements au total)',
                 lengthMenu: 'Afficher _MENU_ équipements par page',
                 search: 'Rechercher :',
                 zeroRecords: 'Aucun équipement trouvé correspondant à votre recherche'
             },
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: -1 }, // Actions non triables
                { responsivePriority: 1, targets: 0 }, // Nom toujours visible
                { responsivePriority: 2, targets: -1 }, // Actions prioritaires
                { responsivePriority: 3, targets: [1, 5] } // Type et statut importants
            ],
            pageLength: 25,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Tous"]],
                         dom: '<"datatable-header"<"datatable-length"l><"datatable-filter"f>>rt<"datatable-footer"<"datatable-info"i><"datatable-pagination"p>>',
            drawCallback: function() {
                // Réinitialiser les tooltips après chaque draw
                $('[title]').tooltip();
            },
            initComplete: function() {
                // Animation d'apparition du tableau
                $('#equipmentsTable').fadeIn(300);
                
                // Statistiques en temps réel
                updateStatistics();
            }
        });
    }
    
    // Fonction pour mettre à jour les statistiques
    function updateStatistics() {
        const table = $('#equipmentsTable').DataTable();
        const totalRows = table.rows().count();
        const visibleRows = table.rows({ search: 'applied' }).count();
        
        // Mise à jour du compteur si filtré
        if (totalRows !== visibleRows) {
            $('.stat-number').first().text(visibleRows + '/' + totalRows);
        }
    }
    
    // Mise à jour des stats lors de la recherche
    $('#equipmentsTable').on('search.dt', function() {
        updateStatistics();
    });
    
    // Tooltips Bootstrap/Popper.js
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Animation des cartes statistiques au scroll
    if ('IntersectionObserver' in window) {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('.stat-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    }
    
    // Confirmation de suppression améliorée
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        const equipmentName = $(this).closest('tr').find('.equipment-name strong').text();
        const url = $(this).attr('href');
        
        if (confirm(`Êtes-vous sûr de vouloir supprimer l'équipement "${equipmentName}" ?\n\nCette action supprimera également tous les ports et connexions associés.\n\nCette action est irréversible.`)) {
            // Afficher un indicateur de chargement
            $(this).html('<i class="fas fa-spinner fa-spin"></i> Suppression...');
            window.location.href = url;
        }
    });
    
         // Messages de feedback visuels
     $('.alert').each(function() {
         const alert = $(this);
         setTimeout(() => {
             alert.fadeOut(500, function() {
                 $(this).remove();
             });
         }, 5000);
     });
     
     // Ajustements visuels pour la pagination
     setTimeout(function() {
         // Centrer la pagination et améliorer l'affichage
         $('.dataTables_paginate').css({
             'display': 'flex',
             'justify-content': 'center',
             'align-items': 'center',
             'flex-wrap': 'wrap',
             'gap': '0.5rem'
         });
         
         // S'assurer que les boutons de pagination sont bien stylés
         $('.paginate_button').each(function() {
             if (!$(this).hasClass('current') && !$(this).hasClass('disabled')) {
                 $(this).css('margin', '0 2px');
             }
         });
     }, 100);
    
    // Amélioration UX sur mobile
    if (window.innerWidth <= 768) {
        // Réduire les animations sur mobile pour de meilleures performances
        $('*').css('transition-duration', '0.1s');
        
        // Optimiser les hover states sur tactile
        $('.equipment-row').on('touchstart', function() {
            $(this).addClass('hover-state');
        }).on('touchend', function() {
            $(this).removeClass('hover-state');
        });
    }
});

// Gestion de l'état de chargement
window.addEventListener('beforeunload', function() {
    document.querySelector('.table-container').classList.add('table-loading');
});

// Gestion du modal des identifiants
const credentialsModal = document.getElementById('credentialsModal');
let currentPassword = '';

// Fonction pour afficher le modal
function showCredentialsModal() {
    credentialsModal.style.display = 'block';
    credentialsModal.classList.add('show');
    document.body.classList.add('modal-open');
}

// Fonction pour cacher le modal
function hideCredentialsModal() {
    credentialsModal.style.display = 'none';
    credentialsModal.classList.remove('show');
    document.body.classList.remove('modal-open');
    
    // Reset password field
    document.getElementById('modalPassword').type = 'password';
    document.getElementById('modalPassword').value = '••••••••';
    document.getElementById('togglePassword').innerHTML = '<i class="fas fa-eye"></i>';
    currentPassword = '';
}

// Gestionnaires pour fermer le modal
document.querySelectorAll('.close-modal').forEach(btn => {
    btn.addEventListener('click', hideCredentialsModal);
});

// Fermer en cliquant à l'extérieur
credentialsModal.addEventListener('click', function(e) {
    if (e.target === credentialsModal) {
        hideCredentialsModal();
    }
});

// Fermer avec la touche Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && credentialsModal.classList.contains('show')) {
        hideCredentialsModal();
    }
});

// Gestion des boutons identifiants
document.querySelectorAll('.btn-credentials').forEach(button => {
    button.addEventListener('click', function() {
        const equipmentId = this.getAttribute('data-equipment-id');
        
        // Changer l'icône pour indiquer le chargement
        const originalHtml = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span class="btn-text">Chargement...</span>';
        this.disabled = true;
        
        fetch(`?page=networks&action=getCredentials&equipment_id=${equipmentId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    alert('Erreur: ' + data.error);
                    return;
                }
                
                // Remplir le modal avec les données
                document.getElementById('modalEquipmentName').textContent = data.equipment_name;
                document.getElementById('modalEquipmentType').textContent = data.equipment_type;
                document.getElementById('modalIpAddress').textContent = data.ip_address;
                document.getElementById('modalUsername').textContent = data.username;
                document.getElementById('modalService').textContent = data.service;
                document.getElementById('modalDescription').textContent = data.description;
                
                // Récupérer le mot de passe via l'API sécurisée
                return fetch(`?page=accounts&action=showPassword&login_id=${data.login_id}`);
            })
            .then(response => response ? response.json() : null)
            .then(passwordData => {
                if (passwordData && passwordData.password) {
                    currentPassword = passwordData.password;
                } else {
                    currentPassword = 'Mot de passe non disponible';
                }
                
                showCredentialsModal();
            })
            .catch(error => {
                console.error('Erreur:', error);
                alert('Une erreur est survenue lors de la récupération des identifiants: ' + error.message);
            })
            .finally(() => {
                // Restaurer le bouton
                this.innerHTML = originalHtml;
                this.disabled = false;
            });
    });
});

// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordField = document.getElementById('modalPassword');
    const icon = this.querySelector('i');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        passwordField.value = currentPassword;
        icon.className = 'fas fa-eye-slash';
    } else {
        passwordField.type = 'password';
        passwordField.value = '••••••••';
        icon.className = 'fas fa-eye';
    }
});

// Copy functions
document.getElementById('copyUsername').addEventListener('click', function() {
    const username = document.getElementById('modalUsername').textContent;
    copyToClipboard(username, this);
});

document.getElementById('copyPassword').addEventListener('click', function() {
    const password = document.getElementById('modalPassword').value;
    if (password && password !== '••••••••') {
        copyToClipboard(password, this);
    } else {
        alert('Révélez d\'abord le mot de passe en cliquant sur l\'œil');
    }
});

function copyToClipboard(text, button) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text)
            .then(() => {
                const originalHtml = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i>';
                button.classList.add('copied');
                setTimeout(() => {
                    button.innerHTML = originalHtml;
                    button.classList.remove('copied');
                }, 2000);
            })
            .catch(err => {
                console.error('Erreur lors de la copie:', err);
                fallbackCopyText(text, button);
            });
    } else {
        fallbackCopyText(text, button);
    }
}

function fallbackCopyText(text, button) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    textArea.style.top = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        document.execCommand('copy');
        const originalHtml = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i>';
        button.classList.add('copied');
        setTimeout(() => {
            button.innerHTML = originalHtml;
            button.classList.remove('copied');
        }, 2000);
    } catch (err) {
        console.error('Fallback: Erreur lors de la copie', err);
        alert('Impossible de copier. Copiez manuellement.');
    }
    
    document.body.removeChild(textArea);
}
</script> 