<div class="page-header">
    <h1 class="page-title">Modifier <?= htmlspecialchars($person['prenom'] . ' ' . $person['nom']) ?></h1>
    <div class="page-actions">
        <a href="?page=accounts&action=view&id=<?= $person['id'] ?>" class="btn btn-secondary">
            <i class="fas fa-eye"></i>
            Voir les détails
        </a>
        <a href="?page=accounts" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Retour à la liste
        </a>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-user-plus"></i>
            Informations de la personne
        </h2>
    </div>
    
    <div class="card-body">
        <form method="POST" class="form">
            <div class="form-row">
                <div class="form-group">
                    <label for="prenom">Prénom *</label>
                    <input type="text" 
                           id="prenom" 
                           name="prenom" 
                           class="form-control" 
                           required 
                           value="<?= htmlspecialchars($_POST['prenom'] ?? $person['prenom']) ?>"
                           placeholder="Entrez le prénom">
                    <small class="form-help">Le prénom de la personne</small>
                </div>

                <div class="form-group">
                    <label for="nom">Nom *</label>
                    <input type="text" 
                           id="nom" 
                           name="nom" 
                           class="form-control" 
                           required 
                           value="<?= htmlspecialchars($_POST['nom'] ?? $person['nom']) ?>"
                           placeholder="Entrez le nom de famille">
                    <small class="form-help">Le nom de famille de la personne</small>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       class="form-control" 
                       value="<?= htmlspecialchars($_POST['email'] ?? $person['email']) ?>"
                       placeholder="exemple@domaine.com">
                <small class="form-help">Adresse email professionnelle (optionnel)</small>
            </div>

            <div class="form-group">
                <label for="tenant_id">Tenant *</label>
                <select id="tenant_id" name="tenant_id" class="form-control" required>
                    <?php 
                    $currentTenant = $_SESSION['current_tenant'] ?? 'all';
                    $selectedTenant = $_POST['tenant_id'] ?? $person['tenant_id'];
                    ?>
                    <?php if (!empty($tenants)): ?>
                        <?php foreach ($tenants as $tenant): ?>
                            <option value="<?= $tenant['id'] ?>" <?= $selectedTenant == $tenant['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($tenant['name']) ?>
                                <?php if (!empty($tenant['domain'])): ?>
                                    (<?= htmlspecialchars($tenant['domain']) ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="1">Tenant par défaut</option>
                    <?php endif; ?>
                </select>
                <small class="form-help">Tenant auquel appartient cette personne</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Mettre à jour
                </button>
                <a href="?page=accounts" class="btn btn-secondary">
                    <i class="fas fa-times"></i>
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>

<div class="info-card">
    <div class="info-header">
        <i class="fas fa-info-circle"></i>
        <h3>Informations importantes</h3>
    </div>
    <div class="info-content">
        <ul>
            <li><strong>Prénom et nom</strong> sont obligatoires pour identifier la personne</li>
            <li><strong>L'email</strong> est optionnel mais recommandé pour les notifications</li>
            <li><strong>Le tenant</strong> détermine l'organisation à laquelle appartient la personne</li>
            <li>Après création, vous pourrez ajouter des comptes de connexion à cette personne</li>
        </ul>
    </div>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert-error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.alert i {
    font-size: 1.25rem;
}

.form {
    max-width: 600px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
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
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-control:invalid {
    border-color: #ef4444;
}

.form-help {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.form-actions {
    display: flex;
    gap: 1rem;
    padding-top: 2rem;
    border-top: 1px solid #e5e7eb;
    margin-top: 2rem;
}

.info-card {
    margin-top: 2rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}

.info-header {
    background: #667eea;
    color: white;
    padding: 1rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.info-header h3 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}

.info-content {
    padding: 1.5rem;
}

.info-content ul {
    margin: 0;
    padding-left: 1.5rem;
}

.info-content li {
    margin-bottom: 0.75rem;
    color: #4b5563;
    line-height: 1.5;
}

.info-content li:last-child {
    margin-bottom: 0;
}

/* Responsive design */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .form-row {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
// Validation en temps réel
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.form');
    const prenomInput = document.getElementById('prenom');
    const nomInput = document.getElementById('nom');
    const emailInput = document.getElementById('email');
    
    // Validation du prénom
    prenomInput.addEventListener('input', function() {
        if (this.value.trim().length < 2) {
            this.setCustomValidity('Le prénom doit contenir au moins 2 caractères');
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Validation du nom
    nomInput.addEventListener('input', function() {
        if (this.value.trim().length < 2) {
            this.setCustomValidity('Le nom doit contenir au moins 2 caractères');
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Validation de l'email
    emailInput.addEventListener('input', function() {
        if (this.value && !this.value.includes('@')) {
            this.setCustomValidity('Veuillez entrer une adresse email valide');
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Soumission du formulaire
    form.addEventListener('submit', function(e) {
        const prenom = prenomInput.value.trim();
        const nom = nomInput.value.trim();
        
        if (!prenom || !nom) {
            e.preventDefault();
            alert('Le prénom et le nom sont obligatoires');
            return;
        }
        
        if (prenom.length < 2 || nom.length < 2) {
            e.preventDefault();
            alert('Le prénom et le nom doivent contenir au moins 2 caractères');
            return;
        }
    });
});
</script> 