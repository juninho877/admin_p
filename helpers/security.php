<?php
/**
 * Funções de Segurança
 */

/**
 * Gera token CSRF
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida token CSRF
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Verifica se o admin está logado
 */
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_email']);
}

/**
 * Verifica timeout da sessão
 */
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity'])) {
        if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            session_destroy();
            header('Location: login.php?timeout=1');
            exit;
        }
    }
    $_SESSION['last_activity'] = time();
}

/**
 * Força login do admin
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit;
    }
    checkSessionTimeout();
}

/**
 * Hash de senha
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verifica senha
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Previne XSS
 */
function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Valida permissão
 */
function hasPermission($permission) {
    return isset($_SESSION['admin_permissions']) && 
           in_array($permission, $_SESSION['admin_permissions']);
}

/**
 * Registra tentativa de login
 */
function logLoginAttempt($email, $success = false, $ip = null) {
    global $db;
    
    if (!$ip) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    
    $sql = "INSERT INTO login_attempts (email, success, ip_address, created_at) 
            VALUES (?, ?, ?, NOW())";
    
    $db->query($sql, [$email, $success ? 1 : 0, $ip]);
}

/**
 * Verifica bloqueio por tentativas
 */
function isBlocked($email, $minutes = 15, $maxAttempts = 5) {
    global $db;
    
    $sql = "SELECT COUNT(*) as attempts 
            FROM login_attempts 
            WHERE email = ? 
            AND success = 0 
            AND created_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)";
    
    $result = $db->fetch($sql, [$email, $minutes]);
    
    return $result['attempts'] >= $maxAttempts;
}