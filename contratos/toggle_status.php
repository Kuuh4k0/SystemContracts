<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
// Expect AJAX POST: id, status
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit;
}
if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID ausente']);
    exit;
}
$id = (int) $_POST['id'];
$status = (isset($_POST['status']) && $_POST['status'] === 'ativo') ? 'ativo' : 'inativo';
try {
    $stmt = $conn->prepare('UPDATE contratos SET status = ? WHERE id = ?');
    $stmt->execute([$status, $id]);
    echo json_encode(['success' => true, 'status' => $status]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status']);
}
