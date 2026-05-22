<?php
// Partial do formulário - etapa 2 (endereço / logradouro)
if (!isset($conn)) require_once __DIR__ . '/../config/database.php';
if (!function_exists('obterClientes')) require_once __DIR__ . '/../includes/functions.php';

$endereco = $endereco ?? '';
$cidade = $cidade ?? '';
$estado = $estado ?? '';
$cep = $cep ?? '';
?>
<form id="clienteStep2Form" method="POST" action="/SystemContracts/clientes/adicionar.php">
    <div class="form-group">
        <label for="cep">CEP</label>
        <div class="input-group">
            <input type="text" class="form-control" id="cep" name="cep" data-mask="cep" value="<?php echo htmlspecialchars($cep); ?>">
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-secondary" id="buscarCepBtn">Buscar CEP</button>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="endereco">Endereço</label>
        <input type="text" class="form-control" id="endereco" name="endereco" value="<?php echo htmlspecialchars($endereco); ?>">
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="cidade">Cidade</label>
                <input type="text" class="form-control" id="cidade" name="cidade" value="<?php echo htmlspecialchars($cidade); ?>">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="estado">Estado</label>
                <input type="text" class="form-control" id="estado" name="estado" maxlength="2" value="<?php echo htmlspecialchars($estado); ?>">
            </div>
        </div>
        <div class="col-md-3 text-right align-self-end">
            <button type="button" id="clienteStep2Back" class="btn btn-light mr-2">&larr; Voltar</button>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Cliente</button>
        </div>
    </div>
</form>
