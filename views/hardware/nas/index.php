<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">NAS</h1>
        <div>
            <a href="?page=hardware" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
            <a href="?page=hardware&section=nas&action=create" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Ajouter un NAS
            </a>
        </div>
    </div>

    <!-- Stats Grid (style PCs) -->
    <div class="stats-grid">
        <div class="stat-card clickable active" data-filter="all" title="Tous les NAS">
            <div class="stat-icon">
                <i class="fas fa-database"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= $totalNas ?? count($nasList ?? []) ?></div>
                <div class="stat-label">Tous les NAS</div>
            </div>
        </div>
        <div class="stat-card clickable" data-filter="ok" title="NAS OK">
            <div class="stat-icon nas-ok">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= $nasAllOk ?? 0 ?></div>
                <div class="stat-label">NAS OK</div>
            </div>
        </div>
        <div class="stat-card clickable" data-filter="problem" title="À vérifier">
            <div class="stat-icon nas-problem">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number"><?= $nasWithProblems ?? 0 ?></div>
                <div class="stat-label">À vérifier</div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Liste des NAS</h6>
            <div class="search-box" style="position:relative;width:250px">
                <input type="text" id="searchInput" placeholder="Rechercher..." class="form-control form-control-sm" style="padding-right:2.5rem">
                <i class="fas fa-search" style="position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);color:#9ca3af"></i>
            </div>
        </div>
        <div class="card-body">
            <?php if (!empty($nasList)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Statut</th>
                                <th>Nom</th>
                                <th>Hôte</th>
                                <th>Type</th>
                                <th>Site</th>
                                <th>Dernière découverte</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($nasList as $nas): ?>
                                <?php
                                $lastDisc = $nas['last_discovery'] ?? null;
                                $disks = [];
                                $allDisksOk = true;
                                if ($lastDisc && !empty($lastDisc['disks_json'])) {
                                    $disks = json_decode($lastDisc['disks_json'], true) ?: [];
                                    foreach ($disks as $d) {
                                        $st = strtolower(trim($d['status'] ?? ''));
                                        $smart = strtoupper(trim($d['smart_status'] ?? ''));
                                        $bad = !empty($d['exceed_bad_sector']) || !empty($d['below_life_threshold']);
                                        $ok = ($st === 'normal' || $st === 'passed' || strpos($smart, 'PASSED') !== false || strpos($smart, 'OK') !== false) && !$bad;
                                        if (!$ok) $allDisksOk = false;
                                    }
                                    if (empty($disks)) $allDisksOk = true;
                                }
                                $volumes = [];
                                if ($lastDisc && !empty($lastDisc['volumes_json'])) {
                                    $volumes = json_decode($lastDisc['volumes_json'], true) ?: [];
                                }
                                ?>
                                <tr class="nas-row" data-nas-id="<?= (int)$nas['id'] ?>" data-status="<?= ($lastDisc && !empty($disks) && $allDisksOk) ? 'ok' : (($lastDisc && !empty($disks) && !$allDisksOk) ? 'problem' : '') ?>" data-volumes='<?= htmlspecialchars(json_encode($volumes), ENT_QUOTES, 'UTF-8') ?>' data-disks='<?= htmlspecialchars(json_encode($disks), ENT_QUOTES, 'UTF-8') ?>'>
                                    <td class="text-center">
                                        <?php if ($lastDisc && !empty($lastDisc['disks_json'])): ?>
                                            <?php if ($allDisksOk && !empty($disks)): ?>
                                                <i class="fas fa-circle nas-status-icon text-success" title="Tous les disques OK"></i>
                                            <?php elseif (empty($disks)): ?>
                                                <i class="fas fa-minus-circle nas-status-icon text-muted" title="Pas de disques découverts"></i>
                                            <?php else: ?>
                                                <i class="fas fa-circle nas-status-icon text-danger" title="Vérifiez les disques"></i>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <i class="fas fa-question-circle nas-status-icon text-muted" title="Aucune découverte"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($nas['name'] ?? 'Sans nom') ?></strong>
                                        <?php if (!empty($nas['description'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($nas['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($nas['host'] ?? '') ?>:<?= (int)($nas['port'] ?? 5000) ?></td>
                                    <td><span class="badge badge-info"><?= htmlspecialchars(ucfirst($nas['type'] ?? 'synology')) ?></span></td>
                                    <td><?= htmlspecialchars($nas['site_name'] ?? '-') ?></td>
                                    <td>
                                        <?php if ($lastDisc && $lastDisc['discovered_at']): ?>
                                            <?= date('d/m/Y H:i', strtotime($lastDisc['discovered_at'])) ?>
                                            <?php
                                            $sh = isset($lastDisc['shares_json']) ? json_decode($lastDisc['shares_json'], true) : [];
                                            $vo = isset($lastDisc['volumes_json']) ? json_decode($lastDisc['volumes_json'], true) : [];
                                            $di = isset($lastDisc['disks_json']) ? json_decode($lastDisc['disks_json'], true) : [];
                                            $ra = isset($lastDisc['raid_json']) ? json_decode($lastDisc['raid_json'], true) : [];
                                            $ns = is_array($sh) ? count($sh) : 0;
                                            $nv = is_array($vo) ? count($vo) : 0;
                                            $nd = is_array($di) ? count($di) : 0;
                                            $nr = is_array($ra) ? count($ra) : 0;
                                            if ($ns || $nv || $nd || $nr): ?>
                                                <br><small class="text-muted"><?= $ns ?> part., <?= $nv ?> vol., <?= $nd ?> disq., <?= $nr ?> RAID</small>
                                            <?php endif; ?>
                                            <?php if ($lastDisc['error_message']): ?>
                                                <br><small class="text-danger"><?= htmlspecialchars($lastDisc['error_message']) ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted">Jamais</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-secondary btn-expand-nas" title="Volumes & disques">
                                                <i class="fas fa-chevron-down"></i><span class="btn-expand-txt"> Détail</span>
                                            </button>
                                            <a href="?page=hardware&section=nas&action=edit&id=<?= $nas['id'] ?>" class="btn btn-warning" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?page=hardware&section=nas&action=delete&id=<?= $nas['id'] ?>" class="btn btn-danger" title="Supprimer"
                                               onclick="return confirm('Supprimer ce NAS ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="mb-3"><i class="fas fa-database fa-3x text-muted"></i></div>
                    <h4>Aucun NAS</h4>
                    <p class="text-muted">Aucun NAS enregistré pour le contexte sélectionné.</p>
                    <a href="?page=hardware&section=nas&action=create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Ajouter un NAS
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
.stat-card { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; display: flex; align-items: center; gap: 1rem; transition: transform 0.2s ease, box-shadow 0.2s ease; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
.stat-card.clickable { cursor: pointer; }
.stat-card.active { border: 2px solid #667eea; box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3); }
.stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.25rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-icon.nas-ok { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); }
.stat-icon.nas-problem { background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); }
.stat-content { flex: 1; }
.stat-number { font-size: 2rem; font-weight: 700; color: #1f2937; }
.stat-label { font-size: 0.875rem; color: #6b7280; margin-top: 0.25rem; }
.nas-status-icon { font-size: 1.25rem; }
.volume-meter { height: 10px; border-radius: 5px; background: #e5e7eb; overflow: hidden; }
.volume-meter-fill { height: 100%; border-radius: 5px; transition: width 0.3s ease; }
.volume-meter-fill.success { background: #16a34a; }
.volume-meter-fill.warning { background: #f59e0b; }
.volume-meter-fill.danger { background: #dc2626; }

/* DataTables footer - même style que PCs */
.dataTables_wrapper { padding: 0; }
.dataTables_length { margin-bottom: 1rem; }
.dataTables_length select { padding: 0.375rem 0.75rem; border: 1px solid #d1d3e2; border-radius: 0.35rem; background-color: #fff; color: #5a5c69; font-size: 0.875rem; }
.dataTables_filter { display: none; }
.dataTables_wrapper .row:last-child { display: flex; align-items: center; justify-content: space-between; margin-top: 1.5rem; padding: 1rem 0; gap: 1rem; border-top: 1px solid #e5e7eb; }
.dataTables_info { background: transparent; padding: 0.5rem 0; color: #6b7280; font-size: 0.875rem; margin: 0; flex-shrink: 0; font-weight: 500; }
.dataTables_paginate { margin: 0; display: flex; justify-content: flex-end; flex-shrink: 0; list-style: none !important; padding: 0; }
.dataTables_paginate ul, .dataTables_paginate li { list-style: none !important; padding: 0; margin: 0; }
.dataTables_paginate .paging_simple_numbers { list-style: none !important; display: flex; align-items: center; gap: 0.25rem; flex-wrap: nowrap; padding: 0; margin: 0; }
.dataTables_paginate .paginate_button { padding: 0.5rem 0.75rem; margin: 0 0.125rem; border: 1px solid #e5e7eb; border-radius: 6px; background: #fff; color: #6b7280; text-decoration: none; font-size: 0.875rem; font-weight: 500; white-space: nowrap; display: inline-block; cursor: pointer; }
.dataTables_paginate .paginate_button:hover { background: #f9fafb; color: #374151; border-color: #d1d5db; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.dataTables_paginate .paginate_button.current { background: #667eea; color: white; border-color: #667eea; box-shadow: 0 1px 2px rgba(102, 126, 234, 0.2); }
.dataTables_paginate .paginate_button.disabled { background: #f9fafb; color: #d1d5db; border-color: #f3f4f6; cursor: not-allowed; }
.dataTables_paginate .paginate_button.disabled:hover { background: #f9fafb; transform: none; box-shadow: none; }
@media (max-width: 768px) {
    .dataTables_wrapper .row:last-child { flex-direction: column; align-items: center; gap: 0.75rem; }
    .dataTables_info { text-align: center; order: 2; }
    .dataTables_paginate { justify-content: center; order: 1; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var dt = null;
    var currentFilter = 'all';
    if (typeof jQuery !== 'undefined' && jQuery('#dataTable').length) {
        dt = jQuery('#dataTable').DataTable({
            language: {
                sInfo: "Affichage de _START_ à _END_ sur _TOTAL_ NAS",
                sInfoEmpty: "Affichage de 0 à 0 sur 0 NAS",
                sInfoFiltered: "(filtré de _MAX_ NAS au total)",
                sLengthMenu: "Afficher _MENU_ éléments",
                sZeroRecords: "Aucun NAS trouvé",
                oPaginate: { sFirst: "Premier", sPrevious: "Précédent", sNext: "Suivant", sLast: "Dernier" }
            },
            pageLength: 25,
            columnDefs: [ { orderable: false, targets: [0, 6] } ],
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6">>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
        });
        jQuery('#searchInput').on('keyup', function() {
            dt.search(this.value).draw();
        });
        jQuery(document).on('click', '.stat-card.clickable', function() {
            var f = jQuery(this).data('filter');
            currentFilter = f;
            jQuery('.stat-card').removeClass('active');
            jQuery(this).addClass('active');
            dt.draw();
        });
        jQuery.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            if (currentFilter === 'all') return true;
            var row = dt.row(dataIndex).node();
            var status = jQuery(row).data('status');
            if (currentFilter === 'ok') return status === 'ok';
            if (currentFilter === 'problem') return status === 'problem';
            return true;
        });
    }
    function formatBytes(b) {
        if (b <= 0) return '0 o';
        var k = 1024, sizes = ['o','Ko','Mo','Go','To'];
        var i = Math.floor(Math.log(b) / Math.log(k));
        return parseFloat((b / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }
    function buildNasDetailHtml(volumes, disks) {
        var vRows = [];
        if (!volumes || volumes.length === 0) {
            vRows.push('<tr><td colspan="5" class="text-muted small">Aucun volume</td></tr>');
        } else {
    volumes.forEach(function(v) {
                var size = v.size || 0, used = v.used || v.used_size || 0;
                var pct = size > 0 ? Math.round(100 * used / size) : 0;
                var bar = pct >= 90 ? 'danger' : (pct >= 75 ? 'warning' : 'success');
                var meterHtml = '<div class="volume-meter"><div class="volume-meter-fill ' + bar + '" style="width:' + pct + '%"></div></div><small class="d-block mt-1">' + pct + '%</small>';
                var vColor = (v.status === 'normal' || !v.status) ? '#28a745' : '#dc3545';
                vRows.push('<tr><td>' + (v.name || v.mount || '-') + '</td><td>' + (v.size_h || formatBytes(size)) + '</td><td>' + (v.used_h || formatBytes(used)) + '</td><td>' + meterHtml + '</td><td><i class="fas fa-circle" style="font-size:0.6rem;color:' + vColor + '"></i> ' + (v.status || 'normal') + '</td></tr>');
            });
        }
        var dRows = [];
        if (!disks || disks.length === 0) {
            dRows.push('<tr><td colspan="4" class="text-muted small">Aucun disque</td></tr>');
        } else {
            disks.forEach(function(d) {
                var st = (d.status || '').toLowerCase();
                var smart = (d.smart_status || '').toUpperCase();
                var bad = d.exceed_bad_sector || d.below_life_threshold;
                var ok = (st === 'normal' || st === 'passed' || smart.indexOf('PASSED') >= 0 || smart.indexOf('OK') >= 0) && !bad;
                var stBadge = bad || !ok ? 'danger' : 'success';
                var model = ((d.model || '') + ' ' + (d.vendor || '')).trim() || '-';
                var cap = d.size_h || (d.size_total ? formatBytes(d.size_total) : '-');
                var dColor = stBadge === 'success' ? '#28a745' : '#dc3545';
                dRows.push('<tr><td>' + (d.name || d.device || '-') + '</td><td>' + model + '</td><td>' + cap + '</td><td><i class="fas fa-circle" style="font-size:0.75rem;color:' + dColor + '"></i> ' + (d.status || d.smart_status || '-') + '</td></tr>');
            });
        }
        return '<div class="nas-detail-content p-3 bg-light">' +
            '<div class="mb-4"><h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-database"></i> Volumes</h6><table class="table table-sm table-bordered mb-0"><thead><tr><th>Volume</th><th>Total</th><th>Utilisé</th><th>Vu-mètre</th><th>Statut</th></tr></thead><tbody>' + vRows.join('') + '</tbody></table></div>' +
            '<div><h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-hdd"></i> Disques</h6><table class="table table-sm table-bordered mb-0"><thead><tr><th>Disque</th><th>Modèle</th><th>Capacité</th><th>Statut</th></tr></thead><tbody>' + dRows.join('') + '</tbody></table></div></div>';
    }

    document.querySelectorAll('.btn-expand-nas').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (!dt) return;
            var rowEl = this.closest('tr');
            var volumes = [], disks = [];
            try { volumes = JSON.parse(rowEl.getAttribute('data-volumes') || '[]'); } catch(x) {}
            try { disks = JSON.parse(rowEl.getAttribute('data-disks') || '[]'); } catch(x) {}
            var content = buildNasDetailHtml(volumes, disks);
            var icon = this.querySelector('i');
            var txt = this.querySelector('.btn-expand-txt');
            if (dt) {
                var row = dt.row(rowEl);
                if (row.child.isShown()) {
                    row.child.hide();
                    if (icon) icon.className = 'fas fa-chevron-down';
                    if (txt) txt.textContent = ' Détail';
                } else {
                    row.child(content).show();
                    if (icon) icon.className = 'fas fa-chevron-up';
                    if (txt) txt.textContent = ' Masquer';
                }
            }
        });
    });
});
</script>
