<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-tasks"></i> 
        Job de Backup
        <span class="job-name">- <?= htmlspecialchars($job['job_name']) ?></span>
    </h1>
    <div class="page-actions">
        <a href="?page=backup&action=report&id=<?= $job['report_id'] ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour au rapport
        </a>
    </div>
</div>

<div class="job-overview">
    <div class="overview-header">
        <h2>Détails du Job</h2>
        <div class="job-status">
            <span class="status-badge status-<?= strtolower($job['status']) ?>">
                <?= htmlspecialchars($job['status']) ?>
            </span>
        </div>
    </div>
    
    <div class="job-details">
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Nom du Job:</span>
                <span class="detail-value"><?= htmlspecialchars($job['job_name']) ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Type:</span>
                <span class="detail-value"><?= htmlspecialchars($job['job_type']) ?></span>
            </div>
        </div>
        
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Date de début:</span>
                <span class="detail-value">
                    <?php if ($job['started_at']): ?>
                        <?= date('d/m/Y à H:i:s', strtotime($job['started_at'])) ?>
                    <?php else: ?>
                        <span class="text-muted">Non défini</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Date de fin:</span>
                <span class="detail-value">
                    <?php if ($job['finished_at']): ?>
                        <?= date('d/m/Y à H:i:s', strtotime($job['finished_at'])) ?>
                    <?php else: ?>
                        <span class="text-muted">Non défini</span>
                    <?php endif; ?>
                </span>
            </div>
        </div>
        
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Durée:</span>
                <span class="detail-value">
                    <?php if ($job['duration_seconds']): ?>
                        <?= gmdate('H:i:s', $job['duration_seconds']) ?>
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Données traitées:</span>
                <span class="detail-value">
                    <?php if ($job['data_processed_gb']): ?>
                        <?= number_format($job['data_processed_gb'], 1) ?> GB
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </span>
            </div>
        </div>
        
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Vitesse:</span>
                <span class="detail-value">
                    <?php if ($job['speed_mbps']): ?>
                        <?= number_format($job['speed_mbps'], 1) ?> MB/s
                    <?php else: ?>
                        <span class="text-muted">-</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Objets source:</span>
                <span class="detail-value"><?= $job['source_objects'] ?? 0 ?></span>
            </div>
        </div>
        
        <?php if ($job['target_storage']): ?>
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Stockage cible:</span>
                <span class="detail-value"><?= htmlspecialchars($job['target_storage']) ?></span>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($job['priority']): ?>
        <div class="detail-row">
            <div class="detail-item">
                <span class="detail-label">Priorité:</span>
                <span class="detail-value">
                    <span class="priority-badge"><?= $job['priority'] ?></span>
                </span>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="vms-section">
    <div class="section-header">
        <h3>
            <i class="fas fa-server"></i> 
            VMs du Job (<?= count($job['vms']) ?>)
        </h3>
        <div class="vm-stats">
            <?php
            $successfulVMs = array_filter($job['vms'], function($vm) { return $vm['status'] === 'Successful'; });
            $failedVMs = array_filter($job['vms'], function($vm) { return $vm['status'] === 'Failed'; });
            $stoppedVMs = array_filter($job['vms'], function($vm) { return $vm['status'] === 'Stopped'; });
            $skippedVMs = array_filter($job['vms'], function($vm) { return $vm['status'] === 'Skipped'; });
            ?>
            <span class="stat-badge success"><?= count($successfulVMs) ?> réussies</span>
            <span class="stat-badge failed"><?= count($failedVMs) ?> échouées</span>
            <span class="stat-badge stopped"><?= count($stoppedVMs) ?> arrêtées</span>
            <span class="stat-badge skipped"><?= count($skippedVMs) ?> ignorées</span>
        </div>
    </div>
    
    <?php if (empty($job['vms'])): ?>
    <div class="empty-state">
        <i class="fas fa-server"></i>
        <h3>Aucune VM trouvée</h3>
        <p>Aucune VM n'a été trouvée pour ce job de backup.</p>
    </div>
    <?php else: ?>
    <div class="table-container">
        <table class="table table-modern" id="vmsTable">
            <thead>
                <tr>
                    <th>Nom de la VM</th>
                    <th>Status</th>
                    <th>Données</th>
                    <th>Vitesse</th>
                    <th>Durée</th>
                    <th>Points de récupération</th>
                    <th>Type de backup</th>
                    <th>Hôte source</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($job['vms'] as $vm): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($vm['vm_name']) ?></strong>
                    </td>
                    <td>
                        <span class="status-badge status-<?= strtolower($vm['status']) ?>">
                            <?= htmlspecialchars($vm['status']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($vm['data_processed_gb']): ?>
                            <?= number_format($vm['data_processed_gb'], 1) ?> GB
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($vm['speed_mbps']): ?>
                            <?= number_format($vm['speed_mbps'], 1) ?> MB/s
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($vm['duration_seconds']): ?>
                            <?= gmdate('H:i:s', $vm['duration_seconds']) ?>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($vm['recovery_points']): ?>
                            <?= $vm['recovery_points'] ?>
                            <?php if ($vm['latest_recovery_point']): ?>
                                <br><small class="text-muted">
                                    Dernier: <?= date('d/m/Y H:i', strtotime($vm['latest_recovery_point'])) ?>
                                </small>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($vm['backup_type']): ?>
                            <?= htmlspecialchars($vm['backup_type']) ?>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($vm['source_host']): ?>
                            <?= htmlspecialchars($vm['source_host']) ?>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.job-name {
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: normal;
}

.job-overview {
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

.job-details {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.detail-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: #f9fafb;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
}

.detail-label {
    font-weight: 500;
    color: #374151;
}

.detail-value {
    color: #111827;
    text-align: right;
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

.status-skipped {
    background: #f3f4f6;
    color: #374151;
}

.status-partial {
    background: #dbeafe;
    color: #1e40af;
}

.status-unknown {
    background: #f3f4f6;
    color: #374151;
}

.priority-badge {
    background: #fef3c7;
    color: #92400e;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}

.vms-section {
    background: #fff;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.section-header h3 {
    margin: 0;
    color: #374151;
}

.vm-stats {
    display: flex;
    gap: 0.5rem;
}

.stat-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}

.stat-badge.success {
    background: #d1fae5;
    color: #065f46;
}

.stat-badge.failed {
    background: #fee2e2;
    color: #991b1b;
}

.stat-badge.stopped {
    background: #fef3c7;
    color: #92400e;
}

.stat-badge.skipped {
    background: #f3f4f6;
    color: #374151;
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
    .detail-row {
        grid-template-columns: 1fr;
    }
    
    .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .vm-stats {
        flex-wrap: wrap;
    }
}
</style>

<script>
$(document).ready(function() {
    $('#vmsTable').DataTable({
        responsive: true,
        order: [[0, 'asc']], // Trier par nom de VM
        pageLength: 25,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json'
        }
    });
});
</script> 