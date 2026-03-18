<?php

class HardwareController extends BaseController {

    private $computerModel;
    private $operatingSystemModel;
    private $ipAddressModel;
    private $modelModel;
    private $loginModel;
    private $tenantModel;
    private $siteModel;
    private $personModel;
    private $loginServiceModel;
    private $serverModel;
    private $nasModel;
    private $esxiModel;

    public function __construct() {
        parent::__construct();
        $this->computerModel = new Computer();
        $this->operatingSystemModel = new OperatingSystem();
        $this->ipAddressModel = new IpAddress();
        $this->modelModel = new Model();
        $this->loginModel = new Login();
        $this->tenantModel = new Tenant();
        $this->siteModel = new Site();
        $this->personModel = new Person();
        $this->loginServiceModel = new LoginService();
        $this->serverModel = new Server();
        $this->nasModel = new Nas();
        $this->esxiModel = new EsxiHost();
    }

    public function index() {
        $this->handleTenantSiteSelection();
        
        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        $currentSite = $_SESSION['current_site'] ?? 'all';
        
        // Obtenir seulement les statistiques pour la page d'accueil
        $computersCount = $this->computerModel->getCount($currentTenant, $currentSite);
        $serversCount = $this->serverModel->getCount($currentTenant, $currentSite);
        $nasCount = $this->nasModel->getCount($currentTenant, $currentSite);
        $esxiCount = $this->esxiModel->getCount($currentTenant, $currentSite);
        
        $this->loadView('hardware/index', [
            'computersCount' => $computersCount,
            'serversCount' => $serversCount,
            'nasCount' => $nasCount,
            'esxiCount' => $esxiCount,
            'printersCount' => 0, // À implémenter plus tard
            'currentTenant' => $currentTenant,
            'currentSite' => $currentSite
        ]);
    }

