<div class="page-header">
    <h1 class="page-title">
        <i class="fas fa-ethernet"></i>
        Ports de <?= htmlspecialchars($equipment['name']) ?>
    </h1>
    <div class="header-actions">
        <a href="?page=networks" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Retour aux équipements
        </a>
        <a href="?page=networks&action=edit&id=<?= $equipment['id'] ?>" class="btn btn-outline-secondary">
            <i class="fas fa-edit"></i>
            Modifier l'équipement
        </a>
    </div>
</div>

<div class="equipment-info-card">
    <div class="equipment-details">
        <h3><?= htmlspecialchars($equipment['name']) ?></h3>
        <div class="equipment-meta">
            <span class="badge badge-<?= $equipment['type'] === 'router' ? 'primary' : ($equipment['type'] === 'switch' ? 'info' : 'success') ?>">
                <i class="fas fa-<?= $equipment['type'] === 'router' ? 'router' : ($equipment['type'] === 'switch' ? 'network-wired' : 'wifi') ?>"></i>
                <?= ucfirst($equipment['type']) ?>
            </span>
            <span class="meta-item">
                <i class="fas fa-map-marker-alt"></i>
                <?= htmlspecialchars($equipment['site_name'] ?? 'Site non défini') ?>
            </span>
            <?php if (!empty($equipment['ip_address'])): ?>
                <span class="meta-item">
                    <i class="fas fa-network-wired"></i>
                    <?= htmlspecialchars($equipment['ip_address']) ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <div class="ports-summary">
        <div class="summary-item">
            <span class="summary-number"><?= count($ports) ?></span>
            <span class="summary-label">Ports total</span>
        </div>
        <div class="summary-item">
            <span class="summary-number text-success"><?= count(array_filter($ports, fn($p) => $p['port_status'] === 'active')) ?></span>
            <span class="summary-label">Actifs</span>
        </div>
        <div class="summary-item">
            <span class="summary-number text-muted"><?= count(array_filter($ports, fn($p) => $p['port_status'] === 'inactive')) ?></span>
            <span class="summary-label">Libres</span>
        </div>
        <div class="summary-item">
            <span class="summary-number text-warning"><?= count(array_filter($ports, fn($p) => !empty($p['connected_to_equipment_id']))) ?></span>
            <span class="summary-label">Connectés</span>
        </div>
    </div>
</div>

<?php if (empty($ports)): ?>
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-ethernet" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
            <h3 style="color: #6b7280; margin-bottom: 0.5rem;">Aucun port configuré</h3>
            <p style="color: #9ca3af; margin-bottom: 2rem;">Cet équipement n'a pas de ports configurés.</p>
            <a href="?page=networks&action=edit&id=<?= $equipment['id'] ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Configurer les ports
            </a>
        </div>
    </div>
