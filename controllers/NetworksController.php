<?php

require_once 'models/NetworkEquipment.php';
require_once 'classes/NetworkPortManager.php';

class NetworksController extends BaseController {
    private $networkModel;
    private $portManager;

    public function __construct() {
        parent::__construct();
        $this->networkModel = new NetworkEquipment();
        $this->portManager = new NetworkPortManager();
    }

    public function index() {
        $this->handleTenantSiteSelection();
        
        $currentSite = $_SESSION['current_site'] ?? 'all';
        
        // Récupérer les statistiques
        $stats = $this->networkModel->getStatistics($currentSite);
        
        // Récupérer les équipements
        $equipments = $this->networkModel->getAll($currentSite);
        
        // Récupérer le résumé des ports
        $portsSummary = $this->networkModel->getPortsSummary();
        
        $flash = $this->getFlashMessage();

        $this->loadView('networks/index', [
            'flash' => $flash,
            'stats' => $stats,
            'equipments' => $equipments,
            'portsSummary' => $portsSummary
        ]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'name' => $_POST['name'],
                    'type' => $_POST['type'],
                    'model_id' => $_POST['model_id'] ?: null,
                    'site_id' => $_POST['site_id'],
                    'manufacturer_id' => $_POST['manufacturer_id'] ?: null,
                    'ip_address_id' => $_POST['ip_address_id'] ?: null,
                    'status' => $_POST['status'] ?: 'inactive',
                    'login_id' => $_POST['login_id'] ?: null,
                    'ports_count' => (int)$_POST['ports_count'] ?: 0,
                    'port_type' => $_POST['port_type'] ?: 'ethernet',
                    'port_speed' => $_POST['port_speed'] ?: '1Gbps'
                ];

                $equipmentId = $this->networkModel->create($data);
                
                $_SESSION['flash_message'] = 'Équipement réseau créé avec succès.';
                $_SESSION['flash_type'] = 'success';
                $this->redirect('networks');
                
            } catch (Exception $e) {
                $_SESSION['flash_message'] = 'Erreur lors de la création : ' . $e->getMessage();
                $_SESSION['flash_type'] = 'error';
            }
        }

        // Récupérer les données pour les formulaires
        $sites = $this->networkModel->getSites();
        $manufacturers = $this->networkModel->getManufacturers();
        $models = $this->networkModel->getModels();
        $ipAddresses = $this->networkModel->getIpAddresses();
        $logins = $this->networkModel->getLogins();
        
        $flash = $this->getFlashMessage();

        $this->loadView('networks/create', [
            'flash' => $flash,
            'sites' => $sites,
            'manufacturers' => $manufacturers,
            'models' => $models,
            'ipAddresses' => $ipAddresses,
            'logins' => $logins
        ]);
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('networks');
            return;
        }

        $equipment = $this->networkModel->getById($id);
        if (!$equipment) {
            $_SESSION['flash_message'] = 'Équipement non trouvé.';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('networks');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $data = [
                    'name' => $_POST['name'],
                    'type' => $_POST['type'],
                    'model_id' => $_POST['model_id'] ?: null,
                    'site_id' => $_POST['site_id'],
                    'manufacturer_id' => $_POST['manufacturer_id'] ?: null,
                    'ip_address_id' => $_POST['ip_address_id'] ?: null,
                    'status' => $_POST['status'] ?: 'inactive',
                    'login_id' => $_POST['login_id'] ?: null,
                    'ports_count' => (int)$_POST['ports_count'] ?: 0
                ];

                $this->networkModel->update($id, $data);
                
                // Mettre à jour les ports si le nombre a changé
                if ($data['ports_count'] != $equipment['ports_count']) {
                    $this->portManager->setEquipmentPorts(
                        $id, 
                        $data['ports_count'], 
                        $_POST['port_type'] ?: 'ethernet',
                        $_POST['port_speed'] ?: '1Gbps'
                    );
                }
                
                $_SESSION['flash_message'] = 'Équipement mis à jour avec succès.';
                $_SESSION['flash_type'] = 'success';
                $this->redirect('networks');
                
            } catch (Exception $e) {
                $_SESSION['flash_message'] = 'Erreur lors de la mise à jour : ' . $e->getMessage();
                $_SESSION['flash_type'] = 'error';
            }
        }

        // Récupérer les données pour les formulaires
        $sites = $this->networkModel->getSites();
        $manufacturers = $this->networkModel->getManufacturers();
        $models = $this->networkModel->getModels();
        $ipAddresses = $this->networkModel->getIpAddresses();
        $logins = $this->networkModel->getLogins();
        
        $flash = $this->getFlashMessage();

        $this->loadView('networks/edit', [
            'flash' => $flash,
            'equipment' => $equipment,
            'sites' => $sites,
            'manufacturers' => $manufacturers,
            'models' => $models,
            'ipAddresses' => $ipAddresses,
            'logins' => $logins
        ]);
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('networks');
            return;
        }

        try {
            $this->networkModel->delete($id);
            $_SESSION['flash_message'] = 'Équipement supprimé avec succès.';
            $_SESSION['flash_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['flash_message'] = 'Erreur lors de la suppression : ' . $e->getMessage();
            $_SESSION['flash_type'] = 'error';
        }

        $this->redirect('networks');
    }

    public function ports() {
        $equipmentId = $_GET['equipment_id'] ?? null;
        if (!$equipmentId) {
            $this->redirect('networks');
            return;
        }

        $equipment = $this->networkModel->getById($equipmentId);
        if (!$equipment) {
            $_SESSION['flash_message'] = 'Équipement non trouvé.';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('networks');
            return;
        }

        // Récupérer les ports de l'équipement
        $ports = $this->networkModel->getPortsForEquipment($equipmentId);
        
        // Récupérer tous les équipements pour les connexions
        $allEquipments = $this->networkModel->getAll();
        
        $flash = $this->getFlashMessage();

        $this->loadView('networks/ports', [
            'flash' => $flash,
            'equipment' => $equipment,
            'ports' => $ports,
            'allEquipments' => $allEquipments
        ]);
    }

    public function updatePort() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('networks');
            return;
        }

        $portId = $_POST['port_id'] ?? null;
        $equipmentId = $_POST['equipment_id'] ?? null;

        if (!$portId || !$equipmentId) {
            $_SESSION['flash_message'] = 'Données manquantes.';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('networks');
            return;
        }

        try {
            $data = [
                'port_name' => $_POST['port_name'],
                'port_type' => $_POST['port_type'],
                'port_speed' => $_POST['port_speed'],
                'port_status' => $_POST['port_status'],
                'vlan_id' => $_POST['vlan_id'] ?: null,
                'description' => $_POST['description'] ?: null
            ];

            $this->portManager->updatePort($portId, $data);
            $_SESSION['flash_message'] = 'Port mis à jour avec succès.';
            $_SESSION['flash_type'] = 'success';
            
        } catch (Exception $e) {
            $_SESSION['flash_message'] = 'Erreur lors de la mise à jour : ' . $e->getMessage();
            $_SESSION['flash_type'] = 'error';
        }

        $this->redirect('networks', 'ports&equipment_id=' . $equipmentId);
    }

    public function connectPorts() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('networks');
            return;
        }

        $port1Id = $_POST['port1_id'] ?? null;
        $port2Id = $_POST['port2_id'] ?? null;
        $equipmentId = $_POST['equipment_id'] ?? null;

        if (!$port1Id || !$port2Id || !$equipmentId) {
            $_SESSION['flash_message'] = 'Données manquantes.';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('networks');
            return;
        }

        try {
            $this->portManager->connectPorts($port1Id, $port2Id);
            $_SESSION['flash_message'] = 'Ports connectés avec succès.';
            $_SESSION['flash_type'] = 'success';
            
        } catch (Exception $e) {
            $_SESSION['flash_message'] = 'Erreur lors de la connexion : ' . $e->getMessage();
            $_SESSION['flash_type'] = 'error';
        }

        $this->redirect('networks', 'ports&equipment_id=' . $equipmentId);
    }

    public function disconnectPort() {
        $portId = $_GET['port_id'] ?? null;
        $equipmentId = $_GET['equipment_id'] ?? null;

        if (!$portId || !$equipmentId) {
            $_SESSION['flash_message'] = 'Données manquantes.';
            $_SESSION['flash_type'] = 'error';
            $this->redirect('networks');
            return;
        }

        try {
            $this->portManager->disconnectPort($portId);
            $_SESSION['flash_message'] = 'Port déconnecté avec succès.';
            $_SESSION['flash_type'] = 'success';
            
        } catch (Exception $e) {
            $_SESSION['flash_message'] = 'Erreur lors de la déconnexion : ' . $e->getMessage();
            $_SESSION['flash_type'] = 'error';
        }

        $this->redirect('networks', 'ports&equipment_id=' . $equipmentId);
    }

    public function connections() {
        // Récupérer toutes les connexions réseau
        $connections = $this->networkModel->getNetworkConnections();
        
        $flash = $this->getFlashMessage();

        $this->loadView('networks/connections', [
            'flash' => $flash,
            'connections' => $connections
        ]);
    }

    public function ajaxGetPorts() {
        $equipmentId = $_GET['equipment_id'] ?? null;
        if (!$equipmentId) {
            echo json_encode([]);
            return;
        }

        $ports = $this->portManager->getAvailablePorts($equipmentId);
        echo json_encode($ports);
    }
    
    public function getCredentials() {
        header('Content-Type: application/json');
        
        // Vérifier les permissions (même vérification que pour showPassword)
        if (!$this->isGlobalAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Accès non autorisé']);
            return;
        }
        
        $equipmentId = $_GET['equipment_id'] ?? null;
        if (!$equipmentId) {
            http_response_code(400);
            echo json_encode(['error' => 'ID d\'équipement manquant']);
            return;
        }

        try {
            // Récupérer l'équipement avec ses informations de connexion
            $equipment = $this->networkModel->getById($equipmentId);
            
            if (!$equipment) {
                http_response_code(404);
                echo json_encode(['error' => 'Équipement non trouvé']);
                return;
            }
            
            if (empty($equipment['login_id'])) {
                echo json_encode(['error' => 'Aucun identifiant associé à cet équipement']);
                return;
            }
            
            // Récupérer les détails du compte de connexion
            $loginDetails = $this->getLoginDetails($equipment['login_id']);
            
            if (!$loginDetails) {
                echo json_encode(['error' => 'Identifiants non trouvés']);
                return;
            }
            
            echo json_encode([
                'equipment_name' => $equipment['name'],
                'equipment_type' => $equipment['type'],
                'ip_address' => $equipment['ip_address'] ?? 'Non définie',
                'username' => $loginDetails['username'],
                'service' => $loginDetails['service_name'] ?? 'Non défini',
                'description' => $loginDetails['description'] ?? 'Aucune description',
                'login_id' => $equipment['login_id']
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Erreur serveur: ' . $e->getMessage()]);
        }
    }
    
    private function getLoginDetails($loginId) {
        $database = new Database();
        $sql = "SELECT l.username, l.description, ls.nom as service_name 
                FROM logins l
                LEFT JOIN login_services ls ON l.service_id = ls.id
                WHERE l.id = ?";
        return $database->fetch($sql, [$loginId]);
    }
}
?>
