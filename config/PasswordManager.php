<?php
require_once 'encryption.php';

/**
 * Gestionnaire d'encryption/decryption des mots de passe
 * Utilise AES-256-CBC pour sécuriser les mots de passe stockés
 */
class PasswordManager {
    
    /**
     * Encrypte un mot de passe
     * 
     * @param string $password Le mot de passe en clair
     * @return string Le mot de passe encrypté (base64)
     * @throws Exception Si l'encryption échoue
     */
    public static function encrypt($password) {
        if (empty($password)) {
            return '';
        }
        
        try {
            // Génération d'un IV aléatoire pour chaque encryption
            $iv = openssl_random_pseudo_bytes(ENCRYPTION_IV_LENGTH);
            
            // Encryption du mot de passe
            $encrypted = openssl_encrypt(
                $password, 
                ENCRYPTION_METHOD, 
                ENCRYPTION_KEY, 
                OPENSSL_RAW_DATA, 
                $iv
            );
            
            if ($encrypted === false) {
                throw new Exception('Erreur lors de l\'encryption du mot de passe');
            }
            
            // Concaténation IV + données encryptées et encodage base64
            return base64_encode($iv . $encrypted);
            
        } catch (Exception $e) {
            error_log('Erreur encryption mot de passe: ' . $e->getMessage());
            throw new Exception('Impossible d\'encrypter le mot de passe');
        }
    }
    
    /**
     * Décrypte un mot de passe
     * 
     * @param string $encryptedPassword Le mot de passe encrypté (base64)
     * @return string Le mot de passe en clair
     * @throws Exception Si la decryption échoue
     */
    public static function decrypt($encryptedPassword) {
        if (empty($encryptedPassword)) {
            return '';
        }
        
        try {
            // Décodage base64
            $data = base64_decode($encryptedPassword);
            
            if ($data === false || strlen($data) < ENCRYPTION_IV_LENGTH) {
                throw new Exception('Données encryptées invalides');
            }
            
            // Extraction de l'IV et des données encryptées
            $iv = substr($data, 0, ENCRYPTION_IV_LENGTH);
            $encrypted = substr($data, ENCRYPTION_IV_LENGTH);
            
            // Decryption
            $decrypted = openssl_decrypt(
                $encrypted, 
                ENCRYPTION_METHOD, 
                ENCRYPTION_KEY, 
                OPENSSL_RAW_DATA, 
                $iv
            );
            
            if ($decrypted === false) {
                throw new Exception('Erreur lors de la decryption du mot de passe');
            }
            
            return $decrypted;
            
        } catch (Exception $e) {
            error_log('Erreur decryption mot de passe: ' . $e->getMessage());
            throw new Exception('Impossible de décrypter le mot de passe');
        }
    }
    
    /**
     * Vérifie si un mot de passe semble être encrypté
     * 
     * @param string $password Le mot de passe à vérifier
     * @return bool True si le mot de passe semble encrypté
     */
    public static function isEncrypted($password) {
        if (empty($password)) {
            return false;
        }
        
        // Un mot de passe encrypté est en base64 et fait au minimum la taille de l'IV
        $decoded = base64_decode($password, true);
        return $decoded !== false && strlen($decoded) >= ENCRYPTION_IV_LENGTH;
    }
    
    /**
     * Génère un mot de passe aléatoire sécurisé
     * 
     * @param int $length Longueur du mot de passe (défaut: 12)
     * @return string Mot de passe généré
     */
    public static function generatePassword($length = 12) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        return $password;
    }
    
    /**
     * Test de l'encryption/decryption
     * 
     * @return bool True si les tests passent
     */
    public static function test() {
        try {
            $testPassword = 'MonMotDePasseTest123!';
            
            // Test encryption
            $encrypted = self::encrypt($testPassword);
            
            // Test decryption
            $decrypted = self::decrypt($encrypted);
            
            // Vérification
            if ($testPassword !== $decrypted) {
                throw new Exception('Test encryption/decryption échoué');
            }
            
            // Test avec mot de passe vide
            $emptyEncrypted = self::encrypt('');
            $emptyDecrypted = self::decrypt($emptyEncrypted);
            
            if ($emptyDecrypted !== '') {
                throw new Exception('Test mot de passe vide échoué');
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log('Test PasswordManager échoué: ' . $e->getMessage());
            return false;
        }
    }
}
?> 