<?php
// Configurações globais da aplicação
define('APP_NAME',      'MedSolutions');
define('APP_VERSION',   '1.0.0');
define('APP_COPYRIGHT', '© 2026 MedSolutions');

// URL base do projeto
define('BASE_URL', '/sibdas/1220673/MedSolutions');

// Configurações da base de dados
define('MYSQL_HOST',     'vsgate-s1.dei.isep.ipp.pt');
define('MYSQL_PORT',     10464);
define('MYSQL_DATABASE', 'db1220673');
define('MYSQL_USERNAME', '1220673');
define('MYSQL_PASSWORD', 'pires_673');
define('MYSQL_AES_KEY',  'M3dSol_MySQL_AES_2026!');

// Segurança - Encriptação com OpenSSL
define('OPENSSL_METHOD', 'AES-256-CBC');
define('OPENSSL_KEY',    'M3dSol_K3y32CharsSecr3tK3y!');
define('OPENSSL_IV',     'M3dSol_IV16CharK!');
