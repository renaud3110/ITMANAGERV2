<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Ajouter un NAS</h1>
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
                                   value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="host">Hôte (IP ou hostname) *</label>
                            <input type="text" class="form-control" id="host" name="host" required
                                   value="<?= htmlspecialchars($_POST['host'] ?? '') ?>" placeholder="192.168.1.10 ou nas.domaine.local">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="port">Port</label>
                            <input type="number" class="form-control" id="port" name="port" value="<?= (int)($_POST['port'] ?? 5000) ?>" min="1" max="65535">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="type">Type</label>
                            <select class="form-control" id="type" name="type">
                                <option value="synology" <?= ($_POST['type'] ?? 'synology') === 'synology' ? 'selected' : '' ?>>Synology</option>
                                <option value="qnap" <?= ($_POST['type'] ?? '') === 'qnap' ? 'selected' : '' ?>>QNAP</option>
                                <option value="generic" <?= ($_POST['type'] ?? '') === 'generic' ? 'selected' : '' ?>>Générique (SMB)</option>
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
                            <label for="tenant_id">Tenant</label>
                            <select class="form-control" id="tenant_id" name="tenant_id">
                                <option value="">Aucun</option>
                                <?php foreach ($tenants ?? [] as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= ($_POST['tenant_id'] ?? '') == $t['id'] ? 'selected' : '' ?>>
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
                                    <option value="<?= $s['id'] ?>" <?= ($_POST['site_id'] ?? '') == $s['id'] ? 'selected' : '' ?>>
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
                    <textarea class="form-control" id="description" name="description" rows="2"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>
                <hr>
                <h6 class="text-muted">Identifiants pour découverte par agent (optionnel)</h6>
                <p class="small text-muted">Enregistrez les identifiants admin du NAS pour que l'agent sur le site puisse découvrir les partages et volumes.</p>
                <p class="small text-warning"><i class="fas fa-info-circle"></i> Synology : désactivez la 2FA ou utilisez un mot de passe d'application. Port 5000 (HTTP) ou 5001 (HTTPS).</p>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cred_username">Utilisateur admin</label>
                            <input type="text" class="form-control" id="cred_username" name="cred_username" placeholder="admin" value="<?= htmlspecialchars($_POST['cred_username'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cred_password">Mot de passe</label>
                            <input type="password" class="form-control" id="cred_password" name="cred_password" placeholder="••••••••">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </form>
        </div>
    </div>
</div>
