<?php
// ============================================================
// DASHBOARD - PÁGINA PRINCIPAL
// ============================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

verificarAutenticacao();

$titulo = 'Dashboard';
$resumo = obterResumoDashboard();
$dadosGrafico = obterDadosGraficoFaturamento();
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="dashboard-hero">
    <div>
        <div class="dashboard-kicker">
            <i class="fas fa-chart-line"></i>
            Painel operacional
        </div>
        <h1>Visão geral do sistema</h1>
        <p>Acompanhe clientes, receitas e pendências em uma tela mais clara e pronta para uso diário.</p>
    </div>
    <div class="dashboard-date-card d-none d-md-flex">
        <span class="dashboard-date-label">Hoje</span>
        <strong><?php echo date('d/m/Y'); ?></strong>
        <span class="dashboard-date-subtitle">Resumo atualizado em tempo real</span>
    </div>
</div>

<div class="row dashboard-stats-row">
    <div class="col-md-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon bg-primary-soft">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <span class="number"><?php echo $resumo['total_clientes']; ?></span>
                <span class="label">Clientes Ativos</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card stat-card-success">
            <div class="stat-icon bg-success-soft">
                <i class="fas fa-hand-holding-usd"></i>
            </div>
            <div class="stat-content">
                <span class="number"><?php echo formatarMoeda($resumo['pagamentos_pendentes']); ?></span>
                <span class="label">A Receber</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon bg-warning-soft">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-content">
                <span class="number"><?php echo formatarMoeda($resumo['total_mes']); ?></span>
                <span class="label">Recebido este Mês</span>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="stat-card stat-card-danger">
            <div class="stat-icon bg-danger-soft">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-content">
                <span class="number"><?php echo $resumo['atrasados']; ?></span>
                <span class="label">Atrasados</span>
            </div>
        </div>
    </div>
</div>

<div class="row dashboard-main-grid mt-2">
    <!-- Gráfico de Faturamento -->
    <div class="col-md-8">
        <div class="card dashboard-panel-card">
            <div class="card-header dashboard-panel-header d-flex justify-content-between align-items-center">
                <div>
                    <div class="dashboard-panel-eyebrow">Financeiro</div>
                    <span><i class="fas fa-chart-area"></i> Desempenho dos últimos 6 meses</span>
                </div>
                <small class="text-muted dashboard-panel-meta">Valores em <?php echo MOEDA_SIMBOLO; ?></small>
            </div>
            <div class="card-body">
                <canvas id="faturamentoChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Ações Rápidas -->
    <div class="col-md-4">
        <div class="card dashboard-panel-card">
            <div class="card-header dashboard-panel-header">
                <div class="dashboard-panel-eyebrow">Atalhos</div>
                <i class="fas fa-bolt"></i> Ações Rápidas
            </div>
            <div class="card-body dashboard-actions-body">
                <div class="list-group list-group-flush dashboard-actions-list">
                    <a href="/SystemContracts/clientes/adicionar.php" class="list-group-item list-group-item-action border-0 d-flex align-items-center px-0 dashboard-action-item">
                        <div class="icon-box bg-light text-primary mr-3"><i class="fas fa-user-plus"></i></div>
                        <span>Novo Cliente</span>
                    </a>
                    <a href="/SystemContracts/contratos/adicionar.php" class="list-group-item list-group-item-action border-0 d-flex align-items-center px-0 dashboard-action-item">
                        <div class="icon-box bg-light text-accent mr-3"><i class="fas fa-file-signature"></i></div>
                        <span>Novo Contrato</span>
                    </a>
                    <a href="/SystemContracts/pagamentos/adicionar.php" class="list-group-item list-group-item-action border-0 d-flex align-items-center px-0 dashboard-action-item">
                        <div class="icon-box bg-light text-success mr-3"><i class="fas fa-hand-holding-usd"></i></div>
                        <span>Registrar Pagamento</span>
                    </a>
                    <?php if ($_SESSION['user_perfil'] === 'admin'): ?>
                    <a href="/SystemContracts/usuarios/adicionar.php" class="list-group-item list-group-item-action border-0 d-flex align-items-center px-0 dashboard-action-item">
                        <div class="icon-box bg-light text-warning mr-3"><i class="fas fa-user-shield"></i></div>
                        <span>Adicionar Operador</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card dashboard-table-card dashboard-panel-card">
            <div class="card-header">
                <div>
                    <div class="dashboard-panel-eyebrow">Cadastro</div>
                    <span><i class="fas fa-users"></i> Clientes Recentes</span>
                </div>
                <a href="/SystemContracts/clientes/listar.php" class="btn btn-sm btn-light dashboard-link-btn">Ver todos</a>
            </div>
            <div class="card-body dashboard-table-body">
                <table class="table table-sm table-hover dashboard-table">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $clientes = obterClientes();
                        $clientes = array_slice($clientes, 0, 5);
                        foreach ($clientes as $cliente):
                        ?>
                        <tr>
                            <td><?php echo $cliente['nome']; ?></td>
                            <td><?php echo $cliente['email']; ?></td>
                            <td>
                                <a href="/SystemContracts/clientes/visualizar.php?id=<?php echo $cliente['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card dashboard-table-card dashboard-panel-card">
            <div class="card-header">
                <div>
                    <div class="dashboard-panel-eyebrow">Cobrança</div>
                    <span><i class="fas fa-credit-card"></i> Pagamentos Pendentes</span>
                </div>
                <a href="/SystemContracts/pagamentos/listar.php" class="btn btn-sm btn-light dashboard-link-btn">Ver todos</a>
            </div>
            <div class="card-body dashboard-table-body">
                <table class="table table-sm table-hover dashboard-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Valor</th>
                            <th>Vencimento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $pagamentos = obterPagamentos();
                        $pagamentos = array_filter($pagamentos, function($p) {
                            return $p['status'] === 'pendente' || $p['status'] === 'atrasado';
                        });
                        $pagamentos = array_slice($pagamentos, 0, 5);
                        foreach ($pagamentos as $pagamento):
                            $cliente = obterCliente($pagamento['cliente_id']);
                        ?>
                        <tr>
                            <td><?php echo $cliente['nome'] ?? 'N/A'; ?></td>
                            <td><?php echo formatarMoeda($pagamento['valor']); ?></td>
                            <td><?php echo formatarData($pagamento['data_vencimento']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('faturamentoChart').getContext('2d');
    const labels = <?php echo json_encode(array_column($dadosGrafico, 'mes')); ?>;
    const dataValues = <?php echo json_encode(array_column($dadosGrafico, 'total')); ?>;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Receita Realizada',
                data: dataValues,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#2563eb'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
