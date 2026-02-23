<?php
// Gestion des messages flash
$successMessage = $_SESSION['success_message'] ?? null;
$errorMessage = $_SESSION['error_message'] ?? $error ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<div class="page-header">
    <div class="page-title">
        <h1>
            <i class="fas fa-globe"></i>
            Gestion des Domaines
            <?php if ($currentTenant !== 'all'): ?>
                <span class="tenant-badge"><?= htmlspecialchars($tenantName) ?></span>
            <?php endif; ?>
        </h1>
        <p class="page-description">Gestion des noms de domaine par tenant</p>
    </div>
    
    <div class="page-actions">
        <a href="?page=domains&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            <span>Nouveau domaine</span>
        </a>
    </div>
</div>

<?php if ($successMessage): ?>
    <div class="alert alert-success flash-message">
        <i class="fas fa-check-circle"></i>
        <?= htmlspecialchars($successMessage) ?>
    </div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="alert alert-error flash-message">
        <i class="fas fa-exclamation-triangle"></i>
        <?= htmlspecialchars($errorMessage) ?>
    </div>
<?php endif; ?>

<!-- Statistiques générales -->
<div class="stats-grid">
    <div class="stat-card stat-primary">
        <div class="stat-icon">
            <i class="fas fa-globe"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($stats['total_domains'] ?? 0) ?></h3>
            <p>Domaines totaux</p>
            <div class="stat-subtext">
                <span class="text-success"><?= number_format($stats['managed_domains'] ?? 0) ?> gérés</span>
            </div>
        </div>
    </div>

    <div class="stat-card stat-success">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($stats['managed_domains'] ?? 0) ?></h3>
            <p>Domaines gérés</p>
            <div class="stat-subtext">
                <span class="text-muted"><?= number_format($stats['unmanaged_domains'] ?? 0) ?> non gérés</span>
            </div>
        </div>
    </div>

    <div class="stat-card stat-warning">
        <div class="stat-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($stats['expiring_soon_domains'] ?? 0) ?></h3>
            <p>Expirent bientôt</p>
            <div class="stat-subtext">
                <span class="text-danger"><?= number_format($stats['expired_domains'] ?? 0) ?> expirés</span>
            </div>
        </div>
    </div>

    <div class="stat-card stat-info">
        <div class="stat-icon">
            <i class="fas fa-sync-alt"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($stats['auto_renewal_domains'] ?? 0) ?></h3>
            <p>Renouvellement auto</p>
            <div class="stat-subtext">
                <span class="text-muted">
                    <?php 
                    $autoRenewalPercent = $stats['total_domains'] > 0 ? 
                        round(($stats['auto_renewal_domains'] / $stats['total_domains']) * 100, 1) : 0;
                    echo $autoRenewalPercent . '%';
                    ?> des domaines
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Alertes pour domaines critiques -->
<?php if (!empty($expired) || !empty($expiringSoon)): ?>
<div class="alerts-section">
    <?php if (!empty($expired)): ?>
        <div class="alert alert-danger">
            <div class="alert-header">
                <i class="fas fa-times-circle"></i>
                <strong>Domaines expirés (<?= count($expired) ?>)</strong>
            </div>
            <div class="alert-content">
                <?php foreach (array_slice($expired, 0, 3) as $domain): ?>
                    <div class="expired-domain">
                        <strong><?= htmlspecialchars($domain['domain_name']) ?></strong>
                        <span class="tenant-name">(<?= htmlspecialchars($domain['tenant_name']) ?>)</span>
                        - Expiré depuis <?= $domain['days_since_expired'] ?> jour(s)
                    </div>
                <?php endforeach; ?>
                <?php if (count($expired) > 3): ?>
                    <div class="more-domains">Et <?= count($expired) - 3 ?> autre(s)...</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($expiringSoon)): ?>
        <div class="alert alert-warning">
            <div class="alert-header">
                <i class="fas fa-clock"></i>
                <strong>Domaines expirant bientôt (<?= count($expiringSoon) ?>)</strong>
            </div>
            <div class="alert-content">
                <?php foreach (array_slice($expiringSoon, 0, 3) as $domain): ?>
                    <div class="expiring-domain">
                        <strong><?= htmlspecialchars($domain['domain_name']) ?></strong>
                        <span class="tenant-name">(<?= htmlspecialchars($domain['tenant_name']) ?>)</span>
                        - Expire dans <?= $domain['days_until_expiry'] ?> jour(s)
                    </div>
                <?php endforeach; ?>
                <?php if (count($expiringSoon) > 3): ?>
                    <div class="more-domains">Et <?= count($expiringSoon) - 3 ?> autre(s)...</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Liste des domaines -->
