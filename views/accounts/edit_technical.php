<?php include 'views/partials/header.php'; ?>

<div class="container mt-4">
    <div class="row mb-3">
        <div class="col">
            <h1>Modifier un compte technique</h1>
            <p class="text-muted">Modifiez les informations du compte technique</p>
        </div>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?= $error ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" action="?page=accounts&action=editTechnical&id=<?= $login['id'] ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="service_id" class="form-label">Service <span class="text-danger">*</span></label>
                            <select class="form-select" id="service_id" name="service_id" required>
                                <option value="">Sélectionnez un service</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?= $service['id'] ?>" <?= ($service['id'] == $login['service_id']) ? 'selected' : '' ?>>
                                        <?php if (!empty($service['logo'])): ?>
                                            <i class="<?= $service['logo'] ?>"></i> 
                                        <?php endif; ?>
                                        <?= htmlspecialchars($service['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($login['username']) ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <div class="form-text">Laissez vide pour conserver le mot de passe actuel</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($login['description'] ?? '') ?></textarea>
                            <div class="form-text">Exemple: Routeur principal, Firewall, Imprimante RH, etc.</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="account_tenant_id">Client:</label>
                            <select name="tenant_id" id="account_tenant_id" class="form-control" required>
                                <option value="">Sélectionnez un client</option>
                                <?php foreach ($tenants as $tenant): ?>
                                    <option value="<?= $tenant['id'] ?>" <?= $login['tenant_id'] == $tenant['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tenant['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="account_site_id">Site:</label>
                            <select name="site_id" id="account_site_id" class="form-control" required>
                                <option value="">Sélectionnez d'abord un client</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between mt-3">
                    <a href="?page=accounts&action=technicalList" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tenantSelect = document.getElementById('account_tenant_id');
    const siteSelect = document.getElementById('account_site_id');
    let currentSiteId = <?= json_encode($login['site_id'] ?? null) ?>;
    
    tenantSelect.addEventListener('change', function() {
        const tenantId = this.value;
        loadSites(tenantId);
    });
    
    function loadSites(tenantId) {
        // Réinitialiser le sélecteur de site
        siteSelect.innerHTML = '';
        siteSelect.disabled = true;
        
        if (tenantId) {
            // Charger les sites pour ce tenant
            fetch(`?page=sites&action=getSitesJson&tenant_id=${tenantId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erreur réseau');
                    }
                    return response.json();
                })
                .then(sites => {
                    siteSelect.innerHTML = '<option value="">Sélectionnez un site</option>';
                    
                    if (sites && sites.length > 0) {
                        sites.forEach(site => {
                            const option = document.createElement('option');
                            option.value = site.id;
                            option.textContent = site.name || site.nom;
                            if (site.id == currentSiteId) {
                                option.selected = true;
                            }
                            siteSelect.appendChild(option);
                        });
                        siteSelect.disabled = false;
                    } else {
                        siteSelect.innerHTML = '<option value="">Aucun site disponible</option>';
                    }
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des sites:', error);
                    siteSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                });
        } else {
            siteSelect.innerHTML = '<option value="">Sélectionnez d\'abord un client</option>';
        }
    }
    
    // Charger les sites si un tenant est déjà sélectionné
    if (tenantSelect.value) {
        loadSites(tenantSelect.value);
    }
});
</script>

<?php include 'views/partials/footer.php'; ?> 