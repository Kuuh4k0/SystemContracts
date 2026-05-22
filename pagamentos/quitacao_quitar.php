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
$metodo = isset($_POST['metodo']) ? trim($_POST['metodo']) : 'dinheiro';
$observacoes = isset($_POST['observacoes']) ? trim($_POST['observacoes']) : '';

if (!is_array($ids) || empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'Nenhum pagamento selecionado.']);
    exit;
}

// sanitize ids
$ids = array_map('intval', $ids);

$res = quitarPagamentos($ids, $metodo, $observacoes);

if ($res['success']) {
    echo json_encode(['success' => true, 'message' => 'Pagamentos registrados com sucesso.', 'updated' => $res['updated']]);
} else {
    echo json_encode(['success' => false, 'message' => $res['message'] ?? 'Erro ao processar.']);
}
