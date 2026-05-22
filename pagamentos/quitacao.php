<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
// include fallback helpers in case includes/functions.php is malformed
require_once __DIR__ . '/../includes/quitacao_helpers.php';
verificarAutenticacao();

$titulo = 'Quitação de Pagamentos';
$devedores = obterClientesComPendencias();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-receipt text-primary mr-2"></i> Quitação</h1>
        <p class="text-muted mb-0">Selecione um cliente à esquerda e registre pagamentos à direita.</p>
    </div>
    <div>
        <!-- reserved -->
    </div>
</div>

<?php echo exibirMensagem(); ?>

<div class="card p-3">
    <div class="row">
        <div class="col-md-4 border-right" style="max-height:70vh; overflow:auto;">
            <h5 class="font-weight-bold">Clientes com pendências</h5>
            <ul class="list-group mt-3" id="quitacaoClientesList">
                <?php if (!empty($devedores)): ?>
                    <?php foreach ($devedores as $d): ?>
                        <li class="list-group-item list-group-item-action d-flex justify-content-between align-items-center quitacao-cliente" data-client-id="<?php echo $d['id']; ?>">
                            <span><?php echo htmlspecialchars($d['nome']); ?></span>
                            <span class="badge badge-pill bg-primary-soft text-primary"><?php echo number_format($d['total_titulos'] ?? 0); ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item text-muted">Nenhum cliente com pendências.</li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="col-md-8" id="quitacaoDetalhe" style="max-height:70vh; overflow:auto;">
            <div class="text-center text-muted py-5" id="quitacaoEmpty">Selecione um cliente à esquerda para ver as dívidas e registrar pagamento.</div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
