<?php include 'views/partials/header.php'; ?>

<div class="container mt-4">
    <div class="page-header">
        <h1 class="page-title">Comptes Techniques</h1>
        <div class="page-actions">
            <a href="?page=accounts&action=technical" class="btn btn-primary">
                <i class="fas fa-plus"></i>
                Nouveau Compte Technique
            </a>
            <a href="?page=accounts&action=index" class="btn btn-secondary">
                <i class="fas fa-users"></i>
                Gestion des Personnes
            </a>
        </div>
    </div>

    <div class="context-info">
        <span class="context-item">
            <i class="fas fa-building"></i>
            <strong>Tenant:</strong>
            <?php if ($currentTenant === 'all'): ?>
                <span class="context-badge all">Tous les tenants</span>
            <?php else: ?>
                <span class="context-badge selected">Tenant <?= $currentTenant ?></span>
            <?php endif; ?>
        </span>
        
        <span class="context-separator">|</span>
        
        <span class="context-item">
            <i class="fas fa-map-marker-alt"></i>
            <strong>Site:</strong>
            <?php if ($currentSite === 'all'): ?>
                <span class="context-badge all">Tous les sites</span>
            <?php else: ?>
                <span class="context-badge selected">Site <?= $currentSite ?></span>
            <?php endif; ?>
        </span>
    </div>
    
    <?php if (isset($flash) && $flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <?= $flash['message'] ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">
                <i class="fas fa-cogs"></i>
                Comptes Techniques
            </h2>
            <div class="card-actions">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Rechercher un compte...">
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <?php if (empty($accounts)): ?>
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h3>Aucun compte technique trouvé</h3>
                    <p>Créez un compte technique pour gérer vos équipements et services.</p>
                    <a href="?page=accounts&action=technical" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Créer un compte technique
                    </a>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="table" id="technicalAccountsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Service</th>
                                <th>Nom d'utilisateur</th>
                                <th>Description</th>
                                <th>Tenant</th>
                                <th>Dernière Mise à Jour</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($accounts as $account): ?>
                                <tr>
                                    <td>
                                        <span class="id-badge"><?= $account['id'] ?></span>
                                    </td>
                                    <td>
                                        <div class="service-info">
                                            <?php if (!empty($account['service_logo'])): ?>
                                                <i class="<?= $account['service_logo'] ?>"></i>
                                            <?php endif; ?>
                                            <strong><?= htmlspecialchars($account['service_nom']) ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="username">
                                            <?= htmlspecialchars($account['username']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($account['description'])): ?>
                                            <?= htmlspecialchars($account['description']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Non défini</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="tenant-badge">
                                            <i class="fas fa-building"></i>
                                            Tenant <?= $account['tenant_id'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="date-info">
                                            <?= date('d/m/Y H:i', strtotime($account['updated_at'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-info show-password" 
                                                   data-login-id="<?= $account['id'] ?>" 
                                                   title="Afficher le mot de passe">
                                                <i class="fas fa-key"></i>
                                            </button>
                                            <a href="?page=accounts&action=editTechnical&id=<?= $account['id'] ?>" 
                                               class="btn btn-sm btn-secondary" 
                                               title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?page=accounts&action=deleteTechnical&id=<?= $account['id'] ?>" 
                                               class="btn btn-sm btn-danger" 
                                               title="Supprimer"
                                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce compte technique ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal pour afficher le mot de passe -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="passwordModalLabel">
                    <i class="fas fa-key"></i>
                    Mot de passe du compte
                </h5>
                <button type="button" class="close-modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="password-container">
                    <div class="account-info">
                        <strong>Nom d'utilisateur:</strong> <span id="modalUsername">-</span><br>
                        <strong>Service:</strong> <span id="modalService">-</span>
                    </div>
                    <div class="password-display">
                        <label for="passwordText">Mot de passe:</label>
                        <div class="password-field">
                            <input type="text" id="passwordText" value="********" readonly class="form-control">
                            <button id="copyPassword" class="btn btn-sm btn-outline-secondary" title="Copier">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fonction de recherche
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const table = document.getElementById('technicalAccountsTable');
            const rows = table.getElementsByTagName('tr');
            
            for (let i = 1; i < rows.length; i++) {
                const row = rows[i];
                const cells = row.getElementsByTagName('td');
                let found = false;
                
                for (let j = 0; j < cells.length - 1; j++) {
                    const cellText = cells[j].textContent.toLowerCase();
                    if (cellText.includes(searchTerm)) {
                        found = true;
                        break;
                    }
                }
                
                row.style.display = found ? '' : 'none';
            }
        });
    }
    
    // Gestion de l'affichage du mot de passe
    const passwordModal = document.getElementById('passwordModal');
    const passwordText = document.getElementById('passwordText');
    const modalUsername = document.getElementById('modalUsername');
    const modalService = document.getElementById('modalService');
    const copyPasswordBtn = document.getElementById('copyPassword');
    
    // Fonction pour afficher le modal
    function showModal() {
        passwordModal.style.display = 'block';
        passwordModal.classList.add('show');
        document.body.classList.add('modal-open');
    }
    
    // Fonction pour cacher le modal
    function hideModal() {
        passwordModal.style.display = 'none';
        passwordModal.classList.remove('show');
        document.body.classList.remove('modal-open');
    }
    
    // Gestionnaires pour fermer le modal
    document.querySelectorAll('.close-modal').forEach(btn => {
        btn.addEventListener('click', hideModal);
    });
    
    // Fermer en cliquant à l'extérieur
    passwordModal.addEventListener('click', function(e) {
        if (e.target === passwordModal) {
            hideModal();
        }
    });
    
    // Fermer avec la touche Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && passwordModal.classList.contains('show')) {
            hideModal();
        }
    });
    
    document.querySelectorAll('.show-password').forEach(button => {
        button.addEventListener('click', function() {
            const loginId = this.getAttribute('data-login-id');
            
            // Changer l'icône pour indiquer le chargement
            const originalHtml = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            this.disabled = true;
            
            fetch(`?page=accounts&action=showPassword&login_id=${loginId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.password) {
                        passwordText.value = data.password;
                        modalUsername.textContent = data.username || 'Non défini';
                        modalService.textContent = data.service || 'Non défini';
                        showModal();
                    } else if (data.error) {
                        alert('Erreur: ' + data.error);
                    } else {
                        alert('Impossible de récupérer le mot de passe.');
                    }
                })
                .catch(error => {
                    console.error('Erreur:', error);
                    alert('Une erreur est survenue lors de la récupération du mot de passe: ' + error.message);
                })
                .finally(() => {
                    // Restaurer le bouton
                    this.innerHTML = originalHtml;
                    this.disabled = false;
                });
        });
    });
    
    // Copier le mot de passe dans le presse-papier
    copyPasswordBtn.addEventListener('click', function() {
        const password = passwordText.value;
        
        if (navigator.clipboard && window.isSecureContext) {
            // Méthode moderne
            navigator.clipboard.writeText(password)
                .then(() => {
                    copyPasswordBtn.innerHTML = '<i class="fas fa-check"></i>';
                    copyPasswordBtn.classList.add('btn-success');
                    copyPasswordBtn.classList.remove('btn-outline-secondary');
                    setTimeout(() => {
                        copyPasswordBtn.innerHTML = '<i class="fas fa-copy"></i>';
                        copyPasswordBtn.classList.remove('btn-success');
                        copyPasswordBtn.classList.add('btn-outline-secondary');
                    }, 2000);
                })
                .catch(err => {
                    console.error('Erreur lors de la copie:', err);
                    fallbackCopyText(password);
                });
        } else {
            // Méthode de fallback
            fallbackCopyText(password);
        }
    });
    
    // Fonction de fallback pour la copie
    function fallbackCopyText(text) {
        const textArea = document.createElement('textarea');
        textArea.value = text;
        textArea.style.position = 'fixed';
        textArea.style.left = '-999999px';
        textArea.style.top = '-999999px';
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        
        try {
            document.execCommand('copy');
            copyPasswordBtn.innerHTML = '<i class="fas fa-check"></i>';
            copyPasswordBtn.classList.add('btn-success');
            copyPasswordBtn.classList.remove('btn-outline-secondary');
            setTimeout(() => {
                copyPasswordBtn.innerHTML = '<i class="fas fa-copy"></i>';
                copyPasswordBtn.classList.remove('btn-success');
                copyPasswordBtn.classList.add('btn-outline-secondary');
            }, 2000);
        } catch (err) {
            console.error('Fallback: Erreur lors de la copie', err);
            alert('Impossible de copier le mot de passe. Copiez-le manuellement.');
        }
        
        document.body.removeChild(textArea);
    }
});
</script>

<style>
.service-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.service-info i {
    font-size: 1.25rem;
}

.password-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.account-info {
    background: #f8fafc;
    padding: 1rem;
    border-radius: 0.5rem;
    border-left: 4px solid #6366f1;
    font-size: 0.9rem;
}

.account-info strong {
    color: #374151;
}

.password-display label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
}

.password-field {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

#passwordText {
    flex-grow: 1;
    font-family: 'Courier New', monospace;
    font-size: 1rem;
    font-weight: 600;
    background: #f8fafc;
    border: 2px solid #e5e7eb;
    padding: 0.75rem;
    border-radius: 0.375rem;
    color: #1f2937;
}

#passwordText:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

/* Styles pour le modal */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1050;
    display: none;
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-dialog {
    position: relative;
    width: auto;
    max-width: 500px;
    margin: 1.75rem;
}

.modal-dialog-centered {
    display: flex;
    align-items: center;
    min-height: calc(100% - 3.5rem);
}

.modal-content {
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid rgba(0, 0, 0, 0.2);
    border-radius: 0.5rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    outline: 0;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
}

.modal-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
}

.modal-title i {
    color: #6366f1;
    margin-right: 0.5rem;
}

.close-modal {
    background: none;
    border: none;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1;
    color: #6b7280;
    cursor: pointer;
    padding: 0;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.25rem;
    transition: all 0.2s ease;
}

.close-modal:hover {
    color: #1f2937;
    background-color: #f3f4f6;
}

.modal-body {
    position: relative;
    flex: 1 1 auto;
    padding: 1.5rem;
}

.modal-footer {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    border-bottom-left-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
    background-color: #f9fafb;
}

.modal-open {
    overflow: hidden;
}

/* Animation du modal */
.modal {
    transition: opacity 0.3s ease;
    opacity: 0;
}

.modal.show {
    opacity: 1;
}

.modal-content {
    transform: scale(0.9);
    transition: transform 0.3s ease;
}

.modal.show .modal-content {
    transform: scale(1);
}

/* Boutons d'action améliorés */
.btn-group .btn {
    transition: all 0.2s ease;
}

.btn-group .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.show-password:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Responsive */
@media (max-width: 576px) {
    .modal-dialog {
        max-width: calc(100% - 2rem);
        margin: 1rem;
    }
    
    .modal-header,
    .modal-body,
    .modal-footer {
        padding: 1rem;
    }
    
    .password-field {
        flex-direction: column;
        align-items: stretch;
    }
    
    #copyPassword {
        align-self: flex-end;
        margin-top: 0.5rem;
    }
}
</style>

<?php include 'views/partials/footer.php'; ?>