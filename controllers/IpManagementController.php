<?php

require_once 'models/IpAddress.php';
require_once 'models/Tenant.php';
require_once 'models/Site.php';

class IpManagementController extends BaseController {
    
    private $ipModel;
    
    public function __construct() {
        parent::__construct();
        $this->ipModel = new IpAddress();
    }

    /**
     * Page principale - Liste des adresses IP
     */
    public function index() {
        $this->handleTenantSiteSelection();
        
        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        $currentSite = $_SESSION['current_site'] ?? 'all';
        
        $currentTenantName = 'Tous';
        $currentSiteName = 'Tous les sites';
        if ($currentTenant !== 'all') {
            $tenant = (new Tenant())->getById($currentTenant);
            $currentTenantName = $tenant['name'] ?? "Tenant $currentTenant";
        }
        if ($currentSite !== 'all') {
            $site = (new Site())->getSiteById($currentSite);
            $currentSiteName = $site['name'] ?? "Site $currentSite";
        }
        
        // Récupérer les adresses IP selon le tenant/site sélectionné
        $ipAddresses = $this->ipModel->getAll($currentTenant, $currentSite);
        
        // Statistiques selon le contexte sélectionné
        $stats = $this->getStatistics($currentTenant, $currentSite);
        
        $flash = $this->getFlashMessage();
        
        $this->loadView('ip-management/index', [
            'ipAddresses' => $ipAddresses,
            'stats' => $stats,
            'currentTenant' => $currentTenant,
            'currentSite' => $currentSite,
            'currentTenantName' => $currentTenantName,
            'currentSiteName' => $currentSiteName,
            'flash' => $flash
        ]);
    }
    
    /**
     * Formulaire de création d'une nouvelle adresse IP
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreate();
            return;
        }
        
        // Récupérer les données pour les listes déroulantes
        $sites = $this->getSites();
        $tenants = $this->getTenants();
        
        $this->loadView('ip-management/create', [
            'sites' => $sites,
            'tenants' => $tenants
        ]);
    }
    
    /**
     * Traitement de la création d'une nouvelle adresse IP
     */
    private function handleCreate() {
        $ip_address = trim($_POST['ip_address'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $subnet_mask = trim($_POST['subnet_mask'] ?? '');
        $gateway = trim($_POST['gateway'] ?? '');
        $dns1 = trim($_POST['dns1'] ?? '');
        $dns2 = trim($_POST['dns2'] ?? '');
        $vlan_id = trim($_POST['vlan_id'] ?? '');
        $tenant_id = $_POST['tenant_id'] ?? ($_SESSION['current_tenant'] !== 'all' ? $_SESSION['current_tenant'] : 1);
        $site_id = $_POST['site_id'] ?? ($_SESSION['current_site'] !== 'all' ? $_SESSION['current_site'] : null);
        
        // Validation
        if (empty($ip_address)) {
            $sites = $this->getSites();
            $tenants = $this->getTenants();
            $this->loadView('ip-management/create', [
                'error' => 'L\'adresse IP est obligatoire',
                'sites' => $sites,
                'tenants' => $tenants
            ]);
            return;
        }
        
        // Valider le format de l'adresse IP
        if (!filter_var($ip_address, FILTER_VALIDATE_IP)) {
            $sites = $this->getSites();
            $tenants = $this->getTenants();
            $this->loadView('ip-management/create', [
                'error' => 'Format d\'adresse IP invalide',
                'sites' => $sites,
                'tenants' => $tenants
            ]);
            return;
        }
        
        $ipData = [
            'ip_address' => $ip_address,
            'description' => $description,
            'subnet_mask' => $subnet_mask,
            'gateway' => $gateway,
            'dns1' => $dns1,
            'dns2' => $dns2,
            'vlan_id' => $vlan_id,
            'tenant_id' => $tenant_id,
            'site_id' => $site_id
        ];
        
        try {
            $ipId = $this->ipModel->create($ipData);
            if ($ipId) {
                $this->redirectWithMessage('ip-management', 'index', 
                    'Adresse IP créée avec succès', 'success');
            } else {
                $sites = $this->getSites();
                $tenants = $this->getTenants();
                $this->loadView('ip-management/create', [
                    'error' => 'Erreur lors de la création de l\'adresse IP',
                    'sites' => $sites,
                    'tenants' => $tenants
                ]);
            }
        } catch (Exception $e) {
            $sites = $this->getSites();
            $tenants = $this->getTenants();
            $this->loadView('ip-management/create', [
                'error' => 'Erreur: ' . $e->getMessage(),
                'sites' => $sites,
                'tenants' => $tenants
            ]);
        }
    }
    
    /**
     * Formulaire d'édition d'une adresse IP
     */
    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirectWithMessage('ip-management', 'index', 'Adresse IP non trouvée', 'error');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEdit($id);
            return;
        }
        
        $ipAddress = $this->ipModel->getById($id);
        if (!$ipAddress) {
            $this->redirectWithMessage('ip-management', 'index', 'Adresse IP non trouvée', 'error');
            return;
        }
        
        $sites = $this->getSites();
        $tenants = $this->getTenants();
        
        $this->loadView('ip-management/edit', [
            'ipAddress' => $ipAddress,
            'sites' => $sites,
            'tenants' => $tenants
        ]);
    }
    
