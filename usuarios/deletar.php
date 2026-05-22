<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
verificarAutenticacao();
verificarAdmin();
$id = $_GET['id'] ?? null;
if ($id && $id !== $_SESSION['user_id']) {
    try {
        $stmt = $conn->prepare('DELETE FROM usuarios WHERE id = ?');
        $stmt->execute([$id]);
        $_SESSION['mensagem'] = 'Usuário deletado com sucesso!';
        $_SESSION['mensagem_tipo'] = 'sucesso';
    } catch (Exception $e) {
        $_SESSION['mensagem'] = 'Erro ao deletar!';
        $_SESSION['mensagem_tipo'] = 'erro';
    }
}
header('Location: /SystemContracts/usuarios/listar.php');
exit;
?>
