<?php

class SitesController extends BaseController {
    private $siteModel;
    private $tenantModel;

    public function __construct() {
        parent::__construct();
        $this->siteModel = new Site();
        $this->tenantModel = new Tenant();
    }

    public function index() {
        $this->requireAdmin(); // Seuls les admins globaux peuvent gérer les sites
        $this->handleTenantSiteSelection();

        $currentTenant = $_SESSION['current_tenant'] ?? 'all';

        if ($currentTenant === 'all') {
            $sites = $this->siteModel->getAllSites();
        } else {
            $sites = $this->siteModel->getSitesByTenant($currentTenant);
        }

        $flash = $this->getFlashMessage();

        $this->loadView('sites/index', [
            'sites' => $sites,
            'flash' => $flash
        ]);
    }

    public function create() {
        $this->requireAdmin();
        $this->handleTenantSiteSelection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'],
                'address' => $_POST['address'],
                'tenant_id' => $_POST['tenant_id'],
                'description' => $_POST['description']
            ];

            try {
                $this->siteModel->createSite($data);
                $this->redirectWithMessage('sites', 'index', 'Site créé avec succès');
            } catch (Exception $e) {
                $error = "Erreur lors de la création du site: " . $e->getMessage();
                $tenants = $this->tenantModel->getAll();
                $this->loadView('sites/create', ['error' => $error, 'tenants' => $tenants]);
                return;
            }
        }

        $tenants = $this->tenantModel->getAll();
        $this->loadView('sites/create', ['tenants' => $tenants]);
    }

    public function edit() {
        $this->requireAdmin();
        $this->handleTenantSiteSelection();

        $id = $_GET['id'] ?? 0;
        $site = $this->siteModel->getSiteById($id);

        if (!$site) {
            $this->redirectWithMessage('sites', 'index', 'Site non trouvé', 'error');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'],
                'tenant_id' => $_POST['tenant_id'],
                'address' => $_POST['address'],
                'is_default' => $_POST['is_default'] ?? 0
            ];

            try {
                $this->siteModel->updateSite($id, $data);
                $this->redirectWithMessage('sites', 'index', 'Site mis à jour avec succès');
            } catch (Exception $e) {
                $error = "Erreur lors de la mise à jour: " . $e->getMessage();
                $tenants = $this->tenantModel->getAll();
                $this->loadView('sites/edit', ['site' => $site, 'tenants' => $tenants, 'error' => $error]);
                return;
            }
        }

        $tenants = $this->tenantModel->getAll();
        $this->loadView('sites/edit', ['site' => $site, 'tenants' => $tenants]);
    }

    public function delete() {
        $this->requireAdmin();
        $id = $_GET['id'] ?? 0;

        try {
            $this->siteModel->deleteSite($id);
            $this->redirectWithMessage('sites', 'index', 'Site supprimé avec succès');
        } catch (Exception $e) {
            $this->redirectWithMessage('sites', 'index', 'Erreur lors de la suppression', 'error');
        }
    }

    // API endpoint pour AJAX
    public function getSitesByTenant() {
        header('Content-Type: application/json');

        $tenantId = $_GET['tenant_id'] ?? 'all';
        $sites = $this->siteModel->getSitesForDropdown($tenantId);

        echo json_encode($sites);
        exit;
    }

    // API endpoint pour AJAX
    public function getSitesJson() {
        header('Content-Type: application/json');

        $tenantId = $_GET['tenant_id'] ?? 'all';
        $sites = $this->siteModel->getSitesForDropdown($tenantId);

        echo json_encode($sites);
        exit;
    }
}
