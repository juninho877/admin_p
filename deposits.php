<?php
require_once 'config/config.php';
requireAdminLogin();

$pageTitle = 'Gestão de Depósitos';

$financial = new Financial();

// Filtros
$filters = [
    'search' => sanitize($_GET['search'] ?? ''),
    'status' => sanitize($_GET['status'] ?? ''),
    'tipo' => sanitize($_GET['tipo'] ?? ''),
    'date_from' => sanitize($_GET['date_from'] ?? ''),
    'date_to' => sanitize($_GET['date_to'] ?? '')
];

$page = (int)($_GET['page'] ?? 1);

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = sanitize($_POST['action']);
    $depositId = (int)($_POST['deposit_id'] ?? 0);
    
    if ($action === 'update_status' && $depositId) {
        $newStatus = sanitize($_POST['status']);
        
        try {
            $financial->updateDepositStatus($depositId, $newStatus, $_SESSION['admin_id']);
            sendNotification('success', 'Status do depósito atualizado com sucesso!');
        } catch (Exception $e) {
            sendNotification('error', 'Erro ao atualizar status: ' . $e->getMessage());
        }
        
        header('Location: deposits.php?' . http_build_query($filters));
        exit;
    }
}

// Busca depósitos
$result = $financial->getDeposits($filters, $page);
$deposits = $result['data'];
$totalPages = $result['pages'];

include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0">Gestão de Depósitos</h1>
        <p class="text-muted">Gerencie todos os depósitos do sistema</p>
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
                       placeholder="Nome, email ou TXID">
            </div>
            
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="pendente" <?php echo $filters['status'] === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                    <option value="confirmado" <?php echo $filters['status'] === 'confirmado' ? 'selected' : ''; ?>>Confirmado</option>
                    <option value="falhou" <?php echo $filters['status'] === 'falhou' ? 'selected' : ''; ?>>Falhou</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="tipo" class="form-label">Tipo</label>
                <select class="form-select" id="tipo" name="tipo">
                    <option value="">Todos</option>
                    <option value="pix" <?php echo $filters['tipo'] === 'pix' ? 'selected' : ''; ?>>PIX</option>
                    <option value="usdt" <?php echo $filters['tipo'] === 'usdt' ? 'selected' : ''; ?>>USDT</option>
                    <option value="usdc" <?php echo $filters['tipo'] === 'usdc' ? 'selected' : ''; ?>>USDC</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="date_from" class="form-label">Data Inicial</label>
                <input type="date" class="form-control" id="date_from" name="date_from" 
                       value="<?php echo escape($filters['date_from']); ?>">
            </div>
            
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-1"></i>Filtrar
                </button>
                <a href="deposits.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Limpar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Depósitos -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-arrow-down me-2"></i>
            Depósitos (<?php echo number_format($result['total']); ?>)
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($deposits)): ?>
            <div class="text-center py-5">
                <i class="fas fa-arrow-down fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Nenhum depósito encontrado</h5>
                <p class="text-muted">Tente ajustar os filtros de busca</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuário</th>
                            <th>Valor</th>
                            <th>Tipo</th>
                            <th>TXID</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deposits as $deposit): ?>
                            <tr>
                                <td><?php echo $deposit['id']; ?></td>
                                <td>
                                    <div>
                                        <div class="fw-bold"><?php echo escape($deposit['user_name']); ?></div>
                                        <small class="text-muted"><?php echo escape($deposit['user_email']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">
                                        <?php echo formatMoney($deposit['valor_usd'], 'USD'); ?>
                                    </span>
                                    <?php if ($deposit['valor_brl']): ?>
                                        <br><small class="text-muted"><?php echo formatMoney($deposit['valor_brl'], 'BRL'); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo strtoupper($deposit['tipo']); ?>
                                    </span>
                                </td>
                                <td>
                                    <code class="small"><?php echo escape(substr($deposit['txid'], 0, 20)) . '...'; ?></code>
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
                                    <small><?php echo formatDate($deposit['created_at']); ?></small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="showDepositModal(<?php echo htmlspecialchars(json_encode($deposit)); ?>)"
                                                data-bs-toggle="tooltip" title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($deposit['status'] === 'pendente'): ?>
                                            <button class="btn btn-sm btn-outline-success" 
                                                    onclick="updateStatus(<?php echo $deposit['id']; ?>, 'confirmado')"
                                                    data-bs-toggle="tooltip" title="Aprovar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="updateStatus(<?php echo $deposit['id']; ?>, 'falhou')"
                                                    data-bs-toggle="tooltip" title="Rejeitar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        <?php endif; ?>
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
                    $baseUrl = 'deposits.php?' . http_build_query(array_filter($filters));
                    echo generatePagination($page, $totalPages, $baseUrl);
                    ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Detalhes -->
<div class="modal fade" id="depositModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-arrow-down me-2"></i>
                    Detalhes do Depósito
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="depositDetails">
                <!-- Conteúdo será preenchido via JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- Form oculto para ações -->
<form id="actionForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="update_status">
    <input type="hidden" name="deposit_id" id="action_deposit_id">
    <input type="hidden" name="status" id="action_status">
</form>

<script>
function showDepositModal(deposit) {
    const modal = new bootstrap.Modal(document.getElementById('depositModal'));
    const details = document.getElementById('depositDetails');
    
    const statusBadge = getStatusBadge(deposit.status);
    const typeBadge = `<span class="badge bg-info">${deposit.tipo.toUpperCase()}</span>`;
    
    details.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>Informações do Usuário</h6>
                <p><strong>Nome:</strong> ${deposit.user_name}</p>
                <p><strong>Email:</strong> ${deposit.user_email}</p>
            </div>
            <div class="col-md-6">
                <h6>Informações do Depósito</h6>
                <p><strong>ID:</strong> #${deposit.id}</p>
                <p><strong>Valor USD:</strong> $${parseFloat(deposit.valor_usd).toFixed(2)}</p>
                ${deposit.valor_brl ? `<p><strong>Valor BRL:</strong> R$ ${parseFloat(deposit.valor_brl).toFixed(2)}</p>` : ''}
                <p><strong>Tipo:</strong> ${typeBadge}</p>
                <p><strong>Status:</strong> ${statusBadge}</p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Detalhes da Transação</h6>
                <p><strong>TXID:</strong> <code>${deposit.txid}</code></p>
                <p><strong>Data:</strong> ${new Date(deposit.created_at).toLocaleString('pt-BR')}</p>
                ${deposit.cpf_informado ? `<p><strong>CPF:</strong> ${deposit.cpf_informado}</p>` : ''}
            </div>
        </div>
    `;
    
    modal.show();
}

function getStatusBadge(status) {
    const badges = {
        'confirmado': '<span class="badge bg-success">Confirmado</span>',
        'pendente': '<span class="badge bg-warning">Pendente</span>',
        'falhou': '<span class="badge bg-danger">Falhou</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Desconhecido</span>';
}

function updateStatus(depositId, status) {
    const messages = {
        'confirmado': 'Tem certeza que deseja aprovar este depósito?',
        'falhou': 'Tem certeza que deseja rejeitar este depósito?'
    };
    
    Swal.fire({
        title: 'Confirmar ação',
        text: messages[status],
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: status === 'confirmado' ? '#28a745' : '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, confirmar!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('action_deposit_id').value = depositId;
            document.getElementById('action_status').value = status;
            document.getElementById('actionForm').submit();
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>