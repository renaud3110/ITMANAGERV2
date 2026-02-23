<div class="page-header">
    <h1 class="page-title">
        <?= htmlspecialchars($person['prenom'] . ' ' . $person['nom']) ?>
        <span class="person-id">(ID: <?= $person['id'] ?>)</span>
    </h1>
    <div class="page-actions">
        <a href="?page=accounts&action=edit&id=<?= $person['id'] ?>" class="btn btn-secondary">
            <i class="fas fa-edit"></i>
            Modifier
        </a>
        <a href="?page=accounts" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i>
            Retour à la liste
        </a>
    </div>
</div>

<!-- Informations de la personne -->
<div class="person-info-card">
    <div class="person-avatar">
        <i class="fas fa-user"></i>
    </div>
    <div class="person-details">
        <h2><?= htmlspecialchars($person['prenom'] . ' ' . $person['nom']) ?></h2>
        <div class="person-meta">
            <?php if (!empty($person['email'])): ?>
                <div class="meta-item">
                    <i class="fas fa-envelope"></i>
                    <a href="mailto:<?= htmlspecialchars($person['email']) ?>">
                        <?= htmlspecialchars($person['email']) ?>
                    </a>
                </div>
            <?php endif; ?>
            <div class="meta-item">
                <i class="fas fa-building"></i>
                <span>
                    <?php if ($personTenant): ?>
                        <?= htmlspecialchars($personTenant['name']) ?>
                        <?php if (!empty($personTenant['domain'])): ?>
                            (<?= htmlspecialchars($personTenant['domain']) ?>)
                        <?php endif; ?>
                    <?php else: ?>
                        Tenant <?= $person['tenant_id'] ?>
                    <?php endif; ?>
                </span>
            </div>
            <div class="meta-item">
                <i class="fas fa-calendar"></i>
                <span>Créé le <?= date('d/m/Y', strtotime($person['created_at'])) ?></span>
            </div>
        </div>
    </div>
    <div class="person-stats">
        <div class="stat-item">
            <div class="stat-number"><?= count($logins) ?></div>
            <div class="stat-label">Comptes</div>
        </div>
    </div>
</div>

<!-- Comptes de connexion -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">
            <i class="fas fa-user-circle"></i>
            Comptes de connexion
        </h2>
        <div class="card-actions">
            <button class="btn btn-primary" onclick="showAddLoginModal()">
                <i class="fas fa-plus"></i>
                Ajouter un compte
            </button>
        </div>
    </div>
    
    <div class="card-body">
        <?php if (empty($logins)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h3>Aucun compte configuré</h3>
                <p>Cette personne n'a pas encore de comptes de connexion.</p>
                <button class="btn btn-primary" onclick="showAddLoginModal()">
                    <i class="fas fa-plus"></i>
                    Ajouter le premier compte
                </button>
            </div>
        <?php else: ?>
            <div class="logins-grid">
                <?php foreach ($logins as $login): ?>
                    <div class="login-card">
                        <div class="login-header">
                            <div class="service-info">
                                <div class="service-title">
                                    <?php if (!empty($login['service_logo'])): ?>
                                        <div class="service-logo">
                                            <i class="<?= htmlspecialchars($login['service_logo']) ?>"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="service-text">
                                        <h4><?= htmlspecialchars($login['service_nom']) ?></h4>
                                        <p><?= htmlspecialchars($login['service_description']) ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="login-actions">
                                <button class="btn btn-sm btn-secondary" 
                                        onclick="showPassword(<?= $login['id'] ?>)"
                                        title="Voir le mot de passe">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="login-details">
                            <div class="detail-item">
                                <label>Nom d'utilisateur</label>
                                <div class="detail-value">
                                    <span class="username"><?= htmlspecialchars($login['username']) ?></span>
                                    <button class="copy-btn" onclick="copyToClipboard('<?= htmlspecialchars($login['username']) ?>')" title="Copier">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="detail-item">
                                <label>Mot de passe</label>
                                <div class="detail-value">
                                    <span class="password" id="password-<?= $login['id'] ?>">••••••••</span>
                                    <button class="copy-btn" id="copy-password-<?= $login['id'] ?>" onclick="copyPassword(<?= $login['id'] ?>)" title="Copier le mot de passe" style="display: none;">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="detail-item">
                                <label>Dernière mise à jour</label>
                                <div class="detail-value">
                                    <span class="date"><?= date('d/m/Y H:i', strtotime($login['updated_at'])) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal d'ajout de compte -->
<div id="addLoginModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Ajouter un compte</h3>
            <button class="modal-close" onclick="hideAddLoginModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="?page=accounts&action=addLogin" class="modal-form">
            <input type="hidden" name="person_id" value="<?= $person['id'] ?>">
            <input type="hidden" name="tenant_id" value="<?= $person['tenant_id'] ?>">
            
            <div class="form-group">
                <label for="service_id">Service *</label>
                <select id="service_id" name="service_id" class="form-control service-select" required>
                    <option value="">Sélectionnez un service</option>
                    <?php foreach ($services as $service): ?>
                        <option value="<?= $service['id'] ?>" data-logo="<?= htmlspecialchars($service['logo'] ?? '') ?>">
                            <?= htmlspecialchars($service['nom']) ?>
                            <?php if ($service['description']): ?>
                                - <?= htmlspecialchars($service['description']) ?>
                            <?php endif; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="username">Nom d'utilisateur *</label>
                <input type="text" 
                       id="username" 
                       name="username" 
                       class="form-control" 
                       required 
                       placeholder="Entrez le nom d'utilisateur">
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="password-input">
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           placeholder="Entrez le mot de passe">
                    <button type="button" class="password-toggle" onclick="togglePasswordVisibility()">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <small class="form-help">Le mot de passe sera chiffré automatiquement</small>
            </div>
            
            <div class="modal-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Ajouter le compte
                </button>
                <button type="button" class="btn btn-secondary" onclick="hideAddLoginModal()">
                    <i class="fas fa-times"></i>
                    Annuler
                </button>
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

.person-id {
    font-size: 1rem;
    color: #6b7280;
    font-weight: normal;
}

.page-actions {
    display: flex;
    gap: 1rem;
}

.person-info-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    padding: 2rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 2rem;
}

