<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
verificarAutenticacao();

header('Content-Type: application/json; charset=utf-8');

$cliente_id = isset($_GET['cliente_id']) ? intval($_GET['cliente_id']) : 0;
if (!$cliente_id) {
    echo json_encode(['success' => false, 'message' => 'Cliente inválido.']);
    exit;
}

$dividas = obterDividasCliente($cliente_id);

$items = [];
foreach ($dividas as $d) {
    $items[] = [
        'id' => $d['id'],
        'contrato_id' => $d['contrato_id'],
        'numero_contrato' => $d['numero_contrato'] ?? '',
        'descricao' => $d['descricao'] ?? '',
        'tipo' => $d['tipo'] ?? '',
        'vencimento' => $d['data_vencimento'] ?? '',
        'valor' => number_format($d['valor'], 2, '.', ''),
        'situacao' => $d['status'] ?? '',
    ];
}

echo json_encode(['success' => true, 'data' => $items]);
