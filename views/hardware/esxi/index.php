<div class="page-header esxi-page-header">
    <div>
        <h1 class="page-title"><i class="fas fa-cube text-primary"></i> Serveurs de virtualisation</h1>
        <p class="page-description">Hôtes VMware ESXi, Proxmox... et inventaire des machines virtuelles</p>
    </div>
    <div class="page-header-actions">
        <a href="?page=hardware" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
        <a href="?page=hardware&section=esxi&action=create" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Ajouter un hôte
        </a>
    </div>
</div>

<!-- Stats -->
<div class="stats-grid esxi-stats">
    <div class="stat-card">
        <div class="stat-icon">
            <i class="fas fa-server"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number"><?= $totalEsxi ?? 0 ?></div>
            <div class="stat-label">Tous les hôtes</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon esxi-vms">
            <i class="fas fa-cube"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number"><?= $esxiWithVms ?? 0 ?></div>
            <div class="stat-label">Avec des VMs</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon esxi-ok">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number"><?= $esxiDiscoveryOk ?? 0 ?></div>
            <div class="stat-label">Découverte OK</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon esxi-config">
            <i class="fas fa-key"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number"><?= $esxiNoCredentials ?? 0 ?></div>
            <div class="stat-label">Sans identifiants</div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Liste des hôtes de virtualisation</h6>
        <?php if (!empty($esxiList)): ?>
        <div class="search-box esxi-search">
            <input type="text" id="searchInput" placeholder="Rechercher..." class="form-control form-control-sm">
            <i class="fas fa-search"></i>
        </div>
        <?php endif; ?>
    </div>
        <div class="card-body">
            <?php if (!empty($esxiList)): ?>
                <div class="table-responsive esxi-table-wrapper">
                    <table class="table table-bordered table-hover" id="esxiTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Hôte</th>
                                <th>Site</th>
                                <th>Dernière découverte</th>
                                <th>VMs</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($esxiList as $esxi): ?>
                                <?php
                                $lastDisc = $esxi['last_discovery'] ?? null;
                                $vms = $esxi['vms'] ?? [];
                                ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($esxi['name'] ?? 'Sans nom') ?></strong>
                                        <?php $ht = $esxi['hypervisor_type'] ?? 'esxi'; ?>
                                        <span class="badge badge-<?= $ht === 'proxmox' ? 'info' : 'primary' ?>" style="font-size:0.7rem"><?= $ht === 'proxmox' ? 'Proxmox' : 'ESXi' ?></span>
                                        <?php if (!empty($esxi['description'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($esxi['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($esxi['host'] ?? '') ?>:<?= (int)($esxi['port'] ?? 443) ?></td>
                                    <td><?= htmlspecialchars($esxi['site_name'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($lastDisc && $lastDisc['discovered_at']): ?>
                                            <?= date('d/m/Y H:i', strtotime($lastDisc['discovered_at'])) ?>
                                            <?php if ($lastDisc['error_message']): ?>
                                                <br><small class="text-danger"><?= htmlspecialchars($lastDisc['error_message']) ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Jamais</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($vms)): ?>
                                            <?= count($vms) ?> VM(s)
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary btn-expand-esxi" data-esxi-id="<?= (int)$esxi['id'] ?>" data-vms='<?= htmlspecialchars(json_encode($vms), ENT_QUOTES, 'UTF-8') ?>' title="Voir les VMs">
                                                <i class="fas fa-chevron-down"></i> VMs
                                            </button>
                                            <button type="button" class="btn btn-info discover-esxi-btn"
                                                    data-esxi-id="<?= (int)$esxi['id'] ?>"
                                                    data-esxi-name="<?= htmlspecialchars($esxi['name']) ?>"
                                                    data-has-credentials="<?= !empty($esxi['has_credentials']) ? '1' : '0' ?>"
                                                    data-site-id="<?= (int)($esxi['site_id'] ?? 0) ?>"
                                                    title="<?= !empty($esxi['has_credentials']) ? 'Lancer la découverte (agent sur le site)' : 'Enregistrez les identifiants dans la fiche du serveur' ?>">
                                                <i class="fas fa-search"></i> Découvrir
                                            </button>
                                            <a href="?page=hardware&section=esxi&action=edit&id=<?= $esxi['id'] ?>" class="btn btn-warning" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?page=hardware&section=esxi&action=delete&id=<?= $esxi['id'] ?>" class="btn btn-danger" title="Supprimer"
                                               onclick="return confirm('Supprimer ce serveur de virtualisation ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php if (!empty($vms)): ?>
                                <?php
                                $hostsData = [];
                                $dsData = [];
                                if ($lastDisc) {
                                    $hostsData = !empty($lastDisc['hosts_json']) ? json_decode($lastDisc['hosts_json'], true) : [];
                                    $dsData = !empty($lastDisc['datastores_json']) ? json_decode($lastDisc['datastores_json'], true) : [];
                                }
                                ?>
                                <tr class="esxi-vms-row" id="esxi-vms-<?= $esxi['id'] ?>" style="display:none">
                                    <td colspan="6" class="p-0">
                                        <div class="p-3 bg-light">
                                            <?php if (!empty($hostsData)): ?>
                                            <div class="mb-3">
                                                <h6 class="font-weight-bold mb-2"><i class="fas fa-server"></i> Host(s)</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered mb-0">
                                                        <thead><tr><th>Nom</th><th>Modèle</th><th>CPU</th><th>RAM totale</th><th>RAM libre</th></tr></thead>
                                                        <tbody>
                                                        <?php foreach ($hostsData as $hd): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($hd['name'] ?? '-') ?></td>
                                                            <td><?= htmlspecialchars($hd['model'] ?? '-') ?></td>
                                                            <td><?= htmlspecialchars($hd['cpu_model'] ?? '-') ?><?= isset($hd['cpu_cores']) ? ' (' . (int)$hd['cpu_cores'] . ' cores' . (isset($hd['cpu_mhz']) ? ', ' . (int)$hd['cpu_mhz'] . ' MHz' : '') . ')' : (isset($hd['cpu_mhz']) ? ' ' . (int)$hd['cpu_mhz'] . ' MHz' : '') ?></td>
                                                            <td><?= isset($hd['ram_total_mb']) ? round((int)$hd['ram_total_mb'] / 1024, 1) . ' Go' : (isset($hd['ram_mb']) ? round((int)$hd['ram_mb'] / 1024, 1) . ' Go' : '-') ?></td>
                                                            <td><?= isset($hd['ram_free_mb']) ? round((int)$hd['ram_free_mb'] / 1024, 1) . ' Go' : '-' ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            <?php if (!empty($dsData)): ?>
                                            <div class="mb-3">
                                                <h6 class="font-weight-bold mb-2"><i class="fas fa-database"></i> Datastores</h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered mb-0">
                                                        <thead><tr><th>Nom</th><th>Capacité totale</th><th>Espace libre</th></tr></thead>
                                                        <tbody>
                                                        <?php foreach ($dsData as $dd): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($dd['name'] ?? '-') ?></td>
                                                            <td><?= isset($dd['capacity_gb']) ? number_format((int)$dd['capacity_gb'], 1) . ' Go' : (isset($dd['capacity']) ? number_format((int)$dd['capacity'] / 1024 / 1024 / 1024, 1) . ' Go' : '-') ?></td>
                                                            <td><?= isset($dd['free_gb']) ? number_format((int)$dd['free_gb'], 1) . ' Go' : (isset($dd['free']) ? number_format((int)$dd['free'] / 1024 / 1024 / 1024, 1) . ' Go' : '-') ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            <h6 class="font-weight-bold mb-2"><i class="fas fa-cube"></i> Machines virtuelles</h6>
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead><tr><th>VM</th><th>État</th><th>OS invité</th><th>vCPU</th><th>RAM</th><th>Dém. auto</th><th>Disques</th><th>Lien serveur</th></tr></thead>
                                                <tbody>
                                                <?php foreach ($vms as $vm): ?>
                                                <?php
                                                $disks = !empty($vm['disks_json']) ? json_decode($vm['disks_json'], true) : [];
                                                $disksSummary = [];
                                                foreach ($disks as $d) {
                                                    $cap = isset($d['capacity_gb']) ? round($d['capacity_gb'], 1) . ' Go' : '';
                                                    $label = $d['label'] ?? $d['filename'] ?? 'Disque';
                                                    $ds = $d['datastore'] ?? '';
                                                    $disksSummary[] = trim($label . ($cap ? ' (' . $cap . ')' : '') . ($ds ? ' [' . $ds . ']' : ''));
                                                }
                                                ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($vm['vm_name']) ?></td>
                                                    <td><span class="badge badge-<?= ($vm['power_state'] ?? '') === 'poweredOn' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($vm['power_state'] ?? '-') ?></span></td>
                                                    <td><?= htmlspecialchars($vm['guest_os'] ?? '-') ?></td>
                                                    <td><?= (int)($vm['cpu_count'] ?? 0) ?></td>
                                                    <td><?= isset($vm['ram_mb']) ? round($vm['ram_mb']/1024, 1) . ' Go' : '-' ?></td>
                                                    <td><?= !empty($vm['auto_start']) ? '<i class="fas fa-play text-success" title="Démarrage automatique"></i>' : '-' ?></td>
                                                    <td><?= !empty($disksSummary) ? implode('<br>', array_map(function($s) { return htmlspecialchars($s); }, $disksSummary)) : '-' ?></td>
                                                    <td>
                                                        <select class="form-control form-control-sm link-vm-server" data-vm-id="<?= (int)$vm['id'] ?>" style="max-width:200px">
                                                            <option value="">Aucun</option>
                                                            <?php foreach ($servers ?? [] as $srv): ?>
                                                                <option value="<?= $srv['id'] ?>" <?= ($vm['server_id'] ?? null) == $srv['id'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($srv['name'] ?? $srv['hostname'] ?? 'Serveur #'.$srv['id']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                        <?php if (!empty($vm['server_name'])): ?>
                                                            <small class="text-success d-block"><i class="fas fa-link"></i> <?= htmlspecialchars($vm['server_name']) ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fas fa-cube"></i></div>
                    <h4>Aucun serveur de virtualisation</h4>
                    <p class="text-muted">Ajoutez un hôte ESXi ou Proxmox pour lancer la découverte des VMs par l'agent sur site.</p>
                    <a href="?page=hardware&section=esxi&action=create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Ajouter un hôte
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Découverte -->
<div id="discoverEsxiModal" class="modal" style="display:none">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Découvrir le serveur de virtualisation</h5>
                <button type="button" class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">La découverte sera effectuée par l'agent sur le site (hosts, VMs, datastores). Compatible ESXi 6.5 et 7.0.</p>
                <input type="hidden" id="discoverEsxiId" value="">
                <div id="discoverEsxiResult" class="mt-3" style="display:none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="btnDiscoverEsxi"><i class="fas fa-search"></i> Lancer la découverte</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Page header */
.esxi-page-header { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; }
.esxi-page-header .page-title { font-size: 1.75rem; font-weight: 600; color: #1f2937; display: flex; align-items: center; gap: 0.5rem; }
.esxi-page-header .page-description { color: #6b7280; margin: 0.5rem 0 0 0; font-size: 0.95rem; }
.page-header-actions { display: flex; gap: 0.5rem; align-items: center; }

/* Stats */
.esxi-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 2rem; }
.esxi-stats .stat-card { background: white; padding: 1.25rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border: 1px solid #e5e7eb; display: flex; align-items: center; gap: 1rem; transition: transform 0.2s, box-shadow 0.2s; }
.esxi-stats .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 20px rgba(0,0,0,0.12); }
.esxi-stats .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); flex-shrink: 0; }
.esxi-stats .stat-icon.esxi-vms { background: linear-gradient(135deg, #0ea5e9 0%, #0369a1 100%); }
.esxi-stats .stat-icon.esxi-ok { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); }
.esxi-stats .stat-icon.esxi-config { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.esxi-stats .stat-content { flex: 1; min-width: 0; }
.esxi-stats .stat-number { font-size: 1.75rem; font-weight: 700; color: #1f2937; }
.esxi-stats .stat-label { font-size: 0.8125rem; color: #6b7280; margin-top: 0.125rem; }

/* Search box */
.esxi-search { position: relative; width: 240px; }
.esxi-search input { padding-right: 2.25rem; border-radius: 8px; border: 1px solid #d1d5db; }
.esxi-search i { position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; font-size: 0.875rem; }

/* Empty state */
.empty-state { text-align: center; padding: 3rem 2rem; }
.empty-state-icon { width: 80px; height: 80px; margin: 0 auto 1.5rem; background: linear-gradient(135deg, rgba(102,126,234,0.1) 0%, rgba(118,75,162,0.1) 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
.empty-state-icon i { font-size: 2rem; color: #667eea; }
.empty-state h4 { margin-bottom: 0.5rem; color: #374151; font-weight: 600; }
.empty-state p { margin-bottom: 1.5rem; max-width: 400px; margin-left: auto; margin-right: auto; }

/* VM expand section */
.esxi-vms-row td { background: #f8fafc !important; border-top: none !important; vertical-align: top; }
.esxi-vms-row .p-3 { padding: 1.25rem 1.5rem !important; }
.esxi-vms-row .bg-light { background: #f1f5f9 !important; border-radius: 8px; margin: 0 0.5rem 0.5rem; }
.esxi-vms-row h6 { color: #475569; margin-bottom: 0.75rem; font-size: 0.9375rem; }
.esxi-vms-row .table-sm { font-size: 0.875rem; }
.esxi-vms-row .badge-success { background: #16a34a; }

/* Modal */
#discoverEsxiModal.modal { position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1050; display: flex; align-items: center; justify-content: center; padding: 1rem; }
#discoverEsxiModal .modal-dialog { max-width: 480px; width: 100%; }
#discoverEsxiModal .modal-content { background: #fff; border-radius: 12px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); overflow: hidden; }
#discoverEsxiModal .modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; background: #f9fafb; }
#discoverEsxiModal .modal-title { font-size: 1.125rem; font-weight: 600; color: #1f2937; margin: 0; }
#discoverEsxiModal .close-modal { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280; line-height: 1; padding: 0.25rem; transition: color 0.2s; }
#discoverEsxiModal .close-modal:hover { color: #374151; }
#discoverEsxiModal .modal-body { padding: 1.5rem; }
#discoverEsxiModal .modal-footer { padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb; display: flex; gap: 0.5rem; justify-content: flex-end; }

@media (max-width: 768px) {
    .esxi-page-header { flex-direction: column; }
    .esxi-stats { grid-template-columns: repeat(2, 1fr); }
    .esxi-table-wrapper .dataTables_wrapper .row:last-child { flex-direction: column; align-items: center; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Recherche en temps réel
    var searchInput = document.getElementById('searchInput');
    var table = document.getElementById('esxiTable');
    if (searchInput && table) {
        searchInput.addEventListener('keyup', function() {
            var q = this.value.toLowerCase().trim();
            var mainRows = table.querySelectorAll('tbody tr:not(.esxi-vms-row)');
            mainRows.forEach(function(mainTr) {
                var match = !q || mainTr.textContent.toLowerCase().indexOf(q) !== -1;
                mainTr.style.display = match ? '' : 'none';
                var next = mainTr.nextElementSibling;
                if (next && next.classList.contains('esxi-vms-row') && next.id === 'esxi-vms-' + (mainTr.dataset.esxiId || '')) {
                    next.style.display = (match && next._expanded) ? 'table-row' : 'none';
                }
            });
        });
    }
    if (table) {
        table.querySelectorAll('tbody tr:not(.esxi-vms-row)').forEach(function(tr) {
            var btn = tr.querySelector('.btn-expand-esxi');
            if (btn) tr.dataset.esxiId = btn.dataset.esxiId;
        });
    }

    var expandId = new URLSearchParams(window.location.search).get('expand');
    if (expandId) {
        var row = document.getElementById('esxi-vms-' + expandId);
        var btn = document.querySelector('.btn-expand-esxi[data-esxi-id="' + expandId + '"]');
        if (row && btn) {
            row.style.display = 'table-row';
            row._expanded = true;
            if (btn.querySelector('i')) btn.querySelector('i').className = 'fas fa-chevron-up';
            btn.innerHTML = '<i class="fas fa-chevron-up"></i> Masquer';
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
    document.querySelectorAll('.btn-expand-esxi').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.dataset.esxiId;
            var row = document.getElementById('esxi-vms-' + id);
            if (!row) return;
            var icon = this.querySelector('i');
            if (row.style.display === 'none' || row.style.display === '') {
                row.style.display = 'table-row';
                row._expanded = true;
                if (icon) icon.className = 'fas fa-chevron-up';
                this.innerHTML = '<i class="fas fa-chevron-up"></i> Masquer';
            } else {
                row.style.display = 'none';
                row._expanded = false;
                if (icon) icon.className = 'fas fa-chevron-down';
                this.innerHTML = '<i class="fas fa-chevron-down"></i> VMs';
            }
        });
    });
    document.querySelectorAll('.discover-esxi-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (this.dataset.hasCredentials === '0') {
                alert('Enregistrez d\'abord les identifiants dans la fiche du serveur (bouton Modifier).');
                return;
            }
            if (!this.dataset.siteId || this.dataset.siteId === '0') {
                alert('Associez un site à l\'hôte pour que l\'agent puisse effectuer la découverte.');
                return;
            }
            document.getElementById('discoverEsxiId').value = this.dataset.esxiId;
            document.getElementById('discoverEsxiResult').style.display = 'none';
            document.getElementById('discoverEsxiModal').style.display = 'flex';
        });
    });
    document.querySelectorAll('#discoverEsxiModal .close-modal').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('discoverEsxiModal').style.display = 'none';
        });
    });
    document.getElementById('btnDiscoverEsxi').addEventListener('click', function() {
        var id = document.getElementById('discoverEsxiId').value;
        var resultEl = document.getElementById('discoverEsxiResult');
        resultEl.style.display = 'block';
        resultEl.innerHTML = '<div class="text-muted"><i class="fas fa-spinner fa-spin"></i> Création du job...</div>';
        fetch('?page=hardware&section=esxi&action=discover&id=' + id, { method: 'POST' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    resultEl.innerHTML = '<div class="alert alert-success"><i class="fas fa-check"></i> ' + (data.message || 'Job créé. Rafraîchissez la page pour voir les résultats.') + '</div>';
                } else {
                    resultEl.innerHTML = '<div class="alert alert-danger">' + (data.error || 'Erreur') + '</div>';
                }
            })
            .catch(function() { resultEl.innerHTML = '<div class="alert alert-danger">Erreur réseau</div>'; });
    });
    document.querySelectorAll('.link-vm-server').forEach(function(sel) {
        sel.addEventListener('change', function() {
            var vmId = this.dataset.vmId;
            var serverId = this.value || '';
            var formData = new FormData();
            formData.append('vm_id', vmId);
            formData.append('server_id', serverId);
            fetch('?page=hardware&section=esxi&action=linkVm', { method: 'POST', body: formData })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.error || 'Erreur');
                    }
                });
        });
    });
});
</script>