.person-avatar {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    flex-shrink: 0;
}

.person-details {
    flex: 1;
}

.person-details h2 {
    margin: 0 0 1rem 0;
    color: #1f2937;
    font-size: 1.5rem;
}

.person-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6b7280;
    font-size: 0.875rem;
}

.meta-item i {
    color: #667eea;
}

.meta-item a {
    color: #667eea;
    text-decoration: none;
}

.meta-item a:hover {
    color: #4f46e5;
}

.person-stats {
    text-align: center;
}

.stat-item {
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
    min-width: 80px;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    line-height: 1;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}

.empty-icon i {
    font-size: 2rem;
    color: #9ca3af;
}

.logins-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
}

.login-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}

.login-header {
    background: white;
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.service-info h4 {
    margin: 0 0 0.5rem 0;
    color: #1f2937;
    font-size: 1.125rem;
}

.service-info p {
    margin: 0;
    color: #6b7280;
    font-size: 0.875rem;
}

.service-title {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.service-logo {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.service-text {
    flex: 1;
}

.login-details {
    padding: 1.5rem;
}

.detail-item {
    margin-bottom: 1rem;
}

.detail-item:last-child {
    margin-bottom: 0;
}

.detail-item label {
    display: block;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.25rem;
}

.detail-value {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.username, .password, .date {
    font-family: 'Courier New', monospace;
    background: white;
    padding: 0.5rem;
    border-radius: 4px;
    border: 1px solid #e5e7eb;
    flex: 1;
    font-size: 0.875rem;
}

.copy-btn {
    background: #667eea;
    color: white;
    border: none;
    padding: 0.5rem;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.copy-btn:hover {
    background: #4f46e5;
}

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #1f2937;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.25rem;
    color: #6b7280;
    cursor: pointer;
    padding: 0.25rem;
}

.modal-close:hover {
    color: #374151;
}

.modal-form {
    padding: 1.5rem;
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

.password-input {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
}

.password-toggle:hover {
    color: #374151;
}

.form-help {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.modal-actions {
    display: flex;
    gap: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
    margin-top: 1rem;
}

/* Responsive design */
@media (max-width: 768px) {
    .page-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
    
    .person-info-card {
        flex-direction: column;
        text-align: center;
        gap: 1rem;
    }
    
    .person-meta {
        justify-content: center;
    }
    
    .logins-grid {
        grid-template-columns: 1fr;
    }
    
    .modal-actions {
        flex-direction: column;
    }
}
</style>

<script>
let currentPasswordId = null;

function showAddLoginModal() {
    document.getElementById('addLoginModal').classList.add('show');
}

function hideAddLoginModal() {
    document.getElementById('addLoginModal').classList.remove('show');
    // Reset form
    document.querySelector('.modal-form').reset();
}

function togglePasswordVisibility() {
    const passwordInput = document.getElementById('password');
    const toggleBtn = document.querySelector('.password-toggle i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleBtn.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        toggleBtn.className = 'fas fa-eye';
    }
}

async function showPassword(loginId) {
    try {
        const response = await fetch(`?page=accounts&action=showPassword&login_id=${loginId}`);
        const data = await response.json();
        
        if (data.password) {
            const passwordElement = document.getElementById(`password-${loginId}`);
            const copyButton = document.getElementById(`copy-password-${loginId}`);
            
            passwordElement.textContent = data.password;
            copyButton.style.display = 'block';
            currentPasswordId = loginId;
            
            // Masquer le mot de passe après 10 secondes
            setTimeout(() => {
                passwordElement.textContent = '••••••••';
                copyButton.style.display = 'none';
            }, 10000);
        } else {
            alert('Erreur: ' + (data.error || 'Impossible de récupérer le mot de passe'));
        }
    } catch (error) {
        alert('Erreur de communication avec le serveur');
    }
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Feedback visuel
        const btn = event.target.closest('.copy-btn');
        const originalIcon = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        btn.style.background = '#10b981';
        
        setTimeout(() => {
            btn.innerHTML = originalIcon;
            btn.style.background = '#667eea';
        }, 1000);
    });
}

function copyPassword(loginId) {
    const passwordElement = document.getElementById(`password-${loginId}`);
    const password = passwordElement.textContent;
    
    if (password !== '••••••••') {
        copyToClipboard(password);
    }
}

// Fermer le modal en cliquant à l'extérieur
document.getElementById('addLoginModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideAddLoginModal();
    }
});

// Fermer le modal avec Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideAddLoginModal();
    }
});
</script> 