<?php

require_once 'config/Database.php';

class License {
    private $db;
    private $encryptionKey;

    public function __construct() {
        $this->db = new Database();
        // Clé de cryptage - en production, à stocker dans un fichier de configuration sécurisé
        $this->encryptionKey = 'LicenseManager2024SecretKey!';
    }

    /**
     * Crypte un mot de passe
     */
    private function encryptPassword($password) {
        if (empty($password)) return null;
        return base64_encode(openssl_encrypt($password, 'AES-256-CBC', $this->encryptionKey, 0, substr($this->encryptionKey, 0, 16)));
    }

    /**
     * Décrypte un mot de passe
     */
    private function decryptPassword($encryptedPassword) {
        if (empty($encryptedPassword)) return null;
        return openssl_decrypt(base64_decode($encryptedPassword), 'AES-256-CBC', $this->encryptionKey, 0, substr($this->encryptionKey, 0, 16));
    }

    /**
     * Récupère toutes les licences pour un tenant ou tous les tenants
     */
    public function getAll($tenantId = null) {
        $sql = "SELECT 
            l.*,
            t.name as tenant_name,
            CASE 
                WHEN l.expiry_date IS NULL THEN 'Non définie'
                WHEN l.expiry_date < CURDATE() THEN 'Expirée'
                WHEN l.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Expire bientôt'
                ELSE 'Valide'
            END as status_text,
            CASE 
                WHEN l.expiry_date IS NULL THEN NULL
                WHEN l.expiry_date < CURDATE() THEN 'expired'
                WHEN l.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'warning'
                ELSE 'valid'
            END as status_class,
            DATEDIFF(l.expiry_date, CURDATE()) as days_until_expiry
        FROM licenses l
        LEFT JOIN tenants t ON l.tenant_id = t.id
        WHERE 1=1";
        
        $params = [];
        
        if ($tenantId && $tenantId !== 'all') {
            $sql .= " AND l.tenant_id = ?";
            $params[] = $tenantId;
        }
        
        $sql .= " ORDER BY l.license_name ASC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Récupère une licence par son ID (avec mot de passe décrypté)
     */
    public function getById($id) {
        $sql = "SELECT 
            l.*,
            t.name as tenant_name
        FROM licenses l
        LEFT JOIN tenants t ON l.tenant_id = t.id
        WHERE l.id = ?";
        
        $license = $this->db->fetch($sql, [$id]);
        
        if ($license && !empty($license['password'])) {
            $license['decrypted_password'] = $this->decryptPassword($license['password']);
        }
        
        return $license;
    }

    /**
     * Crée une nouvelle licence
     */
    public function create($data) {
        $sql = "INSERT INTO licenses (
            tenant_id, 
            license_name, 
            login, 
            password, 
            license_count, 
            expiry_date, 
            description
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        return $this->db->query($sql, [
            $data['tenant_id'],
            $data['license_name'],
            $data['login'] ?: null,
            $this->encryptPassword($data['password'] ?? null),
            $data['license_count'] ?? 1,
            $data['expiry_date'] ?: null,
            $data['description'] ?: null
        ]);
    }

    /**
     * Met à jour une licence
     */
    public function update($id, $data) {
        // Si le mot de passe n'est pas fourni ou est vide, on garde l'ancien
        $passwordUpdate = '';
        $params = [];
        
        if (isset($data['password']) && !empty($data['password'])) {
            $passwordUpdate = ', password = ?';
            $params[] = $this->encryptPassword($data['password']);
        }
        
        $sql = "UPDATE licenses SET 
            tenant_id = ?, 
            license_name = ?, 
            login = ?, 
            license_count = ?, 
            expiry_date = ?, 
            description = ?,
            updated_at = CURRENT_TIMESTAMP
            $passwordUpdate
        WHERE id = ?";
        
        $updateParams = [
            $data['tenant_id'],
            $data['license_name'],
            $data['login'] ?: null,
            $data['license_count'] ?? 1,
            $data['expiry_date'] ?: null,
            $data['description'] ?: null
        ];
        
        // Ajouter le mot de passe crypté si fourni
        if (!empty($params)) {
            $updateParams = array_merge($updateParams, $params);
        }
        
        $updateParams[] = $id;
        
        return $this->db->query($sql, $updateParams);
    }

    /**
     * Supprime une licence
     */
    public function delete($id) {
        return $this->db->query("DELETE FROM licenses WHERE id = ?", [$id]);
    }

    /**
     * Vérifie si une licence existe déjà pour un tenant
     */
    public function exists($licenseName, $tenantId, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM licenses 
                WHERE license_name = ? AND tenant_id = ?";
        $params = [$licenseName, $tenantId];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }

    /**
     * Récupère les statistiques des licences pour un tenant
     */
    public function getStatistics($tenantId = null) {
        $sql = "SELECT 
            COUNT(*) as total_licenses,
            SUM(license_count) as total_license_count,
            COUNT(CASE WHEN login IS NOT NULL AND login != '' THEN 1 END) as licenses_with_login,
            COUNT(CASE WHEN password IS NOT NULL AND password != '' THEN 1 END) as licenses_with_password,
            COUNT(CASE WHEN expiry_date < CURDATE() THEN 1 END) as expired_licenses,
            COUNT(CASE WHEN expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 1 END) as expiring_soon_licenses,
            AVG(license_count) as avg_license_count
        FROM licenses
        WHERE 1=1";
        
        $params = [];
        
        if ($tenantId && $tenantId !== 'all') {
            $sql .= " AND tenant_id = ?";
            $params[] = $tenantId;
        }
        
        $stats = $this->db->fetch($sql, $params);
        
        // Arrondir la moyenne
        if ($stats['avg_license_count']) {
            $stats['avg_license_count'] = round($stats['avg_license_count'], 1);
        }
        
        return $stats;
    }

    /**
     * Récupère les licences qui expirent bientôt
     */
    public function getExpiringSoon($tenantId = null, $days = 30) {
        $sql = "SELECT 
            l.*,
            t.name as tenant_name,
            DATEDIFF(l.expiry_date, CURDATE()) as days_until_expiry
        FROM licenses l
        LEFT JOIN tenants t ON l.tenant_id = t.id
        WHERE l.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)";
        
        $params = [$days];
        
        if ($tenantId && $tenantId !== 'all') {
            $sql .= " AND l.tenant_id = ?";
            $params[] = $tenantId;
        }
        
        $sql .= " ORDER BY l.expiry_date ASC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Récupère les licences expirées
     */
    public function getExpired($tenantId = null) {
        $sql = "SELECT 
            l.*,
            t.name as tenant_name,
            ABS(DATEDIFF(l.expiry_date, CURDATE())) as days_since_expired
        FROM licenses l
        LEFT JOIN tenants t ON l.tenant_id = t.id
        WHERE l.expiry_date < CURDATE()";
        
        $params = [];
        
        if ($tenantId && $tenantId !== 'all') {
            $sql .= " AND l.tenant_id = ?";
            $params[] = $tenantId;
        }
        
        $sql .= " ORDER BY l.expiry_date ASC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Récupère le mot de passe décrypté d'une licence (pour AJAX)
     */
    public function getDecryptedPassword($id) {
        $sql = "SELECT password FROM licenses WHERE id = ?";
        $result = $this->db->fetch($sql, [$id]);
        
        if ($result && !empty($result['password'])) {
            return $this->decryptPassword($result['password']);
        }
        
        return null;
    }

    /**
     * Recherche des licences par nom
     */
    public function search($searchTerm, $tenantId = null) {
        $sql = "SELECT 
            l.*,
            t.name as tenant_name,
            CASE 
                WHEN l.expiry_date IS NULL THEN 'Non définie'
                WHEN l.expiry_date < CURDATE() THEN 'Expirée'
                WHEN l.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'Expire bientôt'
                ELSE 'Valide'
            END as status_text,
            CASE 
                WHEN l.expiry_date IS NULL THEN NULL
                WHEN l.expiry_date < CURDATE() THEN 'expired'
                WHEN l.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'warning'
                ELSE 'valid'
            END as status_class
        FROM licenses l
        LEFT JOIN tenants t ON l.tenant_id = t.id
        WHERE (l.license_name LIKE ? OR l.login LIKE ? OR l.description LIKE ?)";
        
        $searchParam = '%' . $searchTerm . '%';
        $params = [$searchParam, $searchParam, $searchParam];
        
        if ($tenantId && $tenantId !== 'all') {
            $sql .= " AND l.tenant_id = ?";
            $params[] = $tenantId;
        }
        
        $sql .= " ORDER BY l.license_name ASC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Récupère les licences par type/nom (regroupées)
     */
    public function getLicenseTypes($tenantId = null) {
        $sql = "SELECT 
            license_name,
            COUNT(*) as count,
            SUM(license_count) as total_count
        FROM licenses 
        WHERE 1=1";
        
        $params = [];
        
        if ($tenantId && $tenantId !== 'all') {
            $sql .= " AND tenant_id = ?";
            $params[] = $tenantId;
        }
        
        $sql .= " GROUP BY license_name ORDER BY total_count DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
}