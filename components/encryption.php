<?php
/**
 * Encryption and Decryption Utility
 * Uses AES-256-CBC for secure data handling
 */

// In a real production environment, these should be stored in an environment variable (.env)
define('ENCRYPTION_KEY', 'GraceStreetSecureKey2026!#@$'); 
define('ENCRYPTION_METHOD', 'aes-256-cbc');

/**
 * Encrypts a string
 */
function encrypt_data($data) {
    if (empty($data)) return $data;
    
    $iv_length = openssl_cipher_iv_length(ENCRYPTION_METHOD);
    $iv = openssl_random_pseudo_bytes($iv_length);
    
    $encrypted = openssl_encrypt($data, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
    
    // Return IV + Encrypted data as a base64 string
    return base64_encode($iv . $encrypted);
}

/**
 * Decrypts a string
 */
function decrypt_data($data) {
    if (empty($data)) return $data;
    
    $decoded = base64_decode($data);
    $iv_length = openssl_cipher_iv_length(ENCRYPTION_METHOD);
    
    $iv = substr($decoded, 0, $iv_length);
    $encrypted = substr($decoded, $iv_length);
    
    $decrypted = openssl_decrypt($encrypted, ENCRYPTION_METHOD, ENCRYPTION_KEY, 0, $iv);
    
    // If decryption fails, it might be unencrypted data (legacy)
    return ($decrypted === false) ? $data : $decrypted;
}
?>
