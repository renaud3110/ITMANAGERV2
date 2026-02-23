<div class="page-header">
    <h1 class="page-title">Gestion des Tenants</h1>
    <a href="?page=tenants&action=create" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Nouveau Tenant
    </a>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Domaine</th>
                <th>Description</th>
                <th>Client Nakivo</th>
                <th>Nombre de sites</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($tenants)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">
                        <i class="fas fa-building" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i><br>
                        Aucun tenant trouvé
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($tenants as $tenant): ?>
                    <tr>
                        <td><?= $tenant['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($tenant['name']) ?></strong>
                        </td>
                        <td><?= htmlspecialchars($tenant['domain'] ?? '') ?></td>
                        <td><?= htmlspecialchars($tenant['description'] ?? '') ?></td>
                        <td>
                            <?php if (!empty($tenant['nakivo_customer_name'])): ?>
                                <span class="nakivo-name">
                                    <i class="fas fa-shield-alt"></i>
                                    <?= htmlspecialchars($tenant['nakivo_customer_name']) ?>
                                </span>
                            <?php else: ?>
                                <span class="no-nakivo">
                                    <i class="fas fa-minus"></i>
                                    Non configuré
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="site-count">
                                <i class="fas fa-map-marker-alt"></i>
                                <?= $tenant['site_count'] ?? 0 ?>
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="?page=tenants&action=edit&id=<?= $tenant['id'] ?>" 
                                   class="action-btn action-edit" 
                                   title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?page=tenants&action=delete&id=<?= $tenant['id'] ?>" 
                                   class="action-btn action-delete" 
                                   title="Supprimer"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce tenant ? Tous les sites associés seront également supprimés.')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.site-count {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    color: #6b7280;
}

.site-count i {
    font-size: 0.875rem;
}

.nakivo-name {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    color: #059669;
    background: #d1fae5;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 500;
}

.no-nakivo {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    color: #6b7280;
    font-style: italic;
    font-size: 0.875rem;
}

.nakivo-name i, .no-nakivo i {
    font-size: 0.75rem;
}
</style> 