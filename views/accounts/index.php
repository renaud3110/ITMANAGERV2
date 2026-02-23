<div class="page-header">
    <h1 class="page-title">Gestion des Comptes</h1>
    <div class="page-actions">
        <a href="?page=accounts&action=create" class="btn btn-primary">
            <i class="fas fa-user-plus"></i>
            Nouvelle Personne
        </a>
        <a href="?page=accounts&action=technical" class="btn btn-secondary">
            <i class="fas fa-cog"></i>
            Compte Technique
        </a>
        <a href="?page=accounts&action=technicalList" class="btn btn-info">
            <i class="fas fa-cogs"></i>
            Liste Comptes Techniques
        </a>
    </div>
</div>

<div class="context-info">
    <span class="context-item">
        <i class="fas fa-building"></i>
        <strong>Tenant:</strong>
        <?php if ($currentTenant === 'all'): ?>
            <span class="context-badge all">Tous les tenants</span>
        <?php else: ?>
            <span class="context-badge selected">Tenant <?= $currentTenant ?></span>
        <?php endif; ?>
    </span>
    
    <span class="context-separator">|</span>
    
    <span class="context-item">
        <i class="fas fa-map-marker-alt"></i>
        <strong>Site:</strong>
        <?php if ($currentSite === 'all'): ?>
            <span class="context-badge all">Tous les sites</span>
        <?php else: ?>
            <span class="context-badge selected">Site <?= $currentSite ?></span>
        <?php endif; ?>
    </span>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-number"><?= count($persons) ?></div>
        <div class="stat-label">Personnes</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-user-circle"></i>
        </div>
        <div class="stat-number">
            <?= array_sum(array_column($persons, 'logins_count')) ?>
        </div>
        <div class="stat-label">Comptes Total</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-envelope"></i>
        </div>
        <div class="stat-number">
            <?= count(array_filter($persons, function($p) { return !empty($p['email']); })) ?>
        </div>
        <div class="stat-label">Avec Email</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-user-slash"></i>
        </div>
        <div class="stat-number">
            <?= count(array_filter($persons, function($p) { return $p['logins_count'] == 0; })) ?>
        </div>
        <div class="stat-label">Sans Compte</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-users"></i>
            Liste des Personnes
        </h2>
        <div class="card-actions">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Rechercher une personne...">
            </div>
        </div>
    </div>
    
    <div class="card-body">
        <?php if (empty($persons)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Aucune personne trouvée</h3>
                <p>Commencez par créer une nouvelle personne pour gérer ses comptes.</p>
                <a href="?page=accounts&action=create" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i>
                    Créer une personne
                </a>
            </div>
        <?php else: ?>
            <div class="table-container">
                <table class="table" id="personsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom Complet</th>
                            <th>Email</th>
                            <th>Tenant</th>
                            <th>Comptes</th>
                            <th>Dernière Mise à Jour</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($persons as $person): ?>
                            <tr>
                                <td>
                                    <span class="id-badge"><?= $person['id'] ?></span>
                                </td>
                                <td>
                                    <div class="person-info">
                                        <strong><?= htmlspecialchars($person['prenom'] . ' ' . $person['nom']) ?></strong>
                                        <?php if ($person['logins_count'] > 0): ?>
                                            <span class="person-badge active">Actif</span>
                                        <?php else: ?>
                                            <span class="person-badge inactive">Inactif</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($person['email'])): ?>
                                        <a href="mailto:<?= htmlspecialchars($person['email']) ?>" class="email-link">
                                            <i class="fas fa-envelope"></i>
                                            <?= htmlspecialchars($person['email']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Non défini</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="tenant-badge">
                                        <i class="fas fa-building"></i>
                                        Tenant <?= $person['tenant_id'] ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="accounts-count">
                                        <span class="count-badge <?= $person['logins_count'] > 0 ? 'has-accounts' : 'no-accounts' ?>">
                                            <i class="fas fa-user-circle"></i>
                                            <?= $person['logins_count'] ?> compte<?= $person['logins_count'] > 1 ? 's' : '' ?>
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <span class="date-info">
                                        <?= date('d/m/Y H:i', strtotime($person['updated_at'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="?page=accounts&action=view&id=<?= $person['id'] ?>" 
                                           class="btn btn-sm btn-primary" 
                                           title="Voir les détails">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="?page=accounts&action=edit&id=<?= $person['id'] ?>" 
                                           class="btn btn-sm btn-secondary" 
                                           title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?page=accounts&action=delete&id=<?= $person['id'] ?>" 
                                           class="btn btn-sm btn-danger" 
                                           title="Supprimer"
                                           onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette personne ?')">
                                            <i class="fas fa-trash"></i>
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
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.page-actions {
    display: flex;
    gap: 1rem;
}

.context-info {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
    border-left: 4px solid #667eea;
}

.context-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.context-item i {
    color: #667eea;
}

.context-separator {
    color: #d1d5db;
    font-weight: bold;
}

.context-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.context-badge.all {
    background: #e0e7ff;
    color: #3730a3;
}

.context-badge.selected {
    background: #dcfce7;
    color: #166534;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-size: 1.25rem;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    line-height: 1;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.search-box {
    position: relative;
    width: 300px;
}

.search-box i {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

.search-box input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.5rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.search-box input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}

.empty-icon i {
    font-size: 2rem;
    color: #9ca3af;
}

.empty-state h3 {
    margin: 0 0 0.5rem 0;
    color: #1f2937;
    font-size: 1.25rem;
}

.empty-state p {
    margin: 0 0 2rem 0;
    color: #6b7280;
}

.id-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    background: #f3f4f6;
    color: #374151;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.person-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.person-badge {
    padding: 0.125rem 0.5rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.person-badge.active {
    background: #dcfce7;
    color: #166534;
}

.person-badge.inactive {
    background: #fef3c7;
    color: #92400e;
}

.email-link {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #667eea;
    text-decoration: none;
    font-size: 0.875rem;
}

.email-link:hover {
    color: #4f46e5;
}

.tenant-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.75rem;
    background: #e0e7ff;
    color: #3730a3;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.accounts-count .count-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.count-badge.has-accounts {
    background: #dcfce7;
    color: #166534;
}

.count-badge.no-accounts {
    background: #fee2e2;
    color: #991b1b;
}

.date-info {
    font-size: 0.875rem;
    color: #6b7280;
}

.btn-group {
    display: flex;
    gap: 0.25rem;
}

.text-muted {
    color: #9ca3af;
    font-style: italic;
}

/* Responsive design */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .context-info {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .search-box {
        width: 100%;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .btn-group {
        flex-direction: column;
        gap: 0.125rem;
    }
}
</style>

<script>
// Fonction de recherche
document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const table = document.getElementById('personsTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let row of rows) {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
});
</script> 