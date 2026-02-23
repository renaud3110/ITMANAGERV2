<?php

require_once 'models/Person.php';
require_once 'models/Login.php';
require_once 'models/LoginService.php';
require_once 'models/Tenant.php';

class AccountsController extends BaseController {
    
    private $personModel;
    private $loginModel;
    private $serviceModel;
    private $tenantModel;
    
    public function __construct() {
        parent::__construct();
        $this->personModel = new Person();
        $this->loginModel = new Login();
        $this->serviceModel = new LoginService();
        $this->tenantModel = new Tenant();
    }

    /**
     * Page principale - Liste des personnes
     */
    public function index() {
        $this->handleTenantSiteSelection();
        
        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        $currentSite = $_SESSION['current_site'] ?? 'all';
        
        // Récupérer les personnes selon le tenant sélectionné
        if ($currentTenant === 'all') {
            $persons = $this->personModel->getAll();
        } else {
            $persons = $this->personModel->getAll($currentTenant);
        }
        
        // Enrichir avec le nombre de comptes par personne
        foreach ($persons as &$person) {
            $logins = $this->personModel->getLogins($person['id']);
            $person['logins_count'] = count($logins);
        }
        
        $flash = $this->getFlashMessage();
        
        $this->loadView('accounts/index', [
            'persons' => $persons,
            'currentTenant' => $currentTenant,
            'currentSite' => $currentSite,
            'flash' => $flash
        ]);
    }
    
