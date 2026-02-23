<?php include 'views/partials/header.php'; ?>

<div class="container mt-4">
    <div class="row mb-3">
        <div class="col">
            <h1>Ajouter un compte technique</h1>
            <p class="text-muted">Créez un compte qui n'est pas associé à une personne (routeur, équipement, compte générique...)</p>
        </div>
    </div>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?= $error ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($flash) && $flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <?= $flash['message'] ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" action="?page=accounts&action=technical">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="service_id" class="form-label">Service <span class="text-danger">*</span></label>
                            <select class="form-select" id="service_id" name="service_id" required>
                                <option value="">Sélectionnez un service</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?= $service['id'] ?>">
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
                            <input type="text" class="form-control" id="username" name="username" required>
                            <div class="form-text">Exemple: admin, root, router_admin, etc.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password">
                            <div class="form-text">Laissez vide si non applicable</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            <div class="form-text">Exemple: Routeur principal, Firewall, Imprimante RH, etc.</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="account_tenant_id">Client:</label>
                            <select name="tenant_id" id="account_tenant_id" class="form-control" required>
                                <option value="">Sélectionnez un client</option>
                                <?php foreach ($tenants as $tenant): ?>
                                    <option value="<?= $tenant['id'] ?>"><?= htmlspecialchars($tenant['name']) ?></option>
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
                    <a href="?page=accounts&action=index" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">Créer le compte technique</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tenantSelect = document.getElementById('account_tenant_id');
    const siteSelect = document.getElementById('account_site_id');
    
    tenantSelect.addEventListener('change', function() {
        const tenantId = this.value;
        
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
    });
    
    // Déclencher l'événement change si un tenant est déjà sélectionné
    if (tenantSelect.value) {
        tenantSelect.dispatchEvent(new Event('change'));
    }
});
</script>

<?php include 'views/partials/footer.php'; ?> 