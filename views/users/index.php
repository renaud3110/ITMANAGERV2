<div class="page-header">
    <h1 class="page-title">Gestion des Utilisateurs</h1>
    <a href="?page=users&action=create" class="btn btn-primary">
        <i class="fas fa-plus"></i>
        Nouvel Utilisateur
    </a>
</div>

<div class="table-container">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Tenant</th>
                <th>Admin Global</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem; color: #6b7280;">
                        <i class="fas fa-users" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i><br>
                        Aucun utilisateur trouvé
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($user['name']) ?></strong>
                        </td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <?php if (!empty($user['tenant_name'])): ?>
                                <span class="tenant-badge">
                                    <i class="fas fa-building"></i>
                                    <?= htmlspecialchars($user['tenant_name']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="admin-badge admin-<?= $user['is_global_admin'] ? 'yes' : 'no' ?>">
                                <?= $user['is_global_admin'] ? 'Oui' : 'Non' ?>
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="?page=users&action=edit&id=<?= $user['id'] ?>" 
                                   class="action-btn action-edit" 
                                   title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?page=users&action=delete&id=<?= $user['id'] ?>" 
                                   class="action-btn action-delete" 
                                   title="Supprimer"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')">
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

.admin-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
}

.admin-yes {
    background-color: #fef2f2;
    color: #dc2626;
}

.admin-no {
    background-color: #f0f9ff;
    color: #0284c7;
}

.text-muted {
    color: #9ca3af;
    font-style: italic;
}
</style> 