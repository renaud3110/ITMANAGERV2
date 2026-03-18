<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Ordinateurs</h1>
        <div>
            <a href="?page=hardware" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card clickable" data-filter="all" title="Afficher tous les ordinateurs">
            <div class="stat-icon">
                <i class="fas fa-desktop"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= $totalComputers ?></div>
                <div class="stat-label">Tous les PCs</div>
            </div>
        </div>
        
        <div class="stat-card clickable" data-filter="windows10" title="Afficher uniquement les PC Windows 10">
            <div class="stat-icon win10">
                <i class="fab fa-windows"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= $windows10Count ?></div>
                <div class="stat-label">Avec Windows 10</div>
            </div>
        </div>
        
        <div class="stat-card clickable" data-filter="windows11" title="Afficher uniquement les PC Windows 11">
            <div class="stat-icon win11">
                <i class="fab fa-windows"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= $windows11Count ?></div>
                <div class="stat-label">Avec Windows 11</div>
            </div>
        </div>
        
        <div class="stat-card clickable" data-filter="rustdesk" title="Afficher uniquement les PC avec RustDesk">
            <div class="stat-icon rustdesk">
                <i class="fas fa-desktop"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">
                    <?= count(array_filter($computers, function($c) { return !empty($c['rustdesk_id']); })) ?>
                </div>
                <div class="stat-label">Avec RustDesk</div>
            </div>
        </div>
    </div>

    <!-- Filter Indicator -->
    <div class="filter-indicator" id="filterIndicator">
        <i class="fas fa-filter"></i>
        <span id="filterText">Filtre appliqué</span>
        <button class="clear-filter" id="clearFilter">
            <i class="fas fa-times"></i> Effacer
        </button>
    </div>

    <!-- Computers Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Liste des Ordinateurs</h6>
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="Rechercher..." class="form-control form-control-sm">
                <i class="fas fa-search"></i>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($computers)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th class="col-name">Nom</th>
                                <th>Modèle</th>
                                <th>Températures</th>
                                <th>OS</th>
                                <th>Personne attribuée</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($computers as $computer): ?>
                                <tr>
                                    <td class="col-name">
                                        <a href="?page=hardware&section=computers&action=view&id=<?= $computer['id'] ?>" class="text-decoration-none name-link">
                                            <strong><?= htmlspecialchars($computer['name'] ?? 'Sans nom') ?></strong>
                                        </a>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($computer['model_brand'] ?? 'N/A') ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($computer['model_name'] ?? 'N/A') ?></small>
                                    </td>
                                    <td class="col-temps">
                                        <?php
                                        $hasTemps = (isset($computer['monitor_cpu_temp']) && $computer['monitor_cpu_temp'] !== null)
                                            || (isset($computer['monitor_gpu_temp']) && $computer['monitor_gpu_temp'] !== null);
                                        $hasLastSeen = !empty($computer['monitor_last_seen']);
                                        if ($hasLastSeen || $hasTemps):
                                            $diff = $hasLastSeen ? time() - strtotime($computer['monitor_last_seen'] . ' UTC') : 999;
                                            $monOnline = $diff < 60;
                                        ?>
                                        <span class="badge badge-<?= $monOnline ? 'success' : 'danger' ?>" style="font-size:0.75rem;">
                                            <?= $monOnline ? '● En ligne' : ($hasLastSeen ? '○ Hors ligne' : '-') ?>
                                            <?php if (isset($computer['monitor_cpu_temp']) && $computer['monitor_cpu_temp'] !== null): ?>
                                                <span class="ms-1"><i class="fas fa-microchip" title="CPU"></i> <?= round($computer['monitor_cpu_temp'], 0) ?>°C</span>
                                            <?php endif; ?>
                                            <?php if (isset($computer['monitor_gpu_temp']) && $computer['monitor_gpu_temp'] !== null): ?>
                                                <span class="ms-1"><i class="fas fa-video" title="GPU"></i> <?= round($computer['monitor_gpu_temp'], 0) ?>°C</span>
                                            <?php endif; ?>
                                        </span>
                                        <?php else: ?>
                                        <small class="text-muted">—</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($computer['operating_system_name']):
                                            $osName = $computer['operating_system_name'];
                                            $osVer = $computer['os_version_name'] ?? '';
                                            $osShort = preg_replace('/^Windows\s+11.*$/i', 'W11', $osName);
                                            $osShort = preg_replace('/^Windows\s+10.*$/i', 'W10', $osShort);
                                            $osShort = preg_replace('/^Microsoft\s+/i', '', $osShort);
                                            echo htmlspecialchars(trim($osShort . ($osVer ? ' ' . $osVer : '')));
                                        else: ?>
                                            <span class="text-muted">Non défini</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($computer['person_prenom'] || $computer['person_nom']): ?>
                                            <strong><?= htmlspecialchars(trim($computer['person_prenom'] . ' ' . $computer['person_nom'])) ?></strong>
                                            <?php if ($computer['person_email']): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($computer['person_email']) ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Non attribué</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-inline" role="group">
                                            <?php if (!empty($computer['rustdesk_id'])): ?>
                                            <a href="<?= ($rustdeskProtocol ?? 'rustdesk') ?>://<?= htmlspecialchars($computer['rustdesk_id']) ?>" 
                                               class="btn btn-sm rustdesk-btn rustdesk-loading"
                                               data-rustdesk-id="<?= htmlspecialchars($computer['rustdesk_id']) ?>"
                                               title="RustDesk (<?= htmlspecialchars($computer['rustdesk_id']) ?>)">
                                                <i class="fas fa-desktop"></i> RD
                                            </a>
                                            <?php endif; ?>
                                            <a href="?page=hardware&section=computers&action=view&id=<?= $computer['id'] ?>" 
                                               class="btn btn-info btn-sm" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="?page=hardware&section=computers&action=edit&id=<?= $computer['id'] ?>" 
                                               class="btn btn-warning btn-sm" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?page=hardware&section=computers&action=delete&id=<?= $computer['id'] ?>" 
                                               class="btn btn-danger btn-sm" title="Supprimer"
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet ordinateur ?')">
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
                        <i class="fas fa-desktop fa-3x text-muted"></i>
                    </div>
                    <h4>Aucun ordinateur</h4>
                    <p class="text-muted">Aucun ordinateur n'a été enregistré pour le contexte sélectionné.</p>
                    <a href="?page=hardware&section=computers&action=create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Ajouter un ordinateur
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- jQuery and DataTables Dependencies -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>

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
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
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

