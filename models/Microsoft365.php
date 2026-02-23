<?php

require_once 'config/Database.php';

class Microsoft365 {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Récupère le résumé des SKU licences pour un tenant
     */
    public function getSubscribedSkus($tenantId) {
        $sql = "SELECT 
            sku_part_number,
            commercial_name,
            consumed_units,
            enabled_units,
            suspended_units,
            warning_units,
            renewal_date,
            last_updated
        FROM m365_subscribed_skus 
        WHERE tenant_id_ref = ?
        AND commercial_name NOT IN ('Power Automate Free', 'Windows store for business')
        ORDER BY commercial_name";
        
        return $this->db->fetchAll($sql, [$tenantId]);
    }

    /**
     * Récupère les statistiques globales des licences pour un tenant
     */
    public function getLicenseStatistics($tenantId) {
        $sql = "SELECT 
            COUNT(DISTINCT sku_part_number) as total_sku_types,
            SUM(enabled_units) as total_enabled,
            SUM(consumed_units) as total_consumed,
            SUM(enabled_units - consumed_units) as total_available
        FROM m365_subscribed_skus 
        WHERE tenant_id_ref = ?
        AND commercial_name NOT IN ('Power Automate Free', 'Windows store for business')";
        
        $stats = $this->db->fetch($sql, [$tenantId]);
        
        // Calculer le pourcentage d'utilisation
        if ($stats['total_enabled'] > 0) {
            $stats['usage_percentage'] = round(($stats['total_consumed'] / $stats['total_enabled']) * 100, 1);
        } else {
            $stats['usage_percentage'] = 0;
        }
        
        return $stats;
    }

    /**
     * Récupère les utilisateurs avec leurs licences pour un tenant
     */
    public function getUserLicenses($tenantId, $search = null) {
        $sql = "SELECT DISTINCT
            ul.display_name,
            ul.user_principal_name,
            ul.sku_part_number,
            ul.commercial_name,
            ul.assigned_date,
            ul.state,
            ul.last_updated
        FROM m365_user_licenses ul
        WHERE ul.tenant_id_ref = ?";
        
        $params = [$tenantId];
        
        if ($search) {
            $sql .= " AND (ul.display_name LIKE ? OR ul.user_principal_name LIKE ? OR ul.commercial_name LIKE ? OR ul.sku_part_number LIKE ?)";
            $searchParam = '%' . $search . '%';
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        $sql .= " ORDER BY ul.display_name, ul.commercial_name";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Récupère les statistiques des utilisateurs pour un tenant
     */
    public function getUserStatistics($tenantId) {
        $sql = "SELECT 
            COUNT(DISTINCT user_id) as total_users,
            COUNT(CASE WHEN state = 'Active' THEN 1 END) as active_licenses,
            COUNT(CASE WHEN state != 'Active' THEN 1 END) as inactive_licenses,
            COUNT(DISTINCT sku_part_number) as unique_licenses_used
        FROM m365_user_licenses 
        WHERE tenant_id_ref = ?";
        
        return $this->db->fetch($sql, [$tenantId]);
    }

    /**
     * Récupère les types de licences les plus utilisées pour un tenant
     */
    public function getTopLicenses($tenantId, $limit = 5) {
        $sql = "SELECT 
            sku_part_number,
            commercial_name,
            COUNT(*) as user_count,
            COUNT(CASE WHEN state = 'Active' THEN 1 END) as active_count
        FROM m365_user_licenses 
        WHERE tenant_id_ref = ?
        AND commercial_name NOT IN ('Power Automate Free', 'Windows store for business')
        GROUP BY sku_part_number, commercial_name
        ORDER BY user_count DESC
        LIMIT ?";
        
        return $this->db->fetchAll($sql, [$tenantId, $limit]);
    }

    /**
     * Récupère les informations détaillées d'un utilisateur
     */
    public function getUserDetails($userId, $tenantId) {
        $sql = "SELECT 
            ul.*,
            COUNT(*) as total_licenses
        FROM m365_user_licenses ul
        WHERE ul.user_id = ? AND ul.tenant_id_ref = ?
        GROUP BY ul.user_id";
        
        return $this->db->fetch($sql, [$userId, $tenantId]);
    }

    /**
     * Récupère les dates de renouvellement proches (dans les 30 jours)
     */
    public function getUpcomingRenewals($tenantId, $days = 30) {
        $sql = "SELECT 
            sku_part_number,
            commercial_name,
            renewal_date,
            enabled_units,
            consumed_units,
            DATEDIFF(renewal_date, NOW()) as days_until_renewal
        FROM m365_subscribed_skus 
        WHERE tenant_id_ref = ? 
        AND commercial_name NOT IN ('Power Automate Free', 'Windows store for business')
        AND renewal_date IS NOT NULL 
        AND renewal_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? DAY)
        ORDER BY renewal_date ASC";
        
        return $this->db->fetchAll($sql, [$tenantId, $days]);
    }
}