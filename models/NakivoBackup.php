<?php

require_once 'config/Database.php';

class NakivoBackup {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    /**
     * Récupère les rapports de backup par tenant
     */
    public function getBackupReportsByTenant($tenantNakivoName = null) {
        $sql = "SELECT r.*, t.name as tenant_name 
                FROM nakivo_backup_reports r
                LEFT JOIN tenants t ON r.client_name = t.nakivo_customer_name";
        
        $params = [];
        
        if ($tenantNakivoName) {
            $sql .= " WHERE r.client_name = :client_name";
            $params[':client_name'] = $tenantNakivoName;
        }
        
        $sql .= " ORDER BY r.report_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère les jobs de backup pour un rapport
     */
    public function getBackupJobs($reportId) {
        $sql = "SELECT * FROM nakivo_backup_jobs 
                WHERE report_id = :report_id 
                ORDER BY started_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':report_id', $reportId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère les VMs de backup pour un job
     */
    public function getBackupVMs($jobId) {
        $sql = "SELECT * FROM nakivo_backup_vms 
                WHERE job_id = :job_id 
                ORDER BY vm_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère les stockages cibles pour un rapport
     */
    public function getTargetStorage($reportId) {
        $sql = "SELECT * FROM nakivo_target_storage 
                WHERE report_id = :report_id 
                ORDER BY storage_name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':report_id', $reportId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère les statistiques globales
     */
    public function getGlobalStats() {
        $sql = "SELECT 
                    COUNT(*) as total_reports,
                    SUM(total_jobs) as total_jobs,
                    SUM(total_vms) as total_vms,
                    SUM(total_data_gb) as total_data_gb,
                    AVG(duration_seconds) as avg_duration
                FROM nakivo_backup_reports 
                WHERE report_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère les rapports récents (7 derniers jours)
     */
    public function getRecentReports($limit = 10) {
        $sql = "SELECT r.*, t.name as tenant_name 
                FROM nakivo_backup_reports r
                LEFT JOIN tenants t ON r.client_name = t.nakivo_customer_name
                WHERE r.report_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                ORDER BY r.report_date DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère les tenants avec des rapports Nakivo
     */
    public function getTenantsWithReports() {
        $sql = "SELECT DISTINCT t.id, t.name, t.nakivo_customer_name,
                       COUNT(r.id) as report_count,
                       MAX(r.report_date) as last_report_date
                FROM tenants t
                LEFT JOIN nakivo_backup_reports r ON t.nakivo_customer_name = r.client_name
                WHERE t.nakivo_customer_name IS NOT NULL AND t.nakivo_customer_name != ''
                GROUP BY t.id, t.name, t.nakivo_customer_name
                ORDER BY t.name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère un rapport spécifique avec statistiques calculées
     */
    public function getReportById($reportId) {
        $sql = "SELECT r.*, t.name as tenant_name 
                FROM nakivo_backup_reports r
                LEFT JOIN tenants t ON r.client_name = t.nakivo_customer_name
                WHERE r.id = :report_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':report_id', $reportId, PDO::PARAM_INT);
        $stmt->execute();
        
        $report = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($report) {
            // Calculer les statistiques détaillées des jobs
            $jobStats = $this->getJobStatistics($reportId);
            $report = array_merge($report, $jobStats);
        }
        
        return $report;
    }
    
    /**
     * Calcule les statistiques des jobs pour un rapport
     */
    private function getJobStatistics($reportId) {
        $sql = "SELECT 
                    COUNT(*) as total_jobs,
                    SUM(CASE WHEN status = 'Successful' THEN 1 ELSE 0 END) as successful_jobs,
                    SUM(CASE WHEN status = 'Failed' THEN 1 ELSE 0 END) as failed_jobs,
                    SUM(CASE WHEN status = 'Stopped' THEN 1 ELSE 0 END) as stopped_jobs,
                    SUM(CASE WHEN status = 'Running' THEN 1 ELSE 0 END) as running_jobs
                FROM nakivo_backup_jobs 
                WHERE report_id = :report_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':report_id', $reportId, PDO::PARAM_INT);
        $stmt->execute();
        
        $jobStats = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Calculer aussi les statistiques des VMs
        $vmStats = $this->getVMStatistics($reportId);
        
        return array_merge($jobStats, $vmStats);
    }
    
    /**
     * Calcule les statistiques des VMs pour un rapport
     */
    private function getVMStatistics($reportId) {
        $sql = "SELECT 
                    COUNT(*) as total_vms,
                    SUM(CASE WHEN vm.status = 'Successful' THEN 1 ELSE 0 END) as successful_vms,
                    SUM(CASE WHEN vm.status = 'Failed' THEN 1 ELSE 0 END) as failed_vms,
                    SUM(CASE WHEN vm.status = 'Skipped' THEN 1 ELSE 0 END) as skipped_vms,
                    SUM(CASE WHEN vm.status = 'Stopped' THEN 1 ELSE 0 END) as stopped_vms,
                    SUM(CASE WHEN vm.status = 'Unknown' THEN 1 ELSE 0 END) as unknown_vms,
                    SUM(vm.data_processed_gb) as total_data_processed_gb
                FROM nakivo_backup_vms vm
                JOIN nakivo_backup_jobs j ON vm.job_id = j.id
                WHERE j.report_id = :report_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':report_id', $reportId, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Récupère un job spécifique avec ses VMs
     */
    public function getJobWithVMs($jobId) {
        $sql = "SELECT j.*, r.client_name, r.report_date
                FROM nakivo_backup_jobs j
                JOIN nakivo_backup_reports r ON j.report_id = r.id
                WHERE j.id = :job_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':job_id', $jobId, PDO::PARAM_INT);
        $stmt->execute();
        
        $job = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($job) {
            $job['vms'] = $this->getBackupVMs($jobId);
        }
        
        return $job;
    }
}
?> 