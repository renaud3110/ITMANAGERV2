<div class="page-header">
    <h1 class="page-title">Gestion des Backups</h1>
</div>

<div class="stats-container">
    <div class="stats-header">
        <h2><i class="fas fa-chart-bar"></i> Statistiques Globales (30 derniers jours)</h2>
        <div class="context-indicator">
            <?php if ($currentTenant !== 'all'): ?>
                <i class="fas fa-filter"></i> Filtrage par tenant
            <?php else: ?>
                <i class="fas fa-globe"></i> Tous les tenants
            <?php endif; ?>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($globalStats['total_reports'] ?? 0) ?></div>
                <div class="stat-label">Rapports</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-tasks"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($globalStats['total_jobs'] ?? 0) ?></div>
                <div class="stat-label">Jobs</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-server"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($globalStats['total_vms'] ?? 0) ?></div>
                <div class="stat-label">VMs</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-database"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($globalStats['total_data_gb'] ?? 0, 1) ?> GB</div>
                <div class="stat-label">Données</div>
            </div>
        </div>
    </div>
</div>

<div class="content-sections">
    <div class="section">
        <h3><i class="fas fa-shield-alt"></i> Nakivo Backup</h3>
        <p>Gestion des rapports de backup Nakivo par tenant.</p>
        <a href="?page=backup&action=nakivo" class="btn btn-primary">
            <i class="fas fa-arrow-right"></i>
            Accéder à Nakivo
        </a>
    </div>
    
    <div class="section">
        <h3><i class="fas fa-cloud"></i> EuroBackup</h3>
        <p>Gestion des backups EuroBackup (en développement).</p>
        <a href="?page=backup&action=eurobackup" class="btn btn-secondary">
            <i class="fas fa-arrow-right"></i>
            Accéder à EuroBackup
        </a>
    </div>
</div>

<?php if (!empty($recentReports)): ?>
<div class="recent-reports">
    <h3><i class="fas fa-clock"></i> Rapports Récents</h3>
    <div class="table-container">
        <table class="table table-modern">
            <thead>
                <tr>
                    <th>Tenant</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Jobs</th>
                    <th>VMs</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentReports as $report): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($report['tenant_name'] ?? $report['client_name']) ?></strong>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($report['report_date'])) ?></td>
                    <td>
                        <span class="status-badge status-<?= strtolower($report['overall_status']) ?>">
                            <?= htmlspecialchars($report['overall_status']) ?>
                        </span>
                    </td>
                    <td><?= $report['total_jobs'] ?></td>
                    <td><?= $report['total_vms'] ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="?page=backup&action=report&id=<?= $report['id'] ?>" 
                               class="btn-action btn-view" title="Voir le rapport">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<style>
.stats-container {
    background: #fff;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stats-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.stats-header h2 {
    margin: 0;
    color: #374151;
    font-size: 1.25rem;
}

.context-indicator {
    font-size: 0.875rem;
    color: #6b7280;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.stat-card {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.stat-icon {
    width: 3rem;
    height: 3rem;
    background: #3b82f6;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-size: 1.25rem;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    line-height: 1;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.content-sections {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.section {
    background: #fff;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.section h3 {
    margin: 0 0 1rem 0;
    color: #374151;
    font-size: 1.125rem;
}

.section p {
    color: #6b7280;
    margin-bottom: 1rem;
}

.recent-reports {
    background: #fff;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.recent-reports h3 {
    margin: 0 0 1rem 0;
    color: #374151;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
}

.status-successful {
    background: #d1fae5;
    color: #065f46;
}

.status-failed {
    background: #fee2e2;
    color: #991b1b;
}

.status-stopped {
    background: #fef3c7;
    color: #92400e;
}

.status-partial {
    background: #dbeafe;
    color: #1e40af;
}

.status-unknown {
    background: #f3f4f6;
    color: #374151;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .content-sections {
        grid-template-columns: 1fr;
    }
    
    .stats-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
</style> 