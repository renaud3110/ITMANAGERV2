<?php include 'views/partials/header.php'; ?>

<div class="container mt-4">
    <div class="page-header">
        <h1 class="page-title">Modifier l'Adresse IP</h1>
        <a href="?page=ip-management" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Retour
        </a>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Informations de l'adresse IP</h2>
        </div>
        
        <form method="POST" class="ip-form">
            <div class="form-grid">
                <div class="form-group">
                    <label for="ip_address" class="required">Adresse IP</label>
                    <input type="text" id="ip_address" name="ip_address" class="form-control" 
                           value="<?= htmlspecialchars($ipAddress['ip_address'] ?? '') ?>" 
                           placeholder="192.168.1.1" required>
                </div>
                
                <div class="form-group">
                    <label for="subnet_mask">Masque de sous-réseau</label>
                    <input type="text" id="subnet_mask" name="subnet_mask" class="form-control" 
                           value="<?= htmlspecialchars($ipAddress['subnet_mask'] ?? '') ?>"
                           placeholder="255.255.255.0">
                </div>
                
                <div class="form-group">
                    <label for="gateway">Passerelle</label>
                    <input type="text" id="gateway" name="gateway" class="form-control" 
                           value="<?= htmlspecialchars($ipAddress['gateway'] ?? '') ?>"
                           placeholder="192.168.1.1">
                </div>
                
                <div class="form-group">
                    <label for="vlan_id">VLAN ID</label>
                    <input type="number" id="vlan_id" name="vlan_id" class="form-control" 
                           value="<?= htmlspecialchars($ipAddress['vlan_id'] ?? '') ?>"
                           placeholder="10" min="1" max="4094">
                </div>
                
                <div class="form-group">
                    <label for="dns1">DNS Primaire</label>
                    <input type="text" id="dns1" name="dns1" class="form-control" 
                           value="<?= htmlspecialchars($ipAddress['dns1'] ?? '') ?>"
                           placeholder="8.8.8.8">
                </div>
                
                <div class="form-group">
                    <label for="dns2">DNS Secondaire</label>
                    <input type="text" id="dns2" name="dns2" class="form-control" 
                           value="<?= htmlspecialchars($ipAddress['dns2'] ?? '') ?>"
                           placeholder="8.8.4.4">
                </div>
                
                <div class="form-group">
                    <label for="tenant_id">Tenant</label>
                    <select id="tenant_id" name="tenant_id" class="form-control">
                        <option value="">Sélectionner un tenant</option>
                        <?php foreach ($tenants as $tenant): ?>
                            <option value="<?= $tenant['id'] ?>" 
                                    <?= ($ipAddress['tenant_id'] ?? '') == $tenant['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tenant['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="site_id">Site</label>
                    <select id="site_id" name="site_id" class="form-control">
                        <option value="">Sélectionner un site</option>
                        <?php foreach ($sites as $site): ?>
                            <option value="<?= $site['id'] ?>" 
                                    <?= ($ipAddress['site_id'] ?? '') == $site['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($site['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3" 
                          placeholder="Description de l'utilisation de cette adresse IP..."><?= htmlspecialchars($ipAddress['description'] ?? '') ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Modifier l'adresse IP
                </button>
                <a href="?page=ip-management" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation en temps réel de l'adresse IP
    const ipInput = document.getElementById('ip_address');
    const subnetInput = document.getElementById('subnet_mask');
    const gatewayInput = document.getElementById('gateway');
    const dns1Input = document.getElementById('dns1');
    const dns2Input = document.getElementById('dns2');
    
    function validateIP(input) {
        const ipRegex = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
        const isValid = ipRegex.test(input.value) || input.value === '';
        
        if (isValid) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
        }
        
        return isValid;
    }
    
    // Validation en temps réel
    [ipInput, subnetInput, gatewayInput, dns1Input, dns2Input].forEach(input => {
        if (input) {
            input.addEventListener('blur', function() {
                if (this.value) {
                    validateIP(this);
                }
            });
        }
    });
});
</script>

<style>
.ip-form .form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
}

.form-group label.required::after {
    content: ' *';
    color: #ef4444;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.375rem;
    font-size: 1rem;
    transition: all 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.form-control.is-valid {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.form-control.is-invalid {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.form-actions {
    display: flex;
    gap: 1rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

.card {
    background: #fff;
    border-radius: 0.75rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #f8fafc 0%, #e5e7eb 100%);
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.card-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
}

.card form {
    padding: 1.5rem;
}

@media (max-width: 768px) {
    .ip-form .form-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<?php include 'views/partials/footer.php'; ?> 