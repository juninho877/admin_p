<?php
require_once 'config/config.php';
requireAdminLogin();

$userId = (int)($_GET['id'] ?? 0);
if (!$userId) {
    header('Location: users.php');
    exit;
}

$userModel = new User();
$user = $userModel->getUserById($userId);

if (!$user) {
    sendNotification('error', 'Usuário não encontrado');
    header('Location: users.php');
    exit;
}

$pageTitle = 'Editar Usuário - ' . $user['name'];

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar se é alteração de senha
    if (isset($_POST['action_password_change'])) {
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_new_password'] ?? '';
        
        // Validações da senha
        $errors = [];
        
        if (empty($newPassword)) {
            $errors[] = 'Nova senha é obrigatória';
        }
        
        if (empty($confirmPassword)) {
            $errors[] = 'Confirmação de senha é obrigatória';
        }
        
        if (strlen($newPassword) < 6) {
            $errors[] = 'A senha deve ter pelo menos 6 caracteres';
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'As senhas não coincidem';
        }
        
        if (empty($errors)) {
            try {
                $userModel->updateUserPassword($userId, $newPassword);
                logAction('USER_PASSWORD_UPDATED', "Senha atualizada para usuário ID: {$userId}", $_SESSION['admin_id']);
                sendNotification('success', 'Senha atualizada com sucesso!');
                header('Location: user_edit.php?id=' . $userId);
                exit;
            } catch (Exception $e) {
                $errors[] = 'Erro ao atualizar senha: ' . $e->getMessage();
            }
        }
    } else {
        // Processar atualização de dados do usuário
        $data = [
            'name' => sanitize($_POST['name']),
            'email' => sanitize($_POST['email']),
            'telefone' => sanitize($_POST['telefone']),
            'cpf' => sanitize($_POST['cpf']),
            'status' => sanitize($_POST['status'])
        ];
        
        // Validações
        $errors = [];
        
        if (empty($data['name'])) {
            $errors[] = 'Nome é obrigatório';
        }
        
        // Email é opcional, mas se fornecido deve ser válido
        if (!empty($data['email']) && !validateEmail($data['email'])) {
            $errors[] = 'Email deve ter um formato válido';
        }
        
        if (!empty($data['cpf']) && !validateCPF($data['cpf'])) {
            $errors[] = 'CPF inválido';
        }
        
        // Verificar se email já existe (apenas se email foi fornecido)
        if (!empty($data['email'])) {
            $db = new Database();
            $existingUser = $db->fetch(
                "SELECT id FROM users WHERE email = ? AND id != ?",
                [$data['email'], $userId]
            );
            
            if ($existingUser) {
                $errors[] = 'Este email já está sendo usado por outro usuário';
            }
        }
        
        if (empty($errors)) {
            try {
                $userModel->updateUser($userId, $data);
                logAction('USER_UPDATED', "Usuário atualizado: {$data['name']} (ID: {$userId})", $_SESSION['admin_id']);
                sendNotification('success', 'Usuário atualizado com sucesso!');
                header('Location: user_detail.php?id=' . $userId);
                exit;
            } catch (Exception $e) {
                $errors[] = 'Erro ao atualizar usuário: ' . $e->getMessage();
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="users.php">Usuários</a></li>
                <li class="breadcrumb-item"><a href="user_detail.php?id=<?php echo $userId; ?>">Detalhes</a></li>
                <li class="breadcrumb-item active">Editar</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Editar Usuário</h1>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <h6><i class="fas fa-exclamation-triangle me-2"></i>Erros encontrados:</h6>
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-edit me-2"></i>
                    Informações do Usuário
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nome Completo *</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo escape($user['name']); ?>" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo escape($user['email']); ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="telefone" name="telefone" 
                                   value="<?php echo escape($user['telefone'] ?? ''); ?>" 
                                   placeholder="(11) 99999-9999">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="cpf" class="form-label">CPF</label>
                            <input type="text" class="form-control" id="cpf" name="cpf" 
                                   value="<?php echo escape($user['cpf'] ?? ''); ?>" 
                                   placeholder="000.000.000-00">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="ativo" <?php echo $user['status'] === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                                <option value="bloqueado" <?php echo $user['status'] === 'bloqueado' ? 'selected' : ''; ?>>Bloqueado</option>
                            </select>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="user_detail.php?id=<?php echo $userId; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Voltar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Informações Adicionais
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label text-muted">ID do Usuário</label>
                    <p class="mb-0">#<?php echo $user['id']; ?></p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Código de Indicação</label>
                    <p class="mb-0"><code><?php echo escape($user['codigo_indicacao']); ?></code></p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Indicado por</label>
                    <p class="mb-0"><?php echo $user['codigo_indicador'] ? escape($user['codigo_indicador']) : 'Nenhum'; ?></p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Data de Cadastro</label>
                    <p class="mb-0"><?php echo formatDate($user['created_at']); ?></p>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Última Atualização</label>
                    <p class="mb-0"><?php echo $user['updated_at'] ? formatDate($user['updated_at']) : 'Nunca'; ?></p>
                </div>
                
                <hr>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Saldo Principal</label>
                    <h5 class="text-success mb-0"><?php echo formatMoney($user['saldo_carteira'] ?? 0, 'USD'); ?></h5>
                </div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Saldo Comissão</label>
                    <h5 class="text-info mb-0"><?php echo formatMoney($user['saldo_comissao'] ?? 0, 'USD'); ?></h5>
                </div>
                
                <hr>
                
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-info" 
                            onclick="showWalletModal(<?php echo $user['id']; ?>, '<?php echo escape($user['name']); ?>', <?php echo $user['saldo_carteira'] ?? 0; ?>)">
                        <i class="fas fa-wallet me-1"></i>Gerenciar Saldo
                    </button>
                    <a href="affiliates.php?user_id=<?php echo $user['id']; ?>" class="btn btn-outline-primary">
                        <i class="fas fa-network-wired me-1"></i>Ver Rede de Afiliados
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-key me-2"></i>
                    Alterar Senha
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action_password_change" value="1">
                    
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Nova Senha *</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" 
                               minlength="6" required>
                        <small class="text-muted">Mínimo de 6 caracteres</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_new_password" class="form-label">Confirmar Nova Senha *</label>
                        <input type="password" class="form-control" id="confirm_new_password" name="confirm_new_password" 
                               minlength="6" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-key me-1"></i>Alterar Senha
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Gerenciamento de Saldo -->
<div class="modal fade" id="walletModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-wallet me-2"></i>
                    Gerenciar Saldo
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="walletForm" method="POST" action="ajax/update_wallet.php">
                <div class="modal-body">
                    <input type="hidden" id="wallet_user_id" name="user_id">
                    
                    <div class="mb-3">
                        <label class="form-label">Usuário</label>
                        <input type="text" class="form-control" id="wallet_user_name" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Saldo Atual</label>
                        <input type="text" class="form-control" id="wallet_current_balance" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="operation" class="form-label">Operação</label>
                        <select class="form-select" id="operation" name="operation" required>
                            <option value="">Selecione...</option>
                            <option value="add">Adicionar Saldo</option>
                            <option value="subtract">Subtrair Saldo</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="amount" class="form-label">Valor (USD)</label>
                        <input type="number" class="form-control" id="amount" name="amount" 
                               step="0.01" min="0.01" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea class="form-control" id="description" name="description" 
                                  rows="3" placeholder="Motivo da alteração..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Máscaras para campos
document.getElementById('telefone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/(\d{2})(\d)/, '($1) $2');
    value = value.replace(/(\d{5})(\d)/, '$1-$2');
    e.target.value = value;
});

document.getElementById('cpf').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    e.target.value = value;
});

function showWalletModal(userId, userName, currentBalance) {
    document.getElementById('wallet_user_id').value = userId;
    document.getElementById('wallet_user_name').value = userName;
    document.getElementById('wallet_current_balance').value = formatMoney(currentBalance, 'USD');
    
    const modal = new bootstrap.Modal(document.getElementById('walletModal'));
    modal.show();
}

// Handle wallet form submission
document.getElementById('walletForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validação básica
    const operation = document.getElementById('operation').value;
    const amount = parseFloat(document.getElementById('amount').value);
    
    if (!operation) {
        showError('Selecione uma operação');
        return;
    }
    
    if (!amount || amount <= 0) {
        showError('Digite um valor válido');
        return;
    }
    
    const formData = new FormData(this);
    
    fetch('ajax/update_wallet.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message);
            // Fechar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('walletModal'));
            modal.hide();
            setTimeout(() => location.reload(), 1500);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        showError('Erro ao processar solicitação');
    });
});
</script>

<?php include 'includes/footer.php'; ?>