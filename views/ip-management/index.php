<?php include 'views/partials/header.php'; ?>

<div class="container mt-4">
    <div class="page-header">
        <h1 class="page-title">Gestion des Adresses IP</h1>
        <div class="page-actions">
            <a href="?page=ip-management&action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Nouvelle Adresse IP
            </a>
        </div>
    </div>

    <div class="context-info">
        <span class="context-item">
            <i class="fas fa-building"></i>
            <strong>Tenant:</strong>
            <?php if ($currentTenant === 'all'): ?>
                <span class="context-badge all">Tous les tenants</span>
            <?php else: ?>
                <span class="context-badge selected">Tenant <?= $currentTenant ?></span>
            <?php endif; ?>
        </span>
        
        <span class="context-separator">|</span>
        
        <span class="context-item">
            <i class="fas fa-map-marker-alt"></i>
            <strong>Site:</strong>
            <?php if ($currentSite === 'all'): ?>
                <span class="context-badge all">Tous les sites</span>
            <?php else: ?>
                <span class="context-badge selected">Site <?= $currentSite ?></span>
            <?php endif; ?>
        </span>
    </div>
    
    <?php if (isset($flash) && $flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <?= $flash['message'] ?>
        </div>
    <?php endif; ?>

    <!-- Statistiques -->
    <div class="stats-container">
        <div class="stats-header">
            <h3 class="stats-title">
                <i class="fas fa-chart-bar"></i>
                Statistiques des Adresses IP
            </h3>
            <div class="stats-context">
                <?php if ($currentTenant !== 'all' || $currentSite !== 'all'): ?>
                    <span class="context-indicator filtered">
                        <i class="fas fa-filter"></i>
                        Contexte filtré
                        <?php if ($currentTenant !== 'all'): ?>
                            - Tenant <?= $currentTenant ?>
                        <?php endif; ?>
                        <?php if ($currentSite !== 'all'): ?>
                            - Site <?= $currentSite ?>
                        <?php endif; ?>
                    </span>
                <?php else: ?>
                    <span class="context-indicator global">
                        <i class="fas fa-globe"></i>
                        Vue globale
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-globe stat-icon"></i>
                <div class="stat-number"><?= $stats['total'] ?? 0 ?></div>
                <div class="stat-label">Total Adresses</div>
                <div class="stat-trend">
                    <?php if (($stats['total'] ?? 0) > 0): ?>
                        <span class="trend-positive">
                            <i class="fas fa-arrow-up"></i>
                            Actif
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-check-circle stat-icon text-success"></i>
                <div class="stat-number"><?= $stats['used'] ?? 0 ?></div>
                <div class="stat-label">Utilisées</div>
                <div class="stat-trend">
                    <?php 
                    $total = $stats['total'] ?? 0;
                    $used = $stats['used'] ?? 0;
                    $percentage = $total > 0 ? round(($used / $total) * 100) : 0;
                    ?>
                    <span class="usage-percentage <?= $percentage >= 80 ? 'high' : ($percentage >= 50 ? 'medium' : 'low') ?>">
                        <?= $percentage ?>% d'utilisation
                    </span>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-circle stat-icon text-muted"></i>
                <div class="stat-number"><?= $stats['available'] ?? 0 ?></div>
                <div class="stat-label">Disponibles</div>
                <div class="stat-trend">
                    <?php if (($stats['available'] ?? 0) > 0): ?>
                        <span class="trend-positive">
                            <i class="fas fa-check"></i>
                            Disponible
                        </span>
                    <?php else: ?>
                        <span class="trend-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Aucune libre
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="stat-card">
                <i class="fas fa-network-wired stat-icon"></i>
                <div class="stat-number"><?= $stats['subnets'] ?? 0 ?></div>
                <div class="stat-label">Sous-réseaux</div>
                <div class="stat-trend">
                    <?php if (($stats['subnets'] ?? 0) > 1): ?>
                        <span class="trend-info">
                            <i class="fas fa-sitemap"></i>
                            Multi-réseau
                        </span>
                    <?php elseif (($stats['subnets'] ?? 0) === 1): ?>
                        <span class="trend-info">
                            <i class="fas fa-network-wired"></i>
                            Réseau unique
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header card-header-ip">
            <div class="card-title-block">
                <h2 class="card-title">
                    <i class="fas fa-sitemap"></i>
                    Adresses IP du site : <span class="context-value"><?= htmlspecialchars($currentTenantName) ?> / <?= htmlspecialchars($currentSiteName) ?></span>
                </h2>
            </div>
            <div class="card-actions card-actions-right">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Rechercher une adresse IP...">
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <?php if (empty($ipAddresses)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-sitemap"></i>
                    </div>
                    <h3>Aucune adresse IP trouvée</h3>
                    <p>Ajoutez des adresses IP pour commencer la gestion de votre réseau.</p>
                    <a href="?page=ip-management&action=create" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Ajouter une adresse IP
                    </a>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="table table-modern" id="ipAddressesTable">
                        <thead>
                            <tr>
                                <th><i class="fas fa-globe"></i> Adresse IP</th>
                                <th><i class="fas fa-route"></i> Passerelle/Subnet</th>
                                <th><i class="fas fa-tags"></i> VLAN</th>
                                <th><i class="fas fa-file-alt"></i> Description</th>
                                <th><i class="fas fa-power-off"></i> Statut</th>
                                <th><i class="fas fa-map-marker-alt"></i> Site</th>
                                <th><i class="fas fa-tools"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ipAddresses as $ip): ?>
                                <tr class="ip-row">
                                    <td class="ip-main">
                                        <div class="ip-wrapper">
                                            <?php 
                                            $equipmentUrl = null;
                                            if (!empty($ip['is_used']) && !empty($ip['equipment_name'])) {
                                                if (!empty($ip['pc_id'])) {
                                                    $equipmentUrl = '?page=hardware&section=computers&action=view&id=' . (int)$ip['pc_id'];
                                                } elseif (!empty($ip['server_id'])) {
                                                    $equipmentUrl = '?page=servers&action=view&id=' . (int)$ip['server_id'];
                                                } elseif (!empty($ip['network_equipment_id'])) {
                                                    $equipmentUrl = '?page=networks&action=edit&id=' . (int)$ip['network_equipment_id'];
                                                }
                                            }
                                            ?>
                                            <?php if ($equipmentUrl): ?>
                                                <a href="<?= $equipmentUrl ?>" class="ip-address-link" title="Voir l'équipement">
                                                    <code class="ip-address"><?= htmlspecialchars($ip['ip_address']) ?></code>
                                                </a>
                                            <?php else: ?>
                                                <code class="ip-address"><?= htmlspecialchars($ip['ip_address']) ?></code>
                                            <?php endif; ?>
                                            <?php if (!empty($ip['equipment_name'])): ?>
                                                <?php if ($equipmentUrl): ?>
                                                    <a href="<?= $equipmentUrl ?>" class="equipment-info equipment-link" title="Voir l'équipement"><?= htmlspecialchars($ip['equipment_name']) ?></a>
                                                <?php else: ?>
                                                    <span class="equipment-info"><?= htmlspecialchars($ip['equipment_name']) ?></span>
                                                <?php endif; ?>
                                            <?php elseif (!empty($ip['tenant_name'])): ?>
                                                <span class="tenant-info"><?= htmlspecialchars($ip['tenant_name']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="network-info">
                                        <?php if (!empty($ip['subnet_mask']) || !empty($ip['gateway'])): ?>
                                            <div class="network-details">
                                                <?php if (!empty($ip['subnet_mask'])): ?>
                                                    <div class="network-item">
                                                        <i class="fas fa-layer-group"></i>
                                                        <code class="subnet-mask"><?= htmlspecialchars($ip['subnet_mask']) ?></code>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($ip['gateway'])): ?>
                                                    <div class="network-item">
                                                        <i class="fas fa-route"></i>
                                                        <code class="gateway"><?= htmlspecialchars($ip['gateway']) ?></code>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="no-network">Non configuré</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($ip['vlan_id'])): ?>
                                            <span class="vlan-badge">
                                                <i class="fas fa-network-wired"></i>
                                                VLAN <?= htmlspecialchars($ip['vlan_id']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="no-vlan">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="description-info">
                                        <?php if (!empty($ip['description'])): ?>
                                            <div class="description-text" title="<?= htmlspecialchars($ip['description']) ?>">
                                                <?= mb_strlen($ip['description']) > 50 ? mb_substr(htmlspecialchars($ip['description']), 0, 50) . '...' : htmlspecialchars($ip['description']) ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="no-description">Non défini</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (isset($ip['is_used']) && $ip['is_used']): ?>
                                            <span class="status-indicator status-occupied">
                                                <i class="fas fa-circle"></i>
                                                Occupé
                                            </span>
                                        <?php else: ?>
                                            <span class="status-indicator status-free">
                                                <i class="fas fa-circle"></i>
                                                Libre
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="site-info">
                                        <?php if (!empty($ip['site_name'])): ?>
                                            <?= htmlspecialchars($ip['site_name']) ?>
                                        <?php else: ?>
                                            <span class="no-site">Non assigné</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons action-buttons-compact">
                                            <?php if ($equipmentUrl): ?>
                                                <a href="<?= $equipmentUrl ?>" class="btn-action btn-action-icon btn-view" title="Voir l'équipement">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="?page=ip-management&action=edit&id=<?= $ip['id'] ?>" 
                                               class="btn-action btn-action-icon btn-edit" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?page=ip-management&action=delete&id=<?= $ip['id'] ?>" 
                                               class="btn-action btn-action-icon btn-delete" title="Supprimer"
                                               onclick="return confirmDelete('<?= htmlspecialchars($ip['ip_address']) ?>')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Légende des statuts -->
                <div class="status-legend">
                    <h4><i class="fas fa-info-circle"></i> Légende des statuts</h4>
                    <div class="legend-items">
                        <div class="legend-item">
                            <span class="status-indicator status-occupied"><i class="fas fa-circle"></i> Occupé</span>
                            <small>Adresse IP assignée à un équipement (cliquez sur l'IP pour voir l'équipement)</small>
                        </div>
                        <div class="legend-item">
                            <span class="status-indicator status-free"><i class="fas fa-circle"></i> Libre</span>
                            <small>Adresse IP disponible pour assignation</small>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fonction de recherche améliorée
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const table = document.getElementById('ipAddressesTable');
            const rows = table.getElementsByTagName('tr');
            let visibleCount = 0;
            
            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;
                
                // Recherche dans toutes les cellules sauf les actions (dernière colonne)
                for (let j = 0; j < cells.length - 1; j++) {
                    const cellText = cells[j].textContent.toLowerCase();
                    if (cellText.includes(searchTerm)) {
                        found = true;
                        break;
                    }
                }
                
                if (found) {
                    row.style.display = '';
                    row.style.opacity = '1';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                    row.style.opacity = '0';
                }
            }
            
            // Afficher/cacher le message "aucun résultat"
            updateNoResultsMessage(visibleCount, searchTerm);
        });
        
        // Placeholder dynamique
        searchInput.setAttribute('placeholder', 'Rechercher par IP, VLAN, description, site...');
    }
    
    // Fonction pour afficher/cacher le message "aucun résultat"
    function updateNoResultsMessage(count, searchTerm) {
        let noResultsMsg = document.getElementById('noResultsMessage');
        
        if (count === 0 && searchTerm !== '') {
            if (!noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.id = 'noResultsMessage';
                noResultsMsg.className = 'no-results-message';
                noResultsMsg.innerHTML = `
                    <div class="no-results-content">
                        <i class="fas fa-search"></i>
                        <h3>Aucun résultat trouvé</h3>
                        <p>Aucune adresse IP ne correspond à votre recherche "<strong>${searchTerm}</strong>".</p>
                        <small>Essayez avec des termes différents ou vérifiez l'orthographe.</small>
                    </div>
                `;
                document.querySelector('.table-container').appendChild(noResultsMsg);
            } else {
                noResultsMsg.querySelector('p').innerHTML = `Aucune adresse IP ne correspond à votre recherche "<strong>${searchTerm}</strong>".`;
            }
            noResultsMsg.style.display = 'block';
        } else if (noResultsMsg) {
            noResultsMsg.style.display = 'none';
        }
    }
    
    // Auto-hide flash messages
    const flashAlert = document.querySelector('.alert');
    if (flashAlert) {
        setTimeout(() => {
            flashAlert.style.opacity = '0';
            setTimeout(() => {
                flashAlert.remove();
            }, 300);
        }, 5000);
    }
    
    // Animation d'apparition pour les lignes du tableau
    const ipRows = document.querySelectorAll('.ip-row');
    ipRows.forEach((row, index) => {
        row.style.opacity = '0';
        row.style.transform = 'translateY(20px)';
        setTimeout(() => {
            row.style.transition = 'all 0.3s ease';
            row.style.opacity = '1';
            row.style.transform = 'translateY(0)';
        }, index * 50);
    });
});

