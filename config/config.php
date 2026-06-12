<?php
define('BASE_URL', '/sibdas/1220673/MedSolutions');
define('APP_NAME', 'MedSolutions');
define('APP_VERSION', '1.0.0');
define('APP_COPYRIGHT', '© 2026 MedSolutions');
define('MYSQL_HOST', 'vsgate-s1.dei.isep.ipp.pt');
define('MYSQL_PORT', 10464);
define('MYSQL_DATABASE', 'db1220673');
define('MYSQL_USERNAME', '1220673');
define('MYSQL_PASSWORD', 'pires_673');
define('OPENSSL_METHOD', 'AES-256-CBC');
define('OPENSSL_KEY', 'M3dSol_K3y32CharsSecr3tK3y!');  // 32 caracteres
define('OPENSSL_IV',  'M3dSol_IV16CharK!');             // 16 caracteres
define('MYSQL_AES_KEY', 'M3dSol_MySQL_AES_2026!');

/**
 * Função CSRF Token (placeholder)
 */
function csrf_token() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Verificar CSRF Token
 */
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
