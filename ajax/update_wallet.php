<?php
require_once '../config/config.php';
requireAdminLogin();

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }
    
    $userId = (int)($_POST['user_id'] ?? 0);
    $operation = sanitize($_POST['operation'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');
    
    if (!$userId || !$operation || $amount <= 0) {
        throw new Exception('Dados inválidos');
    }
    
    if (!in_array($operation, ['add', 'subtract'])) {
        throw new Exception('Operação inválida');
    }
    
    $userModel = new User();
    
    // Verifica se usuário existe
    $user = $userModel->getUserById($userId);
    if (!$user) {
        throw new Exception('Usuário não encontrado');
    }
    
    // Verifica se há saldo suficiente para subtração
    if ($operation === 'subtract') {
        $currentBalance = $user['saldo_carteira'] ?? 0;
        if ($currentBalance < $amount) {
            throw new Exception('Saldo insuficiente para esta operação');
        }
    }
    
    // Atualiza saldo
    $userModel->updateWalletBalance($userId, $amount, $operation);
    
    // Registra log
    $actionType = $operation === 'add' ? 'WALLET_CREDIT' : 'WALLET_DEBIT';
    $logDetails = "Usuário: {$user['name']} (ID: {$userId}) - Valor: {$amount} USD - Descrição: {$description}";
    
    logAction($actionType, $logDetails, $_SESSION['admin_id']);
    
    echo json_encode([
        'success' => true,
        'message' => 'Saldo atualizado com sucesso!'
    ]);
    
} catch (Exception $e) {
    error_log("Wallet update error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}