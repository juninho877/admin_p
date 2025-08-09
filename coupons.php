<?php
require_once 'config/config.php';
requireAdminLogin();

$pageTitle = 'Gestão de Cupons';

$couponModel = new Coupon();

// Filtros
$filters = [
    'search' => sanitize($_GET['search'] ?? ''),
    'ativo' => isset($_GET['ativo']) ? (int)$_GET['ativo'] : null,
    'status' => sanitize($_GET['status'] ?? '')
];

$page = (int)($_GET['page'] ?? 1);

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = sanitize($_POST['action']);
    
    if ($action === 'create') {
        $data = [
            'codigo' => strtoupper(sanitize($_POST['codigo'])),
            'validade' => sanitize($_POST['validade']),
            'valor_minimo' => (float)($_POST['valor_minimo'] ?? 0.10),
            'valor_maximo' => (float)($_POST['valor_maximo'] ?? 2.00),
            'ativo' => (int)($_POST['ativo'] ?? 1)
        ];
        
        try {
            $couponModel->createCoupon($data);
            logAction('COUPON_CREATED', "Cupom criado: {$data['codigo']}", $_SESSION['admin_id']);
            sendNotification('success', 'Cupom criado com sucesso!');
        } catch (Exception $e) {
            sendNotification('error', 'Erro ao criar cupom: ' . $e->getMessage());
        }
        
        header('Location: coupons.php');
        exit;
    }
    
    if ($action === 'update') {
        $couponId = (int)($_POST['coupon_id'] ?? 0);
        $data = [
            'codigo' => strtoupper(sanitize($_POST['codigo'])),
            'validade' => sanitize($_POST['validade']),
            'valor_minimo' => (float)($_POST['valor_minimo'] ?? 0.10),
            'valor_maximo' => (float)($_POST['valor_maximo'] ?? 2.00),
            'ativo' => (int)($_POST['ativo'] ?? 1)
        ];
        
        try {
            $couponModel->updateCoupon($couponId, $data);
            logAction('COUPON_UPDATED', "Cupom atualizado: ID {$couponId}", $_SESSION['admin_id']);
            sendNotification('success', 'Cupom atualizado com sucesso!');
        } catch (Exception $e) {
            sendNotification('error', 'Erro ao atualizar cupom: ' . $e->getMessage());
        }
        
        header('Location: coupons.php');
        exit;
    }
    
    if ($action === 'delete') {
        $couponId = (int)($_POST['coupon_id'] ?? 0);
        
        try {
            $couponModel->deleteCoupon($couponId);
            logAction('COUPON_DELETED', "Cupom excluído: ID {$couponId}", $_SESSION['admin_id']);
            sendNotification('success', 'Cupom excluído com sucesso!');
        } catch (Exception $e) {
            sendNotification('error', 'Erro ao excluir cupom: ' . $e->getMessage());
        }
        
        header('Location: coupons.php');
        exit;
    }
}

// Busca cupons
$result = $couponModel->getCoupons($filters, $page);
$coupons = $result['data'];
$totalPages = $result['pages'];

// Estatísticas
$stats = $couponModel->getCouponStats();

include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0">Gestão de Cupons</h1>
        <p class="text-muted">Gerencie todos os cupons de bônus do sistema</p>
    </div>
</div>

