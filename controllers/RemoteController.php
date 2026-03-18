<?php

require_once 'models/Server.php';
require_once 'models/Computer.php';

class RemoteController extends BaseController {

    private $serverModel;
    private $computerModel;

    public function __construct() {
        parent::__construct();
        $this->serverModel = new Server();
        $this->computerModel = new Computer();
    }

    public function index() {
        $this->handleTenantSiteSelection();

        $currentTenant = $_SESSION['current_tenant'] ?? 'all';
        $currentSite = $_SESSION['current_site'] ?? 'all';

        $allServers = $this->serverModel->getAll($currentTenant, $currentSite);
        $allComputers = $this->computerModel->getAll($currentTenant, $currentSite);

        // Filtrer uniquement ceux avec RustDesk ID
        $servers = array_filter($allServers, function ($s) {
            return !empty(trim($s['rustdesk_id'] ?? ''));
        });
        $computers = array_filter($allComputers, function ($c) {
            return !empty(trim($c['rustdesk_id'] ?? ''));
        });

        $this->loadView('remote/index', [
            'servers' => array_values($servers),
            'computers' => array_values($computers),
            'currentTenant' => $currentTenant,
            'currentSite' => $currentSite
        ]);
    }
}
