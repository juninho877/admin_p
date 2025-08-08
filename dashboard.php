<?php
require_once 'config/config.php';
requireAdminLogin();

$pageTitle = 'Dashboard';

// Busca estatísticas
$financial = new Financial();
$stats = $financial->getDashboardStats();

// Dados para gráficos
$db = new Database();

// Gráfico de usuários por mês (últimos 12 meses)
$userGrowth = $db->fetchAll("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as count
    FROM users 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
");

// Gráfico financeiro (últimos 30 dias)
$financialChart = $db->fetchAll("
    SELECT 
        DATE(created_at) as date,
        SUM(CASE WHEN status = 'confirmado' THEN valor_usd ELSE 0 END) as deposits,
        (SELECT COALESCE(SUM(valor_usd), 0) FROM saques WHERE DATE(created_at) = DATE(d.created_at) AND status = 'aprovado') as withdrawals
    FROM depositos d
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");

include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0">Dashboard</h1>
        <p class="text-muted">Visão geral do sistema</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="fas fa-users"></i>
            </div>
            <h3 class="mb-1"><?php echo number_format($stats['users']['total_users']); ?></h3>
            <p class="text-muted mb-0">Total de Usuários</p>
            <small class="text-success">
                <i class="fas fa-arrow-up"></i>
                <?php echo $stats['users']['new_today']; ?> novos hoje
            </small>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <i class="fas fa-arrow-down"></i>
            </div>
            <h3 class="mb-1"><?php echo formatMoney($stats['financial']['total_deposits'], 'USD'); ?></h3>
            <p class="text-muted mb-0">Total Depositado</p>
            <small class="text-info">
                <i class="fas fa-clock"></i>
                <?php echo $stats['pending']['pending_deposits']; ?> pendentes
            </small>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon" style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);">
                <i class="fas fa-arrow-up"></i>
            </div>
            <h3 class="mb-1"><?php echo formatMoney($stats['financial']['total_withdrawals'], 'USD'); ?></h3>
            <p class="text-muted mb-0">Total Sacado</p>
            <small class="text-warning">
                <i class="fas fa-clock"></i>
                <?php echo $stats['pending']['pending_withdrawals']; ?> pendentes
            </small>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="stats-card">
            <div class="stats-icon" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                <i class="fas fa-wallet"></i>
            </div>
            <h3 class="mb-1"><?php echo formatMoney($stats['financial']['total_balance'], 'USD'); ?></h3>
            <p class="text-muted mb-0">Saldo Total</p>
            <small class="text-primary">
                <i class="fas fa-chart-line"></i>
                <?php echo formatMoney($stats['financial']['total_investments'], 'USD'); ?> investidos
            </small>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-xl-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Movimento Financeiro (Últimos 30 dias)
                </h5>
            </div>
            <div class="card-body">
                <canvas id="financialChart" height="100"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-xl-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user-plus me-2"></i>
                    Crescimento de Usuários
                </h5>
            </div>
            <div class="card-body">
                <canvas id="userGrowthChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-xl-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>
                    Depósitos Recentes
                </h5>
            </div>
            <div class="card-body">
                <?php
                $recentDeposits = $db->fetchAll("
                    SELECT d.*, u.name as user_name 
                    FROM depositos d 
                    JOIN users u ON u.id = d.user_id 
                    ORDER BY d.created_at DESC 
                    LIMIT 5
                ");
                ?>
                
                <?php if (empty($recentDeposits)): ?>
                    <p class="text-muted text-center">Nenhum depósito encontrado</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($recentDeposits as $deposit): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?php echo escape($deposit['user_name']); ?></h6>
                                    <small class="text-muted">
                                        <?php echo formatDate($deposit['created_at']); ?>
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold"><?php echo formatMoney($deposit['valor_usd'], 'USD'); ?></span>
                                    <br>
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
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-xl-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Ações Pendentes
                </h5>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <?php if ($stats['pending']['pending_deposits'] > 0): ?>
                        <a href="deposits.php?status=pendente" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-arrow-down text-warning me-2"></i>
                                    Depósitos Pendentes
                                </div>
                                <span class="badge bg-warning"><?php echo $stats['pending']['pending_deposits']; ?></span>
                            </div>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($stats['pending']['pending_withdrawals'] > 0): ?>
                        <a href="withdrawals.php?status=pendente" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-arrow-up text-danger me-2"></i>
                                    Saques Pendentes
                                </div>
                                <span class="badge bg-danger"><?php echo $stats['pending']['pending_withdrawals']; ?></span>
                            </div>
                        </a>
                    <?php endif; ?>
                    
                    <?php if ($stats['pending']['pending_deposits'] == 0 && $stats['pending']['pending_withdrawals'] == 0): ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                            <p class="mb-0">Nenhuma ação pendente</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Financial Chart
const financialCtx = document.getElementById('financialChart').getContext('2d');
const financialChart = new Chart(financialCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($financialChart, 'date')); ?>,
        datasets: [{
            label: 'Depósitos',
            data: <?php echo json_encode(array_column($financialChart, 'deposits')); ?>,
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            tension: 0.4
        }, {
            label: 'Saques',
            data: <?php echo json_encode(array_column($financialChart, 'withdrawals')); ?>,
            borderColor: '#dc3545',
            backgroundColor: 'rgba(220, 53, 69, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                    }
                }
            }
        }
    }
});

// User Growth Chart
const userCtx = document.getElementById('userGrowthChart').getContext('2d');
const userGrowthChart = new Chart(userCtx, {
    type: 'doughnut',
    data: {
        labels: ['Usuários Ativos', 'Usuários Bloqueados'],
        datasets: [{
            data: [
                <?php echo $stats['users']['active_users']; ?>,
                <?php echo $stats['users']['total_users'] - $stats['users']['active_users']; ?>
            ],
            backgroundColor: [
                '#28a745',
                '#dc3545'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>