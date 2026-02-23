<?php

require_once 'models/Tenant.php';
require_once 'models/Site.php';
require_once 'models/User.php';
require_once 'models/Person.php';

class DashboardController extends BaseController {

    public function index() {
        try {
            $this->handleTenantSiteSelection();

            // Initialiser les modèles
            $tenantModel = new Tenant();
            $siteModel = new Site();
            $userModel = new User();
            $personModel = new Person();

            $currentTenant = $_SESSION['current_tenant'] ?? 'all';
            $currentSite = $_SESSION['current_site'] ?? 'all';

            // Calculer les statistiques selon le tenant/site sélectionné
            if ($currentTenant === 'all') {
                $totalTenants = count($tenantModel->getAll());
                $totalSites = count($siteModel->getAllSites());
                $totalPersons = count($personModel->getAll());
            } else {
                $totalTenants = 1;
                $totalSites = count($siteModel->getSitesByTenant($currentTenant));
                $totalPersons = count($personModel->getAll($currentTenant));
            }

            $totalUsers = count($userModel->getAllUsers());

            // Statistiques supplémentaires
            $stats = [
                'tenants' => $totalTenants,
                'sites' => $totalSites,
                'users' => $totalUsers,
                'persons' => $totalPersons
            ];

            // Récupérer quelques données récentes pour l'affichage
            $recentPersons = array_slice($personModel->getAll(), 0, 5);
            
            // Récupérer les tenants et sites pour l'affichage du contexte
            $tenants = $tenantModel->getAll();
            $sites = $siteModel->getAllSites();

            $flash = $this->getFlashMessage();

            $this->loadView('dashboard/index', [
                'stats' => $stats,
                'recentPersons' => $recentPersons,
                'tenants' => $tenants,
                'sites' => $sites,
                'flash' => $flash,
                'currentTenant' => $currentTenant,
                'currentSite' => $currentSite
            ]);
            
        } catch (Exception $e) {
            error_log("Erreur Dashboard: " . $e->getMessage());
            
            // Affichage d'urgence en cas d'erreur
            $stats = [
                'tenants' => 0,
                'sites' => 0,
                'users' => 0,
                'persons' => 0
            ];
            
            $this->loadView('dashboard/index', [
                'stats' => $stats,
                'recentPersons' => [],
                'flash' => ['type' => 'error', 'message' => 'Erreur lors du chargement du tableau de bord'],
                'currentTenant' => 'all',
                'currentSite' => 'all'
            ]);
        }
    }
}
