<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
verificarAutenticacao();

$titulo = 'Pagamentos';
$filtro = $_GET['filtro'] ?? '';
$pagamentos = obterPagamentos(null, $filtro);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-credit-card text-primary mr-2"></i> Pagamentos</h1>
        <p class="text-muted mb-0">Gerencie todos os pagamentos de seus clientes.</p>
    </div>
    <div>
        <a href="#" data-remote-url="/SystemContracts/pagamentos/form.php" data-remote-title="Novo Pagamento" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Novo Pagamento
        </a>
    </div>
</div>

<?php echo exibirMensagem(); ?>

<div class="card">
    <div class="card-header bg-white py-3">
        <form method="GET" action="" class="row align-items-center">
            <div class="col-md-10">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
                    </div>
                    <input type="text" class="form-control bg-light border-left-0" name="filtro" placeholder="Buscar por cliente, descrição ou tipo..." value="<?php echo $filtro; ?>">
                </div>
            </div>
            <div class="col-md-2 text-right">
                <button class="btn btn-outline-primary btn-block" type="submit">Filtrar</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th class="pl-4">Cliente</th>
                    <th>Tipo</th>
                    <th>Valor</th>
                    <th>Vencimento</th>
                    <th>Status</th>
                    <th class="text-right pr-4">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($pagamentos) > 0): ?>
                    <?php foreach ($pagamentos as $pagamento): 
                    ?>
                    <tr>
                        <td class="pl-4 font-weight-bold text-dark"><?php echo $pagamento['cliente_nome'] ?? 'N/A'; ?></td>
                        <td class="text-secondary"><?php echo ucfirst($pagamento['tipo']); ?></td>
                        <td class="text-monospace"><?php echo formatarMoeda($pagamento['valor']); ?></td>
                        <td><?php echo formatarData($pagamento['data_vencimento']); ?></td>
                        <td>
                            <?php
                            $status_class = 'bg-primary-soft text-primary'; // Default
                            if ($pagamento['status'] === 'pago') $status_class = 'bg-success-soft text-success';
                            if ($pagamento['status'] === 'pendente') $status_class = 'bg-warning-soft text-warning';
                            if ($pagamento['status'] === 'atrasado') $status_class = 'bg-danger-soft text-danger';
                            if ($pagamento['status'] === 'cancelado') $status_class = 'bg-secondary-soft text-secondary';
                            ?>
                            <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($pagamento['status']); ?></span>
                        </td>
                        <td class="text-right pr-4">
                            <div class="btn-group">
                                <a href="#" data-remote-url="/SystemContracts/pagamentos/editar.php?id=<?php echo $pagamento['id']; ?>&ajax=1" data-remote-title="Editar Pagamento" class="btn btn-sm btn-light border" title="Editar">
                                    <i class="fas fa-edit text-warning"></i>
                                </a>
                                <a href="/SystemContracts/pagamentos/deletar.php?id=<?php echo $pagamento['id']; ?>" data-confirm="Tem certeza que deseja deletar este pagamento?" class="btn btn-sm btn-light border text-danger" title="Deletar">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted">Nenhum pagamento encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
