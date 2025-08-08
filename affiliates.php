<?php
require_once 'config/config.php';
requireAdminLogin();

$pageTitle = 'Sistema de Afiliados';

$userModel = new User();

// Buscar usuário específico se fornecido
$selectedUserId = (int)($_GET['user_id'] ?? 0);
$selectedUser = null;
$affiliateNetwork = [];
$affiliateStats = [];

if ($selectedUserId) {
    $selectedUser = $userModel->getUserById($selectedUserId);
    if ($selectedUser) {
        $affiliateNetwork = $userModel->getAffiliateNetwork($selectedUserId);
        $affiliateStats = $userModel->getAffiliateStats($selectedUserId);
    }
}

// Buscar usuários para seleção
$searchTerm = sanitize($_GET['search'] ?? '');
$users = [];
if ($searchTerm) {
    $db = new Database();
    $users = $db->fetchAll(
        "SELECT id, name, email, codigo_indicacao, codigo_indicador FROM users 
         WHERE name LIKE ? OR email LIKE ? 
         ORDER BY name LIMIT 20",
        ["%{$searchTerm}%", "%{$searchTerm}%"]
    );
}

include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0">Sistema de Afiliados</h1>
        <p class="text-muted">Visualize e gerencie a rede de afiliados até 10 níveis</p>
    </div>
</div>

