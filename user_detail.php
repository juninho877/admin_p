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

$pageTitle = 'Detalhes do Usuário - ' . $user['name'];

// Buscar dados adicionais
$transactions = $userModel->getUserTransactions($userId, 1, 10);
$investments = $userModel->getUserInvestments($userId);

// Buscar depósitos e saques
$db = new Database();
$deposits = $db->fetchAll(
    "SELECT * FROM depositos WHERE user_id = ? ORDER BY created_at DESC LIMIT 10",
    [$userId]
);

$withdrawals = $db->fetchAll(
    "SELECT * FROM saques WHERE user_id = ? ORDER BY created_at DESC LIMIT 10",
    [$userId]
);

include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="users.php">Usuários</a></li>
                <li class="breadcrumb-item active">Detalhes</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Detalhes do Usuário</h1>
    </div>
</div>

<!-- Informações Básicas -->
<div class="row mb-4">
    <div class="col-xl-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user me-2"></i>
                    Informações Pessoais
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="avatar-lg bg-primary rounded-circle d-inline-flex align-items-center justify-content-center">
                        <i class="fas fa-user fa-2x text-white"></i>
                    </div>
                    <h4 class="mt-2 mb-0"><?php echo escape($user['name']); ?></h4>
                    <p class="text-muted"><?php echo escape($user['email']); ?></p>
                    <?php
                    $statusBadge = $user['status'] === 'ativo' ? 'bg-success' : 'bg-danger';
                    ?>
                    <span class="badge <?php echo $statusBadge; ?>">
                        <?php echo ucfirst($user['status']); ?>
                    </span>
                </div>
                
                <hr>
                
                <div class="row text-center">
                    <div class="col-6">
                        <h5 class="text-success mb-0"><?php echo formatMoney($user['saldo_carteira'] ?? 0, 'USD'); ?></h5>
                        <small class="text-muted">Saldo Principal</small>
                    </div>
                    <div class="col-6">
                        <h5 class="text-info mb-0"><?php echo formatMoney($user['saldo_comissao'] ?? 0, 'USD'); ?></h5>
                        <small class="text-muted">Saldo Comissão</small>
                    </div>
                </div>
                
                <hr>
                
                <div class="mb-2">
                    <strong>ID:</strong> #<?php echo $user['id']; ?>
                </div>
                <div class="mb-2">
                    <strong>CPF:</strong> <?php echo $user['cpf'] ? escape($user['cpf']) : 'Não informado'; ?>
                </div>
                <div class="mb-2">
                    <strong>Telefone:</strong> <?php echo $user['telefone'] ? escape($user['telefone']) : 'Não informado'; ?>
                </div>
                <div class="mb-2">
                    <strong>Código de Indicação:</strong> 
                    <code><?php echo escape($user['codigo_indicacao']); ?></code>
                </div>
                <div class="mb-2">
                    <strong>Indicado por:</strong> 
                    <?php echo $user['codigo_indicador'] ? escape($user['codigo_indicador']) : 'Nenhum'; ?>
                </div>
                <div class="mb-2">
                    <strong>Cadastro:</strong> <?php echo formatDate($user['created_at']); ?>
                </div>
                
                <hr>
                
                <div class="d-grid gap-2">
                    <a href="user_edit.php?id=<?php echo $user['id']; ?>" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>Editar Usuário
                    </a>
                    <button class="btn btn-info" 
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
    
    <div class="col-xl-8">
        <!-- Estatísticas -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div class="stats-icon mx-auto" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <h5 class="mb-1">
                        <?php 
                        $totalDeposits = array_sum(array_column(array_filter($deposits, fn($d) => $d['status'] === 'confirmado'), 'valor_usd'));
                        echo formatMoney($totalDeposits, 'USD'); 
                        ?>
                    </h5>
                    <p class="text-muted mb-0">Total Depositado</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div class="stats-icon mx-auto" style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <h5 class="mb-1">
                        <?php 
                        $totalWithdrawals = array_sum(array_column(array_filter($withdrawals, fn($w) => $w['status'] === 'aprovado'), 'valor_usd'));
                        echo formatMoney($totalWithdrawals, 'USD'); 
                        ?>
                    </h5>
                    <p class="text-muted mb-0">Total Sacado</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div class="stats-icon mx-auto" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h5 class="mb-1">
                        <?php 
                        $totalInvested = array_sum(array_column($investments, 'valor_investido'));
                        echo formatMoney($totalInvested, 'USD'); 
                        ?>
                    </h5>
                    <p class="text-muted mb-0">Total Investido</p>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="stats-card text-center">
                    <div class="stats-icon mx-auto" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h5 class="mb-1"><?php echo count(array_filter($investments, fn($i) => $i['status'] === 'ativo')); ?></h5>
                    <p class="text-muted mb-0">Investimentos Ativos</p>
                </div>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="card">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="userTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="transactions-tab" data-bs-toggle="tab" 
                                data-bs-target="#transactions" type="button" role="tab">
                            <i class="fas fa-exchange-alt me-1"></i>Transações
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="deposits-tab" data-bs-toggle="tab" 
                                data-bs-target="#deposits" type="button" role="tab">
                            <i class="fas fa-arrow-down me-1"></i>Depósitos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="withdrawals-tab" data-bs-toggle="tab" 
                                data-bs-target="#withdrawals" type="button" role="tab">
                            <i class="fas fa-arrow-up me-1"></i>Saques
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="investments-tab" data-bs-toggle="tab" 
                                data-bs-target="#investments" type="button" role="tab">
                            <i class="fas fa-chart-line me-1"></i>Investimentos
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="userTabsContent">
                    <!-- Transações -->
                    <div class="tab-pane fade show active" id="transactions" role="tabpanel">
                        <?php if (empty($transactions['data'])): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-exchange-alt fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Nenhuma transação encontrada</h5>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Tipo</th>
                                            <th>Valor</th>
                                            <th>Descrição</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($transactions['data'] as $transaction): ?>
                                            <tr>
                                                <td><?php echo formatDate($transaction['created_at']); ?></td>
                                                <td>
                                                    <?php
                                                    $badgeClass = $transaction['tipo'] === 'credito' ? 'bg-success' : 'bg-danger';
                                                    $icon = $transaction['tipo'] === 'credito' ? 'fa-plus' : 'fa-minus';
                                                    ?>
                                                    <span class="badge <?php echo $badgeClass; ?>">
                                                        <i class="fas <?php echo $icon; ?> me-1"></i>
                                                        <?php echo ucfirst($transaction['tipo']); ?>
                                                    </span>
                                                </td>
                                                <td class="<?php echo $transaction['tipo'] === 'credito' ? 'text-success' : 'text-danger'; ?>">
                                                    <?php echo formatMoney($transaction['valor'], 'USD'); ?>
                                                </td>
                                                <td><?php echo escape($transaction['descricao'] ?? 'N/A'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Depósitos -->
                    <div class="tab-pane fade" id="deposits" role="tabpanel">
                        <?php if (empty($deposits)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-arrow-down fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Nenhum depósito encontrado</h5>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Valor</th>
                                            <th>Tipo</th>
                                            <th>Status</th>
                                            <th>TXID</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($deposits as $deposit): ?>
                                            <tr>
                                                <td><?php echo formatDate($deposit['created_at']); ?></td>
                                                <td><?php echo formatMoney($deposit['valor_usd'], 'USD'); ?></td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo strtoupper($deposit['tipo']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $badgeClass = match($deposit['status']) {
                                                        'confirmado' => 'bg-success',
                                                        'pendente' => 'bg-warning',
                                                        'falhou' => 'bg-danger',
                                                        default => 'bg-secondary'
                                                    };
                                                    ?>
                                                    <span class="badge <?php echo $badgeClass; ?>">
                                                        <?php echo ucfirst($deposit['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <code class="small"><?php echo escape(substr($deposit['txid'], 0, 20)) . '...'; ?></code>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Saques -->
                    <div class="tab-pane fade" id="withdrawals" role="tabpanel">
                        <?php if (empty($withdrawals)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-arrow-up fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Nenhum saque encontrado</h5>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Data</th>
                                            <th>Valor</th>
                                            <th>Tipo</th>
                                            <th>Status</th>
                                            <th>Destino</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($withdrawals as $withdrawal): ?>
                                            <tr>
                                                <td><?php echo formatDate($withdrawal['created_at']); ?></td>
                                                <td><?php echo formatMoney($withdrawal['valor_usd'], 'USD'); ?></td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo strtoupper($withdrawal['tipo']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $badgeClass = match($withdrawal['status']) {
                                                        'aprovado' => 'bg-success',
                                                        'pendente' => 'bg-warning',
                                                        'rejeitado' => 'bg-danger',
                                                        default => 'bg-secondary'
                                                    };
                                                    ?>
                                                    <span class="badge <?php echo $badgeClass; ?>">
                                                        <?php echo ucfirst($withdrawal['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php 
                                                        if ($withdrawal['tipo'] === 'pix') {
                                                            echo escape($withdrawal['chave_pix'] ?? 'N/A');
                                                        } else {
                                                            echo escape(substr($withdrawal['endereco'] ?? 'N/A', 0, 20)) . '...';
                                                        }
                                                        ?>
                                                    </small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Investimentos -->
                    <div class="tab-pane fade" id="investments" role="tabpanel">
                        <?php if (empty($investments)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Nenhum investimento encontrado</h5>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Produto</th>
                                            <th>Valor Investido</th>
                                            <th>Rendimento</th>
                                            <th>Status</th>
                                            <th>Data Início</th>
                                            <th>Data Fim</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($investments as $investment): ?>
                                            <tr>
                                                <td><?php echo escape($investment['produto_nome']); ?></td>
                                                <td><?php echo formatMoney($investment['valor_investido'], 'USD'); ?></td>
                                                <td class="text-success">
                                                    <?php echo formatMoney($investment['rendimento_liquido'] ?? 0, 'USD'); ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $badgeClass = $investment['status'] === 'ativo' ? 'bg-success' : 'bg-secondary';
                                                    ?>
                                                    <span class="badge <?php echo $badgeClass; ?>">
                                                        <?php echo ucfirst($investment['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo formatDate($investment['data_inicio'], 'd/m/Y'); ?></td>
                                                <td><?php echo formatDate($investment['data_fim'], 'd/m/Y'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
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
    
    const formData = new FormData(this);
    
    fetch('ajax/update_wallet.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message);
            setTimeout(() => location.reload(), 1500);
        } else {
            showError(data.message);
        }
    })
    .catch(error => {
        showError('Erro ao processar solicitação');
    });
});
</script>

<?php include 'includes/footer.php'; ?>