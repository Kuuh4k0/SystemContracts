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

function quitacao_build_pdf_blocks(array $pagamentos, array $formas, string $clienteNome, string $operador, string $reciboCodigo, string $dataHora, float $valorPago, float $totalSelecionado, float $troco, string $observacoes, bool $compacto = false): array {
    $blocks = [];
    if ($compacto) {
        $blocks[] = ['type' => 'text', 'text' => 'ARISE TECH LTDA', 'size' => 10, 'bold' => true, 'line_gap' => 10];
        $blocks[] = ['type' => 'text', 'text' => 'CNPJ: 00.000.000/0001-00', 'size' => 8, 'line_gap' => 8];
        $blocks[] = ['type' => 'divider'];
        $blocks[] = ['type' => 'text', 'text' => 'Recibo: ' . $reciboCodigo, 'size' => 8, 'bold' => true, 'line_gap' => 9];
        $blocks[] = ['type' => 'text', 'text' => 'Data: ' . $dataHora, 'size' => 8, 'line_gap' => 8];
        $blocks[] = ['type' => 'text', 'text' => 'Cliente: ' . $clienteNome, 'size' => 8, 'line_gap' => 8];
        $blocks[] = ['type' => 'text', 'text' => 'Operador: ' . $operador, 'size' => 8, 'line_gap' => 8];
        $blocks[] = ['type' => 'divider'];
        $blocks[] = ['type' => 'text', 'text' => 'Formas de pagamento', 'size' => 8, 'bold' => true, 'line_gap' => 9];
        foreach ($formas as $forma) {
            $rotulo = ucfirst((string) ($forma['metodo'] ?? 'dinheiro'));
            if (($forma['metodo'] ?? '') === 'cartao') {
                $rotulo .= ' (' . ucfirst((string) ($forma['cartao_tipo'] ?? 'debito')) . ')';
            }
            $blocks[] = ['type' => 'row', 'left' => $rotulo, 'right' => 'R$ ' . number_format((float) ($forma['valor'] ?? 0), 2, ',', '.'), 'size' => 8, 'line_gap' => 10];
        }
        $blocks[] = ['type' => 'divider'];
        $blocks[] = ['type' => 'text', 'text' => 'Total: R$ ' . number_format($totalSelecionado, 2, ',', '.'), 'size' => 9, 'bold' => true, 'line_gap' => 10];
        $blocks[] = ['type' => 'text', 'text' => 'Recebido: R$ ' . number_format($valorPago, 2, ',', '.'), 'size' => 8, 'line_gap' => 8];
        $blocks[] = ['type' => 'text', 'text' => 'Troco: R$ ' . number_format($troco, 2, ',', '.'), 'size' => 8, 'line_gap' => 8];
        $blocks[] = ['type' => 'divider'];
        $blocks[] = ['type' => 'text', 'text' => 'Itens quitados', 'size' => 8, 'bold' => true, 'line_gap' => 9];
        foreach ($pagamentos as $pagamento) {
            $descricao = trim((string) ($pagamento['descricao'] ?? 'Pagamento'));
            $numeroContrato = trim((string) ($pagamento['numero_contrato'] ?? ''));
            $linha = ($numeroContrato ? '[' . $numeroContrato . '] ' : '') . $descricao;
            $blocks[] = ['type' => 'text', 'text' => $linha, 'size' => 8, 'line_gap' => 8];
            $blocks[] = ['type' => 'text', 'text' => 'Venc.: ' . formatarData($pagamento['data_vencimento']) . ' | ' . number_format((float) $pagamento['valor'], 2, ',', '.'), 'size' => 7, 'line_gap' => 7];
        }
        if ($observacoes !== '') {
            $blocks[] = ['type' => 'divider'];
            $blocks[] = ['type' => 'text', 'text' => 'Obs.: ' . $observacoes, 'size' => 7, 'line_gap' => 8];
        }
        $blocks[] = ['type' => 'divider'];
        $blocks[] = ['type' => 'text', 'text' => 'AriseTech - comprovante gerado automaticamente', 'size' => 7, 'line_gap' => 7];
        return $blocks;
    }

    $blocks[] = ['type' => 'section', 'text' => $compacto ? 'RESUMO DO RECEBIMENTO' : 'NOTA DE ENTREGA / QUITAÇÃO'];
    $blocks[] = ['type' => 'box', 'text' => 'Recibo: ' . $reciboCodigo . '    ' . $dataHora, 'height' => $compacto ? 30 : 34, 'size' => $compacto ? 8 : 10, 'bold' => true, 'fill' => [0.96, 0.98, 1]];
    $blocks[] = ['type' => 'kv', 'label' => 'Cliente', 'value' => $clienteNome, 'label_width' => $compacto ? 66 : 88, 'line_gap' => $compacto ? 12 : 15];
    $blocks[] = ['type' => 'kv', 'label' => 'Operador', 'value' => $operador, 'label_width' => $compacto ? 66 : 88, 'line_gap' => $compacto ? 12 : 15];
    $blocks[] = ['type' => 'kv', 'label' => 'Documento', 'value' => $reciboCodigo, 'label_width' => $compacto ? 66 : 88, 'line_gap' => $compacto ? 12 : 15];
    $blocks[] = ['type' => 'kv', 'label' => 'Forma', 'value' => strtoupper(count($formas) === 1 ? ($formas[0]['metodo'] ?? 'dinheiro') : 'misto'), 'label_width' => $compacto ? 66 : 88, 'line_gap' => $compacto ? 12 : 15];
    $blocks[] = ['type' => 'divider'];
    $blocks[] = ['type' => 'section', 'text' => 'ITENS ENTREGUES / QUITADOS'];

    foreach ($formas as $forma) {
        $rotulo = ucfirst((string) ($forma['metodo'] ?? 'dinheiro'));
        if (($forma['metodo'] ?? '') === 'cartao') {
            $rotulo .= ' (' . ucfirst((string) ($forma['cartao_tipo'] ?? 'debito')) . ')';
        }
        $blocks[] = [
            'type' => 'row',
            'left' => $rotulo,
            'right' => 'R$ ' . number_format((float) ($forma['valor'] ?? 0), 2, ',', '.'),
            'size' => $compacto ? 8 : 10,
            'line_gap' => $compacto ? 12 : 14,
            'bold' => true,
        ];
    }

    $blocks[] = ['type' => 'divider'];
    $blocks[] = ['type' => 'section', 'text' => 'TÍTULOS QUITADOS'];

    foreach ($pagamentos as $pagamento) {
        $descricao = trim((string) ($pagamento['descricao'] ?? 'Pagamento'));
        $numeroContrato = trim((string) ($pagamento['numero_contrato'] ?? ''));
        $linha = ($numeroContrato ? '[' . $numeroContrato . '] ' : '') . $descricao;
        $blocks[] = [
            'type' => 'row',
            'left' => $linha,
            'right' => 'R$ ' . number_format((float) $pagamento['valor'], 2, ',', '.'),
            'size' => $compacto ? 8 : 9,
            'line_gap' => $compacto ? 11 : 14,
            'bold' => !$compacto,
        ];
        $blocks[] = [
            'type' => 'text',
            'text' => 'Venc.: ' . formatarData($pagamento['data_vencimento']) . ' | Status: ' . ucfirst((string) ($pagamento['status'] ?? '')),
            'size' => $compacto ? 7 : 8,
            'line_gap' => $compacto ? 8 : 10,
        ];
    }

    if ($observacoes !== '') {
        $blocks[] = ['type' => 'divider'];
        $blocks[] = ['type' => 'section', 'text' => 'OBSERVAÇÕES'];
        $blocks[] = ['type' => 'text', 'text' => 'Obs.: ' . $observacoes, 'size' => $compacto ? 7 : 9, 'line_gap' => $compacto ? 10 : 12];
    }

    if (!$compacto) {
        $blocks[] = ['type' => 'divider'];
        $blocks[] = ['type' => 'section', 'text' => 'RESUMO FINANCEIRO'];
        $blocks[] = ['type' => 'kv', 'label' => 'Valor Recebido', 'value' => 'R$ ' . number_format($valorPago, 2, ',', '.'), 'label_width' => 110, 'line_gap' => 15];
        $blocks[] = ['type' => 'kv', 'label' => 'Total', 'value' => 'R$ ' . number_format($totalSelecionado, 2, ',', '.'), 'label_width' => 110, 'line_gap' => 15];
        $blocks[] = ['type' => 'kv', 'label' => 'Troco', 'value' => 'R$ ' . number_format($troco, 2, ',', '.'), 'label_width' => 110, 'line_gap' => 15];
        $blocks[] = ['type' => 'divider'];
        $blocks[] = ['type' => 'section', 'text' => 'FORMAS DE PAGAMENTO'];
    }

    return $blocks;
}

