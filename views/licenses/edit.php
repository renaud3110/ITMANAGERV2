<?php
// Gestion des messages flash
$errorMessage = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);
?>

<div class="page-header">
    <div class="page-title">
        <h1>
            <i class="fas fa-edit"></i>
            Modifier la licence
        </h1>
        <p class="page-description">
            Modification de la licence <strong><?= htmlspecialchars($license['license_name']) ?></strong>
        </p>
    </div>
    
    <div class="page-actions">
        <a href="?page=licenses" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            <span>Retour</span>
        </a>
    </div>
</div>

<?php if ($errorMessage): ?>
    <div class="alert alert-error flash-message">
        <i class="fas fa-exclamation-triangle"></i>
        <?= $errorMessage ?>
    </div>
<?php endif; ?>

<div class="form-container">
    <form method="POST" class="license-form">
        <div class="form-grid">
            <!-- Nom de la licence -->
            <div class="form-group">
                <label for="license_name" class="form-label required">
                    <i class="fas fa-key"></i>
                    Nom de la licence
                </label>
                <input type="text" 
                       id="license_name" 
                       name="license_name" 
                       class="form-input"
                       placeholder="Adobe Creative Suite, Microsoft Office..."
                       value="<?= htmlspecialchars($_POST['license_name'] ?? $license['license_name']) ?>"
                       required>
                <small class="form-help">Nom du logiciel ou de la licence</small>
            </div>

            <!-- Tenant -->
            <div class="form-group">
                <label for="tenant_id" class="form-label required">
                    <i class="fas fa-building"></i>
                    Tenant
                </label>
                <select id="tenant_id" name="tenant_id" class="form-select" required>
                    <option value="">Sélectionner un tenant</option>
                    <?php foreach ($tenants as $tenant): ?>
                        <option value="<?= $tenant['id'] ?>" 
                                <?= (($_POST['tenant_id'] ?? $license['tenant_id']) == $tenant['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tenant['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-help">Choisissez le tenant propriétaire de cette licence</small>
            </div>

            <!-- Identifiant de connexion -->
            <div class="form-group">
                <label for="login" class="form-label">
                    <i class="fas fa-user"></i>
                    Identifiant de connexion
                </label>
                <input type="text" 
                       id="login" 
                       name="login" 
                       class="form-input"
                       placeholder="nom.utilisateur@domaine.com"
                       value="<?= htmlspecialchars($_POST['login'] ?? $license['login'] ?? '') ?>">
                <small class="form-help">Identifiant pour se connecter au service (optionnel)</small>
            </div>

            <!-- Mot de passe -->
            <div class="form-group">
                <label for="password" class="form-label">
                    <i class="fas fa-lock"></i>
                    Mot de passe
                </label>
                <div class="password-input-group">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-input"
                           placeholder="Laisser vide pour conserver l'actuel"
                           value="">
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small class="form-help">
                    Laisser vide pour conserver le mot de passe actuel. 
                    <?php if (!empty($license['password'])): ?>
                        <button type="button" class="show-current-password" onclick="showCurrentPassword()">
                            <i class="fas fa-key"></i>
                            Voir l'actuel
                        </button>
                    <?php endif; ?>
                </small>
            </div>

            <!-- Nombre de licences -->
            <div class="form-group">
                <label for="license_count" class="form-label">
                    <i class="fas fa-sort-numeric-up"></i>
                    Nombre de licences
                </label>
                <input type="number" 
                       id="license_count" 
                       name="license_count" 
                       class="form-input"
                       placeholder="1"
                       min="1"
                       max="999"
                       value="<?= htmlspecialchars($_POST['license_count'] ?? $license['license_count']) ?>">
                <small class="form-help">Nombre d'unités de licence disponibles</small>
            </div>

            <!-- Date d'expiration -->
            <div class="form-group">
                <label for="expiry_date" class="form-label">
                    <i class="fas fa-calendar-alt"></i>
                    Date d'expiration
                </label>
                <input type="date" 
                       id="expiry_date" 
                       name="expiry_date" 
                       class="form-input"
                       value="<?= htmlspecialchars($_POST['expiry_date'] ?? $license['expiry_date'] ?? '') ?>">
                <small class="form-help">Date d'expiration de la licence (optionnel)</small>
            </div>

            <!-- Description -->
            <div class="form-group form-group-full">
                <label for="description" class="form-label">
                    <i class="fas fa-sticky-note"></i>
                    Description
                </label>
                <textarea id="description" 
                          name="description" 
                          class="form-textarea"
                          rows="3"
                          placeholder="Description de la licence, utilisation, notes..."><?= htmlspecialchars($_POST['description'] ?? $license['description'] ?? '') ?></textarea>
                <small class="form-help">Description détaillée de la licence (optionnel)</small>
            </div>
        </div>

        <!-- Informations système -->
        <div class="system-info">
            <h3>
                <i class="fas fa-info-circle"></i>
                Informations système
            </h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>Créée le :</label>
                    <span><?= date('d/m/Y à H:i', strtotime($license['created_at'])) ?></span>
                </div>
                <div class="info-item">
                    <label>Modifiée le :</label>
                    <span><?= date('d/m/Y à H:i', strtotime($license['updated_at'])) ?></span>
                </div>
                <div class="info-item">
                    <label>Tenant actuel :</label>
                    <span><?= htmlspecialchars($license['tenant_name']) ?></span>
                </div>
                <?php if ($license['expiry_date']): ?>
                    <div class="info-item">
                        <label>Statut :</label>
                        <span class="status-<?= $license['status_class'] ?? 'unknown' ?>">
                            <?= htmlspecialchars($license['status_text'] ?? 'Inconnu') ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                <span>Sauvegarder</span>
            </button>
            <a href="?page=licenses" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                <span>Annuler</span>
            </a>
            <button type="button" 
                    class="btn btn-danger" 
                    onclick="confirmDelete(<?= $license['id'] ?>, '<?= htmlspecialchars($license['license_name'], ENT_QUOTES) ?>')">
                <i class="fas fa-trash"></i>
                <span>Supprimer</span>
            </button>
        </div>
    </form>
</div>

<!-- Modal pour afficher le mot de passe actuel -->
<div id="currentPasswordModal" class="modal" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-key"></i>
                    Mot de passe actuel
                </h5>
                <button type="button" class="close-modal" onclick="hideCurrentPasswordModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="password-display">
                    <div class="password-field">
                        <input type="password" 
                               id="currentPasswordField" 
                               class="form-input" 
                               readonly 
                               value="Chargement...">
                        <button type="button" 
                                class="btn-toggle" 
                                id="toggleCurrentPassword"
                                onclick="toggleCurrentPasswordVisibility()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="hideCurrentPasswordModal()">
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* ===== STYLES POUR LE FORMULAIRE ===== */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 2rem;
    gap: 2rem;
}

.page-title h1 {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.page-description {
    color: #6b7280;
    font-size: 1rem;
    margin: 0;
}

.page-actions {
    flex-shrink: 0;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
}

.btn-secondary {
    background: #f3f4f6;
    color: #6b7280;
    border: 1px solid #d1d5db;
}

.btn-secondary:hover {
    background: #e5e7eb;
    color: #4b5563;
}

.btn-danger {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fca5a5;
}

.btn-danger:hover {
    background: #dc2626;
    color: white;
}

.form-container {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    padding: 2rem;
    max-width: 800px;
    margin: 0 auto;
}

.license-form {
    width: 100%;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group-full {
    grid-column: 1 / -1;
}

.form-label {
    font-weight: 600;
    color: #374151;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
}

.form-label.required::after {
    content: '*';
    color: #dc2626;
    margin-left: 0.25rem;
}

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    background: white;
    color: #374151;
    transition: all 0.2s ease;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-input::placeholder,
.form-textarea::placeholder {
    color: #9ca3af;
}

.form-textarea {
    resize: vertical;
    min-height: 80px;
}

/* ===== CHAMP MOT DE PASSE ===== */
.password-input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.password-input-group .form-input {
    padding-right: 3rem;
}

.password-toggle {
    position: absolute;
    right: 0.75rem;
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
}

.password-toggle:hover {
    color: #3b82f6;
    background: #f3f4f6;
}

.show-current-password {
    background: #e0f2fe;
    color: #0277bd;
    border: 1px solid #b3e5fc;
    padding: 0.25rem 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    cursor: pointer;
    margin-left: 0.5rem;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    transition: all 0.2s ease;
}

.show-current-password:hover {
    background: #0277bd;
    color: white;
}

.form-help {
    color: #6b7280;
    font-size: 0.75rem;
    line-height: 1.4;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
}

/* ===== INFORMATIONS SYSTÈME ===== */
.system-info {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.system-info h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #374151;
    margin: 0 0 1rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.info-item label {
    font-size: 0.75rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.info-item span {
    font-size: 0.875rem;
    color: #374151;
    font-weight: 500;
}

.status-valid {
    color: #059669;
}

.status-warning {
    color: #d97706;
}

.status-expired {
    color: #dc2626;
}

.status-unknown {
    color: #6b7280;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

/* ===== MODAL ===== */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-dialog {
    background: white;
    border-radius: 1rem;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    width: 90%;
    max-width: 400px;
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-content {
    padding: 0;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.close-modal {
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
}

.close-modal:hover {
    color: #dc2626;
    background: #fee2e2;
}

.modal-body {
    padding: 1.5rem;
}

.password-display {
    margin-bottom: 1rem;
}

.password-field {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.password-field .form-input {
    flex: 1;
    font-family: 'Courier New', monospace;
}

.btn-toggle {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    padding: 0.75rem;
    border-radius: 0.5rem;
    cursor: pointer;
    color: #6b7280;
    transition: all 0.2s ease;
}

.btn-toggle:hover {
    background: #e5e7eb;
    color: #374151;
}

.modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
}

/* ===== MESSAGES FLASH ===== */
.alert {
    padding: 1rem 1.5rem;
    border-radius: 0.75rem;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    font-weight: 500;
    border: 1px solid;
}

.alert-error {
    background: #fee2e2;
    color: #dc2626;
    border-color: #fca5a5;
}

.flash-message {
    animation: slideInDown 0.3s ease;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .form-container {
        padding: 1.5rem;
        border-radius: 0.75rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .form-group-full {
        grid-column: 1;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column-reverse;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
    
    .modal-dialog {
        width: 95%;
        margin: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-masquer les messages flash après 5 secondes
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(function(message) {
        setTimeout(function() {
            message.style.opacity = '0';
            message.style.transform = 'translateY(-20px)';
            setTimeout(function() {
                message.remove();
            }, 300);
        }, 5000);
    });
    
    // Validation du nombre de licences
    const licenseCountInput = document.getElementById('license_count');
    if (licenseCountInput) {
        licenseCountInput.addEventListener('input', function() {
            const value = parseInt(this.value);
            if (value < 1) {
                this.value = 1;
            } else if (value > 999) {
                this.value = 999;
            }
        });
    }
});

// Fonction pour afficher/masquer le mot de passe
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const button = input.parentElement.querySelector('.password-toggle');
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Fonction pour afficher le mot de passe actuel
function showCurrentPassword() {
    const modal = document.getElementById('currentPasswordModal');
    const passwordField = document.getElementById('currentPasswordField');
    
    modal.style.display = 'flex';
    passwordField.value = 'Chargement...';
    
    // Récupérer le mot de passe via AJAX
    fetch(`?page=licenses&action=getPassword&license_id=<?= $license['id'] ?>`)
        .then(response => response.json())
        .then(data => {
            if (data.password !== undefined) {
                passwordField.value = data.password || 'Aucun mot de passe';
            } else {
                passwordField.value = 'Erreur de chargement';
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            passwordField.value = 'Erreur de chargement';
        });
}

// Fonction pour masquer le modal
function hideCurrentPasswordModal() {
    const modal = document.getElementById('currentPasswordModal');
    modal.style.display = 'none';
}

// Fonction pour basculer la visibilité du mot de passe actuel
function toggleCurrentPasswordVisibility() {
    const input = document.getElementById('currentPasswordField');
    const button = document.getElementById('toggleCurrentPassword');
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Fonction de confirmation de suppression
function confirmDelete(licenseId, licenseName) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer la licence "${licenseName}" ?\n\nCette action est irréversible.`)) {
        window.location.href = `?page=licenses&action=delete&id=${licenseId}`;
    }
}

// Fermer le modal en cliquant en dehors
document.addEventListener('click', function(event) {
    const modal = document.getElementById('currentPasswordModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});
</script>