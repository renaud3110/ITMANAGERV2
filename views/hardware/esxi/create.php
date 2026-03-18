<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Ajouter un serveur de virtualisation</h1>
        <a href="?page=hardware&section=esxi" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informations du serveur de virtualisation</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="hypervisor_type">Type d'hyperviseur</label>
                            <select class="form-control" id="hypervisor_type" name="hypervisor_type">
                                <option value="esxi" <?= ($_POST['hypervisor_type'] ?? 'esxi') === 'esxi' ? 'selected' : '' ?>>VMware ESXi</option>
                                <option value="proxmox" <?= ($_POST['hypervisor_type'] ?? '') === 'proxmox' ? 'selected' : '' ?>>Proxmox VE</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="name">Nom *</label>
                            <input type="text" class="form-control" id="name" name="name" required
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="Ex: ESXi-Prod-01">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="host">Hôte (IP ou hostname) *</label>
                            <input type="text" class="form-control" id="host" name="host" required
                                   value="<?= htmlspecialchars($_POST['host'] ?? '') ?>" placeholder="192.168.1.10">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="port">Port</label>
                            <input type="number" class="form-control" id="port" name="port" value="<?= (int)($_POST['port'] ?? 443) ?>" min="1" max="65535" title="ESXi: 443, Proxmox: 8006">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="site_id">Site</label>
                            <select class="form-control" id="site_id" name="site_id">
                                <option value="">Aucun</option>
                                <?php foreach ($sites ?? [] as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= ($_POST['site_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['name']) ?>
                                        <?php if (!empty($s['tenant_name'])): ?> (<?= htmlspecialchars($s['tenant_name']) ?>)<?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="ip_address_id">Adresse IP liée</label>
                            <select class="form-control" id="ip_address_id" name="ip_address_id">
                                <option value="">Aucune</option>
                                <?php foreach ($ipAddresses ?? [] as $ip): ?>
                                    <option value="<?= $ip['id'] ?>" <?= ($_POST['ip_address_id'] ?? '') == $ip['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ip['ip_address'] ?? $ip['id']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="discovery_interval_hours">Découverte automatique</label>
                            <select class="form-control" id="discovery_interval_hours" name="discovery_interval_hours">
                                <option value="0" <?= ($_POST['discovery_interval_hours'] ?? 1) == 0 ? 'selected' : '' ?>>Manuel uniquement (bouton Découvrir)</option>
                                <option value="1" <?= ($_POST['discovery_interval_hours'] ?? 1) == 1 ? 'selected' : '' ?>>Toutes les heures</option>
                                <option value="2" <?= ($_POST['discovery_interval_hours'] ?? 1) == 2 ? 'selected' : '' ?>>Toutes les 2 heures</option>
                                <option value="4" <?= ($_POST['discovery_interval_hours'] ?? 1) == 4 ? 'selected' : '' ?>>Toutes les 4 heures</option>
                                <option value="6" <?= ($_POST['discovery_interval_hours'] ?? 1) == 6 ? 'selected' : '' ?>>Toutes les 6 heures</option>
                            </select>
                            <small class="text-muted">Nécessite le cron <code>scripts/cron_discovery.php</code></small>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="2"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
                <hr>
                <h6 class="text-muted">Identifiants pour découverte par agent</h6>
                <p class="small text-muted">ESXi: root ou admin vSphere. Proxmox: root@pam ou utilisateur avec droits. L'agent sur le site utilisera ces identifiants.</p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cred_username">Utilisateur</label>
                            <input type="text" class="form-control" id="cred_username" name="cred_username" placeholder="root" value="<?= htmlspecialchars($_POST['cred_username'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cred_password">Mot de passe</label>
                            <input type="password" class="form-control" id="cred_password" name="cred_password" placeholder="••••••••">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
            </form>
        </div>
    </div>
</div>
<script>
document.getElementById('hypervisor_type').addEventListener('change', function() {
    var port = document.getElementById('port');
    if (this.value === 'proxmox' && (port.value === '443' || port.value === '')) {
        port.value = '8006';
    } else if (this.value === 'esxi' && port.value === '8006') {
        port.value = '443';
    }
});
</script>
