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

<div class="quitacao-hero mb-4 bg-white border-0">
    <div>
        <div class="quitacao-hero-kicker text-primary font-weight-bold">MÓDULO DE CAIXA</div>
        <h1 class="h3 mb-1 text-dark font-weight-bold">Quitação de Títulos</h1>
        <p class="text-muted mb-0">Selecione um cliente à esquerda e registre pagamentos com troco automático.</p>
    </div>
</div>

<?php echo exibirMensagem(); ?>

<div class="card quitacao-pdv-card p-0">
    <div class="row no-gutters">
        <div class="col-lg-3 col-md-4 quitacao-sidebar border-right">
            <div class="quitacao-sidebar-header">
                <h5 class="font-weight-bold mb-0">Clientes com pendências</h5>
                <div class="input-group input-group-sm mt-3 quitacao-search-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                    </div>
                    <input type="text" id="quitacaoSearch" class="form-control" placeholder="Filtrar cliente...">
                </div>
            </div>
            <ul class="list-group quitacao-clientes-list" id="quitacaoClientesList">
                <?php if (!empty($devedores)): ?>
                    <?php foreach ($devedores as $d): ?>
                        <li class="list-group-item list-group-item-action d-flex justify-content-between align-items-center quitacao-cliente" data-client-id="<?php echo $d['id']; ?>" data-cliente-nome="<?php echo htmlspecialchars($d['nome']); ?>">
                            <span class="d-flex align-items-center">
                                <span class="quitacao-cliente-icon mr-2"><i class="fas fa-search"></i></span>
                                <span class="quitacao-cliente-name"><?php echo htmlspecialchars($d['nome']); ?></span>
                            </span>
                            <span class="badge badge-pill bg-primary-soft text-primary"><?php echo number_format($d['total_titulos'] ?? 0); ?></span>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item text-muted">Nenhum cliente com pendências.</li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="col-lg-9 col-md-8 quitacao-main" id="quitacaoDetalhe">
            <div class="text-center text-muted py-5" id="quitacaoEmpty">Selecione um cliente à esquerda para ver as dívidas e registrar pagamento.</div>
        </div>
    </div>
</div>

<div class="modal fade" id="quitacaoPagamentoModal" tabindex="-1" role="dialog" aria-labelledby="quitacaoPagamentoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 bg-light">
                <h5 class="modal-title font-weight-bold text-dark py-2" id="quitacaoPagamentoModalLabel"><i class="fas fa-cash-register mr-2 text-primary"></i>FINALIZAR RECEBIMENTO</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true" class="text-dark">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-4">
                <form id="quitacaoPagamentoForm">
                    <input type="hidden" id="quitacaoPagamentoIds" name="ids_json" value="">
                    <input type="hidden" id="totalOriginal" value="0">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="mb-0 font-weight-bold text-dark">Formas de Recebimento</h5>
                            <p class="small text-muted mb-0">Você pode adicionar múltiplas formas para este pagamento</p>
                        </div>
                        <button type="button" class="btn btn-primary shadow-sm px-4" id="addQuitacaoForma" style="border-radius:12px">
                            <i class="fas fa-plus mr-1"></i> Adicionar Forma
                        </button>
                    </div>

                    <div id="quitacaoFormasList" class="mb-3"></div>

                    <div class="row quitacao-summary-grid mb-4">
                        <div class="col-md-3">
                            <div class="quitacao-summary-card bg-light">
                                <small class="text-uppercase font-weight-bold text-muted">Total Dívida</small>
                                <div class="quitacao-summary-value text-dark" id="quitacaoTotalSelecionado">R$ 0,00</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="quitacao-summary-card">
                                <small class="text-uppercase font-weight-bold text-primary">Total Recebido</small>
                                <div class="quitacao-summary-value text-primary" id="quitacaoTotalRecebido">R$ 0,00</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="quitacao-summary-card">
                                <small class="text-uppercase font-weight-bold text-success">Troco</small>
                                <div class="quitacao-summary-value text-success" id="quitacaoTotalTroco">R$ 0,00</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="quitacao-summary-card">
                                <small class="text-uppercase font-weight-bold text-danger">Faltando</small>
                                <div class="quitacao-summary-value text-danger" id="quitacaoTotalRestante">R$ 0,00</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0 mt-4">
                        <label for="quitacaoObservacoes" class="small font-weight-bold text-muted text-uppercase">Notas do Operador</label>
                        <textarea id="quitacaoObservacoes" name="observacoes" class="form-control border-0 bg-light p-3" rows="2" placeholder="Digite aqui alguma observação se necessário..." style="border-radius:15px"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 bg-white">
                <button type="button" class="btn btn-link text-muted font-weight-bold" data-dismiss="modal">Desistir</button>
                <button type="button" class="btn btn-primary px-5 py-3 font-weight-bold shadow-lg" id="quitacaoConfirmarPagamento" style="border-radius:15px; font-size:1.1rem"><i class="fas fa-check-circle mr-2"></i>CONFIRMAR PAGAMENTO E GERAR RECIBO</button>
            </div>
        </div>
    </div>
</div>

<template id="quitacaoFormaTemplate">
    <div class="quitacao-forma-item">
        <div class="row align-items-end">
            <div class="col-12 col-md-5 mb-2 mb-md-0">
                <label class="small text-muted mb-1">Forma</label>
                <select class="form-control quitacao-forma-metodo" required>
                    <option value="dinheiro">Dinheiro</option>
                    <option value="pix">PIX</option>
                    <option value="cartao">Cartão</option>
                    <option value="transferencia">Transferência</option>
                </select>
            </div>
            <div class="col-12 col-md-4 mb-2 mb-md-0">
                <label class="small text-muted mb-1">Valor</label>
                <input type="number" step="0.01" min="0" class="form-control quitacao-forma-valor" placeholder="0,00" required>
            </div>
            <div class="col-12 col-md-2 mb-2 mb-md-0 quitacao-forma-cartao-wrap d-none">
                <label class="small text-muted mb-1">Cartão</label>
                <select class="form-control quitacao-forma-cartao-tipo">
                    <option value="debito">Débito</option>
                    <option value="credito">Crédito</option>
                </select>
            </div>
            <div class="col-12 col-md-1 text-right">
                <button type="button" class="btn btn-outline-danger btn-sm quitacao-remover-forma" title="Remover">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<div class="modal fade" id="quitacaoReciboModal" tabindex="-1" role="dialog" aria-labelledby="quitacaoReciboModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title font-weight-bold text-success" id="quitacaoReciboModalLabel">
                    <i class="fas fa-check-circle mr-2"></i>Quitação confirmada com sucesso!
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted mb-3">O cupom fiscal foi gerado. Você pode compartilhar, imprimir ou abrir em nova aba.</p>
                <div class="border rounded bg-light" style="height: 62vh; min-height: 380px; overflow: hidden;">
                    <iframe id="quitacaoReciboPreview" title="Preview do cupom fiscal" src="about:blank" style="width:100%;height:100%;border:0;"></iframe>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-outline-secondary" id="quitacaoReciboOpen">
                    <i class="fas fa-external-link-alt mr-1"></i>Abrir em nova aba
                </button>
                <button type="button" class="btn btn-outline-primary" id="quitacaoReciboShare">
                    <i class="fas fa-share-alt mr-1"></i>Compartilhar
                </button>
                <button type="button" class="btn btn-primary" id="quitacaoReciboPrint">
                    <i class="fas fa-print mr-1"></i>Imprimir
                </button>
                <button type="button" class="btn btn-link text-muted" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>