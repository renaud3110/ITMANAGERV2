<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Modifier le Serveur</h1>
        <a href="?page=servers" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informations du Serveur</h6>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Nom du serveur *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($_POST['name'] ?? $server['name']) ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="type">Type *</label>
                            <select class="form-control" id="type" name="type" required>
                                <option value="Physique" <?= ($_POST['type'] ?? $server['type']) === 'Physique' ? 'selected' : '' ?>>Physique</option>
                                <option value="Virtuel" <?= ($_POST['type'] ?? $server['type']) === 'Virtuel' ? 'selected' : '' ?>>Virtuel</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="hostname">Hostname</label>
                            <input type="text" class="form-control" id="hostname" name="hostname" 
                                   value="<?= htmlspecialchars($_POST['hostname'] ?? $server['hostname']) ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="site_id">Site</label>
                            <select class="form-control" id="site_id" name="site_id">
                                <option value="">Sélectionner un site</option>
                                <?php foreach ($sites as $site): ?>
                                    <option value="<?= $site['id'] ?>" 
                                            <?= ($_POST['site_id'] ?? $server['site_id']) == $site['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($site['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="model_id">Modèle</label>
                            <select class="form-control" id="model_id" name="model_id">
                                <option value="">Sélectionner un modèle</option>
                                <?php foreach ($models as $model): ?>
                                    <option value="<?= $model['id'] ?>" 
                                            <?= ($_POST['model_id'] ?? $server['model_id']) == $model['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($model['manufacturer_name'] . ' - ' . $model['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="processor_model">Processeur</label>
                            <input type="text" class="form-control" id="processor_model" name="processor_model" 
                                   value="<?= htmlspecialchars($_POST['processor_model'] ?? $server['processor_model']) ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ram_total_gb">RAM Totale (GB)</label>
                            <input type="number" step="0.01" class="form-control" id="ram_total_gb" name="ram_total_gb" 
                                   value="<?= htmlspecialchars($_POST['ram_total_gb'] ?? $server['ram_total_gb']) ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ram_used_gb">RAM Utilisée (GB)</label>
                            <input type="number" step="0.01" class="form-control" id="ram_used_gb" name="ram_used_gb" 
                                   value="<?= htmlspecialchars($_POST['ram_used_gb'] ?? $server['ram_used_gb']) ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="operating_system_id">Système d'exploitation</label>
                            <select class="form-control" id="operating_system_id" name="operating_system_id">
                                <option value="">Sélectionner un OS</option>
                                <?php foreach ($operatingSystems as $os): ?>
                                    <option value="<?= $os['id'] ?>" 
                                            <?= ($_POST['operating_system_id'] ?? $server['operating_system_id']) == $os['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($os['name'] . ' - ' . $os['version']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ip_address_id">Adresse IP</label>
                            <select class="form-control" id="ip_address_id" name="ip_address_id">
                                <option value="">Sélectionner une IP</option>
                                <?php foreach ($ipAddresses as $ip): ?>
                                    <option value="<?= $ip['id'] ?>" 
                                            <?= ($_POST['ip_address_id'] ?? $server['ip_address_id']) == $ip['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($ip['ip_address']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="teamviewer_id">TeamViewer ID</label>
                            <input type="text" class="form-control" id="teamviewer_id" name="teamviewer_id" 
                                   value="<?= htmlspecialchars($_POST['teamviewer_id'] ?? $server['teamviewer_id']) ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Modifier le Serveur
                    </button>
                    <a href="?page=servers&action=view&id=<?= $server['id'] ?>" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div> 