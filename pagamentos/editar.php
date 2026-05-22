<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
verificarAutenticacao();
$titulo = 'Editar Pagamento';
$id = $_GET['id'] ?? null;
$erro = '';
if (!$id) { header('Location: /SystemContracts/pagamentos/listar.php'); exit; }

try {
    $stmt = $conn->prepare('SELECT * FROM pagamentos WHERE id = ?');
    $stmt->execute([$id]);
    $pagamento = $stmt->fetch();
} catch (Exception $e) { $pagamento = null; }

if (!$pagamento) { header('Location: /SystemContracts/pagamentos/listar.php'); exit; }

$clientes = obterClientes();

// If requested via AJAX, return only the form partial populated
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    $tipo = $pagamento['tipo'] ?? '';
    $descricao = $pagamento['descricao'] ?? '';
    $valor = $pagamento['valor'] ?? '';
    $data_vencimento = $pagamento['data_vencimento'] ?? '';
    $status = $pagamento['status'] ?? 'pendente';
    $clienteSelecionado = $pagamento['cliente_id'] ?? '';
    $form_action = '/SystemContracts/pagamentos/editar.php?id=' . urlencode($id);
    include __DIR__ . '/form.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = $_POST['cliente_id'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $valor = $_POST['valor'] ?? '';
    $data_vencimento = $_POST['data_vencimento'] ?? '';
    $status = $_POST['status'] ?? 'pendente';
    
    try {
        $stmt = $conn->prepare('UPDATE pagamentos SET cliente_id=?, tipo=?, descricao=?, valor=?, data_vencimento=?, status=? WHERE id=?');
        $stmt->execute([$cliente_id, $tipo, $descricao, $valor, $data_vencimento, $status, $id]);
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_POST['ajax']) && $_POST['ajax'] == '1');
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Pagamento atualizado com sucesso!', 'redirect' => '/SystemContracts/pagamentos/listar.php']);
                exit;
            }
            $_SESSION['mensagem'] = 'Pagamento atualizado com sucesso!';
            $_SESSION['mensagem_tipo'] = 'sucesso';
            header('Location: /SystemContracts/pagamentos/listar.php');
            exit;
    } catch (Exception $e) { $erro = 'Erro ao atualizar!'; }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<h1 class="mb-4"><i class="fas fa-edit"></i> Editar Pagamento</h1>
<?php if (!empty($erro)): ?><div class="alert alert-danger"><?php echo $erro; ?></div><?php endif; ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><i class="fas fa-credit-card"></i> Dados do Pagamento</div>
            <div class="card-body">
                <?php
                    $tipo = $pagamento['tipo'] ?? '';
                    $descricao = $pagamento['descricao'] ?? '';
                    $valor = $pagamento['valor'] ?? '';
                    $data_vencimento = $pagamento['data_vencimento'] ?? '';
                    $status = $pagamento['status'] ?? 'pendente';
                    $clienteSelecionado = $pagamento['cliente_id'] ?? '';
                    $form_action = '/SystemContracts/pagamentos/editar.php?id=' . urlencode($id);
                    include __DIR__ . '/form.php';
                ?>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
