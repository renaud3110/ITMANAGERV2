<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-shield-alt"></i> 
        Nakivo Backup
        <?php if ($tenantNakivoName): ?>
            <span class="tenant-filter">- <?= htmlspecialchars($tenantNakivoName) ?></span>
        <?php endif; ?>
    </h1>
    <div class="page-actions">
        <a href="?page=backup" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

<?php if ($currentTenant === 'all'): ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <strong>Filtrage :</strong> Sélectionnez un tenant pour voir ses rapports de backup spécifiques.
</div>
<?php endif; ?>

<?php if (empty($backupReports)): ?>
<div class="empty-state">
    <div class="empty-icon">
        <i class="fas fa-shield-alt"></i>
    </div>
    <h3>Aucun rapport de backup trouvé</h3>
    <p>
        <?php if ($tenantNakivoName): ?>
            Aucun rapport de backup Nakivo trouvé pour le tenant "<?= htmlspecialchars($tenantNakivoName) ?>".
        <?php else: ?>
            Aucun rapport de backup Nakivo disponible. Vérifiez que les rapports sont bien importés.
        <?php endif; ?>
    </p>
</div>
<?php else: ?>

<style>
.backup-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 20px;
    color: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.stat-card .stat-icon {
    font-size: 2.5em;
    margin-bottom: 15px;
    opacity: 0.9;
}

.stat-card .stat-content {
    text-align: center;
}

.stat-card .stat-value {
    font-size: 2.2em;
    font-weight: bold;
    margin-bottom: 5px;
    color: white;
}

.stat-card .stat-label {
    font-size: 0.9em;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #333;
}

