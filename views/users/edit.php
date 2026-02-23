<div class="page-header">
    <h1 class="page-title">Modifier l'utilisateur</h1>
    <a href="?page=users" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Retour à la liste
    </a>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card">
    <form method="POST" class="form">
        <div class="form-group">
            <label for="name">Nom *</label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   class="form-control" 
                   required 
                   value="<?= htmlspecialchars($_POST['name'] ?? $user['name']) ?>">
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" 
                   id="email" 
                   name="email" 
                   class="form-control" 
                   required 
                   value="<?= htmlspecialchars($_POST['email'] ?? $user['email']) ?>">
        </div>

        <div class="form-group">
            <label for="tenant_id">Tenant</label>
            <select id="tenant_id" name="tenant_id" class="form-control">
                <option value="">Aucun tenant spécifique</option>
                <?php foreach ($tenants as $tenant): ?>
                    <option value="<?= $tenant['id'] ?>" <?= ($_POST['tenant_id'] ?? $user['tenant_id']) == $tenant['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tenant['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" 
                       name="is_global_admin" 
                       value="1" 
                       <?= ($_POST['is_global_admin'] ?? $user['is_global_admin']) ? 'checked' : '' ?>>
                Administrateur global
            </label>
        </div>

        <div class="form-info">
            <p><strong>Compte créé:</strong> ID <?= $user['id'] ?></p>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Sauvegarder les modifications
            </button>
            <a href="?page=users" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                Annuler
            </a>
        </div>
    </form>
</div>

<style>
.form-info {
    background: #f9fafb;
    padding: 1rem;
    border-radius: 6px;
    margin: 1rem 0;
    border: 1px solid #e5e7eb;
}

.form-info p {
    margin: 0;
    color: #6b7280;
}

.form-actions {
    display: flex;
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
    margin-top: 2rem;
}
</style> 