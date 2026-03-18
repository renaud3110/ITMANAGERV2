<?php
/**
 * Chiffrement/déchiffrement des identifiants NAS pour la découverte par agent
 */
function _nas_credential_key() {
    if (defined('DISCOVERY_CREDENTIALS_KEY')) return DISCOVERY_CREDENTIALS_KEY;
    if (defined('API_INVENTORY_KEY')) return API_INVENTORY_KEY;
    $cfg = __DIR__ . '/api_config.php';
    if (file_exists($cfg)) {
        require_once $cfg;
        return defined('API_INVENTORY_KEY') ? API_INVENTORY_KEY : '';
    }
    return '';
}

function nas_credential_encrypt($plaintext) {
    $key = _nas_credential_key();
    if (empty($key)) return '';
    $key16 = substr(hash('sha256', $key), 0, 16);
    $iv = substr(hash('sha256', $key . 'nas-iv'), 0, 16);
    $enc = openssl_encrypt($plaintext, 'AES-128-CBC', $key16, OPENSSL_RAW_DATA, $iv);
    return $enc !== false ? base64_encode($enc) : '';
}

function nas_credential_decrypt($ciphertext) {
    if (empty($ciphertext)) return '';
    $key = _nas_credential_key();
    if (empty($key)) return '';
    $key16 = substr(hash('sha256', $key), 0, 16);
    $iv = substr(hash('sha256', $key . 'nas-iv'), 0, 16);
    $dec = openssl_decrypt(base64_decode($ciphertext), 'AES-128-CBC', $key16, OPENSSL_RAW_DATA, $iv);
    return $dec !== false ? $dec : '';
}

/** Chiffrement identifiants ESXi (pour découverte par agent) */
function esxi_credential_encrypt($plaintext) {
    $key = _nas_credential_key();
    if (empty($key)) return '';
    $key16 = substr(hash('sha256', $key), 0, 16);
    $iv = substr(hash('sha256', $key . 'esxi-iv'), 0, 16);
    $enc = openssl_encrypt($plaintext, 'AES-128-CBC', $key16, OPENSSL_RAW_DATA, $iv);
    return $enc !== false ? base64_encode($enc) : '';
}

function esxi_credential_decrypt($ciphertext) {
    if (empty($ciphertext)) return '';
    $key = _nas_credential_key();
    if (empty($key)) return '';
    $key16 = substr(hash('sha256', $key), 0, 16);
    $iv = substr(hash('sha256', $key . 'esxi-iv'), 0, 16);
    $dec = openssl_decrypt(base64_decode($ciphertext), 'AES-128-CBC', $key16, OPENSSL_RAW_DATA, $iv);
    return $dec !== false ? $dec : '';
}
