<?php
require_once 'config/config.php';
requireAdminLogin();

$pageTitle = 'Gestão de Usuários';

$userModel = new User();

// Filtros
$filters = [
    'search' => sanitize($_GET['search'] ?? ''),
    'status' => sanitize($_GET['status'] ?? ''),
    'date_from' => sanitize($_GET['date_from'] ?? ''),
    'date_to' => sanitize($_GET['date_to'] ?? '')
];

// Validação dos filtros de data
if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
    if (empty($filters['date_from']) || empty($filters['date_to'])) {
        sendNotification('error', 'Para usar o filtro de data, é obrigatório selecionar tanto a data inicial quanto a data final.');
        // Limpar os filtros de data para evitar erro no banco
        $filters['date_from'] = '';
        $filters['date_to'] = '';
    }
}

$page = (int)($_GET['page'] ?? 1);

// Busca usuários
$result = $userModel->getUsers($filters, $page);
$users = $result['data'];
$totalPages = $result['pages'];

include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0">Gestão de Usuários</h1>
        <p class="text-muted">Gerencie todos os usuários do sistema</p>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-filter me-2"></i>
            Filtros
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?php echo escape($filters['search']); ?>" 
                       placeholder="Nome, email ou CPF">
            </div>
            
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="ativo" <?php echo $filters['status'] === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
                    <option value="bloqueado" <?php echo $filters['status'] === 'bloqueado' ? 'selected' : ''; ?>>Bloqueado</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="date_from" class="form-label">Data Inicial</label>
                <input type="date" class="form-control" id="date_from" name="date_from" 
                       value="<?php echo escape($filters['date_from']); ?>">
            </div>
            
            <div class="col-md-2">
                <label for="date_to" class="form-label">Data Final</label>
                <input type="date" class="form-control" id="date_to" name="date_to" 
                       value="<?php echo escape($filters['date_to']); ?>">
            </div>
            
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-1"></i>Filtrar
                </button>
                <a href="users.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Limpar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Usuários -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-users me-2"></i>
            Usuários (<?php echo number_format($result['total']); ?>)
        </h5>
        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
            <i class="fas fa-download me-1"></i>Exportar
        </button>
    </div>
    <div class="card-body">
        <?php if (empty($users)): ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Nenhum usuário encontrado</h5>
                <p class="text-muted">Tente ajustar os filtros de busca</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th class="d-none d-md-table-cell">CPF</th>
                            <th class="d-none d-sm-table-cell">Status</th>
                            <th>Saldo</th>
                            <th class="d-none d-lg-table-cell">Investimentos</th>
                            <th class="d-none d-lg-table-cell">Cadastro</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?php echo escape($user['name']); ?></div>
                                            <?php if ($user['telefone']): ?>
                                                <small class="text-muted"><?php echo escape($user['telefone']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo escape($user['email']); ?></td>
                                <td class="d-none d-md-table-cell"><?php echo $user['cpf'] ? escape($user['cpf']) : '-'; ?></td>
                                <td class="d-none d-sm-table-cell">
                                    <?php
                                    $badgeClass = $user['status'] === 'ativo' ? 'bg-success' : 'bg-danger';
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">
                                        <?php echo formatMoney($user['saldo_carteira'] ?? 0, 'USD'); ?>
                                    </span>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <span class="badge bg-info">
                                        <?php echo $user['investimentos_ativos']; ?> ativos
                                    </span>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <small><?php echo formatDate($user['created_at']); ?></small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="user_detail.php?id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" 
                                           data-bs-toggle="tooltip" title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="user_edit.php?id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-outline-warning" 
                                           data-bs-toggle="tooltip" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="showWalletModal(<?php echo $user['id']; ?>, '<?php echo escape($user['name']); ?>', <?php echo $user['saldo_carteira'] ?? 0; ?>)"
                                                data-bs-toggle="tooltip" title="Gerenciar Saldo">
                                            <i class="fas fa-wallet"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginação -->
            <?php if ($totalPages > 1): ?>
                <div class="d-flex justify-content-center mt-4">
                    <?php
                    $baseUrl = 'users.php?' . http_build_query(array_filter($filters));
                    echo generatePagination($page, $totalPages, $baseUrl);
                    ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
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

<!-- Modal de Exportação -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-download me-2"></i>
                    Exportar Usuários
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="export/users.php">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Formato</label>
                        <select class="form-select" name="format" required>
                            <option value="excel">Excel (.xlsx)</option>
                            <option value="csv">CSV (.csv)</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Campos a Exportar</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="basic" checked>
                            <label class="form-check-label">Dados Básicos</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="financial">
                            <label class="form-check-label">Dados Financeiros</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="fields[]" value="investments">
                            <label class="form-check-label">Investimentos</label>
                        </div>
                    </div>
                    
                    <!-- Incluir filtros atuais -->
                    <?php foreach ($filters as $key => $value): ?>
                        <?php if (!empty($value)): ?>
                            <input type="hidden" name="<?php echo $key; ?>" value="<?php echo escape($value); ?>">
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-download me-1"></i>Exportar
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