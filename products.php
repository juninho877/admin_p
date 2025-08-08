<?php
require_once 'config/config.php';
requireAdminLogin();

$pageTitle = 'Gestão de Produtos';

$productModel = new Product();

// Filtros
$filters = [
    'search' => sanitize($_GET['search'] ?? ''),
    'ativo' => isset($_GET['ativo']) ? (int)$_GET['ativo'] : null
];

$page = (int)($_GET['page'] ?? 1);

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = sanitize($_POST['action']);
    
    if ($action === 'create') {
        $data = [
            'nome' => sanitize($_POST['nome']),
            'avatar_url' => sanitize($_POST['avatar_url']),
            'comportamento' => sanitize($_POST['comportamento']),
            'ativo' => (int)($_POST['ativo'] ?? 1)
        ];
        
        try {
            $productModel->createProduct($data);
            logAction('PRODUCT_CREATED', "Produto criado: {$data['nome']}", $_SESSION['admin_id']);
            sendNotification('success', 'Produto criado com sucesso!');
        } catch (Exception $e) {
            sendNotification('error', 'Erro ao criar produto: ' . $e->getMessage());
        }
        
        header('Location: products.php');
        exit;
    }
    
    if ($action === 'update') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $data = [
            'nome' => sanitize($_POST['nome']),
            'avatar_url' => sanitize($_POST['avatar_url']),
            'comportamento' => sanitize($_POST['comportamento']),
            'ativo' => (int)($_POST['ativo'] ?? 1)
        ];
        
        try {
            $productModel->updateProduct($productId, $data);
            logAction('PRODUCT_UPDATED', "Produto atualizado: ID {$productId}", $_SESSION['admin_id']);
            sendNotification('success', 'Produto atualizado com sucesso!');
        } catch (Exception $e) {
            sendNotification('error', 'Erro ao atualizar produto: ' . $e->getMessage());
        }
        
        header('Location: products.php');
        exit;
    }
    
    if ($action === 'delete') {
        $productId = (int)($_POST['product_id'] ?? 0);
        
        try {
            $productModel->deleteProduct($productId);
            logAction('PRODUCT_DELETED', "Produto excluído: ID {$productId}", $_SESSION['admin_id']);
            sendNotification('success', 'Produto excluído com sucesso!');
        } catch (Exception $e) {
            sendNotification('error', 'Erro ao excluir produto: ' . $e->getMessage());
        }
        
        header('Location: products.php');
        exit;
    }
}

// Busca produtos
$result = $productModel->getProducts($filters, $page);
$products = $result['data'];
$totalPages = $result['pages'];

include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0">Gestão de Produtos</h1>
        <p class="text-muted">Gerencie todos os produtos/planos de investimento</p>
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
            <div class="col-md-4">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?php echo escape($filters['search']); ?>" 
                       placeholder="Nome do produto">
            </div>
            
            <div class="col-md-3">
                <label for="ativo" class="form-label">Status</label>
                <select class="form-select" id="ativo" name="ativo">
                    <option value="">Todos</option>
                    <option value="1" <?php echo $filters['ativo'] === 1 ? 'selected' : ''; ?>>Ativo</option>
                    <option value="0" <?php echo $filters['ativo'] === 0 ? 'selected' : ''; ?>>Inativo</option>
                </select>
            </div>
            
            <div class="col-md-5 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-1"></i>Filtrar
                </button>
                <a href="products.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-times me-1"></i>Limpar
                </a>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#productModal">
                    <i class="fas fa-plus me-1"></i>Novo Produto
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Produtos -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-box me-2"></i>
            Produtos (<?php echo number_format($result['total']); ?>)
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($products)): ?>
            <div class="text-center py-5">
                <i class="fas fa-box fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">Nenhum produto encontrado</h5>
                <p class="text-muted">Clique em "Novo Produto" para criar o primeiro produto</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Comportamento</th>
                            <th>Investimentos Ativos</th>
                            <th>Total Investido</th>
                            <th>Status</th>
                            <th>Data Criação</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr class="responsive-table-row">
                                <td data-label="ID"><?php echo $product['id']; ?></td>
                                <td data-label="Nome">
                                    <div class="d-flex align-items-center">
                                        <?php if ($product['avatar_url']): ?>
                                            <img src="<?php echo escape($product['avatar_url']); ?>" 
                                                 class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                                        <?php else: ?>
                                            <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                <i class="fas fa-box text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="fw-bold"><?php echo escape($product['nome']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Comportamento">
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
                                </td>
                                <td data-label="Investimentos Ativos">
                                    <span class="badge bg-info">
                                        <?php echo $product['active_investments']; ?>
                                    </span>
                                </td>
                                <td data-label="Total Investido">
                                    <span class="fw-bold text-success">
                                        <?php echo formatMoney($product['total_invested'], 'USD'); ?>
                                    </span>
                                </td>
                                <td data-label="Status">
                                    <?php
                                    $statusBadge = $product['ativo'] ? 'bg-success' : 'bg-danger';
                                    $statusText = $product['ativo'] ? 'Ativo' : 'Inativo';
                                    ?>
                                    <span class="badge <?php echo $statusBadge; ?>">
                                        <?php echo $statusText; ?>
                                    </span>
                                </td>
                                <td data-label="Data Criação">
                                    <small><?php echo formatDate($product['created_at']); ?></small>
                                </td>
                                <td data-label="Ações">
                                    <div class="btn-group" role="group">
                                        <a href="product_detail.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" 
                                           data-bs-toggle="tooltip" title="Ver Detalhes">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-warning" 
                                                onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)"
                                                data-bs-toggle="tooltip" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo escape($product['nome']); ?>')"
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
                    $baseUrl = 'products.php?' . http_build_query(array_filter($filters));
                    echo generatePagination($page, $totalPages, $baseUrl);
                    ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Produto -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="productModalTitle">
                    <i class="fas fa-box me-2"></i>
                    Novo Produto
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="productForm" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" id="product_action" value="create">
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
                            <option value="moderado" selected>Moderado</option>
                            <option value="agressivo">Agressivo</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="ativo" name="ativo" value="1" checked>
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

<!-- Form oculto para exclusão -->
<form id="deleteForm" method="POST" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="product_id" id="delete_product_id">
</form>

<script>
function editProduct(product) {
    document.getElementById('productModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Editar Produto';
    document.getElementById('product_action').value = 'update';
    document.getElementById('product_id').value = product.id;
    document.getElementById('nome').value = product.nome;
    document.getElementById('avatar_url').value = product.avatar_url || '';
    document.getElementById('comportamento').value = product.comportamento;
    document.getElementById('ativo').checked = product.ativo == 1;
    
    const modal = new bootstrap.Modal(document.getElementById('productModal'));
    modal.show();
}

function deleteProduct(productId, productName) {
    Swal.fire({
        title: 'Confirmar exclusão',
        text: `Tem certeza que deseja excluir o produto "${productName}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete_product_id').value = productId;
            document.getElementById('deleteForm').submit();
        }
    });
}

// Reset modal when closed
document.getElementById('productModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('productModalTitle').innerHTML = '<i class="fas fa-box me-2"></i>Novo Produto';
    document.getElementById('product_action').value = 'create';
    document.getElementById('productForm').reset();
    document.getElementById('product_id').value = '';
    document.getElementById('ativo').checked = true;
});
</script>

<?php include 'includes/footer.php'; ?>