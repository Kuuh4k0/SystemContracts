<?php
if (!isset($conn)) require_once __DIR__ . '/../config/database.php';
if (!function_exists('obterClientes')) require_once __DIR__ . '/../includes/functions.php';

$clientes = $clientes ?? obterClientes();
$cobrancasExistentes = $cobrancasExistentes ?? [];
$produtos = $conn->query("SELECT * FROM produtos WHERE ativo = 1 ORDER BY nome ASC")->fetchAll();
$descricao = $descricao ?? '';
$valor_total = $valor_total ?? '';
$data_inicio = $data_inicio ?? '';
$data_fim = $data_fim ?? '';
$status = $status ?? 'ativo';
$clienteSelecionado = $clienteSelecionado ?? '';
$arquivoPdfAtual = $arquivoPdfAtual ?? '';
?>
<?php $form_action = $form_action ?? '/SystemContracts/contratos/adicionar.php'; ?>
<form method="POST" action="<?php echo $form_action; ?>" enctype="multipart/form-data">
    <div class="form-group">
        <label for="cliente_id">Cliente *</label>
        <select class="form-control" id="cliente_id" name="cliente_id" required>
            <option value="">Selecione um cliente</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?php echo $cliente['id']; ?>" <?php echo ((string)$clienteSelecionado === (string)$cliente['id']) ? 'selected' : ''; ?>><?php echo $cliente['nome']; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="arquivo_pdf">Arquivo PDF do Contrato</label>
        <input type="file" class="form-control-file" id="arquivo_pdf" name="arquivo_pdf" accept="application/pdf">
        <small class="form-text text-muted">Envie o PDF do contrato para armazenar no sistema.</small>
        <?php if (!empty($arquivoPdfAtual)): ?>
            <div class="mt-2">
                <a href="/SystemContracts/uploads/contratos/<?php echo rawurlencode($arquivoPdfAtual); ?>" target="_blank" rel="noopener">
                    <i class="fas fa-file-pdf text-danger mr-1"></i> PDF atual: <?php echo htmlspecialchars($arquivoPdfAtual); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <div class="form-group">
        <label>Cobranças</label>
        <div id="cobrancasList" class="mb-2">
            <?php if (!empty($cobrancasExistentes)): ?>
                <?php foreach ($cobrancasExistentes as $cobranca): ?>
                    <div class="card mb-2 cobranca-item" data-cobranca-id="<?php echo htmlspecialchars($cobranca['id']); ?>">
                        <div class="card-body p-2">
                            <div class="form-row align-items-end">
                                <div class="col-md-3">
                                    <label>Tipo</label>
                                    <select class="form-control cobranca-tipo" name="cobrancas[tipo][]">
                                        <option value="momentanea" <?php echo ($cobranca['tipo'] === 'momentanea') ? 'selected' : ''; ?>>Momentânea</option>
                                        <option value="mensal" <?php echo ($cobranca['tipo'] === 'mensal') ? 'selected' : ''; ?>>Mensal</option>
                                        <option value="trimestral" <?php echo ($cobranca['tipo'] === 'trimestral') ? 'selected' : ''; ?>>Trimestral</option>
                                        <option value="anual" <?php echo ($cobranca['tipo'] === 'anual') ? 'selected' : ''; ?>>Anual</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Produto</label>
                                    <select class="form-control cobranca-produto" name="cobrancas[produto_id][]">
                                        <option value="">Nenhum</option>
                                        <?php foreach ($produtos as $p): ?>
                                            <option value="<?php echo $p['id']; ?>" data-preco="<?php echo $p['preco']; ?>" <?php echo (isset($cobranca['produto_id']) && $cobranca['produto_id'] == $p['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($p['nome']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label>Valor (R$)</label>
                                    <input type="number" step="0.01" class="form-control cobranca-valor" name="cobrancas[valor][]" required value="<?php echo htmlspecialchars($cobranca['valor']); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label>Descrição</label>
                                    <input type="text" class="form-control cobranca-descricao" name="cobrancas[descricao][]" value="<?php echo htmlspecialchars($cobranca['descricao'] ?? ''); ?>">
                                </div>
                                <div class="col-md-1 text-right">
                                    <button type="button" class="btn btn-sm btn-outline-danger removerCobranca" title="Remover"><i class="fas fa-trash"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button type="button" id="adicionarCobranca" class="btn btn-sm btn-outline-primary"><i class="fas fa-plus"></i> Adicionar Cobrança</button>
        <small class="form-text text-muted">Adicione quantas cobranças desejar (momentânea, mensal, trimestral, anual).</small>
    </div>
    <!-- Status and Valor Total removed from visible form per request -->
    <input type="hidden" id="status" name="status" value="<?php echo htmlspecialchars($status); ?>">
    <input type="hidden" id="valor_total" name="valor_total" value="<?php echo htmlspecialchars($valor_total); ?>">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="data_inicio">Data Início *</label>
                <input type="date" class="form-control" id="data_inicio" name="data_inicio" required value="<?php echo htmlspecialchars($data_inicio); ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                    <label for="data_fim">Data Fim</label>
                    <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?php echo htmlspecialchars($data_fim); ?>">
            </div>
        </div>
    </div>
</form>

<!-- Template for cobrança row -->
<template id="cobrancaTemplate">
    <div class="card mb-2 cobranca-item">
        <div class="card-body p-2">
            <div class="form-row align-items-end">
                <div class="col-md-3">
                    <label>Tipo</label>
                    <select class="form-control cobranca-tipo" name="cobrancas[tipo][]">
                        <option value="momentanea">Momentânea</option>
                        <option value="mensal">Mensal</option>
                        <option value="trimestral">Trimestral</option>
                        <option value="anual">Anual</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Produto</label>
                    <select class="form-control cobranca-produto" name="cobrancas[produto_id][]">
                        <option value="">Nenhum</option>
                        <?php foreach ($produtos as $p): ?>
                            <option value="<?php echo $p['id']; ?>" data-preco="<?php echo $p['preco']; ?>"><?php echo htmlspecialchars($p['nome']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Valor (R$)</label>
                    <input type="number" step="0.01" class="form-control cobranca-valor" name="cobrancas[valor][]" required>
                </div>
                <div class="col-md-3">
                    <label>Descrição</label>
                    <input type="text" class="form-control cobranca-descricao" name="cobrancas[descricao][]">
                </div>
                <div class="col-md-1 text-right">
                    <button type="button" class="btn btn-sm btn-outline-danger removerCobranca" title="Remover"><i class="fas fa-trash"></i></button>
                </div>
            </div>
        </div>
    </div>
</template>
