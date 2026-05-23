<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
verificarAutenticacao();
$titulo = 'Contratos';
$contratos = obterContratos();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-file-alt text-primary mr-2"></i> Contratos</h1>
        <p class="text-muted mb-0">Gestão e acompanhamento de contratos vigentes.</p>
    </div>
    <div>
        <a href="#" data-remote-url="/SystemContracts/contratos/form.php" data-remote-title="Novo Contrato" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Novo Contrato
        </a>
    </div>
</div>

<?php echo exibirMensagem(); ?>

<div class="card-header bg-white py-3">
    <form id="contratosSearchForm" method="GET" action="" class="row align-items-center">
            <div class="col-md-10">
            <div class="input-group" style="max-width:480px;">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
                </div>
                <input id="contratosSearch" name="filtro" type="text" class="form-control bg-light border-left-0" placeholder="Pesquisar por número, cliente ou status..." value="<?php echo htmlspecialchars($_GET['filtro'] ?? ''); ?>">
            </div>
        </div>
        <div class="col-md-2 text-right">
            <button class="btn btn-outline-primary btn-block" type="submit">Filtrar</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-body p-0">
        <table id="contratosTable" class="table table-hover mb-0">
            <thead>
                <tr>
                    <th class="pl-4">Número</th>
                    <th>Cliente</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th>Data Início</th>
                    <th class="text-right pr-4">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($contratos) > 0): ?>
                    <?php foreach ($contratos as $contrato): 
                        $cliente = obterCliente($contrato['cliente_id']);
                        
                        // Mapeamento de status para cores soft
                        $status_class = 'bg-primary-soft text-primary';
                        if ($contrato['status'] === 'ativo') $status_class = 'bg-success-soft text-success';
                        if ($contrato['status'] === 'rascunho') $status_class = 'bg-warning-soft text-warning';
                        if ($contrato['status'] === 'cancelado') $status_class = 'bg-danger-soft text-danger';
                        if ($contrato['status'] === 'finalizado') $status_class = 'bg-primary-soft text-primary';
                    ?>
                    <tr>
                        <td class="pl-4 font-weight-bold text-primary"><?php echo $contrato['numero_contrato']; ?></td>
                        <td class="text-dark"><?php echo $cliente['nome'] ?? 'N/A'; ?></td>
                        <td class="text-monospace"><?php echo formatarMoeda($contrato['valor_total']); ?></td>
                        <td>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input contrato-status-switch" id="contratoSwitch<?php echo $contrato['id']; ?>" data-id="<?php echo $contrato['id']; ?>" <?php echo ($contrato['status'] === 'ativo') ? 'checked' : ''; ?>>
                                <label class="custom-control-label" for="contratoSwitch<?php echo $contrato['id']; ?>"></label>
                            </div>
                        </td>
                        <td><?php echo formatarData($contrato['data_inicio']); ?></td>
                        <td class="text-right pr-4">
                            <div class="btn-group">
                                <a href="/SystemContracts/contratos/visualizar.php?id=<?php echo $contrato['id']; ?>" class="btn btn-sm btn-light border" title="Visualizar">
                                    <i class="fas fa-eye text-primary"></i>
                                </a>
                                <a href="#" data-remote-url="/SystemContracts/contratos/editar.php?id=<?php echo $contrato['id']; ?>&ajax=1" data-remote-title="Editar Contrato" class="btn btn-sm btn-light border mx-1" title="Editar">
                                    <i class="fas fa-edit text-warning"></i>
                                </a>
                                <a href="/SystemContracts/contratos/deletar.php?id=<?php echo $contrato['id']; ?>" class="btn btn-sm btn-light border text-danger" title="Deletar" data-confirm="Tem certeza que deseja deletar: <?php echo addslashes($contrato['numero_contrato']); ?>?">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-5 text-muted">Nenhum contrato encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
