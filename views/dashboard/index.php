<div class="page-header">
    <h1 class="page-title">Tableau de bord</h1>
    <div class="context-info">
        <span class="context-item">
            <i class="fas fa-building"></i>
            <strong>Tenant:</strong>
            <?php if ($currentTenant === 'all'): ?>
                <span class="context-badge all">Tous les tenants</span>
            <?php else: ?>
                <?php 
                $selectedTenant = null;
                foreach ($tenants as $tenant) {
                    if ($tenant['id'] == $currentTenant) {
                        $selectedTenant = $tenant;
                        break;
                    }
                }
                ?>
                <span class="context-badge selected"><?= htmlspecialchars($selectedTenant['name'] ?? 'Tenant inconnu') ?></span>
            <?php endif; ?>
        </span>
        
        <span class="context-separator">|</span>
        
        <span class="context-item">
            <i class="fas fa-map-marker-alt"></i>
            <strong>Site:</strong>
            <?php if ($currentSite === 'all'): ?>
                <span class="context-badge all">Tous les sites</span>
            <?php else: ?>
                <?php 
                $selectedSite = null;
                foreach ($sites as $site) {
                    if ($site['id'] == $currentSite) {
                        $selectedSite = $site;
                        break;
                    }
                }
                ?>
                <span class="context-badge selected"><?= htmlspecialchars($selectedSite['name'] ?? 'Site inconnu') ?></span>
            <?php endif; ?>
        </span>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-building"></i>
        </div>
        <div class="stat-number"><?= $stats['tenants'] ?></div>
        <div class="stat-label">
            <?= $currentTenant === 'all' ? 'Tenants Total' : 'Tenant Sélectionné' ?>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-map-marker-alt"></i>
        </div>
        <div class="stat-number"><?= $stats['sites'] ?></div>
        <div class="stat-label">
            <?= $currentTenant === 'all' ? 'Sites Total' : 'Sites du Tenant' ?>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-number"><?= $stats['users'] ?></div>
        <div class="stat-label">Utilisateurs</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-user-friends"></i>
        </div>
        <div class="stat-number"><?= $stats['persons'] ?></div>
        <div class="stat-label">Personnes</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Actions Rapides</h2>
    </div>
    
    <div class="quick-actions">
        <a href="?page=accounts&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Nouvelle Personne
        </a>
        <a href="?page=tenants&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Nouveau Tenant
        </a>
        <a href="?page=sites&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Nouveau Site
        </a>
        <a href="?page=users&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Nouvel Utilisateur
        </a>
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    gap: 2rem;
}

.page-title {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    flex-shrink: 0;
}

.context-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 1rem;
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
    border-radius: 8px;
    border: 1px solid rgba(102, 126, 234, 0.2);
    font-size: 0.9rem;
    flex-shrink: 0;
}

.context-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #374151;
}

.context-item i {
    color: #667eea;
    font-size: 0.875rem;
}

.context-item strong {
    color: #1f2937;
}

.context-separator {
    color: #9ca3af;
    font-weight: bold;
}

.context-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    white-space: nowrap;
}

.context-badge.all {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #1d4ed8;
    border: 1px solid #93c5fd;
}

.context-badge.selected {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 2px 4px rgba(102, 126, 234, 0.3);
}

.quick-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

@media (max-width: 1024px) {
    .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .context-info {
        width: 100%;
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .context-info {
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-start;
    }
    
    .context-separator {
        display: none;
    }
    
    .context-item {
        width: 100%;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
}
</style> 