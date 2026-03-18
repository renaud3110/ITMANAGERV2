<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Modifier le NAS</h1>
        <a href="?page=hardware&section=nas" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informations du NAS</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Nom *</label>
                            <input type="text" class="form-control" id="name" name="name" required
                                   value="<?= htmlspecialchars($nas['name'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="host">Hôte (IP ou hostname) *</label>
                            <input type="text" class="form-control" id="host" name="host" required
                                   value="<?= htmlspecialchars($nas['host'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="port">Port</label>
                            <input type="number" class="form-control" id="port" name="port" value="<?= (int)($nas['port'] ?? 5000) ?>" min="1" max="65535">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="type">Type</label>
                            <select class="form-control" id="type" name="type">
                                <option value="synology" <?= ($nas['type'] ?? 'synology') === 'synology' ? 'selected' : '' ?>>Synology</option>
                                <option value="qnap" <?= ($nas['type'] ?? '') === 'qnap' ? 'selected' : '' ?>>QNAP</option>
                                <option value="generic" <?= ($nas['type'] ?? '') === 'generic' ? 'selected' : '' ?>>Générique (SMB)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="ip_address_id">Adresse IP liée</label>
                            <select class="form-control" id="ip_address_id" name="ip_address_id">
                                <option value="">Aucune</option>
                                <?php foreach ($ipAddresses ?? [] as $ip): ?>
                                    <option value="<?= $ip['id'] ?>" <?= ($nas['ip_address_id'] ?? '') == $ip['id'] ? 'selected' : '' ?>>
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
                            <label for="tenant_id">Tenant</label>
                            <select class="form-control" id="tenant_id" name="tenant_id">
                                <option value="">Aucun</option>
                                <?php foreach ($tenants ?? [] as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= ($nas['tenant_id'] ?? '') == $t['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($t['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="site_id">Site</label>
                            <select class="form-control" id="site_id" name="site_id">
                                <option value="">Aucun</option>
                                <?php foreach ($sites ?? [] as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= ($nas['site_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['name']) ?>
                                        <?php if (!empty($s['tenant_name'])): ?> (<?= htmlspecialchars($s['tenant_name']) ?>)<?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="2"><?= htmlspecialchars($nas['description'] ?? '') ?></textarea>
                </div>
                <hr>
                <h6 class="text-muted">Identifiants pour découverte par agent</h6>
                <p class="small text-muted">L'agent sur le site utilise ces identifiants pour découvrir les partages. Laissez vide pour conserver l'existant.</p>
                <p class="small text-warning"><i class="fas fa-info-circle"></i> Synology : désactivez la 2FA pour le compte utilisé, ou utilisez un mot de passe d'application. Vérifiez que le port est correct (5000 HTTP, 5001 HTTPS).</p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cred_username">Utilisateur admin</label>
                            <input type="text" class="form-control" id="cred_username" name="cred_username" placeholder="admin" value="<?= htmlspecialchars($_POST['cred_username'] ?? ($nas['cred_username'] ?? '')) ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cred_password">Mot de passe</label>
                            <input type="password" class="form-control" id="cred_password" name="cred_password" placeholder="<?= !empty($nas['has_credentials']) ? 'Laisser vide pour ne pas changer' : 'Obligatoire pour découverte' ?>">
                        </div>
                    </div>
                </div>
                <?php if (!empty($nas['has_credentials'])): ?>
                    <p class="small text-success"><i class="fas fa-check"></i> Identifiants enregistrés</p>
                <?php endif; ?>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </form>
        </div>
    </div>
</div>
