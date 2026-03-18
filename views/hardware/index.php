<div class="page-header">
    <h1 class="page-title">Matériel</h1>
    <p class="page-description">Gestion de l'équipement informatique</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-desktop"></i>
        </div>
        <div class="stat-number"><?= $computersCount ?? 0 ?></div>
        <div class="stat-label">Ordinateurs</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-server"></i>
        </div>
        <div class="stat-number"><?= $serversCount ?? 0 ?></div>
        <div class="stat-label">Serveurs</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-print"></i>
        </div>
        <div class="stat-number"><?= $printersCount ?? 0 ?></div>
        <div class="stat-label">Imprimantes</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-database"></i>
        </div>
        <div class="stat-number"><?= $nasCount ?? 0 ?></div>
        <div class="stat-label">NAS</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-cube"></i>
        </div>
        <div class="stat-number"><?= $esxiCount ?? 0 ?></div>
        <div class="stat-label">Serveur de virtualisation</div>
    </div>
</div>

<div class="hardware-sections">
    <div class="section-card" onclick="location.href='?page=hardware&section=computers'">
        <div class="section-icon">
            <i class="fas fa-desktop"></i>
        </div>
        <h3 class="section-title">Ordinateurs</h3>
        <p class="section-description">Gestion des PC et ordinateurs portables</p>
        <div class="section-count"><?= $computersCount ?? 0 ?> ordinateurs</div>
        <div class="section-actions">
            <a href="?page=hardware&section=computers&action=create" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i>
                Ajouter
            </a>
        </div>
    </div>

    <div class="section-card" onclick="location.href='?page=hardware&section=servers'">
        <div class="section-icon">
            <i class="fas fa-server"></i>
        </div>
        <h3 class="section-title">Serveurs</h3>
        <p class="section-description">Gestion des serveurs et équipements réseau</p>
        <div class="section-count"><?= $serversCount ?? 0 ?> serveurs</div>
        <div class="section-actions">
            <a href="?page=servers&action=create" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i>
                Ajouter
            </a>
        </div>
    </div>

    <div class="section-card" onclick="location.href='?page=hardware&section=nas'">
        <div class="section-icon">
            <i class="fas fa-database"></i>
        </div>
        <h3 class="section-title">NAS</h3>
        <p class="section-description">Gestion des NAS (Synology, QNAP...)</p>
        <div class="section-count"><?= $nasCount ?? 0 ?> NAS</div>
        <div class="section-actions">
            <a href="?page=hardware&section=nas&action=create" class="btn btn-sm btn-primary" onclick="event.stopPropagation()">
                <i class="fas fa-plus"></i>
                Ajouter
            </a>
        </div>
    </div>

    <div class="section-card" onclick="location.href='?page=hardware&section=esxi'">
        <div class="section-icon">
            <i class="fas fa-cube"></i>
        </div>
        <h3 class="section-title">Serveur de virtualisation</h3>
        <p class="section-description">Hôtes VMware ESXi, Proxmox... et inventaire des VMs</p>
        <div class="section-count"><?= $esxiCount ?? 0 ?> hôtes</div>
        <div class="section-actions">
            <a href="?page=hardware&section=esxi&action=create" class="btn btn-sm btn-primary" onclick="event.stopPropagation()">
                <i class="fas fa-plus"></i>
                Ajouter
            </a>
        </div>
    </div>

    <div class="section-card" onclick="location.href='?page=hardware&section=printers'" style="cursor: not-allowed; opacity: 0.6;">
        <div class="section-icon">
            <i class="fas fa-print"></i>
        </div>
        <h3 class="section-title">Imprimantes</h3>
        <p class="section-description">Gestion des imprimantes et équipements d'impression</p>
        <div class="section-count"><?= $printersCount ?? 0 ?> imprimantes</div>
        <div class="section-badge">Bientôt disponible</div>
    </div>
</div>

<style>
.page-description {
    color: #6b7280;
    margin-top: 0.5rem;
    font-size: 1rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    border: 1px solid #e5e7eb;
    text-align: center;
}

.stat-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem auto;
}

.stat-icon i {
    font-size: 1.25rem;
    color: white;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
}

.hardware-sections {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.section-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    cursor: pointer;
    border: 1px solid #e5e7eb;
    position: relative;
}

.section-card:hover:not([style*="not-allowed"]) {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    border-color: #667eea;
}

.section-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
}

.section-icon i {
    font-size: 1.5rem;
    color: white;
}

.section-title {
    margin: 0 0 0.5rem 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #1f2937;
}

.section-description {
    margin: 0 0 1rem 0;
    color: #6b7280;
    font-size: 0.9rem;
    line-height: 1.4;
}

.section-count {
    font-size: 0.875rem;
    font-weight: 500;
    color: #667eea;
    margin-bottom: 1rem;
}

.section-actions {
    display: flex;
    gap: 0.5rem;
}

.section-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: #f59e0b;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .hardware-sections {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }
}
</style> 