<?php
// ============================================================
// PERFIL - MEUS DADOS
// ============================================================

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

verificarAutenticacao();

$titulo = 'Meu Perfil';
$usuario_id = $_SESSION['user_id'];

// Obter dados do usuário
try {
    $stmt = $conn->prepare('SELECT * FROM usuarios WHERE id = ?');
    $stmt->execute([$usuario_id]);
    $usuario = $stmt->fetch();
} catch (Exception $e) {
    $usuario = [];
}

// Processar alteração de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha_atual = $_POST['senha_atual'] ?? '';
    $senha_nova = $_POST['senha_nova'] ?? '';
    $senha_confirma = $_POST['senha_confirma'] ?? '';
    
    if (hash('sha256', $senha_atual) !== $usuario['senha']) {
        $_SESSION['mensagem'] = 'Senha atual incorreta!';
        $_SESSION['mensagem_tipo'] = 'erro';
    } elseif ($senha_nova !== $senha_confirma) {
        $_SESSION['mensagem'] = 'As senhas não conferem!';
        $_SESSION['mensagem_tipo'] = 'erro';
    } elseif (strlen($senha_nova) < 6) {
        $_SESSION['mensagem'] = 'A senha deve ter no mínimo 6 caracteres!';
        $_SESSION['mensagem_tipo'] = 'erro';
    } else {
        try {
            $stmt = $conn->prepare('UPDATE usuarios SET senha = ? WHERE id = ?');
            $stmt->execute([hash('sha256', $senha_nova), $usuario_id]);
            $_SESSION['mensagem'] = 'Senha alterada com sucesso!';
            $_SESSION['mensagem_tipo'] = 'sucesso';
        } catch (Exception $e) {
            $_SESSION['mensagem'] = 'Erro ao alterar senha!';
            $_SESSION['mensagem_tipo'] = 'erro';
        }
    }
    header('Location: /SystemContracts/perfil.php');
    exit;
}
?>
<?php include __DIR__ . '/includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-user-circle text-primary mr-2"></i> Meu Perfil</h1>
        <p class="text-muted mb-0">Visualize seus dados e gerencie sua segurança.</p>
    </div>
    <div>
        <a href="/SystemContracts/dashboard.php" class="btn btn-light border shadow-sm">
            <i class="fas fa-arrow-left fa-sm mr-1"></i> Voltar
        </a>
    </div>
</div>

<?php echo exibirMensagem(); ?>

<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-white">
                <i class="fas fa-info-circle text-primary"></i> Informações da Conta
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small uppercase font-weight-bold">Nome Completo</label>
                    <p class="h6"><?php echo $usuario['nome'] ?? ''; ?></p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small uppercase font-weight-bold">E-mail</label>
                    <p class="h6"><?php echo $usuario['email'] ?? ''; ?></p>
                </div>
                <div class="mb-3">
                    <label class="text-muted small uppercase font-weight-bold">Nível de Acesso</label>
                    <div>
                        <?php $perfil_class = ($usuario['perfil'] === 'admin') ? 'bg-accent-soft text-accent' : 'bg-info-soft text-info'; ?>
                        <span class="badge <?php echo $perfil_class; ?>">
                            <?php echo $usuario['perfil'] === 'admin' ? 'Administrador' : 'Usuário'; ?>
                        </span>
                    </div>
                </div>
                <div class="mb-0">
                    <label class="text-muted small uppercase font-weight-bold">Status do Usuário</label>
                    <div>
                        <?php if ($usuario['ativo']): ?>
                            <span class="badge bg-success-soft text-success">Ativo</span>
                        <?php else: ?>
                            <span class="badge bg-danger-soft text-danger">Inativo</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white">
                <i class="fas fa-lock text-warning"></i> Segurança e Senha
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="senha_atual">Senha Atual</label>
                                <input type="password" class="form-control bg-light" id="senha_atual" name="senha_atual" placeholder="Confirme sua senha atual" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="senha_nova">Nova Senha</label>
                                <input type="password" class="form-control" id="senha_nova" name="senha_nova" placeholder="Mínimo 6 caracteres" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="senha_confirma">Confirmar Nova Senha</label>
                                <input type="password" class="form-control" id="senha_confirma" name="senha_confirma" placeholder="Repita a nova senha" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key mr-1"></i> Atualizar Senha
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
