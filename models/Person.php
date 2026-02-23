<?php
require_once 'config/Database.php';

/**
 * Modèle pour la gestion des personnes physiques
 */
class Person {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Récupère toutes les personnes
     */
    public function getAll($tenant_id = null) {
        if ($tenant_id) {
            $query = "SELECT * FROM persons WHERE tenant_id = ? ORDER BY nom, prenom";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$tenant_id]);
        } else {
            $query = "SELECT * FROM persons ORDER BY nom, prenom";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère une personne par ID
     */
    public function getById($id) {
        $query = "SELECT * FROM persons WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Recherche des personnes par nom ou prénom
     */
    public function search($term) {
        $query = "SELECT * FROM persons 
                  WHERE nom LIKE ? OR prenom LIKE ? OR email LIKE ?
                  ORDER BY nom, prenom";
        $searchTerm = "%$term%";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
    
    /**
     * Crée une nouvelle personne
     */
    public function create($data) {
        $query = "INSERT INTO persons (nom, prenom, email, tenant_id) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        
        $result = $stmt->execute([
            $data['nom'],
            $data['prenom'],
            $data['email'] ?? null,
            $data['tenant_id'] ?? 1
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        return false;
    }
    
    /**
     * Met à jour une personne
     */
    public function update($id, $data) {
        $query = "UPDATE persons SET nom = ?, prenom = ?, email = ?, tenant_id = ? WHERE id = ?";
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute([
            $data['nom'],
            $data['prenom'],
            $data['email'] ?? null,
            $data['tenant_id'] ?? 1,
            $id
        ]);
    }
    
    /**
     * Supprime une personne
     */
    public function delete($id) {
        // Vérifier s'il y a des comptes associés
        $checkQuery = "SELECT COUNT(*) FROM logins WHERE person_id = ?";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->execute([$id]);
        $loginCount = $checkStmt->fetchColumn();
        
        if ($loginCount > 0) {
            throw new Exception("Impossible de supprimer cette personne car elle a des comptes associés.");
        }
        
        $query = "DELETE FROM persons WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }
    
    /**
     * Récupère les comptes d'une personne
     */
    public function getLogins($personId) {
        $query = "SELECT l.*, ls.nom as service_nom, ls.description as service_description, ls.logo as service_logo
                  FROM logins l
                  JOIN login_services ls ON l.service_id = ls.id
                  WHERE l.person_id = ?
                  ORDER BY ls.nom";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$personId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Vérifie si un email existe déjà
     */
    public function emailExists($email, $excludeId = null) {
        if (empty($email)) {
            return false;
        }
        
        $query = "SELECT COUNT(*) FROM persons WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Récupère les statistiques des personnes
     */
    public function getStats() {
        $stats = [];
        
        // Nombre total de personnes
        $query = "SELECT COUNT(*) as total FROM persons";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total'] = $stmt->fetchColumn();
        
        // Personnes avec email
        $query = "SELECT COUNT(*) as with_email FROM persons WHERE email IS NOT NULL AND email != ''";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['with_email'] = $stmt->fetchColumn();
        
        // Personnes avec comptes
        $query = "SELECT COUNT(DISTINCT person_id) as with_accounts FROM logins WHERE person_id IS NOT NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['with_accounts'] = $stmt->fetchColumn();
        
        return $stats;
    }
}
?> 