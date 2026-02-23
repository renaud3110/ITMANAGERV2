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
    }

    public function index() {
        $this->handleTenantSiteSelection();
        
        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        $currentSite = $_SESSION['current_site'] ?? 'all';
        
        // Obtenir seulement les statistiques pour la page d'accueil
        $computersCount = $this->computerModel->getCount($currentTenant, $currentSite);
        $serversCount = $this->serverModel->getCount($currentTenant, $currentSite);
        
        $this->loadView('hardware/index', [
            'computersCount' => $computersCount,
            'serversCount' => $serversCount,
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
        
        // Récupérer les logiciels installés
        $installedSoftware = $this->getInstalledSoftware($id);
        
        $this->loadView('hardware/computers/view', [
            'computer' => $computer,
            'disks' => $disks,
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
                'teamviewer_id' => $_POST['teamviewer_id'],
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
                    'teamviewer_id' => $_POST['teamviewer_id'],
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
        header('Content-Type: application/json');
        
        $pcId = $_GET['pc_id'] ?? null;
        if (!$pcId) {
            echo json_encode(['error' => 'PC ID manquant']);
            return;
        }

        try {
            $db = new Database();
            $query = "SELECT temperature, created_at 
                     FROM cpu_temperatures 
                     WHERE pc_id = ? 
                     ORDER BY created_at DESC 
                     LIMIT 100";
            
            $temperatures = $db->fetchAll($query, [$pcId]);
            
            // Formater les données pour le graphique
            $data = [
                'labels' => [],
                'temperatures' => []
            ];
            
            foreach ($temperatures as $temp) {
                $data['labels'][] = date('H:i', strtotime($temp['created_at']));
                $data['temperatures'][] = floatval($temp['temperature']);
            }
            
            // Inverser les tableaux pour avoir les données dans l'ordre chronologique
            $data['labels'] = array_reverse($data['labels']);
            $data['temperatures'] = array_reverse($data['temperatures']);
            
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
}
