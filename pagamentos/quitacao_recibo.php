<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/pdf_receipt.php';

verificarAutenticacao();

$reciboId = isset($_GET['recibo_id']) ? (int) $_GET['recibo_id'] : 0;
$formato = isset($_GET['formato']) ? strtolower((string) $_GET['formato']) : 'pdf';

if (!$reciboId) {
    http_response_code(400);
    echo 'Recibo inválido.';
    exit;
}

$stmt = $conn->prepare('SELECT r.*, c.nome AS cliente_nome, u.nome AS usuario_nome FROM quitacao_recebimentos r LEFT JOIN clientes c ON c.id = r.cliente_id LEFT JOIN usuarios u ON u.id = r.criado_por WHERE r.id = ?');
$stmt->execute([$reciboId]);
$recibo = $stmt->fetch();

if (!$recibo) {
    http_response_code(404);
    echo 'Recibo não encontrado.';
    exit;
}

$stmtFormas = $conn->prepare('SELECT * FROM quitacao_recebimento_formas WHERE recebimento_id = ? ORDER BY id ASC');
$stmtFormas->execute([$reciboId]);
$formas = $stmtFormas->fetchAll();

$stmtPagamentos = $conn->prepare('SELECT p.*, ct.numero_contrato FROM quitacao_recebimento_pagamentos rp INNER JOIN pagamentos p ON p.id = rp.pagamento_id LEFT JOIN contratos ct ON ct.id = p.contrato_id WHERE rp.recebimento_id = ? ORDER BY p.id ASC');
$stmtPagamentos->execute([$reciboId]);
$pagamentos = $stmtPagamentos->fetchAll();

$clienteNome = $recibo['cliente_nome'] ?? 'Cliente';
$metodo = $recibo['valor_total'] > 0 && count($formas) === 1 ? ($formas[0]['metodo'] ?? 'dinheiro') : 'misto';
$valorPago = (float) ($recibo['valor_recebido'] ?? 0);
$observacoes = (string) ($recibo['observacoes'] ?? '');
$totalSelecionado = (float) ($recibo['valor_total'] ?? 0);
$dataHora = date('d/m/Y H:i');
$operador = $recibo['usuario_nome'] ?? ($_SESSION['user_nome'] ?? 'Sistema');
$reciboCodigo = 'Q-' . str_pad((string) $reciboId, 6, '0', STR_PAD_LEFT);

$resumoFormas = [];
$linhas = [];
$linhas[] = 'ARISE TECH LTDA';
$linhas[] = 'CNPJ: 00.000.000/0001-00';
$linhas[] = str_repeat('=', 42);
$linhas[] = 'Recibo: ' . $reciboCodigo;
$linhas[] = 'Data: ' . $dataHora;
$linhas[] = 'Cliente: ' . $clienteNome;
$linhas[] = 'Operador: ' . $operador;
$linhas[] = 'Forma(s) de pagamento: ' . strtoupper($metodo);
$linhas[] = 'VALOR RECEBIDO: R$ ' . number_format($valorPago, 2, ',', '.');
$linhas[] = 'TOTAL DOS ITENS: R$ ' . number_format($totalSelecionado, 2, ',', '.');
$linhas[] = 'Troco: R$ ' . number_format((float) ($recibo['troco'] ?? 0), 2, ',', '.');
$linhas[] = str_repeat('-', 42);

foreach ($formas as $forma) {
    $rotulo = ucfirst((string) ($forma['metodo'] ?? 'dinheiro'));
    if (($forma['metodo'] ?? '') === 'cartao') {
        $rotulo .= ' (' . ucfirst((string) ($forma['cartao_tipo'] ?? 'debito')) . ')';
    }
    $textoForma = $rotulo . ': R$ ' . number_format((float) ($forma['valor'] ?? 0), 2, ',', '.');
    $linhas[] = $textoForma;
    $resumoFormas[] = $textoForma;
}

$linhas[] = str_repeat('-', 42);

foreach ($pagamentos as $pagamento) {
    $descricao = trim((string) ($pagamento['descricao'] ?? 'Pagamento'));
    $numeroContrato = trim((string) ($pagamento['numero_contrato'] ?? ''));
    $linhas[] = ($numeroContrato ? '[' . $numeroContrato . '] ' : '') . $descricao;
    $linhas[] = 'Venc.: ' . formatarData($pagamento['data_vencimento']) . ' | Valor: R$ ' . number_format((float) $pagamento['valor'], 2, ',', '.') . ' | Status: ' . ucfirst((string) ($pagamento['status'] ?? ''));
}

if ($observacoes !== '') {
    $linhas[] = str_repeat('-', 42);
    $linhas[] = 'Obs.: ' . $observacoes;
}

