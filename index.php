<?php
session_start();
ob_start(); // Démarrer le buffering de sortie

// Fuseau horaire pour l'affichage (les timestamps moniteur sont stockés en UTC)
date_default_timezone_set('Europe/Brussels');

// Configuration de base
define('BASE_PATH', __DIR__);
define('BASE_URL', '/');

// Autoloader simple
spl_autoload_register(function ($class) {
    $paths = [
        'controllers/',
        'models/',
        'config/'
    ];

    foreach ($paths as $path) {
        $file = BASE_PATH . '/' . $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Routeur simple
$request = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';
$section = $_GET['section'] ?? null;

// Routes disponibles
$routes = [
    'auth' => 'AuthController',
    'dashboard' => 'DashboardController',
    'remote' => 'RemoteController',
    'hardware' => 'HardwareController',
    'networks' => 'NetworksController',
    'ip-management' => 'IpManagementController',
    'accounts' => 'AccountsController',
    'services' => 'ServicesController',
    'microsoft365' => 'Microsoft365Controller',
    'domains' => 'DomainsController',
    'licenses' => 'LicensesController',
    'backup' => 'BackupController',
    'tools' => 'ToolsController',
    'users' => 'UsersController',
    'tenants' => 'TenantsController',
    'sites' => 'SitesController',
    'security' => 'SecurityController',
    'servers' => 'ServersController'
];

// Pages publiques (sans authentification)
$publicPages = ['auth'];

try {
    // Vérifier si l'utilisateur est connecté
    $isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

    // Si pas connecté et page non publique, rediriger vers la connexion
    if (!$isLoggedIn && !in_array($request, $publicPages)) {
        if (ob_get_level()) {
            ob_end_clean(); // Nettoyer le buffer avant redirection
        }
        header('Location: ?page=auth&action=login');
        exit;
    }

    // Si connecté et tentative d'accès à la page de connexion, rediriger vers le dashboard
    if ($isLoggedIn && $request === 'auth' && $action === 'login') {
        if (ob_get_level()) {
            ob_end_clean(); // Nettoyer le buffer avant redirection
        }
        header('Location: ?page=dashboard');
        exit;
    }

    if (isset($routes[$request])) {
        $controllerName = $routes[$request];
        $controller = new $controllerName();

        // Gestion spéciale pour les sections (ex: hardware/computers)
        if ($section && method_exists($controller, $section)) {
            $controller->$section();
        } elseif (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->index();
        }
    } else {
        // Page par défaut
        if (!$isLoggedIn) {
            if (ob_get_level()) {
                ob_end_clean(); // Nettoyer le buffer avant redirection
            }
            header('Location: ?page=auth&action=login');
            exit;
        }
        $controller = new DashboardController();
        $controller->index();
    }
} catch (Exception $e) {
    error_log("Erreur: " . $e->getMessage());
    include 'views/error.php';
}

if (ob_get_level()) {
    ob_end_flush(); // Envoyer le contenu du buffer
}