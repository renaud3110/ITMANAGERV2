<?php if (!$tenantDsdName): ?>
<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-file-invoice"></i> 
        Historique DSD Factures
    </h1>
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <strong>Information :</strong> Veuillez sélectionner un tenant pour afficher l'historique des factures DSD.
</div>

<?php else: ?>
<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-file-invoice"></i> 
        Historique DSD Factures
        <span class="tenant-name">- <?= htmlspecialchars($currentTenant !== 'all' ? $tenants[$currentTenant]['name'] ?? 'Tenant' : 'Tous les tenants') ?></span>
    </h1>
</div>

<!-- Statistiques globales -->
<div class="stats-overview">
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $globalStats['total_factures'] ?? 0 ?></div>
            <div class="stat-label">Total Factures</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-key"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $globalStats['total_licenses'] ?? 0 ?></div>
            <div class="stat-label">Types de Licences</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-calculator"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($globalStats['total_licenses_quantity'] ?? 0) ?></div>
            <div class="stat-label">Licences Total</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-euro-sign"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($globalStats['total_amount'] ?? 0, 2) ?> €</div>
            <div class="stat-label">Montant Total</div>
        </div>
    </div>
</div>

<!-- Évolution des licences -->
<div class="content-section">
    <div class="section-header">
        <h2><i class="fas fa-chart-line"></i> Évolution des Licences</h2>
        <p>Aperçu de l'évolution du nombre de licences par mois</p>
    </div>
    
    <?php if (empty($licenceMatrix['licenses'])): ?>
    <div class="empty-state">
        <i class="fas fa-chart-line"></i>
        <h3>Aucune donnée d'évolution</h3>
        <p>Aucune donnée d'évolution des licences n'a été trouvée pour ce tenant.</p>
    </div>
    <?php else: ?>
    <div class="table-container">
        <table class="table table-modern" id="licenceMatrixTable">
            <thead>
                <tr>
                    <th><i class="fas fa-key"></i> Licence</th>
                    <?php foreach ($licenceMatrix['months'] as $month): ?>
                    <th class="text-center">
                        <i class="fas fa-calendar"></i><br>
                        <?= date('M Y', strtotime($month . '-01')) ?>
                    </th>
                    <?php endforeach; ?>
                    <th class="text-center">
                        <i class="fas fa-chart-line"></i><br>
                        Graphique
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($licenceMatrix['licenses'] as $license): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($license) ?></strong>
                    </td>
                    <?php foreach ($licenceMatrix['months'] as $month): ?>
                    <td class="text-center">
                        <?php 
                        $quantity = $licenceMatrix['matrix'][$license][$month] ?? 0;
                        if ($quantity > 0): 
                        ?>
                        <span class="quantity-badge">
                            <?= number_format($quantity) ?>
                        </span>
                        <?php else: ?>
                        <span class="quantity-empty">-</span>
                        <?php endif; ?>
                    </td>
                    <?php endforeach; ?>
                    <td class="text-center">
                        <button class="btn btn-sm btn-primary" 
                                onclick="showLicenseChart('<?= htmlspecialchars($license) ?>')"
                                title="Voir l'évolution">
                            <i class="fas fa-chart-line"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Liste des factures -->
<div class="content-section">
    <div class="section-header">
        <h2><i class="fas fa-list"></i> Liste des Factures</h2>
        <p>Historique détaillé des factures reçues</p>
    </div>
    
    <?php if (empty($factures)): ?>
    <div class="empty-state">
        <i class="fas fa-file-invoice"></i>
        <h3>Aucune facture trouvée</h3>
        <p>Aucune facture n'a été trouvée pour ce tenant.</p>
    </div>
    <?php else: ?>
    <div class="table-container">
        <table class="table table-modern" id="facturesTable">
            <thead>
                <tr>
                    <th><i class="fas fa-envelope"></i> Email ID</th>
                    <th><i class="fas fa-file-alt"></i> Sujet</th>
                    <th><i class="fas fa-calendar"></i> Date Réception</th>
                    <th><i class="fas fa-euro-sign"></i> Montant</th>
                    <th><i class="fas fa-key"></i> Licences</th>
                    <th><i class="fas fa-cog"></i> Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($factures as $facture): ?>
                <tr>
                    <td>
                        <span class="email-id"><?= htmlspecialchars($facture['email_id']) ?></span>
                    </td>
                    <td>
                        <div class="subject-text">
                            <?= htmlspecialchars($facture['subject']) ?>
                        </div>
                    </td>
                    <td>
                        <span class="date-badge">
                            <?= date('d/m/Y H:i', strtotime($facture['received_date'])) ?>
                        </span>
                    </td>
                    <td>
                        <span class="amount-badge">
                            <?= number_format($facture['montant'], 2) ?> €
                        </span>
                    </td>
                    <td>
                        <?php 
                        $licences = $this->dsdFactures->getLicencesByFacture($facture['id']);
                        $licenseCount = count($licences);
                        ?>
                        <span class="license-count-badge">
                            <?= $licenseCount ?> licence(s)
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-info" 
                                onclick="showFactureDetails(<?= $facture['id'] ?>)"
                                title="Voir les détails">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Modal pour les détails de facture -->
<div class="modal fade" id="factureDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-invoice"></i> 
                    Détails de la Facture
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="factureDetailsContent">
                <!-- Le contenu sera chargé dynamiquement -->
            </div>
        </div>
    </div>
</div>

<style>
.quantity-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-weight: bold;
    font-size: 0.9em;
}

.quantity-empty {
    color: #ccc;
    font-style: italic;
}

#licenseChartModal .modal-dialog {
    max-width: 800px;
}

.chart-container {
    position: relative;
    height: 400px;
    margin: 20px 0;
}
</style>

<!-- Modal pour le graphique de licence -->
<div class="modal fade" id="licenseChartModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-chart-line"></i> 
                    Évolution de la Licence: <span id="licenseChartTitle"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="chart-container">
                    <canvas id="licenseChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showFactureDetails(factureId) {
    // Charger les détails de la facture via AJAX
    fetch(`?page=tools&action=facture-details&id=${factureId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('factureDetailsContent').innerHTML = html;
            $('#factureDetailsModal').modal('show');
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors du chargement des détails');
        });
}

function showLicenseChart(licenseName) {
    // Mettre à jour le titre du modal
    document.getElementById('licenseChartTitle').textContent = licenseName;
    
    // Charger les données de la licence via AJAX
    fetch(`?page=tools&action=licence-detail&license=${encodeURIComponent(licenseName)}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            // Créer le graphique avec Chart.js
            const ctx = document.getElementById('licenseChart').getContext('2d');
            
            // Détruire le graphique existant s'il y en a un
            if (window.licenseChart) {
                window.licenseChart.destroy();
            }
            
            window.licenseChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Quantité de licences',
                        data: data.values,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Nombre de licences'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Mois'
                            }
                        }
                    }
                }
            });
            
            $('#licenseChartModal').modal('show');
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors du chargement du graphique');
        });
}

// Initialisation DataTables
$(document).ready(function() {
    $('#licenceMatrixTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/French.json"
        },
        "order": [[1, "desc"]],
        "pageLength": 25
    });
    
    $('#facturesTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/French.json"
        },
        "order": [[2, "desc"]],
        "pageLength": 25
    });
    
    // Forcer le rechargement de la page quand le tenant change
    $('#tenant_id').on('change', function() {
        // Attendre un peu pour que la session soit mise à jour
        setTimeout(function() {
            window.location.reload();
        }, 500);
    });
});
</script>

<?php endif; ?> 