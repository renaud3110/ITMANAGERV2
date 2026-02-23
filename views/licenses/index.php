<?php
// Gestion des messages flash
$successMessage = $_SESSION['success_message'] ?? null;
$errorMessage = $_SESSION['error_message'] ?? $error ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<div class="page-header">
    <div class="page-title">
        <h1>
            <i class="fas fa-key"></i>
            Gestion des Licences
            <?php if ($currentTenant !== 'all'): ?>
                <span class="tenant-badge"><?= htmlspecialchars($tenantName) ?></span>
            <?php endif; ?>
        </h1>
        <p class="page-description">Gestion des licences logicielles par tenant</p>
    </div>
    
    <div class="page-actions">
        <a href="?page=licenses&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            <span>Nouvelle licence</span>
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
            <i class="fas fa-key"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($stats['total_licenses'] ?? 0) ?></h3>
            <p>Licences</p>
            <div class="stat-subtext">
                <span class="text-success"><?= number_format($stats['total_license_count'] ?? 0) ?> unités totales</span>
            </div>
        </div>
    </div>

    <div class="stat-card stat-success">
        <div class="stat-icon">
            <i class="fas fa-user-lock"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($stats['licenses_with_login'] ?? 0) ?></h3>
            <p>Avec identifiants</p>
            <div class="stat-subtext">
                <span class="text-muted"><?= number_format($stats['licenses_with_password'] ?? 0) ?> avec mot de passe</span>
            </div>
        </div>
    </div>

    <div class="stat-card stat-warning">
        <div class="stat-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($stats['expiring_soon_licenses'] ?? 0) ?></h3>
            <p>Expirent bientôt</p>
            <div class="stat-subtext">
                <span class="text-danger"><?= number_format($stats['expired_licenses'] ?? 0) ?> expirées</span>
            </div>
        </div>
    </div>

    <div class="stat-card stat-info">
        <div class="stat-icon">
            <i class="fas fa-chart-bar"></i>
        </div>
        <div class="stat-content">
            <h3><?= ($stats['avg_license_count'] ?? 0) ?></h3>
            <p>Moyenne par licence</p>
            <div class="stat-subtext">
                <span class="text-muted">unités en moyenne</span>
            </div>
        </div>
    </div>
</div>

<!-- Alertes pour licences critiques -->
<?php if (!empty($expired) || !empty($expiringSoon)): ?>
<div class="alerts-section">
    <?php if (!empty($expired)): ?>
        <div class="alert alert-danger">
            <div class="alert-header">
                <i class="fas fa-times-circle"></i>
                <strong>Licences expirées (<?= count($expired) ?>)</strong>
            </div>
            <div class="alert-content">
                <?php foreach (array_slice($expired, 0, 3) as $license): ?>
                    <div class="expired-license">
                        <strong><?= htmlspecialchars($license['license_name']) ?></strong>
                        <span class="tenant-name">(<?= htmlspecialchars($license['tenant_name']) ?>)</span>
                        - Expirée depuis <?= $license['days_since_expired'] ?> jour(s)
                    </div>
                <?php endforeach; ?>
                <?php if (count($expired) > 3): ?>
                    <div class="more-licenses">Et <?= count($expired) - 3 ?> autre(s)...</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($expiringSoon)): ?>
        <div class="alert alert-warning">
            <div class="alert-header">
                <i class="fas fa-clock"></i>
                <strong>Licences expirant bientôt (<?= count($expiringSoon) ?>)</strong>
            </div>
            <div class="alert-content">
                <?php foreach (array_slice($expiringSoon, 0, 3) as $license): ?>
                    <div class="expiring-license">
                        <strong><?= htmlspecialchars($license['license_name']) ?></strong>
                        <span class="tenant-name">(<?= htmlspecialchars($license['tenant_name']) ?>)</span>
                        - Expire dans <?= $license['days_until_expiry'] ?> jour(s)
                    </div>
                <?php endforeach; ?>
                <?php if (count($expiringSoon) > 3): ?>
                    <div class="more-licenses">Et <?= count($expiringSoon) - 3 ?> autre(s)...</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Liste des licences -->
