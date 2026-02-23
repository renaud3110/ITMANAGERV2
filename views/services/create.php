<?php
$pageTitle = 'Nouveau Service';
?>

<div class="page-header">
    <div>
        <h1>Nouveau Service</h1>
        <p class="page-subtitle">Créer un nouveau service de connexion</p>
    </div>
    <div class="page-actions">
        <a href="?page=services" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Retour à la liste
        </a>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3>Informations du service</h3>
    </div>
    
    <div class="card-body">
        <form method="POST" class="service-form">
            <div class="form-group">
                <label for="nom">Nom du service *</label>
                <input type="text" 
                       id="nom" 
                       name="nom" 
                       class="form-control" 
                       required 
                       placeholder="Ex: Office365, Adobe, etc."
                       value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" 
                          name="description" 
                          class="form-control" 
                          rows="3"
                          placeholder="Description optionnelle du service"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="logo">Logo (Icône Font Awesome)</label>
                <div class="logo-input-group">
                    <input type="text" 
                           id="logo" 
                           name="logo" 
                           class="form-control" 
                           placeholder="Ex: fab fa-microsoft, fas fa-database"
                           value="<?= htmlspecialchars($_POST['logo'] ?? '') ?>"
                           oninput="updateLogoPreview()">
                    <div class="logo-preview" id="logo-preview">
                        <i class="fas fa-cog"></i>
                    </div>
                </div>
                <small class="form-help">
                    Utilisez les classes Font Awesome. Exemples: 
                    <span class="logo-example" onclick="setLogo('fab fa-microsoft')">fab fa-microsoft</span>,
                    <span class="logo-example" onclick="setLogo('fas fa-file-pdf')">fas fa-file-pdf</span>,
                    <span class="logo-example" onclick="setLogo('fas fa-database')">fas fa-database</span>,
                    <span class="logo-example" onclick="setLogo('fas fa-server')">fas fa-server</span>
                </small>
            </div>
            
            <div class="popular-logos">
                <h4>Logos populaires</h4>
                <div class="logo-grid">
                    <div class="logo-option" onclick="setLogo('fab fa-microsoft')" title="Microsoft">
                        <i class="fab fa-microsoft"></i>
                        <span>Microsoft</span>
                    </div>
                    <div class="logo-option" onclick="setLogo('fas fa-file-pdf')" title="Adobe/PDF">
                        <i class="fas fa-file-pdf"></i>
                        <span>Adobe</span>
                    </div>
                    <div class="logo-option" onclick="setLogo('fab fa-google')" title="Google">
                        <i class="fab fa-google"></i>
                        <span>Google</span>
                    </div>
                    <div class="logo-option" onclick="setLogo('fas fa-database')" title="Base de données">
                        <i class="fas fa-database"></i>
                        <span>Database</span>
                    </div>
                    <div class="logo-option" onclick="setLogo('fas fa-server')" title="Serveur">
                        <i class="fas fa-server"></i>
                        <span>Serveur</span>
                    </div>
                    <div class="logo-option" onclick="setLogo('fas fa-network-wired')" title="Réseau">
                        <i class="fas fa-network-wired"></i>
                        <span>Réseau</span>
                    </div>
                    <div class="logo-option" onclick="setLogo('fas fa-desktop')" title="Desktop">
                        <i class="fas fa-desktop"></i>
                        <span>Desktop</span>
                    </div>
                    <div class="logo-option" onclick="setLogo('fas fa-users-cog')" title="Active Directory">
                        <i class="fas fa-users-cog"></i>
                        <span>AD</span>
                    </div>
                    <div class="logo-option" onclick="setLogo('fas fa-cloud')" title="Cloud">
                        <i class="fas fa-cloud"></i>
                        <span>Cloud</span>
                    </div>
                    <div class="logo-option" onclick="setLogo('fas fa-shield-alt')" title="Sécurité">
                        <i class="fas fa-shield-alt"></i>
                        <span>Sécurité</span>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Créer le service
                </button>
                <a href="?page=services" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Annuler
                </a>
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

.page-subtitle {
    color: #6b7280;
    margin: 0.5rem 0 0 0;
}

.page-actions {
    display: flex;
    gap: 1rem;
}

.service-form {
    max-width: 600px;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.logo-input-group {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.logo-input-group .form-control {
    flex: 1;
}

.logo-preview {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.form-help {
    display: block;
    margin-top: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.logo-example {
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.logo-example:hover {
    background: #e5e7eb;
}

.popular-logos {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
}

.popular-logos h4 {
    margin-bottom: 1rem;
    color: #374151;
}

.logo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 1rem;
}

.logo-option {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    background: #f8fafc;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.logo-option:hover {
    background: #e5e7eb;
    border-color: #667eea;
}

.logo-option i {
    font-size: 1.5rem;
    color: #667eea;
}

.logo-option span {
    font-size: 0.75rem;
    color: #6b7280;
    text-align: center;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border: none;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #4f46e5;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}
</style>

<script>
function updateLogoPreview() {
    const logoInput = document.getElementById('logo');
    const logoPreview = document.getElementById('logo-preview');
    const logoValue = logoInput.value.trim();
    
    if (logoValue) {
        logoPreview.innerHTML = `<i class="${logoValue}"></i>`;
    } else {
        logoPreview.innerHTML = '<i class="fas fa-cog"></i>';
    }
}

function setLogo(logoClass) {
    const logoInput = document.getElementById('logo');
    logoInput.value = logoClass;
    updateLogoPreview();
}

// Initialiser la prévisualisation au chargement
document.addEventListener('DOMContentLoaded', function() {
    updateLogoPreview();
});
</script>

 