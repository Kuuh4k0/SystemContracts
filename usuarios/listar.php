<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
verificarAutenticacao();
verificarAdmin();
$titulo = 'Usuários';
$usuarios = obterUsuarios();
?>
<?php include __DIR__ . '/../includes/header.php'; ?> 
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-tie text-primary mr-2"></i> Usuários</h1>
        <p class="text-muted mb-0">Gerencie os acessos e perfis dos usuários do sistema.</p>
    </div>
    <div>
        <a href="#" data-remote-url="/SystemContracts/usuarios/form.php" data-remote-title="Novo Usuário" class="btn btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50 mr-1"></i> Novo Usuário
        </a>
    </div>
</div>
<?php echo exibirMensagem(); ?>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th class="pl-4">Nome</th>
                    <th>Email</th>
                    <th>Perfil</th>
                    <th>Status</th>
                    <th class="text-right pr-4">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td class="pl-4 font-weight-bold text-dark"><?php echo $usuario['nome']; ?></td>
                    <td class="text-secondary"><?php echo $usuario['email']; ?></td>
                    <td>
                        <?php 
                            $perfil_class = ($usuario['perfil'] === 'admin') ? 'bg-accent-soft text-accent' : 'bg-info-soft text-info';
                        ?>
                        <span class="badge <?php echo $perfil_class; ?>"><?php echo ucfirst($usuario['perfil']); ?></span>
                    </td>
                    <td>
                        <?php if ($usuario['ativo']): ?>
                            <span class="badge bg-success-soft text-success">Ativo</span>
                        <?php else: ?>
                            <span class="badge bg-danger-soft text-danger">Inativo</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-right pr-4">
                        <div class="btn-group">
                            <a href="#" data-remote-url="/SystemContracts/usuarios/editar.php?id=<?php echo $usuario['id']; ?>&ajax=1" data-remote-title="Editar Usuário" class="btn btn-sm btn-light border" title="Editar">
                                <i class="fas fa-edit text-warning"></i>
                            </a>
                            <?php if ($usuario['id'] !== $_SESSION['user_id']): // Não permite deletar o próprio usuário logado ?>
                            <a href="/SystemContracts/usuarios/deletar.php?id=<?php echo $usuario['id']; ?>" data-confirm="Tem certeza que deseja deletar: <?php echo addslashes($usuario['nome']); ?>?" class="btn btn-sm btn-light border text-danger" title="Deletar">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
