<?php

require_once 'controllers/BaseController.php';
require_once 'models/Microsoft365.php';
require_once 'models/Tenant.php';

class Microsoft365Controller extends BaseController {
    private $microsoft365Model;

    public function __construct() {
        parent::__construct();
        $this->handleTenantSiteSelection();
        $this->microsoft365Model = new Microsoft365();
    }

    public function index() {
        // Si on vient de traiter un changement de tenant/site, rediriger pour éviter la re-soumission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['change_tenant']) || isset($_POST['change_site']))) {
            header('Location: ?page=microsoft365');
            exit;
        }
        
        // Récupérer le tenant actuel depuis la session
        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        
        // Si aucun tenant n'est sélectionné, afficher un message d'erreur
        if ($currentTenant === 'all' || empty($currentTenant)) {
            $this->loadView('microsoft365/index', [
                'error' => 'Veuillez sélectionner un tenant pour accéder aux données Microsoft 365.',
                'showNoTenantMessage' => true
            ]);
            return;
        }

        // Récupérer les données pour le tenant sélectionné
        try {
            // 1. Statistiques générales des licences
            $licenseStats = $this->microsoft365Model->getLicenseStatistics($currentTenant);
            
            // 2. Statistiques des utilisateurs
            $userStats = $this->microsoft365Model->getUserStatistics($currentTenant);
            
            // 3. Résumé des SKU licences
            $subscribedSkus = $this->microsoft365Model->getSubscribedSkus($currentTenant);
            
            // 4. Liste des utilisateurs avec licences (avec recherche si fournie)
            $search = $_GET['search'] ?? null;
            $userLicenses = $this->microsoft365Model->getUserLicenses($currentTenant, $search);
            
            // 5. Top des licences les plus utilisées
            $topLicenses = $this->microsoft365Model->getTopLicenses($currentTenant);
            
            // 6. Renouvellements à venir
            $upcomingRenewals = $this->microsoft365Model->getUpcomingRenewals($currentTenant);

            // Récupérer le nom du tenant pour l'affichage
            $tenantModel = new Tenant();
            $tenant = $tenantModel->getById($currentTenant);
            $tenantName = $tenant ? $tenant['name'] : 'Tenant #' . $currentTenant;

            $this->loadView('microsoft365/index', [
                'licenseStats' => $licenseStats,
                'userStats' => $userStats,
                'subscribedSkus' => $subscribedSkus,
                'userLicenses' => $userLicenses,
                'topLicenses' => $topLicenses,
                'upcomingRenewals' => $upcomingRenewals,
                'tenantName' => $tenantName,
                'currentTenant' => $currentTenant,
                'search' => $search,
                'showNoTenantMessage' => false
            ]);

        } catch (Exception $e) {
            error_log("Erreur Microsoft 365: " . $e->getMessage());
            $this->loadView('microsoft365/index', [
                'error' => 'Une erreur est survenue lors du chargement des données Microsoft 365.',
                'showNoTenantMessage' => false
            ]);
        }
    }

    /**
     * Action AJAX pour rechercher des utilisateurs
     */
    public function searchUsers() {
        header('Content-Type: application/json');
        
        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        
        if ($currentTenant === 'all' || empty($currentTenant)) {
            http_response_code(400);
            echo json_encode(['error' => 'Aucun tenant sélectionné']);
            return;
        }

        $search = $_GET['q'] ?? '';
        
        try {
            $users = $this->microsoft365Model->getUserLicenses($currentTenant, $search);
            echo json_encode(['users' => $users]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la recherche: ' . $e->getMessage()]);
        }
    }

    /**
     * Action AJAX pour obtenir les détails d'un utilisateur
     */
    public function getUserDetails() {
        header('Content-Type: application/json');
        
        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        $userId = $_GET['user_id'] ?? null;
        
        if ($currentTenant === 'all' || empty($currentTenant)) {
            http_response_code(400);
            echo json_encode(['error' => 'Aucun tenant sélectionné']);
            return;
        }

        if (!$userId) {
            http_response_code(400);
            echo json_encode(['error' => 'ID utilisateur manquant']);
            return;
        }

        try {
            $userDetails = $this->microsoft365Model->getUserDetails($userId, $currentTenant);
            if ($userDetails) {
                echo json_encode($userDetails);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Utilisateur non trouvé']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la récupération: ' . $e->getMessage()]);
        }
    }

    /**
     * Action AJAX pour obtenir les statistiques mises à jour
     */
    public function getStatistics() {
        header('Content-Type: application/json');
        
        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        
        if ($currentTenant === 'all' || empty($currentTenant)) {
            http_response_code(400);
            echo json_encode(['error' => 'Aucun tenant sélectionné']);
            return;
        }

        try {
            $licenseStats = $this->microsoft365Model->getLicenseStatistics($currentTenant);
            $userStats = $this->microsoft365Model->getUserStatistics($currentTenant);
            
            echo json_encode([
                'licenseStats' => $licenseStats,
                'userStats' => $userStats
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur lors de la récupération: ' . $e->getMessage()]);
        }
    }
}