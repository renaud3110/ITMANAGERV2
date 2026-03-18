<?php
/**
 * Configuration API inventaire
 * Copier vers api_config.php et renseigner la clé API
 */
define('API_INVENTORY_KEY', 'changez-moi-clé-secrète-longue');
define('API_INVENTORY_ENABLED', true);

// Découverte NAS par agent sur site (optionnel)
// Clé pour chiffrer les identifiants NAS. Par défaut utilise API_INVENTORY_KEY.
// define('DISCOVERY_CREDENTIALS_KEY', 'clé-32-caractères-minimum');

// RustDesk Pro - statut en ligne des clients (optionnel)
// Créer un token dans la console : Settings → Tokens, permission user/device/audit/ab
define('RUSTDESK_API_URL', 'http://votre-serveur-rustdesk:21114');
define('RUSTDESK_API_TOKEN', 'votre-token-api');

// Protocole pour les liens de connexion à distance (rustdesk ou supportrgd)
// Si vous utilisez supportrgd (RustDesk personnalisé dans C:\Program Files\supportrgd\),
// définir 'supportrgd' car le client peut enregistrer supportrgd:// au lieu de rustdesk://
// define('RUSTDESK_PROTOCOL', 'supportrgd');
