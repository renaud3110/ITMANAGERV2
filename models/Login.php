<?php
require_once 'config/Database.php';
require_once 'config/PasswordManager.php';

/**
 * Modèle pour la gestion des comptes de connexion avec encryption
 */
class Login {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Récupère tous les comptes avec informations des personnes et services
     */
    public function getAll($tenant_id = null, $site_id = null) {
        $query = "SELECT l.*, 
                         p.nom as person_nom, p.prenom as person_prenom, p.email as person_email,
                         ls.nom as service_nom, ls.description as service_description
                  FROM logins l
                  LEFT JOIN persons p ON l.person_id = p.id
                  JOIN login_services ls ON l.service_id = ls.id
                  WHERE 1=1";
        
        $params = [];
        
        if ($tenant_id) {
            $query .= " AND l.tenant_id = ?";
            $params[] = $tenant_id;
        }
        
        if ($site_id) {
            $query .= " AND l.site_id = ?";
            $params[] = $site_id;
        }
        
        $query .= " ORDER BY ls.nom, p.nom, p.prenom, l.username";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Récupère un compte par ID
     */
    public function getById($id) {
        $query = "SELECT l.*, 
                         p.nom as person_nom, p.prenom as person_prenom, p.email as person_email,
                         ls.nom as service_nom, ls.description as service_description
                  FROM logins l
                  LEFT JOIN persons p ON l.person_id = p.id
                  JOIN login_services ls ON l.service_id = ls.id
                  WHERE l.id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Crée un nouveau compte
     */
    public function create($data) {
        $query = "INSERT INTO logins (person_id, username, password, service_id, tenant_id, site_id, description) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        
        // Encryption du mot de passe
        $encryptedPassword = '';
        if (!empty($data['password'])) {
            $encryptedPassword = PasswordManager::encrypt($data['password']);
        }
        
        $result = $stmt->execute([
            $data['person_id'] ?? null,
            $data['username'],
            $encryptedPassword,
            $data['service_id'],
            $data['tenant_id'] ?? 1,
            $data['site_id'] ?? null,
            $data['description'] ?? null
        ]);
        
        if ($result) {
            return $this->db->lastInsertId();
        }
        return false;
    }
    
    /**
     * Récupère le mot de passe décrypté d'un compte
     */
    public function getDecryptedPassword($id) {
        $query = "SELECT password FROM logins WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result && !empty($result['password'])) {
            try {
                return PasswordManager::decrypt($result['password']);
            } catch (Exception $e) {
                error_log("Erreur décryption mot de passe ID $id: " . $e->getMessage());
                return null;
            }
        }
        
        return null;
    }
    
    /**
     * Récupère les statistiques des comptes
     */
    public function getStats() {
        $stats = [];
        
        // Nombre total de comptes
        $query = "SELECT COUNT(*) as total FROM logins";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['total'] = $stmt->fetchColumn();
        
        // Comptes avec personne associée
        $query = "SELECT COUNT(*) as with_person FROM logins WHERE person_id IS NOT NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $stats['with_person'] = $stmt->fetchColumn();
        
        // Comptes orphelins
        $stats['orphans'] = $stats['total'] - $stats['with_person'];
        
        return $stats;
    }
    
    /**
     * Récupère tous les comptes techniques (sans personne associée)
     */
    public function getTechnicalAccounts($tenant_id = null, $site_id = null) {
        $query = "SELECT l.*, 
                         ls.nom as service_nom, ls.description as service_description, ls.logo as service_logo
                  FROM logins l
                  JOIN login_services ls ON l.service_id = ls.id
                  WHERE l.person_id IS NULL";
        
        $params = [];
        
        if ($tenant_id && $tenant_id !== 'all') {
            $query .= " AND l.tenant_id = ?";
            $params[] = $tenant_id;
        }
        
        if ($site_id && $site_id !== 'all') {
            $query .= " AND l.site_id = ?";
            $params[] = $site_id;
        }
        
        $query .= " ORDER BY ls.nom, l.username";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Met à jour un compte
     */
    public function update($id, $data) {
        // Construire la requête dynamiquement en fonction des champs fournis
        $fields = [];
        $params = [];
        
        if (isset($data['username'])) {
            $fields[] = "username = ?";
            $params[] = $data['username'];
        }
        
        if (isset($data['service_id'])) {
            $fields[] = "service_id = ?";
            $params[] = $data['service_id'];
        }
        
        if (isset($data['tenant_id'])) {
            $fields[] = "tenant_id = ?";
            $params[] = $data['tenant_id'];
        }
        
        if (isset($data['site_id'])) {
            $fields[] = "site_id = ?";
            $params[] = $data['site_id'];
        }
        
        if (isset($data['description'])) {
            $fields[] = "description = ?";
            $params[] = $data['description'];
        }
        
        // Traiter le mot de passe séparément pour l'encryption
        if (isset($data['password']) && !empty($data['password'])) {
            $fields[] = "password = ?";
            $params[] = PasswordManager::encrypt($data['password']);
        }
        
        if (empty($fields)) {
            return false; // Rien à mettre à jour
        }
        
        $query = "UPDATE logins SET " . implode(", ", $fields) . " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }
    
    /**
     * Supprime un compte
     */
    public function delete($id) {
        $query = "DELETE FROM logins WHERE id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$id]);
    }
}
?> 