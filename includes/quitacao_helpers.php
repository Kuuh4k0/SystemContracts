<?php
// Minimal Quitação helpers — safe to include when original functions.php is malformed
if (!function_exists('obterClientesComPendencias')) {
    function obterClientesComPendencias() {
        global $conn;
        try {
            $stmt = $conn->query('SELECT c.id, c.nome, COUNT(p.id) AS total_titulos, COALESCE(SUM(p.valor), 0) AS total_devido FROM clientes c INNER JOIN pagamentos p ON p.cliente_id = c.id WHERE p.status IN ("pendente", "atrasado") GROUP BY c.id, c.nome ORDER BY c.nome');
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}

if (!function_exists('obterDividasCliente')) {
    function obterDividasCliente($cliente_id) {
        global $conn;
        try {
            $stmt = $conn->prepare('SELECT p.*, ct.numero_contrato, cc.tipo AS tipo_cobranca, cc.descricao AS descricao_cobranca FROM pagamentos p LEFT JOIN contratos ct ON ct.id = p.contrato_id LEFT JOIN contrato_cobrancas cc ON cc.id = p.contrato_cobranca_id WHERE p.cliente_id = ? AND p.status IN ("pendente", "atrasado") ORDER BY p.data_vencimento ASC, p.id ASC');
            $stmt->execute([$cliente_id]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}

if (!function_exists('quitarPagamentos')) {
    function quitarPagamentos($ids, $metodo = 'caixa', $observacoes = '') {
        global $conn;
        if (empty($ids) || !is_array($ids)) {
            return ['success' => false, 'message' => 'IDs inválidos'];
        }
        try {
            $conn->beginTransaction();
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $sql = 'UPDATE pagamentos SET status = "pago", data_pagamento = CURDATE(), metodo_pagamento = ?, observacoes = CONCAT(COALESCE(observacoes, ""), CASE WHEN observacoes IS NULL OR observacoes = "" THEN "" ELSE " | " END, ?) WHERE id IN (' . $placeholders . ')';
            $stmt = $conn->prepare($sql);
            $params = array_merge([$metodo, $observacoes], array_map('intval', $ids));
            $ok = $stmt->execute($params);
            if (!$ok) {
                $conn->rollBack();
                return ['success' => false, 'message' => 'Falha ao atualizar pagamentos.'];
            }
            $conn->commit();
            return ['success' => true, 'updated' => array_map('intval', $ids)];
        } catch (Exception $e) {
            if ($conn->inTransaction()) $conn->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