    public function computers() {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'create':
                $this->createComputer();
                break;
            case 'view':
                $this->viewComputer();
                break;
            case 'edit':
                $this->editComputer();
                break;
            case 'delete':
                $this->deleteComputer();
                break;
            default:
                $this->listComputers();
        }
    }

    public function nas() {
        $action = $_GET['action'] ?? 'list';
        switch ($action) {
            case 'create':
                $this->createNas();
                break;
            case 'edit':
                $this->editNas();
                break;
            case 'delete':
                $this->deleteNas();
                break;
            default:
                $this->listNas();
        }
    }

    public function esxi() {
        $action = $_GET['action'] ?? 'list';
        switch ($action) {
            case 'create':
                $this->createEsxi();
                break;
            case 'edit':
                $this->editEsxi();
                break;
            case 'delete':
                $this->deleteEsxi();
                break;
            case 'discover':
                $this->discoverEsxi();
                break;
            case 'linkVm':
                $this->linkEsxiVm();
                break;
            default:
                $this->listEsxi();
        }
    }

    public function servers() {
        // Rediriger vers le contrôleur dédié aux serveurs
        $action = $_GET['action'] ?? 'index';
        $id = $_GET['id'] ?? null;
        
        $url = "?page=servers";
        if ($action !== 'index') {
            $url .= "&action=" . $action;
        }
        if ($id) {
            $url .= "&id=" . $id;
        }
        
        header("Location: " . $url);
        exit;
    }

    private function listComputers() {
        $this->handleTenantSiteSelection();
        
        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        $currentSite = $_SESSION['current_site'] ?? 'all';
        
        $computers = $this->computerModel->getAll($currentTenant, $currentSite);
        
        // Calculer les statistiques par OS
        $totalComputers = count($computers);
        $windows10Count = 0;
        $windows11Count = 0;
        
        foreach ($computers as $computer) {
            $osName = strtolower($computer['operating_system_name'] ?? '');
            if (strpos($osName, 'windows 10') !== false) {
                $windows10Count++;
            } elseif (strpos($osName, 'windows 11') !== false) {
                $windows11Count++;
            }
        }
        
        $this->loadView('hardware/computers/index', [
            'computers' => $computers,
            'currentTenant' => $currentTenant,
            'currentSite' => $currentSite,
            'totalComputers' => $totalComputers,
            'windows10Count' => $windows10Count,
            'windows11Count' => $windows11Count
        ]);
    }

    private function viewComputer() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            header('Location: ?page=hardware&section=computers');
            exit;
        }
        
        $computer = $this->computerModel->getById($id);
        if (!$computer) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            header('Location: ?page=hardware&section=computers');
            exit;
        }
        
        // Récupérer les informations sur les disques et partitions
        $disks = $this->computerModel->getDisksWithPartitions($id);
        $gpus = $this->computerModel->getGpus($id);
        $monitors = $this->computerModel->getMonitors($id);
        $printers = $this->computerModel->getPrinters($id);
        $networkAdapters = $this->computerModel->getNetworkAdapters($id);
        $windowsUpdates = $this->computerModel->getWindowsUpdates($id);
        $windowsServices = $this->computerModel->getWindowsServices($id);
        $windowsStartup = $this->computerModel->getWindowsStartup($id);
        $windowsShared = $this->computerModel->getWindowsShared($id);
        $windowsMapped = $this->computerModel->getWindowsMapped($id);
        $windowsUsers = $this->computerModel->getWindowsUsers($id);
        $windowsUserGroups = $this->computerModel->getWindowsUserGroups($id);
        $windowsLicense = $this->computerModel->getWindowsLicense($id);

        // Récupérer les logiciels installés
        $installedSoftware = $this->getInstalledSoftware($id);

        $this->loadView('hardware/computers/view', [
            'computer' => $computer,
            'disks' => $disks,
            'gpus' => $gpus,
            'monitors' => $monitors,
            'printers' => $printers,
            'networkAdapters' => $networkAdapters,
            'windowsUpdates' => $windowsUpdates,
            'windowsServices' => $windowsServices,
            'windowsStartup' => $windowsStartup,
            'windowsShared' => $windowsShared,
            'windowsMapped' => $windowsMapped,
            'windowsUsers' => $windowsUsers,
            'windowsUserGroups' => $windowsUserGroups,
            'windowsLicense' => $windowsLicense,
            'installedSoftware' => $installedSoftware
        ]);
    }

    private function createComputer() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST)) {
            $data = [
                'name' => $_POST['name'] ?? '',
                'tenant_id' => $_POST['tenant_id'],
                'site_id' => $_POST['site_id'],
                'operating_system_id' => $_POST['operating_system_id'],
                'ip_address_id' => $_POST['ip_address_id'],
                'processor_model' => $_POST['processor_model'],
                'teamviewer_id' => $_POST['teamviewer_id'] ?? '',
                'rustdesk_id' => $_POST['rustdesk_id'] ?? '',
                'model_id' => $_POST['model_id'],
                'status' => $_POST['status'],
                'account_id' => $_POST['account_id'],
                'last_account' => $_POST['last_account'],
                'serial_number' => $_POST['serial_number'] ?? ''
            ];
            
            if ($this->computerModel->create($data)) {
                if (ob_get_level()) {
                    ob_end_clean();
                }
                header('Location: ?page=hardware&section=computers');
                exit;
            } else {
                $error = "Erreur lors de la création de l'ordinateur";
            }
        }
        
        // Récupérer les données pour les formulaires
        $tenants = $this->tenantModel->getAll();
        $sites = $this->siteModel->getAllSites();
        
        $operatingSystems = $this->operatingSystemModel->getAll();
        $ipAddresses = $this->ipAddressModel->getAll();
        $models = $this->modelModel->getAll();
        $logins = $this->loginModel->getAll();
        
        $this->loadView('hardware/computers/create', [
            'tenants' => $tenants,
            'sites' => $sites,
            'operatingSystems' => $operatingSystems,
            'ipAddresses' => $ipAddresses,
            'models' => $models,
            'logins' => $logins,
            'error' => $error ?? null
        ]);
    }

    private function editComputer() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            header('Location: ?page=hardware&section=computers');
            exit;
        }
        
        $computer = $this->computerModel->getById($id);
        if (!$computer) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            header('Location: ?page=hardware&section=computers');
            exit;
        }
        
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $personId = $_POST['person_id'] ?? null;
            $newFirstName = trim($_POST['new_first_name'] ?? '');
            $newLastName = trim($_POST['new_last_name'] ?? '');
            $newEmail = trim($_POST['new_email'] ?? '');
            
            // Si une nouvelle personne est demandée, créer la personne
            if ($personId === 'new_person' && !empty($newFirstName) && !empty($newLastName)) {
                // Vérifier si l'email existe déjà (si fourni)
                if (!empty($newEmail) && $this->personModel->emailExists($newEmail)) {
                    $error = "Une personne avec cet email ($newEmail) existe déjà";
                } else {
                    // Créer la nouvelle personne avec le même tenant que le PC
                    $newPersonData = [
                        'nom' => $newLastName,
                        'prenom' => $newFirstName,
                        'email' => !empty($newEmail) ? $newEmail : null,
                        'tenant_id' => $computer['tenant_id']
                    ];
                    
                    try {
                        $personId = $this->personModel->create($newPersonData);
                        if (!$personId) {
                            $error = "Erreur lors de la création de la nouvelle personne";
                        }
                    } catch (Exception $e) {
                        $error = "Erreur lors de la création de la personne : " . $e->getMessage();
                    }
                }
            } elseif ($personId === 'new_person') {
                $error = "Veuillez remplir au moins le prénom et le nom pour créer une nouvelle personne";
                $personId = null;
            }
            
            if (!isset($error)) {
                $data = [
                    'name' => $_POST['name'] ?? '',
                    'tenant_id' => $_POST['tenant_id'],
                    'site_id' => $_POST['site_id'],
                    'operating_system_id' => $_POST['operating_system_id'],
                    'ip_address_id' => $_POST['ip_address_id'],
                    'processor_model' => $_POST['processor_model'],
                    'teamviewer_id' => $_POST['teamviewer_id'] ?? '',
                    'rustdesk_id' => $_POST['rustdesk_id'] ?? '',
                    'model_id' => $_POST['model_id'],
                    'status' => $_POST['status'],
                    'person_id' => $personId,
                    'last_account' => $_POST['last_account'],
                    'serial_number' => $_POST['serial_number'] ?? ''
                ];
                
                if ($this->computerModel->update($id, $data)) {
                    if (ob_get_level()) {
                        ob_end_clean();
                    }
                    header('Location: ?page=hardware&section=computers');
                    exit;
                } else {
                    $error = "Erreur lors de la modification de l'ordinateur";
                }
            }
        }
        
        // Récupérer les données pour les formulaires
        $tenants = $this->tenantModel->getAll();
        $sites = $this->siteModel->getAllSites();
        
        $operatingSystems = $this->operatingSystemModel->getAll();
        $ipAddresses = $this->ipAddressModel->getAll();
        $models = $this->modelModel->getAll();
        // Récupérer seulement les personnes du même tenant que le PC
        $persons = $this->personModel->getAll($computer['tenant_id']);
        
        $this->loadView('hardware/computers/edit', [
            'computer' => $computer,
            'tenants' => $tenants,
            'sites' => $sites,
            'operatingSystems' => $operatingSystems,
            'ipAddresses' => $ipAddresses,
            'models' => $models,
            'persons' => $persons,
            'error' => $error ?? null
        ]);
    }

    private function deleteComputer() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            header('Location: ?page=hardware&section=computers');
            exit;
        }
        
        if ($this->computerModel->delete($id)) {
            $_SESSION['flash_message'] = 'Ordinateur supprimé avec succès';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Erreur lors de la suppression';
            $_SESSION['flash_type'] = 'error';
        }
        
        if (ob_get_level()) {
            ob_end_clean();
        }
        header('Location: ?page=hardware&section=computers');
        exit;
    }

    public function getCpuTemperatures() {
        $this->getMonitorTemperatures();
    }

    public function getMonitorTemperatures() {
        header('Content-Type: application/json');
        
        $pcId = $_GET['pc_id'] ?? null;
        if (!$pcId) {
            echo json_encode(['error' => 'PC ID manquant']);
            return;
        }

        try {
            $db = new Database();
            // Priorité : pc_monitor_history (moniteur actuel). Sinon fallback sur cpu_temperatures (legacy)
            $tableExists = $db->fetch("SHOW TABLES LIKE 'pc_monitor_history'");
            if ($tableExists) {
                $rows = $db->fetchAll(
                    "SELECT cpu_temp, gpu_temp, created_at FROM pc_monitor_history 
                     WHERE pc_id = ? ORDER BY created_at DESC LIMIT 200",
                    [$pcId]
                );
                $rows = array_reverse($rows); // Ordre chronologique pour le graphique
                $labels = [];
                $cpuTemps = [];
                $gpuTemps = [];
                foreach ($rows as $r) {
                    $labels[] = date('d/m H:i', strtotime($r['created_at'] . ' UTC'));
                    $cpuTemps[] = $r['cpu_temp'] !== null ? (float)$r['cpu_temp'] : null;
                    $gpuTemps[] = $r['gpu_temp'] !== null ? (float)$r['gpu_temp'] : null;
                }
                echo json_encode([
                    'labels' => $labels,
                    'cpu_temperatures' => $cpuTemps,
                    'gpu_temperatures' => $gpuTemps,
                    'history' => $rows
                ]);
                return;
            }

            // Fallback legacy cpu_temperatures
            $temperatures = $db->fetchAll(
                "SELECT temperature, created_at FROM cpu_temperatures WHERE pc_id = ? ORDER BY created_at DESC LIMIT 100",
                [$pcId]
            );
            $data = ['labels' => [], 'temperatures' => [], 'cpu_temperatures' => [], 'gpu_temperatures' => []];
            foreach ($temperatures as $temp) {
                $data['labels'][] = date('H:i', strtotime($temp['created_at']));
                $data['temperatures'][] = floatval($temp['temperature']);
                $data['cpu_temperatures'][] = floatval($temp['temperature']);
                $data['gpu_temperatures'][] = null;
            }
            $data['labels'] = array_reverse($data['labels']);
            $data['cpu_temperatures'] = array_reverse($data['cpu_temperatures']);
            $data['gpu_temperatures'] = array_reverse($data['gpu_temperatures']);
            $data['history'] = [];
            echo json_encode($data);
        } catch (Exception $e) {
            echo json_encode(['error' => 'Erreur lors de la récupération des températures: ' . $e->getMessage()]);
        }
    }

    /**
     * Récupère les logiciels installés sur un PC
     */
    public function getInstalledSoftware($pcId) {
        try {
            $db = new Database();
            $query = "SELECT s.name, s.version, ins.installation_date
                     FROM software s 
                     JOIN installed_software ins ON s.id = ins.software_id 
                     WHERE ins.pc_id = ? 
                     ORDER BY s.name ASC";
            
            return $db->fetchAll($query, [$pcId]);
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des logiciels: " . $e->getMessage());
            return [];
        }
    }

    private function listNas() {
        $this->handleTenantSiteSelection();
        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        $currentSite = $_SESSION['current_site'] ?? 'all';
        $nasList = $this->nasModel->getAll($currentTenant, $currentSite);
        $nasAllOk = 0;
        $nasWithProblems = 0;
        foreach ($nasList as &$nas) {
            $nas['last_discovery'] = $this->nasModel->getLastDiscovery($nas['id']);
            $nas['has_credentials'] = $this->nasModel->hasCredentials($nas['id']);
            $disks = [];
            $allOk = true;
            $hasProblems = false;
            if (!empty($nas['last_discovery']['disks_json'])) {
                $disks = json_decode($nas['last_discovery']['disks_json'], true) ?: [];
                foreach ($disks as $d) {
                    $st = strtolower(trim($d['status'] ?? ''));
                    $smart = strtoupper(trim($d['smart_status'] ?? ''));
                    $bad = !empty($d['exceed_bad_sector']) || !empty($d['below_life_threshold']);
                    $ok = ($st === 'normal' || $st === 'passed' || strpos($smart, 'PASSED') !== false || strpos($smart, 'OK') !== false) && !$bad;
                    if (!$ok) $allOk = false;
                    if ($bad || !$ok) $hasProblems = true;
                }
                if (!empty($disks)) {
                    if ($allOk) $nasAllOk++;
                    else $nasWithProblems++;
                }
            }
        }
        unset($nas);
        $sites = $this->siteModel->getSitesForDropdown($currentTenant === 'all' ? null : $currentTenant);
        $tenants = $this->tenantModel->getAll();
        $this->loadView('hardware/nas/index', [
            'nasList' => $nasList,
            'totalNas' => count($nasList),
            'nasAllOk' => $nasAllOk,
            'nasWithProblems' => $nasWithProblems,
            'sites' => $sites,
            'tenants' => $tenants,
            'currentTenant' => $currentTenant,
            'currentSite' => $currentSite,
            'flash' => $this->getFlashMessage()
        ]);
    }

    private function createNas() {
        $this->handleTenantSiteSelection();
        $sites = $this->siteModel->getSitesForDropdown();
        $tenants = $this->tenantModel->getAll();
        $ipAddresses = $this->ipAddressModel->getAll();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'host' => trim($_POST['host'] ?? ''),
                'port' => (int)($_POST['port'] ?? 5000),
                'type' => $_POST['type'] ?? 'synology',
                'site_id' => !empty($_POST['site_id']) ? (int)$_POST['site_id'] : null,
                'tenant_id' => !empty($_POST['tenant_id']) ? (int)$_POST['tenant_id'] : null,
                'ip_address_id' => !empty($_POST['ip_address_id']) ? (int)$_POST['ip_address_id'] : null,
                'description' => trim($_POST['description'] ?? '') ?: null
            ];
            if (empty($data['name']) || empty($data['host'])) {
                $this->loadView('hardware/nas/create', ['error' => 'Nom et hôte requis', 'sites' => $sites, 'tenants' => $tenants, 'ipAddresses' => $ipAddresses]);
                return;
            }
            try {
                $nasId = $this->nasModel->create($data);
                if (!empty($_POST['cred_username']) && $_POST['cred_password'] !== '') {
                    $this->nasModel->saveCredentials($nasId, trim($_POST['cred_username']), $_POST['cred_password']);
                }
                $_SESSION['flash_message'] = 'NAS ajouté avec succès';
                $_SESSION['flash_type'] = 'success';
                header('Location: ?page=hardware&section=nas');
                exit;
            } catch (Exception $e) {
                $this->loadView('hardware/nas/create', ['error' => $e->getMessage(), 'sites' => $sites, 'tenants' => $tenants, 'ipAddresses' => $ipAddresses]);
                return;
            }
        }
        $this->loadView('hardware/nas/create', ['sites' => $sites, 'tenants' => $tenants, 'ipAddresses' => $ipAddresses]);
    }

    private function editNas() {
        $id = $_GET['id'] ?? 0;
        $nas = $this->nasModel->getById($id);
        if (!$nas) {
            header('Location: ?page=hardware&section=nas');
            exit;
        }
        $sites = $this->siteModel->getSitesForDropdown();
        $tenants = $this->tenantModel->getAll();
        $ipAddresses = $this->ipAddressModel->getAll();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'host' => trim($_POST['host'] ?? ''),
                'port' => (int)($_POST['port'] ?? 5000),
                'type' => $_POST['type'] ?? 'synology',
                'site_id' => !empty($_POST['site_id']) ? (int)$_POST['site_id'] : null,
                'tenant_id' => !empty($_POST['tenant_id']) ? (int)$_POST['tenant_id'] : null,
                'ip_address_id' => !empty($_POST['ip_address_id']) ? (int)$_POST['ip_address_id'] : null,
                'description' => trim($_POST['description'] ?? '') ?: null
            ];
            try {
                $this->nasModel->update($id, $data);
                if (isset($_POST['cred_username']) && !empty(trim($_POST['cred_username'])) && $_POST['cred_password'] !== '') {
                    $this->nasModel->saveCredentials($id, trim($_POST['cred_username']), $_POST['cred_password']);
                }
                $_SESSION['flash_message'] = 'NAS mis à jour';
                $_SESSION['flash_type'] = 'success';
                header('Location: ?page=hardware&section=nas');
                exit;
            } catch (Exception $e) {
                $this->loadView('hardware/nas/edit', ['nas' => array_merge($nas, $_POST), 'error' => $e->getMessage(), 'sites' => $sites, 'tenants' => $tenants, 'ipAddresses' => $ipAddresses]);
                return;
            }
        }
        $nas['has_credentials'] = $this->nasModel->hasCredentials($id);
        $nas['cred_username'] = $this->nasModel->getCredentialsUsername($id);
        $this->loadView('hardware/nas/edit', ['nas' => $nas, 'sites' => $sites, 'tenants' => $tenants, 'ipAddresses' => $ipAddresses]);
    }

    private function deleteNas() {
        $id = $_GET['id'] ?? 0;
        try {
            $this->nasModel->delete($id);
            $_SESSION['flash_message'] = 'NAS supprimé';
            $_SESSION['flash_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['flash_message'] = 'Erreur: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'error';
        }
        header('Location: ?page=hardware&section=nas');
        exit;
    }

    private function listEsxi() {
        $this->handleTenantSiteSelection();
        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        $currentSite = $_SESSION['current_site'] ?? 'all';
        $esxiList = $this->esxiModel->getAll($currentTenant, $currentSite);
        foreach ($esxiList as &$e) {
            $e['last_discovery'] = $this->esxiModel->getLastDiscovery($e['id']);
            $e['has_credentials'] = $this->esxiModel->hasCredentials($e['id']);
            $e['vms'] = $this->esxiModel->getVms($e['id']);
        }
        unset($e);
        $esxiWithVms = count(array_filter($esxiList, fn($e) => !empty($e['vms'])));
        $esxiDiscoveryOk = count(array_filter($esxiList, fn($e) => !empty($e['last_discovery']['discovered_at']) && empty($e['last_discovery']['error_message'])));
        $esxiNoCredentials = count(array_filter($esxiList, fn($e) => empty($e['has_credentials'])));
        $sites = $this->siteModel->getSitesForDropdown($currentTenant === 'all' ? null : $currentTenant);
        $tenants = $this->tenantModel->getAll();
        $servers = $this->serverModel->getAll($currentTenant, $currentSite);
        $this->loadView('hardware/esxi/index', [
            'esxiList' => $esxiList,
            'totalEsxi' => count($esxiList),
            'esxiWithVms' => $esxiWithVms,
            'esxiDiscoveryOk' => $esxiDiscoveryOk,
            'esxiNoCredentials' => $esxiNoCredentials,
            'sites' => $sites,
            'tenants' => $tenants,
            'servers' => $servers,
            'currentTenant' => $currentTenant,
            'currentSite' => $currentSite,
            'flash' => $this->getFlashMessage()
        ]);
    }

    private function createEsxi() {
        $this->handleTenantSiteSelection();
        $sites = $this->siteModel->getSitesForDropdown();
        $tenants = $this->tenantModel->getAll();
        $ipAddresses = $this->ipAddressModel->getAll();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $siteId = !empty($_POST['site_id']) ? (int)$_POST['site_id'] : null;
            $tenantId = !empty($_POST['tenant_id']) ? (int)$_POST['tenant_id'] : null;
            if ($siteId && !$tenantId) {
                $site = $this->siteModel->getSiteById($siteId);
                if ($site && $site['tenant_id']) {
                    $tenantId = (int)$site['tenant_id'];
                }
            }
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'host' => trim($_POST['host'] ?? ''),
                'port' => (int)($_POST['port'] ?? 443),
                'site_id' => $siteId,
                'tenant_id' => $tenantId,
                'ip_address_id' => !empty($_POST['ip_address_id']) ? (int)$_POST['ip_address_id'] : null,
                'description' => trim($_POST['description'] ?? '') ?: null,
                'hypervisor_type' => $_POST['hypervisor_type'] ?? 'esxi',
                'discovery_interval_hours' => (int)($_POST['discovery_interval_hours'] ?? 1)
            ];
            if (empty($data['name']) || empty($data['host'])) {
                $this->loadView('hardware/esxi/create', ['error' => 'Nom et hôte requis', 'sites' => $sites, 'tenants' => $tenants, 'ipAddresses' => $ipAddresses]);
                return;
            }
            try {
                $id = $this->esxiModel->create($data);
                if (!empty($_POST['cred_username']) && $_POST['cred_password'] !== '') {
                    $this->esxiModel->saveCredentials($id, trim($_POST['cred_username']), $_POST['cred_password']);
                }
                $_SESSION['flash_message'] = 'Hôte ESXi ajouté avec succès';
                $_SESSION['flash_type'] = 'success';
                header('Location: ?page=hardware&section=esxi');
                exit;
            } catch (Exception $e) {
                $this->loadView('hardware/esxi/create', ['error' => $e->getMessage(), 'sites' => $sites, 'tenants' => $tenants, 'ipAddresses' => $ipAddresses]);
                return;
            }
        }
        $this->loadView('hardware/esxi/create', ['sites' => $sites, 'tenants' => $tenants, 'ipAddresses' => $ipAddresses]);
    }

    private function editEsxi() {
        $id = $_GET['id'] ?? 0;
        $esxi = $this->esxiModel->getById($id);
        if (!$esxi) {
            header('Location: ?page=hardware&section=esxi');
            exit;
        }
        $sites = $this->siteModel->getSitesForDropdown();
        $tenants = $this->tenantModel->getAll();
        $ipAddresses = $this->ipAddressModel->getAll();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'host' => trim($_POST['host'] ?? ''),
                'port' => (int)($_POST['port'] ?? 443),
                'site_id' => !empty($_POST['site_id']) ? (int)$_POST['site_id'] : null,
                'tenant_id' => !empty($_POST['tenant_id']) ? (int)$_POST['tenant_id'] : null,
                'ip_address_id' => !empty($_POST['ip_address_id']) ? (int)$_POST['ip_address_id'] : null,
                'description' => trim($_POST['description'] ?? '') ?: null,
                'hypervisor_type' => $_POST['hypervisor_type'] ?? 'esxi',
                'discovery_interval_hours' => (int)($_POST['discovery_interval_hours'] ?? 1)
            ];
            try {
                $this->esxiModel->update($id, $data);
                if (isset($_POST['cred_username']) && !empty(trim($_POST['cred_username'])) && $_POST['cred_password'] !== '') {
                    $this->esxiModel->saveCredentials($id, trim($_POST['cred_username']), $_POST['cred_password']);
                }
                $_SESSION['flash_message'] = 'Hôte ESXi mis à jour';
                $_SESSION['flash_type'] = 'success';
                header('Location: ?page=hardware&section=esxi');
                exit;
            } catch (Exception $e) {
                $this->loadView('hardware/esxi/edit', ['esxi' => array_merge($esxi, $_POST), 'error' => $e->getMessage(), 'sites' => $sites, 'tenants' => $tenants, 'ipAddresses' => $ipAddresses]);
                return;
            }
        }
        $esxi['has_credentials'] = $this->esxiModel->hasCredentials($id);
        $esxi['cred_username'] = $this->esxiModel->getCredentialsUsername($id);
        $this->loadView('hardware/esxi/edit', ['esxi' => $esxi, 'sites' => $sites, 'tenants' => $tenants, 'ipAddresses' => $ipAddresses]);
    }

    private function deleteEsxi() {
        $id = $_GET['id'] ?? 0;
        try {
            $this->esxiModel->delete($id);
            $_SESSION['flash_message'] = 'Hôte ESXi supprimé';
            $_SESSION['flash_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['flash_message'] = 'Erreur: ' . $e->getMessage();
            $_SESSION['flash_type'] = 'error';
        }
        header('Location: ?page=hardware&section=esxi');
        exit;
    }

    private function discoverEsxi() {
        header('Content-Type: application/json; charset=utf-8');
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'ID requis']);
            return;
        }
        try {
            $jobId = $this->esxiModel->createDiscoveryJob($id);
            if (ob_get_level()) ob_end_clean();
            echo json_encode([
                'success' => true,
                'mode' => 'agent',
                'job_id' => $jobId,
                'message' => "Job créé. L'agent sur le site effectuera la découverte ESXi (hosts, VMs, datastores)."
            ]);
        } catch (Exception $e) {
            if (ob_get_level()) ob_end_clean();
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    private function linkEsxiVm() {
        header('Content-Type: application/json; charset=utf-8');
        $vmId = (int)($_POST['vm_id'] ?? 0);
        $serverId = !empty($_POST['server_id']) ? (int)$_POST['server_id'] : null;
        if (!$vmId) {
            echo json_encode(['success' => false, 'error' => 'vm_id requis']);
            return;
        }
        try {
            $this->esxiModel->linkVmToServer($vmId, $serverId);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }
}
