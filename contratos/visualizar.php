<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

verificarAutenticacao();

$titulo = 'Visualizar Contrato';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$id) {
    header('Location: /SystemContracts/contratos/listar.php');
    exit;
}

$stmt = $conn->prepare('SELECT c.*, cl.nome AS cliente_nome FROM contratos c LEFT JOIN clientes cl ON cl.id = c.cliente_id WHERE c.id = ?');
$stmt->execute([$id]);
$contrato = $stmt->fetch();

if (!$contrato) {
    header('Location: /SystemContracts/contratos/listar.php');
    exit;
}

$arquivoPdf = trim((string)($contrato['arquivo_pdf'] ?? ''));
$caminhoFisico = __DIR__ . '/../uploads/contratos/' . $arquivoPdf;
$urlPdf = '/SystemContracts/uploads/contratos/' . rawurlencode($arquivoPdf);
$arquivoExiste = $arquivoPdf !== '' && is_file($caminhoFisico);
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-file-pdf text-danger mr-2"></i> Visualizar Contrato</h1>
        <p class="text-muted mb-0"><?php echo htmlspecialchars($contrato['numero_contrato'] ?? ''); ?> - <?php echo htmlspecialchars($contrato['cliente_nome'] ?? 'Cliente'); ?></p>
    </div>
    <div>
        <a href="/SystemContracts/contratos/listar.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </a>
        <?php if ($arquivoExiste): ?>
            <a href="<?php echo $urlPdf; ?>" class="btn btn-primary" target="_blank" rel="noopener">
                <i class="fas fa-external-link-alt mr-1"></i> Abrir PDF
            </a>
        <?php endif; ?>
    </div>
</div>

<?php echo exibirMensagem(); ?>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <?php if ($arquivoExiste): ?>
            <div style="height:75vh;">
                <iframe
                    src="<?php echo $urlPdf; ?>"
                    title="PDF do contrato"
                    style="width:100%; height:100%; border:0;"
                ></iframe>
            </div>
        <?php else: ?>
            <div class="p-5 text-center">
                <div class="display-4 text-muted mb-3"><i class="fas fa-file-pdf"></i></div>
                <h4 class="mb-2">PDF não encontrado</h4>
                <p class="text-muted mb-4">
                    Este contrato não possui arquivo PDF anexado ou o arquivo foi removido do servidor.
                </p>
                <a href="/SystemContracts/contratos/editar.php?id=<?php echo $id; ?>" class="btn btn-warning">
                    <i class="fas fa-edit mr-1"></i> Editar contrato
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