<?php else: ?>
    <div class="ports-grid">
        <?php foreach ($ports as $port): ?>
            <div class="port-card" data-port-id="<?= $port['id'] ?>">
                <div class="port-header">
                    <div class="port-name">
                        <strong><?= htmlspecialchars($port['port_name']) ?></strong>
                        <span class="port-number">#<?= $port['port_number'] ?></span>
                    </div>
                    <div class="port-status">
                        <span class="status-indicator status-<?= $port['port_status'] ?>" title="<?= ucfirst($port['port_status']) ?>"></span>
                    </div>
                </div>
                
                <div class="port-details">
                    <div class="detail-row">
                        <span class="detail-label">Type:</span>
                        <span class="detail-value"><?= ucfirst($port['port_type']) ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Vitesse:</span>
                        <span class="detail-value"><?= htmlspecialchars($port['port_speed'] ?? 'Non définie') ?></span>
                    </div>
                    <?php if (!empty($port['vlan_id'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">VLAN:</span>
                            <span class="detail-value vlan-tag"><?= htmlspecialchars($port['vlan_id']) ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($port['connected_to_equipment_id'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">Connexion:</span>
                            <span class="detail-value connected">
                                <i class="fas fa-link"></i>
                                Connecté
                            </span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($port['description'])): ?>
                        <div class="detail-row">
                            <span class="detail-label">Description:</span>
                            <span class="detail-value"><?= htmlspecialchars($port['description']) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="port-actions">
                    <button class="btn btn-sm btn-outline-primary" onclick="editPort(<?= $port['id'] ?>)">
                        <i class="fas fa-edit"></i>
                        Modifier
                    </button>
                    <?php if (empty($port['connected_to_equipment_id'])): ?>
                        <button class="btn btn-sm btn-outline-success" onclick="connectPort(<?= $port['id'] ?>)">
                            <i class="fas fa-link"></i>
                            Connecter
                        </button>
                    <?php else: ?>
                        <a href="?page=networks&action=disconnectPort&port_id=<?= $port['id'] ?>&equipment_id=<?= $equipment['id'] ?>" 
                           class="btn btn-sm btn-outline-warning"
                           onclick="return confirm('Êtes-vous sûr de vouloir déconnecter ce port ?')">
                            <i class="fas fa-unlink"></i>
                            Déconnecter
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal pour modifier un port -->
<div id="editPortModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Modifier le port</h3>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        <form method="POST" action="?page=networks&action=updatePort">
            <input type="hidden" name="port_id" id="edit_port_id">
            <input type="hidden" name="equipment_id" value="<?= $equipment['id'] ?>">
            
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_port_name">Nom du port</label>
                        <input type="text" id="edit_port_name" name="port_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_port_type">Type</label>
                        <select id="edit_port_type" name="port_type" class="form-control">
                            <option value="ethernet">Ethernet</option>
                            <option value="fiber">Fibre optique</option>
                            <option value="sfp">SFP</option>
                            <option value="qsfp">QSFP</option>
                            <option value="serial">Série</option>
                            <option value="console">Console</option>
                            <option value="management">Management</option>
                            <option value="power">Alimentation</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_port_speed">Vitesse</label>
                        <select id="edit_port_speed" name="port_speed" class="form-control">
                            <option value="10Mbps">10 Mbps</option>
                            <option value="100Mbps">100 Mbps</option>
                            <option value="1Gbps">1 Gbps</option>
                            <option value="10Gbps">10 Gbps</option>
                            <option value="25Gbps">25 Gbps</option>
                            <option value="40Gbps">40 Gbps</option>
                            <option value="100Gbps">100 Gbps</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_port_status">Statut</label>
                        <select id="edit_port_status" name="port_status" class="form-control">
                            <option value="inactive">Inactif</option>
                            <option value="active">Actif</option>
                            <option value="disabled">Désactivé</option>
                            <option value="error">Erreur</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="edit_vlan_id">VLAN ID</label>
                        <input type="text" id="edit_vlan_id" name="vlan_id" class="form-control" placeholder="Ex: 100">
                    </div>
                    
                    <div class="form-group full-width">
                        <label for="edit_description">Description</label>
                        <textarea id="edit_description" name="description" class="form-control" rows="3" placeholder="Description du port..."></textarea>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal pour connecter des ports -->
<div id="connectPortModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Connecter le port</h3>
            <button class="modal-close" onclick="closeConnectModal()">&times;</button>
        </div>
        <form method="POST" action="?page=networks&action=connectPorts">
            <input type="hidden" name="port1_id" id="connect_port1_id">
            <input type="hidden" name="equipment_id" value="<?= $equipment['id'] ?>">
            
            <div class="modal-body">
                <div class="form-group">
                    <label for="connect_equipment">Équipement de destination</label>
                    <select id="connect_equipment" class="form-control" required onchange="loadTargetPorts()">
                        <option value="">Sélectionner un équipement</option>
                        <?php foreach ($allEquipments as $eq): ?>
                            <?php if ($eq['id'] != $equipment['id']): ?>
                                <option value="<?= $eq['id'] ?>"><?= htmlspecialchars($eq['name']) ?> (<?= ucfirst($eq['type']) ?>)</option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="connect_port2_id">Port de destination</label>
                    <select id="connect_port2_id" name="port2_id" class="form-control" required>
                        <option value="">Sélectionner d'abord un équipement</option>
                    </select>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeConnectModal()">Annuler</button>
                <button type="submit" class="btn btn-primary">Connecter</button>
            </div>
        </form>
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.header-actions {
    display: flex;
    gap: 0.5rem;
}

.equipment-info-card {
    background: white;
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-bottom: 2rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.equipment-details h3 {
    margin: 0 0 0.5rem 0;
    color: #1f2937;
}

.equipment-meta {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    color: #6b7280;
    font-size: 0.875rem;
}

.ports-summary {
    display: flex;
    gap: 2rem;
}

.summary-item {
    text-align: center;
}

.summary-number {
    display: block;
    font-size: 1.5rem;
    font-weight: bold;
    color: #1f2937;
}

.summary-label {
    display: block;
    font-size: 0.75rem;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.ports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

.port-card {
    background: white;
    border-radius: 0.5rem;
    border: 1px solid #e5e7eb;
    overflow: hidden;
    transition: all 0.2s ease;
}

.port-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.port-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
}

.port-name {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.port-number {
    color: #6b7280;
    font-size: 0.875rem;
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}

.status-active { background-color: #10b981; }
.status-inactive { background-color: #6b7280; }
.status-disabled { background-color: #f59e0b; }
.status-error { background-color: #ef4444; }

.port-details {
    padding: 1rem;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.detail-label {
    color: #6b7280;
    font-size: 0.875rem;
}

.detail-value {
    font-size: 0.875rem;
    font-weight: 500;
    color: #1f2937;
}

.detail-value.connected {
    color: #10b981;
}

.vlan-tag {
    background: #dbeafe;
    color: #1e40af;
    padding: 0.125rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
}

.port-actions {
    padding: 1rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    gap: 0.5rem;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 0.5rem;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #6b7280;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-primary { background-color: #dbeafe; color: #1e40af; }
.badge-info { background-color: #e0f2fe; color: #0277bd; }
.badge-success { background-color: #d1fae5; color: #065f46; }

.text-success { color: #059669 !important; }
.text-muted { color: #6b7280 !important; }
.text-warning { color: #d97706 !important; }

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

.btn-primary { background-color: #6366f1; border-color: #6366f1; color: white; }
.btn-secondary { background-color: #6b7280; border-color: #6b7280; color: white; }
.btn-outline-primary { background-color: transparent; border-color: #6366f1; color: #6366f1; }
.btn-outline-secondary { background-color: transparent; border-color: #6b7280; color: #6b7280; }
.btn-outline-success { background-color: transparent; border-color: #10b981; color: #10b981; }
.btn-outline-warning { background-color: transparent; border-color: #f59e0b; color: #f59e0b; }

.btn:hover { opacity: 0.9; }
.btn-outline-primary:hover { background-color: #6366f1; color: white; }
.btn-outline-secondary:hover { background-color: #6b7280; color: white; }
.btn-outline-success:hover { background-color: #10b981; color: white; }
.btn-outline-warning:hover { background-color: #f59e0b; color: white; }

.form-control {
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
}

.form-control:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}
</style>

<script>
const portsData = <?= json_encode($ports) ?>;

function editPort(portId) {
    const port = portsData.find(p => p.id == portId);
    if (!port) return;
    
    document.getElementById('edit_port_id').value = port.id;
    document.getElementById('edit_port_name').value = port.port_name;
    document.getElementById('edit_port_type').value = port.port_type;
    document.getElementById('edit_port_speed').value = port.port_speed || '1Gbps';
    document.getElementById('edit_port_status').value = port.port_status;
    document.getElementById('edit_vlan_id').value = port.vlan_id || '';
    document.getElementById('edit_description').value = port.description || '';
    
    document.getElementById('editPortModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editPortModal').style.display = 'none';
}

function connectPort(portId) {
    document.getElementById('connect_port1_id').value = portId;
    document.getElementById('connectPortModal').style.display = 'flex';
}

function closeConnectModal() {
    document.getElementById('connectPortModal').style.display = 'none';
    document.getElementById('connect_equipment').value = '';
    document.getElementById('connect_port2_id').innerHTML = '<option value="">Sélectionner d\'abord un équipement</option>';
}

function loadTargetPorts() {
    const equipmentId = document.getElementById('connect_equipment').value;
    const portSelect = document.getElementById('connect_port2_id');
    
    if (!equipmentId) {
        portSelect.innerHTML = '<option value="">Sélectionner d\'abord un équipement</option>';
        return;
    }
    
    // Charger les ports disponibles via AJAX
    fetch(`?page=networks&action=ajaxGetPorts&equipment_id=${equipmentId}`)
        .then(response => response.json())
        .then(ports => {
            portSelect.innerHTML = '<option value="">Sélectionner un port</option>';
            ports.forEach(port => {
                portSelect.innerHTML += `<option value="${port.id}">${port.port_name} (${port.port_type})</option>`;
            });
        })
        .catch(error => {
            console.error('Erreur:', error);
            portSelect.innerHTML = '<option value="">Erreur de chargement</option>';
        });
}

// Fermer les modals en cliquant à l'extérieur
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
});
</script> 