<?php

require_once 'config/Database.php';

class Domain {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Récupère tous les domaines pour un tenant ou tous les tenants
     */
    public function getAll($tenantId = null) {
        $sql = "SELECT 
            d.*,
            t.name as tenant_name,
            CASE 
                WHEN d.expiry_date IS NULL THEN 'Non définie'
                WHEN d.expiry_date < CURDATE() THEN 'Expiré'
                WHEN d.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Expire bientôt'
                ELSE 'Valide'
            END as status_text,
            CASE 
                WHEN d.expiry_date IS NULL THEN NULL
                WHEN d.expiry_date < CURDATE() THEN 'expired'
                WHEN d.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'warning'
                ELSE 'valid'
            END as status_class,
            DATEDIFF(d.expiry_date, CURDATE()) as days_until_expiry
        FROM domains d
        LEFT JOIN tenants t ON d.tenant_id = t.id
        WHERE 1=1";
        
        $params = [];
        
        if ($tenantId && $tenantId !== 'all') {
            $sql .= " AND d.tenant_id = ?";
            $params[] = $tenantId;
        }
        
        $sql .= " ORDER BY d.domain_name ASC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Récupère un domaine par son ID
     */
    public function getById($id) {
        $sql = "SELECT 
            d.*,
            t.name as tenant_name
        FROM domains d
        LEFT JOIN tenants t ON d.tenant_id = t.id
        WHERE d.id = ?";
        
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Crée un nouveau domaine
     */
    public function create($data) {
        $sql = "INSERT INTO domains (
            domain_name, 
            tenant_id, 
            is_managed, 
            expiry_date, 
            hosting_provider, 
            auto_renewal
        ) VALUES (?, ?, ?, ?, ?, ?)";
        
        return $this->db->query($sql, [
            $data['domain_name'],
            $data['tenant_id'],
            $data['is_managed'] ?? true,
            $data['expiry_date'] ?: null,
            $data['hosting_provider'] ?: null,
            $data['auto_renewal'] ?? false
        ]);
    }

    /**
     * Met à jour un domaine
     */
    public function update($id, $data) {
        $sql = "UPDATE domains SET 
            domain_name = ?, 
            tenant_id = ?, 
            is_managed = ?, 
            expiry_date = ?, 
            hosting_provider = ?, 
            auto_renewal = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = ?";
        
        return $this->db->query($sql, [
            $data['domain_name'],
            $data['tenant_id'],
            $data['is_managed'] ?? true,
            $data['expiry_date'] ?: null,
            $data['hosting_provider'] ?: null,
            $data['auto_renewal'] ?? false,
            $id
        ]);
    }

    /**
     * Supprime un domaine
     */
    public function delete($id) {
        return $this->db->query("DELETE FROM domains WHERE id = ?", [$id]);
    }

    /**
     * Vérifie si un domaine existe déjà pour un tenant
     */
    public function exists($domainName, $tenantId, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM domains 
                WHERE domain_name = ? AND tenant_id = ?";
        $params = [$domainName, $tenantId];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Récupère les statistiques des domaines pour un tenant
     */
    public function getStatistics($tenantId = null) {
        $sql = "SELECT 
            COUNT(*) as total_domains,
            COUNT(CASE WHEN is_managed = 1 THEN 1 END) as managed_domains,
            COUNT(CASE WHEN is_managed = 0 THEN 1 END) as unmanaged_domains,
            COUNT(CASE WHEN auto_renewal = 1 THEN 1 END) as auto_renewal_domains,
            COUNT(CASE WHEN expiry_date < CURDATE() THEN 1 END) as expired_domains,
            COUNT(CASE WHEN expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as expiring_soon_domains
        FROM domains
        WHERE 1=1";
        
        $params = [];
        
        if ($tenantId && $tenantId !== 'all') {
            $sql .= " AND tenant_id = ?";
            $params[] = $tenantId;
        }
        
        return $this->db->fetch($sql, $params);
    }

    /**
     * Récupère les domaines qui expirent bientôt
     */
    public function getExpiringSoon($tenantId = null, $days = 30) {
        $sql = "SELECT 
            d.*,
            t.name as tenant_name,
            DATEDIFF(d.expiry_date, CURDATE()) as days_until_expiry
        FROM domains d
        LEFT JOIN tenants t ON d.tenant_id = t.id
        WHERE d.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)";
        
        $params = [$days];
        
        if ($tenantId && $tenantId !== 'all') {
            $sql .= " AND d.tenant_id = ?";
            $params[] = $tenantId;
        }
        
        $sql .= " ORDER BY d.expiry_date ASC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Récupère les domaines expirés
     */
    public function getExpired($tenantId = null) {
        $sql = "SELECT 
            d.*,
            t.name as tenant_name,
            ABS(DATEDIFF(d.expiry_date, CURDATE())) as days_since_expired
        FROM domains d
        LEFT JOIN tenants t ON d.tenant_id = t.id
        WHERE d.expiry_date < CURDATE()";
        
        $params = [];
        
        if ($tenantId && $tenantId !== 'all') {
            $sql .= " AND d.tenant_id = ?";
            $params[] = $tenantId;
        }
        
        $sql .= " ORDER BY d.expiry_date ASC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Récupère la liste des hébergeurs/registrars utilisés
     */
    public function getHostingProviders($tenantId = null) {
        $sql = "SELECT 
            hosting_provider,
            COUNT(*) as domain_count
        FROM domains 
        WHERE hosting_provider IS NOT NULL AND hosting_provider != ''";
        
        $params = [];
        
        if ($tenantId && $tenantId !== 'all') {
            $sql .= " AND tenant_id = ?";
            $params[] = $tenantId;
        }
        
        $sql .= " GROUP BY hosting_provider ORDER BY domain_count DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Recherche des domaines par nom
     */
    public function search($searchTerm, $tenantId = null) {
        $sql = "SELECT 
            d.*,
            t.name as tenant_name
        FROM domains d
        LEFT JOIN tenants t ON d.tenant_id = t.id
        WHERE d.domain_name LIKE ?";
        
        $params = ['%' . $searchTerm . '%'];
        
        if ($tenantId && $tenantId !== 'all') {
            $sql .= " AND d.tenant_id = ?";
            $params[] = $tenantId;
        }
        
        $sql .= " ORDER BY d.domain_name ASC";
        
        return $this->db->fetchAll($sql, $params);
    }
}