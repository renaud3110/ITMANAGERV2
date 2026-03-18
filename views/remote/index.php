<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-desktop"></i> Remote — RustDesk</h1>
    </div>

    <p class="text-muted mb-4">Machines accessibles à distance via RustDesk (filtre : Tenant / Site en haut)</p>

    <!-- Recherche -->
    <div class="card shadow mb-4">
        <div class="card-body py-2">
            <div class="search-box remote-search">
                <input type="text" id="remoteSearch" placeholder="Rechercher..." class="form-control form-control-sm">
                <i class="fas fa-search"></i>
            </div>
        </div>
    </div>

    <?php $protocol = $rustdeskProtocol ?? 'rustdesk'; ?>

    <!-- Section Serveurs -->
    <?php if (!empty($servers)): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-server"></i> Serveurs</h6>
        </div>
        <div class="card-body">
            <div class="remote-grid" id="remoteServers">
                <?php foreach ($servers as $server): ?>
                <div class="remote-card" data-search="<?= htmlspecialchars(strtolower($server['name'] . ' ' . ($server['hostname'] ?? '') . ' ' . ($server['rustdesk_id'] ?? ''))) ?>" data-rustdesk-id="<?= htmlspecialchars($server['rustdesk_id']) ?>">
                    <a href="<?= $protocol ?>://<?= htmlspecialchars($server['rustdesk_id']) ?>" class="remote-card-link" title="Se connecter">
                        <div class="remote-card-icon server"><i class="fas fa-server"></i></div>
                        <div class="remote-card-body">
                            <div class="remote-card-name"><?= htmlspecialchars($server['name'] ?? $server['hostname'] ?? 'Sans nom') ?></div>
                            <div class="remote-card-id">ID <?= htmlspecialchars($server['rustdesk_id']) ?></div>
                            <span class="remote-card-status rustdesk-loading" data-rustdesk-id="<?= htmlspecialchars($server['rustdesk_id']) ?>"><i class="fas fa-circle-notch fa-spin"></i> Vérification…</span>
                        </div>
                        <div class="remote-card-arrow"><i class="fas fa-external-link-alt"></i></div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Section PCs -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-laptop"></i> PCs</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($computers)): ?>
            <div class="remote-grid" id="remoteComputers">
                <?php foreach ($computers as $computer): ?>
                <div class="remote-card" data-search="<?= htmlspecialchars(strtolower($computer['name'] . ' ' . ($computer['rustdesk_id'] ?? ''))) ?>" data-rustdesk-id="<?= htmlspecialchars($computer['rustdesk_id']) ?>">
                    <a href="<?= $protocol ?>://<?= htmlspecialchars($computer['rustdesk_id']) ?>" class="remote-card-link" title="Se connecter">
                        <div class="remote-card-icon pc"><i class="fas fa-desktop"></i></div>
                        <div class="remote-card-body">
                            <div class="remote-card-name"><?= htmlspecialchars($computer['name'] ?? 'Sans nom') ?></div>
                            <div class="remote-card-id">ID <?= htmlspecialchars($computer['rustdesk_id']) ?></div>
                            <span class="remote-card-status rustdesk-loading" data-rustdesk-id="<?= htmlspecialchars($computer['rustdesk_id']) ?>"><i class="fas fa-circle-notch fa-spin"></i> Vérification…</span>
                        </div>
                        <div class="remote-card-arrow"><i class="fas fa-external-link-alt"></i></div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-muted mb-0">Aucun PC avec RustDesk pour ce tenant/site.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($servers) && empty($computers)): ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Aucune machine avec RustDesk configuré pour la sélection actuelle (Tenant / Site).
    </div>
    <?php endif; ?>
</div>

