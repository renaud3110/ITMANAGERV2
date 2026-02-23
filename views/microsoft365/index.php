<?php
// Gestion des messages flash
$successMessage = $_SESSION['success_message'] ?? null;
$errorMessage = $_SESSION['error_message'] ?? $error ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<div class="page-header">
    <div class="page-title">
        <h1>
            <i class="fab fa-microsoft"></i>
            Microsoft 365
            <?php if (isset($tenantName)): ?>
                <span class="tenant-badge"><?= htmlspecialchars($tenantName) ?></span>
            <?php endif; ?>
        </h1>
        <p class="page-description">Gestion des licences et utilisateurs Microsoft 365</p>
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

<?php if ($showNoTenantMessage ?? false): ?>
    <div class="no-tenant-message">
        <div class="no-tenant-card">
            <i class="fas fa-building fa-3x"></i>
            <h3>Sélection de tenant requise</h3>
            <p>Veuillez sélectionner un tenant dans le menu déroulant en haut de la page pour accéder aux données Microsoft 365.</p>
            <div class="help-text">
                <i class="fas fa-info-circle"></i>
                Les données Microsoft 365 sont organisées par tenant (organisation).
            </div>
        </div>
    </div>
<?php else: ?>

<!-- Statistiques générales -->
<div class="stats-grid">
    <div class="stat-card stat-primary">
        <div class="stat-icon">
            <i class="fas fa-certificate"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($licenseStats['total_enabled'] ?? 0) ?></h3>
            <p>Licences totales</p>
            <div class="stat-subtext">
                <span class="text-success"><?= number_format($licenseStats['total_consumed'] ?? 0) ?> utilisées</span>
            </div>
        </div>
    </div>

    <div class="stat-card stat-success">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($userStats['total_users'] ?? 0) ?></h3>
            <p>Utilisateurs</p>
            <div class="stat-subtext">
                <span class="text-success"><?= number_format($userStats['active_licenses'] ?? 0) ?> actifs</span>
            </div>
        </div>
    </div>

    <div class="stat-card stat-warning">
        <div class="stat-icon">
            <i class="fas fa-chart-pie"></i>
        </div>
        <div class="stat-content">
            <h3><?= ($licenseStats['usage_percentage'] ?? 0) ?>%</h3>
            <p>Taux d'utilisation</p>
            <div class="stat-subtext">
                <span class="text-muted"><?= number_format($licenseStats['total_available'] ?? 0) ?> disponibles</span>
            </div>
        </div>
    </div>

    <div class="stat-card stat-info">
        <div class="stat-icon">
            <i class="fas fa-layer-group"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($licenseStats['total_sku_types'] ?? 0) ?></h3>
            <p>Types de licences</p>
            <div class="stat-subtext">
                <span class="text-muted"><?= number_format($userStats['unique_licenses_used'] ?? 0) ?> utilisés</span>
            </div>
        </div>
    </div>
</div>

