<?php
$pageTitle = 'Gestion des Services';
?>

<div class="page-header">
    <div>
        <h1>Services de Connexion</h1>
        <p class="page-subtitle">Gérez les services et leurs logos</p>
    </div>
    <div class="page-actions">
        <a href="?page=services&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            Nouveau Service
        </a>
    </div>
</div>



<div class="card">
    <div class="card-header">
        <h3>Liste des Services</h3>
        <div class="card-stats">
            <span class="stat-item">
                <span class="stat-number"><?= count($services) ?></span>
                <span class="stat-label">Services</span>
            </span>
        </div>
    </div>
    
    <div class="card-body">
        <?php if (empty($services)): ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-cogs"></i>
                </div>
                <h3>Aucun service configuré</h3>
                <p>Commencez par ajouter votre premier service.</p>
                <a href="?page=services&action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Créer le premier service
                </a>
            </div>
        <?php else: ?>
            <div class="services-grid">
                <?php foreach ($services as $service): ?>
                    <div class="service-card">
                        <div class="service-header">
                            <div class="service-info">
                                <div class="service-title">
                                    <?php if (!empty($service['logo'])): ?>
                                        <div class="service-logo">
                                            <i class="<?= htmlspecialchars($service['logo']) ?>"></i>
                                        </div>
                                    <?php else: ?>
                                        <div class="service-logo service-logo-default">
                                            <i class="fas fa-cog"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="service-text">
                                        <h4><?= htmlspecialchars($service['nom']) ?></h4>
                                        <?php if ($service['description']): ?>
                                            <p><?= htmlspecialchars($service['description']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="service-actions">
                                <a href="?page=services&action=edit&id=<?= $service['id'] ?>" 
                                   class="btn btn-sm btn-primary" title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?page=services&action=delete&id=<?= $service['id'] ?>" 
                                   class="btn btn-sm btn-danger" title="Supprimer"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce service ?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                        <div class="service-stats">
                            <div class="stat-item">
                                <span class="stat-number"><?= $service['login_count'] ?></span>
                                <span class="stat-label">Comptes</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-date">
                                    Créé le <?= date('d/m/Y', strtotime($service['created_at'])) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.card-stats {
    display: flex;
    gap: 2rem;
}

.stat-item {
    text-align: center;
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

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
}

.service-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.2s ease;
}

.service-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.service-header {
    background: white;
    padding: 1.5rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.service-title {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.service-logo {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.service-logo-default {
    background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%);
}

.service-text {
    flex: 1;
}

.service-text h4 {
    margin: 0 0 0.5rem 0;
    color: #1f2937;
    font-size: 1.25rem;
}

.service-text p {
    margin: 0;
    color: #6b7280;
    font-size: 0.875rem;
}

.service-actions {
    display: flex;
    gap: 0.5rem;
}

.service-stats {
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8fafc;
}

.service-stats .stat-item {
    background: none;
    padding: 0;
    min-width: auto;
}

.service-stats .stat-number {
    font-size: 1.5rem;
}

.stat-date {
    font-size: 0.75rem;
    color: #9ca3af;
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

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-danger:hover {
    background: #dc2626;
}

.btn-sm {
    padding: 0.5rem;
    font-size: 0.875rem;
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}
</style>

 