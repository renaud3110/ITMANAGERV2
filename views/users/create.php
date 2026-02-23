<div class="page-header">
    <h1 class="page-title">Nouvel Utilisateur</h1>
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
                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" 
                   id="email" 
                   name="email" 
                   class="form-control" 
                   required 
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="password">Mot de passe *</label>
            <input type="password" 
                   id="password" 
                   name="password" 
                   class="form-control" 
                   required 
                   minlength="6">
            <small class="form-text">Le mot de passe doit contenir au moins 6 caractères</small>
        </div>

        <div class="form-group">
            <label for="tenant_id">Tenant</label>
            <select id="tenant_id" name="tenant_id" class="form-control">
                <option value="">Aucun tenant spécifique</option>
                <?php foreach ($tenants as $tenant): ?>
                    <option value="<?= $tenant['id'] ?>" <?= ($_POST['tenant_id'] ?? '') == $tenant['id'] ? 'selected' : '' ?>>
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
                       <?= isset($_POST['is_global_admin']) ? 'checked' : '' ?>>
                Administrateur global
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Créer l'utilisateur
            </button>
            <a href="?page=users" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                Annuler
            </a>
        </div>
    </form>
</div>

<style>
.form-text {
    color: #6b7280;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
    margin-top: 2rem;
}
</style> 