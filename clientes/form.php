<?php
// Partial do formulário de cliente — pode ser chamado diretamente via AJAX
if (!isset($conn)) require_once __DIR__ . '/../config/database.php';
if (!function_exists('obterClientes')) require_once __DIR__ . '/../includes/functions.php';

// Preencher valores padrão
$nome = $nome ?? '';
$email = $email ?? '';
$telefone = $telefone ?? '';
$cpf_cnpj = $cpf_cnpj ?? '';
$endereco = $endereco ?? '';
$cidade = $cidade ?? '';
$estado = $estado ?? '';
$cep = $cep ?? '';
$status = $status ?? 'ativo';
?>
<?php $form_action = $form_action ?? '/SystemContracts/clientes/adicionar.php'; ?>
<form method="POST" action="<?php echo $form_action; ?>">
    <div class="form-group">
        <label for="nome">Nome *</label>
        <input type="text" class="form-control" id="nome" name="nome" required value="<?php echo htmlspecialchars($nome); ?>">
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="text" class="form-control" id="telefone" name="telefone" data-mask="phone" value="<?php echo htmlspecialchars($telefone); ?>">
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="cpf_cnpj">CPF/CNPJ</label>
                <input type="text" class="form-control" id="cpf_cnpj" name="cpf_cnpj" data-mask="cpf" value="<?php echo htmlspecialchars($cpf_cnpj); ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="status">Status</label>
                <select class="form-control" id="status" name="status">
                    <option value="ativo" <?php echo ($status === 'ativo') ? 'selected' : ''; ?>>Ativo</option>
                    <option value="inativo" <?php echo ($status === 'inativo') ? 'selected' : ''; ?>>Inativo</option>
                </select>
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
        <div class="col-md-3">
            <div class="form-group">
                <label for="cep">CEP</label>
                <input type="text" class="form-control" id="cep" name="cep" data-mask="cep" value="<?php echo htmlspecialchars($cep); ?>">
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Cliente</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
    </div>
</form>