<div class="section-card">
    <div class="section-header">
        <h2>
            <i class="fas fa-list"></i>
            Liste des domaines
            <?php if ($currentTenant !== 'all'): ?>
                <span class="domain-count">(<?= count($domains) ?>)</span>
            <?php endif; ?>
        </h2>
        <div class="section-actions">
            <div class="search-container">
                <input type="text" 
                       id="domainSearch" 
                       placeholder="Rechercher un domaine..."
                       value="<?= htmlspecialchars($search ?? '') ?>"
                       class="search-input">
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>
    </div>

    <?php if (!empty($domains)): ?>
        <div class="table-container">
            <table class="data-table" id="domainsTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-globe"></i> Domaine</th>
                        <th><i class="fas fa-building"></i> Tenant</th>
                        <th><i class="fas fa-cog"></i> Gestion</th>
                        <th><i class="fas fa-calendar-alt"></i> Expiration</th>
                        <th><i class="fas fa-server"></i> Hébergeur</th>
                        <th><i class="fas fa-sync-alt"></i> Auto-renouv.</th>
                        <th><i class="fas fa-tools"></i> Actions</th>
                    </tr>
                </thead>
                <tbody id="domainsTableBody">
                    <?php foreach ($domains as $domain): ?>
                        <tr data-domain-id="<?= $domain['id'] ?>">
                            <td>
                                <div class="domain-info">
                                    <strong class="domain-name"><?= htmlspecialchars($domain['domain_name']) ?></strong>
                                    <div class="domain-status">
                                        <span class="status-badge status-<?= $domain['status_class'] ?? 'unknown' ?>">
                                            <?= htmlspecialchars($domain['status_text'] ?? 'Inconnu') ?>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="tenant-badge-small"><?= htmlspecialchars($domain['tenant_name']) ?></span>
                            </td>
                            <td>
                                <span class="management-badge <?= $domain['is_managed'] ? 'managed' : 'unmanaged' ?>">
                                    <i class="fas fa-<?= $domain['is_managed'] ? 'check' : 'times' ?>"></i>
                                    <?= $domain['is_managed'] ? 'Géré' : 'Non géré' ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($domain['expiry_date']): ?>
                                    <div class="expiry-info">
                                        <span class="expiry-date <?= $domain['status_class'] ?>">
                                            <?= date('d/m/Y', strtotime($domain['expiry_date'])) ?>
                                        </span>
                                        <?php if ($domain['days_until_expiry'] !== null): ?>
                                            <small class="days-info">
                                                <?php if ($domain['days_until_expiry'] < 0): ?>
                                                    Expiré depuis <?= abs($domain['days_until_expiry']) ?> jour(s)
                                                <?php else: ?>
                                                    Dans <?= $domain['days_until_expiry'] ?> jour(s)
                                                <?php endif; ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Non définie</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($domain['hosting_provider']): ?>
                                    <span class="hosting-provider"><?= htmlspecialchars($domain['hosting_provider']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">Non spécifié</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="auto-renewal-badge <?= $domain['auto_renewal'] ? 'enabled' : 'disabled' ?>">
                                    <i class="fas fa-<?= $domain['auto_renewal'] ? 'check-circle' : 'times-circle' ?>"></i>
                                    <?= $domain['auto_renewal'] ? 'Activé' : 'Désactivé' ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?page=domains&action=edit&id=<?= $domain['id'] ?>" 
                                       class="btn-action btn-edit" 
                                       title="Modifier">
                                        <i class="fas fa-edit"></i>
                                        <span class="btn-text">Modifier</span>
                                    </a>
                                    <button class="btn-action btn-delete" 
                                            onclick="confirmDelete(<?= $domain['id'] ?>, '<?= htmlspecialchars($domain['domain_name'], ENT_QUOTES) ?>')"
                                            title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                        <span class="btn-text">Supprimer</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="noResults" class="empty-state" style="display: none;">
            <i class="fas fa-search fa-3x"></i>
            <h3>Aucun résultat</h3>
            <p>Aucun domaine ne correspond à votre recherche.</p>
        </div>

    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-globe fa-3x"></i>
            <h3>Aucun domaine trouvé</h3>
            <p>
                <?php if ($currentTenant === 'all'): ?>
                    Aucun domaine n'a été configuré dans le système.
                <?php else: ?>
                    Aucun domaine n'a été configuré pour ce tenant.
                <?php endif; ?>
            </p>
            <a href="?page=domains&action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Ajouter le premier domaine
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
/* ===== STYLES GÉNÉRAUX ===== */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    gap: 2rem;
}

