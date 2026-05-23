<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

verificarAutenticacao();

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: /SystemContracts/produtos/listar.php');
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
    $produto = $stmt->fetch();
} catch (Exception $e) {
    $produto = null;
}

if (!$produto) {
    header('Location: /SystemContracts/produtos/listar.php');
    exit;
}

// Verifica se a requisição é AJAX
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_REQUEST['ajax']) && $_REQUEST['ajax'] == '1');

// Se for uma requisição GET via AJAX, retorna apenas o formulário parcial para o modal
if ($isAjax && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $form_action = '/SystemContracts/produtos/editar.php?id=' . $id;
    include __DIR__ . '/form.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $preco_raw = $_POST['preco'] ?? '0,00';
    
    // Converte o valor mascarado para decimal (ex: "R$ 1.250,50" -> 1250.50)
    $preco = str_replace(['R$', '.', ','], ['', '', '.'], $preco_raw);
    $preco = trim($preco);

    if (empty($nome)) {
        $erro = 'O nome do produto é obrigatório!';
    } elseif (!is_numeric($preco)) {
        $erro = 'O preço informado é inválido!';
    } else {
        try {
            $stmt = $conn->prepare('UPDATE produtos SET nome = ?, descricao = ?, preco = ? WHERE id = ?');
            $stmt->execute([$nome, $descricao, (float)$preco, $id]);

            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => true,
                    'message' => 'Produto atualizado com sucesso!',
                    'redirect' => '/SystemContracts/produtos/listar.php'
                ]);
                exit;
            }

            $_SESSION['mensagem'] = 'Produto atualizado com sucesso!';
            $_SESSION['mensagem_tipo'] = 'sucesso';
            header('Location: /SystemContracts/produtos/listar.php');
            exit;
        } catch (Exception $e) {
            $erro = 'Erro ao atualizar produto: ' . $e->getMessage();
        }
    }

    if ($isAjax && !empty($erro)) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $erro]);
        exit;
    }
}

if (!$isAjax) include __DIR__ . '/../includes/header.php';
?>
<h1 class="mb-4"><i class="fas fa-edit"></i> Editar Produto</h1>
<?php if (!empty($erro)): ?><div class="alert alert-danger"><?php echo $erro; ?></div><?php endif; ?>
<div class="card">
    <div class="card-body">
        <?php 
        $form_action = '/SystemContracts/produtos/editar.php?id=' . $id;
        include __DIR__ . '/form.php'; 
        ?>
    </div>
</div>
<?php if (!$isAjax) include __DIR__ . '/../includes/footer.php'; ?>