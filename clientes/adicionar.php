<?php
// ============================================================
// CLIENTES - ADICIONAR
// ============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

verificarAutenticacao();

$titulo = 'Novo Cliente';
$erro = '';
$sucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $cpf_cnpj = $_POST['cpf_cnpj'] ?? '';
    $endereco = $_POST['endereco'] ?? '';
    $cidade = $_POST['cidade'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $cep = $_POST['cep'] ?? '';
    $status = $_POST['status'] ?? 'ativo';
    
    if (empty($nome)) {
        $erro = 'Nome é obrigatório!';
    } elseif (!empty($email) && !validarEmail($email)) {
        $erro = 'Email inválido!';
    } else {
        try {
            $stmt = $conn->prepare('INSERT INTO clientes (nome, email, telefone, cpf_cnpj, endereco, cidade, estado, cep, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$nome, $email, $telefone, $cpf_cnpj, $endereco, $cidade, $estado, $cep, $status]);
            $sucesso = true;
                $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_POST['ajax']) && $_POST['ajax'] == '1');
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Cliente adicionado com sucesso!', 'redirect' => '/SystemContracts/clientes/listar.php']);
                    exit;
                }
                $_SESSION['mensagem'] = 'Cliente adicionado com sucesso!';
                $_SESSION['mensagem_tipo'] = 'sucesso';
                header('Location: /SystemContracts/clientes/listar.php');
                exit;
        } catch (Exception $e) {
                $erro = 'Erro ao adicionar cliente!';
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

<h1 class="mb-4"><i class="fas fa-plus"></i> Novo Cliente</h1>

<?php if (!empty($erro)): ?>
<div class="alert alert-danger" role="alert">
    <i class="fas fa-exclamation-circle"></i> <?php echo $erro; ?>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-user"></i> Dados do Cliente
            </div>
            <div class="card-body">
                <?php include __DIR__ . '/form.php'; ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