// Fonction de confirmation de suppression améliorée
function confirmDelete(ipAddress) {
    const confirmation = confirm(
        `Êtes-vous sûr de vouloir supprimer l'adresse IP ${ipAddress} ?\n\n` +
        'Cette action est irréversible.'
    );
    
    if (confirmation) {
        // Afficher un indicateur de chargement
        const deleteBtn = event.target.closest('.btn-delete');
        if (deleteBtn) {
            deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span class="btn-text">Suppression...</span>';
            deleteBtn.style.pointerEvents = 'none';
        }
    }
    
    return confirmation;
}
</script>

<style>
/* ===== STYLES POUR LES ADRESSES IP ===== */
.ip-row {
    transition: all 0.2s ease;
}

.ip-row:hover {
    background-color: #f8fafc;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.ip-main {
    min-width: 150px;
}

.ip-wrapper {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.ip-address {
    font-family: 'Courier New', monospace;
    font-weight: 700;
    color: #2563eb;
    font-size: 1rem;
    background-color: #eff6ff;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    border: 1px solid #bfdbfe;
    display: inline-block;
}

.tenant-info {
    font-size: 0.75rem;
    color: #6b7280;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* ===== INFORMATIONS RÉSEAU ===== */
.network-info {
    min-width: 200px;
}

.network-details {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
}

.network-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.network-item i {
    color: #6b7280;
    width: 12px;
    text-align: center;
}

.subnet-mask {
    font-family: 'Courier New', monospace;
    color: #059669;
    background-color: #ecfdf5;
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
    border: 1px solid #bbf7d0;
}

.gateway {
    font-family: 'Courier New', monospace;
    color: #7c3aed;
    background-color: #faf5ff;
    padding: 0.125rem 0.375rem;
    border-radius: 0.25rem;
    border: 1px solid #c4b5fd;
}

.no-network {
    color: #9ca3af;
    font-style: italic;
    font-size: 0.875rem;
}

/* ===== VLAN BADGE ===== */
.vlan-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    padding: 0.375rem 0.75rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #0c4a6e;
    border: 1px solid #7dd3fc;
}

.vlan-badge i {
    color: #0ea5e9;
}

.no-vlan {
    color: #9ca3af;
    font-style: italic;
}

/* ===== DESCRIPTION ===== */
.description-info {
    max-width: 250px;
}

.description-text {
    color: #374151;
    line-height: 1.4;
    cursor: help;
}

.no-description {
    color: #9ca3af;
    font-style: italic;
}

/* ===== STATUS INDICATORS ===== */
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

.status-occupied,
.status-used {
    background-color: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.status-free {
    background-color: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
}

.status-occupied i, .status-used i, .status-free i {
    font-size: 0.6rem;
}

.ip-address-link {
    text-decoration: none;
}

.ip-address-link:hover .ip-address {
    background-color: #dbeafe;
    border-color: #93c5fd;
}

.equipment-info {
    display: block;
    font-size: 0.8rem;
    color: #4b5563;
    font-weight: 500;
    margin-top: 0.2rem;
}

.equipment-link {
    color: #1d4ed8;
    text-decoration: none;
}

.equipment-link:hover {
    text-decoration: underline;
}

/* ===== SITE INFO ===== */
.site-info {
    color: #374151;
    font-weight: 500;
}

.no-site {
    color: #9ca3af;
    font-style: italic;
}

.stats-container {
    margin-bottom: 2rem;
}

.stats-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding: 1rem 0;
    border-bottom: 2px solid #e5e7eb;
}

.stats-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stats-title i {
    color: #6366f1;
}

.stats-context {
    display: flex;
    align-items: center;
}

.context-indicator {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.context-indicator.global {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #1e40af;
    border: 1px solid #93c5fd;
}

.context-indicator.filtered {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
    border: 1px solid #fbbf24;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.stat-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    padding: 1.5rem;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: #6366f1;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.stat-trend {
    font-size: 0.75rem;
    font-weight: 600;
}

.trend-positive {
    color: #059669;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.trend-warning {
    color: #dc2626;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.trend-info {
    color: #6366f1;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.usage-percentage {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-size: 0.65rem;
}

.usage-percentage.low {
    background-color: #dcfce7;
    color: #166534;
}

.usage-percentage.medium {
    background-color: #fef3c7;
    color: #92400e;
}

.usage-percentage.high {
    background-color: #fecaca;
    color: #991b1b;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
}

.empty-icon {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.empty-state h3 {
    font-size: 1.5rem;
    color: #374151;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #6b7280;
    margin-bottom: 2rem;
}

/* ===== TABLEAU MODERNE ===== */
.table-container {
    background: #fff;
    border-radius: 0.75rem;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
}

.table-modern {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.9rem;
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

/* ===== CARD HEADER IP ===== */
.card-header-ip {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.card-title-block {
    flex: 0 1 auto;
}

.card-actions-right {
    margin-left: auto;
}

.card-header-ip .context-value {
    font-weight: 600;
    color: #4b5563;
}

/* ===== BOUTONS D'ACTION ===== */
.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.action-buttons-compact {
    gap: 0.35rem;
    flex-wrap: nowrap;
}

.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border: 1px solid transparent;
    border-radius: 0.5rem;
    font-size: 0.8rem;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
    white-space: nowrap;
}

.btn-action-icon {
    padding: 0.5rem;
    min-width: 2.25rem;
}

.btn-action-icon .btn-text {
    display: none;
}

.btn-view {
    background-color: #eff6ff;
    color: #1d4ed8;
    border-color: #bfdbfe;
}

.btn-view:hover {
    background-color: #1d4ed8;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(29, 78, 216, 0.3);
}

.btn-edit {
    background-color: #f0fdf4;
    color: #166534;
    border-color: #bbf7d0;
}

.btn-edit:hover {
    background-color: #166534;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(22, 101, 52, 0.3);
}

.btn-delete {
    background-color: #fef2f2;
    color: #dc2626;
    border-color: #fecaca;
}

.btn-delete:hover {
    background-color: #dc2626;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.btn-text {
    display: none;
}

/* ===== LÉGENDE DES STATUTS ===== */
.status-legend {
    margin-top: 2rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border-radius: 0.75rem;
    border: 1px solid #e5e7eb;
}

.status-legend h4 {
    margin: 0 0 1rem 0;
    color: #374151;
    font-size: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.status-legend h4 i {
    color: #6366f1;
}

.legend-items {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.legend-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.legend-item small {
    color: #6b7280;
    font-size: 0.75rem;
    margin-left: 0.5rem;
}

/* ===== SEARCH BOX ALIGNMENT ===== */
.card-actions-right .search-box {
    position: relative;
    min-width: 220px;
}

.card-actions-right .search-box input {
    padding: 0.5rem 2rem 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    width: 100%;
}

.card-actions-right .search-box i {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

/* ===== RESPONSIVE DESIGN ===== */
@media (min-width: 1024px) {
    .action-buttons:not(.action-buttons-compact) .btn-text {
        display: inline;
    }
    
    .action-buttons {
        justify-content: flex-end;
    }
}

@media (max-width: 768px) {
    .stats-header {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .stats-title {
        font-size: 1.25rem;
    }
    
    .context-indicator {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    
    .stat-card {
        padding: 1rem;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .table-container {
        overflow-x: auto;
        border-radius: 0.5rem;
    }
    
    .table-modern thead th {
        padding: 1rem 0.75rem;
        font-size: 0.8rem;
    }
    
    .table-modern tbody td {
        padding: 1rem 0.75rem;
    }
    
    .ip-wrapper {
        gap: 0.125rem;
    }
    
    .network-details {
        gap: 0.25rem;
    }
    
    .network-item {
        font-size: 0.8rem;
    }
    
    .description-info {
        max-width: 200px;
    }
    
    .action-buttons {
        gap: 0.25rem;
    }
    
    .btn-action {
        padding: 0.375rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .status-legend {
        padding: 1rem;
    }
    
    .legend-items {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
}

@media (max-width: 480px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-header {
        padding: 0.5rem 0;
    }
    
    .stat-card {
        padding: 0.75rem;
    }
    
    .table-modern thead th {
        padding: 0.75rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .table-modern tbody td {
        padding: 0.75rem 0.5rem;
        font-size: 0.85rem;
    }
    
    .ip-address {
        font-size: 0.875rem;
        padding: 0.125rem 0.375rem;
    }
    
    .network-item {
        font-size: 0.75rem;
    }
    
    .subnet-mask, .gateway {
        font-size: 0.75rem;
        padding: 0.125rem 0.25rem;
    }
    
    .vlan-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
    
    .description-info {
        max-width: 150px;
    }
    
    .status-indicator {
        font-size: 0.7rem;
        padding: 0.375rem 0.75rem;
    }
    
    .btn-action {
        padding: 0.25rem 0.375rem;
        font-size: 0.7rem;
    }
    
    .action-buttons:not(.action-buttons-compact) {
        flex-direction: column;
        gap: 0.25rem;
        align-items: stretch;
    }
    
    .action-buttons-compact {
        flex-direction: row;
    }
    
    .ip-row:hover {
        transform: none;
        box-shadow: none;
    }
}

/* ===== MESSAGE AUCUN RÉSULTAT ===== */
.no-results-message {
    display: none;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(2px);
    z-index: 10;
    border-radius: 0.75rem;
}

.no-results-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    padding: 2rem;
    color: #6b7280;
}

.no-results-content i {
    font-size: 3rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

.no-results-content h3 {
    margin: 0 0 0.5rem 0;
    color: #374151;
    font-size: 1.25rem;
    font-weight: 600;
}

.no-results-content p {
    margin: 0 0 0.5rem 0;
    color: #6b7280;
}

.no-results-content small {
    color: #9ca3af;
    font-style: italic;
}

/* ===== AMÉLIORATION DE L'ÉTAT VIDE ===== */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border-radius: 0.75rem;
    margin: 2rem 0;
}

.empty-icon {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 1.5rem;
}

.empty-state h3 {
    font-size: 1.5rem;
    color: #374151;
    margin-bottom: 0.75rem;
    font-weight: 600;
}

.empty-state p {
    color: #6b7280;
    margin-bottom: 2rem;
    font-size: 1.1rem;
    line-height: 1.6;
}

/* Position relative pour le conteneur de table pour le message de recherche */
.table-container {
    position: relative;
}
</style>

<?php include 'views/partials/footer.php'; ?> 