<?php

class SecurityController extends BaseController {

    public function __construct() {
        // Initialisation du contrôleur
    }

    public function index() {
        $this->handleTenantSiteSelection();

        $flash = $this->getFlashMessage();

        $this->loadView('security/index', [
            'flash' => $flash
        ]);
    }
}
