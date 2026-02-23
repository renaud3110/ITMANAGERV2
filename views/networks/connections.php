<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-project-diagram"></i>
        Connexions Réseau
    </h1>
    <a href="?page=networks" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Retour aux équipements
    </a>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <i class="fas fa-link stat-icon"></i>
        <div class="stat-number"><?= count($connections) ?></div>
        <div class="stat-label">Connexions Total</div>
    </div>
    <div class="stat-card">
        <i class="fas fa-check-circle stat-icon text-success"></i>
        <div class="stat-number"><?= count(array_filter($connections, fn($c) => $c['port_status'] === 'active')) ?></div>
        <div class="stat-label">Connexions Actives</div>
    </div>
    <div class="stat-card">
        <i class="fas fa-network-wired stat-icon"></i>
        <div class="stat-number"><?= count(array_unique(array_column($connections, 'equipment_name'))) ?></div>
        <div class="stat-label">Équipements Connectés</div>
    </div>
    <div class="stat-card">
        <i class="fas fa-layer-group stat-icon"></i>
        <div class="stat-number"><?= count(array_unique(array_filter(array_column($connections, 'vlan_id')))) ?></div>
        <div class="stat-label">VLANs Utilisés</div>
    </div>
</div>

<?php if (empty($connections)): ?>
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-project-diagram" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
            <h3 style="color: #6b7280; margin-bottom: 0.5rem;">Aucune connexion</h3>
            <p style="color: #9ca3af; margin-bottom: 2rem;">Il n'y a actuellement aucune connexion entre les équipements réseau.</p>
            <a href="?page=networks" class="btn btn-primary">
                <i class="fas fa-ethernet"></i>
                Gérer les équipements
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Tableau des connexions</h2>
            <div class="card-actions">
                <button class="btn btn-outline-secondary" onclick="toggleView('table')">
                    <i class="fas fa-table"></i>
                    Vue tableau
                </button>
                <button class="btn btn-outline-secondary" onclick="toggleView('visual')">
                    <i class="fas fa-project-diagram"></i>
                    Vue graphique
                </button>
            </div>
        </div>
        
        <!-- Vue tableau -->
        <div id="tableView" class="table-responsive">
            <table class="table" id="connectionsTable">
                <thead>
                    <tr>
                        <th>Équipement Source</th>
                        <th>Port Source</th>
                        <th>Équipement Destination</th>
                        <th>Port Destination</th>
                        <th>VLAN</th>
                        <th>Statut</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($connections as $connection): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($connection['equipment_name']) ?></strong>
                            </td>
                            <td>
                                <code><?= htmlspecialchars($connection['port_name']) ?></code>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($connection['connected_to_equipment']) ?></strong>
                            </td>
                            <td>
                                <code><?= htmlspecialchars($connection['connected_to_port']) ?></code>
                            </td>
                            <td>
                                <?php if (!empty($connection['vlan_id'])): ?>
                                    <span class="vlan-tag"><?= htmlspecialchars($connection['vlan_id']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?= $connection['port_status'] ?>">
                                    <?= ucfirst($connection['port_status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($connection['description'])): ?>
                                    <?= htmlspecialchars($connection['description']) ?>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="?page=networks&action=ports&equipment_id=<?= $connection['port_id'] ?>" 
                                       class="btn btn-sm btn-outline-primary" title="Gérer le port">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?page=networks&action=disconnectPort&port_id=<?= $connection['port_id'] ?>&equipment_id=<?= $connection['port_id'] ?>" 
                                       class="btn btn-sm btn-outline-warning" title="Déconnecter"
                                       onclick="return confirm('Êtes-vous sûr de vouloir déconnecter ces ports ?')">
                                        <i class="fas fa-unlink"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Vue graphique -->
        <div id="visualView" style="display: none;">
            <div class="network-diagram">
                <div class="diagram-controls">
                    <button class="btn btn-sm btn-outline-secondary" onclick="resetZoom()">
                        <i class="fas fa-search-minus"></i>
                        Reset zoom
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" onclick="centerDiagram()">
                        <i class="fas fa-crosshairs"></i>
                        Centrer
                    </button>
                </div>
                <div id="networkCanvas" class="network-canvas">
                    <!-- Le diagramme sera généré par JavaScript -->
                </div>
            </div>
        </div>
    </div>
    
    <!-- Panneau latéral d'informations -->
    <div id="infoPanel" class="info-panel" style="display: none;">
        <div class="panel-header">
            <h4>Informations de connexion</h4>
            <button class="panel-close" onclick="closeInfoPanel()">&times;</button>
        </div>
        <div class="panel-content">
            <div id="connectionDetails">
                <!-- Les détails seront remplis par JavaScript -->
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.stat-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: #6366f1;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.stat-label {
    color: #6b7280;
    font-size: 0.875rem;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.card-actions {
    display: flex;
    gap: 0.5rem;
}

.empty-state {
    text-align: center;
    padding: 3rem 2rem;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
}

.status-active {
    background-color: #d1fae5;
    color: #065f46;
}

.status-inactive {
    background-color: #f3f4f6;
    color: #374151;
}

.vlan-tag {
    background: #dbeafe;
    color: #1e40af;
    padding: 0.125rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.btn-group {
    display: flex;
    gap: 0.25rem;
}

.network-diagram {
    position: relative;
    height: 600px;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    overflow: hidden;
}

.diagram-controls {
    position: absolute;
    top: 1rem;
    right: 1rem;
    z-index: 10;
    display: flex;
    gap: 0.5rem;
}

.network-canvas {
    width: 100%;
    height: 100%;
    position: relative;
    overflow: auto;
}

.equipment-node {
    position: absolute;
    background: white;
    border: 2px solid #6366f1;
    border-radius: 0.5rem;
    padding: 1rem;
    min-width: 120px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.equipment-node:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.equipment-node.router {
    border-color: #dc2626;
    background: #fef2f2;
}

.equipment-node.switch {
    border-color: #2563eb;
    background: #eff6ff;
}

.equipment-node.wifi {
    border-color: #059669;
    background: #ecfdf5;
}

.connection-line {
    position: absolute;
    background: #6b7280;
    height: 2px;
    transform-origin: left center;
    pointer-events: none;
}

.connection-line.active {
    background: #10b981;
    height: 3px;
}

.info-panel {
    position: fixed;
    top: 0;
    right: 0;
    width: 400px;
    height: 100vh;
    background: white;
    box-shadow: -4px 0 8px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    transform: translateX(100%);
    transition: transform 0.3s ease;
}

.info-panel.open {
    transform: translateX(0);
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.panel-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #6b7280;
}

.panel-content {
    padding: 1.5rem;
}

.table th, .table td {
    padding: 0.75rem;
    vertical-align: middle;
    border-top: 1px solid #e5e7eb;
}

.table thead th {
    font-weight: 600;
    background-color: #f9fafb;
    border-bottom: 2px solid #e5e7eb;
}

.text-success { color: #059669 !important; }
.text-muted { color: #6b7280 !important; }

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.5rem 1rem;
    border: 1px solid transparent;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.15s ease;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}

.btn-secondary { background-color: #6b7280; border-color: #6b7280; color: white; }
.btn-primary { background-color: #6366f1; border-color: #6366f1; color: white; }
.btn-outline-secondary { background-color: transparent; border-color: #6b7280; color: #6b7280; }
.btn-outline-primary { background-color: transparent; border-color: #6366f1; color: #6366f1; }
.btn-outline-warning { background-color: transparent; border-color: #f59e0b; color: #f59e0b; }

.btn:hover { opacity: 0.9; }
.btn-outline-secondary:hover { background-color: #6b7280; color: white; }
.btn-outline-primary:hover { background-color: #6366f1; color: white; }
.btn-outline-warning:hover { background-color: #f59e0b; color: white; }
</style>

<script>
const connectionsData = <?= json_encode($connections) ?>;
let currentView = 'table';

function toggleView(view) {
    currentView = view;
    const tableView = document.getElementById('tableView');
    const visualView = document.getElementById('visualView');
    
    if (view === 'table') {
        tableView.style.display = 'block';
        visualView.style.display = 'none';
    } else {
        tableView.style.display = 'none';
        visualView.style.display = 'block';
        setTimeout(generateNetworkDiagram, 100);
    }
}

function generateNetworkDiagram() {
    const canvas = document.getElementById('networkCanvas');
    canvas.innerHTML = '';
    
    // Extraire les équipements uniques
    const equipments = new Map();
    connectionsData.forEach(conn => {
        if (!equipments.has(conn.equipment_name)) {
            equipments.set(conn.equipment_name, {
                name: conn.equipment_name,
                connections: []
            });
        }
        if (!equipments.has(conn.connected_to_equipment)) {
            equipments.set(conn.connected_to_equipment, {
                name: conn.connected_to_equipment,
                connections: []
            });
        }
        
        equipments.get(conn.equipment_name).connections.push(conn);
    });
    
    // Positionner les équipements en cercle
    const centerX = 400;
    const centerY = 250;
    const radius = 200;
    const equipmentArray = Array.from(equipments.values());
    
    equipmentArray.forEach((equipment, index) => {
        const angle = (index / equipmentArray.length) * 2 * Math.PI;
        const x = centerX + radius * Math.cos(angle) - 60;
        const y = centerY + radius * Math.sin(angle) - 40;
        
        const node = document.createElement('div');
        node.className = 'equipment-node';
        node.style.left = x + 'px';
        node.style.top = y + 'px';
        node.innerHTML = `
            <div style="font-weight: bold; margin-bottom: 0.5rem;">${equipment.name}</div>
            <div style="font-size: 0.75rem; color: #6b7280;">${equipment.connections.length} connexions</div>
        `;
        
        node.addEventListener('click', () => showEquipmentDetails(equipment));
        canvas.appendChild(node);
        
        equipment.x = x + 60;
        equipment.y = y + 40;
    });
    
    // Dessiner les connexions
    connectionsData.forEach(conn => {
        const source = equipmentArray.find(eq => eq.name === conn.equipment_name);
        const target = equipmentArray.find(eq => eq.name === conn.connected_to_equipment);
        
        if (source && target) {
            drawConnection(canvas, source, target, conn);
        }
    });
}

function drawConnection(canvas, source, target, connection) {
    const line = document.createElement('div');
    line.className = `connection-line ${connection.port_status}`;
    
    const dx = target.x - source.x;
    const dy = target.y - source.y;
    const length = Math.sqrt(dx * dx + dy * dy);
    const angle = Math.atan2(dy, dx) * 180 / Math.PI;
    
    line.style.left = source.x + 'px';
    line.style.top = source.y + 'px';
    line.style.width = length + 'px';
    line.style.transform = `rotate(${angle}deg)`;
    line.style.zIndex = '1';
    
    line.addEventListener('click', () => showConnectionDetails(connection));
    canvas.appendChild(line);
}

function showEquipmentDetails(equipment) {
    const panel = document.getElementById('infoPanel');
    const details = document.getElementById('connectionDetails');
    
    details.innerHTML = `
        <h5>${equipment.name}</h5>
        <div class="detail-list">
            <div class="detail-item">
                <span class="detail-label">Connexions:</span>
                <span class="detail-value">${equipment.connections.length}</span>
            </div>
            ${equipment.connections.map(conn => `
                <div class="connection-item">
                    <strong>${conn.port_name}</strong> → ${conn.connected_to_equipment} (${conn.connected_to_port})
                    ${conn.vlan_id ? `<br><small>VLAN: ${conn.vlan_id}</small>` : ''}
                </div>
            `).join('')}
        </div>
    `;
    
    panel.classList.add('open');
    panel.style.display = 'block';
}

function showConnectionDetails(connection) {
    const panel = document.getElementById('infoPanel');
    const details = document.getElementById('connectionDetails');
    
    details.innerHTML = `
        <h5>Détails de la connexion</h5>
        <div class="detail-list">
            <div class="detail-item">
                <span class="detail-label">Source:</span>
                <span class="detail-value">${connection.equipment_name} (${connection.port_name})</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Destination:</span>
                <span class="detail-value">${connection.connected_to_equipment} (${connection.connected_to_port})</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Statut:</span>
                <span class="detail-value status-${connection.port_status}">${connection.port_status}</span>
            </div>
            ${connection.vlan_id ? `
                <div class="detail-item">
                    <span class="detail-label">VLAN:</span>
                    <span class="detail-value">${connection.vlan_id}</span>
                </div>
            ` : ''}
            ${connection.description ? `
                <div class="detail-item">
                    <span class="detail-label">Description:</span>
                    <span class="detail-value">${connection.description}</span>
                </div>
            ` : ''}
        </div>
    `;
    
    panel.classList.add('open');
    panel.style.display = 'block';
}

function closeInfoPanel() {
    const panel = document.getElementById('infoPanel');
    panel.classList.remove('open');
    setTimeout(() => panel.style.display = 'none', 300);
}

function resetZoom() {
    // Fonction pour reset le zoom (à implémenter selon les besoins)
}

function centerDiagram() {
    // Fonction pour centrer le diagramme (à implémenter selon les besoins)
}

// Initialiser DataTable
$(document).ready(function() {
    if ($.fn.DataTable && document.getElementById('connectionsTable')) {
        $('#connectionsTable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
            },
            order: [[0, 'asc']],
            columnDefs: [
                { orderable: false, targets: -1 }
            ]
        });
    }
});
</script> 