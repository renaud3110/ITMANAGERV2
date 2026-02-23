<div class="page-header">
    <h1 class="page-title">Nouveau Tenant</h1>
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
                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="domain">Domaine</label>
            <input type="text" 
                   id="domain" 
                   name="domain" 
                   class="form-control" 
                   placeholder="exemple.com"
                   value="<?= htmlspecialchars($_POST['domain'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" 
                      name="description" 
                      class="form-control" 
                      rows="3"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label for="nakivo_customer_name">Nom client Nakivo</label>
            <input type="text" 
                   id="nakivo_customer_name" 
                   name="nakivo_customer_name" 
                   class="form-control" 
                   placeholder="Nom du client dans Nakivo"
                   value="<?= htmlspecialchars($_POST['nakivo_customer_name'] ?? '') ?>">
            <small class="form-text">Nom du client tel qu'il apparaît dans la console Nakivo</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Créer le tenant
            </button>
            <a href="?page=tenants" class="btn btn-secondary">
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