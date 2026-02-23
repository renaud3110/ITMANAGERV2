<?php

require_once 'models/Server.php';
require_once 'models/Site.php';
require_once 'models/Tenant.php';
require_once 'models/OperatingSystem.php';
require_once 'models/IpAddress.php';
require_once 'models/Model.php';

class ServersController extends BaseController 
{
    private $serverModel;
    private $siteModel;
    private $tenantModel;
    private $osModel;
    private $ipModel;
    private $modelModel;

    public function __construct() 
    {
        parent::__construct();
        $this->serverModel = new Server();
        $this->siteModel = new Site();
        $this->tenantModel = new Tenant();
        $this->osModel = new OperatingSystem();
        $this->ipModel = new IpAddress();
        $this->modelModel = new Model();
    }

    public function index() 
    {
        $this->handleTenantSiteSelection();
        
        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        $currentSite = $_SESSION['current_site'] ?? 'all';
        
        $servers = $this->serverModel->getAll($currentTenant, $currentSite);
        $totalServers = $this->serverModel->getCount($currentTenant, $currentSite);
        
        $this->loadView('servers/index', [
            'servers' => $servers,
            'totalServers' => $totalServers
        ]);
    }

    public function view() 
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            header('Location: ?page=servers');
            exit;
        }
        
        $server = $this->serverModel->getById($id);
        
        if (!$server) {
            $_SESSION['error'] = 'Serveur non trouvé.';
            header('Location: ?page=servers');
            exit;
        }
        
        $this->loadView('servers/view', [
            'server' => $server
        ]);
    }

    public function create() 
    {
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'type' => $_POST['type'] ?? 'Physique',
                'site_id' => $_POST['site_id'] ?? null,
                'model_id' => $_POST['model_id'] ?? null,
                'processor_model' => $_POST['processor_model'] ?? '',
                'ram_total_gb' => $_POST['ram_total_gb'] ?? null,
                'ram_used_gb' => $_POST['ram_used_gb'] ?? null,
                'operating_system_id' => $_POST['operating_system_id'] ?? null,
                'ip_address_id' => $_POST['ip_address_id'] ?? null,
                'hostname' => $_POST['hostname'] ?? '',
                'teamviewer_id' => $_POST['teamviewer_id'] ?? ''
            ];
            
            if ($this->serverModel->create($data)) {
                $_SESSION['success'] = 'Serveur créé avec succès.';
                header('Location: ?page=servers');
                exit;
            } else {
                $_SESSION['error'] = 'Erreur lors de la création du serveur.';
            }
        }
        
        $sites = $this->siteModel->getAllSites();
        $operatingSystems = $this->osModel->getAll();
        $ipAddresses = $this->ipModel->getAll();
        $models = $this->modelModel->getAll();
        
        $this->loadView('servers/create', [
            'sites' => $sites,
            'operatingSystems' => $operatingSystems,
            'ipAddresses' => $ipAddresses,
            'models' => $models
        ]);
    }

    public function edit() 
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            header('Location: ?page=servers');
            exit;
        }
        
        $server = $this->serverModel->getById($id);
        
        if (!$server) {
            $_SESSION['error'] = 'Serveur non trouvé.';
            header('Location: ?page=servers');
            exit;
        }
        
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'type' => $_POST['type'] ?? 'Physique',
                'site_id' => $_POST['site_id'] ?? null,
                'model_id' => $_POST['model_id'] ?? null,
                'processor_model' => $_POST['processor_model'] ?? '',
                'ram_total_gb' => $_POST['ram_total_gb'] ?? null,
                'ram_used_gb' => $_POST['ram_used_gb'] ?? null,
                'operating_system_id' => $_POST['operating_system_id'] ?? null,
                'ip_address_id' => $_POST['ip_address_id'] ?? null,
                'hostname' => $_POST['hostname'] ?? '',
                'teamviewer_id' => $_POST['teamviewer_id'] ?? ''
            ];
            
            if ($this->serverModel->update($id, $data)) {
                $_SESSION['success'] = 'Serveur modifié avec succès.';
                header('Location: ?page=servers&action=view&id=' . $id);
                exit;
            } else {
                $_SESSION['error'] = 'Erreur lors de la modification du serveur.';
            }
        }
        
        $sites = $this->siteModel->getAllSites();
        $operatingSystems = $this->osModel->getAll();
        $ipAddresses = $this->ipModel->getAll();
        $models = $this->modelModel->getAll();
        
        $this->loadView('servers/edit', [
            'server' => $server,
            'sites' => $sites,
            'operatingSystems' => $operatingSystems,
            'ipAddresses' => $ipAddresses,
            'models' => $models
        ]);
    }

    public function delete() 
    {
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            header('Location: ?page=servers');
            exit;
        }
        
        if ($this->serverModel->delete($id)) {
            $_SESSION['success'] = 'Serveur supprimé avec succès.';
        } else {
            $_SESSION['error'] = 'Erreur lors de la suppression du serveur.';
        }
        
        header('Location: ?page=servers');
        exit;
    }
}
?> 