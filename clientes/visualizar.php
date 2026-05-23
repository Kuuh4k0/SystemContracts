<?php
// ============================================================
// CLIENTES - VISUALIZAR
// ============================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

verificarAutenticacao();

$titulo = 'Visualizar Cliente';
$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: /SystemContracts/clientes/listar.php');
    exit;
}

$cliente = obterCliente($id);
if (!$cliente) {
    header('Location: /SystemContracts/clientes/listar.php');
    exit;
}

$contratos = obterContratos($id);
$pagamentos = obterPagamentos($id);
// obter recibos/quitacoes do cliente
$stmtRec = $conn->prepare('SELECT * FROM quitacao_recebimentos WHERE cliente_id = ? ORDER BY criado_em DESC');
$stmtRec->execute([$id]);
$recibos = $stmtRec->fetchAll();

include __DIR__ . '/../includes/header.php';

$nome = htmlspecialchars($cliente['nome'] ?? 'Cliente');
$email = htmlspecialchars($cliente['email'] ?? '-');
$telefone = htmlspecialchars($cliente['telefone'] ?? '-');
$cpfCnpj = htmlspecialchars($cliente['cpf_cnpj'] ?? '-');
$endereco = htmlspecialchars($cliente['endereco'] ?? '-');
$cidade = htmlspecialchars($cliente['cidade'] ?? '-');
$estado = htmlspecialchars($cliente['estado'] ?? '-');
$cep = htmlspecialchars($cliente['cep'] ?? '-');
$status = $cliente['status'] ?? 'inativo';
$statusClass = ($status === 'ativo') ? 'badge-success' : 'badge-danger';
$dataCadastro = !empty($cliente['criado_em']) ? date('d/m/Y', strtotime($cliente['criado_em'])) : '—';
?>

<div class="profile-header">
    <div class="profile-left">
        <div class="profile-avatar" aria-hidden="true"><i class="fas fa-user"></i></div>
        <div>
            <div class="profile-title">📇 <?php echo $nome; ?></div>
            <div class="profile-sub">👋 Perfil do cliente e histórico resumido</div>
            <div class="profile-meta">
                <div><i class="fas fa-calendar-alt"></i> Cadastrado em <?php echo $dataCadastro; ?></div>
                <div><i class="fas fa-id-card"></i> <?php echo $cpfCnpj; ?></div>
            </div>
        </div>
    </div>
    <div class="profile-actions">
        <a href="/SystemContracts/clientes/listar.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Voltar
        </a>
    </div>
</div>

<?php echo exibirMensagem(); ?>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-info-circle icon-left"></i> Informações Gerais
            </div>
            <div class="card-body">
                <p><strong>Nome:</strong> <?php echo $nome; ?></p>
                <p><strong>Email:</strong> <?php echo $email; ?></p>
                <p><strong>Telefone:</strong> <?php echo $telefone; ?></p>
                <p><strong>CPF/CNPJ:</strong> <?php echo $cpfCnpj; ?></p>
                <p><strong>Status:</strong> <span class="badge <?php echo $statusClass; ?>"><?php echo ucfirst($status); ?></span></p>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <i class="fas fa-map-marker-alt icon-left"></i> Endereço
            </div>
            <div class="card-body">
                <p><strong>Rua:</strong> <?php echo $endereco; ?></p>
                <p><strong>Cidade:</strong> <?php echo $cidade; ?></p>
                <p><strong>Estado:</strong> <?php echo $estado; ?></p>
                <p><strong>CEP:</strong> <?php echo $cep; ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-1">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs" id="clienteTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="contratos-tab" data-toggle="tab" href="#contratosTab" role="tab" aria-controls="contratosTab" aria-selected="true">Contratos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="pagamentos-tab" data-toggle="tab" href="#pagamentosTab" role="tab" aria-controls="pagamentosTab" aria-selected="false">Pagamentos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="comprovantes-tab" data-toggle="tab" href="#comprovantesTab" role="tab" aria-controls="comprovantesTab" aria-selected="false">Comprovantes</a>
                    </li>
                </ul>
                <div class="tab-content mt-3" id="clienteTabsContent">
                    <div class="tab-pane fade show active" id="contratosTab" role="tabpanel" aria-labelledby="contratos-tab">
                        <?php if (count($contratos) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Número</th>
                                        <th>Valor</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($contratos as $contrato): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($contrato['numero_contrato']); ?></td>
                                        <td><?php echo formatarMoeda($contrato['valor_total']); ?></td>
                                        <td><span class="badge badge-info"><?php echo htmlspecialchars(ucfirst($contrato['status'])); ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">📄 Nenhum contrato encontrado.</p>
                        <?php endif; ?>
                    </div>
                    <div class="tab-pane fade" id="pagamentosTab" role="tabpanel" aria-labelledby="pagamentos-tab">
                        <?php if (count($pagamentos) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Valor</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pagamentos as $pagamento): ?>
                                        <?php
                                        $badge_class = 'badge-secondary';
                                        if ($pagamento['status'] === 'pago') $badge_class = 'badge-success';
                                        if ($pagamento['status'] === 'pendente') $badge_class = 'badge-warning';
                                        if ($pagamento['status'] === 'atrasado') $badge_class = 'badge-danger';
                                        ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(ucfirst($pagamento['tipo'])); ?></td>
                                        <td><?php echo formatarMoeda($pagamento['valor']); ?></td>
                                        <td><span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars(ucfirst($pagamento['status'])); ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">💳 Nenhum pagamento encontrado.</p>
                        <?php endif; ?>
                    </div>
                    <div class="tab-pane fade" id="comprovantesTab" role="tabpanel" aria-labelledby="comprovantes-tab">
                        <?php if (count($recibos) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Recibo</th>
                                            <th>Data</th>
                                            <th>Valor Recebido</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recibos as $r): ?>
                                            <tr>
                                                <td><?php echo 'Q-' . str_pad($r['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                                <td><?php echo !empty($r['criado_em']) ? date('d/m/Y H:i', strtotime($r['criado_em'])) : '-'; ?></td>
                                                <td><?php echo formatarMoeda($r['valor_recebido']); ?></td>
                                                <td>
                                                    <a href="/SystemContracts/pagamentos/quitacao_recibo.php?recibo_id=<?php echo $r['id']; ?>" target="_blank" class="btn btn-sm btn-light border" title="Abrir PDF"><i class="fas fa-file-pdf text-primary"></i></a>
                                                    <a href="/SystemContracts/pagamentos/quitacao_recibo.php?recibo_id=<?php echo $r['id']; ?>&formato=png" target="_blank" class="btn btn-sm btn-light border ml-1" title="Abrir imagem"><i class="fas fa-image text-secondary"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted mb-0">🧾 Nenhum comprovante encontrado.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
