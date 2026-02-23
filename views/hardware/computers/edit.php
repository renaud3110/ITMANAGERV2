<div class="page-header">
    <h1 class="page-title">Modifier l'ordinateur</h1>
    <div class="page-actions">
        <a href="?page=hardware&section=computers" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Retour à la liste
        </a>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-error">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Informations de l'ordinateur</h2>
    </div>
    <div class="card-body">
        <form method="POST" class="form">
            <div class="edit-section">
                <h3 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Informations de l'ordinateur
                </h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">Nom de l'ordinateur <span class="required">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?= htmlspecialchars($computer['name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="site_id">Site <span class="required">*</span></label>
                        <select id="site_id" name="site_id" class="form-control" required>
                            <option value="">Sélectionner un site</option>
                            <?php foreach ($sites as $site): ?>
                                <option value="<?= $site['id'] ?>" 
                                        <?= ($computer['site_id'] == $site['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($site['name']) ?>
                                    <?php if (isset($site['tenant_name'])): ?>
                                        (<?= htmlspecialchars($site['tenant_name']) ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Statut</label>
                        <select id="status" name="status" class="form-control">
                            <option value="free" <?= ($computer['status'] ?? '') === 'free' ? 'selected' : '' ?>>
                                Libre
                            </option>
                            <option value="used" <?= ($computer['status'] ?? '') === 'used' ? 'selected' : '' ?>>
                                Utilisé
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="person_id">Personne attribuée</label>
                        <select id="person_id" name="person_id" class="form-control">
                            <option value="">Aucune personne assignée</option>
                            <?php foreach ($persons as $person): ?>
                                <option value="<?= $person['id'] ?>" 
                                        <?= ($computer['person_id'] == $person['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($person['prenom'] . ' ' . $person['nom']) ?>
                                    <?php if ($person['email']): ?>
                                        (<?= htmlspecialchars($person['email']) ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="new_person">+ Créer une nouvelle personne</option>
                        </select>
                    </div>

                    <div class="form-group" id="new_person_section" style="display: none;">
                        <div class="new-person-form">
                            <h4 class="new-person-title">
                                <i class="fas fa-user-plus"></i>
                                Créer une nouvelle personne
                            </h4>
                            
                            <div class="new-person-grid">
                                <div class="form-group">
                                    <label for="new_first_name">Prénom <span class="required">*</span></label>
                                    <input type="text" id="new_first_name" name="new_first_name" class="form-control" 
                                           placeholder="Prénom de la personne">
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_last_name">Nom <span class="required">*</span></label>
                                    <input type="text" id="new_last_name" name="new_last_name" class="form-control" 
                                           placeholder="Nom de famille">
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_email">Email</label>
                                    <input type="email" id="new_email" name="new_email" class="form-control" 
                                           placeholder="adresse@email.com (optionnel)">
                                </div>
                            </div>
                            
                            <small class="form-help">
                                <i class="fas fa-info-circle"></i>
                                Une fois créée, cette personne pourra être réutilisée pour d'autres ordinateurs
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Champs cachés pour préserver les données non modifiables -->
            <input type="hidden" name="tenant_id" value="<?= htmlspecialchars($computer['tenant_id'] ?? '') ?>">
            <input type="hidden" name="serial_number" value="<?= htmlspecialchars($computer['serial_number'] ?? '') ?>">
            <input type="hidden" name="model_id" value="<?= htmlspecialchars($computer['model_id'] ?? '') ?>">
            <input type="hidden" name="processor_model" value="<?= htmlspecialchars($computer['processor_model'] ?? '') ?>">
            <input type="hidden" name="operating_system_id" value="<?= htmlspecialchars($computer['operating_system_id'] ?? '') ?>">
            <input type="hidden" name="ip_address_id" value="<?= htmlspecialchars($computer['ip_address_id'] ?? '') ?>">
            <input type="hidden" name="teamviewer_id" value="<?= htmlspecialchars($computer['teamviewer_id'] ?? '') ?>">
            <input type="hidden" name="last_account" value="<?= htmlspecialchars($computer['last_account'] ?? '') ?>">

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Enregistrer les modifications
                </button>
                <a href="?page=hardware&section=computers" class="btn btn-secondary">
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

.page-actions {
    display: flex;
    gap: 0.5rem;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.alert-error {
    background-color: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
}

.form {
    max-width: none;
}

.edit-section {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #ffffff;
    border-radius: 8px;
    border: 2px solid #667eea;
}

.section-title {
    margin: 0 0 1.5rem 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-title i {
    color: #667eea;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-weight: 500;
    color: #374151;
    font-size: 0.875rem;
}

.required {
    color: #dc2626;
}

.form-control {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    transition: border-color 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-actions {
    display: flex;
    gap: 1rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-primary {
    background-color: #667eea;
    color: white;
}

.btn-primary:hover {
    background-color: #5a67d8;
}

.btn-secondary {
    background-color: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
}

.btn-secondary:hover {
    background-color: #e5e7eb;
}

/* Styles pour la création d'utilisateur */
.form-help {
    color: #6b7280;
    font-size: 0.75rem;
    margin-top: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.form-help i {
    color: #3b82f6;
}

#new_user_section {
    background: #f0f9ff;
    border: 1px solid #bfdbfe;
    border-radius: 6px;
    padding: 1.5rem;
    margin-top: 0.5rem;
    animation: slideDown 0.3s ease-out;
}

.new-person-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.new-person-title {
    margin: 0 0 1rem 0;
    color: #1e40af;
    font-size: 1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border-bottom: 1px solid #bfdbfe;
    padding-bottom: 0.5rem;
}

.new-person-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.new-person-grid .form-group {
    margin: 0;
}

.new-person-grid label {
    color: #1e40af;
    font-weight: 600;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive design */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const personSelect = document.getElementById('person_id');
    const newPersonSection = document.getElementById('new_person_section');
    const firstNameInput = document.getElementById('new_first_name');
    const lastNameInput = document.getElementById('new_last_name');
    const emailInput = document.getElementById('new_email');
    
    personSelect.addEventListener('change', function() {
        if (this.value === 'new_person') {
            newPersonSection.style.display = 'block';
            firstNameInput.required = true;
            lastNameInput.required = true;
            setTimeout(() => firstNameInput.focus(), 100);
        } else {
            newPersonSection.style.display = 'none';
            firstNameInput.required = false;
            lastNameInput.required = false;
            // Réinitialiser les champs
            firstNameInput.value = '';
            lastNameInput.value = '';
            emailInput.value = '';
        }
    });
    
    // Validation du formulaire
    const form = document.querySelector('.form');
    form.addEventListener('submit', function(e) {
        if (personSelect.value === 'new_person') {
            const firstName = firstNameInput.value.trim();
            const lastName = lastNameInput.value.trim();
            const email = emailInput.value.trim();
            
            if (!firstName) {
                e.preventDefault();
                alert('Veuillez entrer un prénom');
                firstNameInput.focus();
                return false;
            }
            
            if (!lastName) {
                e.preventDefault();
                alert('Veuillez entrer un nom');
                lastNameInput.focus();
                return false;
            }
            
            // Vérifier que les noms ne contiennent que des caractères valides
            const validName = /^[a-zA-ZÀ-ÿ\s-']+$/.test(firstName) && /^[a-zA-ZÀ-ÿ\s-']+$/.test(lastName);
            if (!validName) {
                e.preventDefault();
                alert('Les noms ne peuvent contenir que des lettres, espaces, tirets et apostrophes');
                return false;
            }
            
            // Vérifier la longueur minimale
            if (firstName.length < 2 || lastName.length < 2) {
                e.preventDefault();
                alert('Le prénom et le nom doivent contenir au moins 2 caractères');
                return false;
            }
            
            // Vérifier l'email si fourni
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                e.preventDefault();
                alert('Veuillez entrer une adresse email valide');
                emailInput.focus();
                return false;
            }
        }
    });
    
    // Validation en temps réel des champs
    function validateNameInput(input) {
        input.addEventListener('input', function() {
            const name = this.value.trim();
            const validName = /^[a-zA-ZÀ-ÿ\s-']*$/.test(name);
            
            if (!validName && name.length > 0) {
                this.style.borderColor = '#dc2626';
                this.style.backgroundColor = '#fef2f2';
            } else {
                this.style.borderColor = '#d1d5db';
                this.style.backgroundColor = 'white';
            }
        });
    }
    
    function validateEmailInput(input) {
        input.addEventListener('input', function() {
            const email = this.value.trim();
            const validEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            
            if (email && !validEmail) {
                this.style.borderColor = '#dc2626';
                this.style.backgroundColor = '#fef2f2';
            } else {
                this.style.borderColor = '#d1d5db';
                this.style.backgroundColor = 'white';
            }
        });
    }
    
    validateNameInput(firstNameInput);
    validateNameInput(lastNameInput);
    validateEmailInput(emailInput);
});
</script> 