.stat-icon.win10 {
    background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%);
}

.stat-icon.win11 {
    background: linear-gradient(135deg, #0067b8 0%, #00bcf2 100%);
}

.stat-icon.rustdesk {
    background: linear-gradient(135deg, #f74c00 0%, #ff6b35 100%);
}

.col-name {
    min-width: 180px;
    white-space: nowrap;
}

.col-name .name-link {
    display: inline-block;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
}

.stat-content {
    flex: 1;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    line-height: 1;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

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

/* Custom DataTables styling */
.dataTables_wrapper {
    padding: 0;
}

.dataTables_length {
    margin-bottom: 1rem;
}

.dataTables_length select {
    padding: 0.375rem 0.75rem;
    border: 1px solid #d1d3e2;
    border-radius: 0.35rem;
    background-color: #fff;
    color: #5a5c69;
    font-size: 0.875rem;
}

.dataTables_filter {
    display: none; /* On utilise notre propre champ de recherche */
}

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

/* Force inline display for all pagination elements */
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

/* Responsive adjustments */
@media (max-width: 768px) {
    .dataTables_wrapper .row:last-child {
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
    }
    
    .dataTables_info {
        text-align: center;
        order: 2;
    }
    
    .dataTables_paginate {
        justify-content: center;
        order: 1;
    }
    
    .dataTables_paginate .paginate_button {
        padding: 0.375rem 0.5rem;
        font-size: 0.8rem;
    }
}

/* Force horizontal layout for pagination buttons */
.dataTables_paginate span,
.dataTables_paginate a {
    display: inline-block !important;
    float: none !important;
    vertical-align: middle !important;
}

.dataTables_paginate .paging_simple_numbers {
    white-space: nowrap !important;
}

.dataTables_paginate .paging_simple_numbers span,
.dataTables_paginate .paging_simple_numbers a {
    margin: 0 2px !important;
}

/* Force horizontal layout for pagination buttons */
.dataTables_paginate span,
.dataTables_paginate a {
    display: inline-block !important;
    float: none !important;
    vertical-align: middle !important;
}

.dataTables_paginate .paging_simple_numbers {
    white-space: nowrap !important;
}

.dataTables_paginate .paging_simple_numbers span,
.dataTables_paginate .paging_simple_numbers a {
    margin: 0 2px !important;
}

/* Boutons sur une seule ligne */
.btn-group-inline {
    display: inline-flex;
    flex-wrap: nowrap;
    white-space: nowrap;
}
.btn-group-inline .btn {
    margin-right: 2px;
}
/* RustDesk icône: vert = en ligne, rouge = hors ligne */
.rustdesk-btn.rustdesk-loading {
    color: #6b7280;
    border-color: #9ca3af;
}
.rustdesk-btn.rustdesk-online {
    color: #16a34a;
    border-color: #16a34a;
}
.rustdesk-btn.rustdesk-online:hover {
    color: #15803d;
    border-color: #15803d;
}
.rustdesk-btn.rustdesk-offline {
    color: #dc2626;
    border-color: #dc2626;
}
.rustdesk-btn.rustdesk-offline:hover {
    color: #b91c1c;
    border-color: #b91c1c;
}
</style>

<script>
// Attendre que le DOM soit prêt ET que jQuery soit chargé
window.addEventListener('load', function() {
    // Vérifier si jQuery est disponible
    if (typeof jQuery === 'undefined') {
        console.error('jQuery n\'est pas chargé');
        return;
    }
    
    jQuery(document).ready(function($) {
        console.log('Initialisation du filtrage des ordinateurs');
        
        // Initialiser DataTables
        var table = $('#dataTable').DataTable({
            "language": {
                "sProcessing": "Traitement en cours...",
                "sSearch": "Rechercher&nbsp;:",
                "sLengthMenu": "Afficher _MENU_ éléments",
                "sInfo": "Affichage de _START_ à _END_ sur _TOTAL_ ordinateurs",
                "sInfoEmpty": "Affichage de 0 à 0 sur 0 ordinateur",
                "sInfoFiltered": "(filtré de _MAX_ ordinateurs au total)",
                "sInfoPostFix": "",
                "sLoadingRecords": "Chargement en cours...",
                "sZeroRecords": "Aucun ordinateur trouvé",
                "sEmptyTable": "Aucun ordinateur disponible",
                "oPaginate": {
                    "sFirst": "Premier",
                    "sPrevious": "Précédent",
                    "sNext": "Suivant",
                    "sLast": "Dernier"
                },
                "oAria": {
                    "sSortAscending": ": activer pour trier la colonne par ordre croissant",
                    "sSortDescending": ": activer pour trier la colonne par ordre décroissant"
                }
            },
            "pageLength": 25,
            "order": [[ 0, "asc" ]],
            "dom": '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6">>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
        });
        
        // Recherche
        $('#searchInput').on('keyup', function() {
            table.search(this.value).draw();
        });
        
        // Filtrage par cartes
        $('.stat-card.clickable').on('click', function() {
            console.log('Carte cliquée:', $(this).data('filter'));
            
            var filter = $(this).data('filter');
            var filterText = $(this).find('.stat-label').text();
            
            // Retirer la classe active de toutes les cartes
            $('.stat-card').removeClass('active');
            
            // Ajouter la classe active à la carte cliquée
            $(this).addClass('active');
            
            // Appliquer le filtre
            if (filter === 'all') {
                table.search('').column(4).search('').draw();
                $('#filterIndicator').removeClass('active');
            } else {
                var searchTerm = '';
                
                switch(filter) {
                    case 'windows10':
                        searchTerm = 'W10';
                        break;
                    case 'windows11':
                        searchTerm = 'W11';
                        break;
                    case 'rustdesk':
                        // Filtre par colonne Rust (colonne 4) - afficher uniquement les PC avec RustDesk
                        table.search('').column(4).search('RD', true, false).draw();
                        $('#filterIndicator').addClass('active');
                        $('#filterText').text('Filtré par: ' + filterText);
                        return;
                }
                
                if (searchTerm) {
                    table.search(searchTerm).draw();
                    $('#filterIndicator').addClass('active');
                    $('#filterText').text('Filtré par: ' + filterText);
                }
            }
        });
        
        // Effacer le filtre
        $('#clearFilter').on('click', function() {
            $('.stat-card').removeClass('active');
            $('.stat-card[data-filter="all"]').addClass('active');
            table.search('').column(4).search('').draw();
            $('#filterIndicator').removeClass('active');
        });
        
        // Marquer "Tous les PCs" comme actif par défaut
        $('.stat-card[data-filter="all"]').addClass('active');

        // Charger le statut RustDesk Pro pour tous les PCs
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