$blocosA4 = quitacao_build_pdf_blocks($pagamentos, $formas, $clienteNome, $operador, $reciboCodigo, $dataHora, $valorPago, $totalSelecionado, (float) ($recibo['troco'] ?? 0), $observacoes, false);
$blocosTermica = quitacao_build_pdf_blocks($pagamentos, $formas, $clienteNome, $operador, $reciboCodigo, $dataHora, $valorPago, $totalSelecionado, (float) ($recibo['troco'] ?? 0), $observacoes, true);

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
    $drawLines[] = ['type' => 'header', 'text' => 'ARISE TECH LTDA'];
    $drawLines[] = ['type' => 'subtitle', 'text' => 'CUPOM DE QUITAÇÃO'];
    $drawLines[] = ['type' => 'spacer', 'size' => 10];
    $drawLines[] = ['type' => 'text', 'text' => 'Recibo: ' . $reciboCodigo, 'size' => 8, 'bold' => true, 'line_gap' => 10];
    $drawLines[] = ['type' => 'text', 'text' => 'Cliente: ' . $clienteNome, 'size' => 8, 'line_gap' => 8];
    $drawLines[] = ['type' => 'text', 'text' => 'Data: ' . $dataHora, 'size' => 8, 'line_gap' => 8];
    $drawLines[] = ['type' => 'divider'];
    $drawLines[] = ['type' => 'text', 'text' => 'Valor total: R$ ' . number_format($totalSelecionado, 2, ',', '.'), 'size' => 9, 'bold' => true, 'line_gap' => 10];
    $drawLines[] = ['type' => 'text', 'text' => 'Recebido: R$ ' . number_format($valorPago, 2, ',', '.'), 'size' => 8, 'line_gap' => 8];
    $drawLines[] = ['type' => 'text', 'text' => 'Troco: R$ ' . number_format((float) ($recibo['troco'] ?? 0), 2, ',', '.'), 'size' => 8, 'line_gap' => 8];
    $drawLines[] = ['type' => 'divider'];
    $drawLines[] = ['type' => 'text', 'text' => 'Formas:', 'size' => 8, 'bold' => true, 'line_gap' => 9];
    foreach ($formas as $forma) {
        $rotulo = ucfirst((string) ($forma['metodo'] ?? 'dinheiro'));
        if (($forma['metodo'] ?? '') === 'cartao') {
            $rotulo .= ' (' . ucfirst((string) ($forma['cartao_tipo'] ?? 'debito')) . ')';
        }
        $drawLines[] = ['type' => 'row', 'left' => $rotulo, 'right' => 'R$ ' . number_format((float) ($forma['valor'] ?? 0), 2, ',', '.'), 'size' => 8, 'line_gap' => 10];
    }
    $drawLines[] = ['type' => 'divider'];
    $drawLines[] = ['type' => 'text', 'text' => 'Itens quitados:', 'size' => 8, 'bold' => true, 'line_gap' => 9];
    foreach ($pagamentos as $pagamento) {
        $descricao = trim((string) ($pagamento['descricao'] ?? 'Pagamento'));
        $numeroContrato = trim((string) ($pagamento['numero_contrato'] ?? ''));
        $drawLines[] = ['type' => 'text', 'text' => ($numeroContrato ? '[' . $numeroContrato . '] ' : '') . $descricao, 'size' => 7, 'line_gap' => 8];
        $drawLines[] = ['type' => 'text', 'text' => 'Venc.: ' . formatarData($pagamento['data_vencimento']) . ' | ' . number_format((float) $pagamento['valor'], 2, ',', '.'), 'size' => 7, 'line_gap' => 7];
    }
    if ($observacoes !== '') {
        $drawLines[] = ['type' => 'divider'];
        $drawLines[] = ['type' => 'text', 'text' => 'Obs.: ' . $observacoes, 'size' => 7, 'line_gap' => 8];
    }

    $height = $margin * 2 + 110 + count($drawLines) * 10;
    $image = imagecreatetruecolor($width, $height);
    $white = imagecolorallocate($image, 255, 255, 255);
    $light = imagecolorallocate($image, 243, 247, 251);
    $blue = imagecolorallocate($image, 14, 105, 164);
    $dark = imagecolorallocate($image, 20, 33, 43);
    $muted = imagecolorallocate($image, 95, 109, 122);
    $accent = imagecolorallocate($image, 0, 145, 255);
    $border = imagecolorallocate($image, 224, 232, 239);
    $green = imagecolorallocate($image, 20, 150, 105);

    imagefill($image, 0, 0, $light);
    imagefilledrectangle($image, $margin, $margin, $width - $margin, $height - $margin, $white);
    imagefilledrectangle($image, $margin, $margin, $width - $margin, $margin + 92, $blue);
    imagerectangle($image, $margin, $margin, $width - $margin, $height - $margin, $border);

    imagestring($image, 5, $margin + 24, $margin + 18, 'ARISE TECH LTDA', $white);
    imagestring($image, 4, $margin + 24, $margin + 50, 'CUPOM DE QUITAÇÃO | ' . $reciboCodigo, $white);
    imagestring($image, 3, $margin + 24, $margin + 70, $dataHora, $white);

    $y = $margin + 118;
    foreach ($drawLines as $entry) {
        switch ($entry['type']) {
            case 'spacer':
                $y += isset($entry['size']) ? (int) $entry['size'] : 8;
                break;
            case 'header':
                imagestring($image, 5, $margin + 24, $y, $entry['text'], $accent);
                $y += 30;
                break;
            case 'subtitle':
                imagestring($image, 3, $margin + 24, $y, $entry['text'], $muted);
                $y += 20;
                break;
            case 'section':
                imagefilledrectangle($image, $margin + 18, $y - 2, $width - $margin - 18, $y + 22, $light);
                imagestring($image, 4, $margin + 24, $y + 4, $entry['text'], $blue);
                $y += 28;
                break;
            case 'kv':
                imagestring($image, 4, $margin + 24, $y, $entry['label'] . ':', $dark);
                imagestring($image, 4, $margin + 160, $y, $entry['value'], $green);
                $y += 18;
                break;
            case 'row':
                imagestring($image, 4, $margin + 24, $y, $entry['left'], $dark);
                imagestring($image, 4, $width - $margin - 24 - (strlen((string)$entry['right']) * 7), $y, $entry['right'], $blue);
                $y += 18;
                break;
            case 'text':
            default:
                imagestring($image, 3, $margin + 24, $y, $entry['text'], $muted);
                $y += 14;
                break;
        }
    }

    imagefilledrectangle($image, $margin + 18, $height - $margin - 32, $width - $margin - 18, $height - $margin - 10, $light);
    imagestring($image, 3, $margin + 24, $height - $margin - 26, 'AriseTech - comprovante gerado automaticamente', $blue);

    header('Content-Type: image/png');
    header('Content-Disposition: inline; filename="recibo_quitacao_' . $reciboId . '.png"');
    imagepng($image);
    imagedestroy($image);
    exit;
}

if ($formato === 'termico' || $formato === 'thermal') {
    $pageHeight = max(210, 160 + (count($blocosTermica) * 8));
    $pdf = pdf_receipt_make($blocosTermica, 'CUPOM TÉRMICO', [
        'page_width' => 210,
        'page_height' => $pageHeight,
        'font_size' => 7,
        'title_size' => 10,
        'subtitle_size' => 7,
        'margin' => 7,
        'compact' => true,
    ]);
} else {
    $pdf = pdf_receipt_make($blocosA4, 'COMPROVANTE DE QUITAÇÃO', [
        'page_width' => 595,
        'page_height' => 842,
        'font_size' => 11,
        'title_size' => 16,
        'subtitle_size' => 10,
        'margin' => 32,
    ]);
}

header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="cupom_pagamento_' . $reciboId . '.pdf"');
header('Content-Length: ' . strlen($pdf));
echo $pdf;
