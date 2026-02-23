<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-tools"></i> 
        Outils
    </h1>
</div>

<div class="content-section">
    <div class="section-header">
        <h2><i class="fas fa-chart-bar"></i> Vue d'ensemble</h2>
        <p>Statistiques globales des outils disponibles</p>
    </div>
    
    <!-- Statistiques globales -->
    <div class="stats-overview">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-file-invoice"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $globalStats['total_factures'] ?? 0 ?></div>
                <div class="stat-label">Total Factures DSD</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-key"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $globalStats['total_licenses'] ?? 0 ?></div>
                <div class="stat-label">Types de Licences</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon info">
                <i class="fas fa-calculator"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($globalStats['total_licenses_quantity'] ?? 0) ?></div>
                <div class="stat-label">Licences Total</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-euro-sign"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($globalStats['total_amount'] ?? 0, 2) ?> €</div>
                <div class="stat-label">Montant Total</div>
            </div>
        </div>
    </div>
</div>

<!-- Modules disponibles -->
<div class="content-section">
    <div class="section-header">
        <h2><i class="fas fa-cogs"></i> Modules Disponibles</h2>
        <p>Outils et fonctionnalités disponibles</p>
    </div>
    
    <div class="modules-grid">
        <div class="module-card">
            <div class="module-icon">
                <i class="fas fa-file-invoice"></i>
            </div>
            <div class="module-content">
                <h3>Historique DSD Factures</h3>
                <p>Gestion et suivi des factures DSD avec évolution des licences par tenant.</p>
                <div class="module-stats">
                    <span class="stat-item">
                        <i class="fas fa-building"></i>
                        <?= count($tenantsWithFactures) ?> tenants configurés
                    </span>
                    <span class="stat-item">
                        <i class="fas fa-file-invoice"></i>
                        <?= $globalStats['total_factures'] ?? 0 ?> factures
                    </span>
                </div>
                <a href="?page=tools&action=dsdFactures" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> Accéder
                </a>
            </div>
        </div>
        
        <!-- Placeholder pour futurs modules -->
        <div class="module-card disabled">
            <div class="module-icon">
                <i class="fas fa-chart-pie"></i>
            </div>
            <div class="module-content">
                <h3>Rapports Avancés</h3>
                <p>Génération de rapports personnalisés et analyses détaillées.</p>
                <div class="module-stats">
                    <span class="stat-item">
                        <i class="fas fa-clock"></i>
                        Bientôt disponible
                    </span>
                </div>
                <button class="btn btn-secondary" disabled>
                    <i class="fas fa-lock"></i> En développement
                </button>
            </div>
        </div>
        
        <div class="module-card disabled">
            <div class="module-icon">
                <i class="fas fa-download"></i>
            </div>
            <div class="module-content">
                <h3>Export de Données</h3>
                <p>Export des données en différents formats (CSV, Excel, PDF).</p>
                <div class="module-stats">
                    <span class="stat-item">
                        <i class="fas fa-clock"></i>
                        Bientôt disponible
                    </span>
                </div>
                <button class="btn btn-secondary" disabled>
                    <i class="fas fa-lock"></i> En développement
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Tenants avec factures DSD -->
<div class="content-section">
    <div class="section-header">
        <h2><i class="fas fa-building"></i> Tenants avec Factures DSD</h2>
        <p>Liste des tenants configurés pour le suivi DSD</p>
    </div>
    
    <?php if (empty($tenantsWithFactures)): ?>
    <div class="empty-state">
        <i class="fas fa-building"></i>
        <h3>Aucun tenant configuré</h3>
        <p>Aucun tenant n'a de configuration DSD pour le moment.</p>
    </div>
    <?php else: ?>
    <div class="table-container">
        <table class="table table-modern" id="tenantsTable">
            <thead>
                <tr>
                    <th><i class="fas fa-building"></i> Tenant</th>
                    <th><i class="fas fa-tag"></i> Nom DSD</th>
                    <th><i class="fas fa-file-invoice"></i> Factures</th>
                    <th><i class="fas fa-calendar"></i> Dernière Facture</th>
                    <th><i class="fas fa-cog"></i> Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tenantsWithFactures as $tenant): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($tenant['name']) ?></strong>
                    </td>
                    <td>
                        <span class="dsd-name-badge">
                            <?= htmlspecialchars($tenant['dsd_customer_name']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="facture-count-badge">
                            <?= $tenant['facture_count'] ?> facture(s)
                        </span>
                    </td>
                    <td>
                        <?php if ($tenant['last_facture_date']): ?>
                        <span class="date-badge">
                            <?= date('d/m/Y', strtotime($tenant['last_facture_date'])) ?>
                        </span>
                        <?php else: ?>
                        <span class="no-data">Aucune facture</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="?page=tools&action=dsdFactures" 
                           class="btn btn-sm btn-primary" title="Voir les factures">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<style>
.modules-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1rem;
}

.module-card {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: 1px solid #e0e0e0;
    transition: all 0.3s ease;
}

.module-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.module-card.disabled {
    opacity: 0.6;
    background: #f8f9fa;
}

.module-icon {
    font-size: 2.5rem;
    color: #007bff;
    margin-bottom: 1rem;
    text-align: center;
}

.module-card.disabled .module-icon {
    color: #6c757d;
}

.module-content h3 {
    margin: 0 0 0.5rem 0;
    color: #333;
    font-size: 1.2rem;
}

.module-content p {
    color: #666;
    margin-bottom: 1rem;
    line-height: 1.5;
}

.module-stats {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
    flex-wrap: wrap;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #666;
}

.stat-item i {
    color: #007bff;
}

.dsd-name-badge {
    background: #e3f2fd;
    color: #1976d2;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 500;
}

.facture-count-badge {
    background: #e8f5e8;
    color: #2e7d32;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.85rem;
    font-weight: 500;
}

.no-data {
    color: #999;
    font-style: italic;
}
</style>

<script>
$(document).ready(function() {
    $('#tenantsTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/French.json"
        },
        "order": [[2, "desc"]],
        "pageLength": 25
    });
});
</script> 