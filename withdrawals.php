<?php
require_once 'config/config.php';
requireAdminLogin();

$pageTitle = 'Gestão de Saques';

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
    $withdrawalId = (int)($_POST['withdrawal_id'] ?? 0);
    
    if ($action === 'update_status' && $withdrawalId) {
        $newStatus = sanitize($_POST['status']);
        
        try {
            $financial->updateWithdrawalStatus($withdrawalId, $newStatus, $_SESSION['admin_id']);
            sendNotification('success', 'Status do saque atualizado com sucesso!');
        } catch (Exception $e) {
            sendNotification('error', 'Erro ao atualizar status: ' . $e->getMessage());
        }
        
        header('Location: withdrawals.php?' . http_build_query($filters));
        exit;
    }
}

// Busca saques
$result = $financial->getWithdrawals($filters, $page);
$withdrawals = $result['data'];
$totalPages = $result['pages'];

include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0">Gestão de Saques</h1>
        <p class="text-muted">Gerencie todos os saques do sistema</p>
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
                       placeholder="Nome ou email">
            </div>
            
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="pendente" <?php echo $filters['status'] === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                    <option value="aprovado" <?php echo $filters['status'] === 'aprovado' ? 'selected' : ''; ?>>Aprovado</option>
                    <option value="rejeitado" <?php echo $filters['status'] === 'rejeitado' ? 'selected' : ''; ?>>Rejeitado</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="tipo" class="form-label">Tipo</label>
                <select class="form-select" id="tipo" name="tipo">
                    <option value="">Todos</option>
                    <option value="pix" <?php echo $filters['tipo'] === 'pix' ? 'selected' : ''; ?>>PIX</option>
                    <option value="usdt" <?php echo $filters['tipo'] === 'usdt' ? 'selected' : ''; ?>>USDT</option>
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
                <a href="withdrawals.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Limpar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Saques -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-arrow-up me-2"></i>
            Saques (<?php echo number_format($result['total']); ?>)
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($withdrawals)): ?>
            <div class="text-center py-5">
                <i class="fas fa-arrow-up fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Nenhum saque encontrado</h5>
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
                            <th>Destino</th>
                            <th>Status</th>
                            <th>Data</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($withdrawals as $withdrawal): ?>
                            <tr>
                                <td><?php echo $withdrawal['id']; ?></td>
                                <td>
                                    <div>
                                        <div class="fw-bold"><?php echo escape($withdrawal['user_name']); ?></div>
                                        <small class="text-muted"><?php echo escape($withdrawal['user_email']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-bold text-danger">
                                        <?php echo formatMoney($withdrawal['valor'], 'USD'); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo strtoupper($withdrawal['tipo']); ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo escape(substr($withdrawal['endereco_carteira'] ?? 'N/A', 0, 20)) . '...'; ?>
                                    </small>
                                </td>
                                <td>
                                    <?php
                                    $statusText = '';
                                    $badgeClass = match($withdrawal['status']) {
                                        'pago' => 'bg-success',
                                        'completed' => 'bg-success',
                                        'pendente' => 'bg-warning',
                                        'erro' => 'bg-danger',
                                        default => 'bg-secondary'
                                    };
                                    
                                    if ($withdrawal['status'] === 'pago' && $withdrawal['tipo'] === 'usdt') {
                                        $statusText = 'Pago';
                                    } elseif ($withdrawal['status'] === 'completed' && $withdrawal['tipo'] === 'pix') {
                                        $statusText = 'Completed';
                                    } elseif ($withdrawal['status'] === 'erro') {
                                        $statusText = 'Rejeitado';
                                    } else {
                                        $statusText = ucfirst($withdrawal['status']);
                                    }
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                                <td>
                                    <small><?php echo formatDate($withdrawal['created_at']); ?></small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="showWithdrawalModal(<?php echo htmlspecialchars(json_encode($withdrawal)); ?>)"
                                                data-bs-toggle="tooltip" title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <?php if ($withdrawal['status'] === 'pendente'): ?>
                                            <button class="btn btn-sm btn-outline-success" 
                                                    onclick="updateStatus(<?php echo $withdrawal['id']; ?>, 'aprovado')"
                                                    data-bs-toggle="tooltip" title="Aprovar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="updateStatus(<?php echo $withdrawal['id']; ?>, 'rejeitado')"
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
                    $baseUrl = 'withdrawals.php?' . http_build_query(array_filter($filters));
                    echo generatePagination($page, $totalPages, $baseUrl);
                    ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Detalhes -->
<div class="modal fade" id="withdrawalModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-arrow-up me-2"></i>
                    Detalhes do Saque
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="withdrawalDetails">
                <!-- Conteúdo será preenchido via JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- Form oculto para ações -->
<form id="actionForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="update_status">
    <input type="hidden" name="withdrawal_id" id="action_withdrawal_id">
    <input type="hidden" name="status" id="action_status">
</form>

<script>
function showWithdrawalModal(withdrawal) {
    const modal = new bootstrap.Modal(document.getElementById('withdrawalModal'));
    const details = document.getElementById('withdrawalDetails');
    
    const statusBadge = getStatusBadge(withdrawal.status, withdrawal.tipo);
    const typeBadge = `<span class="badge bg-info">${withdrawal.tipo.toUpperCase()}</span>`;
    
    let destinationInfo = '';
    if (withdrawal.tipo === 'pix') {
        destinationInfo = `
            <p><strong>Endereço/Chave:</strong> ${withdrawal.endereco_carteira || 'N/A'}</p>
            <p><strong>Rede:</strong> ${withdrawal.rede || 'N/A'}</p>
        `;
    } else {
        destinationInfo = `
            <p><strong>Endereço:</strong> <code>${withdrawal.endereco_carteira || 'N/A'}</code></p>
            <p><strong>Rede:</strong> ${withdrawal.rede || 'N/A'}</p>
        `;
    }
    
    details.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>Informações do Usuário</h6>
                <p><strong>Nome:</strong> ${withdrawal.user_name}</p>
                <p><strong>Email:</strong> ${withdrawal.user_email}</p>
            </div>
            <div class="col-md-6">
                <h6>Informações do Saque</h6>
                <p><strong>ID:</strong> #${withdrawal.id}</p>
                <p><strong>Valor:</strong> $${parseFloat(withdrawal.valor).toFixed(2)}</p>
                <p><strong>Taxa:</strong> $${parseFloat(withdrawal.taxa).toFixed(2)}</p>
                <p><strong>Tipo:</strong> ${typeBadge}</p>
                <p><strong>Status:</strong> ${statusBadge}</p>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-12">
                <h6>Dados de Destino</h6>
                ${destinationInfo}
                <p><strong>Data:</strong> ${new Date(withdrawal.created_at).toLocaleString('pt-BR')}</p>
            </div>
        </div>
    `;
    
    modal.show();
}

function getStatusBadge(status, tipo) {
    const badges = {
        'pago': '<span class="badge bg-success">Pago</span>',
        'completed': '<span class="badge bg-success">Completed</span>',
        'pendente': '<span class="badge bg-warning">Pendente</span>',
        'erro': '<span class="badge bg-danger">Rejeitado</span>'
    };
    
    // Verificar se é um status específico baseado no tipo
    if (status === 'pago' && tipo === 'usdt') {
        return '<span class="badge bg-success">Pago</span>';
    } else if (status === 'completed' && tipo === 'pix') {
        return '<span class="badge bg-success">Completed</span>';
    }
    
    return badges[status] || '<span class="badge bg-secondary">Desconhecido</span>';
}

function updateStatus(withdrawalId, status) {
    const messages = {
        'aprovado': 'Tem certeza que deseja aprovar este saque?',
        'rejeitado': 'Tem certeza que deseja rejeitar este saque? O valor será reembolsado na carteira do usuário.'
    };
    
    Swal.fire({
        title: 'Confirmar ação',
        text: messages[status],
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: status === 'aprovado' ? '#28a745' : '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, confirmar!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('action_withdrawal_id').value = withdrawalId;
            document.getElementById('action_status').value = status;
            document.getElementById('actionForm').submit();
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>