<!-- Busca de Usuário -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-search me-2"></i>
            Buscar Usuário
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <label for="search" class="form-label">Nome ou Email</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?php echo escape($searchTerm); ?>" 
                       placeholder="Digite o nome ou email do usuário">
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-1"></i>Buscar
                </button>
                <?php if ($selectedUserId): ?>
                    <a href="affiliates.php" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Limpar
                    </a>
                <?php endif; ?>
            </div>
        </form>
        
        <?php if ($searchTerm && !empty($users)): ?>
            <div class="mt-3">
                <h6>Resultados da busca:</h6>
                <div class="list-group">
                    <?php foreach ($users as $user): ?>
                        <a href="affiliates.php?user_id=<?php echo $user['id']; ?>" 
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?php echo escape($user['name']); ?></h6>
                                    <small class="text-muted"><?php echo escape($user['email']); ?></small>
                                    <br><small class="text-info">Indicado por: <?php echo $user['codigo_indicador'] ? escape($user['codigo_indicador']) : 'Nenhum'; ?></small>
                                </div>
                                <small class="text-muted">
                                    Código: <?php echo escape($user['codigo_indicacao']); ?>
                                </small>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($selectedUser): ?>
    <!-- Informações do Usuário Selecionado -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-user me-2"></i>
                Usuário Selecionado
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Nome:</strong> <?php echo escape($selectedUser['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo escape($selectedUser['email']); ?></p>
                    <p><strong>Código de Indicação:</strong> 
                       <code><?php echo escape($selectedUser['codigo_indicacao']); ?></code></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Saldo:</strong> 
                       <?php echo formatMoney($selectedUser['saldo_carteira'] ?? 0, 'USD'); ?></p>
                    <p><strong>Cadastro:</strong> <?php echo formatDate($selectedUser['created_at']); ?></p>
                    <p><strong>Status:</strong> 
                       <span class="badge <?php echo $selectedUser['status'] === 'ativo' ? 'bg-success' : 'bg-danger'; ?>">
                           <?php echo ucfirst($selectedUser['status']); ?>
                       </span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Estatísticas por Nível -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-chart-bar me-2"></i>
                Estatísticas da Rede por Nível
            </h5>
        </div>
        <div class="card-body">
            <!-- Cabeçalho da tabela -->
            <div class="affiliates-stats-header">
                <div class="stats-col stats-nivel">Nível</div>
                <div class="stats-col stats-usuarios">Usuários</div>
                <div class="stats-col stats-deposito">Total Depositado</div>
                <div class="stats-col stats-saque">Total Sacado</div>
                <div class="stats-col stats-investido">Total Investido</div>
            </div>
            
            <!-- Dados da tabela -->
            <div class="affiliates-stats-body">
                <?php 
                $totalUsers = 0;
                $totalDeposits = 0;
                $totalWithdrawals = 0;
                $totalInvestments = 0;
                $totalCommissions = 0;
                
                for ($level = 1; $level <= 10; $level++): 
                    $stats = $affiliateStats[$level] ?? [
                        'total_users' => 0,
                        'total_deposits' => 0,
                        'total_withdrawals' => 0,
                        'total_investments' => 0,
                        'total_commissions' => 0
                    ];
                    
                    $totalUsers += $stats['total_users'];
                    $totalDeposits += $stats['total_deposits'];
                    $totalWithdrawals += $stats['total_withdrawals'];
                    $totalInvestments += $stats['total_investments'];
                    $totalCommissions += $stats['total_commissions'];
                ?>
                    <div class="stats-row <?php echo $stats['total_users'] > 0 ? '' : 'text-muted'; ?>">
                        <div class="stats-col stats-nivel"><strong>Nível <?php echo $level; ?></strong></div>
                        <div class="stats-col stats-usuarios"><?php echo number_format($stats['total_users']); ?></div>
                        <div class="stats-col stats-deposito"><?php echo formatMoney($stats['total_deposits'], 'USD'); ?></div>
                        <div class="stats-col stats-saque"><?php echo formatMoney($stats['total_withdrawals'], 'USD'); ?></div>
                        <div class="stats-col stats-investido"><?php echo formatMoney($stats['total_investments'], 'USD'); ?></div>
                    </div>
                <?php endfor; ?>
            </div>
            
            <!-- Total -->
            <div class="affiliates-stats-footer">
                <div class="stats-col stats-nivel"><strong>TOTAL</strong></div>
                <div class="stats-col stats-usuarios"><strong><?php echo number_format($totalUsers); ?></strong></div>
                <div class="stats-col stats-deposito"><strong><?php echo formatMoney($totalDeposits, 'USD'); ?></strong></div>
                <div class="stats-col stats-saque"><strong><?php echo formatMoney($totalWithdrawals, 'USD'); ?></strong></div>
                <div class="stats-col stats-investido"><strong><?php echo formatMoney($totalInvestments, 'USD'); ?></strong></div>
            </div>
        </div>
    </div>

    <!-- Árvore Genealógica -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-sitemap me-2"></i>
                Árvore Genealógica
            </h5>
        </div>
        <div class="card-body">
            <?php if (empty($affiliateNetwork)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-sitemap fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nenhum afiliado encontrado</h5>
                    <p class="text-muted">Este usuário ainda não possui indicados diretos</p>
                </div>
            <?php else: ?>
                <div id="affiliateTree">
                    <?php renderAffiliateTree($affiliateNetwork); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>
    <!-- Estado inicial -->
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-network-wired fa-4x text-muted mb-4"></i>
            <h4 class="text-muted">Sistema de Afiliados</h4>
            <p class="text-muted mb-4">
                Use o campo de busca acima para encontrar um usuário e visualizar sua rede de afiliados.
            </p>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="row text-start">
                        <div class="col-md-6">
                            <h6><i class="fas fa-check text-success me-2"></i>Funcionalidades:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-arrow-right text-primary me-2"></i>Visualização até 10 níveis</li>
                                <li><i class="fas fa-arrow-right text-primary me-2"></i>Estatísticas por nível</li>
                                <li><i class="fas fa-arrow-right text-primary me-2"></i>Árvore genealógica visual</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-chart-line text-info me-2"></i>Métricas:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-arrow-right text-primary me-2"></i>Total de usuários por nível</li>
                                <li><i class="fas fa-arrow-right text-primary me-2"></i>Valores depositados/sacados</li>
                                <li><i class="fas fa-arrow-right text-primary me-2"></i>Comissões geradas</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
.affiliate-tree {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.affiliate-node {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 15px;
    margin: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: all 0.3s;
}

.affiliate-node:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.affiliate-level-1 { border-color: #007bff; }
.affiliate-level-2 { border-color: #28a745; }
.affiliate-level-3 { border-color: #ffc107; }
.affiliate-level-4 { border-color: #dc3545; }
.affiliate-level-5 { border-color: #6f42c1; }
.affiliate-level-6 { border-color: #fd7e14; }
.affiliate-level-7 { border-color: #20c997; }
.affiliate-level-8 { border-color: #e83e8c; }
.affiliate-level-9 { border-color: #6c757d; }
.affiliate-level-10 { border-color: #343a40; }

.affiliate-children {
    margin-left: 30px;
    border-left: 2px dashed #dee2e6;
    padding-left: 20px;
}

/* Estilos para a tabela de estatísticas - Layout igual à imagem */
.affiliates-stats-header,
.stats-row,
.affiliates-stats-footer {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
    gap: 0.5rem;
    padding: 0.75rem;
    border-bottom: 1px solid #e9ecef;
    align-items: center;
}

.affiliates-stats-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    border-radius: 8px 8px 0 0;
    margin-bottom: 0;
}

.stats-row {
    background: white;
    transition: background-color 0.2s ease;
}

.stats-row:hover {
    background-color: rgba(102, 126, 234, 0.05);
}

.affiliates-stats-footer {
    background: #343a40;
    color: white;
    font-weight: 600;
    border-radius: 0 0 8px 8px;
    border-bottom: none;
    margin-top: 0;
}

.stats-col {
    padding: 0.25rem;
    text-align: center;
}

.stats-nivel {
    text-align: left !important;
}

.stats-usuarios {
    text-align: center !important;
}

.stats-deposito,
.stats-saque,
.stats-investido {
    text-align: right !important;
}

/* Responsividade - mantém o mesmo layout em todas as telas */
@media (max-width: 768px) {
    .affiliates-stats-header,
    .stats-row,
    .affiliates-stats-footer {
        grid-template-columns: 1fr 1fr 1fr 1fr 1fr;
        gap: 0.25rem;
        padding: 0.5rem 0.25rem;
        font-size: 0.8rem;
    }
    
    .stats-col {
        padding: 0.125rem;
    }
}

@media (max-width: 480px) {
    .affiliates-stats-header,
    .stats-row,
    .affiliates-stats-footer {
        font-size: 0.75rem;
        padding: 0.4rem 0.2rem;
    }
}
</style>

<?php
function renderAffiliateTree($network, $level = 1) {
    if (empty($network)) return;
    
    echo '<div class="affiliate-tree">';
    foreach ($network as $affiliate) {
        echo '<div class="affiliate-node affiliate-level-' . $level . '">';
        echo '<div class="row align-items-center">';
        echo '<div class="col-md-8">';
        echo '<h6 class="mb-1">' . escape($affiliate['name']) . '</h6>';
        echo '<small class="text-muted">' . escape($affiliate['email']) . '</small><br>';
        echo '<small class="text-muted">Código: ' . escape($affiliate['codigo_indicacao']) . '</small>';
        echo '</div>';
        echo '<div class="col-md-4 text-end">';
        echo '<div class="badge bg-primary mb-1">Nível ' . $level . '</div><br>';
        echo '<small class="text-success">Saldo: ' . formatMoney($affiliate['saldo'] ?? 0, 'USD') . '</small><br>';
        echo '<small class="text-info">' . ($affiliate['direct_referrals'] ?? 0) . ' indicados</small>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        if (!empty($affiliate['children'])) {
            echo '<div class="affiliate-children">';
            renderAffiliateTree($affiliate['children'], $level + 1);
            echo '</div>';
        }
    }
    echo '</div>';
}
?>

<?php include 'includes/footer.php'; ?>