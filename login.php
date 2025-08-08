<?php
require_once 'config/config.php';

// Se já está logado, redireciona
if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Preencha todos os campos';
    } elseif (!validateCSRFToken($csrf_token)) {
        $error = 'Token de segurança inválido';
    } else {
        try {
            $authController = new AuthController();
            $authController->login($email, $password);
            
            header('Location: dashboard.php');
            exit;
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Verifica se houve timeout
if (isset($_GET['timeout'])) {
    $error = 'Sua sessão expirou. Faça login novamente.';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SYSTEM_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* ==========================================================================
           RESPONSIVE LOGIN PAGE - MOBILE FIRST APPROACH
           ========================================================================== */
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, 'Roboto', sans-serif;
        }
        
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 1.5rem;
            text-align: center;
            position: relative;
        }
        
        .login-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0.1) 100%);
        }
        
        .login-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }
        
        .login-header h3 {
            margin: 0 0 0.5rem 0;
            font-weight: 600;
            font-size: 1.5rem;
        }
        
        .login-header p {
            margin: 0;
            opacity: 0.8;
            font-size: 0.9rem;
        }
        
        .login-body {
            padding: 2rem 1.5rem;
        }
        
        .form-control {
            border-radius: 12px;
            border: 2px solid #e9ecef;
            padding: 0.875rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            min-height: 48px;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            transform: translateY(-1px);
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .form-label i {
            width: 20px;
            text-align: center;
            opacity: 0.7;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 0.875rem 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            min-height: 48px;
            font-size: 0.9rem;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
        }
        
        /* ==========================================================================
           RESPONSIVE BREAKPOINTS
           ========================================================================== */
        
        /* Small mobile devices */
        @media (max-width: 480px) {
            body {
                padding: 0.5rem;
            }
            
            .login-card {
                max-width: 100%;
                margin: 0;
            }
            
            .login-header {
                padding: 1.5rem 1rem;
            }
            
            .login-header i {
                font-size: 2.5rem;
            }
            
            .login-header h3 {
                font-size: 1.25rem;
            }
            
            .login-body {
                padding: 1.5rem 1rem;
            }
            
            .form-control {
                padding: 0.75rem;
                font-size: 16px; /* Prevents zoom on iOS */
            }
            
            .btn-login {
                padding: 0.75rem;
                font-size: 0.85rem;
            }
        }
        
        /* Medium mobile devices */
        @media (min-width: 481px) and (max-width: 767px) {
            .login-card {
                max-width: 380px;
            }
        }
        
        /* Tablet devices */
        @media (min-width: 768px) {
            body {
                padding: 2rem;
            }
            
            .login-card {
                max-width: 450px;
            }
            
            .login-header {
                padding: 2.5rem 2rem;
            }
            
            .login-body {
                padding: 2.5rem 2rem;
            }
        }
        
        /* Desktop devices */
        @media (min-width: 1024px) {
            .login-card {
                max-width: 480px;
            }
            
            .login-header i {
                font-size: 3.5rem;
            }
            
            .login-header h3 {
                font-size: 1.75rem;
            }
        }
        
        /* ==========================================================================
           ACCESSIBILITY & INTERACTION IMPROVEMENTS
           ========================================================================== */
        
        @media (prefers-reduced-motion: reduce) {
            .login-card {
                animation: none;
            }
            
            .form-control:focus,
            .btn-login:hover {
                transform: none;
            }
        }
        
        /* High contrast mode */
        @media (prefers-contrast: high) {
            .form-control {
                border-width: 3px;
            }
            
            .btn-login {
                border: 2px solid #000;
            }
        }
        
        /* Focus styles for keyboard navigation */
        .form-control:focus,
        .btn-login:focus {
            outline: 2px solid #667eea;
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <i class="fas fa-shield-alt fa-3x mb-3"></i>
            <h3><?php echo SYSTEM_NAME; ?></h3>
            <p class="mb-0">Painel Administrativo</p>
        </div>
        
        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope me-2"></i>Email
                    </label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-2"></i>Senha
                    </label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-login w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Entrar
                </button>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>