<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
verificarAutenticacao();
verificarAdmin();
$titulo = 'Novo Usuário';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $perfil = $_POST['perfil'] ?? 'usuario';
    
    if (empty($nome) || empty($email) || empty($senha)) {
        $erro = 'Preencha os campos obrigatórios!';
    } elseif (!validarEmail($email)) {
        $erro = 'Email inválido!';
    } elseif (strlen($senha) < 6) {
        $erro = 'Senha deve ter no mínimo 6 caracteres!';
    } else {
        try {
            $stmt = $conn->prepare('INSERT INTO usuarios (nome, email, senha, perfil) VALUES (?, ?, ?, ?)');
            $stmt->execute([$nome, $email, hash('sha256', $senha), $perfil]);
                $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_POST['ajax']) && $_POST['ajax'] == '1');
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Usuário adicionado com sucesso!', 'redirect' => '/SystemContracts/usuarios/listar.php']);
                    exit;
                }
                $_SESSION['mensagem'] = 'Usuário adicionado com sucesso!';
                $_SESSION['mensagem_tipo'] = 'sucesso';
                header('Location: /SystemContracts/usuarios/listar.php');
                exit;
        } catch (Exception $e) {
                $erro = 'Email já cadastrado!';
                $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_POST['ajax']) && $_POST['ajax'] == '1');
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $erro]);
                    exit;
                }
        }
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<h1 class="mb-4"><i class="fas fa-plus"></i> Novo Usuário</h1>
<?php if (!empty($erro)): ?><div class="alert alert-danger"><?php echo $erro; ?></div><?php endif; ?>
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="fas fa-user"></i> Dados do Usuário</div>
            <div class="card-body">
                <?php include __DIR__ . '/form.php'; ?>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
