<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
verificarAutenticacao();
$titulo = 'Novo Contrato';
$erro = '';
$clientes = obterClientes();

$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_POST['ajax']) && $_POST['ajax'] == '1');

function responderContratoAjax($success, $message, $redirect = null, $httpCode = 200) {
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
    $produto_ids = is_array($cobrancas) ? ($cobrancas['produto_id'] ?? []) : [];
    $descrs = is_array($cobrancas) ? ($cobrancas['descricao'] ?? []) : [];

    if ((!is_numeric($valor_total) || (float)$valor_total <= 0) && !empty($valores)) {
        $totalCalculado = 0;
        foreach ($valores as $valorItem) {
            $valorItem = floatval(str_replace(',', '.', (string)$valorItem));
            $totalCalculado += $valorItem;
        }
        $valor_total = $totalCalculado;
    }
    
    if (empty($cliente_id) || !is_numeric($valor_total) || (float)$valor_total <= 0 || empty($data_inicio)) {
        $erro = 'Preencha os campos obrigatórios!';
        if ($isAjax) {
            responderContratoAjax(false, $erro, null, 422);
        }
    } else {
        try {
            $numero = gerarNumeroContrato();
            // Handle file upload (arquivo_pdf)
            $arquivo_nome = null;
            if (!empty($_FILES['arquivo_pdf']) && $_FILES['arquivo_pdf']['error'] === UPLOAD_ERR_OK) {
                $uploaddir = __DIR__ . '/../uploads/contratos/';
                if (!is_dir($uploaddir)) mkdir($uploaddir, 0755, true);
                $tmp = $_FILES['arquivo_pdf']['tmp_name'];
                $orig = basename($_FILES['arquivo_pdf']['name']);
                $ext = pathinfo($orig, PATHINFO_EXTENSION);
                $arquivo_nome = $numero . '-' . time() . '.' . $ext;
                move_uploaded_file($tmp, $uploaddir . $arquivo_nome);
            }

            $stmt = $conn->prepare('INSERT INTO contratos (cliente_id, numero_contrato, descricao, valor_total, data_inicio, data_fim, status, arquivo_pdf) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$cliente_id, $numero, $descricao, $valor_total, $data_inicio, $data_fim, $status, $arquivo_nome]);
            $contrato_id = $conn->lastInsertId();

            // Process cobrancas if provided
            if (!empty($tipos)) {
                $now = new DateTime($data_inicio ?: 'now');
                for ($i=0; $i < count($tipos); $i++) {
                    $tipo = $tipos[$i] ?? 'servico';
                    $valor = isset($valores[$i]) ? floatval(str_replace(',', '.', $valores[$i])) : 0.0;
                    $prodId = !empty($produto_ids[$i]) ? $produto_ids[$i] : null;
                    $descrCob = $descrs[$i] ?? '';
                    // Determine due date
                    $due = clone $now;
                    if ($tipo === 'momentanea') {
                        $dueDate = $now->format('Y-m-d');
                        $tipo_pag = 'servico';
                    } elseif ($tipo === 'mensal') {
                        $due->modify('+1 month');
                        $dueDate = $due->format('Y-m-d');
                        $tipo_pag = 'mensalidade';
                    } elseif ($tipo === 'trimestral') {
                        $due->modify('+3 months');
                        $dueDate = $due->format('Y-m-d');
                        $tipo_pag = 'mensalidade';
                    } elseif ($tipo === 'anual') {
                        $due->modify('+12 months');
                        $dueDate = $due->format('Y-m-d');
                        $tipo_pag = 'mensalidade';
                    } else {
                        $dueDate = $now->format('Y-m-d');
                        $tipo_pag = 'outro';
                    }

                    // Insert into pagamentos
                    $pstmt = $conn->prepare('INSERT INTO pagamentos (cliente_id, contrato_id, produto_id, tipo, descricao, valor, data_vencimento, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                    $pstmt->execute([$cliente_id, $contrato_id, $prodId, $tipo_pag, $descrCob, $valor, $dueDate, 'pendente']);
                }
            }
            if ($isAjax) {
                responderContratoAjax(true, 'Contrato adicionado com sucesso!', '/SystemContracts/contratos/listar.php');
            }
            $_SESSION['mensagem'] = 'Contrato adicionado com sucesso!';
            $_SESSION['mensagem_tipo'] = 'sucesso';
            header('Location: /SystemContracts/contratos/listar.php');
            exit;
        } catch (Exception $e) {
            $erro = 'Erro ao adicionar contrato!';
            if ($isAjax) {
                responderContratoAjax(false, $erro . ' ' . $e->getMessage(), null, 500);
            }
        }
    }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<h1 class="mb-4"><i class="fas fa-plus"></i> Novo Contrato</h1>
<?php if (!empty($erro)): ?>
<div class="alert alert-danger"><?php echo $erro; ?></div>
<?php endif; ?>
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><i class="fas fa-file-alt"></i> Dados do Contrato</div>
            <div class="card-body">
                <?php include __DIR__ . '/form.php'; ?>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
