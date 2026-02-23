<div class="page-header">
    <h1 class="page-title">Gestion des Sites</h1>
    <a href="?page=sites&action=create" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Nouveau Site
    </a>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Tenant</th>
                <th>Adresse</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($sites)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 2rem; color: #6b7280;">
                        <i class="fas fa-map-marker-alt" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i><br>
                        Aucun site trouvé
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($sites as $site): ?>
                    <tr>
                        <td><?= $site['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($site['name']) ?></strong>
                        </td>
                        <td>
                            <span class="tenant-badge">
                                <i class="fas fa-building"></i>
                                <?= htmlspecialchars($site['tenant_name'] ?? 'N/A') ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($site['address'])): ?>
                                <i class="fas fa-map-pin"></i>
                                <?= htmlspecialchars($site['address']) ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="?page=sites&action=edit&id=<?= $site['id'] ?>" 
                                   class="action-btn action-edit" 
                                   title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?page=sites&action=delete&id=<?= $site['id'] ?>" 
                                   class="action-btn action-delete" 
                                   title="Supprimer"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce site ?')">
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
.tenant-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    background-color: #e0e7ff;
    color: #3730a3;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 500;
}

.tenant-badge i {
    font-size: 0.75rem;
}

.text-muted {
    color: #9ca3af;
    font-style: italic;
}
</style> 