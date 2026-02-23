<?php

require_once 'controllers/BaseController.php';
require_once 'models/NakivoBackup.php';

class BackupController extends BaseController {
    private $nakivoBackup;

    public function __construct() {
        $this->nakivoBackup = new NakivoBackup();
    }

    public function index() {
        $this->requireAdmin();
        $this->handleTenantSiteSelection();

        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        
        // Récupérer les statistiques globales
        $globalStats = $this->nakivoBackup->getGlobalStats();
        
        // Récupérer les rapports récents
        $recentReports = $this->nakivoBackup->getRecentReports();
        
        // Récupérer les tenants avec des rapports
        $tenantsWithReports = $this->nakivoBackup->getTenantsWithReports();

        $this->loadView('backup/index', [
            'globalStats' => $globalStats,
            'recentReports' => $recentReports,
            'tenantsWithReports' => $tenantsWithReports,
            'currentTenant' => $currentTenant
        ]);
    }

    public function nakivo() {
        $this->requireAdmin();
        $this->handleTenantSiteSelection();

        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        
        // Récupérer le nom Nakivo du tenant sélectionné
        $tenantNakivoName = null;
        if ($currentTenant !== 'all') {
            require_once 'models/Tenant.php';
            $tenantModel = new Tenant();
            $tenant = $tenantModel->getTenantById($currentTenant);
            if ($tenant && !empty($tenant['nakivo_customer_name'])) {
                $tenantNakivoName = $tenant['nakivo_customer_name'];
            }
        }
        
        // Récupérer les rapports de backup
        $backupReports = $this->nakivoBackup->getBackupReportsByTenant($tenantNakivoName);
        
        // Récupérer les statistiques
        $globalStats = $this->nakivoBackup->getGlobalStats();

        $this->loadView('backup/nakivo', [
            'backupReports' => $backupReports,
            'globalStats' => $globalStats,
            'currentTenant' => $currentTenant,
            'tenantNakivoName' => $tenantNakivoName
        ]);
    }

    public function report() {
        $this->requireAdmin();
        
        $reportId = $_GET['id'] ?? 0;
        
        if (!$reportId) {
            $this->redirectWithMessage('backup', 'nakivo', 'Rapport non spécifié', 'error');
        }
        
        // Récupérer le rapport
        $report = $this->nakivoBackup->getReportById($reportId);
        
        if (!$report) {
            $this->redirectWithMessage('backup', 'nakivo', 'Rapport non trouvé', 'error');
        }
        
        // Récupérer les jobs
        $jobs = $this->nakivoBackup->getBackupJobs($reportId);
        
        // Récupérer les stockages
        $storage = $this->nakivoBackup->getTargetStorage($reportId);

        $this->loadView('backup/report', [
            'report' => $report,
            'jobs' => $jobs,
            'storage' => $storage
        ]);
    }

    public function job() {
        $this->requireAdmin();
        
        $jobId = $_GET['id'] ?? 0;
        
        if (!$jobId) {
            $this->redirectWithMessage('backup', 'nakivo', 'Job non spécifié', 'error');
        }
        
        // Récupérer le job avec ses VMs
        $job = $this->nakivoBackup->getJobWithVMs($jobId);
        
        if (!$job) {
            $this->redirectWithMessage('backup', 'nakivo', 'Job non trouvé', 'error');
        }

        $this->loadView('backup/job', [
            'job' => $job
        ]);
    }

    public function eurobackup() {
        $this->requireAdmin();
        $this->handleTenantSiteSelection();

        // TODO: Implémenter EuroBackup quand les tables seront disponibles
        $this->loadView('backup/eurobackup', [
            'message' => 'Module EuroBackup en cours de développement'
        ]);
    }
}
?> 