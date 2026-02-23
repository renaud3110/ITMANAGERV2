<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Serveurs</h1>
        <div>
            <a href="?page=hardware" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <!-- Dashboard cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Serveurs</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($servers) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-server fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Avec TeamViewer</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count(array_filter($servers, function($s) { return !empty($s['teamviewer_id']); })) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-desktop fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Servers Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Liste des Serveurs</h6>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Rechercher..." class="form-control form-control-sm">
                <i class="fas fa-search"></i>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($servers)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Modèle</th>
                                <th>Site</th>
                                <th>Système d'exploitation</th>
                                <th>TeamViewer</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($servers as $server): ?>
                                <tr>
                                    <td>
                                        <a href="?page=servers&action=view&id=<?= $server['id'] ?>" class="text-decoration-none">
                                            <strong><?= htmlspecialchars($server['name'] ?? 'Sans nom') ?></strong>
                                        </a>
                                        <br><small class="text-muted">
                                            <span class="badge badge-<?= $server['type'] === 'Virtuel' ? 'info' : 'success' ?>">
                                                <?= htmlspecialchars($server['type']) ?>
                                            </span>
                                        </small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($server['model_brand'] ?? 'N/A') ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($server['model_name'] ?? 'N/A') ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($server['site_name'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php if ($server['operating_system_name']): ?>
                                            <strong><?= htmlspecialchars($server['operating_system_name']) ?></strong>
                                            <?php if ($server['os_version_name']): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($server['os_version_name']) ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Non défini</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($server['teamviewer_id']): ?>
                                            <a href="https://start.teamviewer.com/<?= htmlspecialchars($server['teamviewer_id']) ?>" 
                                               target="_blank" 
                                               class="btn btn-primary btn-sm" title="Se connecter via TeamViewer">
                                                <i class="fas fa-desktop"></i>
                                                <?= htmlspecialchars($server['teamviewer_id']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Non défini</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="?page=servers&action=view&id=<?= $server['id'] ?>" 
                                               class="btn btn-info btn-sm" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="?page=servers&action=edit&id=<?= $server['id'] ?>" 
                                               class="btn btn-warning btn-sm" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?page=servers&action=delete&id=<?= $server['id'] ?>" 
                                               class="btn btn-danger btn-sm" title="Supprimer"
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce serveur ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-server fa-3x text-muted"></i>
                    </div>
                    <h4>Aucun serveur</h4>
                    <p class="text-muted">Aucun serveur n'a été enregistré pour le contexte sélectionné.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.search-box {
    position: relative;
    width: 250px;
}

.search-box input {
    padding-right: 2.5rem;
}

.search-box i {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}
</style>

<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
        },
        "pageLength": 25,
        "order": [[ 0, "asc" ]]
    });
    
    // Search functionality
    $('#searchInput').on('keyup', function() {
        $('#dataTable').DataTable().search(this.value).draw();
    });
});
</script> 