<!-- Résumé des SKU licences -->
<div class="section-card">
    <div class="section-header">
        <h2>
            <i class="fas fa-list-alt"></i>
            Résumé des licences (SKU)
        </h2>
        <div class="section-actions">
            <?php if (!empty($upcomingRenewals)): ?>
                <span class="renewal-alert">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= count($upcomingRenewals) ?> renouvellement(s) à venir
                </span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($subscribedSkus)): ?>
        <div class="table-container">
            <table class="data-table" id="skuTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-tag"></i> SKU / Licence</th>
                        <th><i class="fas fa-chart-bar"></i> Utilisation</th>
                        <th><i class="fas fa-users"></i> Consommées</th>
                        <th><i class="fas fa-check-circle"></i> Activées</th>
                        <th><i class="fas fa-calendar-alt"></i> Renouvellement</th>
                        <th><i class="fas fa-clock"></i> Mise à jour</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subscribedSkus as $sku): 
                        $usagePercent = $sku['enabled_units'] > 0 ? round(($sku['consumed_units'] / $sku['enabled_units']) * 100, 1) : 0;
                        $usageClass = $usagePercent >= 90 ? 'usage-high' : ($usagePercent >= 70 ? 'usage-medium' : 'usage-low');
                    ?>
                        <tr>
                            <td>
                                <div class="sku-name">
                                    <strong><?= htmlspecialchars($sku['commercial_name'] ?: $sku['sku_part_number']) ?></strong>
                                    <?php if ($sku['commercial_name'] && $sku['commercial_name'] !== $sku['sku_part_number']): ?>
                                        <br><small class="sku-code"><?= htmlspecialchars($sku['sku_part_number']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <div class="usage-bar">
                                    <div class="usage-progress">
                                        <div class="usage-fill <?= $usageClass ?>" style="width: <?= $usagePercent ?>%"></div>
                                    </div>
                                    <span class="usage-text"><?= $usagePercent ?>%</span>
                                </div>
                            </td>
                            <td>
                                <span class="count-badge consumed"><?= number_format($sku['consumed_units']) ?></span>
                            </td>
                            <td>
                                <span class="count-badge enabled"><?= number_format($sku['enabled_units']) ?></span>
                            </td>
                            <td>
                                <?php if ($sku['renewal_date']): ?>
                                    <?php 
                                    $renewalDate = new DateTime($sku['renewal_date']);
                                    $today = new DateTime();
                                    $daysDiff = $today->diff($renewalDate)->days;
                                    $isUpcoming = $renewalDate > $today && $daysDiff <= 30;
                                    ?>
                                    <span class="renewal-date <?= $isUpcoming ? 'upcoming' : '' ?>">
                                        <i class="fas fa-calendar"></i>
                                        <?= $renewalDate->format('d/m/Y') ?>
                                        <?php if ($isUpcoming): ?>
                                            <br><small class="text-warning">Dans <?= $daysDiff ?> jours</small>
                                        <?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Non définie</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="last-updated">
                                    <?= date('d/m/Y H:i', strtotime($sku['last_updated'])) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-clipboard-list fa-3x"></i>
            <h3>Aucune licence trouvée</h3>
            <p>Aucune licence Microsoft 365 n'est configurée pour ce tenant.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Liste des utilisateurs -->
<div class="section-card">
    <div class="section-header">
        <h2>
            <i class="fas fa-users"></i>
            Utilisateurs et licences
        </h2>
        <div class="section-actions">
            <div class="search-container">
                <input type="text" 
                       id="userSearch" 
                       placeholder="Rechercher un utilisateur..."
                       value="<?= htmlspecialchars($search ?? '') ?>"
                       class="search-input">
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>
    </div>

    <?php if (!empty($userLicenses)): ?>
        <div class="table-container">
            <table class="data-table" id="usersTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-user"></i> Utilisateur</th>
                        <th><i class="fas fa-envelope"></i> Email</th>
                        <th><i class="fas fa-tag"></i> Licence</th>
                        <th><i class="fas fa-calendar-plus"></i> Assignée le</th>
                        <th><i class="fas fa-toggle-on"></i> État</th>
                        <th><i class="fas fa-sync-alt"></i> Dernière MAJ</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <?php foreach ($userLicenses as $user): ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <strong><?= htmlspecialchars($user['display_name']) ?></strong>
                                </div>
                            </td>
                            <td>
                                <span class="user-email"><?= htmlspecialchars($user['user_principal_name']) ?></span>
                            </td>
                            <td>
                                <span class="license-badge"><?= htmlspecialchars($user['commercial_name'] ?: $user['sku_part_number']) ?></span>
                                <?php if ($user['commercial_name'] && $user['commercial_name'] !== $user['sku_part_number']): ?>
                                    <br><small class="sku-code-small"><?= htmlspecialchars($user['sku_part_number']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="assign-date">
                                    <?= $user['assigned_date'] ? date('d/m/Y', strtotime($user['assigned_date'])) : 'Non définie' ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-<?= strtolower($user['state']) ?>">
                                    <?= htmlspecialchars($user['state']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="last-updated">
                                    <?= date('d/m/Y H:i', strtotime($user['last_updated'])) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="noResults" class="empty-state" style="display: none;">
            <i class="fas fa-search fa-3x"></i>
            <h3>Aucun résultat</h3>
            <p>Aucun utilisateur ne correspond à votre recherche.</p>
        </div>

    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-users fa-3x"></i>
            <h3>Aucun utilisateur trouvé</h3>
            <p>Aucun utilisateur avec des licences Microsoft 365 n'a été trouvé pour ce tenant.</p>
        </div>
    <?php endif; ?>
</div>

<?php endif; ?>

<style>
/* ===== STYLES GÉNÉRAUX ===== */
.page-header {
    margin-bottom: 2rem;
}

.tenant-badge {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    margin-left: 1rem;
}

/* ===== MESSAGE NO TENANT ===== */
.no-tenant-message {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 60vh;
}

.no-tenant-card {
    text-align: center;
    background: white;
    padding: 3rem;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    max-width: 500px;
}

.no-tenant-card i {
    color: #6b7280;
    margin-bottom: 1.5rem;
}

.no-tenant-card h3 {
    color: #374151;
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.no-tenant-card p {
    color: #6b7280;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}

.help-text {
    background: #f3f4f6;
    padding: 1rem;
    border-radius: 0.5rem;
    color: #4b5563;
    font-size: 0.875rem;
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

.section-actions {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.renewal-alert {
    background: #fef3c7;
    color: #92400e;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
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
.sku-name strong {
    color: #1f2937;
    font-weight: 600;
}

.sku-code {
    color: #6b7280;
    font-size: 0.75rem;
    font-weight: 400;
    font-family: 'Courier New', monospace;
}

.sku-code-small {
    color: #9ca3af;
    font-size: 0.7rem;
    font-weight: 400;
    font-family: 'Courier New', monospace;
}

.usage-bar {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.usage-progress {
    width: 80px;
    height: 8px;
    background: #f3f4f6;
    border-radius: 4px;
    overflow: hidden;
}

.usage-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.3s ease;
}

.usage-low { background: #10b981; }
.usage-medium { background: #f59e0b; }
.usage-high { background: #ef4444; }

.usage-text {
    font-weight: 600;
    min-width: 35px;
}

.count-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-weight: 600;
    font-size: 0.875rem;
}

.count-badge.consumed {
    background: #fee2e2;
    color: #dc2626;
}

.count-badge.enabled {
    background: #d1fae5;
    color: #065f46;
}

.renewal-date {
    display: inline-block;
}

.renewal-date.upcoming {
    color: #d97706;
    font-weight: 600;
}

.license-badge {
    background: #e0f2fe;
    color: #0277bd;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-active {
    background: #d1fae5;
    color: #065f46;
}

.status-inactive, .status-suspended {
    background: #fee2e2;
    color: #dc2626;
}

.status-warning {
    background: #fef3c7;
    color: #92400e;
}

.user-info strong {
    color: #1f2937;
    font-weight: 600;
}

.user-email {
    color: #6b7280;
    font-size: 0.875rem;
}

.assign-date, .last-updated {
    color: #6b7280;
    font-size: 0.875rem;
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

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
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
}

/* ===== MESSAGES FLASH ===== */
.alert {
    padding: 1rem 1.5rem;
    border-radius: 0.75rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-weight: 500;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert-error {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fca5a5;
}

.flash-message {
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de la recherche en temps réel
    const searchInput = document.getElementById('userSearch');
    const usersTable = document.getElementById('usersTable');
    const usersTableBody = document.getElementById('usersTableBody');
    const noResults = document.getElementById('noResults');
    
    if (searchInput && usersTableBody) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const rows = usersTableBody.getElementsByTagName('tr');
            let visibleRows = 0;
            
            for (let row of rows) {
                const displayName = row.cells[0].textContent.toLowerCase();
                const email = row.cells[1].textContent.toLowerCase();
                const license = row.cells[2].textContent.toLowerCase();
                
                if (displayName.includes(searchTerm) || 
                    email.includes(searchTerm) || 
                    license.includes(searchTerm)) {
                    row.style.display = '';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            }
            
            // Afficher/masquer le message "aucun résultat"
            if (noResults) {
                if (visibleRows === 0 && searchTerm !== '') {
                    usersTable.style.display = 'none';
                    noResults.style.display = 'block';
                } else {
                    usersTable.style.display = 'table';
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
    
    // Animation des barres d'utilisation
    const usageFills = document.querySelectorAll('.usage-fill');
    usageFills.forEach(function(fill) {
        const width = fill.style.width;
        fill.style.width = '0%';
        setTimeout(function() {
            fill.style.width = width;
        }, 200);
    });
});
</script>