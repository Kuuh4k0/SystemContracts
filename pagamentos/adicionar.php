<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
verificarAutenticacao();
$titulo = 'Novo Pagamento';
$erro = '';
$clientes = obterClientes();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = $_POST['cliente_id'] ?? '';
    $contrato_id = $_POST['contrato_id'] ?? null;
    $tipo = $_POST['tipo'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $valor = $_POST['valor'] ?? '';
    $data_vencimento = $_POST['data_vencimento'] ?? '';
    $status = $_POST['status'] ?? 'pendente';
    
    if (empty($cliente_id) || empty($valor) || empty($data_vencimento)) {
        $erro = 'Preencha os campos obrigatórios!';
    } else {
        try {
            $stmt = $conn->prepare('INSERT INTO pagamentos (cliente_id, contrato_id, tipo, descricao, valor, data_vencimento, status, criado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$cliente_id, $contrato_id ?: null, $tipo, $descricao, $valor, $data_vencimento, $status, $_SESSION['user_id']]);
                $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_POST['ajax']) && $_POST['ajax'] == '1');
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Pagamento adicionado com sucesso!', 'redirect' => '/SystemContracts/pagamentos/listar.php']);
                    exit;
                }
                $_SESSION['mensagem'] = 'Pagamento adicionado com sucesso!';
                $_SESSION['mensagem_tipo'] = 'sucesso';
                header('Location: /SystemContracts/pagamentos/listar.php');
                exit;
        } catch (Exception $e) {
                $erro = 'Erro ao adicionar pagamento!';
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
<h1 class="mb-4"><i class="fas fa-plus"></i> Novo Pagamento</h1>
<?php if (!empty($erro)): ?><div class="alert alert-danger"><?php echo $erro; ?></div><?php endif; ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><i class="fas fa-credit-card"></i> Dados do Pagamento</div>
            <div class="card-body">
                <?php include __DIR__ . '/form.php'; ?>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
