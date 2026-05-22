<?php
if (!isset($conn)) require_once __DIR__ . '/../config/database.php';
if (!function_exists('obterClientes')) require_once __DIR__ . '/../includes/functions.php';

$clientes = $clientes ?? obterClientes();
$tipo = $tipo ?? 'mensalidade';
$descricao = $descricao ?? '';
$valor = $valor ?? '';
$data_vencimento = $data_vencimento ?? '';
$status = $status ?? 'pendente';
$clienteSelecionado = $clienteSelecionado ?? '';
?>
<?php $form_action = $form_action ?? '/SystemContracts/pagamentos/adicionar.php'; ?>
<form method="POST" action="<?php echo $form_action; ?>">
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
        <label for="tipo">Tipo *</label>
        <select class="form-control" id="tipo" name="tipo" required>
            <option value="mensalidade" <?php echo $tipo === 'mensalidade' ? 'selected' : ''; ?>>Mensalidade</option>
            <option value="servico" <?php echo $tipo === 'servico' ? 'selected' : ''; ?>>Serviço</option>
            <option value="multa" <?php echo $tipo === 'multa' ? 'selected' : ''; ?>>Multa</option>
            <option value="outro" <?php echo $tipo === 'outro' ? 'selected' : ''; ?>>Outro</option>
        </select>
    </div>
    <div class="form-group">
        <label for="descricao">Descrição</label>
        <input type="text" class="form-control" id="descricao" name="descricao" value="<?php echo htmlspecialchars($descricao); ?>">
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="valor">Valor *</label>
                <input type="number" class="form-control" id="valor" name="valor" step="0.01" required value="<?php echo htmlspecialchars($valor); ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="status">Status</label>
                <select class="form-control" id="status" name="status">
                    <option value="pendente" <?php echo $status === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                    <option value="pago" <?php echo $status === 'pago' ? 'selected' : ''; ?>>Pago</option>
                    <option value="atrasado" <?php echo $status === 'atrasado' ? 'selected' : ''; ?>>Atrasado</option>
                    <option value="cancelado" <?php echo $status === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>
        </div>
    </div>
    <div class="form-group">
        <label for="data_vencimento">Data Vencimento *</label>
        <input type="date" class="form-control" id="data_vencimento" name="data_vencimento" required value="<?php echo htmlspecialchars($data_vencimento); ?>">
    </div>
    <div class="form-group">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Pagamento</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
    </div>
</form>
