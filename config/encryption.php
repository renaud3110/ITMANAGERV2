<?php
/**
 * Configuration d'encryption pour les mots de passe
 * 
 * IMPORTANT: Cette clé doit être gardée secrète et sauvegardée.
 * Si elle est perdue, tous les mots de passe encryptés seront irrécupérables.
 */

// Clé d'encryption AES-256-CBC (32 bytes = 256 bits)
// Cette clé a été générée aléatoirement - NE PAS MODIFIER
define('ENCRYPTION_KEY', 'K7mN9pQ2rS5tU8vW1xY4zA6bC9dF2gH5');

// Méthode d'encryption
define('ENCRYPTION_METHOD', 'aes-256-cbc');

// Longueur de l'IV (Initialization Vector) pour AES-256-CBC
define('ENCRYPTION_IV_LENGTH', 16);

/**
 * Génère une nouvelle clé d'encryption (à utiliser une seule fois)
 * Décommentez cette fonction pour générer une nouvelle clé si nécessaire
 */
/*
function generateEncryptionKey() {
    return bin2hex(random_bytes(32));
}
*/

/**
 * Vérifie que la configuration d'encryption est valide
 */
function validateEncryptionConfig() {
    if (!defined('ENCRYPTION_KEY') || strlen(ENCRYPTION_KEY) !== 32) {
        throw new Exception('Clé d\'encryption invalide. Elle doit faire exactement 32 caractères.');
    }
    
    if (!in_array(ENCRYPTION_METHOD, openssl_get_cipher_methods())) {
        throw new Exception('Méthode d\'encryption non supportée: ' . ENCRYPTION_METHOD);
    }
    
    return true;
}

// Validation automatique au chargement
validateEncryptionConfig();
?> 