    /**
     * Afficher une personne et ses comptes
     */
    public function view() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirectWithMessage('accounts', 'index', 'Personne non trouvée', 'error');
        }
        
        $person = $this->personModel->getById($id);
        if (!$person) {
            $this->redirectWithMessage('accounts', 'index', 'Personne non trouvée', 'error');
        }
        
        // Récupérer les informations du tenant de la personne
        $personTenant = $this->tenantModel->getById($person['tenant_id']);
        
        // Récupérer les comptes de la personne
        $logins = $this->personModel->getLogins($id);
        
        // Récupérer tous les services pour le formulaire d'ajout de compte
        $services = $this->serviceModel->getAll();
        
        // Récupérer tous les tenants pour le formulaire d'ajout de compte
        $tenants = $this->tenantModel->getAll();
        
        $flash = $this->getFlashMessage();
        
        $this->loadView('accounts/view', [
            'person' => $person,
            'personTenant' => $personTenant,
            'logins' => $logins,
            'services' => $services,
            'tenants' => $tenants,
            'flash' => $flash
        ]);
    }
    
    /**
     * Formulaire de création d'une personne
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreatePerson();
            return;
        }
        
        // Récupérer tous les tenants pour le formulaire
        $tenants = $this->tenantModel->getAll();
        
        $this->loadView('accounts/create', [
            'tenants' => $tenants
        ]);
    }
    
    /**
     * Traitement de la création d'une personne
     */
    private function handleCreatePerson() {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $tenant_id = $_POST['tenant_id'] ?? ($_SESSION['current_tenant'] !== 'all' ? $_SESSION['current_tenant'] : 1);
        
        // Validation
        if (empty($nom) || empty($prenom)) {
            $tenants = $this->tenantModel->getAll();
            $this->loadView('accounts/create', [
                'error' => 'Le nom et le prénom sont obligatoires',
                'tenants' => $tenants
            ]);
            return;
        }
        
        // Vérifier l'unicité de l'email
        if (!empty($email) && $this->personModel->emailExists($email)) {
            $tenants = $this->tenantModel->getAll();
            $this->loadView('accounts/create', [
                'error' => 'Cette adresse email est déjà utilisée',
                'tenants' => $tenants
            ]);
            return;
        }
        
        $personData = [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => !empty($email) ? $email : null,
            'tenant_id' => $tenant_id
        ];
        
        $personId = $this->personModel->create($personData);
        
        if ($personId) {
            $this->redirectWithMessage('accounts', 'view', 
                "Personne {$prenom} {$nom} créée avec succès", 'success', ['id' => $personId]);
        } else {
            $tenants = $this->tenantModel->getAll();
            $this->loadView('accounts/create', [
                'error' => 'Erreur lors de la création de la personne',
                'tenants' => $tenants
            ]);
        }
    }
    
    /**
     * Formulaire d'édition d'une personne
     */
    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirectWithMessage('accounts', 'index', 'Personne non trouvée', 'error');
        }
        
        $person = $this->personModel->getById($id);
        if (!$person) {
            $this->redirectWithMessage('accounts', 'index', 'Personne non trouvée', 'error');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEditPerson($id);
            return;
        }
        
        // Récupérer tous les tenants pour le formulaire
        $tenants = $this->tenantModel->getAll();
        
        $this->loadView('accounts/edit', [
            'person' => $person,
            'tenants' => $tenants
        ]);
    }
    
    /**
     * Traitement de l'édition d'une personne
     */
    private function handleEditPerson($id) {
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $tenant_id = $_POST['tenant_id'] ?? 1;
        
        // Validation
        if (empty($nom) || empty($prenom)) {
            $person = $this->personModel->getById($id);
            $tenants = $this->tenantModel->getAll();
            $this->loadView('accounts/edit', [
                'person' => $person,
                'tenants' => $tenants,
                'error' => 'Le nom et le prénom sont obligatoires'
            ]);
            return;
        }
        
        // Vérifier l'unicité de l'email (exclure la personne actuelle)
        if (!empty($email) && $this->personModel->emailExists($email, $id)) {
            $person = $this->personModel->getById($id);
            $tenants = $this->tenantModel->getAll();
            $this->loadView('accounts/edit', [
                'person' => $person,
                'tenants' => $tenants,
                'error' => 'Cette adresse email est déjà utilisée'
            ]);
            return;
        }
        
        $personData = [
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => !empty($email) ? $email : null,
            'tenant_id' => $tenant_id
        ];
        
        if ($this->personModel->update($id, $personData)) {
            $this->redirectWithMessage('accounts', 'view', 
                "Personne {$prenom} {$nom} mise à jour avec succès", 'success', ['id' => $id]);
        } else {
            $person = $this->personModel->getById($id);
            $this->loadView('accounts/edit', [
                'person' => $person,
                'error' => 'Erreur lors de la mise à jour'
            ]);
        }
    }
    
    /**
     * Suppression d'une personne
     */
    public function delete() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirectWithMessage('accounts', 'index', 'Personne non trouvée', 'error');
        }
        
        try {
            $person = $this->personModel->getById($id);
            if (!$person) {
                $this->redirectWithMessage('accounts', 'index', 'Personne non trouvée', 'error');
            }
            
            if ($this->personModel->delete($id)) {
                $this->redirectWithMessage('accounts', 'index', 
                    "Personne {$person['prenom']} {$person['nom']} supprimée avec succès", 'success');
            } else {
                $this->redirectWithMessage('accounts', 'index', 
                    'Erreur lors de la suppression', 'error');
            }
        } catch (Exception $e) {
            $this->redirectWithMessage('accounts', 'index', $e->getMessage(), 'error');
        }
    }
    
    /**
     * Ajouter un compte à une personne
     */
    public function addLogin() {
        $person_id = $_POST['person_id'] ?? null;
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $service_id = $_POST['service_id'] ?? null;
        $tenant_id = $_POST['tenant_id'] ?? ($_SESSION['current_tenant'] !== 'all' ? $_SESSION['current_tenant'] : 1);
        $site_id = $_POST['site_id'] ?? ($_SESSION['current_site'] !== 'all' ? $_SESSION['current_site'] : null);
        
        if (!$person_id || empty($username) || !$service_id) {
            $this->redirectWithMessage('accounts', 'view', 
                'Tous les champs obligatoires doivent être remplis', 'error', ['id' => $person_id]);
        }
        
        $loginData = [
            'person_id' => $person_id,
            'username' => $username,
            'password' => $password,
            'service_id' => $service_id,
            'tenant_id' => $tenant_id,
            'site_id' => $site_id
        ];
        
        try {
            $loginId = $this->loginModel->create($loginData);
            if ($loginId) {
                $this->redirectWithMessage('accounts', 'view', 
                    'Compte ajouté avec succès', 'success', ['id' => $person_id]);
            } else {
                $this->redirectWithMessage('accounts', 'view', 
                    'Erreur lors de la création du compte', 'error', ['id' => $person_id]);
            }
        } catch (Exception $e) {
            $this->redirectWithMessage('accounts', 'view', 
                'Erreur: ' . $e->getMessage(), 'error', ['id' => $person_id]);
        }
    }
    
    /**
     * Afficher le mot de passe d'un compte (décrypté)
     */
    public function showPassword() {
        header('Content-Type: application/json');
        
        $login_id = $_GET['login_id'] ?? null;
        if (!$login_id) {
            echo json_encode(['error' => 'ID de compte manquant']);
            return;
        }
        
        try {
            // Récupérer toutes les informations du compte
            $login = $this->loginModel->getById($login_id);
            
            if (!$login) {
                echo json_encode(['error' => 'Compte non trouvé']);
                return;
            }
            
            // Décrypter le mot de passe
            $password = $this->loginModel->getDecryptedPassword($login_id);
            
            if ($password === null) {
                echo json_encode(['error' => 'Impossible de décrypter le mot de passe']);
                return;
            }
            
            // Retourner toutes les informations nécessaires
            echo json_encode([
                'password' => $password,
                'username' => $login['username'] ?? 'Non défini',
                'service' => $login['service_nom'] ?? 'Non défini'
            ]);
            
        } catch (Exception $e) {
            error_log("Erreur dans showPassword: " . $e->getMessage());
            echo json_encode(['error' => 'Erreur serveur']);
        }
    }
    
    /**
     * Gestion de la sélection tenant/site (hérité de BaseController)
     */
    protected function handleTenantSiteSelection() {
        if (isset($_POST['change_tenant'])) {
            $_SESSION['current_tenant'] = $_POST['tenant_id'] ?? 'all';
            $_SESSION['current_site'] = 'all'; // Reset site when tenant changes
        }
        
        if (isset($_POST['change_site'])) {
            $_SESSION['current_site'] = $_POST['site_id'] ?? 'all';
        }
    }
    
    /**
     * Redirection avec paramètres supplémentaires
     */
    protected function redirectWithMessage($page, $action = 'index', $message = '', $type = 'success', $params = []) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
        
        $url = "?page=$page&action=$action";
        foreach ($params as $key => $value) {
            $url .= "&$key=$value";
        }
        
        if (ob_get_level()) {
            ob_end_clean();
        }
        header("Location: $url");
        exit;
    }
    
    /**
     * Formulaire d'ajout d'un compte technique (sans personne)
     */
    public function technical() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreateTechnicalAccount();
            return;
        }
        
        // Récupérer tous les services pour le formulaire
        $services = $this->serviceModel->getAll();
        
        // Récupérer tous les tenants pour le formulaire
        $tenants = $this->tenantModel->getAll();
        
        $flash = $this->getFlashMessage();
        
        $this->loadView('accounts/technical', [
            'services' => $services,
            'tenants' => $tenants,
            'flash' => $flash
        ]);
    }
    
    /**
     * Traitement de la création d'un compte technique
     */
    private function handleCreateTechnicalAccount() {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $service_id = $_POST['service_id'] ?? null;
        $tenant_id = $_POST['tenant_id'] ?? ($_SESSION['current_tenant'] !== 'all' ? $_SESSION['current_tenant'] : 1);
        $site_id = $_POST['site_id'] ?? ($_SESSION['current_site'] !== 'all' ? $_SESSION['current_site'] : null);
        $description = trim($_POST['description'] ?? '');
        
        // Validation
        if (empty($username) || empty($service_id)) {
            $services = $this->serviceModel->getAll();
            $tenants = $this->tenantModel->getAll();
            $this->loadView('accounts/technical', [
                'error' => 'Le nom d\'utilisateur et le service sont obligatoires',
                'services' => $services,
                'tenants' => $tenants
            ]);
            return;
        }
        
        $loginData = [
            'person_id' => null, // Pas de personne associée
            'username' => $username,
            'password' => $password,
            'service_id' => $service_id,
            'tenant_id' => $tenant_id,
            'site_id' => $site_id,
            'description' => $description
        ];
        
        try {
            $loginId = $this->loginModel->create($loginData);
            if ($loginId) {
                $this->redirectWithMessage('accounts', 'index', 
                    'Compte technique créé avec succès', 'success');
            } else {
                $services = $this->serviceModel->getAll();
                $tenants = $this->tenantModel->getAll();
                $this->loadView('accounts/technical', [
                    'error' => 'Erreur lors de la création du compte technique',
                    'services' => $services,
                    'tenants' => $tenants
                ]);
            }
        } catch (Exception $e) {
            $services = $this->serviceModel->getAll();
            $tenants = $this->tenantModel->getAll();
            $this->loadView('accounts/technical', [
                'error' => 'Erreur: ' . $e->getMessage(),
                'services' => $services,
                'tenants' => $tenants
            ]);
        }
    }
    
    /**
     * Liste des comptes techniques (sans personne associée)
     */
    public function technicalList() {
        $this->handleTenantSiteSelection();
        
        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        $currentSite = $_SESSION['current_site'] ?? 'all';
        
        // Récupérer les comptes sans personne associée
        $technicalAccounts = $this->loginModel->getTechnicalAccounts($currentTenant, $currentSite);
        
        $flash = $this->getFlashMessage();
        
        $this->loadView('accounts/technical_list', [
            'accounts' => $technicalAccounts,
            'currentTenant' => $currentTenant,
            'currentSite' => $currentSite,
            'flash' => $flash
        ]);
    }
    
    /**
     * Formulaire d'édition d'un compte technique
     */
    public function editTechnical() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirectWithMessage('accounts', 'technicalList', 'Compte non trouvé', 'error');
        }
        
        $login = $this->loginModel->getById($id);
        if (!$login || $login['person_id'] !== null) {
            $this->redirectWithMessage('accounts', 'technicalList', 'Compte technique non trouvé', 'error');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEditTechnicalAccount($id);
            return;
        }
        
        // Récupérer tous les services pour le formulaire
        $services = $this->serviceModel->getAll();
        
        // Récupérer tous les tenants pour le formulaire
        $tenants = $this->tenantModel->getAll();
        
        $this->loadView('accounts/edit_technical', [
            'login' => $login,
            'services' => $services,
            'tenants' => $tenants
        ]);
    }
    
    /**
     * Traitement de l'édition d'un compte technique
     */
    private function handleEditTechnicalAccount($id) {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $service_id = $_POST['service_id'] ?? null;
        $tenant_id = $_POST['tenant_id'] ?? 1;
        $site_id = $_POST['site_id'] ?? null;
        $description = trim($_POST['description'] ?? '');
        
        // Validation
        if (empty($username) || empty($service_id)) {
            $login = $this->loginModel->getById($id);
            $services = $this->serviceModel->getAll();
            $tenants = $this->tenantModel->getAll();
            $this->loadView('accounts/edit_technical', [
                'login' => $login,
                'services' => $services,
                'tenants' => $tenants,
                'error' => 'Le nom d\'utilisateur et le service sont obligatoires'
            ]);
            return;
        }
        
        $loginData = [
            'username' => $username,
            'service_id' => $service_id,
            'tenant_id' => $tenant_id,
            'site_id' => $site_id,
            'description' => $description
        ];
        
        // Ajouter le mot de passe seulement s'il est fourni
        if (!empty($password)) {
            $loginData['password'] = $password;
        }
        
        try {
            if ($this->loginModel->update($id, $loginData)) {
                $this->redirectWithMessage('accounts', 'technicalList', 
                    'Compte technique mis à jour avec succès', 'success');
            } else {
                $login = $this->loginModel->getById($id);
                $services = $this->serviceModel->getAll();
                $tenants = $this->tenantModel->getAll();
                $this->loadView('accounts/edit_technical', [
                    'login' => $login,
                    'services' => $services,
                    'tenants' => $tenants,
                    'error' => 'Erreur lors de la mise à jour du compte'
                ]);
            }
        } catch (Exception $e) {
            $login = $this->loginModel->getById($id);
            $services = $this->serviceModel->getAll();
            $tenants = $this->tenantModel->getAll();
            $this->loadView('accounts/edit_technical', [
                'login' => $login,
                'services' => $services,
                'tenants' => $tenants,
                'error' => 'Erreur: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Suppression d'un compte technique
     */
    public function deleteTechnical() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirectWithMessage('accounts', 'technicalList', 'Compte non trouvé', 'error');
        }
        
        $login = $this->loginModel->getById($id);
        if (!$login || $login['person_id'] !== null) {
            $this->redirectWithMessage('accounts', 'technicalList', 'Compte technique non trouvé', 'error');
        }
        
        try {
            if ($this->loginModel->delete($id)) {
                $this->redirectWithMessage('accounts', 'technicalList', 
                    'Compte technique supprimé avec succès', 'success');
            } else {
                $this->redirectWithMessage('accounts', 'technicalList', 
                    'Erreur lors de la suppression du compte', 'error');
            }
        } catch (Exception $e) {
            $this->redirectWithMessage('accounts', 'technicalList', 
                'Erreur: ' . $e->getMessage(), 'error');
        }
    }
}
?> 