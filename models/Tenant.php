<?php

require_once 'config/Database.php';

class Tenant {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Récupère tous les tenants
     */
    public function getAll() {
        $sql = "SELECT id, name, domain, description, nakivo_customer_name, dsd_customer_name, created_at, updated_at 
                FROM tenants 
                ORDER BY name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère un tenant par son ID
     */
    public function getById($id) {
        $sql = "SELECT id, name, domain, description, nakivo_customer_name, dsd_customer_name, created_at, updated_at 
                FROM tenants 
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère un tenant par son nom
     */
    public function getByName($name) {
        $sql = "SELECT id, name, domain, description, nakivo_customer_name, dsd_customer_name, created_at, updated_at 
                FROM tenants 
                WHERE name = :name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crée un nouveau tenant
     */
    public function create($name, $domain = null, $description = null, $nakivo_customer_name = null) {
        $sql = "INSERT INTO tenants (name, domain, description, nakivo_customer_name) 
                VALUES (:name, :domain, :description, :nakivo_customer_name)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':domain', $domain);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':nakivo_customer_name', $nakivo_customer_name);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }
    
    /**
     * Met à jour un tenant
     */
    public function update($id, $name, $domain = null, $description = null, $nakivo_customer_name = null) {
        $sql = "UPDATE tenants 
                SET name = :name, domain = :domain, description = :description, nakivo_customer_name = :nakivo_customer_name, updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':domain', $domain);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':nakivo_customer_name', $nakivo_customer_name);
        
        return $stmt->execute();
    }
    
    /**
     * Supprime un tenant
     */
    public function delete($id) {
        // Vérifier s'il y a des personnes liées à ce tenant
        $checkSql = "SELECT COUNT(*) as count FROM persons WHERE tenant_id = :id";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $checkStmt->execute();
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            throw new Exception("Impossible de supprimer ce tenant car il contient des personnes.");
        }
        
        $sql = "DELETE FROM tenants WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    
    /**
     * Vérifie si un nom de tenant existe déjà
     */
    public function nameExists($name, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM tenants WHERE name = :name";
        $params = [':name' => $name];
        
        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result['count'] > 0;
    }
    
    /**
     * Récupère tous les tenants avec le nombre de sites associés
     */
    public function getTenantsWithSiteCount() {
        $sql = "SELECT t.id, t.name, t.domain, t.description, t.nakivo_customer_name, t.created_at, t.updated_at,
                       COUNT(s.id) as site_count
                FROM tenants t
                LEFT JOIN sites s ON t.id = s.tenant_id
                GROUP BY t.id, t.name, t.domain, t.description, t.nakivo_customer_name, t.created_at, t.updated_at
                ORDER BY t.name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crée un tenant avec validation
     */
    public function createTenant($data) {
        // Vérifier si le nom existe déjà
        if ($this->nameExists($data['name'])) {
            throw new Exception("Un tenant avec ce nom existe déjà.");
        }
        
        $sql = "INSERT INTO tenants (name, domain, description, contact_email, nakivo_customer_name) 
                VALUES (:name, :domain, :description, :contact_email, :nakivo_customer_name)";
        
        $stmt = $this->db->prepare($sql);
        $name = $data['name'];
        $domain = $data['domain'] ?? null;
        $description = $data['description'] ?? null;
        $contact_email = $data['contact_email'] ?? null;
        $nakivo_customer_name = $data['nakivo_customer_name'] ?? null;
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':domain', $domain);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':contact_email', $contact_email);
        $stmt->bindParam(':nakivo_customer_name', $nakivo_customer_name);
        
        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        
        throw new Exception("Erreur lors de la création du tenant.");
    }
    
    /**
     * Met à jour un tenant par ID
     */
    public function updateTenant($id, $data) {
        // Vérifier si le nom existe déjà (en excluant le tenant actuel)
        if ($this->nameExists($data['name'], $id)) {
            throw new Exception("Un tenant avec ce nom existe déjà.");
        }
        
        $sql = "UPDATE tenants 
                SET name = :name, domain = :domain, description = :description, nakivo_customer_name = :nakivo_customer_name, updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':domain', $data['domain']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':nakivo_customer_name', $data['nakivo_customer_name'] ?? null);
        
        if (!$stmt->execute()) {
            throw new Exception("Erreur lors de la mise à jour du tenant.");
        }
        
        return true;
    }
    
    /**
     * Récupère un tenant par ID
     */
    public function getTenantById($id) {
        return $this->getById($id);
    }
    
    /**
     * Supprime un tenant
     */
    public function deleteTenant($id) {
        return $this->delete($id);
    }
    
    /**
     * Récupère les statistiques d'un tenant
     */
    public function getStats($tenantId) {
        $stats = [];
        
        // Nombre de personnes
        $sql = "SELECT COUNT(*) as count FROM persons WHERE tenant_id = :tenant_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        $stats['persons'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        // Nombre de comptes de connexion
        $sql = "SELECT COUNT(*) as count FROM logins l 
                INNER JOIN persons p ON l.person_id = p.id 
                WHERE p.tenant_id = :tenant_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tenant_id', $tenantId, PDO::PARAM_INT);
        $stmt->execute();
        $stats['logins'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        
        return $stats;
    }
}
?>
