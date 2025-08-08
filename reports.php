<?php
require_once 'config/config.php';
requireAdminLogin();

$pageTitle = 'Relatórios';

$financial = new Financial();

// Período padrão (últimos 30 dias)
$dateFrom = sanitize($_GET['date_from'] ?? date('Y-m-d', strtotime('-30 days')));
$dateTo = sanitize($_GET['date_to'] ?? date('Y-m-d'));

// Gerar relatório
$report = null;
if ($dateFrom && $dateTo) {
    $report = $financial->getFinancialReport($dateFrom, $dateTo);
}

include 'includes/header.php';
?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0">Relatórios</h1>
        <p class="text-muted">Relatórios financeiros e estatísticas do sistema</p>
    </div>
</div>

<!-- Filtros de Período -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-calendar me-2"></i>
            Período do Relatório
        </h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="date_from" class="form-label">Data Inicial</label>
                <input type="date" class="form-control" id="date_from" name="date_from" 
                       value="<?php echo $dateFrom; ?>" required>
            </div>
            
            <div class="col-md-3">
                <label for="date_to" class="form-label">Data Final</label>
                <input type="date" class="form-control" id="date_to" name="date_to" 
                       value="<?php echo $dateTo; ?>" required>
            </div>
            
            <div class="col-md-6 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-chart-line me-1"></i>Gerar Relatório
                </button>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" 
                            data-bs-toggle="dropdown">
                        <i class="fas fa-clock me-1"></i>Períodos
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="?date_from=<?php echo date('Y-m-d'); ?>&date_to=<?php echo date('Y-m-d'); ?>">Hoje</a></li>
                        <li><a class="dropdown-item" href="?date_from=<?php echo date('Y-m-d', strtotime('-7 days')); ?>&date_to=<?php echo date('Y-m-d'); ?>">Últimos 7 dias</a></li>
                        <li><a class="dropdown-item" href="?date_from=<?php echo date('Y-m-d', strtotime('-30 days')); ?>&date_to=<?php echo date('Y-m-d'); ?>">Últimos 30 dias</a></li>
                        <li><a class="dropdown-item" href="?date_from=<?php echo date('Y-m-01'); ?>&date_to=<?php echo date('Y-m-t'); ?>">Este mês</a></li>
                        <li><a class="dropdown-item" href="?date_from=<?php echo date('Y-m-01', strtotime('-1 month')); ?>&date_to=<?php echo date('Y-m-t', strtotime('-1 month')); ?>">Mês passado</a></li>
                    </ul>
                </div>
            </div>
        </form>
    </div>
</div>

