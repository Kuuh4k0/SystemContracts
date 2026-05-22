<?php
// Partial do formulário - etapa 1 (dados do cliente)
if (!isset($conn)) require_once __DIR__ . '/../config/database.php';
if (!function_exists('obterClientes')) require_once __DIR__ . '/../includes/functions.php';

$nome = $nome ?? '';
$email = $email ?? '';
$telefone = $telefone ?? '';
$cpf_cnpj = $cpf_cnpj ?? '';
$status = $status ?? 'ativo';
?>
<form id="clienteStep1Form" class="needs-validation" novalidate>
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

    <div class="form-group text-right">
        <button type="button" id="clienteStep1Next" class="btn btn-primary"><i class="fas fa-arrow-right"></i> Próximo</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
    </div>
</form>