.page-title h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.page-description {
    color: #6b7280;
    font-size: 1rem;
    margin: 0;
}

.tenant-badge {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.page-actions {
    flex-shrink: 0;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

/* ===== GRILLE DE STATISTIQUES ===== */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.stat-primary .stat-icon {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
}

.stat-success .stat-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.stat-warning .stat-icon {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

.stat-info .stat-icon {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
}

.stat-content h3 {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0 0 0.25rem 0;
}

.stat-content p {
    color: #6b7280;
    margin: 0 0 0.5rem 0;
    font-weight: 500;
}

.stat-subtext {
    font-size: 0.875rem;
}

/* ===== ALERTES ===== */
.alerts-section {
    margin-bottom: 2rem;
}

.alert {
    padding: 1rem 1.5rem;
    border-radius: 0.75rem;
    margin-bottom: 1rem;
    border: 1px solid;
}

.alert-danger {
    background: #fef2f2;
    color: #dc2626;
    border-color: #fca5a5;
}

.alert-warning {
    background: #fffbeb;
    color: #d97706;
    border-color: #fcd34d;
}

.alert-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.alert-content {
    line-height: 1.5;
}

.expired-domain, .expiring-domain {
    margin-bottom: 0.25rem;
}

.tenant-name {
    color: #6b7280;
    font-size: 0.875rem;
}

.more-domains {
    font-style: italic;
    color: #6b7280;
    margin-top: 0.5rem;
}

/* ===== SECTIONS ===== */
.section-card {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
    overflow: hidden;
}

.section-header {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.section-header h2 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.domain-count {
    color: #6b7280;
    font-weight: 400;
}

.section-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

/* ===== RECHERCHE ===== */
.search-container {
    position: relative;
}

.search-input {
    padding: 0.75rem 1rem 0.75rem 3rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    width: 300px;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.search-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

/* ===== TABLEAUX ===== */
.table-container {
    padding: 1.5rem;
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
}

.data-table th {
    background: linear-gradient(135deg, #374151 0%, #1f2937 100%);
    color: white;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid #d1d5db;
    white-space: nowrap;
}

.data-table th i {
    margin-right: 0.5rem;
}

.data-table td {
    padding: 1rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: top;
}

.data-table tbody tr {
    transition: background-color 0.2s ease;
}

.data-table tbody tr:hover {
    background-color: #f9fafb;
}

/* ===== BADGES ET INDICATEURS ===== */
.domain-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.domain-name {
    color: #1f2937;
    font-weight: 600;
    font-size: 0.95rem;
}

.domain-status {
    display: flex;
    align-items: center;
}

.status-badge {
    padding: 0.125rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-valid {
    background: #d1fae5;
    color: #065f46;
}

.status-warning {
    background: #fef3c7;
    color: #92400e;
}

.status-expired {
    background: #fee2e2;
    color: #dc2626;
}

.status-unknown {
    background: #f3f4f6;
    color: #6b7280;
}

.tenant-badge-small {
    background: #e0f2fe;
    color: #0277bd;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.management-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.management-badge.managed {
    background: #d1fae5;
    color: #065f46;
}

.management-badge.unmanaged {
    background: #fee2e2;
    color: #dc2626;
}

.expiry-info {
    display: flex;
    flex-direction: column;
    gap: 0.125rem;
}

.expiry-date {
    font-weight: 600;
}

.expiry-date.valid {
    color: #059669;
}

.expiry-date.warning {
    color: #d97706;
}

.expiry-date.expired {
    color: #dc2626;
}

.days-info {
    color: #6b7280;
    font-size: 0.75rem;
}

.hosting-provider {
    color: #4b5563;
    font-weight: 500;
}

.auto-renewal-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.auto-renewal-badge.enabled {
    background: #d1fae5;
    color: #065f46;
}

.auto-renewal-badge.disabled {
    background: #f3f4f6;
    color: #6b7280;
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

.btn-text {
    display: none;
}

.btn-action i {
    font-size: 1rem;
}

.btn-edit {
    background: #fef3c7;
    color: #d97706;
    border-color: #fcd34d;
}

.btn-edit:hover {
    background: #d97706;
    color: white;
}

.btn-delete {
    background: #fee2e2;
    color: #dc2626;
    border-color: #fca5a5;
}

.btn-delete:hover {
    background: #dc2626;
    color: white;
}

/* ===== ÉTATS VIDES ===== */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #6b7280;
}

.empty-state i {
    margin-bottom: 1rem;
}

.empty-state h3 {
    color: #374151;
    margin-bottom: 0.5rem;
}

/* ===== MESSAGES FLASH ===== */
.alert.flash-message {
    animation: slideInDown 0.3s ease;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ===== UTILITAIRES ===== */
.text-success { color: #059669; }
.text-danger { color: #dc2626; }
.text-warning { color: #d97706; }
.text-muted { color: #6b7280; }

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .section-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .search-input {
        width: 100%;
    }
    
    .table-container {
        padding: 1rem;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.75rem 0.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 0.25rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la recherche en temps réel
    const searchInput = document.getElementById('domainSearch');
    const domainsTable = document.getElementById('domainsTable');
    const domainsTableBody = document.getElementById('domainsTableBody');
    const noResults = document.getElementById('noResults');
    
    if (searchInput && domainsTableBody) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const rows = domainsTableBody.getElementsByTagName('tr');
            let visibleRows = 0;
            
            for (let row of rows) {
                const domainName = row.cells[0].textContent.toLowerCase();
                const tenantName = row.cells[1].textContent.toLowerCase();
                const hostingProvider = row.cells[4].textContent.toLowerCase();
                
                if (domainName.includes(searchTerm) || 
                    tenantName.includes(searchTerm) || 
                    hostingProvider.includes(searchTerm)) {
                    row.style.display = '';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            }
            
            // Afficher/masquer le message "aucun résultat"
            if (noResults) {
                if (visibleRows === 0 && searchTerm !== '') {
                    domainsTable.style.display = 'none';
                    noResults.style.display = 'block';
                } else {
                    domainsTable.style.display = 'table';
                    noResults.style.display = 'none';
                }
            }
        });
    }
    
    // Auto-masquer les messages flash après 5 secondes
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(function(message) {
        setTimeout(function() {
            message.style.opacity = '0';
            message.style.transform = 'translateY(-20px)';
            setTimeout(function() {
                message.remove();
            }, 300);
        }, 5000);
    });
});

// Fonction de confirmation de suppression
function confirmDelete(domainId, domainName) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le domaine "${domainName}" ?\n\nCette action est irréversible.`)) {
        window.location.href = `?page=domains&action=delete&id=${domainId}`;
    }
}
</script>