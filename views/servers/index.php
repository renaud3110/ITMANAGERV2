<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Serveurs</h1>
        <div>
            <a href="?page=hardware" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <a href="?page=servers&action=create" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Ajouter un serveur
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card clickable" data-filter="all" title="Afficher tous les serveurs">
            <div class="stat-icon">
                <i class="fas fa-server"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= count($servers) ?></div>
                <div class="stat-label">Tous les serveurs</div>
            </div>
        </div>

        <div class="stat-card clickable" data-filter="physique" title="Afficher uniquement les serveurs physiques">
            <div class="stat-icon physique">
                <i class="fas fa-hdd"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= count(array_filter($servers, function($s) { return ($s['type'] ?? '') === 'Physique'; })) ?></div>
                <div class="stat-label">Physiques</div>
            </div>
        </div>

        <div class="stat-card clickable" data-filter="virtuel" title="Afficher uniquement les serveurs virtuels">
            <div class="stat-icon virtuel">
                <i class="fas fa-cloud"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= count(array_filter($servers, function($s) { return ($s['type'] ?? '') === 'Virtuel'; })) ?></div>
                <div class="stat-label">Virtuels</div>
            </div>
        </div>

        <div class="stat-card clickable" data-filter="rustdesk" title="Afficher uniquement les serveurs avec RustDesk">
            <div class="stat-icon rustdesk">
                <i class="fas fa-desktop"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">
                    <?= count(array_filter($servers, function($s) { return !empty($s['rustdesk_id']); })) ?>
                </div>
                <div class="stat-label">Avec RustDesk</div>
            </div>
        </div>
    </div>

    <!-- Filter Indicator -->
    <div class="filter-indicator" id="filterIndicator">
        <i class="fas fa-filter"></i>
        <span id="filterText">Filtre appliqué</span>
        <button type="button" class="clear-filter" id="clearFilter">
            <i class="fas fa-times"></i> Effacer
        </button>
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
                                <th class="col-name">Nom</th>
                                <th>Modèle</th>
                                <th>Températures</th>
                                <th>Site</th>
                                <th>OS</th>
                                <th>Accès à distance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($servers as $server): ?>
                                <tr>
                                    <td class="col-name">
                                        <a href="?page=servers&action=view&id=<?= $server['id'] ?>" class="text-decoration-none name-link">
                                            <strong><?= htmlspecialchars($server['name'] ?? $server['hostname'] ?? 'Sans nom') ?></strong>
                                        </a>
                                        <br><small class="text-muted">
                                            <span class="badge badge-<?= ($server['type'] ?? '') === 'Virtuel' ? 'info' : 'success' ?>">
                                                <?= htmlspecialchars($server['type'] ?? 'Physique') ?>
                                            </span>
                                        </small>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($server['model_brand'] ?? 'N/A') ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($server['model_name'] ?? 'N/A') ?></small>
                                    </td>
                                    <td class="col-temps">
                                        <?php
                                        $hasTemps = (isset($server['monitor_cpu_temp']) && $server['monitor_cpu_temp'] !== null)
                                            || (isset($server['monitor_gpu_temp']) && $server['monitor_gpu_temp'] !== null);
                                        $hasLastSeen = !empty($server['monitor_last_seen']);
                                        if ($hasLastSeen || $hasTemps):
                                            $diff = $hasLastSeen ? time() - strtotime($server['monitor_last_seen'] . ' UTC') : 999;
                                            $monOnline = $diff < 60;
                                        ?>
                                        <span class="badge badge-<?= $monOnline ? 'success' : 'danger' ?>" style="font-size:0.75rem;">
                                            <?= $monOnline ? '● En ligne' : ($hasLastSeen ? '○ Hors ligne' : '-') ?>
                                            <?php if (isset($server['monitor_cpu_temp']) && $server['monitor_cpu_temp'] !== null): ?>
                                                <span class="ms-1"><i class="fas fa-microchip" title="CPU"></i> <?= round($server['monitor_cpu_temp'], 0) ?>°C</span>
                                            <?php endif; ?>
                                            <?php if (isset($server['monitor_gpu_temp']) && $server['monitor_gpu_temp'] !== null): ?>
                                                <span class="ms-1"><i class="fas fa-video" title="GPU"></i> <?= round($server['monitor_gpu_temp'], 0) ?>°C</span>
                                            <?php endif; ?>
                                        </span>
                                        <?php else: ?>
                                        <small class="text-muted">—</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($server['site_name'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php
                                        if ($server['operating_system_name']):
                                            $osName = $server['operating_system_name'];
                                            $osVer = $server['os_version_name'] ?? '';
                                            echo htmlspecialchars(trim($osName . ($osVer ? ' ' . $osVer : '')));
                                        else: ?>
                                            <span class="text-muted">Non défini</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($server['teamviewer_id'])): ?>
                                            <a href="https://start.teamviewer.com/<?= htmlspecialchars($server['teamviewer_id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="TeamViewer">
                                                <i class="fas fa-desktop"></i> TV
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($server['rustdesk_id'])): ?>
                                            <a href="<?= ($rustdeskProtocol ?? 'rustdesk') ?>://<?= htmlspecialchars($server['rustdesk_id']) ?>"
                                               class="btn btn-sm rustdesk-btn rustdesk-loading"
                                               data-rustdesk-id="<?= htmlspecialchars($server['rustdesk_id']) ?>"
                                               title="RustDesk (<?= htmlspecialchars($server['rustdesk_id']) ?>)">
                                                <i class="fas fa-desktop"></i> RD
                                            </a>
                                        <?php endif; ?>
                                        <?php if (empty($server['teamviewer_id']) && empty($server['rustdesk_id'])): ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-inline" role="group">
                                            <a href="?page=servers&action=view&id=<?= $server['id'] ?>" class="btn btn-info btn-sm" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="?page=servers&action=edit&id=<?= $server['id'] ?>" class="btn btn-warning btn-sm" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?page=servers&action=delete&id=<?= $server['id'] ?>" class="btn btn-danger btn-sm" title="Supprimer"
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
                    <a href="?page=servers&action=create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Ajouter un serveur
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.stat-card.clickable {
    cursor: pointer;
    position: relative;
}

