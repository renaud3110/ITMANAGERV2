<?php
require_once 'config/Database.php';

/**
 * Modèle pour la gestion des services de connexion
 */
class LoginService {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Récupère tous les services
     */
    public function getAll() {
        $query = "SELECT * FROM login_services ORDER BY nom";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère un service par ID
     */
    public function getById($id) {
        $query = "SELECT * FROM login_services WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Récupère un service par nom
     */
    public function getByName($name) {
        $query = "SELECT * FROM login_services WHERE nom = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$name]);
        return $stmt->fetch();
    }
    
    /**
     * Crée un nouveau service
     */
    public function create($data) {
        $query = "INSERT INTO login_services (nom, description, logo) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($query);
        
        $result = $stmt->execute([
            $data['nom'],
            $data['description'] ?? null,
            $data['logo'] ?? null
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        return false;
    }
    
    /**
     * Met à jour un service
     */
    public function update($id, $data) {
        $query = "UPDATE login_services SET nom = ?, description = ?, logo = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute([
            $data['nom'],
            $data['description'] ?? null,
            $data['logo'] ?? null,
            $id
        ]);
    }
    
    /**
     * Supprime un service
     */
    public function delete($id) {
        // Vérifier s'il y a des comptes associés
        $checkQuery = "SELECT COUNT(*) FROM logins WHERE service_id = ?";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->execute([$id]);
        $loginCount = $checkStmt->fetchColumn();
        
        if ($loginCount > 0) {
            throw new Exception("Impossible de supprimer ce service car il a des comptes associés.");
        }
        
        $query = "DELETE FROM login_services WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }
    
    /**
     * Récupère les comptes d'un service
     */
    public function getLogins($serviceId) {
        $query = "SELECT l.*, p.nom as person_nom, p.prenom as person_prenom
                  FROM logins l
                  LEFT JOIN persons p ON l.person_id = p.id
                  WHERE l.service_id = ?
                  ORDER BY p.nom, p.prenom, l.username";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$serviceId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Vérifie si un nom de service existe déjà
     */
    public function nameExists($name, $excludeId = null) {
        $query = "SELECT COUNT(*) FROM login_services WHERE nom = ?";
        $params = [$name];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Récupère les statistiques des services
     */
    public function getStats() {
        $stats = [];
        
        // Nombre total de services
        $query = "SELECT COUNT(*) as total FROM login_services";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total'] = $stmt->fetchColumn();
        
        // Services avec comptes
        $query = "SELECT COUNT(DISTINCT service_id) as with_accounts FROM logins";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['with_accounts'] = $stmt->fetchColumn();
        
        // Service le plus utilisé
        $query = "SELECT ls.nom, COUNT(l.id) as count
                  FROM login_services ls
                  LEFT JOIN logins l ON ls.id = l.service_id
                  GROUP BY ls.id, ls.nom
                  ORDER BY count DESC
                  LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $mostUsed = $stmt->fetch();
        $stats['most_used'] = $mostUsed ? $mostUsed['nom'] : 'Aucun';
        
        return $stats;
    }
    
    /**
     * Récupère les services avec le nombre de comptes
     */
    public function getAllWithCounts() {
        $query = "SELECT ls.*, COUNT(l.id) as login_count
                  FROM login_services ls
                  LEFT JOIN logins l ON ls.id = l.service_id
                  GROUP BY ls.id, ls.nom, ls.description
                  ORDER BY ls.nom";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?> 