<!-- Estatísticas -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="fas fa-ticket-alt"></i>
            </div>
            <h3 class="mb-1"><?php echo number_format($stats['total_cupons']); ?></h3>
            <p class="text-muted mb-0">Total de Cupons</p>
            <small class="text-success">
                <i class="fas fa-check"></i>
                <?php echo $stats['cupons_ativos']; ?> ativos
            </small>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <i class="fas fa-calendar-check"></i>
            </div>
            <h3 class="mb-1"><?php echo number_format($stats['cupons_validos']); ?></h3>
            <p class="text-muted mb-0">Cupons Válidos</p>
            <small class="text-danger">
                <i class="fas fa-calendar-times"></i>
                <?php echo $stats['cupons_expirados']; ?> expirados
            </small>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                <i class="fas fa-gift"></i>
            </div>
            <h3 class="mb-1"><?php echo number_format($stats['total_resgates']); ?></h3>
            <p class="text-muted mb-0">Total de Resgates</p>
            <small class="text-info">
                <i class="fas fa-users"></i>
                Usuários únicos
            </small>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon" style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <h3 class="mb-1"><?php echo formatMoney($stats['valor_total_resgatado'], 'USD'); ?></h3>
            <p class="text-muted mb-0">Valor Total Resgatado</p>
            <small class="text-primary">
                <i class="fas fa-chart-line"></i>
                Em bônus
            </small>
        </div>
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
                <label for="search" class="form-label">Buscar Código</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?php echo escape($filters['search']); ?>" 
                       placeholder="BEEFUND-123456">
            </div>
            
            <div class="col-md-2">
                <label for="ativo" class="form-label">Status</label>
                <select class="form-select" id="ativo" name="ativo">
                    <option value="">Todos</option>
                    <option value="1" <?php echo $filters['ativo'] === 1 ? 'selected' : ''; ?>>Ativo</option>
                    <option value="0" <?php echo $filters['ativo'] === 0 ? 'selected' : ''; ?>>Inativo</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label for="status" class="form-label">Validade</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="valido" <?php echo $filters['status'] === 'valido' ? 'selected' : ''; ?>>Válido</option>
                    <option value="expirado" <?php echo $filters['status'] === 'expirado' ? 'selected' : ''; ?>>Expirado</option>
                </select>
            </div>
            
            <div class="col-md-5 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-1"></i>Filtrar
                </button>
                <a href="coupons.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-times me-1"></i>Limpar
                </a>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#couponModal">
                    <i class="fas fa-plus"></i>
                    <span class="d-none d-md-inline ms-1">Novo Cupom</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Cupons -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-ticket-alt me-2"></i>
            Cupons (<?php echo number_format($result['total']); ?>)
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($coupons)): ?>
            <div class="text-center py-5">
                <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Nenhum cupom encontrado</h5>
                <p class="text-muted">Clique em "Novo Cupom" para criar o primeiro cupom</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Validade</th>
                            <th>Status</th>
                            <th>Valor Min/Max</th>
                            <th>Resgates</th>
                            <th>Valor Resgatado</th>
                            <th>Criado em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coupons as $coupon): ?>
                            <tr class="responsive-table-row">
                                <td data-label="Código">
                                    <code class="fw-bold"><?php echo escape($coupon['codigo']); ?></code>
                                </td>
                                <td data-label="Validade">
                                    <div>
                                        <small><?php echo formatDate($coupon['validade'], 'd/m/Y H:i'); ?></small>
                                        <br>
                                        <?php
                                        $validadeBadge = $coupon['status_validade'] === 'valido' ? 'bg-success' : 'bg-danger';
                                        $validadeText = $coupon['status_validade'] === 'valido' ? 'Válido' : 'Expirado';
                                        ?>
                                        <span class="badge <?php echo $validadeBadge; ?>">
                                            <?php echo $validadeText; ?>
                                        </span>
                                    </div>
                                </td>
                                <td data-label="Status">
                                    <?php
                                    $statusBadge = $coupon['ativo'] ? 'bg-success' : 'bg-danger';
                                    $statusText = $coupon['ativo'] ? 'Ativo' : 'Inativo';
                                    ?>
                                    <span class="badge <?php echo $statusBadge; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                                <td data-label="Valor Min/Max">
                                    <div>
                                        <small class="text-muted">Min:</small> <?php echo formatMoney($coupon['valor_minimo'], 'USD'); ?>
                                        <br>
                                        <small class="text-muted">Max:</small> <?php echo formatMoney($coupon['valor_maximo'], 'USD'); ?>
                                    </div>
                                </td>
                                <td data-label="Resgates">
                                    <span class="badge bg-info">
                                        <?php echo $coupon['total_resgates']; ?>
                                    </span>
                                </td>
                                <td data-label="Valor Resgatado">
                                    <span class="fw-bold text-success">
                                        <?php echo formatMoney($coupon['valor_total_resgatado'], 'USD'); ?>
                                    </span>
                                </td>
                                <td data-label="Criado em">
                                    <small><?php echo formatDate($coupon['created_at']); ?></small>
                                </td>
                                <td data-label="Ações">
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-outline-primary" 
                                                onclick="showRedemptions(<?php echo $coupon['id']; ?>, '<?php echo escape($coupon['codigo']); ?>')"
                                                data-bs-toggle="tooltip" title="Ver Resgates">
                                            <i class="fas fa-users"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" 
                                                onclick="editCoupon(<?php echo htmlspecialchars(json_encode($coupon)); ?>)"
                                                data-bs-toggle="tooltip" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteCoupon(<?php echo $coupon['id']; ?>, '<?php echo escape($coupon['codigo']); ?>')"
                                                data-bs-toggle="tooltip" title="Excluir">
                                            <i class="fas fa-trash"></i>
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
                    $baseUrl = 'coupons.php?' . http_build_query(array_filter($filters));
                    echo generatePagination($page, $totalPages, $baseUrl);
                    ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Cupom -->
