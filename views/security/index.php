<div class="page-header">
    <h1 class="page-title">Sécurité & Conformité</h1>
    <a href="?page=security&action=policies" class="btn btn-primary">
        <i class="fas fa-shield-alt"></i>
        Politiques de Sécurité
    </a>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <i class="fas fa-exclamation-triangle stat-icon" style="color: #ef4444;"></i>
        <div class="stat-number">0</div>
        <div class="stat-label">Alertes Actives</div>
    </div>
    <div class="stat-card">
        <i class="fas fa-key stat-icon" style="color: #f59e0b;"></i>
        <div class="stat-number">0</div>
        <div class="stat-label">Accès à Revoir</div>
    </div>
    <div class="stat-card">
        <i class="fas fa-lock stat-icon" style="color: #10b981;"></i>
        <div class="stat-number">0</div>
        <div class="stat-label">Audits Effectués</div>
    </div>
    <div class="stat-card">
        <i class="fas fa-user-shield stat-icon" style="color: #3b82f6;"></i>
        <div class="stat-number">0</div>
        <div class="stat-label">Utilisateurs Conformes</div>
    </div>
</div>

<div class="security-grid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-eye"></i>
                Surveillance en Temps Réel
            </h3>
        </div>
        <div class="security-status">
            <div class="status-item status-ok">
                <i class="fas fa-check-circle"></i>
                <span>Système sécurisé</span>
            </div>
            <div class="status-item status-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <span>0 tentatives d'intrusion</span>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-tasks"></i>
                Actions Rapides
            </h3>
        </div>
        <div class="quick-actions">
            <a href="?page=security&action=scan" class="action-item">
                <i class="fas fa-search"></i>
                <span>Scanner Vulnérabilités</span>
            </a>
            <a href="?page=security&action=backup" class="action-item">
                <i class="fas fa-database"></i>
                <span>Sauvegarde Sécurité</span>
            </a>
            <a href="?page=security&action=logs" class="action-item">
                <i class="fas fa-file-alt"></i>
                <span>Consulter Logs</span>
            </a>
            <a href="?page=security&action=reports" class="action-item">
                <i class="fas fa-chart-bar"></i>
                <span>Rapports Sécurité</span>
            </a>
        </div>
    </div>
</div>

<style>
.security-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.security-status {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.status-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    border-radius: 6px;
    font-weight: 500;
}

.status-ok {
    background: #dcfce7;
    color: #166534;
}

.status-warning {
    background: #fef3c7;
    color: #92400e;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.action-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 6px;
    text-decoration: none;
    color: #374151;
    transition: all 0.2s ease;
    border: 1px solid #e5e7eb;
}

.action-item:hover {
    background: #f3f4f6;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.action-item i {
    font-size: 1.25rem;
    color: #667eea;
}
</style> 