<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
verificarAutenticacao();
$titulo = 'Editar Contrato';
$id = $_GET['id'] ?? null;
$erro = '';
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_POST['ajax']) && $_POST['ajax'] == '1');

function responderContratoAjaxEdicao($success, $message, $redirect = null, $httpCode = 200) {
    if (!headers_sent()) {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
    }
    $payload = ['success' => (bool)$success, 'message' => $message];
    if ($redirect) {
        $payload['redirect'] = $redirect;
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}
if (!$id) { header('Location: /SystemContracts/contratos/listar.php'); exit; }

try {
    $stmt = $conn->prepare('SELECT * FROM contratos WHERE id = ?');
    $stmt->execute([$id]);
    $contrato = $stmt->fetch();
} catch (Exception $e) { $contrato = null; }

if (!$contrato) { header('Location: /SystemContracts/contratos/listar.php'); exit; }

// If requested via AJAX, return only the form partial populated
if (isset($_GET['ajax']) && $_GET['ajax'] == '1') {
    $clientes = obterClientes();
    $descricao = $contrato['descricao'] ?? '';
    $valor_total = $contrato['valor_total'] ?? '';
    $data_inicio = $contrato['data_inicio'] ?? '';
    $data_fim = $contrato['data_fim'] ?? '';
    $status = $contrato['status'] ?? 'ativo';
    $clienteSelecionado = $contrato['cliente_id'] ?? '';
    $arquivoPdfAtual = $contrato['arquivo_pdf'] ?? '';
    $cobrancasExistentes = obterCobrancasContrato($id, true);
    $form_action = '/SystemContracts/contratos/editar.php?id=' . urlencode($id);
    include __DIR__ . '/form.php';
    exit;
}

$clientes = obterClientes();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = $_POST['cliente_id'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $valor_total = $_POST['valor_total'] ?? '';
    $data_inicio = $_POST['data_inicio'] ?? '';
    $data_fim = $_POST['data_fim'] ?? '';
    $status = $_POST['status'] ?? 'ativo';

    $cobrancas = $_POST['cobrancas'] ?? null;
    $tipos = is_array($cobrancas) ? ($cobrancas['tipo'] ?? []) : [];
    $valores = is_array($cobrancas) ? ($cobrancas['valor'] ?? []) : [];
    $descrs = is_array($cobrancas) ? ($cobrancas['descricao'] ?? []) : [];

    if ((!is_numeric($valor_total) || (float)$valor_total <= 0) && !empty($valores)) {
        $totalCalculado = 0;
        foreach ($valores as $valorItem) {
            $totalCalculado += floatval(str_replace(',', '.', (string)$valorItem));
        }
        $valor_total = $totalCalculado;
    }
    
    if (empty($cliente_id) || !is_numeric($valor_total) || (float)$valor_total <= 0 || empty($data_inicio)) {
        $erro = 'Preencha os campos obrigatórios!';
        if ($isAjax) {
            responderContratoAjaxEdicao(false, $erro, null, 422);
        }
    } else {
        try {
            // Handle file upload update
            $arquivo_nome = $contrato['arquivo_pdf'];
            if (!empty($_FILES['arquivo_pdf']) && $_FILES['arquivo_pdf']['error'] === UPLOAD_ERR_OK) {
                $uploaddir = __DIR__ . '/../uploads/contratos/';
                if (!is_dir($uploaddir)) mkdir($uploaddir, 0755, true);
                $tmp = $_FILES['arquivo_pdf']['tmp_name'];
                $orig = basename($_FILES['arquivo_pdf']['name']);
                $ext = pathinfo($orig, PATHINFO_EXTENSION);
                $novo = ($contrato['numero_contrato'] ?? 'contrato') . '-' . time() . '.' . $ext;
                move_uploaded_file($tmp, $uploaddir . $novo);
                $arquivo_nome = $novo;
            }

            $stmt = $conn->prepare('UPDATE contratos SET cliente_id=?, descricao=?, valor_total=?, data_inicio=?, data_fim=?, status=?, arquivo_pdf=? WHERE id=?');
            $stmt->execute([$cliente_id, $descricao, $valor_total, $data_inicio, $data_fim, $status, $arquivo_nome, $id]);

            if (is_array($cobrancas) && (!empty($tipos) || !empty($valores) || !empty($descrs))) {
                salvarCobrancasContrato($id, $cobrancas);
                gerarPagamentosContrato($id, true);
            }

            if ($isAjax) {
                responderContratoAjaxEdicao(true, 'Contrato atualizado com sucesso!', '/SystemContracts/contratos/listar.php');
            }
            $_SESSION['mensagem'] = 'Contrato atualizado com sucesso!';
            $_SESSION['mensagem_tipo'] = 'sucesso';
            header('Location: /SystemContracts/contratos/listar.php');
            exit;
        } catch (Exception $e) {
            $erro = 'Erro ao atualizar!';
            if ($isAjax) {
                responderContratoAjaxEdicao(false, $erro . ' ' . $e->getMessage(), null, 500);
            }
        }
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<h1 class="mb-4"><i class="fas fa-edit"></i> Editar Contrato</h1>
<?php if (!empty($erro)): ?><div class="alert alert-danger"><?php echo $erro; ?></div><?php endif; ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><i class="fas fa-file-alt"></i> Dados do Contrato</div>
            <div class="card-body">
                <?php
                    $clientes = obterClientes();
                    $descricao = $contrato['descricao'] ?? '';
                    $valor_total = $contrato['valor_total'] ?? '';
                    $data_inicio = $contrato['data_inicio'] ?? '';
                    $data_fim = $contrato['data_fim'] ?? '';
                    $status = $contrato['status'] ?? 'ativo';
                    $clienteSelecionado = $contrato['cliente_id'] ?? '';
                    $arquivoPdfAtual = $contrato['arquivo_pdf'] ?? '';
                    $cobrancasExistentes = obterCobrancasContrato($id, true);
                    $form_action = '/SystemContracts/contratos/editar.php?id=' . urlencode($id);
                    include __DIR__ . '/form.php';
                ?>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
