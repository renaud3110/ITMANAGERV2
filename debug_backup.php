<?php
require_once 'config/Database.php';
require_once 'models/NakivoBackup.php';

echo "<h1>Debug Backup System</h1>";

try {
    $nakivo = new NakivoBackup();
    
    echo "<h2>1. Test de connexion à la base</h2>";
    $database = new Database();
    $db = $database->getConnection();
    echo "✅ Connexion OK<br>";
    
    echo "<h2>2. Vérification des tables</h2>";
    $tables = ['nakivo_backup_reports', 'nakivo_backup_jobs', 'nakivo_backup_vms', 'nakivo_target_storage'];
    foreach ($tables as $table) {
        $stmt = $db->query("SELECT COUNT(*) as count FROM $table");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Table $table: {$result['count']} enregistrements<br>";
    }
    
    echo "<h2>3. Test getGlobalStats()</h2>";
    $globalStats = $nakivo->getGlobalStats();
    echo "<pre>";
    print_r($globalStats);
    echo "</pre>";
    
    echo "<h2>4. Test getRecentReports()</h2>";
    $recentReports = $nakivo->getRecentReports(5);
    echo "Nombre de rapports récents: " . count($recentReports) . "<br>";
    echo "<pre>";
    print_r($recentReports);
    echo "</pre>";
    
    echo "<h2>5. Test getTenantsWithReports()</h2>";
    $tenantsWithReports = $nakivo->getTenantsWithReports();
    echo "Nombre de tenants avec rapports: " . count($tenantsWithReports) . "<br>";
    echo "<pre>";
    print_r($tenantsWithReports);
    echo "</pre>";
    
    echo "<h2>6. Test getBackupReportsByTenant() - Tous les tenants</h2>";
    $allReports = $nakivo->getBackupReportsByTenant();
    echo "Nombre total de rapports: " . count($allReports) . "<br>";
    if (!empty($allReports)) {
        echo "Premier rapport:<br>";
        echo "<pre>";
        print_r($allReports[0]);
        echo "</pre>";
    }
    
    echo "<h2>7. Vérification des tenants avec nakivo_customer_name</h2>";
    $stmt = $db->query("SELECT id, name, nakivo_customer_name FROM tenants WHERE nakivo_customer_name IS NOT NULL AND nakivo_customer_name != ''");
    $tenants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Tenants avec nakivo_customer_name: " . count($tenants) . "<br>";
    echo "<pre>";
    print_r($tenants);
    echo "</pre>";
    
    echo "<h2>8. Test avec un tenant spécifique</h2>";
    if (!empty($tenants)) {
        $firstTenant = $tenants[0];
        echo "Test avec tenant: {$firstTenant['name']} (nakivo_customer_name: {$firstTenant['nakivo_customer_name']})<br>";
        
        $reportsForTenant = $nakivo->getBackupReportsByTenant($firstTenant['nakivo_customer_name']);
        echo "Rapports pour ce tenant: " . count($reportsForTenant) . "<br>";
        if (!empty($reportsForTenant)) {
            echo "<pre>";
            print_r($reportsForTenant[0]);
            echo "</pre>";
        }
    }
    
} catch (Exception $e) {
    echo "<h2>❌ Erreur</h2>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?> 