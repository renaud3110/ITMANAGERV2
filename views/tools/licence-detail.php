<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-chart-line"></i> 
        Évolution de la Licence
        <span class="license-name">- <?= htmlspecialchars($licenseName) ?></span>
        <span class="tenant-name">(<?= htmlspecialchars($tenantName) ?>)</span>
    </h1>
    <div class="page-actions">
        <a href="?page=tools&action=dsdFactures" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
    </div>
</div>

<?php if (empty($evolutionDetail)): ?>
<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    <strong>Aucune donnée :</strong> Aucune donnée d'évolution n'a été trouvée pour cette licence.
</div>
<?php else: ?>
<!-- Graphique d'évolution -->
<div class="content-section">
    <div class="section-header">
        <h2><i class="fas fa-chart-area"></i> Graphique d'Évolution</h2>
        <p>Évolution du nombre de licences au fil du temps</p>
    </div>
    
    <div class="chart-container">
        <canvas id="licenceEvolutionChart" width="400" height="200"></canvas>
    </div>
</div>

<!-- Tableau détaillé -->
<div class="content-section">
    <div class="section-header">
        <h2><i class="fas fa-table"></i> Détails par Facture</h2>
        <p>Historique détaillé des factures pour cette licence</p>
    </div>
    
    <div class="table-container">
        <table class="table table-modern" id="evolutionDetailTable">
            <thead>
                <tr>
                    <th><i class="fas fa-calendar"></i> Mois</th>
                    <th><i class="fas fa-calendar-alt"></i> Date Facture</th>
                    <th><i class="fas fa-calculator"></i> Quantité</th>
                    <th><i class="fas fa-euro-sign"></i> Montant</th>
                    <th><i class="fas fa-file-alt"></i> Sujet</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($evolutionDetail as $detail): ?>
                <tr>
                    <td>
                        <span class="month-badge">
                            <?= date('M Y', strtotime($detail['month'] . '-01')) ?>
                        </span>
                    </td>
                    <td>
                        <span class="date-badge">
                            <?= date('d/m/Y', strtotime($detail['received_date'])) ?>
                        </span>
                    </td>
                    <td>
                        <span class="quantity-badge">
                            <?= number_format($detail['quantity']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="amount-badge">
                            <?= number_format($detail['montant'], 2) ?> €
                        </span>
                    </td>
                    <td>
                        <div class="subject-text">
                            <?= htmlspecialchars($detail['subject']) ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Statistiques de la licence -->
<div class="stats-overview">
    <?php 
    $totalQuantity = array_sum(array_column($evolutionDetail, 'quantity'));
    $totalAmount = array_sum(array_column($evolutionDetail, 'montant'));
    $factureCount = count($evolutionDetail);
    $avgQuantity = $factureCount > 0 ? $totalQuantity / $factureCount : 0;
    ?>
    
    <div class="stat-card">
        <div class="stat-icon primary">
            <i class="fas fa-calculator"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($totalQuantity) ?></div>
            <div class="stat-label">Total Licences</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon success">
            <i class="fas fa-file-invoice"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= $factureCount ?></div>
            <div class="stat-label">Factures</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon info">
            <i class="fas fa-chart-bar"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($avgQuantity, 1) ?></div>
            <div class="stat-label">Moyenne/Facture</div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon warning">
            <i class="fas fa-euro-sign"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($totalAmount, 2) ?> €</div>
            <div class="stat-label">Montant Total</div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Préparation des données pour le graphique
const chartData = {
    labels: [<?= implode(',', array_map(function($detail) { 
        return '"' . date('M Y', strtotime($detail['month'] . '-01')) . '"'; 
    }, $evolutionDetail)) ?>],
    datasets: [{
        label: 'Quantité de Licences',
        data: [<?= implode(',', array_column($evolutionDetail, 'quantity')) ?>],
        borderColor: 'rgb(75, 192, 192)',
        backgroundColor: 'rgba(75, 192, 192, 0.2)',
        tension: 0.1,
        fill: true
    }]
};

// Configuration du graphique
const config = {
    type: 'line',
    data: chartData,
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Évolution de <?= htmlspecialchars($licenseName) ?>'
            },
            legend: {
                display: true
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Nombre de Licences'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Période'
                }
            }
        }
    }
};

// Création du graphique
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('licenceEvolutionChart').getContext('2d');
    new Chart(ctx, config);
});

// Initialisation DataTables
$(document).ready(function() {
    $('#evolutionDetailTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/French.json"
        },
        "order": [[1, "desc"]],
        "pageLength": 25
    });
});
</script>

<?php endif; ?> 