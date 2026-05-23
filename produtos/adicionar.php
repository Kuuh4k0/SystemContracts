<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

verificarAutenticacao();

$titulo = 'Novo Produto';
$erro = '';
$produto = []; // Inicializa variável para evitar avisos no form.php

// Verifica se a requisição é AJAX (utilizado pelo seu script.js nos modais)
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || (isset($_POST['ajax']) && $_POST['ajax'] == '1');

// Se for uma requisição GET via AJAX, retorna apenas o formulário parcial para o modal
if ($isAjax && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $form_action = '/SystemContracts/produtos/adicionar.php';
    include __DIR__ . '/form.php';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $preco_raw = $_POST['preco'] ?? '0,00';
    
    // Converte o valor mascarado (ex: "R$ 1.250,50") para o formato decimal do MySQL (1250.50)
    $preco = str_replace(['R$', '.', ','], ['', '', '.'], $preco_raw);
    $preco = trim($preco);

    if (empty($nome)) {
        $erro = 'O nome do produto é obrigatório!';
    } elseif (!is_numeric($preco)) {
        $erro = 'O preço informado é inválido!';
    } else {
        try {
            $stmt = $conn->prepare('INSERT INTO produtos (nome, descricao, preco, ativo) VALUES (?, ?, ?, 1)');
            $stmt->execute([$nome, $descricao, (float)$preco]);

            $mensagem = 'Produto cadastrado com sucesso!';
            
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => true,
                    'message' => $mensagem,
                    'redirect' => '/SystemContracts/produtos/listar.php'
                ]);
                exit;
            }

            $_SESSION['mensagem'] = $mensagem;
            $_SESSION['mensagem_tipo'] = 'sucesso';
            header('Location: /SystemContracts/produtos/listar.php');
            exit;
        } catch (Exception $e) {
            $erro = 'Erro ao salvar produto: ' . $e->getMessage();
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

<h1 class="mb-4"><i class="fas fa-plus"></i> Novo Produto</h1>

<?php if (!empty($erro)): ?>
    <div class="alert alert-danger"><?php echo $erro; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php 
        $form_action = '/SystemContracts/produtos/adicionar.php';
        include __DIR__ . '/form.php'; 
        ?>
    </div>
</div>

<?php if (!$isAjax) include __DIR__ . '/../includes/footer.php'; ?>