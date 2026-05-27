<?php

function pdf_receipt_escape($text) {
    $text = (string) $text;
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    return $text;
}

function pdf_receipt_text_width($text, $fontSize = 10) {
    $text = (string) $text;
    return strlen($text) * ($fontSize * 0.52);
}

function pdf_receipt_make(array $lines, $title = 'Cupom de Pagamento', array $options = []) {
    $pageWidth = isset($options['page_width']) ? (int) $options['page_width'] : 595;
    $pageHeight = isset($options['page_height']) ? (int) $options['page_height'] : 842;
    $margin = isset($options['margin']) ? (int) $options['margin'] : 36;
    $bodySize = isset($options['font_size']) ? (int) $options['font_size'] : 10;
    $titleSize = isset($options['title_size']) ? (int) $options['title_size'] : 16;
    $subtitleSize = isset($options['subtitle_size']) ? (int) $options['subtitle_size'] : 9;
    $lineGap = isset($options['line_gap']) ? (int) $options['line_gap'] : 15;
    $compact = !empty($options['compact']);
    $usableWidth = $pageWidth - ($margin * 2);

    $title = pdf_receipt_escape($title);
    $content = "q\n";

    // Background and page frame.
    $content .= "0.96 0.98 1 rg\n0 0 " . $pageWidth . " " . $pageHeight . " re f\n";
    $content .= "1 1 1 rg\n" . $margin . " " . $margin . " " . ($pageWidth - ($margin * 2)) . " " . ($pageHeight - ($margin * 2)) . " re f\n";

    $cursorY = $pageHeight - $margin - 26;

    $drawText = function($text, $x, $y, $size, $font = 'F1') use (&$content) {
        $text = pdf_receipt_escape($text);
        $content .= "0 0 0 rg\nBT /" . $font . " " . $size . " Tf 1 0 0 1 " . $x . " " . $y . " Tm (" . $text . ") Tj ET\n";
    };

    $drawLine = function($x1, $y1, $x2, $y2, $r = 0.85, $g = 0.88, $b = 0.91) use (&$content) {
        $content .= $r . " " . $g . " " . $b . " RG " . $x1 . " " . $y1 . " m " . $x2 . " " . $y2 . " l S\n";
    };

    $drawBlock = function($yTop, $height, $r, $g, $b) use (&$content, $margin, $pageWidth, $usableWidth) {
        $content .= $r . " " . $g . " " . $b . " rg\n" . $margin . " " . $yTop . " " . $usableWidth . " " . $height . " re f\n";
    };

    $drawSection = function($label, $yTop, $fill = [0.95, 0.97, 0.99]) use (&$drawBlock, &$drawText, $margin, $usableWidth) {
        $drawBlock($yTop, 18, $fill[0], $fill[1], $fill[2]);
        $drawText($label, $margin + 10, $yTop + 5, 9, 'F2');
    };

    $drawKeyValue = function($label, $value, $yTop, $labelWidth = 92) use (&$drawText, $margin, $usableWidth, $bodySize) {
        $drawText($label, $margin + 10, $yTop, $bodySize, 'F2');
        $drawText($value, $margin + 10 + $labelWidth, $yTop, $bodySize, 'F1');
    };

    // Interpret structured or simple lines.
    $normalized = [];
    foreach ($lines as $line) {
        if (is_array($line)) {
            $normalized[] = $line;
        } else {
            $normalized[] = ['type' => 'text', 'text' => (string) $line];
        }
    }

    // Header area without a heavy blue rectangle.
    $content .= "0.06 0.43 0.60 RG\n" . $margin . " " . ($pageHeight - $margin - 16) . " m " . ($pageWidth - $margin) . " " . ($pageHeight - $margin - 16) . " l S\n";
    $content .= "0 0 0 rg\n";
    $drawText('ARISE TECH LTDA', $margin + 8, $pageHeight - $margin - 12, $titleSize, 'F2');
    $drawText($title, $margin + 8, $pageHeight - $margin - 30, $subtitleSize, 'F1');
    $drawText(date('d/m/Y H:i'), $pageWidth - $margin - 94, $pageHeight - $margin - 12, 9, 'F1');

    $cursorY = $pageHeight - $margin - 52;
    $sectionOpen = false;
    foreach ($normalized as $item) {
        $type = $item['type'] ?? 'text';
        if ($type === 'spacer') {
            $cursorY -= !empty($item['size']) ? (int) $item['size'] : 6;
            continue;
        }
        if ($cursorY < ($margin + 20)) {
            break;
        }

        if ($type === 'section') {
            $drawSection((string) ($item['text'] ?? ''), $cursorY - 12, isset($item['fill']) ? $item['fill'] : [0.95, 0.97, 0.99]);
            $cursorY -= $compact ? 20 : 24;
            $sectionOpen = true;
            continue;
        }

        if ($type === 'divider') {
            $drawLine($margin + 8, $cursorY - 4, $pageWidth - $margin - 8, $cursorY - 4);
            $cursorY -= 10;
            continue;
        }

        if ($type === 'kv') {
            $label = (string) ($item['label'] ?? '');
            $value = (string) ($item['value'] ?? '');
            $labelWidth = isset($item['label_width']) ? (int) $item['label_width'] : 92;
            $drawKeyValue($label, $value, $cursorY, $labelWidth);
            $cursorY -= isset($item['line_gap']) ? (int) $item['line_gap'] : $lineGap;
            continue;
        }

        if ($type === 'row') {
            $left = (string) ($item['left'] ?? '');
            $right = (string) ($item['right'] ?? '');
            $fontSize = isset($item['size']) ? (int) $item['size'] : $bodySize;
            $drawText($left, $margin + 10, $cursorY, $fontSize, !empty($item['bold']) ? 'F2' : 'F1');
            $rightWidth = pdf_receipt_text_width($right, $fontSize);
            $drawText($right, $pageWidth - $margin - 10 - $rightWidth, $cursorY, $fontSize, !empty($item['bold']) ? 'F2' : 'F1');
            $cursorY -= isset($item['line_gap']) ? (int) $item['line_gap'] : $lineGap;
            continue;
        }

        if ($type === 'box') {
            $height = isset($item['height']) ? (int) $item['height'] : 28;
            $fill = isset($item['fill']) && is_array($item['fill']) ? $item['fill'] : [0.97, 0.99, 1];
            $content .= $fill[0] . " " . $fill[1] . " " . $fill[2] . " rg\n" . ($margin + 6) . " " . ($cursorY - $height + 8) . " " . ($usableWidth - 12) . " " . $height . " re f\n";
            if (!empty($item['text'])) {
                $drawText((string) $item['text'], $margin + 12, $cursorY - 8, isset($item['size']) ? (int) $item['size'] : $bodySize, !empty($item['bold']) ? 'F2' : 'F1');
            }
            $cursorY -= $height + 4;
            continue;
        }

        $text = (string) ($item['text'] ?? '');
        $size = isset($item['size']) ? (int) $item['size'] : $bodySize;
        $font = !empty($item['bold']) ? 'F2' : 'F1';
        $drawText($text, $margin + 10, $cursorY, $size, $font);
        $cursorY -= isset($item['line_gap']) ? (int) $item['line_gap'] : $lineGap;
    }

    // Footer accent line.
    $drawLine($margin + 10, $margin + 30, $pageWidth - $margin - 10, $margin + 30, 0.06, 0.43, 0.60);
    $drawText('AriseTech - comprovante gerado automaticamente', $margin + 10, $margin + 16, 8, 'F1');

    $content .= "Q\n";

    $objects = [];
    $objects[] = "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj";
    $objects[] = "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj";
    $objects[] = "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 " . $pageWidth . " " . $pageHeight . "] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >> endobj";
    $objects[] = "4 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj";
    $objects[] = "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >> endobj";
    $objects[] = "6 0 obj << /Length " . strlen($content) . " >> stream\n" . $content . "endstream endobj";

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