<div class="modal fade" id="couponModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered mx-auto">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="couponModalTitle">
                    <i class="fas fa-ticket-alt me-2"></i>
                    Novo Cupom
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="couponForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="coupon_action" value="create">
                    <input type="hidden" name="coupon_id" id="coupon_id">
                    
                    <div class="mb-3">
                        <label for="codigo" class="form-label">Código do Cupom *</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="codigo" name="codigo" required 
                                   placeholder="BEEFUND-123456" style="text-transform: uppercase;">
                            <button type="button" class="btn btn-outline-secondary" onclick="generateCode()">
                                <i class="fas fa-random"></i>
                            </button>
                        </div>
                        <small class="text-muted">Deixe em branco para gerar automaticamente</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="validade" class="form-label">Data de Validade *</label>
                        <input type="datetime-local" class="form-control" id="validade" name="validade" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="valor_minimo" class="form-label">Valor Mínimo (USD) *</label>
                            <input type="number" class="form-control" id="valor_minimo" name="valor_minimo" 
                                   step="0.01" min="0.01" value="0.10" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="valor_maximo" class="form-label">Valor Máximo (USD) *</label>
                            <input type="number" class="form-control" id="valor_maximo" name="valor_maximo" 
                                   step="0.01" min="0.01" value="2.00" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1" checked>
                            <label class="form-check-label" for="ativo">
                                Cupom Ativo
                            </label>
                        </div>
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

<!-- Modal de Resgates -->
<div class="modal fade" id="redemptionsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-users me-2"></i>
                    Resgates do Cupom: <span id="redemptionCouponCode"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="redemptionsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Carregando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form oculto para exclusão -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="coupon_id" id="delete_coupon_id">
</form>

<script>
function generateCode() {
    const prefix = 'BEEFUND';
    const randomPart = Math.random().toString(36).substring(2, 8).toUpperCase();
    document.getElementById('codigo').value = prefix + '-' + randomPart;
}

