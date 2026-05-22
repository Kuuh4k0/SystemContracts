<?php
// ============================================================
// CLIENTES - EDITAR
// ============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

verificarAutenticacao();

$titulo = 'Editar Cliente';
$id = $_GET['id'] ?? null;
$erro = '';

if (!$id) {
    header('Location: /SystemContracts/clientes/listar.php');
    exit;
}

$cliente = obterCliente($id);
if (!$cliente) {
    header('Location: /SystemContracts/clientes/listar.php');
    exit;
}

// If requested via AJAX, return only the form partial populated
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    // populate variables expected by form.php
    $nome = $cliente['nome'];
    $email = $cliente['email'] ?? '';
    $telefone = $cliente['telefone'] ?? '';
    $cpf_cnpj = $cliente['cpf_cnpj'] ?? '';
    $endereco = $cliente['endereco'] ?? '';
    $cidade = $cliente['cidade'] ?? '';
    $estado = $cliente['estado'] ?? '';
    $cep = $cliente['cep'] ?? '';
    $status = $cliente['status'] ?? 'ativo';
    $form_action = '/SystemContracts/clientes/editar.php?id=' . urlencode($id);
    include __DIR__ . '/form.php';
    exit;
}

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
            $stmt = $conn->prepare('UPDATE clientes SET nome=?, email=?, telefone=?, cpf_cnpj=?, endereco=?, cidade=?, estado=?, cep=?, status=? WHERE id=?');
            $stmt->execute([$nome, $email, $telefone, $cpf_cnpj, $endereco, $cidade, $estado, $cep, $status, $id]);
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_POST['ajax']) && $_POST['ajax'] == '1');
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Cliente atualizado com sucesso!', 'redirect' => '/SystemContracts/clientes/visualizar.php?id=' . $id]);
                exit;
            }
            $_SESSION['mensagem'] = 'Cliente atualizado com sucesso!';
            $_SESSION['mensagem_tipo'] = 'sucesso';
            header('Location: /SystemContracts/clientes/visualizar.php?id=' . $id);
            exit;
        } catch (Exception $e) {
            $erro = 'Erro ao atualizar cliente!';
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

<h1 class="mb-4"><i class="fas fa-edit"></i> Editar Cliente</h1>

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
                <?php
                    // provide variables for form partial
                    $nome = $cliente['nome'];
                    $email = $cliente['email'] ?? '';
                    $telefone = $cliente['telefone'] ?? '';
                    $cpf_cnpj = $cliente['cpf_cnpj'] ?? '';
                    $endereco = $cliente['endereco'] ?? '';
                    $cidade = $cliente['cidade'] ?? '';
                    $estado = $cliente['estado'] ?? '';
                    $cep = $cliente['cep'] ?? '';
                    $status = $cliente['status'] ?? 'ativo';
                    $form_action = '/SystemContracts/clientes/editar.php?id=' . urlencode($id);
                    include __DIR__ . '/form.php';
                ?>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
