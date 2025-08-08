<?php
/**
 * Controlador de Autenticação
 */

class AuthController {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Processa login
     */
    public function login($email, $password) {
        // Verifica se está bloqueado
        if (isBlocked($email)) {
            throw new Exception("Muitas tentativas de login. Tente novamente em 15 minutos.");
        }
        
        // Busca admin
        $admin = $this->db->fetch(
            "SELECT * FROM admin_users WHERE email = ?",
            [$email]
        );
        
        if (!$admin || !verifyPassword($password, $admin['password'])) {
            logLoginAttempt($email, false);
            throw new Exception("Email ou senha inválidos");
        }
        
        // Login bem-sucedido
        logLoginAttempt($email, true);
        
        // Inicia sessão
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['last_activity'] = time();
        
        // Registra log
        logAction('LOGIN', 'Login realizado com sucesso', $admin['id']);
        
        return true;
    }
    
    /**
     * Logout
     */
    public function logout() {
        if (isset($_SESSION['admin_id'])) {
            logAction('LOGOUT', 'Logout realizado', $_SESSION['admin_id']);
        }
        
        session_destroy();
        return true;
    }
    
    /**
     * Cria admin
     */
    public function createAdmin($name, $email, $password) {
        // Verifica se email já existe
        $existing = $this->db->fetch(
            "SELECT id FROM admin_users WHERE email = ?",
            [$email]
        );
        
        if ($existing) {
            throw new Exception("Email já cadastrado");
        }
        
        // Cria admin
        $hashedPassword = hashPassword($password);
        
        return $this->db->query(
            "INSERT INTO admin_users (name, email, password, created_at) VALUES (?, ?, ?, NOW())",
            [$name, $email, $hashedPassword]
        );
    }
    
    /**
     * Altera senha
     */
    public function changePassword($adminId, $currentPassword, $newPassword) {
        // Busca admin
        $admin = $this->db->fetch(
            "SELECT password FROM admin_users WHERE id = ?",
            [$adminId]
        );
        
        if (!$admin || !verifyPassword($currentPassword, $admin['password'])) {
            throw new Exception("Senha atual incorreta");
        }
        
        // Atualiza senha
        $hashedPassword = hashPassword($newPassword);
        
        $result = $this->db->query(
            "UPDATE admin_users SET password = ?, updated_at = NOW() WHERE id = ?",
            [$hashedPassword, $adminId]
        );
        
        if ($result) {
            logAction('PASSWORD_CHANGED', 'Senha alterada', $adminId);
        }
        
        return $result;
    }
}