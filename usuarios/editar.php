<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
verificarAutenticacao();
verificarAdmin();
$titulo = 'Editar Usuário';
$id = $_GET['id'] ?? null;
$erro = '';
if (!$id) { header('Location: /SystemContracts/usuarios/listar.php'); exit; }

try {
    $stmt = $conn->prepare('SELECT * FROM usuarios WHERE id = ?');
    $stmt->execute([$id]);
    $usuario = $stmt->fetch();
} catch (Exception $e) { $usuario = null; }

if (!$usuario) { header('Location: /SystemContracts/usuarios/listar.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $perfil = $_POST['perfil'] ?? 'usuario';
    $ativo = $_POST['ativo'] ?? 0;
    
    try {
        $stmt = $conn->prepare('UPDATE usuarios SET nome=?, email=?, perfil=?, ativo=? WHERE id=?');
        $stmt->execute([$nome, $email, $perfil, $ativo, $id]);
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_POST['ajax']) && $_POST['ajax'] == '1');
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Usuário atualizado com sucesso!', 'redirect' => '/SystemContracts/usuarios/listar.php']);
                exit;
            }
            $_SESSION['mensagem'] = 'Usuário atualizado com sucesso!';
            $_SESSION['mensagem_tipo'] = 'sucesso';
            header('Location: /SystemContracts/usuarios/listar.php');
            exit;
    } catch (Exception $e) { $erro = 'Erro ao atualizar!'; }
}
// If requested via AJAX, return only the form partial populated
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    $nome = $usuario['nome'];
    $email = $usuario['email'];
    $perfil = $usuario['perfil'];
    $ativo = $usuario['ativo'];
    $form_action = '/SystemContracts/usuarios/editar.php?id=' . urlencode($id);
    $require_password = false;
    $show_active = true;
    $ativo = $usuario['ativo'];
    include __DIR__ . '/form.php';
    exit;
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<h1 class="mb-4"><i class="fas fa-edit"></i> Editar Usuário</h1>
<?php if (!empty($erro)): ?><div class="alert alert-danger"><?php echo $erro; ?></div><?php endif; ?>
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header"><i class="fas fa-user"></i> Dados do Usuário</div>
            <div class="card-body">
                <?php
                    $nome = $usuario['nome'];
                    $email = $usuario['email'];
                    $perfil = $usuario['perfil'];
                    $form_action = '/SystemContracts/usuarios/editar.php?id=' . urlencode($id);
                    include __DIR__ . '/form.php';
                ?>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
