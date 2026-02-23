<?php

require_once 'controllers/BaseController.php';
require_once 'models/Tenant.php';

class TenantsController extends BaseController {
    private $tenantModel;

    public function __construct() {
        $this->tenantModel = new Tenant();
    }

    public function index() {
        $this->requireAdmin(); // Seuls les admins globaux peuvent gérer les tenants
        $this->handleTenantSiteSelection();

        $tenants = $this->tenantModel->getTenantsWithSiteCount();
        $flash = $this->getFlashMessage();

        $this->loadView('tenants/index', [
            'tenants' => $tenants,
            'flash' => $flash
        ]);
    }

    public function create() {
        $this->requireAdmin();
        $this->handleTenantSiteSelection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'domain' => trim($_POST['domain'] ?? '') ?: null,
                'description' => trim($_POST['description'] ?? '') ?: null,
                'contact_email' => trim($_POST['contact_email'] ?? '') ?: null,
                'nakivo_customer_name' => trim($_POST['nakivo_customer_name'] ?? '') ?: null
            ];

            try {
                $this->tenantModel->createTenant($data);
                $this->redirectWithMessage('tenants', 'index', 'Tenant créé avec succès');
            } catch (Exception $e) {
                $error = "Erreur lors de la création du tenant: " . $e->getMessage();
                $this->loadView('tenants/create', ['error' => $error]);
                return;
            }
        }

        $this->loadView('tenants/create');
    }

    public function edit() {
        $this->requireAdmin();
        $this->handleTenantSiteSelection();

        $id = $_GET['id'] ?? 0;
        
        // Nettoyer les données POST si c'est une nouvelle demande d'édition (GET)
        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Vider les données POST précédentes pour éviter les conflits
            $_POST = [];
        }
        
        $tenant = $this->tenantModel->getTenantById($id);

        if (!$tenant) {
            $this->redirectWithMessage('tenants', 'index', 'Tenant non trouvé', 'error');
        }

        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'],
                'domain' => $_POST['domain'],
                'description' => $_POST['description'],
                'nakivo_customer_name' => $_POST['nakivo_customer_name'] ?? null
            ];

            try {
                $this->tenantModel->updateTenant($id, $data);
                $this->redirectWithMessage('tenants', 'index', 'Tenant mis à jour avec succès');
            } catch (Exception $e) {
                $error = "Erreur lors de la mise à jour: " . $e->getMessage();
                $this->loadView('tenants/edit', ['tenant' => $tenant, 'error' => $error]);
                return;
            }
        }

        $this->loadView('tenants/edit', ['tenant' => $tenant]);
    }

    public function delete() {
        $this->requireAdmin();
        $id = $_GET['id'] ?? 0;

        try {
            $this->tenantModel->deleteTenant($id);
            $this->redirectWithMessage('tenants', 'index', 'Tenant supprimé avec succès');
        } catch (Exception $e) {
            $this->redirectWithMessage('tenants', 'index', 'Erreur lors de la suppression', 'error');
        }
    }
}
