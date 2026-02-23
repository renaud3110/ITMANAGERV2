<div class="page-header">
    <h1 class="page-title">Modifier le site</h1>
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
                   value="<?= htmlspecialchars($_POST['name'] ?? $site['name']) ?>">
        </div>

        <div class="form-group">
            <label for="tenant_id">Tenant *</label>
            <select id="tenant_id" name="tenant_id" class="form-control" required>
                <?php foreach ($tenants as $tenant): ?>
                    <option value="<?= $tenant['id'] ?>" <?= ($_POST['tenant_id'] ?? $site['tenant_id']) == $tenant['id'] ? 'selected' : '' ?>>
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
                   value="<?= htmlspecialchars($_POST['address'] ?? $site['address']) ?>">
        </div>

        <div class="form-group">
            <label for="is_default">Site par défaut</label>
            <select id="is_default" name="is_default" class="form-control">
                <option value="0" <?= ($_POST['is_default'] ?? $site['is_default']) == '0' ? 'selected' : '' ?>>Non</option>
                <option value="1" <?= ($_POST['is_default'] ?? $site['is_default']) == '1' ? 'selected' : '' ?>>Oui</option>
            </select>
        </div>

        <div class="form-info">
            <p><strong>Tenant actuel:</strong> <?= htmlspecialchars($site['tenant_name'] ?? 'N/A') ?></p>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Sauvegarder les modifications
            </button>
            <a href="?page=sites" class="btn btn-secondary">
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
    margin-bottom: 0.5rem;
    color: #6b7280;
}

.form-info p:last-child {
    margin-bottom: 0;
}

.form-actions {
    display: flex;
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
    margin-top: 2rem;
}
</style> 