function editCoupon(coupon) {
    document.getElementById('couponModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Editar Cupom';
    document.getElementById('coupon_action').value = 'update';
    document.getElementById('coupon_id').value = coupon.id;
    document.getElementById('codigo').value = coupon.codigo;
    
    // Converter data do formato MySQL para datetime-local
    const validadeDate = new Date(coupon.validade);
    const year = validadeDate.getFullYear();
    const month = String(validadeDate.getMonth() + 1).padStart(2, '0');
    const day = String(validadeDate.getDate()).padStart(2, '0');
    const hours = String(validadeDate.getHours()).padStart(2, '0');
    const minutes = String(validadeDate.getMinutes()).padStart(2, '0');
    
    const datetimeLocal = `${year}-${month}-${day}T${hours}:${minutes}`;
    document.getElementById('validade').value = datetimeLocal;
    
    document.getElementById('valor_minimo').value = coupon.valor_minimo;
    document.getElementById('valor_maximo').value = coupon.valor_maximo;
    document.getElementById('ativo').checked = coupon.ativo == 1;
    
    const modal = new bootstrap.Modal(document.getElementById('couponModal'));
    modal.show();
}

function deleteCoupon(couponId, couponCode) {
    Swal.fire({
        title: 'Confirmar exclusão',
        text: `Tem certeza que deseja excluir o cupom "${couponCode}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete_coupon_id').value = couponId;
            document.getElementById('deleteForm').submit();
        }
    });
}

function showRedemptions(couponId, couponCode) {
    document.getElementById('redemptionCouponCode').textContent = couponCode;
    
    const modal = new bootstrap.Modal(document.getElementById('redemptionsModal'));
    modal.show();
    
    // Carregar resgates via AJAX
    fetch(`ajax/get_coupon_redemptions.php?coupon_id=${couponId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayRedemptions(data.redemptions);
            } else {
                document.getElementById('redemptionsContent').innerHTML = 
                    '<div class="alert alert-danger">Erro ao carregar resgates</div>';
            }
        })
        .catch(error => {
            document.getElementById('redemptionsContent').innerHTML = 
                '<div class="alert alert-danger">Erro ao carregar resgates</div>';
        });
}

function displayRedemptions(redemptions) {
    const content = document.getElementById('redemptionsContent');
    
    if (redemptions.length === 0) {
        content.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Nenhum resgate encontrado</h5>
                <p class="text-muted">Este cupom ainda não foi resgatado por nenhum usuário</p>
            </div>
        `;
        return;
    }
    
    let html = `
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th>Email</th>
                        <th>Código Indicação</th>
                        <th>Valor Resgatado</th>
                        <th>Data do Resgate</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    redemptions.forEach(redemption => {
        html += `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <div class="fw-bold">${redemption.user_name}</div>
                        </div>
                    </div>
                </td>
                <td>${redemption.user_email}</td>
                <td><code>${redemption.codigo_indicacao}</code></td>
                <td>
                    <span class="fw-bold text-success">
                        $${parseFloat(redemption.valor_usd).toFixed(2)}
                    </span>
                </td>
                <td>
                    <small>${new Date(redemption.created_at).toLocaleString('pt-BR')}</small>
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    content.innerHTML = html;
}

// Reset modal when closed
document.getElementById('couponModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('couponModalTitle').innerHTML = '<i class="fas fa-ticket-alt me-2"></i>Novo Cupom';
    document.getElementById('coupon_action').value = 'create';
    document.getElementById('couponForm').reset();
    document.getElementById('coupon_id').value = '';
    document.getElementById('ativo').checked = true;
    document.getElementById('valor_minimo').value = '0.10';
    document.getElementById('valor_maximo').value = '2.00';
    
    // Resetar data de validade para 2 horas a partir de agora
    const now = new Date();
    const twoHoursFromNow = new Date(now.getTime() + (2 * 60 * 60 * 1000));
    twoHoursFromNow.setMinutes(twoHoursFromNow.getMinutes() - twoHoursFromNow.getTimezoneOffset());
    document.getElementById('validade').value = twoHoursFromNow.toISOString().slice(0, 16);
});

// Set minimum datetime to now
const now = new Date();
const twoHoursFromNow = new Date(now.getTime() + (2 * 60 * 60 * 1000)); // Adiciona 2 horas

// Ajustar para timezone local
now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
twoHoursFromNow.setMinutes(twoHoursFromNow.getMinutes() - twoHoursFromNow.getTimezoneOffset());

// Definir valor mínimo (agora)
document.getElementById('validade').min = now.toISOString().slice(0, 16);

// Definir valor padrão (2 horas a partir de agora)
document.getElementById('validade').value = twoHoursFromNow.toISOString().slice(0, 16);
</script>

<?php include 'includes/footer.php'; ?>