<?php
// Configurações globais da aplicação MedSolutions
define('BASE_URL', '/MedSolutions');
define('APP_NAME', 'MedSolutions');

// Versão e Copyright
define('APP_VERSION', '1.0.0');
define('APP_COPYRIGHT', '© 2026 MedSolutions');

// Base de Dados
define('MYSQL_HOST', 'localhost');
define('MYSQL_DATABASE', 'hospital_inventario');
define('MYSQL_USERNAME', 'root');
define('MYSQL_PASSWORD', '');

// Segurança – Encriptação com OpenSSL (IDs em URLs)
define('OPENSSL_METHOD', 'AES-256-CBC');
define('OPENSSL_KEY', 'M3dSol_K3y32CharsSecr3tK3y!');  // 32 caracteres
define('OPENSSL_IV',  'M3dSol_IV16CharK!');             // 16 caracteres

// Chave AES usada pelo MySQL para encriptar dados sensíveis
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
