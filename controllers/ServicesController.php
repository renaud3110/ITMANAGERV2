<?php
require_once 'controllers/BaseController.php';
require_once 'models/LoginService.php';

/**
 * Contrôleur pour la gestion des services de connexion
 */
class ServicesController extends BaseController {
    private $serviceModel;
    
    public function __construct() {
        parent::__construct();
        $this->serviceModel = new LoginService();
    }
    
    /**
     * Liste des services
     */
    public function index() {
        $services = $this->serviceModel->getAllWithCounts();
        
        $flash = $this->getFlashMessage();
        
        $this->loadView('services/index', [
            'services' => $services,
            'flash' => $flash
        ]);
    }
    
    /**
     * Détail d'un service
     */
    public function view() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirectWithMessage('services', 'index', 'Service non trouvé', 'error');
        }
        
        $service = $this->serviceModel->getById($id);
        if (!$service) {
            $this->redirectWithMessage('services', 'index', 'Service non trouvé', 'error');
        }
        
        $logins = $this->serviceModel->getLogins($id);
        
        $flash = $this->getFlashMessage();
        
        $this->loadView('services/view', [
            'service' => $service,
            'logins' => $logins,
            'flash' => $flash
        ]);
    }
    
    /**
     * Formulaire de création d'un service
     */
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCreateService();
            return;
        }
        
        $this->loadView('services/create');
    }
    
    /**
     * Traitement de la création d'un service
     */
    private function handleCreateService() {
        $nom = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $logo = trim($_POST['logo'] ?? '');
        
        // Validation
        if (empty($nom)) {
            $this->loadView('services/create', [
                'error' => 'Le nom du service est obligatoire'
            ]);
            return;
        }
        
        // Vérifier l'unicité du nom
        if ($this->serviceModel->nameExists($nom)) {
            $this->loadView('services/create', [
                'error' => 'Ce nom de service existe déjà'
            ]);
            return;
        }
        
        $serviceData = [
            'nom' => $nom,
            'description' => !empty($description) ? $description : null,
            'logo' => !empty($logo) ? $logo : null
        ];
        
        $serviceId = $this->serviceModel->create($serviceData);
        
        if ($serviceId) {
            $this->redirectWithMessage('services', 'index', 
                "Service {$nom} créé avec succès", 'success');
        } else {
            $this->loadView('services/create', [
                'error' => 'Erreur lors de la création du service'
            ]);
        }
    }
    
    /**
     * Formulaire d'édition d'un service
     */
    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirectWithMessage('services', 'index', 'Service non trouvé', 'error');
        }
        
        $service = $this->serviceModel->getById($id);
        if (!$service) {
            $this->redirectWithMessage('services', 'index', 'Service non trouvé', 'error');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEditService($id);
            return;
        }
        
        $this->loadView('services/edit', [
            'service' => $service
        ]);
    }
    
    /**
     * Traitement de l'édition d'un service
     */
    private function handleEditService($id) {
        $nom = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $logo = trim($_POST['logo'] ?? '');
        
        // Validation
        if (empty($nom)) {
            $service = $this->serviceModel->getById($id);
            $this->loadView('services/edit', [
                'service' => $service,
                'error' => 'Le nom du service est obligatoire'
            ]);
            return;
        }
        
        // Vérifier l'unicité du nom (exclure le service actuel)
        if ($this->serviceModel->nameExists($nom, $id)) {
            $service = $this->serviceModel->getById($id);
            $this->loadView('services/edit', [
                'service' => $service,
                'error' => 'Ce nom de service existe déjà'
            ]);
            return;
        }
        
        $serviceData = [
            'nom' => $nom,
            'description' => !empty($description) ? $description : null,
            'logo' => !empty($logo) ? $logo : null
        ];
        
        if ($this->serviceModel->update($id, $serviceData)) {
            $this->redirectWithMessage('services', 'index', 
                "Service {$nom} mis à jour avec succès", 'success');
        } else {
            $service = $this->serviceModel->getById($id);
            $this->loadView('services/edit', [
                'service' => $service,
                'error' => 'Erreur lors de la mise à jour'
            ]);
        }
    }
    
    /**
     * Suppression d'un service
     */
    public function delete() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirectWithMessage('services', 'index', 'Service non trouvé', 'error');
        }
        
        try {
            $service = $this->serviceModel->getById($id);
            if (!$service) {
                $this->redirectWithMessage('services', 'index', 'Service non trouvé', 'error');
            }
            
            if ($this->serviceModel->delete($id)) {
                $this->redirectWithMessage('services', 'index', 
                    "Service {$service['nom']} supprimé avec succès", 'success');
            } else {
                $this->redirectWithMessage('services', 'index', 
                    'Erreur lors de la suppression', 'error');
            }
        } catch (Exception $e) {
            $this->redirectWithMessage('services', 'index', $e->getMessage(), 'error');
        }
    }
    
    /**
     * Redirection avec message
     */
    protected function redirectWithMessage($page, $action = 'index', $message = '', $type = 'success', $params = []) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
        
        $url = "?page={$page}&action={$action}";
        if (!empty($params)) {
            $url .= '&' . http_build_query($params);
        }
        
        header("Location: {$url}");
        exit;
    }
}
?> 