<div class="section-card">
    <div class="section-header">
        <h2>
            <i class="fas fa-list"></i>
            Liste des licences
            <?php if ($currentTenant !== 'all'): ?>
                <span class="license-count">(<?= count($licenses) ?>)</span>
            <?php endif; ?>
        </h2>
        <div class="section-actions">
            <div class="search-container">
                <input type="text" 
                       id="licenseSearch" 
                       placeholder="Rechercher une licence..."
                       value="<?= htmlspecialchars($search ?? '') ?>"
                       class="search-input">
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>
    </div>

    <?php if (!empty($licenses)): ?>
        <div class="table-container">
            <table class="data-table" id="licensesTable">
                <thead>
                    <tr>
                        <th><i class="fas fa-key"></i> Licence</th>
                        <th><i class="fas fa-building"></i> Tenant</th>
                        <th><i class="fas fa-user"></i> Identifiants</th>
                        <th><i class="fas fa-sort-numeric-up"></i> Nombre</th>
                        <th><i class="fas fa-calendar-alt"></i> Expiration</th>
                        <th><i class="fas fa-sticky-note"></i> Description</th>
                        <th><i class="fas fa-tools"></i> Actions</th>
                    </tr>
                </thead>
                <tbody id="licensesTableBody">
                    <?php foreach ($licenses as $license): ?>
                        <tr data-license-id="<?= $license['id'] ?>">
                            <td>
                                <div class="license-info">
                                    <strong class="license-name"><?= htmlspecialchars($license['license_name']) ?></strong>
                                    <div class="license-status">
                                        <span class="status-badge status-<?= $license['status_class'] ?? 'unknown' ?>">
                                            <?= htmlspecialchars($license['status_text'] ?? 'Inconnu') ?>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="tenant-badge-small"><?= htmlspecialchars($license['tenant_name']) ?></span>
                            </td>
                            <td>
                                <div class="credentials-info">
                                    <?php if (!empty($license['login'])): ?>
                                        <div class="login-info">
                                            <span class="login-badge">
                                                <i class="fas fa-user"></i>
                                                <?= htmlspecialchars($license['login']) ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($license['password'])): ?>
                                        <div class="password-info">
                                            <button class="btn-password" 
                                                    onclick="togglePassword(<?= $license['id'] ?>)"
                                                    title="Afficher/Masquer le mot de passe">
                                                <i class="fas fa-eye"></i>
                                                <span class="password-text" id="password-<?= $license['id'] ?>">••••••••</span>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (empty($license['login']) && empty($license['password'])): ?>
                                        <span class="text-muted">Aucun identifiant</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="license-count-badge"><?= number_format($license['license_count']) ?></span>
                            </td>
                            <td>
                                <?php if ($license['expiry_date']): ?>
                                    <div class="expiry-info">
                                        <span class="expiry-date <?= $license['status_class'] ?>">
                                            <?= date('d/m/Y', strtotime($license['expiry_date'])) ?>
                                        </span>
                                        <?php if ($license['days_until_expiry'] !== null): ?>
                                            <small class="days-info">
                                                <?php if ($license['days_until_expiry'] < 0): ?>
                                                    Expirée depuis <?= abs($license['days_until_expiry']) ?> jour(s)
                                                <?php else: ?>
                                                    Dans <?= $license['days_until_expiry'] ?> jour(s)
                                                <?php endif; ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Non définie</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($license['description']): ?>
                                    <div class="description-preview" title="<?= htmlspecialchars($license['description']) ?>">
                                        <?= htmlspecialchars(mb_strimwidth($license['description'], 0, 50, '...')) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Aucune description</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?page=licenses&action=edit&id=<?= $license['id'] ?>" 
                                       class="btn-action btn-edit" 
                                       title="Modifier">
                                        <i class="fas fa-edit"></i>
                                        <span class="btn-text">Modifier</span>
                                    </a>
                                    <button class="btn-action btn-delete" 
                                            onclick="confirmDelete(<?= $license['id'] ?>, '<?= htmlspecialchars($license['license_name'], ENT_QUOTES) ?>')"
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
            <p>Aucune licence ne correspond à votre recherche.</p>
        </div>

    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-key fa-3x"></i>
            <h3>Aucune licence trouvée</h3>
            <p>
                <?php if ($currentTenant === 'all'): ?>
                    Aucune licence n'a été configurée dans le système.
                <?php else: ?>
                    Aucune licence n'a été configurée pour ce tenant.
                <?php endif; ?>
            </p>
            <a href="?page=licenses&action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Ajouter la première licence
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

