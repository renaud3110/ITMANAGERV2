<?php

require_once 'controllers/BaseController.php';
require_once 'models/DsdFactures.php';

class ToolsController extends BaseController {
    private $dsdFactures;

    public function __construct() {
        $this->dsdFactures = new DsdFactures();
    }

    public function index() {
        $this->requireAdmin();
        $this->handleTenantSiteSelection();

        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        
        // Récupérer les statistiques globales
        $globalStats = $this->dsdFactures->getGlobalStats();
        
        // Récupérer les tenants avec des factures
        $tenantsWithFactures = $this->dsdFactures->getTenantsWithFactures();

        $this->loadView('tools/index', [
            'globalStats' => $globalStats,
            'tenantsWithFactures' => $tenantsWithFactures,
            'currentTenant' => $currentTenant
        ]);
    }

    public function dsdFactures() {
        $this->requireAdmin();
        $this->handleTenantSiteSelection();

        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        
        // Récupérer le nom DSD du tenant sélectionné
        $tenantDsdName = null;
        if ($currentTenant !== 'all') {
            require_once 'models/Tenant.php';
            $tenantModel = new Tenant();
            $tenant = $tenantModel->getTenantById($currentTenant);
            if ($tenant && !empty($tenant['dsd_customer_name'])) {
                $tenantDsdName = $tenant['dsd_customer_name'];
            }
        }
        
        // Récupérer les factures
        $factures = $this->dsdFactures->getFacturesByTenant($tenantDsdName);
        
        // Récupérer l'évolution des licences
        $licenceEvolution = [];
        $licenceMatrix = [];
        if ($tenantDsdName) {
            $licenceEvolution = $this->dsdFactures->getLicenceEvolution($tenantDsdName);
            $licenceMatrix = $this->dsdFactures->getLicenceMatrix($tenantDsdName);
        }
        
        // Récupérer les statistiques
        $globalStats = $this->dsdFactures->getGlobalStats();

        // Récupérer la liste des tenants pour l'affichage
        $tenants = [];
        if ($currentTenant !== 'all') {
            $tenants[$currentTenant] = $tenant;
        }

        $this->loadView('tools/dsd-factures', [
            'factures' => $factures,
            'licenceEvolution' => $licenceEvolution,
            'licenceMatrix' => $licenceMatrix,
            'globalStats' => $globalStats,
            'currentTenant' => $currentTenant,
            'tenantDsdName' => $tenantDsdName,
            'tenants' => $tenants
        ]);
    }

    public function licenceDetail() {
        $this->requireAdmin();
        
        $licenseName = $_GET['license'] ?? '';
        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        
        if (!$licenseName || $currentTenant === 'all') {
            $this->redirectWithMessage('tools', 'dsdFactures', 'Licence ou tenant non spécifié', 'error');
        }
        
        // Récupérer le nom DSD du tenant
        require_once 'models/Tenant.php';
        $tenantModel = new Tenant();
        $tenant = $tenantModel->getTenantById($currentTenant);
        
        if (!$tenant || empty($tenant['dsd_customer_name'])) {
            $this->redirectWithMessage('tools', 'dsdFactures', 'Tenant DSD non configuré', 'error');
        }
        
        // Récupérer l'évolution détaillée de la licence
        $evolutionDetail = $this->dsdFactures->getLicenceEvolutionDetail($tenant['dsd_customer_name'], $licenseName);
        
        // Si c'est une requête AJAX, retourner du JSON
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            $labels = [];
            $values = [];
            
            foreach ($evolutionDetail as $row) {
                $labels[] = date('M Y', strtotime($row['month'] . '-01'));
                $values[] = (int)$row['quantity'];
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'labels' => $labels,
                'values' => $values
            ]);
            exit;
        }
        
        // Sinon, afficher la vue normale
        $this->loadView('tools/licence-detail', [
            'licenseName' => $licenseName,
            'tenantName' => $tenant['name'],
            'evolutionDetail' => $evolutionDetail
        ]);
    }
}
?> 