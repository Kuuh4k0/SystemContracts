<?php
// ============================================================
// CLIENTES - DELETAR
// ============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

verificarAutenticacao();

$id = $_GET['id'] ?? null;

if ($id) {
    try {
        $stmt = $conn->prepare('DELETE FROM clientes WHERE id = ?');
        $stmt->execute([$id]);
        $_SESSION['mensagem'] = 'Cliente deletado com sucesso!';
        $_SESSION['mensagem_tipo'] = 'sucesso';
    } catch (Exception $e) {
        $_SESSION['mensagem'] = 'Erro ao deletar cliente!';
        $_SESSION['mensagem_tipo'] = 'erro';
    }
}

header('Location: /SystemContracts/clientes/listar.php');
exit;
?>
