<?php
// Gestion des messages flash
$errorMessage = $_SESSION['error_message'] ?? null;
unset($_SESSION['error_message']);
?>

<div class="page-header">
    <div class="page-title">
        <h1>
            <i class="fas fa-edit"></i>
            Modifier le domaine
        </h1>
        <p class="page-description">
            Modification du domaine <strong><?= htmlspecialchars($domain['domain_name']) ?></strong>
        </p>
    </div>
    
    <div class="page-actions">
        <a href="?page=domains" class="btn btn-secondary">
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
    <form method="POST" class="domain-form">
        <div class="form-grid">
            <!-- Nom de domaine -->
            <div class="form-group">
                <label for="domain_name" class="form-label required">
                    <i class="fas fa-globe"></i>
                    Nom de domaine
                </label>
                <input type="text" 
                       id="domain_name" 
                       name="domain_name" 
                       class="form-input"
                       placeholder="exemple.com"
                       value="<?= htmlspecialchars($_POST['domain_name'] ?? $domain['domain_name']) ?>"
                       required>
                <small class="form-help">Entrez le nom de domaine complet (ex: monsite.com)</small>
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
                                <?= (($_POST['tenant_id'] ?? $domain['tenant_id']) == $tenant['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tenant['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-help">Choisissez le tenant propriétaire de ce domaine</small>
            </div>

            <!-- Gestion du domaine -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-cog"></i>
                    Gestion du domaine
                </label>
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" 
                               name="is_managed" 
                               value="1" 
                               <?= ($_POST['is_managed'] ?? $domain['is_managed']) ? 'checked' : '' ?>>
                        <span class="checkbox-text">Ce domaine est géré par notre équipe</span>
                    </label>
                </div>
                <small class="form-help">Cochez si vous gérez activement ce domaine</small>
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
                       value="<?= htmlspecialchars($_POST['expiry_date'] ?? $domain['expiry_date']) ?>">
                <small class="form-help">Date d'expiration du domaine (optionnel)</small>
            </div>

            <!-- Hébergeur/Registrar -->
            <div class="form-group">
                <label for="hosting_provider" class="form-label">
                    <i class="fas fa-server"></i>
                    Hébergeur/Registrar
                </label>
                <input type="text" 
                       id="hosting_provider" 
                       name="hosting_provider" 
                       class="form-input"
                       placeholder="OVH, Gandi, Namecheap..."
                       value="<?= htmlspecialchars($_POST['hosting_provider'] ?? $domain['hosting_provider'] ?? '') ?>">
                <small class="form-help">Nom de l'hébergeur ou du registrar (optionnel)</small>
            </div>

            <!-- Renouvellement automatique -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-sync-alt"></i>
                    Renouvellement automatique
                </label>
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" 
                               name="auto_renewal" 
                               value="1" 
                               <?= ($_POST['auto_renewal'] ?? $domain['auto_renewal']) ? 'checked' : '' ?>>
                        <span class="checkbox-text">Le domaine se renouvelle automatiquement</span>
                    </label>
                </div>
                <small class="form-help">Cochez si le renouvellement automatique est activé</small>
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
                    <label>Créé le :</label>
                    <span><?= date('d/m/Y à H:i', strtotime($domain['created_at'])) ?></span>
                </div>
                <div class="info-item">
                    <label>Modifié le :</label>
                    <span><?= date('d/m/Y à H:i', strtotime($domain['updated_at'])) ?></span>
                </div>
                <div class="info-item">
                    <label>Tenant actuel :</label>
                    <span><?= htmlspecialchars($domain['tenant_name']) ?></span>
                </div>
                <?php if ($domain['expiry_date']): ?>
                    <div class="info-item">
                        <label>Statut :</label>
                        <span class="status-<?= $domain['status_class'] ?? 'unknown' ?>">
                            <?= htmlspecialchars($domain['status_text'] ?? 'Inconnu') ?>
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
            <a href="?page=domains" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                <span>Annuler</span>
            </a>
            <button type="button" 
                    class="btn btn-danger" 
                    onclick="confirmDelete(<?= $domain['id'] ?>, '<?= htmlspecialchars($domain['domain_name'], ENT_QUOTES) ?>')">
                <i class="fas fa-trash"></i>
                <span>Supprimer</span>
            </button>
        </div>
    </form>
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

.domain-form {
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

.form-group:nth-child(1),
.form-group:nth-child(3),
.form-group:nth-child(6) {
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
.form-select {
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
.form-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-input::placeholder {
    color: #9ca3af;
}

.checkbox-group {
    margin-top: 0.5rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    cursor: pointer;
    font-weight: 400;
    color: #374151;
}

.checkbox-label input[type="checkbox"] {
    width: 1.25rem;
    height: 1.25rem;
    accent-color: #3b82f6;
    cursor: pointer;
}

.checkbox-text {
    font-size: 0.875rem;
}

.form-help {
    color: #6b7280;
    font-size: 0.75rem;
    line-height: 1.4;
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
    
    .form-group:nth-child(1),
    .form-group:nth-child(3),
    .form-group:nth-child(6) {
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
    
    // Validation du nom de domaine en temps réel
    const domainInput = document.getElementById('domain_name');
    if (domainInput) {
        domainInput.addEventListener('input', function() {
            let value = this.value.toLowerCase().trim();
            
            // Supprimer les protocoles si présents
            value = value.replace(/^https?:\/\//, '');
            value = value.replace(/^www\./, '');
            
            // Supprimer les chemins
            value = value.split('/')[0];
            
            this.value = value;
        });
    }
});

// Fonction de confirmation de suppression
function confirmDelete(domainId, domainName) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le domaine "${domainName}" ?\n\nCette action est irréversible.`)) {
        window.location.href = `?page=domains&action=delete&id=${domainId}`;
    }
}
</script>