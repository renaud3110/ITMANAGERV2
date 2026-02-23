<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-file-alt"></i> 
        Rapport de Backup
        <?php if ($report['tenant_name']): ?>
            <span class="tenant-name">- <?= htmlspecialchars($report['tenant_name']) ?></span>
        <?php endif; ?>
    </h1>
    <div class="page-actions">
        <a href="?page=backup&action=nakivo" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

<div class="report-overview">
    <div class="overview-header">
        <h2>Vue d'ensemble</h2>
        <div class="report-date">
            <i class="fas fa-calendar"></i>
            <?= date('d/m/Y à H:i', strtotime($report['report_date'])) ?>
        </div>
    </div>
    
    <div class="overview-stats">
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $report['successful_jobs'] ?></div>
                <div class="stat-label">Jobs Réussis</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon failed">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $report['failed_jobs'] ?></div>
                <div class="stat-label">Jobs Échoués</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-pause-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $report['stopped_jobs'] ?></div>
                <div class="stat-label">Jobs Arrêtés</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon info">
                <i class="fas fa-server"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $report['total_vms'] ?></div>
                <div class="stat-label">VMs Total</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $report['successful_vms'] ?? 0 ?></div>
                <div class="stat-label">VMs Réussies</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon failed">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $report['failed_vms'] ?? 0 ?></div>
                <div class="stat-label">VMs Échouées</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="fas fa-pause-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= $report['stopped_vms'] ?? 0 ?></div>
                <div class="stat-label">VMs Arrêtées</div>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="fas fa-database"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($report['total_data_processed_gb'] ?? 0, 1) ?> GB</div>
                <div class="stat-label">Données Traitées</div>
            </div>
        </div>
    </div>
    
    <div class="overview-status">
        <div class="status-indicator">
            <span class="status-badge status-<?= strtolower($report['overall_status']) ?>">
                <?= htmlspecialchars($report['overall_status']) ?>
            </span>
        </div>
    </div>
</div>

