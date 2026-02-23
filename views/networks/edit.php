<div class="page-header">
    <h1 class="page-title">Modifier l'équipement: <?= htmlspecialchars($equipment['name']) ?></h1>
    <a href="?page=networks" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i>
        Retour
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Informations de l'équipement</h2>
        <?php if ($equipment['ports_count'] > 0): ?>
            <a href="?page=networks&action=ports&equipment_id=<?= $equipment['id'] ?>" class="btn btn-outline-primary">
                <i class="fas fa-ethernet"></i>
                Gérer les ports (<?= $equipment['ports_count'] ?>)
            </a>
        <?php endif; ?>
    </div>
    
    <form method="POST" class="equipment-form">
        <div class="form-grid">
            <div class="form-group">
                <label for="name" class="required">Nom de l'équipement</label>
                <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($equipment['name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="type" class="required">Type d'équipement</label>
                <select id="type" name="type" class="form-control" required>
                    <option value="">Sélectionner un type</option>
                    <option value="router" <?= $equipment['type'] === 'router' ? 'selected' : '' ?>>Router</option>
                    <option value="switch" <?= $equipment['type'] === 'switch' ? 'selected' : '' ?>>Switch</option>
                    <option value="wifiAP" <?= $equipment['type'] === 'wifiAP' ? 'selected' : '' ?>>Point d'accès WiFi</option>
                    <option value="wifi infra" <?= $equipment['type'] === 'wifi infra' ? 'selected' : '' ?>>Infrastructure WiFi</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="site_id" class="required">Site</label>
                <select id="site_id" name="site_id" class="form-control" required>
                    <option value="">Sélectionner un site</option>
                    <?php foreach ($sites as $site): ?>
                        <option value="<?= $site['id'] ?>" <?= $equipment['site_id'] == $site['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($site['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="status">Statut</label>
                <select id="status" name="status" class="form-control">
                    <option value="inactive" <?= $equipment['status'] === 'inactive' ? 'selected' : '' ?>>Inactif</option>
                    <option value="active" <?= $equipment['status'] === 'active' ? 'selected' : '' ?>>Actif</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="manufacturer_id">Fabricant</label>
                <select id="manufacturer_id" name="manufacturer_id" class="form-control">
                    <option value="">Sélectionner un fabricant</option>
                    <?php foreach ($manufacturers as $manufacturer): ?>
                        <option value="<?= $manufacturer['id'] ?>" <?= $equipment['manufacturer_id'] == $manufacturer['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($manufacturer['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="model_id">Modèle</label>
                <select id="model_id" name="model_id" class="form-control">
                    <option value="">Sélectionner un modèle</option>
                    <?php foreach ($models as $model): ?>
                        <option value="<?= $model['id'] ?>" <?= $equipment['model_id'] == $model['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($model['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="ip_address_id">Adresse IP de gestion</label>
                <select id="ip_address_id" name="ip_address_id" class="form-control">
                    <option value="">Sélectionner une adresse IP</option>
                    <?php foreach ($ipAddresses as $ip): ?>
                        <option value="<?= $ip['id'] ?>" <?= $equipment['ip_address_id'] == $ip['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ip['address']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="login_id">Identifiants de connexion</label>
                <select id="login_id" name="login_id" class="form-control">
                    <option value="">Sélectionner des identifiants</option>
                    <?php foreach ($logins as $login): ?>
                        <option value="<?= $login['id'] ?>" <?= $equipment['login_id'] == $login['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($login['username']) ?>
                            <?php if (!empty($login['service_name'])): ?>
                                - <?= htmlspecialchars($login['service_name']) ?>
                            <?php endif; ?>
                            <?php if (!empty($login['description'])): ?>
                                (<?= htmlspecialchars($login['description']) ?>)
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-help">Format: Nom d'utilisateur - Service (Description)</small>
            </div>
        </div>
        
        <div class="form-section">
            <h3>Configuration des ports</h3>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Attention:</strong> Modifier le nombre de ports supprimera tous les ports existants et leurs connexions, puis créera de nouveaux ports.
            </div>
            <div class="form-grid">
                <div class="form-group">
                    <label for="ports_count">Nombre de ports</label>
                    <input type="number" id="ports_count" name="ports_count" class="form-control" min="0" max="100" 
                           value="<?= $equipment['ports_count'] ?>">
                    <small class="form-help">Nombre actuel: <?= $equipment['ports_count'] ?> ports</small>
                </div>
                
                <div class="form-group">
                    <label for="port_type">Type de port par défaut</label>
                    <select id="port_type" name="port_type" class="form-control">
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
                    <label for="port_speed">Vitesse par défaut</label>
                    <select id="port_speed" name="port_speed" class="form-control">
                        <option value="10Mbps">10 Mbps</option>
                        <option value="100Mbps">100 Mbps</option>
                        <option value="1Gbps" selected>1 Gbps</option>
                        <option value="10Gbps">10 Gbps</option>
                        <option value="25Gbps">25 Gbps</option>
                        <option value="40Gbps">40 Gbps</option>
                        <option value="100Gbps">100 Gbps</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Mettre à jour
            </button>
            <a href="?page=networks" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                Annuler
            </a>
            <?php if ($equipment['ports_count'] > 0): ?>
                <a href="?page=networks&action=ports&equipment_id=<?= $equipment['id'] ?>" class="btn btn-outline-primary">
                    <i class="fas fa-ethernet"></i>
                    Gérer les ports
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<style>
.equipment-form {
    padding: 1.5rem;
}

.form-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
}

.form-section h3 {
    margin-bottom: 1rem;
    color: #374151;
    font-size: 1.125rem;
    font-weight: 600;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: #374151;
}

.form-group label.required::after {
    content: " *";
    color: #dc2626;
}

.form-control {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.form-help {
    margin-top: 0.25rem;
    font-size: 0.75rem;
    color: #6b7280;
}

.form-actions {
    display: flex;
    gap: 1rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
    margin-top: 2rem;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0;
    padding: 1.5rem 1.5rem 0;
    border-bottom: none;
}

.card-title {
    margin: 0;
    color: #374151;
    font-size: 1.25rem;
    font-weight: 600;
}

.alert {
    padding: 1rem;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
}

.alert-info {
    background-color: #dbeafe;
    border: 1px solid #93c5fd;
    color: #1e40af;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border: 1px solid transparent;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
}

.btn-primary {
    background-color: #6366f1;
    border-color: #6366f1;
    color: white;
}

.btn-primary:hover {
    background-color: #5b21b6;
    border-color: #5b21b6;
}

.btn-secondary {
    background-color: #6b7280;
    border-color: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background-color: #374151;
    border-color: #374151;
}

.btn-outline-primary {
    background-color: transparent;
    border-color: #6366f1;
    color: #6366f1;
}

.btn-outline-primary:hover {
    background-color: #6366f1;
    color: white;
}
</style>

<script>
$(document).ready(function() {
    const originalPortsCount = <?= $equipment['ports_count'] ?>;
    
    // Avertir si changement du nombre de ports
    $('#ports_count').change(function() {
        const newCount = parseInt($(this).val()) || 0;
        const warningDiv = $('.port-warning');
        
        if (newCount !== originalPortsCount && originalPortsCount > 0) {
            if (warningDiv.length === 0) {
                $('.form-section .alert').after(`
                    <div class="alert alert-warning port-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Attention:</strong> Changer le nombre de ports supprimera tous les ports actuels et leurs connexions !
                    </div>
                `);
            }
        } else {
            warningDiv.remove();
        }
    });
    
    // Validation du formulaire
    $('form').submit(function(e) {
        const portsCount = parseInt($('#ports_count').val()) || 0;
        const newCount = portsCount;
        
        if (portsCount > 100) {
            alert('Le nombre de ports ne peut pas dépasser 100');
            e.preventDefault();
            return false;
        }
        
        if (newCount !== originalPortsCount && originalPortsCount > 0) {
            if (!confirm('Êtes-vous sûr de vouloir modifier le nombre de ports ? Cela supprimera tous les ports existants et leurs connexions.')) {
                e.preventDefault();
                return false;
            }
        }
    });
});
</script> 