.stat-card.clickable::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(102, 126, 234, 0.1);
    border-radius: 12px;
    opacity: 0;
    transition: opacity 0.2s ease;
}

.stat-card.clickable:hover::after {
    opacity: 1;
}

.stat-card.active {
    border: 2px solid #667eea;
    box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
}

.filter-indicator {
    display: none;
    background: #667eea;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    align-items: center;
    gap: 0.5rem;
}

.filter-indicator.active {
    display: flex;
}

.filter-indicator .clear-filter {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.75rem;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-icon.physique {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.stat-icon.virtuel {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.stat-icon.rustdesk {
    background: linear-gradient(135deg, #f74c00 0%, #ff6b35 100%);
}

.stat-content { flex: 1; }
.stat-number { font-size: 2rem; font-weight: 700; color: #1f2937; line-height: 1; }
.stat-label { font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem; }

.col-name { min-width: 180px; white-space: nowrap; }
.col-name .name-link { display: inline-block; max-width: 100%; overflow: hidden; text-overflow: ellipsis; }

.search-box { position: relative; width: 250px; }
.search-box input { padding-right: 2.5rem; }
.search-box i {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

.dataTables_wrapper { padding: 0; }
.dataTables_length { margin-bottom: 1rem; }
.dataTables_length select {
    padding: 0.375rem 0.75rem;
    border: 1px solid #d1d3e2;
    border-radius: 0.35rem;
    background-color: #fff;
    color: #5a5c69;
    font-size: 0.875rem;
}
.dataTables_filter { display: none; }
.dataTables_wrapper .row:last-child {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 1.5rem;
    padding: 1rem 0;
    gap: 1rem;
    border-top: 1px solid #e5e7eb;
}
.dataTables_info {
    background: transparent;
    padding: 0.5rem 0;
    color: #6b7280;
    font-size: 0.875rem;
    margin: 0;
    flex-shrink: 0;
    font-weight: 500;
}
.dataTables_paginate {
    margin: 0;
    display: flex;
    justify-content: flex-end;
    flex-shrink: 0;
}
.dataTables_paginate .paging_simple_numbers {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    flex-wrap: nowrap;
}
.dataTables_paginate .paginate_button {
    padding: 0.5rem 0.75rem;
    margin: 0 0.125rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: #fff;
    color: #6b7280;
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 0.875rem;
    font-weight: 500;
    white-space: nowrap;
    display: inline-block;
    vertical-align: middle;
    min-width: auto;
}
.dataTables_paginate span,
.dataTables_paginate a {
    display: inline-block !important;
    float: none !important;
}
.dataTables_paginate .paginate_button:hover {
    background: #f9fafb;
    color: #374151;
    border-color: #d1d5db;
    transform: none;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}
.dataTables_paginate .paginate_button.current {
    background: #667eea;
    color: white;
    border-color: #667eea;
    box-shadow: 0 1px 2px rgba(102, 126, 234, 0.2);
}
.dataTables_paginate .paginate_button.disabled {
    background: #f9fafb;
    color: #d1d5db;
    border-color: #f3f4f6;
    cursor: not-allowed;
}
.dataTables_paginate .paginate_button.disabled:hover {
    background: #f9fafb;
    color: #d1d5db;
    border-color: #f3f4f6;
    transform: none;
    box-shadow: none;
}
@media (max-width: 768px) {
    .dataTables_wrapper .row:last-child {
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
    }
    .dataTables_info { text-align: center; order: 2; }
    .dataTables_paginate { justify-content: center; order: 1; }
    .dataTables_paginate .paginate_button {
        padding: 0.375rem 0.5rem;
        font-size: 0.8rem;
    }
}
.dataTables_paginate span,
.dataTables_paginate a {
    display: inline-block !important;
    float: none !important;
    vertical-align: middle !important;
}
.dataTables_paginate .paging_simple_numbers {
    white-space: nowrap !important;
}

.btn-group-inline { display: inline-flex; flex-wrap: nowrap; white-space: nowrap; }
.btn-group-inline .btn { margin-right: 2px; }

.rustdesk-btn.rustdesk-loading { color: #6b7280; border-color: #9ca3af; }
.rustdesk-btn.rustdesk-online { color: #16a34a; border-color: #16a34a; }
.rustdesk-btn.rustdesk-offline { color: #dc2626; border-color: #dc2626; }
</style>

<script>
window.addEventListener('load', function() {
    if (typeof jQuery === 'undefined') return;
    jQuery(document).ready(function($) {
        var table = $('#dataTable').DataTable({
            "language": {
                "sProcessing": "Traitement en cours...",
                "sSearch": "Rechercher&nbsp;:",
                "sLengthMenu": "Afficher _MENU_ éléments",
                "sInfo": "Affichage de _START_ à _END_ sur _TOTAL_ serveurs",
                "sInfoEmpty": "Affichage de 0 à 0 sur 0 serveur",
                "sInfoFiltered": "(filtré de _MAX_ serveurs au total)",
                "sZeroRecords": "Aucun serveur trouvé",
                "sEmptyTable": "Aucun serveur disponible",
                "oPaginate": {
                    "sFirst": "Premier",
                    "sPrevious": "Précédent",
                    "sNext": "Suivant",
                    "sLast": "Dernier"
                }
            },
            "pageLength": 25,
            "order": [[ 0, "asc" ]],
            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6">>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
        });

        $('#searchInput').on('keyup', function() {
            table.search(this.value).draw();
        });

        $('.stat-card.clickable').on('click', function() {
            var filter = $(this).data('filter');
            var filterText = $(this).find('.stat-label').text();
            $('.stat-card').removeClass('active');
            $(this).addClass('active');

            if (filter === 'all') {
                table.search('');
                table.columns().search('');
                table.draw();
                $('#filterIndicator').removeClass('active');
            } else {
                var searchTerm = '';
                var colFilter = -1;
                switch(filter) {
                    case 'physique': searchTerm = 'Physique'; colFilter = 0; break;
                    case 'virtuel': searchTerm = 'Virtuel'; colFilter = 0; break;
                    case 'rustdesk': searchTerm = 'RD'; colFilter = 5; break;
                }
                if (colFilter >= 0) {
                    table.search('').column(colFilter).search(searchTerm).draw();
                } else {
                    table.search(searchTerm).draw();
                }
                $('#filterIndicator').addClass('active');
                $('#filterText').text('Filtré par: ' + filterText);
            }
        });

        $('#clearFilter').on('click', function() {
            $('.stat-card').removeClass('active');
            $('.stat-card[data-filter="all"]').addClass('active');
            table.search('');
            table.columns().search('');
            table.draw();
            $('#filterIndicator').removeClass('active');
        });

        $('.stat-card[data-filter="all"]').addClass('active');

        var rustdeskIds = [];
        $('.rustdesk-btn[data-rustdesk-id]').each(function() {
            var id = $(this).data('rustdesk-id');
            if (id) rustdeskIds.push(id);
        });
        if (rustdeskIds.length > 0) {
            var pathDir = window.location.pathname.replace(/\/[^/]*$/, '') || '/';
            var apiBase = pathDir.endsWith('/') ? pathDir : pathDir + '/';
            fetch(apiBase + 'api/rustdesk_status_batch.php?ids=' + rustdeskIds.join(','))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    $('.rustdesk-btn[data-rustdesk-id]').each(function() {
                        var id = $(this).data('rustdesk-id');
                        var info = data[id] || { online: false };
                        $(this).removeClass('rustdesk-loading rustdesk-online rustdesk-offline');
                        $(this).addClass(info.online ? 'rustdesk-online' : 'rustdesk-offline');
                        $(this).attr('title', 'RustDesk (' + id + ') - ' + (info.online ? 'En ligne' : 'Hors ligne'));
                    });
                })
                .catch(function() {
                    $('.rustdesk-btn[data-rustdesk-id]').removeClass('rustdesk-loading').addClass('rustdesk-offline');
                });
        }
    });
});
</script>
