<?php
/**
 * Configurações Gerais do Sistema
 */

// Configurações de Sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Mude para 1 em HTTPS
session_start();

// Configurações de Erro
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('America/Sao_Paulo');

// Constantes do Sistema
define('BASE_URL', 'http://localhost/admin');
define('ADMIN_EMAIL', 'admin@sistema.com');
define('SYSTEM_NAME', 'Painel Administrativo');
define('VERSION', '1.0.0');

// Configurações de Paginação
define('ITEMS_PER_PAGE', 20);

// Configurações de Upload
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Configurações de Segurança
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_TIMEOUT', 3600); // 1 hora

// Incluir Database class primeiro
require_once 'database.php';

// Autoload das classes
spl_autoload_register(function ($class) {
    $basePath = dirname(__FILE__) . '/..';
    $paths = [
        $basePath . '/config/',
        $basePath . '/classes/',
        $basePath . '/models/',
        $basePath . '/controllers/',
        $basePath . '/helpers/'
    ];
    
    foreach ($paths as $path) {
        $file = $path . '/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            break;
        }
    }
});

// Incluir helpers
$basePath = dirname(__FILE__) . '/..';
require_once $basePath . '/helpers/functions.php';
require_once $basePath . '/helpers/security.php';

// Inicializar conexão global do banco de dados
$db = new Database();