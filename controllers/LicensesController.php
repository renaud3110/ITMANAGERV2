<?php

require_once 'controllers/BaseController.php';
require_once 'models/License.php';
require_once 'models/Tenant.php';

class LicensesController extends BaseController {
    private $licenseModel;

    public function __construct() {
        parent::__construct();
        $this->handleTenantSiteSelection();
        $this->licenseModel = new License();
    }

    public function index() {
        // Si on vient de traiter un changement de tenant/site, rediriger pour éviter la re-soumission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['change_tenant']) || isset($_POST['change_site']))) {
            header('Location: ?page=licenses');
            exit;
        }
        
        // Récupérer le tenant actuel depuis la session
        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        
        try {
            // 1. Statistiques générales des licences
            $stats = $this->licenseModel->getStatistics($currentTenant);
            
            // 2. Liste des licences
            $search = $_GET['search'] ?? null;
            if ($search) {
                $licenses = $this->licenseModel->search($search, $currentTenant);
            } else {
                $licenses = $this->licenseModel->getAll($currentTenant);
            }
            
            // 3. Licences qui expirent bientôt
            $expiringSoon = $this->licenseModel->getExpiringSoon($currentTenant);
            
            // 4. Licences expirées
            $expired = $this->licenseModel->getExpired($currentTenant);
            
            // 5. Types de licences
            $licenseTypes = $this->licenseModel->getLicenseTypes($currentTenant);

            // Récupérer le nom du tenant pour l'affichage
            $tenantName = 'Tous les tenants';
            if ($currentTenant !== 'all') {
                $tenantModel = new Tenant();
                $tenant = $tenantModel->getById($currentTenant);
                $tenantName = $tenant ? $tenant['name'] : 'Tenant #' . $currentTenant;
            }

            $this->loadView('licenses/index', [
                'stats' => $stats,
                'licenses' => $licenses,
                'expiringSoon' => $expiringSoon,
                'expired' => $expired,
                'licenseTypes' => $licenseTypes,
                'tenantName' => $tenantName,
                'currentTenant' => $currentTenant,
                'search' => $search
            ]);

        } catch (Exception $e) {
            error_log("Erreur Licences: " . $e->getMessage());
            $this->loadView('licenses/index', [
                'error' => 'Une erreur est survenue lors du chargement des données des licences.',
                'stats' => ['total_licenses' => 0, 'total_license_count' => 0, 'licenses_with_login' => 0, 'licenses_with_password' => 0, 'expired_licenses' => 0, 'expiring_soon_licenses' => 0, 'avg_license_count' => 0],
                'licenses' => [],
                'expiringSoon' => [],
                'expired' => [],
                'licenseTypes' => [],
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
                
                if (empty($_POST['license_name'])) {
                    $errors[] = 'Le nom de la licence est requis.';
                }
                
                if (empty($_POST['tenant_id'])) {
                    $errors[] = 'Le tenant est requis.';
                }
                
                // Vérifier si la licence existe déjà
                if (!empty($_POST['license_name']) && !empty($_POST['tenant_id'])) {
                    if ($this->licenseModel->exists($_POST['license_name'], $_POST['tenant_id'])) {
                        $errors[] = 'Cette licence existe déjà pour ce tenant.';
                    }
                }
                
                // Validation du nombre de licences
                if (!empty($_POST['license_count']) && (!is_numeric($_POST['license_count']) || intval($_POST['license_count']) < 1)) {
                    $errors[] = 'Le nombre de licences doit être un nombre entier positif.';
                }
                
                // Validation de la date d'expiration
                if (!empty($_POST['expiry_date']) && !strtotime($_POST['expiry_date'])) {
                    $errors[] = 'La date d\'expiration n\'est pas valide.';
                }
                
                if (empty($errors)) {
                    $data = [
                        'tenant_id' => $_POST['tenant_id'],
                        'license_name' => trim($_POST['license_name']),
                        'login' => trim($_POST['login']) ?: null,
                        'password' => $_POST['password'] ?: null,
                        'license_count' => intval($_POST['license_count']) ?: 1,
                        'expiry_date' => !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null,
                        'description' => trim($_POST['description']) ?: null
                    ];
                    
                    if ($this->licenseModel->create($data)) {
                        $_SESSION['success_message'] = 'Licence créée avec succès.';
                        header('Location: ?page=licenses');
                        exit;
                    } else {
                        $errors[] = 'Erreur lors de la création de la licence.';
                    }
                }
                
                $_SESSION['error_message'] = implode('<br>', $errors);
                
            } catch (Exception $e) {
                error_log("Erreur création licence: " . $e->getMessage());
                $_SESSION['error_message'] = 'Une erreur est survenue lors de la création de la licence.';
            }
        }
        
        // Récupérer les tenants pour le formulaire
        $tenantModel = new Tenant();
        $tenants = $tenantModel->getAll();
        
        $this->loadView('licenses/create', [
            'tenants' => $tenants,
            'currentTenant' => $_SESSION['current_tenant'] ?? 'all'
        ]);
    }

