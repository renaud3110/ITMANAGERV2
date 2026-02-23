<?php

require_once 'config/Database.php';

class DsdFactures {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Récupère les factures par tenant
     */
    public function getFacturesByTenant($tenantDsdName = null) {
        $sql = "SELECT DISTINCT f.*, t.name as tenant_name 
                FROM factures f
                JOIN licences_facture lf ON f.id = lf.facture_id
                LEFT JOIN tenants t ON lf.client = t.dsd_customer_name";
        
        $params = [];
        
        if ($tenantDsdName) {
            $sql .= " WHERE lf.client = :client_name";
            $params[':client_name'] = $tenantDsdName;
        }
        
        $sql .= " ORDER BY f.received_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère les licences d'une facture
     */
    public function getLicencesByFacture($factureId) {
        $sql = "SELECT * FROM licences_facture 
                WHERE facture_id = :facture_id 
                ORDER BY license_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':facture_id', $factureId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère l'évolution des licences par tenant
     */
    public function getLicenceEvolution($tenantDsdName) {
        $sql = "SELECT 
                    lf.license_name,
                    DATE_FORMAT(f.received_date, '%Y-%m') as month,
                    SUM(lf.quantity) as total_quantity,
                    COUNT(DISTINCT f.id) as facture_count
                FROM licences_facture lf
                JOIN factures f ON lf.facture_id = f.id
                WHERE lf.client = :client_name
                GROUP BY lf.license_name, DATE_FORMAT(f.received_date, '%Y-%m')
                ORDER BY lf.license_name, month ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':client_name', $tenantDsdName, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère l'évolution d'une licence spécifique
     */
    public function getLicenceEvolutionDetail($tenantDsdName, $licenseName) {
        $sql = "SELECT 
                    DATE_FORMAT(f.received_date, '%Y-%m') as month,
                    f.received_date,
                    lf.quantity,
                    lf.total_price as montant,
                    f.subject
                FROM licences_facture lf
                JOIN factures f ON lf.facture_id = f.id
                WHERE lf.client = :client_name 
                AND lf.license_name = :license_name
                ORDER BY f.received_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':client_name', $tenantDsdName, PDO::PARAM_STR);
        $stmt->bindParam(':license_name', $licenseName, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère les données pour le tableau à deux entrées (licences x mois)
     */
    public function getLicenceMatrix($tenantDsdName) {
        // Récupérer toutes les données d'évolution
        $evolution = $this->getLicenceEvolution($tenantDsdName);
        
        $licenses = [];
        $months = [];
        $matrix = [];
        
        // Organiser les données
        foreach ($evolution as $row) {
            $licenseName = $row['license_name'];
            $month = $row['month'];
            $quantity = $row['total_quantity'];
            
            // Ajouter la licence à la liste
            if (!in_array($licenseName, $licenses)) {
                $licenses[] = $licenseName;
            }
            
            // Ajouter le mois à la liste
            if (!in_array($month, $months)) {
                $months[] = $month;
            }
            
            // Stocker la quantité
            $matrix[$licenseName][$month] = $quantity;
        }
        
        // Trier les licences et mois
        sort($licenses);
        sort($months);
        
        return [
            'licenses' => $licenses,
            'months' => $months,
            'matrix' => $matrix
        ];
    }
    
    /**
     * Récupère les tenants avec des factures DSD
     */
    public function getTenantsWithFactures() {
        $sql = "SELECT DISTINCT t.id, t.name, t.dsd_customer_name,
                       COUNT(DISTINCT f.id) as facture_count,
                       MAX(f.received_date) as last_facture_date
                FROM tenants t
                LEFT JOIN licences_facture lf ON t.dsd_customer_name = lf.client
                LEFT JOIN factures f ON lf.facture_id = f.id
                WHERE t.dsd_customer_name IS NOT NULL AND t.dsd_customer_name != ''
                GROUP BY t.id, t.name, t.dsd_customer_name
                ORDER BY t.name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère les statistiques globales
     */
    public function getGlobalStats() {
        $sql = "SELECT 
                    COUNT(DISTINCT f.id) as total_factures,
                    COUNT(DISTINCT lf.license_name) as total_licenses,
                    SUM(lf.quantity) as total_licenses_quantity,
                    SUM(lf.total_price) as total_amount
                FROM factures f
                JOIN licences_facture lf ON f.id = lf.facture_id
                WHERE f.received_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?> 