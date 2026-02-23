<?php

class AuthController extends BaseController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function login() {
        // Si déjà connecté, rediriger vers le dashboard
        if ($this->isLoggedIn()) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            header('Location: ?page=dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = "Veuillez saisir votre email et mot de passe";
                $this->loadView('auth/login', ['error' => $error], false);
                return;
            }

            try {
                $user = $this->userModel->getUserByEmail($email);

                if ($user && password_verify($password, $user['password'])) {
                    // Connexion réussie
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['is_global_admin'] = $user['is_global_admin'];
                    $_SESSION['tenant_id'] = $user['tenant_id'];

                    // Rediriger vers le dashboard
                    if (ob_get_level()) {
                        ob_end_clean();
                    }
                    header('Location: ?page=dashboard');
                    exit;
                } else {
                    $error = "Email ou mot de passe incorrect";
                    $this->loadView('auth/login', ['error' => $error], false);
                    return;
                }
            } catch (Exception $e) {
                $error = "Erreur de connexion: " . $e->getMessage();
                $this->loadView('auth/login', ['error' => $error], false);
                return;
            }
        }

        // Afficher le formulaire de connexion
        $this->loadView('auth/login', [], false);
    }

    public function logout() {
        session_destroy();
        if (ob_get_level()) {
            ob_end_clean();
        }
        header('Location: ?page=auth&action=login');
        exit;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    public function isGlobalAdmin() {
        return $this->isLoggedIn() && ($_SESSION['is_global_admin'] ?? false);
    }

    public function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    public function getCurrentUserTenantId() {
        return $_SESSION['tenant_id'] ?? null;
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            if (ob_get_level()) {
                ob_end_clean();
            }
            header('Location: ?page=auth&action=login');
            exit;
        }
    }

    public function requireAdmin() {
        $this->requireLogin();
        if (!$this->isGlobalAdmin()) {
            $this->redirectWithMessage('dashboard', 'index', 'Accès non autorisé', 'error');
        }
    }
}