    public function edit() {
        $this->handleTenantSiteSelection();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error_message'] = 'ID de la licence manquant.';
            header('Location: ?page=licenses');
            exit;
        }
        
        $license = $this->licenseModel->getById($id);
        if (!$license) {
            $_SESSION['error_message'] = 'Licence non trouvée.';
            header('Location: ?page=licenses');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Validation
                $errors = [];
                
                if (empty($_POST['license_name'])) {
                    $errors[] = 'Le nom de la licence est requis.';
                }
                
                if (empty($_POST['tenant_id'])) {
                    $errors[] = 'Le tenant est requis.';
                }
                
                // Vérifier si la licence existe déjà (en excluant la licence actuelle)
                if (!empty($_POST['license_name']) && !empty($_POST['tenant_id'])) {
                    if ($this->licenseModel->exists($_POST['license_name'], $_POST['tenant_id'], $id)) {
                        $errors[] = 'Cette licence existe déjà pour ce tenant.';
                    }
                }
                
                // Validation du nombre de licences
                if (!empty($_POST['license_count']) && (!is_numeric($_POST['license_count']) || intval($_POST['license_count']) < 1)) {
                    $errors[] = 'Le nombre de licences doit être un nombre entier positif.';
                }
                
                // Validation de la date d'expiration
                if (!empty($_POST['expiry_date']) && !strtotime($_POST['expiry_date'])) {
                    $errors[] = 'La date d\'expiration n\'est pas valide.';
                }
                
                if (empty($errors)) {
                    $data = [
                        'tenant_id' => $_POST['tenant_id'],
                        'license_name' => trim($_POST['license_name']),
                        'login' => trim($_POST['login']) ?: null,
                        'license_count' => intval($_POST['license_count']) ?: 1,
                        'expiry_date' => !empty($_POST['expiry_date']) ? $_POST['expiry_date'] : null,
                        'description' => trim($_POST['description']) ?: null
                    ];
                    
                    // Ajouter le mot de passe seulement s'il est fourni
                    if (!empty($_POST['password'])) {
                        $data['password'] = $_POST['password'];
                    }
                    
                    if ($this->licenseModel->update($id, $data)) {
                        $_SESSION['success_message'] = 'Licence modifiée avec succès.';
                        header('Location: ?page=licenses');
                        exit;
                    } else {
                        $errors[] = 'Erreur lors de la modification de la licence.';
                    }
                }
                
                $_SESSION['error_message'] = implode('<br>', $errors);
                
            } catch (Exception $e) {
                error_log("Erreur modification licence: " . $e->getMessage());
                $_SESSION['error_message'] = 'Une erreur est survenue lors de la modification de la licence.';
            }
        }
        
        // Récupérer les tenants pour le formulaire
        $tenantModel = new Tenant();
        $tenants = $tenantModel->getAll();
        
        $this->loadView('licenses/edit', [
            'license' => $license,
            'tenants' => $tenants
        ]);
    }

    public function delete() {
        $this->handleTenantSiteSelection();
        
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $_SESSION['error_message'] = 'ID de la licence manquant.';
            header('Location: ?page=licenses');
            exit;
        }
        
        $license = $this->licenseModel->getById($id);
        if (!$license) {
            $_SESSION['error_message'] = 'Licence non trouvée.';
            header('Location: ?page=licenses');
            exit;
        }
        
        try {
            if ($this->licenseModel->delete($id)) {
                $_SESSION['success_message'] = 'Licence "' . $license['license_name'] . '" supprimée avec succès.';
            } else {
                $_SESSION['error_message'] = 'Erreur lors de la suppression de la licence.';
            }
        } catch (Exception $e) {
            error_log("Erreur suppression licence: " . $e->getMessage());
            $_SESSION['error_message'] = 'Une erreur est survenue lors de la suppression de la licence.';
        }
        
        header('Location: ?page=licenses');
        exit;
    }

    /**
     * Action AJAX pour récupérer le mot de passe décrypté
     */
    public function getPassword() {
        header('Content-Type: application/json');
        
        // Vérifier les permissions d'administration
        if (!$this->isGlobalAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Accès non autorisé']);
            return;
        }
        
        $licenseId = $_GET['license_id'] ?? null;
        if (!$licenseId) {
            http_response_code(400);
            echo json_encode(['error' => 'ID de licence manquant']);
            return;
        }
        
        try {
            $password = $this->licenseModel->getDecryptedPassword($licenseId);
            if ($password !== null) {
                echo json_encode(['password' => $password]);
            } else {
                echo json_encode(['password' => '']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la récupération du mot de passe']);
        }
    }

    /**
     * Action AJAX pour rechercher des licences
     */
    public function searchLicenses() {
        header('Content-Type: application/json');
        
        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        $search = $_GET['q'] ?? '';
        
        try {
            $licenses = $this->licenseModel->search($search, $currentTenant);
            echo json_encode(['licenses' => $licenses]);
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
            $stats = $this->licenseModel->getStatistics($currentTenant);
            echo json_encode(['stats' => $stats]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la récupération: ' . $e->getMessage()]);
        }
    }
}