if ($formato === 'png') {
    if (!function_exists('imagecreatetruecolor')) {
        http_response_code(500);
        echo 'A geração de imagem não está disponível neste servidor.';
        exit;
    }

    $width = 1080;
    $margin = 40;
    $lineHeight = 28;
    $font = 5;
    $wrapWidth = 92;

    $drawLines = [];
    $drawLines[] = ['type' => 'header', 'text' => 'RESUMO DO RECEBIMENTO'];
    $drawLines[] = ['type' => 'subtitle', 'text' => 'Emitente: Arise Tech LTDA'];
    $drawLines[] = ['type' => 'spacer'];
    $drawLines[] = ['type' => 'label', 'text' => 'Cliente: ' . $clienteNome];
    $drawLines[] = ['type' => 'label', 'text' => 'Operador: ' . $operador];
    $drawLines[] = ['type' => 'label', 'text' => 'Forma(s): ' . strtoupper($metodo)];
    $drawLines[] = ['type' => 'label', 'text' => 'VALOR RECEBIDO: R$ ' . number_format($valorPago, 2, ',', '.')];
    $drawLines[] = ['type' => 'label', 'text' => 'TOTAL DOS ITENS: R$ ' . number_format($totalSelecionado, 2, ',', '.')];
    $drawLines[] = ['type' => 'label', 'text' => 'Troco: R$ ' . number_format((float) ($recibo['troco'] ?? 0), 2, ',', '.')];
    $drawLines[] = ['type' => 'spacer'];
    $drawLines[] = ['type' => 'section', 'text' => 'Detalhamento das Formas'];

    foreach ($resumoFormas as $formaLinha) {
        foreach (explode("\n", wordwrap($formaLinha, $wrapWidth, "\n", true)) as $parte) {
            $drawLines[] = ['type' => 'text', 'text' => $parte];
        }
    }

    $drawLines[] = ['type' => 'spacer'];
    $drawLines[] = ['type' => 'section', 'text' => 'Títulos / Contratos Quitados'];
    foreach ($pagamentos as $pagamento) {
        $descricao = trim((string) ($pagamento['descricao'] ?? 'Pagamento'));
        $numeroContrato = trim((string) ($pagamento['numero_contrato'] ?? ''));
        $linhaItem = ($numeroContrato ? '[' . $numeroContrato . '] ' : '') . $descricao;
        foreach (explode("\n", wordwrap($linhaItem, $wrapWidth, "\n", true)) as $parte) {
            $drawLines[] = ['type' => 'text', 'text' => $parte];
        }
        $drawLines[] = ['type' => 'text', 'text' => 'Venc.: ' . formatarData($pagamento['data_vencimento']) . ' | Valor: R$ ' . number_format((float) $pagamento['valor'], 2, ',', '.') . ' | Status: ' . ucfirst((string) ($pagamento['status'] ?? ''))];
    }

    if ($observacoes !== '') {
        $drawLines[] = ['type' => 'spacer'];
        $drawLines[] = ['type' => 'section', 'text' => 'Observações'];
        foreach (explode("\n", wordwrap('Obs.: ' . $observacoes, $wrapWidth, "\n", true)) as $parte) {
            $drawLines[] = ['type' => 'text', 'text' => $parte];
        }
    }

    $height = $margin * 2 + 160 + count($drawLines) * $lineHeight;
    $image = imagecreatetruecolor($width, $height);
    $white = imagecolorallocate($image, 255, 255, 255);
    $light = imagecolorallocate($image, 243, 247, 251);
    $blue = imagecolorallocate($image, 14, 105, 164);
    $dark = imagecolorallocate($image, 20, 33, 43);
    $muted = imagecolorallocate($image, 95, 109, 122);
    $accent = imagecolorallocate($image, 0, 145, 255);
    $border = imagecolorallocate($image, 224, 232, 239);

    imagefill($image, 0, 0, $light);
    imagefilledrectangle($image, $margin, $margin, $width - $margin, $height - $margin, $white);
    imagefilledrectangle($image, $margin, $margin, $width - $margin, $margin + 88, $blue);
    imagerectangle($image, $margin, $margin, $width - $margin, $height - $margin, $border);

    imagestring($image, 5, $margin + 24, $margin + 20, 'ARISE TECH LTDA', $white);
    imagestring($image, 4, $margin + 24, $margin + 54, 'Comprovante: ' . $reciboCodigo . '  |  ' . $dataHora, $white);

    $y = $margin + 110;
    foreach ($drawLines as $entry) {
        switch ($entry['type']) {
            case 'spacer':
                $y += 10;
                break;
            case 'header':
                imagestring($image, 5, $margin + 24, $y, $entry['text'], $accent);
                $y += 34;
                break;
            case 'subtitle':
                imagestring($image, 3, $margin + 24, $y, $entry['text'], $muted);
                $y += 26;
                break;
            case 'section':
                imagefilledrectangle($image, $margin + 18, $y - 2, $width - $margin - 18, $y + 24, $light);
                imagestring($image, 4, $margin + 24, $y + 4, $entry['text'], $blue);
                $y += 34;
                break;
            case 'label':
            case 'text':
            default:
                imagestring($image, $font, $margin + 24, $y, $entry['text'], $dark);
                $y += $lineHeight;
                break;
        }
    }

    header('Content-Type: image/png');
    header('Content-Disposition: inline; filename="recibo_quitacao_' . $reciboId . '.png"');
    imagepng($image);
    imagedestroy($image);
    exit;
}

$pdf = pdf_receipt_make($linhas, 'ARISE TECH LTDA - COMPROVANTE');

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="cupom_pagamento_' . $reciboId . '.pdf"');
header('Content-Length: ' . strlen($pdf));
echo $pdf;
