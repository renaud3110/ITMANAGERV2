<div class="page-header">
    <h1 class="page-title">Modifier le tenant</h1>
    <a href="?page=tenants" class="btn btn-secondary">
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
            <label for="name">Nom du tenant *</label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   class="form-control" 
                   required 
                   value="<?= htmlspecialchars($tenant['name']) ?>">
        </div>

        <div class="form-group">
            <label for="domain">Domaine</label>
            <input type="text" 
                   id="domain" 
                   name="domain" 
                   class="form-control" 
                   placeholder="exemple.com"
                   value="<?= htmlspecialchars($tenant['domain']) ?>">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" 
                      name="description" 
                      class="form-control" 
                      rows="3"><?= htmlspecialchars($tenant['description']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="nakivo_customer_name">Nom client Nakivo</label>
            <input type="text" 
                   id="nakivo_customer_name" 
                   name="nakivo_customer_name" 
                   class="form-control" 
                   placeholder="Nom du client dans Nakivo"
                   value="<?= htmlspecialchars($tenant['nakivo_customer_name'] ?? '') ?>">
            <small class="form-text">Nom du client tel qu'il apparaît dans la console Nakivo</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Sauvegarder les modifications
            </button>
            <a href="?page=tenants" class="btn btn-secondary">
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