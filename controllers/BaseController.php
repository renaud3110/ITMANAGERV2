<?php

require_once 'models/Tenant.php';
require_once 'models/Site.php';

class BaseController {

    public function __construct() {
        // Rien de spécial à faire dans le constructeur de base
    }

    protected function loadView($view, $data = [], $useLayout = true) {
        // Extraire les données pour les rendre disponibles dans la vue
        extract($data);

        if (!$useLayout) {
            // Charger la vue sans layout (pour la page de connexion)
            include "views/$view.php";
            return;
        }

        // Charger les données communes à toutes les vues
        $tenantModel = new Tenant();
        $siteModel = new Site();

        $tenants = $tenantModel->getAll();
        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        $currentSite = $_SESSION['current_site'] ?? 'all';

        // Obtenir les sites pour le tenant courant
        if ($currentTenant === 'all') {
            $sites = $siteModel->getSitesForDropdown();
        } else {
            $sites = $siteModel->getSitesForDropdown($currentTenant);
        }

        // Charger le layout principal
        include 'views/layout.php';
    }

    protected function redirect($page, $action = 'index') {
        if (ob_get_level()) {
            ob_end_clean(); // Nettoyer le buffer avant redirection
        }
        header("Location: ?page=$page&action=$action");
        exit;
    }

    protected function redirectWithMessage($page, $action = 'index', $message = '', $type = 'success') {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
        $this->redirect($page, $action);
    }

    protected function getFlashMessage() {
        $message = $_SESSION['flash_message'] ?? '';
        $type = $_SESSION['flash_type'] ?? 'info';

        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);

        return ['message' => $message, 'type' => $type];
    }

    protected function handleTenantSiteSelection() {
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['change_tenant'])) {
                $_SESSION['current_tenant'] = $_POST['tenant_id'];
                $_SESSION['current_site'] = 'all'; // Reset site selection
            }

            if (isset($_POST['change_site'])) {
                $_SESSION['current_site'] = $_POST['site_id'];
            }
        }
    }

    // Méthodes d'authentification
    protected function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    protected function isGlobalAdmin() {
        return $this->isLoggedIn() && ($_SESSION['is_global_admin'] ?? false);
    }

    protected function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    protected function getCurrentUserTenantId() {
        return $_SESSION['tenant_id'] ?? null;
    }

    protected function requireLogin() {
        if (!$this->isLoggedIn()) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            header('Location: ?page=auth&action=login');
            exit;
        }
    }

    protected function requireAdmin() {
        $this->requireLogin();
        if (!$this->isGlobalAdmin()) {
            $this->redirectWithMessage('dashboard', 'index', 'Accès non autorisé - Droits administrateur requis', 'error');
        }
    }

    protected function getCurrentUserInfo() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'is_global_admin' => $_SESSION['is_global_admin'] ?? false,
            'tenant_id' => $_SESSION['tenant_id'] ?? null
        ];
    }
}
