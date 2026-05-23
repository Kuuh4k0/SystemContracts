<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
verificarAutenticacao();

// Processar exclusão se solicitado via URL (?action=delete&id=X)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_excluir = (int)$_GET['id'];
    try {
        // Validação de segurança: Verifica se o produto está vinculado a alguma cobrança ou pagamento
        $stmtCheck = $conn->prepare("SELECT (SELECT COUNT(*) FROM contrato_cobrancas WHERE produto_id = ?) + (SELECT COUNT(*) FROM pagamentos WHERE produto_id = ?)");
        $stmtCheck->execute([$id_excluir, $id_excluir]);
        
        if ($stmtCheck->fetchColumn() > 0) {
            redirecionarComMensagem('listar.php', 'erro', 'Este produto não pode ser excluído pois está vinculado a contratos ou pagamentos existentes.');
        }

        $stmt = $conn->prepare("DELETE FROM produtos WHERE id = ?");
        $stmt->execute([$id_excluir]);
        
        redirecionarComMensagem('listar.php', 'sucesso', 'Produto excluído com sucesso!');
    } catch (Exception $e) {
        redirecionarComMensagem('listar.php', 'erro', 'Erro ao excluir produto: ' . $e->getMessage());
    }
}

$titulo = 'Produtos';

$filtro = $_GET['filtro'] ?? '';
$query = "SELECT * FROM produtos WHERE 1=1";
$params = [];

if (!empty($filtro)) {
    $query .= " AND (nome LIKE ? OR descricao LIKE ?)";
    $params[] = "%$filtro%";
    $params[] = "%$filtro%";
}

$query .= " ORDER BY nome ASC";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$produtos = $stmt->fetchAll();
?>
<?php include __DIR__ . '/../includes/header.php'; ?>
<?php echo exibirMensagem(); ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-box"></i> Produtos</h1>
    <a href="#" data-remote-url="/SystemContracts/produtos/adicionar.php?ajax=1" data-remote-title="Novo Produto" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus"></i> Novo Produto
    </a>
</div>

<div class="card">
    <div class="card-header bg-white py-3">
        <form method="GET" action="" class="row align-items-center">
            <div class="col-md-10">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
                    </div>
                    <input type="text" class="form-control bg-light border-left-0" name="filtro" placeholder="Buscar por nome ou descrição..." value="<?php echo htmlspecialchars($filtro); ?>">
                </div>
            </div>
            <div class="col-md-2 text-right">
                <button class="btn btn-outline-primary btn-block" type="submit">Filtrar</button>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Preço</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($produtos)): ?>
                    <tr><td colspan="5" class="text-center text-muted">Nenhum produto cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($produtos as $p): ?>
                        <tr>
                            <td><?php echo $p['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($p['nome']); ?></strong></td>
                            <td><small><?php echo htmlspecialchars($p['descricao']); ?></small></td>
                            <td>R$ <?php echo number_format($p['preco'], 2, ',', '.'); ?></td>
                            <td class="text-right">
                                <a href="#" data-remote-url="/SystemContracts/produtos/editar.php?id=<?php echo $p['id']; ?>&ajax=1" data-remote-title="Editar Produto" class="btn btn-sm btn-info" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-danger" onclick="confirmarExclusao(<?php echo $p['id']; ?>, '<?php echo addslashes($p['nome']); ?>')" title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>