/* ===== MÊME STYLES QUE DOMAINES ===== */
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

.expired-license, .expiring-license {
    margin-bottom: 0.25rem;
}

.tenant-name {
    color: #6b7280;
    font-size: 0.875rem;
}

.more-licenses {
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

.license-count {
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

/* ===== BADGES SPÉCIFIQUES AUX LICENCES ===== */
.license-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.license-name {
    color: #1f2937;
    font-weight: 600;
    font-size: 0.95rem;
}

.license-status {
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

/* ===== IDENTIFIANTS ===== */
.credentials-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.login-badge {
    background: #e0f2fe;
    color: #0277bd;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
}

.btn-password {
    background: #fef3c7;
    color: #d97706;
    border: 1px solid #fcd34d;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    transition: all 0.2s ease;
}

.btn-password:hover {
    background: #d97706;
    color: white;
}

.password-text {
    font-family: 'Courier New', monospace;
    font-size: 0.75rem;
}

.license-count-badge {
    background: #ddd6fe;
    color: #7c3aed;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 600;
}

/* ===== EXPIRATION ===== */
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

/* ===== DESCRIPTION ===== */
.description-preview {
    color: #4b5563;
    font-size: 0.875rem;
    line-height: 1.4;
    cursor: help;
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
    const searchInput = document.getElementById('licenseSearch');
    const licensesTable = document.getElementById('licensesTable');
    const licensesTableBody = document.getElementById('licensesTableBody');
    const noResults = document.getElementById('noResults');
    
    if (searchInput && licensesTableBody) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            const rows = licensesTableBody.getElementsByTagName('tr');
            let visibleRows = 0;
            
            for (let row of rows) {
                const licenseName = row.cells[0].textContent.toLowerCase();
                const tenantName = row.cells[1].textContent.toLowerCase();
                const login = row.cells[2].textContent.toLowerCase();
                const description = row.cells[5].textContent.toLowerCase();
                
                if (licenseName.includes(searchTerm) || 
                    tenantName.includes(searchTerm) || 
                    login.includes(searchTerm) ||
                    description.includes(searchTerm)) {
                    row.style.display = '';
                    visibleRows++;
                } else {
                    row.style.display = 'none';
                }
            }
            
            // Afficher/masquer le message "aucun résultat"
            if (noResults) {
                if (visibleRows === 0 && searchTerm !== '') {
                    licensesTable.style.display = 'none';
                    noResults.style.display = 'block';
                } else {
                    licensesTable.style.display = 'table';
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

// Fonction pour afficher/masquer les mots de passe
function togglePassword(licenseId) {
    const passwordElement = document.getElementById('password-' + licenseId);
    const button = passwordElement.parentElement;
    const icon = button.querySelector('i');
    
    if (passwordElement.textContent === '••••••••') {
        // Afficher le mot de passe
        button.disabled = true;
        passwordElement.textContent = 'Chargement...';
        
        fetch(`?page=licenses&action=getPassword&license_id=${licenseId}`)
            .then(response => response.json())
            .then(data => {
                if (data.password !== undefined) {
                    passwordElement.textContent = data.password || 'Aucun mot de passe';
                    icon.className = 'fas fa-eye-slash';
                } else {
                    passwordElement.textContent = 'Erreur';
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                passwordElement.textContent = 'Erreur de chargement';
            })
            .finally(() => {
                button.disabled = false;
            });
    } else {
        // Masquer le mot de passe
        passwordElement.textContent = '••••••••';
        icon.className = 'fas fa-eye';
    }
}

// Fonction de confirmation de suppression
function confirmDelete(licenseId, licenseName) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer la licence "${licenseName}" ?\n\nCette action est irréversible.`)) {
        window.location.href = `?page=licenses&action=delete&id=${licenseId}`;
    }
}
</script>