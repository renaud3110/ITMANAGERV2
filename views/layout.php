<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Manager</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- jQuery and DataTables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <h1><i class="fas fa-server"></i> IT Manager</h1>
                
                <div class="header-center">
                    <div class="tenant-selector">
                        <form method="POST" class="inline-form" id="tenant-form">
                            <div class="form-group">
                                <label for="tenant_id">
                                    <i class="fas fa-building"></i>
                                    Tenant:
                                </label>
                                <select name="tenant_id" id="tenant_id" class="styled-select" onchange="submitTenantForm()">
                                    <option value="all" <?= $currentTenant === 'all' ? 'selected' : '' ?>>Tous les tenants</option>
                                    <?php foreach ($tenants as $tenant_option): ?>
                                        <option value="<?= $tenant_option['id'] ?>" <?= $currentTenant == $tenant_option['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tenant_option['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="change_tenant" value="1">
                            </div>
                        </form>
                        
                        <form method="POST" class="inline-form" id="site-form">
                            <div class="form-group">
                                <label for="site_id">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Site:
                                </label>
                                <select name="site_id" id="site_id" class="styled-select" onchange="submitSiteForm()">
                                    <option value="all" <?= $currentSite === 'all' ? 'selected' : '' ?>>Tous les sites</option>
                                    <?php foreach ($sites as $site): ?>
                                        <option value="<?= $site['id'] ?>" <?= $currentSite == $site['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($site['name']) ?>
                                            <?php if (isset($site['tenant_name'])): ?>
                                                (<?= htmlspecialchars($site['tenant_name']) ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="change_site" value="1">
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="user-info">
                    <div class="user-details">
                        <span class="user-name">
                            <i class="fas fa-user"></i>
                            <?= htmlspecialchars($_SESSION['user_name'] ?? 'Utilisateur') ?>
                        </span>
                        <?php if ($_SESSION['is_global_admin'] ?? false): ?>
                            <span class="admin-badge">
                                <i class="fas fa-crown"></i>
                                Admin Global
                            </span>
                        <?php endif; ?>
                    </div>
                    <a href="?page=auth&action=logout" class="logout-btn" title="Se déconnecter">
                        <i class="fas fa-sign-out-alt"></i>
                        Déconnexion
                    </a>
                </div>
            </div>
        </header>

        <div class="main-layout">
            <!-- Menu latéral -->
            <nav class="sidebar">
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="?page=dashboard" class="nav-link <?= ($_GET['page'] ?? 'dashboard') === 'dashboard' ? 'active' : '' ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Tableau de bord</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?page=hardware" class="nav-link <?= ($_GET['page'] ?? '') === 'hardware' ? 'active' : '' ?>">
                            <i class="fas fa-laptop"></i>
                            <span>Matériel</span>
                        </a>
                    </li>

                    <li class="nav-item nav-dropdown">
                        <a href="#" class="nav-link nav-dropdown-toggle <?= in_array($_GET['page'] ?? '', ['networks', 'ip-management']) ? 'active' : '' ?>" 
                           onclick="toggleDropdown(event)">
                            <i class="fas fa-network-wired"></i>
                            <span>Réseaux</span>
                            <i class="fas fa-chevron-down dropdown-arrow"></i>
                        </a>
                        <ul class="nav-dropdown-menu">
                            <li class="nav-dropdown-item">
                                <a href="?page=networks" class="nav-link <?= ($_GET['page'] ?? '') === 'networks' ? 'active' : '' ?>">
                                    <i class="fas fa-server"></i>
                                    <span>Équipements</span>
                                </a>
                            </li>
                            <li class="nav-dropdown-item">
                                <a href="?page=ip-management" class="nav-link <?= ($_GET['page'] ?? '') === 'ip-management' ? 'active' : '' ?>">
                                    <i class="fas fa-sitemap"></i>
                                    <span>Gestion IP</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a href="?page=accounts" class="nav-link <?= ($_GET['page'] ?? '') === 'accounts' ? 'active' : '' ?>">
                            <i class="fas fa-user-circle"></i>
                            <span>Comptes</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="?page=microsoft365" class="nav-link <?= ($_GET['page'] ?? '') === 'microsoft365' ? 'active' : '' ?>">
                            <i class="fab fa-microsoft"></i>
                            <span>Microsoft 365</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?page=domains" class="nav-link <?= ($_GET['page'] ?? '') === 'domains' ? 'active' : '' ?>">
                            <i class="fas fa-globe"></i>
                            <span>Domaines</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="?page=licenses" class="nav-link <?= ($_GET['page'] ?? '') === 'licenses' ? 'active' : '' ?>">
                            <i class="fas fa-key"></i>
                            <span>Licences</span>
                        </a>
                    </li>
                    <li class="nav-item nav-dropdown">
                        <a href="#" class="nav-link nav-dropdown-toggle <?= in_array($_GET['page'] ?? '', ['backup']) ? 'active' : '' ?>" 
                           onclick="toggleDropdown(event)">
                            <i class="fas fa-shield-alt"></i>
                            <span>Backup</span>
                            <i class="fas fa-chevron-down dropdown-arrow"></i>
                        </a>
                        <ul class="nav-dropdown-menu">
                            <li class="nav-dropdown-item">
                                <a href="?page=backup" class="nav-link <?= ($_GET['page'] ?? '') === 'backup' && ($_GET['action'] ?? '') === '' ? 'active' : '' ?>">
                                    <i class="fas fa-chart-bar"></i>
                                    <span>Vue d'ensemble</span>
                                </a>
                            </li>
                            <li class="nav-dropdown-item">
                                <a href="?page=backup&action=nakivo" class="nav-link <?= ($_GET['page'] ?? '') === 'backup' && ($_GET['action'] ?? '') === 'nakivo' ? 'active' : '' ?>">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Nakivo</span>
                                </a>
                            </li>
                            <li class="nav-dropdown-item">
                                <a href="?page=backup&action=eurobackup" class="nav-link <?= ($_GET['page'] ?? '') === 'backup' && ($_GET['action'] ?? '') === 'eurobackup' ? 'active' : '' ?>">
                                    <i class="fas fa-cloud"></i>
                                    <span>EuroBackup</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item nav-dropdown">
                        <a href="#" class="nav-link nav-dropdown-toggle <?= in_array($_GET['page'] ?? '', ['tools']) ? 'active' : '' ?>" 
                           onclick="toggleDropdown(event)">
                            <i class="fas fa-tools"></i>
                            <span>Outils</span>
                            <i class="fas fa-chevron-down dropdown-arrow"></i>
                        </a>
                        <ul class="nav-dropdown-menu">
                            <li class="nav-dropdown-item">
                                <a href="?page=tools&action=dsd-factures" class="nav-link <?= ($_GET['page'] ?? '') === 'tools' && ($_GET['action'] ?? '') === 'dsd-factures' ? 'active' : '' ?>">
                                    <i class="fas fa-file-invoice"></i>
                                    <span>Historique DSD Factures</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-separator"></li>
                    <li class="nav-item nav-dropdown">
                        <a href="#" class="nav-link nav-dropdown-toggle <?= in_array($_GET['page'] ?? '', ['tenants', 'sites', 'users', 'security', 'services']) ? 'active' : '' ?>" 
                           onclick="toggleDropdown(event)">
                            <i class="fas fa-cog"></i>
                            <span>Paramètres</span>
                            <i class="fas fa-chevron-down dropdown-arrow"></i>
                        </a>
                        <ul class="nav-dropdown-menu">
                            <li class="nav-dropdown-item">
                                <a href="?page=tenants" class="nav-link <?= ($_GET['page'] ?? '') === 'tenants' ? 'active' : '' ?>">
                                    <i class="fas fa-building"></i>
                                    <span>Tenants</span>
                                </a>
                            </li>
                            <li class="nav-dropdown-item">
                                <a href="?page=sites" class="nav-link <?= ($_GET['page'] ?? '') === 'sites' ? 'active' : '' ?>">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>Sites</span>
                                </a>
                            </li>
                            <li class="nav-dropdown-item">
                                <a href="?page=users" class="nav-link <?= ($_GET['page'] ?? '') === 'users' ? 'active' : '' ?>">
                                    <i class="fas fa-users"></i>
                                    <span>Utilisateurs</span>
                                </a>
                            </li>
                            <li class="nav-dropdown-item">
                                <a href="?page=security" class="nav-link <?= ($_GET['page'] ?? '') === 'security' ? 'active' : '' ?>">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Sécurité</span>
                                </a>
                            </li>
                            <li class="nav-dropdown-item">
                                <a href="?page=services" class="nav-link <?= ($_GET['page'] ?? '') === 'services' ? 'active' : '' ?>">
                                    <i class="fas fa-cogs"></i>
                                    <span>Services</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>

            <!-- Contenu principal -->
            <main class="content">
                <?php if (!empty($flash['message'])): ?>
                    <div class="alert alert-<?= $flash['type'] ?>">
                        <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'exclamation-circle' : 'info-circle') ?>"></i>
                        <?= htmlspecialchars($flash['message']) ?>
                    </div>
                <?php endif; ?>
                
                <?php 
                // Charger la vue spécifique
                $viewFile = "views/$view.php";
                if (file_exists($viewFile)) {
                    include $viewFile;
                } else {
                    echo "<h1>Vue non trouvée: $view</h1>";
                }
                ?>
            </main>
        </div>
    </div>

    <script>
        function toggleDropdown(event) {
            event.preventDefault();
            const dropdownItem = event.target.closest('.nav-dropdown');
            const dropdownMenu = dropdownItem.querySelector('.nav-dropdown-menu');
            const arrow = dropdownItem.querySelector('.dropdown-arrow');
            
            // Fermer tous les autres dropdowns
            document.querySelectorAll('.nav-dropdown').forEach(item => {
                if (item !== dropdownItem) {
                    item.classList.remove('open');
                    item.querySelector('.nav-dropdown-menu').style.display = 'none';
                    item.querySelector('.dropdown-arrow').style.transform = 'rotate(0deg)';
                }
            });
            
            // Toggle le dropdown actuel
            if (dropdownItem.classList.contains('open')) {
                dropdownItem.classList.remove('open');
                dropdownMenu.style.display = 'none';
                arrow.style.transform = 'rotate(0deg)';
            } else {
                dropdownItem.classList.add('open');
                dropdownMenu.style.display = 'block';
                arrow.style.transform = 'rotate(180deg)';
            }
        }

        // Ouvrir automatiquement le dropdown si une page du sous-menu est active
        document.addEventListener('DOMContentLoaded', function() {
            const activeDropdown = document.querySelector('.nav-dropdown .nav-link.active');
            if (activeDropdown && activeDropdown.closest('.nav-dropdown-menu')) {
                const dropdownItem = activeDropdown.closest('.nav-dropdown');
                const dropdownMenu = dropdownItem.querySelector('.nav-dropdown-menu');
                const arrow = dropdownItem.querySelector('.dropdown-arrow');
                
                dropdownItem.classList.add('open');
                dropdownMenu.style.display = 'block';
                arrow.style.transform = 'rotate(180deg)';
            }
        });

        function submitTenantForm() {
            // Auto-submit le formulaire quand le tenant change
            document.getElementById('tenant-form').submit();
        }
        
        function submitSiteForm() {
            // Auto-submit le formulaire quand le site change
            document.getElementById('site-form').submit();
        }
    </script>
</body>
</html> 