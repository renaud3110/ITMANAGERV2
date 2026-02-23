<div class="page-header">
    <h1 class="page-title">Nouveau Site</h1>
    <a href="?page=sites" class="btn btn-secondary">
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
            <label for="name">Nom du site *</label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   class="form-control" 
                   required 
                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="tenant_id">Tenant *</label>
            <select id="tenant_id" name="tenant_id" class="form-control" required>
                <option value="">Sélectionnez un tenant</option>
                <?php foreach ($tenants as $tenant): ?>
                    <option value="<?= $tenant['id'] ?>" <?= ($_POST['tenant_id'] ?? '') == $tenant['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tenant['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="address">Adresse</label>
            <input type="text" 
                   id="address" 
                   name="address" 
                   class="form-control" 
                   value="<?= htmlspecialchars($_POST['address'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="is_default">Site par défaut</label>
            <select id="is_default" name="is_default" class="form-control">
                <option value="0" <?= ($_POST['is_default'] ?? '0') === '0' ? 'selected' : '' ?>>Non</option>
                <option value="1" <?= ($_POST['is_default'] ?? '') === '1' ? 'selected' : '' ?>>Oui</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Créer le site
            </button>
            <a href="?page=sites" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                Annuler
            </a>
        </div>
    </form>
</div>

<style>
.form-actions {
    display: flex;
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
    margin-top: 2rem;
}
</style> 