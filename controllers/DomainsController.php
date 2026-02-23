<?php

require_once 'controllers/BaseController.php';
require_once 'models/Domain.php';
require_once 'models/Tenant.php';

class DomainsController extends BaseController {
    private $domainModel;

    public function __construct() {
        parent::__construct();
        $this->handleTenantSiteSelection();
        $this->domainModel = new Domain();
    }

    public function index() {
        // Si on vient de traiter un changement de tenant/site, rediriger pour éviter la re-soumission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['change_tenant']) || isset($_POST['change_site']))) {
            header('Location: ?page=domains');
            exit;
        }
        
        // Récupérer le tenant actuel depuis la session
        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        
        try {
            // 1. Statistiques générales des domaines
            $stats = $this->domainModel->getStatistics($currentTenant);
            
            // 2. Liste des domaines
            $search = $_GET['search'] ?? null;
            if ($search) {
                $domains = $this->domainModel->search($search, $currentTenant);
            } else {
                $domains = $this->domainModel->getAll($currentTenant);
            }
            
            // 3. Domaines qui expirent bientôt
            $expiringSoon = $this->domainModel->getExpiringSoon($currentTenant);
            
            // 4. Domaines expirés
            $expired = $this->domainModel->getExpired($currentTenant);
            
            // 5. Hébergeurs/Registrars
            $hostingProviders = $this->domainModel->getHostingProviders($currentTenant);

            // Récupérer le nom du tenant pour l'affichage
            $tenantName = 'Tous les tenants';
            if ($currentTenant !== 'all') {
                $tenantModel = new Tenant();
                $tenant = $tenantModel->getById($currentTenant);
                $tenantName = $tenant ? $tenant['name'] : 'Tenant #' . $currentTenant;
            }

            $this->loadView('domains/index', [
                'stats' => $stats,
                'domains' => $domains,
                'expiringSoon' => $expiringSoon,
                'expired' => $expired,
                'hostingProviders' => $hostingProviders,
                'tenantName' => $tenantName,
                'currentTenant' => $currentTenant,
                'search' => $search
            ]);

        } catch (Exception $e) {
            error_log("Erreur Domaines: " . $e->getMessage());
            $this->loadView('domains/index', [
                'error' => 'Une erreur est survenue lors du chargement des données des domaines.',
                'stats' => ['total_domains' => 0, 'managed_domains' => 0, 'unmanaged_domains' => 0, 'auto_renewal_domains' => 0, 'expired_domains' => 0, 'expiring_soon_domains' => 0],
                'domains' => [],
                'expiringSoon' => [],
                'expired' => [],
                'hostingProviders' => [],
                'tenantName' => 'Erreur',
                'currentTenant' => $currentTenant,
                'search' => null
            ]);
        }
    }

    public function create() {
        $this->handleTenantSiteSelection();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validation
                $errors = [];
                
                if (empty($_POST['domain_name'])) {
                    $errors[] = 'Le nom de domaine est requis.';
                } elseif (!filter_var($_POST['domain_name'], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                    $errors[] = 'Le nom de domaine n\'est pas valide.';
                }
                
                if (empty($_POST['tenant_id'])) {
                    $errors[] = 'Le tenant est requis.';
                }
                
                // Vérifier si le domaine existe déjà
                if (!empty($_POST['domain_name']) && !empty($_POST['tenant_id'])) {
                    if ($this->domainModel->exists($_POST['domain_name'], $_POST['tenant_id'])) {
                        $errors[] = 'Ce domaine existe déjà pour ce tenant.';
                    }
                }
                
                // Validation de la date d'expiration
                if (!empty($_POST['expiry_date']) && !strtotime($_POST['expiry_date'])) {
                    $errors[] = 'La date d\'expiration n\'est pas valide.';
                }
                
                if (empty($errors)) {
                    $data = [
                        'domain_name' => trim($_POST['domain_name']),
                        'tenant_id' => $_POST['tenant_id'],
                        'is_managed' => isset($_POST['is_managed']) ? 1 : 0,
                        'expiry_date' => !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null,
                        'hosting_provider' => trim($_POST['hosting_provider']) ?: null,
                        'auto_renewal' => isset($_POST['auto_renewal']) ? 1 : 0
                    ];
                    
                    if ($this->domainModel->create($data)) {
                        $_SESSION['success_message'] = 'Domaine créé avec succès.';
                        header('Location: ?page=domains');
                        exit;
                    } else {
                        $errors[] = 'Erreur lors de la création du domaine.';
                    }
                }
                
                $_SESSION['error_message'] = implode('<br>', $errors);
                
            } catch (Exception $e) {
                error_log("Erreur création domaine: " . $e->getMessage());
                $_SESSION['error_message'] = 'Une erreur est survenue lors de la création du domaine.';
            }
        }
        
        // Récupérer les tenants pour le formulaire
        $tenantModel = new Tenant();
        $tenants = $tenantModel->getAll();
        
        $this->loadView('domains/create', [
            'tenants' => $tenants,
            'currentTenant' => $_SESSION['current_tenant'] ?? 'all'
        ]);
    }

    public function edit() {
        $this->handleTenantSiteSelection();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error_message'] = 'ID du domaine manquant.';
            header('Location: ?page=domains');
            exit;
        }
        
        $domain = $this->domainModel->getById($id);
        if (!$domain) {
            $_SESSION['error_message'] = 'Domaine non trouvé.';
            header('Location: ?page=domains');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validation
                $errors = [];
                
                if (empty($_POST['domain_name'])) {
                    $errors[] = 'Le nom de domaine est requis.';
                } elseif (!filter_var($_POST['domain_name'], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                    $errors[] = 'Le nom de domaine n\'est pas valide.';
                }
                
                if (empty($_POST['tenant_id'])) {
                    $errors[] = 'Le tenant est requis.';
                }
                
                // Vérifier si le domaine existe déjà (en excluant le domaine actuel)
                if (!empty($_POST['domain_name']) && !empty($_POST['tenant_id'])) {
                    if ($this->domainModel->exists($_POST['domain_name'], $_POST['tenant_id'], $id)) {
                        $errors[] = 'Ce domaine existe déjà pour ce tenant.';
                    }
                }
                
                // Validation de la date d'expiration
                if (!empty($_POST['expiry_date']) && !strtotime($_POST['expiry_date'])) {
                    $errors[] = 'La date d\'expiration n\'est pas valide.';
                }
                
                if (empty($errors)) {
                    $data = [
                        'domain_name' => trim($_POST['domain_name']),
                        'tenant_id' => $_POST['tenant_id'],
                        'is_managed' => isset($_POST['is_managed']) ? 1 : 0,
                        'expiry_date' => !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null,
                        'hosting_provider' => trim($_POST['hosting_provider']) ?: null,
                        'auto_renewal' => isset($_POST['auto_renewal']) ? 1 : 0
                    ];
                    
                    if ($this->domainModel->update($id, $data)) {
                        $_SESSION['success_message'] = 'Domaine modifié avec succès.';
                        header('Location: ?page=domains');
                        exit;
                    } else {
                        $errors[] = 'Erreur lors de la modification du domaine.';
                    }
                }
                
                $_SESSION['error_message'] = implode('<br>', $errors);
                
            } catch (Exception $e) {
                error_log("Erreur modification domaine: " . $e->getMessage());
                $_SESSION['error_message'] = 'Une erreur est survenue lors de la modification du domaine.';
            }
        }
        
        // Récupérer les tenants pour le formulaire
        $tenantModel = new Tenant();
        $tenants = $tenantModel->getAll();
        
        $this->loadView('domains/edit', [
            'domain' => $domain,
            'tenants' => $tenants
        ]);
    }

    public function delete() {
        $this->handleTenantSiteSelection();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error_message'] = 'ID du domaine manquant.';
            header('Location: ?page=domains');
            exit;
        }
        
        $domain = $this->domainModel->getById($id);
        if (!$domain) {
            $_SESSION['error_message'] = 'Domaine non trouvé.';
            header('Location: ?page=domains');
            exit;
        }
        
        try {
            if ($this->domainModel->delete($id)) {
                $_SESSION['success_message'] = 'Domaine "' . $domain['domain_name'] . '" supprimé avec succès.';
            } else {
                $_SESSION['error_message'] = 'Erreur lors de la suppression du domaine.';
            }
        } catch (Exception $e) {
            error_log("Erreur suppression domaine: " . $e->getMessage());
            $_SESSION['error_message'] = 'Une erreur est survenue lors de la suppression du domaine.';
        }
        
        header('Location: ?page=domains');
        exit;
    }

    /**
     * Action AJAX pour rechercher des domaines
     */
    public function searchDomains() {
        header('Content-Type: application/json');
        
        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        $search = $_GET['q'] ?? '';
        
        try {
            $domains = $this->domainModel->search($search, $currentTenant);
            echo json_encode(['domains' => $domains]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la recherche: ' . $e->getMessage()]);
        }
    }

    /**
     * Action AJAX pour obtenir les statistiques mises à jour
     */
    public function getStatistics() {
        header('Content-Type: application/json');
        
        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        
        try {
            $stats = $this->domainModel->getStatistics($currentTenant);
            echo json_encode(['stats' => $stats]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la récupération: ' . $e->getMessage()]);
        }
    }
}