/* Couleurs spécifiques pour chaque carte */
.stat-card:nth-child(1) {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-card:nth-child(2) {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stat-card:nth-child(3) {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.stat-card:nth-child(4) {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

/* Amélioration des badges de statut */
.job-badge, .vm-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: bold;
    margin: 2px;
}

.job-badge.success, .vm-badge.success {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    color: white;
}

.job-badge.failed, .vm-badge.failed {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.job-badge.stopped, .vm-badge.stopped {
    background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
    color: #333;
}

/* Amélioration du tableau */
.table-modern {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.table-modern thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.table-modern th {
    border: none;
    padding: 15px;
    font-weight: 600;
}

.table-modern td {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
}

.table-modern tbody tr:hover {
    background: linear-gradient(135deg, #f8f9ff 0%, #f0f2ff 100%);
}
</style>

<div class="backup-stats">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-file-alt"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= count($backupReports) ?></div>
            <div class="stat-label">Rapports</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-tasks"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= array_sum(array_column($backupReports, 'total_jobs')) ?></div>
            <div class="stat-label">Jobs Total</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-server"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= array_sum(array_column($backupReports, 'total_vms')) ?></div>
            <div class="stat-label">VMs Total</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= count(array_filter($backupReports, function($report) { return $report['overall_status'] === 'Successful'; })) ?></div>
            <div class="stat-label">Backups Réussis</div>
        </div>
    </div>
</div>

<div class="table-container">
    <table class="table table-modern" id="backupTable">
        <thead>
            <tr>
                <th>Tenant</th>
                <th>Date du Rapport</th>
                <th>Status Global</th>
                <th>Jobs</th>
                <th>VMs</th>
                <th>Durée</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($backupReports as $report): ?>
            <tr>
                <td>
                    <div class="tenant-info">
                        <strong><?= htmlspecialchars($report['tenant_name'] ?? $report['client_name']) ?></strong>
                        <?php if ($report['tenant_name']): ?>
                            <small class="text-muted"><?= htmlspecialchars($report['client_name']) ?></small>
                        <?php endif; ?>
                    </div>
                </td>
                <td>
                    <div class="date-info">
                        <div><?= date('d/m/Y', strtotime($report['report_date'])) ?></div>
                        <small class="text-muted"><?= date('H:i', strtotime($report['report_date'])) ?></small>
                    </div>
                </td>
                <td>
                    <span class="status-badge status-<?= strtolower($report['overall_status']) ?>">
                        <?= htmlspecialchars($report['overall_status']) ?>
                    </span>
                </td>
                <td>
                    <div class="jobs-info">
                        <div class="jobs-summary">
                            <?php if ($report['successful_jobs'] > 0): ?>
                                <span class="job-badge success"><?= $report['successful_jobs'] ?> ✓</span>
                            <?php endif; ?>
                            <?php if ($report['failed_jobs'] > 0): ?>
                                <span class="job-badge failed"><?= $report['failed_jobs'] ?> ✗</span>
                            <?php endif; ?>
                            <?php if ($report['stopped_jobs'] > 0): ?>
                                <span class="job-badge stopped"><?= $report['stopped_jobs'] ?> ⏸</span>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted">Total: <?= $report['total_jobs'] ?></small>
                    </div>
                </td>
                <td>
                    <div class="vms-info">
                        <div class="vms-summary">
                            <?php if ($report['successful_vms'] > 0): ?>
                                <span class="vm-badge success"><?= $report['successful_vms'] ?> ✓</span>
                            <?php endif; ?>
                            <?php if ($report['failed_vms'] > 0): ?>
                                <span class="vm-badge failed"><?= $report['failed_vms'] ?> ✗</span>
                            <?php endif; ?>
                            <?php if ($report['stopped_vms'] > 0): ?>
                                <span class="vm-badge stopped"><?= $report['stopped_vms'] ?> ⏸</span>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted">Total: <?= $report['total_vms'] ?></small>
                    </div>
                </td>
                <td>
                    <?php if ($report['duration_seconds']): ?>
                        <?= gmdate('H:i:s', $report['duration_seconds']) ?>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="action-buttons">
                        <a href="?page=backup&action=report&id=<?= $report['id'] ?>" 
                           class="btn-action btn-view" title="Voir le rapport détaillé">
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

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.tenant-filter {
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: normal;
}

.tenant-info {
    display: flex;
    flex-direction: column;
}

.date-info {
    display: flex;
    flex-direction: column;
}

.jobs-info, .vms-info {
    display: flex;
    flex-direction: column;
}

.jobs-summary, .vms-summary {
    display: flex;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.jobs-summary .success, .vms-summary .success {
    color: #059669;
    font-weight: 500;
}

.jobs-summary .failed, .vms-summary .failed {
    color: #dc2626;
    font-weight: 500;
}

.jobs-summary .stopped, .vms-summary .stopped {
    color: #d97706;
    font-weight: 500;
}

/* Ajout des couleurs pour les badges de statut */
.status-badge.status-successful {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.status-failed {
    background: #fee2e2;
    color: #991b1b;
}

.status-badge.status-stopped {
    background: #fef3c7;
    color: #92400e;
}

.status-badge.status-partial {
    background: #dbeafe;
    color: #1e40af;
}

.status-badge.status-unknown {
    background: #f3f4f6;
    color: #374151;
}

/* Badges pour jobs et VMs */
.job-badge, .vm-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
    margin-right: 0.25rem;
    margin-bottom: 0.25rem;
}

.job-badge.success, .vm-badge.success {
    background: #d1fae5;
    color: #065f46;
}

.job-badge.failed, .vm-badge.failed {
    background: #fee2e2;
    color: #991b1b;
}

.job-badge.stopped, .vm-badge.stopped {
    background: #fef3c7;
    color: #92400e;
}

.jobs-summary, .vms-summary {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    margin-bottom: 0.25rem;
}

.data-info {
    display: flex;
    flex-direction: column;
}

.data-size {
    font-weight: 500;
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

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.empty-icon {
    font-size: 3rem;
    color: #9ca3af;
    margin-bottom: 1rem;
}

.empty-state h3 {
    color: #374151;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #6b7280;
    max-width: 400px;
    margin: 0 auto;
}

@media (max-width: 768px) {
    .backup-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
}
</style>

<script>
$(document).ready(function() {
    $('#backupTable').DataTable({
        responsive: true,
        order: [[1, 'desc']], // Trier par date décroissante
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json'
        }
    });
});
</script> 