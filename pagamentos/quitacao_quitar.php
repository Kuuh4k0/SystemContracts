<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
verificarAutenticacao();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit;
}

$ids = isset($_POST['ids']) ? $_POST['ids'] : [];
$valor_pago = isset($_POST['valor_pago']) ? (float) $_POST['valor_pago'] : null;
$formasJson = isset($_POST['formas_json']) ? (string) $_POST['formas_json'] : '[]';
$observacoes = isset($_POST['observacoes']) ? trim($_POST['observacoes']) : '';

if (!is_array($ids) || empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'Nenhum pagamento selecionado.']);
    exit;
}

// sanitize ids
$ids = array_map('intval', $ids);

$formas = json_decode($formasJson, true);
if (!is_array($formas)) {
    $formas = [];
}

$stmt = $conn->prepare('SELECT id, cliente_id, valor FROM pagamentos WHERE id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')');
$stmt->execute($ids);
$pagamentosSelecionados = $stmt->fetchAll();

if (empty($pagamentosSelecionados)) {
    echo json_encode(['success' => false, 'message' => 'Pagamentos não encontrados.']);
    exit;
}

$clienteId = (int) ($pagamentosSelecionados[0]['cliente_id'] ?? 0);
$totalSelecionado = 0.0;
foreach ($pagamentosSelecionados as $p) {
    $totalSelecionado += (float) $p['valor'];
}

$valorPago = 0.0;
$somaFormas = 0.0;
$temDinheiro = false;
foreach ($formas as $forma) {
    $valorForma = (float) ($forma['valor'] ?? 0);
    $somaFormas += $valorForma;
    if (($forma['metodo'] ?? '') === 'dinheiro') {
        $temDinheiro = true;
    }
}

$valorPago = $somaFormas;

if (empty($formas)) {
    $formas = [[
        'metodo' => 'dinheiro',
        'valor' => $totalSelecionado,
        'cartao_tipo' => '',
    ]];
    $somaFormas = $totalSelecionado;
    $valorPago = $totalSelecionado;
    $temDinheiro = true;
}

if ($somaFormas + 0.01 < $totalSelecionado) {
    echo json_encode(['success' => false, 'message' => 'A soma das formas de pagamento precisa atingir o total selecionado.']);
    exit;
}

if ($somaFormas > $totalSelecionado + 0.01 && !$temDinheiro) {
    echo json_encode(['success' => false, 'message' => 'Troco só pode ser gerado quando houver pagamento em dinheiro.']);
    exit;
}

$troco = max(0, $valorPago - $totalSelecionado);

$conn->exec("CREATE TABLE IF NOT EXISTS quitacao_recebimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    valor_total DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    valor_recebido DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    troco DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    observacoes TEXT,
    criado_por INT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    INDEX idx_cliente (cliente_id),
    INDEX idx_data (criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$conn->exec("CREATE TABLE IF NOT EXISTS quitacao_recebimento_formas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recebimento_id INT NOT NULL,
    metodo VARCHAR(50) NOT NULL,
    cartao_tipo VARCHAR(20) NULL,
    valor DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recebimento_id) REFERENCES quitacao_recebimentos(id) ON DELETE CASCADE,
    INDEX idx_recebimento (recebimento_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$conn->exec("CREATE TABLE IF NOT EXISTS quitacao_recebimento_pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recebimento_id INT NOT NULL,
    pagamento_id INT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recebimento_id) REFERENCES quitacao_recebimentos(id) ON DELETE CASCADE,
    FOREIGN KEY (pagamento_id) REFERENCES pagamentos(id) ON DELETE CASCADE,
    INDEX idx_recebimento_pg (recebimento_id),
    INDEX idx_pagamento (pagamento_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$conn->beginTransaction();

$stmtReceipt = $conn->prepare('INSERT INTO quitacao_recebimentos (cliente_id, valor_total, valor_recebido, troco, observacoes, criado_por) VALUES (?, ?, ?, ?, ?, ?)');
$stmtReceipt->execute([
    $clienteId,
    $totalSelecionado,
    $valorPago,
    $troco,
    $observacoes,
    $_SESSION['user_id'] ?? null,
]);
$reciboId = (int) $conn->lastInsertId();

$stmtForma = $conn->prepare('INSERT INTO quitacao_recebimento_formas (recebimento_id, metodo, cartao_tipo, valor) VALUES (?, ?, ?, ?)');
foreach ($formas as $forma) {
    $stmtForma->execute([
        $reciboId,
        trim((string) ($forma['metodo'] ?? 'dinheiro')),
        trim((string) ($forma['cartao_tipo'] ?? '')) ?: null,
        (float) ($forma['valor'] ?? 0),
    ]);
}

$metodosResumo = [];
foreach ($formas as $forma) {
    $rotulo = ucfirst((string) ($forma['metodo'] ?? 'dinheiro'));
    if (($forma['metodo'] ?? '') === 'cartao') {
        $rotulo .= ' (' . ucfirst((string) ($forma['cartao_tipo'] ?? 'debito')) . ')';
    }
    $metodosResumo[] = $rotulo . ' R$ ' . number_format((float) ($forma['valor'] ?? 0), 2, ',', '.');
}
$stmtLink = $conn->prepare('INSERT INTO quitacao_recebimento_pagamentos (recebimento_id, pagamento_id) VALUES (?, ?)');
foreach ($ids as $id) {
    $stmtLink->execute([$reciboId, $id]);
}

$placeholders = implode(',', array_fill(0, count($ids), '?'));
$sqlUpdate = 'UPDATE pagamentos SET status = "pago", data_pagamento = CURDATE(), metodo_pagamento = ?, observacoes = CONCAT(COALESCE(observacoes, ""), CASE WHEN observacoes IS NULL OR observacoes = "" THEN "" ELSE " | " END, ?) WHERE id IN (' . $placeholders . ')';
$stmtUpdate = $conn->prepare($sqlUpdate);
$observacaoFinal = trim('Pagamento dividido: ' . implode(' / ', $metodosResumo) . ($observacoes ? ' | ' . $observacoes : '') . ' | Valor recebido: R$ ' . number_format($valorPago, 2, ',', '.'));
$params = array_merge(['misto', $observacaoFinal], $ids);
$ok = $stmtUpdate->execute($params);

if (!$ok) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Falha ao atualizar pagamentos.']);
    exit;
}

$conn->commit();

echo json_encode([
    'success' => true,
    'message' => 'Pagamentos registrados com sucesso.',
    'updated' => $ids,
    'pdf_url' => '/SystemContracts/pagamentos/quitacao_recibo.php?recibo_id=' . $reciboId . '&formato=termico',
    'pdf_url_a4' => '/SystemContracts/pagamentos/quitacao_recibo.php?recibo_id=' . $reciboId . '&formato=a4',
    'pdf_url_termico' => '/SystemContracts/pagamentos/quitacao_recibo.php?recibo_id=' . $reciboId . '&formato=termico',
    'image_url' => '/SystemContracts/pagamentos/quitacao_recibo.php?recibo_id=' . $reciboId . '&formato=png',
]);