<div class="content-tabs">
    <div class="tab-nav">
        <button class="tab-btn active" data-tab="jobs">
            <i class="fas fa-tasks"></i> Jobs (<?= count($jobs) ?>)
        </button>
        <button class="tab-btn" data-tab="storage">
            <i class="fas fa-hdd"></i> Stockage (<?= count($storage) ?>)
        </button>
    </div>
    
    <div class="tab-content">
        <!-- Onglet Jobs -->
        <div class="tab-pane active" id="jobs">
            <?php if (empty($jobs)): ?>
            <div class="empty-state">
                <i class="fas fa-tasks"></i>
                <h3>Aucun job trouvé</h3>
                <p>Aucun job de backup n'a été trouvé pour ce rapport.</p>
            </div>
            <?php else: ?>
            <div class="table-container">
                <table class="table table-modern" id="jobsTable">
                    <thead>
                        <tr>
                            <th>Nom du Job</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Début</th>
                            <th>Fin</th>
                            <th>Durée</th>
                            <th>Données</th>
                            <th>Vitesse</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $job): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($job['job_name']) ?></strong>
                                <?php if ($job['priority']): ?>
                                    <small class="priority-badge">Prio: <?= $job['priority'] ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($job['job_type']) ?></td>
                            <td>
                                <span class="status-badge status-<?= strtolower($job['status']) ?>">
                                    <?= htmlspecialchars($job['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($job['started_at']): ?>
                                    <?= date('d/m/Y H:i', strtotime($job['started_at'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($job['finished_at']): ?>
                                    <?= date('d/m/Y H:i', strtotime($job['finished_at'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($job['duration_seconds']): ?>
                                    <?= gmdate('H:i:s', $job['duration_seconds']) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($job['data_processed_gb']): ?>
                                    <?= number_format($job['data_processed_gb'], 1) ?> GB
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($job['speed_mbps']): ?>
                                    <?= number_format($job['speed_mbps'], 1) ?> MB/s
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?page=backup&action=job&id=<?= $job['id'] ?>" 
                                       class="btn-action btn-view" title="Voir les VMs">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Onglet Stockage -->
        <div class="tab-pane" id="storage">
            <?php if (empty($storage)): ?>
            <div class="empty-state">
                <i class="fas fa-hdd"></i>
                <h3>Aucun stockage trouvé</h3>
                <p>Aucune information de stockage n'a été trouvée pour ce rapport.</p>
            </div>
            <?php else: ?>
            <div class="storage-grid">
                <?php foreach ($storage as $store): ?>
                <div class="storage-card">
                    <div class="storage-header">
                        <h3><?= htmlspecialchars($store['storage_name']) ?></h3>
                        <div class="storage-usage">
                            <?php 
                            $usagePercent = $store['total_space_gb'] > 0 ? 
                                (($store['used_space_gb'] / $store['total_space_gb']) * 100) : 0;
                            ?>
                            <div class="usage-bar">
                                <div class="usage-fill" style="width: <?= $usagePercent ?>%"></div>
                            </div>
                            <span class="usage-text"><?= number_format($usagePercent, 1) ?>%</span>
                        </div>
                    </div>
                    
                    <div class="storage-stats">
                        <div class="stat-row">
                            <span class="stat-label">Espace total:</span>
                            <span class="stat-value"><?= number_format($store['total_space_gb'], 1) ?> GB</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Espace utilisé:</span>
                            <span class="stat-value"><?= number_format($store['used_space_gb'], 1) ?> GB</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Espace libre:</span>
                            <span class="stat-value"><?= number_format($store['free_space_gb'], 1) ?> GB</span>
                        </div>
                        <div class="stat-row">
                            <span class="stat-label">Backups:</span>
                            <span class="stat-value"><?= $store['total_backups'] ?></span>
                        </div>
                        <?php if ($store['space_savings_gb'] > 0): ?>
                        <div class="stat-row">
                            <span class="stat-label">Économies:</span>
                            <span class="stat-value savings"><?= number_format($store['space_savings_gb'], 1) ?> GB</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
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

.tenant-name {
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: normal;
}

.report-overview {
    background: #fff;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.overview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.overview-header h2 {
    margin: 0;
    color: #374151;
}

.report-date {
    color: #6b7280;
    font-size: 0.875rem;
}

.overview-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
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
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    font-size: 1.25rem;
    color: white;
}

.stat-icon.success { background: #10b981; }
.stat-icon.failed { background: #ef4444; }
.stat-icon.warning { background: #f59e0b; }
.stat-icon.info { background: #3b82f6; }
.stat-icon.primary { background: #8b5cf6; }
.stat-icon.secondary { background: #6b7280; }

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

.overview-status {
    text-align: center;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    font-size: 0.875rem;
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

.content-tabs {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.tab-nav {
    display: flex;
    border-bottom: 1px solid #e5e7eb;
}

.tab-btn {
    padding: 1rem 1.5rem;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
    border-bottom: 2px solid transparent;
    transition: all 0.2s;
}

.tab-btn:hover {
    color: #374151;
    background: #f9fafb;
}

.tab-btn.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
}

.tab-content {
    padding: 1.5rem;
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
}

.priority-badge {
    background: #fef3c7;
    color: #92400e;
    padding: 0.125rem 0.375rem;
    border-radius: 4px;
    font-size: 0.75rem;
    margin-left: 0.5rem;
}

.storage-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.storage-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
}

.storage-header {
    margin-bottom: 1rem;
}

.storage-header h3 {
    margin: 0 0 0.5rem 0;
    color: #374151;
    font-size: 1.125rem;
}

.storage-usage {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.usage-bar {
    flex: 1;
    height: 8px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
}

.usage-fill {
    height: 100%;
    background: #3b82f6;
    transition: width 0.3s ease;
}

.usage-text {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    min-width: 3rem;
}

.storage-stats {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
}

.stat-value {
    font-weight: 500;
    color: #374151;
}

.stat-value.savings {
    color: #10b981;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6b7280;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: #9ca3af;
}

.empty-state h3 {
    margin: 0 0 0.5rem 0;
    color: #374151;
}

.empty-state p {
    margin: 0;
}

@media (max-width: 768px) {
    .overview-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .storage-grid {
        grid-template-columns: 1fr;
    }
    
    .tab-nav {
        flex-direction: column;
    }
    
    .tab-btn {
        text-align: left;
    }
}
</style>

<script>
$(document).ready(function() {
    // Gestion des onglets
    $('.tab-btn').click(function() {
        $('.tab-btn').removeClass('active');
        $('.tab-pane').removeClass('active');
        
        $(this).addClass('active');
        $('#' + $(this).data('tab')).addClass('active');
    });
    
    // DataTable pour les jobs
    $('#jobsTable').DataTable({
        responsive: true,
        order: [[3, 'desc']], // Trier par date de début décroissante
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json'
        }
    });
});
</script> 