    /**
     * Traitement de l'édition d'une adresse IP
     */
    private function handleEdit($id) {
        $ip_address = trim($_POST['ip_address'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $subnet_mask = trim($_POST['subnet_mask'] ?? '');
        $gateway = trim($_POST['gateway'] ?? '');
        $dns1 = trim($_POST['dns1'] ?? '');
        $dns2 = trim($_POST['dns2'] ?? '');
        $vlan_id = trim($_POST['vlan_id'] ?? '');
        $tenant_id = $_POST['tenant_id'] ?? null;
        $site_id = $_POST['site_id'] ?? null;
        
        // Validation
        if (empty($ip_address)) {
            $ipAddress = $this->ipModel->getById($id);
            $sites = $this->getSites();
            $tenants = $this->getTenants();
            $this->loadView('ip-management/edit', [
                'error' => 'L\'adresse IP est obligatoire',
                'ipAddress' => $ipAddress,
                'sites' => $sites,
                'tenants' => $tenants
            ]);
            return;
        }
        
        // Valider le format de l'adresse IP
        if (!filter_var($ip_address, FILTER_VALIDATE_IP)) {
            $ipAddress = $this->ipModel->getById($id);
            $sites = $this->getSites();
            $tenants = $this->getTenants();
            $this->loadView('ip-management/edit', [
                'error' => 'Format d\'adresse IP invalide',
                'ipAddress' => $ipAddress,
                'sites' => $sites,
                'tenants' => $tenants
            ]);
            return;
        }
        
        $ipData = [
            'ip_address' => $ip_address,
            'description' => $description,
            'subnet_mask' => $subnet_mask,
            'gateway' => $gateway,
            'dns1' => $dns1,
            'dns2' => $dns2,
            'vlan_id' => $vlan_id,
            'tenant_id' => $tenant_id,
            'site_id' => $site_id
        ];
        
        try {
            if ($this->ipModel->update($id, $ipData)) {
                $this->redirectWithMessage('ip-management', 'index', 
                    'Adresse IP modifiée avec succès', 'success');
            } else {
                $ipAddress = $this->ipModel->getById($id);
                $sites = $this->getSites();
                $tenants = $this->getTenants();
                $this->loadView('ip-management/edit', [
                    'error' => 'Erreur lors de la modification de l\'adresse IP',
                    'ipAddress' => $ipAddress,
                    'sites' => $sites,
                    'tenants' => $tenants
                ]);
            }
        } catch (Exception $e) {
            $ipAddress = $this->ipModel->getById($id);
            $sites = $this->getSites();
            $tenants = $this->getTenants();
            $this->loadView('ip-management/edit', [
                'error' => 'Erreur: ' . $e->getMessage(),
                'ipAddress' => $ipAddress,
                'sites' => $sites,
                'tenants' => $tenants
            ]);
        }
    }
    
    /**
     * Suppression d'une adresse IP
     */
    public function delete() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirectWithMessage('ip-management', 'index', 'Adresse IP non trouvée', 'error');
            return;
        }
        
        try {
            if ($this->ipModel->delete($id)) {
                $this->redirectWithMessage('ip-management', 'index', 
                    'Adresse IP supprimée avec succès', 'success');
            } else {
                $this->redirectWithMessage('ip-management', 'index', 
                    'Erreur lors de la suppression de l\'adresse IP', 'error');
            }
        } catch (Exception $e) {
            $this->redirectWithMessage('ip-management', 'index', 
                'Erreur: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Obtenir les statistiques des adresses IP
     */
    private function getStatistics($tenant_id = null, $site_id = null) {
        return [
            'total' => $this->ipModel->getTotalCount($tenant_id, $site_id),
            'used' => $this->ipModel->getUsedCount($tenant_id, $site_id),
            'available' => $this->ipModel->getAvailableCount($tenant_id, $site_id),
            'subnets' => $this->ipModel->getSubnetCount($tenant_id, $site_id)
        ];
    }
    
    /**
     * Récupérer la liste des sites
     */
    private function getSites() {
        $database = new Database();
        $db = $database->getConnection();
        $query = "SELECT id, name FROM sites ORDER BY name";
        $stmt = $db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupérer la liste des tenants
     */
    private function getTenants() {
        $database = new Database();
        $db = $database->getConnection();
        $query = "SELECT id, name FROM tenants ORDER BY name";
        $stmt = $db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Redirection avec message flash
     */
    protected function redirectWithMessage($page, $action = 'index', $message = '', $type = 'success') {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
        
        $url = "?page=$page";
        if ($action !== 'index') {
            $url .= "&action=$action";
        }
        
        $this->redirect(str_replace('?page=', '', $url));
    }
}
?> 