<?php
// ============================================================
// CLIENTES - LISTAR
// ============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

verificarAutenticacao();

$titulo = 'Clientes';
$filtro = $_GET['filtro'] ?? '';
$clientes = obterClientes($filtro);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-users text-primary mr-2"></i> Clientes</h1>
        <p class="text-muted mb-0">Gerencie sua base de clientes e parceiros.</p>
    </div>
    <div>
        <a href="#" data-remote-url="/SystemContracts/clientes/form_step1.php" data-remote-title="Novo Cliente" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Novo Cliente
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
                    <input type="text" class="form-control bg-light border-left-0" name="filtro" placeholder="Buscar por nome, email ou CPF/CNPJ..." value="<?php echo $filtro; ?>">
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
                    <th class="pl-4">Nome</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th>CPF/CNPJ</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($clientes) > 0): ?>
                    <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td class="pl-4 font-weight-bold"><?php echo $cliente['nome']; ?></td>
                        <td class="text-secondary"><?php echo $cliente['email'] ?? '-'; ?></td>
                        <td><?php echo $cliente['telefone'] ?? '-'; ?></td>
                        <td class="text-monospace"><?php echo $cliente['cpf_cnpj'] ?? '-'; ?></td>
                        <td>
                            <?php if ($cliente['status'] === 'ativo'): ?>
                                <span class="badge bg-success-soft text-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge bg-danger-soft text-danger">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-right pr-4">
                            <div class="btn-group">
                                <a href="/SystemContracts/clientes/visualizar.php?id=<?php echo $cliente['id']; ?>" class="btn btn-sm btn-light border" title="Visualizar">
                                    <i class="fas fa-eye text-primary"></i>
                                </a>
                                <a href="#" data-remote-url="/SystemContracts/clientes/editar.php?id=<?php echo $cliente['id']; ?>&ajax=1" data-remote-title="Editar Cliente" class="btn btn-sm btn-light border mx-1" title="Editar">
                                    <i class="fas fa-edit text-warning"></i>
                                </a>
                                <a href="/SystemContracts/clientes/deletar.php?id=<?php echo $cliente['id']; ?>" class="btn btn-sm btn-light border text-danger" title="Deletar" data-confirm="Tem certeza que deseja deletar: <?php echo addslashes($cliente['nome']); ?>?">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted">Nenhum cliente encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
