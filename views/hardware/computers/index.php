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
        
        <div class="stat-card clickable" data-filter="teamviewer" title="Afficher uniquement les PC avec TeamViewer">
            <div class="stat-icon teamviewer">
                <i class="fas fa-desktop"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">
                    <?= count(array_filter($computers, function($c) { return !empty($c['teamviewer_id']); })) ?>
                </div>
                <div class="stat-label">Avec TeamViewer</div>
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
                                <th>Nom</th>
                                <th>Modèle</th>
                                <th>Site</th>
                                <th>Système d'exploitation</th>
                                <th>Personne attribuée</th>
                                <th>TeamViewer</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($computers as $computer): ?>
                                <tr>
                                    <td>
                                        <a href="?page=hardware&section=computers&action=view&id=<?= $computer['id'] ?>" class="text-decoration-none">
                                            <strong><?= htmlspecialchars($computer['name'] ?? 'Sans nom') ?></strong>
                                        </a>
                                        <?php if ($computer['serial_number']): ?>
                                            <br><small class="text-muted">S/N: <?= htmlspecialchars($computer['serial_number']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($computer['model_brand'] ?? 'N/A') ?></strong>
                                        <br><small class="text-muted"><?= htmlspecialchars($computer['model_name'] ?? 'N/A') ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($computer['site_name'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php if ($computer['operating_system_name']): ?>
                                            <strong><?= htmlspecialchars($computer['operating_system_name']) ?></strong>
                                            <?php if ($computer['os_version_name']): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($computer['os_version_name']) ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
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
                                        <?php if ($computer['teamviewer_id']): ?>
                                            <a href="https://start.teamviewer.com/<?= htmlspecialchars($computer['teamviewer_id']) ?>" 
                                               target="_blank" 
                                               class="btn btn-primary btn-sm" title="Se connecter via TeamViewer">
                                                <i class="fas fa-desktop"></i>
                                                <?= htmlspecialchars($computer['teamviewer_id']) ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">Non défini</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
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

.stat-icon.teamviewer {
    background: linear-gradient(135deg, #004788 0%, #0066CC 100%);
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
                table.search('').column(5).search('').draw();
                $('#filterIndicator').removeClass('active');
            } else {
                var searchTerm = '';
                
                switch(filter) {
                    case 'windows10':
                        searchTerm = 'Windows 10';
                        break;
                    case 'windows11':
                        searchTerm = 'Windows 11';
                        break;
                    case 'teamviewer':
                        // Filtre par colonne TeamViewer (colonne 5)
                        table.search('').column(5).search('^(?!.*Non défini).*$', true, false).draw();
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
            table.search('').column(5).search('').draw();
            $('#filterIndicator').removeClass('active');
        });
        
        // Marquer "Tous les PCs" comme actif par défaut
        $('.stat-card[data-filter="all"]').addClass('active');
    });
});
</script> 