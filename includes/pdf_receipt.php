<?php

function pdf_receipt_escape($text) {
    $text = (string) $text;
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    return $text;
}

function pdf_receipt_make(array $lines, $title = 'Cupom de Pagamento') {
    $title = pdf_receipt_escape($title);
    $escapedLines = [];
    foreach ($lines as $line) {
        $escapedLines[] = pdf_receipt_escape($line);
    }

    $content = "BT\n/F1 10 Tf\n50 780 Td\n(" . $title . ") Tj\n";
    $content .= "0 -18 Td\n";
    foreach ($escapedLines as $line) {
        $content .= "(" . $line . ") Tj\n0 -14 Td\n";
    }
    $content .= "ET\n";

    $objects = [];
    $objects[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj";
    $objects[] = "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj";
    $objects[] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >> endobj";
    $objects[] = "4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj";
    $objects[] = "5 0 obj << /Length " . strlen($content) . " >> stream\n" . $content . "endstream endobj";

    $pdf = "%PDF-1.4\n";
    $offsets = [0];
    foreach ($objects as $object) {
        $offsets[] = strlen($pdf);
        $pdf .= $object . "\n";
    }

    $xrefPos = strlen($pdf);
    $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
    $pdf .= "0000000000 65535 f \n";
    for ($i = 1; $i <= count($objects); $i++) {
        $pdf .= str_pad((string)$offsets[$i], 10, '0', STR_PAD_LEFT) . " 00000 n \n";
    }
    $pdf .= "trailer << /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
    $pdf .= "startxref\n" . $xrefPos . "\n%%EOF";

    return $pdf;
}