<?php if ($report): ?>
    <!-- Resumo Executivo -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="stats-icon" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <h3 class="mb-1"><?php echo formatMoney($report['deposits']['confirmed_amount'], 'USD'); ?></h3>
                <p class="text-muted mb-0">Depósitos Confirmados</p>
                <small class="text-info">
                    <?php echo $report['deposits']['confirmed_deposits']; ?> de <?php echo $report['deposits']['total_deposits']; ?> depósitos
                </small>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="stats-icon" style="background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <h3 class="mb-1"><?php echo formatMoney($report['withdrawals']['approved_amount'], 'USD'); ?></h3>
                <p class="text-muted mb-0">Saques Aprovados</p>
                <small class="text-info">
                    <?php echo $report['withdrawals']['approved_withdrawals']; ?> de <?php echo $report['withdrawals']['total_withdrawals']; ?> saques
                </small>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="stats-icon" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="mb-1"><?php echo formatMoney($report['investments']['active_amount'], 'USD'); ?></h3>
                <p class="text-muted mb-0">Investimentos Ativos</p>
                <small class="text-info">
                    <?php echo $report['investments']['active_investments']; ?> de <?php echo $report['investments']['total_investments']; ?> investimentos
                </small>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="stats-card">
                <div class="stats-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-percentage"></i>
                </div>
                <h3 class="mb-1"><?php echo formatMoney($report['commissions']['total_commission_amount'], 'USD'); ?></h3>
                <p class="text-muted mb-0">Comissões Pagas</p>
                <small class="text-info">
                    <?php echo number_format($report['commissions']['total_commissions']); ?> comissões
                </small>
            </div>
        </div>
    </div>

    <!-- Análise Detalhada -->
    <div class="row mb-4">
        <div class="col-xl-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Análise Financeira Detalhada
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Categoria</th>
                                    <th>Total</th>
                                    <th>Processados</th>
                                    <th>Taxa de Sucesso</th>
                                    <th>Valor Médio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <i class="fas fa-arrow-down text-success me-2"></i>
                                        Depósitos
                                    </td>
                                    <td><?php echo number_format($report['deposits']['total_deposits']); ?></td>
                                    <td><?php echo number_format($report['deposits']['confirmed_deposits']); ?></td>
                                    <td>
                                        <?php 
                                        $depositRate = $report['deposits']['total_deposits'] > 0 
                                            ? ($report['deposits']['confirmed_deposits'] / $report['deposits']['total_deposits']) * 100 
                                            : 0;
                                        ?>
                                        <span class="badge <?php echo $depositRate >= 80 ? 'bg-success' : ($depositRate >= 60 ? 'bg-warning' : 'bg-danger'); ?>">
                                            <?php echo number_format($depositRate, 1); ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $avgDeposit = $report['deposits']['confirmed_deposits'] > 0 
                                            ? $report['deposits']['confirmed_amount'] / $report['deposits']['confirmed_deposits'] 
                                            : 0;
                                        echo formatMoney($avgDeposit, 'USD');
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="fas fa-arrow-up text-danger me-2"></i>
                                        Saques
                                    </td>
                                    <td><?php echo number_format($report['withdrawals']['total_withdrawals']); ?></td>
                                    <td><?php echo number_format($report['withdrawals']['approved_withdrawals']); ?></td>
                                    <td>
                                        <?php 
                                        $withdrawalRate = $report['withdrawals']['total_withdrawals'] > 0 
                                            ? ($report['withdrawals']['approved_withdrawals'] / $report['withdrawals']['total_withdrawals']) * 100 
                                            : 0;
                                        ?>
                                        <span class="badge <?php echo $withdrawalRate >= 80 ? 'bg-success' : ($withdrawalRate >= 60 ? 'bg-warning' : 'bg-danger'); ?>">
                                            <?php echo number_format($withdrawalRate, 1); ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $avgWithdrawal = $report['withdrawals']['approved_withdrawals'] > 0 
                                            ? $report['withdrawals']['approved_amount'] / $report['withdrawals']['approved_withdrawals'] 
                                            : 0;
                                        echo formatMoney($avgWithdrawal, 'USD');
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <i class="fas fa-chart-line text-warning me-2"></i>
                                        Investimentos
                                    </td>
                                    <td><?php echo number_format($report['investments']['total_investments']); ?></td>
                                    <td><?php echo number_format($report['investments']['active_investments']); ?></td>
                                    <td>
                                        <?php 
                                        $investmentRate = $report['investments']['total_investments'] > 0 
                                            ? ($report['investments']['active_investments'] / $report['investments']['total_investments']) * 100 
                                            : 0;
                                        ?>
                                        <span class="badge bg-info">
                                            <?php echo number_format($investmentRate, 1); ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $avgInvestment = $report['investments']['total_investments'] > 0 
                                            ? $report['investments']['total_investment_amount'] / $report['investments']['total_investments'] 
                                            : 0;
                                        echo formatMoney($avgInvestment, 'USD');
                                        ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calculator me-2"></i>
                        Resumo Financeiro
                    </h5>
                </div>
                <div class="card-body">
                    <?php
                    $netFlow = $report['deposits']['confirmed_amount'] - $report['withdrawals']['approved_amount'];
                    $roi = $report['deposits']['confirmed_amount'] > 0 
                        ? ($report['commissions']['total_commission_amount'] / $report['deposits']['confirmed_amount']) * 100 
                        : 0;
                    ?>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Fluxo Líquido</label>
                        <h4 class="<?php echo $netFlow >= 0 ? 'text-success' : 'text-danger'; ?>">
                            <?php echo formatMoney($netFlow, 'USD'); ?>
                        </h4>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">ROI em Comissões</label>
                        <h5 class="text-info"><?php echo number_format($roi, 2); ?>%</h5>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label text-muted">Período</label>
                        <p class="mb-0">
                            <?php echo formatDate($dateFrom, 'd/m/Y'); ?> até 
                            <?php echo formatDate($dateTo, 'd/m/Y'); ?>
                        </p>
                        <small class="text-muted">
                            <?php 
                            $days = (strtotime($dateTo) - strtotime($dateFrom)) / (60 * 60 * 24) + 1;
                            echo number_format($days) . ' dias';
                            ?>
                        </small>
                    </div>
                    
                    <hr>
                    
                    <div class="d-grid">
                        <button class="btn btn-outline-primary" onclick="exportReport()">
                            <i class="fas fa-download me-1"></i>Exportar Relatório
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Performance -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-chart-area me-2"></i>
                Performance do Período
            </h5>
        </div>
        <div class="card-body">
            <canvas id="performanceChart" height="100"></canvas>
        </div>
    </div>

<?php else: ?>
    <!-- Estado inicial -->
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-chart-bar fa-4x text-muted mb-4"></i>
            <h4 class="text-muted">Relatórios Financeiros</h4>
            <p class="text-muted mb-4">
                Selecione um período acima para gerar relatórios detalhados do sistema.
            </p>
        </div>
    </div>
<?php endif; ?>

<script>
<?php if ($report): ?>
// Performance Chart
const performanceCtx = document.getElementById('performanceChart').getContext('2d');
const performanceChart = new Chart(performanceCtx, {
    type: 'bar',
    data: {
        labels: ['Depósitos', 'Saques', 'Investimentos', 'Comissões'],
        datasets: [{
            label: 'Valores (USD)',
            data: [
                <?php echo $report['deposits']['confirmed_amount']; ?>,
                <?php echo $report['withdrawals']['approved_amount']; ?>,
                <?php echo $report['investments']['active_amount']; ?>,
                <?php echo $report['commissions']['total_commission_amount']; ?>
            ],
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',
                'rgba(220, 53, 69, 0.8)',
                'rgba(255, 193, 7, 0.8)',
                'rgba(102, 126, 234, 0.8)'
            ],
            borderColor: [
                'rgba(40, 167, 69, 1)',
                'rgba(220, 53, 69, 1)',
                'rgba(255, 193, 7, 1)',
                'rgba(102, 126, 234, 1)'
            ],
            borderWidth: 2
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
            },
            legend: {
                display: false
            }
        }
    }
});

function exportReport() {
    // Implementar exportação do relatório
    Swal.fire({
        title: 'Exportar Relatório',
        text: 'Funcionalidade em desenvolvimento',
        icon: 'info'
    });
}
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>