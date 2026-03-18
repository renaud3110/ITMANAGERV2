<?php

class UsersController extends BaseController {
    private $userModel;
    private $tenantModel;

    public function __construct() {
        $this->userModel = new User();
        $this->tenantModel = new Tenant();
    }

    public function index() {
        $this->requireAdmin(); // Seuls les admins globaux peuvent gérer les utilisateurs
        $this->handleTenantSiteSelection();

        $users = $this->userModel->getUsersWithTenant();
        $flash = $this->getFlashMessage();

        $this->loadView('users/index', [
            'users' => $users,
            'flash' => $flash
        ]);
    }

    public function create() {
        $this->requireAdmin();
        $this->handleTenantSiteSelection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'tenant_id' => $_POST['tenant_id'] ?: null,
                'is_global_admin' => isset($_POST['is_global_admin']) ? 1 : 0
            ];

            try {
                $this->userModel->createUser($data);
                $this->redirectWithMessage('users', 'index', 'Utilisateur créé avec succès');
            } catch (Exception $e) {
                $error = "Erreur lors de la création de l'utilisateur: " . $e->getMessage();
                $tenants = $this->tenantModel->getAll();
                $this->loadView('users/create', ['error' => $error, 'tenants' => $tenants]);
                return;
            }
        }

        $tenants = $this->tenantModel->getAll();
        $this->loadView('users/create', ['tenants' => $tenants]);
    }

    public function edit() {
        $this->requireAdmin();
        $this->handleTenantSiteSelection();

        $id = $_GET['id'] ?? 0;
        $user = $this->userModel->getUserById($id);

        if (!$user) {
            $this->redirectWithMessage('users', 'index', 'Utilisateur non trouvé', 'error');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'],
                'email' => $_POST['email'],
                'tenant_id' => $_POST['tenant_id'] ?: null,
                'is_global_admin' => isset($_POST['is_global_admin']) ? 1 : 0
            ];

            try {
                $this->userModel->updateUser($id, $data);
                $this->redirectWithMessage('users', 'index', 'Utilisateur mis à jour avec succès');
            } catch (Exception $e) {
                $error = "Erreur lors de la mise à jour: " . $e->getMessage();
                $tenants = $this->tenantModel->getAll();
                $this->loadView('users/edit', ['user' => $user, 'tenants' => $tenants, 'error' => $error]);
                return;
            }
        }

        $tenants = $this->tenantModel->getAll();
        $this->loadView('users/edit', ['user' => $user, 'tenants' => $tenants]);
    }

    public function delete() {
        $this->requireAdmin();
        $id = $_GET['id'] ?? 0;

        try {
            $this->userModel->deleteUser($id);
            $this->redirectWithMessage('users', 'index', 'Utilisateur supprimé avec succès');
        } catch (Exception $e) {
            $this->redirectWithMessage('users', 'index', 'Erreur lors de la suppression', 'error');
        }
    }
}
