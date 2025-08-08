<?php
require_once 'config/config.php';
requireAdminLogin();

$productId = (int)($_GET['id'] ?? 0);
if (!$productId) {
    header('Location: products.php');
    exit;
}

$productModel = new Product();
$product = $productModel->getProductById($productId);

if (!$product) {
    sendNotification('error', 'Produto não encontrado');
    header('Location: products.php');
    exit;
}

$pageTitle = 'Detalhes do Produto - ' . $product['nome'];

// Buscar investimentos do produto
$investments = $productModel->getProductInvestments($productId, 1, 20);

include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="products.php">Produtos</a></li>
                <li class="breadcrumb-item active">Detalhes</li>
            </ol>
        </nav>
        <h1 class="h3 mb-0">Detalhes do Produto</h1>
    </div>
</div>

<!-- Informações do Produto -->
<div class="row mb-4">
    <div class="col-xl-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-box me-2"></i>
                    Informações do Produto
                </h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <?php if ($product['avatar_url']): ?>
                        <img src="<?php echo escape($product['avatar_url']); ?>" 
                             class="rounded-circle mb-3" width="80" height="80" alt="Avatar">
                    <?php else: ?>
                        <div class="avatar-lg bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3">
                            <i class="fas fa-box fa-2x text-white"></i>
                        </div>
                    <?php endif; ?>
                    <h4 class="mb-0"><?php echo escape($product['nome']); ?></h4>
                    <?php
                    $statusBadge = $product['ativo'] ? 'bg-success' : 'bg-danger';
                    $statusText = $product['ativo'] ? 'Ativo' : 'Inativo';
                    ?>
                    <span class="badge <?php echo $statusBadge; ?> mt-2">
                        <?php echo $statusText; ?>
                    </span>
                </div>
                
                <hr>
                
                <div class="row text-center">
                    <div class="col-6">
                        <h5 class="text-primary mb-0"><?php echo $product['active_investments']; ?></h5>
                        <small class="text-muted">Investimentos Ativos</small>
                    </div>
                    <div class="col-6">
                        <h5 class="text-success mb-0"><?php echo formatMoney($product['total_invested'], 'USD'); ?></h5>
                        <small class="text-muted">Total Investido</small>
                    </div>
                </div>
                
                <hr>
                
                <div class="mb-2">
                    <strong>ID:</strong> #<?php echo $product['id']; ?>
                </div>
                <div class="mb-2">
                    <strong>Comportamento:</strong> 
                    <?php
                    $comportamentoBadge = match($product['comportamento']) {
                        'conservador' => 'bg-success',
                        'moderado' => 'bg-warning',
                        'agressivo' => 'bg-danger',
                        default => 'bg-secondary'
                    };
                    ?>
                    <span class="badge <?php echo $comportamentoBadge; ?>">
                        <?php echo ucfirst($product['comportamento']); ?>
                    </span>
                </div>
                <div class="mb-2">
                    <strong>Criado em:</strong> <?php echo formatDate($product['created_at']); ?>
                </div>
                <div class="mb-2">
                    <strong>Atualizado em:</strong> 
                    <?php echo $product['updated_at'] ? formatDate($product['updated_at']) : 'Nunca'; ?>
                </div>
                
                <hr>
                
                <div class="d-grid gap-2">
                    <button class="btn btn-warning" 
                            onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                        <i class="fas fa-edit me-1"></i>Editar Produto
                    </button>
                    <a href="products.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Voltar para Lista
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-8">
        <!-- Investimentos -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Investimentos (<?php echo number_format($investments['total']); ?>)
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($investments['data'])): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhum investimento encontrado</h5>
                        <p class="text-muted">Este produto ainda não possui investimentos</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Usuário</th>
                                    <th>Valor Investido</th>
                                    <th>Rendimento</th>
                                    <th>Status</th>
                                    <th>Data Início</th>
                                    <th>Data Fim</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($investments['data'] as $investment): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <div class="fw-bold"><?php echo escape($investment['user_name']); ?></div>
                                                <small class="text-muted"><?php echo escape($investment['user_email']); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-primary">
                                                <?php echo formatMoney($investment['valor_investido'], 'USD'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">
                                                <?php echo formatMoney($investment['rendimento_liquido'] ?? 0, 'USD'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $statusBadge = $investment['status'] === 'ativo' ? 'bg-success' : 'bg-secondary';
                                            ?>
                                            <span class="badge <?php echo $statusBadge; ?>">
                                                <?php echo ucfirst($investment['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small><?php echo formatDate($investment['data_inicio'], 'd/m/Y'); ?></small>
                                        </td>
                                        <td>
                                            <small><?php echo formatDate($investment['data_fim'], 'd/m/Y'); ?></small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="user_detail.php?id=<?php echo $investment['user_id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   data-bs-toggle="tooltip" title="Ver Usuário">
                                                    <i class="fas fa-user"></i>
                                                </a>
                                                <?php if ($investment['status'] === 'ativo'): ?>
                                                    <button class="btn btn-sm btn-outline-success" 
                                                            onclick="finalizeInvestment(<?php echo $investment['id']; ?>)"
                                                            data-bs-toggle="tooltip" title="Finalizar">
                                                        <i class="fas fa-check"></i>
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
                    <?php if ($investments['pages'] > 1): ?>
                        <div class="d-flex justify-content-center mt-4">
                            <?php
                            $baseUrl = 'product_detail.php?id=' . $productId;
                            echo generatePagination(1, $investments['pages'], $baseUrl);
                            ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Edição -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>
                    Editar Produto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="productForm" method="POST" action="products.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="product_id" id="product_id">
                    
                    <div class="mb-3">
                        <label for="nome" class="form-label">Nome do Produto *</label>
                        <input type="text" class="form-control" id="nome" name="nome" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="avatar_url" class="form-label">URL do Avatar</label>
                        <input type="url" class="form-control" id="avatar_url" name="avatar_url" 
                               placeholder="https://exemplo.com/avatar.jpg">
                    </div>
                    
                    <div class="mb-3">
                        <label for="comportamento" class="form-label">Comportamento</label>
                        <select class="form-select" id="comportamento" name="comportamento">
                            <option value="conservador">Conservador</option>
                            <option value="moderado">Moderado</option>
                            <option value="agressivo">Agressivo</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1">
                            <label class="form-check-label" for="ativo">
                                Produto Ativo
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

<script>
function editProduct(product) {
    document.getElementById('product_id').value = product.id;
    document.getElementById('nome').value = product.nome;
    document.getElementById('avatar_url').value = product.avatar_url || '';
    document.getElementById('comportamento').value = product.comportamento;
    document.getElementById('ativo').checked = product.ativo == 1;
    
    const modal = new bootstrap.Modal(document.getElementById('productModal'));
    modal.show();
}

function finalizeInvestment(investmentId) {
    Swal.fire({
        title: 'Finalizar Investimento',
        text: 'Tem certeza que deseja finalizar este investimento? O valor será creditado na carteira do usuário.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sim, finalizar!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Implementar finalização do investimento
            fetch('ajax/finalize_investment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    investment_id: investmentId
                })
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
        }
    });
}
</script>

<?php include 'includes/footer.php'; ?>