<style>
.remote-search { position: relative; max-width: 400px; }
.remote-search input { padding-left: 2.25rem; }
.remote-search i { position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; }
.remote-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
}
.remote-card {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
    transition: background 0.2s, border-color 0.2s;
}
.remote-card:hover { border-color: #667eea; background: rgba(102, 126, 234, 0.05); }
.remote-card.hidden { display: none !important; }
.remote-card-link {
    display: flex;
    align-items: center;
    padding: 1rem;
    text-decoration: none;
    color: inherit;
}
.remote-card-link:hover { color: inherit; text-decoration: none; }
.remote-card-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin-right: 1rem;
    flex-shrink: 0;
}
.remote-card-icon.server { background: rgba(59, 130, 246, 0.15); color: #3b82f6; }
.remote-card-icon.pc { background: rgba(16, 185, 129, 0.15); color: #10b981; }
.remote-card.status-offline .remote-card-icon { background: rgba(220, 38, 38, 0.15); color: #dc2626; }
.remote-card-body { flex: 1; min-width: 0; }
.remote-card-name { font-weight: 600; color: #374151; }
.remote-card-id { font-size: 0.75rem; color: #6b7280; font-family: monospace; margin-top: 0.25rem; }
.remote-card-status { font-size: 0.7rem; display: inline-flex; align-items: center; gap: 0.35rem; margin-top: 0.35rem; }
.remote-card-status i { font-size: 0.5rem; }
.remote-card-status.rustdesk-loading { color: #9ca3af; }
.remote-card-status.rustdesk-online { color: #10b981; }
.remote-card-status.rustdesk-online i { color: #10b981; }
.remote-card-status.rustdesk-offline { color: #dc2626; }
.remote-card-status.rustdesk-offline i { color: #dc2626; }
.remote-card-arrow { color: #9ca3af; font-size: 0.875rem; }
.remote-card-link:hover .remote-card-arrow { color: #667eea; }
</style>

<script>
(function() {
    const search = document.getElementById('remoteSearch');
    if (search) {
        search.addEventListener('input', function() {
            const term = this.value.toLowerCase().trim();
            document.querySelectorAll('.remote-card').forEach(function(card) {
                const txt = (card.dataset.search || '');
                card.classList.toggle('hidden', term && txt.indexOf(term) === -1);
            });
        });
    }

    // Statut RustDesk (en ligne / hors ligne)
    var rustdeskIds = [];
    document.querySelectorAll('.remote-card-status[data-rustdesk-id]').forEach(function(el) {
        var id = el.getAttribute('data-rustdesk-id');
        if (id && rustdeskIds.indexOf(id) === -1) rustdeskIds.push(id);
    });
    if (rustdeskIds.length > 0) {
        var pathDir = window.location.pathname.replace(/\/[^/]*$/, '') || '/';
        var apiBase = pathDir.endsWith('/') ? pathDir : pathDir + '/';
        fetch(apiBase + 'api/rustdesk_status_batch.php?ids=' + rustdeskIds.join(','))
            .then(function(r) { return r.json(); })
            .then(function(data) {
                document.querySelectorAll('.remote-card[data-rustdesk-id]').forEach(function(card) {
                    var id = card.getAttribute('data-rustdesk-id');
                    var info = data[id] || { online: false };
                    card.classList.remove('status-online', 'status-offline');
                    card.classList.add(info.online ? 'status-online' : 'status-offline');
                });
                document.querySelectorAll('.remote-card-status[data-rustdesk-id]').forEach(function(el) {
                    var id = el.getAttribute('data-rustdesk-id');
                    var info = data[id] || { online: false };
                    el.classList.remove('rustdesk-loading', 'rustdesk-online', 'rustdesk-offline');
                    el.classList.add(info.online ? 'rustdesk-online' : 'rustdesk-offline');
                    el.innerHTML = info.online ? '<i class="fas fa-circle"></i> En ligne' : '<i class="fas fa-circle"></i> Hors ligne';
                });
            })
            .catch(function() {
                document.querySelectorAll('.remote-card[data-rustdesk-id]').forEach(function(card) {
                    card.classList.add('status-offline');
                });
                document.querySelectorAll('.remote-card-status[data-rustdesk-id]').forEach(function(el) {
                    el.classList.remove('rustdesk-loading');
                    el.classList.add('rustdesk-offline');
                    el.innerHTML = '<i class="fas fa-circle"></i> Hors ligne';
                });
            });
    }
})();
</script>
