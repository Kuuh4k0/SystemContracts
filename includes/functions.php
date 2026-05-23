<?php
/**
 * FUNÇÕES AUXILIARES DO SISTEMA (corrigido)
 */

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
// AUTENTICAÇÃO
// ============================================================

function verificarAutenticacao() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /SystemContracts/index.php');
        exit;
    }
}

function verificarAdmin() {
    verificarAutenticacao();
    if (!isset($_SESSION['user_perfil']) || $_SESSION['user_perfil'] !== 'admin') {
        header('Location: /SystemContracts/dashboard.php');
        exit;
    }
}

function fazerLogin($email, $senha) {
    global $conn;
    try {
        $stmt = $conn->prepare('SELECT * FROM usuarios WHERE email = ? AND ativo = 1');
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        if ($usuario && hash('sha256', $senha) === $usuario['senha']) {
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['user_nome'] = $usuario['nome'];
            $_SESSION['user_email'] = $usuario['email'];
            $_SESSION['user_perfil'] = $usuario['perfil'];
            return true;
        }
        return false;
    } catch (Exception $e) {
        return false;
    }
}

function fazerLogout() {
    session_destroy();
    header('Location: /SystemContracts/index.php');
    exit;
}

// ============================================================
// FORMATAÇÃO
// ============================================================

function formatarMoeda($valor) {
    return (defined('MOEDA_SIMBOLO') ? MOEDA_SIMBOLO : 'R$') . ' ' . number_format($valor, defined('MOEDA_DECIMAIS') ? MOEDA_DECIMAIS : 2, ',', '.');
}

function formatarData($data) {
    if (empty($data)) return '';
    return date('d/m/Y', strtotime($data));
}

function formatarDataTempo($data) {
    if (empty($data)) return '';
    return date('d/m/Y H:i', strtotime($data));
}

// ============================================================
// VALIDAÇÃO
// ============================================================

function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/is', '', $cpf);
    if (strlen($cpf) !== 11) return false;
    if (preg_match('/(\d)\1{10}/', $cpf)) return false;
    return true;
}

function validarCNPJ($cnpj) {
    $cnpj = preg_replace('/[^0-9]/is', '', $cnpj);
    if (strlen($cnpj) !== 14) return false;
    if (preg_match('/(\d)\1{13}/', $cnpj)) return false;
    return true;
}

// ============================================================
// BANCO DE DADOS
// ============================================================

function obterCliente($id) {
    global $conn;
    try {
        $stmt = $conn->prepare('SELECT * FROM clientes WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}

function obterClientes($filtro = '') {
    global $conn;
    try {
        $sql = 'SELECT * FROM clientes WHERE 1=1';
        if (!empty($filtro)) {
            $sql .= " AND (nome LIKE ? OR email LIKE ? OR cpf_cnpj LIKE ?)";
            $filtro_param = '%' . $filtro . '%';
            $stmt = $conn->prepare($sql);
            $stmt->execute([$filtro_param, $filtro_param, $filtro_param]);
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
        }
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function obterContratos($cliente_id = null) {
    global $conn;
    try {
        if ($cliente_id) {
            $stmt = $conn->prepare('SELECT * FROM contratos WHERE cliente_id = ? ORDER BY criado_em DESC');
            $stmt->execute([$cliente_id]);
        } else {
            $stmt = $conn->query('SELECT * FROM contratos ORDER BY criado_em DESC');
        }
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function obterPagamentos($cliente_id = null, $filtro = '') {
    global $conn;
    try {
        $sql = 'SELECT p.*, c.nome as cliente_nome FROM pagamentos p LEFT JOIN clientes c ON p.cliente_id = c.id WHERE 1=1';
        $params = [];

        if ($cliente_id) {
            $sql .= ' AND p.cliente_id = ?';
            $params[] = $cliente_id;
        }
        if (!empty($filtro)) {
            $sql .= " AND (p.descricao LIKE ? OR c.nome LIKE ? OR p.tipo LIKE ?)";
            $filtro_param = '%' . $filtro . '%';
            $params[] = $filtro_param;
            $params[] = $filtro_param;
            $params[] = $filtro_param;
        }
        $sql .= ' ORDER BY p.data_vencimento DESC';

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function obterCobrancasContrato($contrato_id, $ativas = true) {
    global $conn;
    try {
        $sql = 'SELECT * FROM contrato_cobrancas WHERE contrato_id = ?';
        $params = [$contrato_id];
        if ($ativas) {
            $sql .= ' AND ativa = 1';
        }
        $sql .= ' ORDER BY id ASC';
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function salvarCobrancasContrato($contrato_id, array $cobrancas) {
    global $conn;
    $tipos = $cobrancas['tipo'] ?? [];
    $valores = $cobrancas['valor'] ?? [];
    $descricoes = $cobrancas['descricao'] ?? [];
    $produto_ids = $cobrancas['produto_id'] ?? [];

    try {
        $conn->beginTransaction();
        $delete = $conn->prepare('DELETE FROM contrato_cobrancas WHERE contrato_id = ?');
        $delete->execute([$contrato_id]);

        $insert = $conn->prepare('INSERT INTO contrato_cobrancas (contrato_id, produto_id, tipo, descricao, valor, ativa) VALUES (?, ?, ?, ?, ?, 1)');
        for ($i = 0; $i < count($tipos); $i++) {
            $tipo = $tipos[$i] ?? '';
            $valor = isset($valores[$i]) ? (float) str_replace(',', '.', (string) $valores[$i]) : 0;
            $descricao = $descricoes[$i] ?? '';
            $prodId = !empty($produto_ids[$i]) ? $produto_ids[$i] : null;

            if (!in_array($tipo, ['momentanea', 'mensal', 'trimestral', 'anual'], true)) {
                continue;
            }
            if ($valor <= 0) {
                continue;
            }

            $insert->execute([$contrato_id, $prodId, $tipo, $descricao, $valor]);
        }

        $conn->commit();
        return true;
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        return false;
    }
}

function calcularVencimentoCobranca(DateTime $base, $tipo) {
    $data = clone $base;
    if ($tipo === 'mensal') {
        $data->modify('+1 month');
    } elseif ($tipo === 'trimestral') {
        $data->modify('+3 months');
    } elseif ($tipo === 'anual') {
        $data->modify('+12 months');
    }
    return $data;
}

function pagamentoJaExisteParaCobranca($contrato_id, $data_vencimento, $tipo, $descricao, $valor) {
    global $conn;
    try {
        $stmt = $conn->prepare('SELECT id FROM pagamentos WHERE contrato_id = ? AND data_vencimento = ? AND tipo = ? AND descricao = ? AND valor = ? AND status <> "cancelado" LIMIT 1');
        $stmt->execute([$contrato_id, $data_vencimento, $tipo, $descricao, $valor]);
        return (bool) $stmt->fetch();
    } catch (Exception $e) {
        return false;
    }
}

function gerarPagamentosContrato($contrato_id, $recriarFuturos = true) {
    global $conn;
    try {
        $stmt = $conn->prepare('SELECT * FROM contratos WHERE id = ?');
        $stmt->execute([$contrato_id]);
        $contrato = $stmt->fetch();
        if (!$contrato) {
            return false;
        }

        $cobrancas = obterCobrancasContrato($contrato_id, true);
        if (empty($cobrancas)) {
            return true;
        }

        if ($recriarFuturos) {
            $delete = $conn->prepare('DELETE FROM pagamentos WHERE contrato_id = ? AND status = "pendente" AND data_vencimento >= CURDATE()');
            $delete->execute([$contrato_id]);
        }

        $inicio = new DateTime($contrato['data_inicio']);
        $fim = !empty($contrato['data_fim']) ? new DateTime($contrato['data_fim']) : null;

        foreach ($cobrancas as $cobranca) {
            $tipo = $cobranca['tipo'];
            $valor = (float) $cobranca['valor'];
            $descricao = $cobranca['descricao'] ?: 'Cobrança contratual';
            $cobrancaId = $cobranca['id'];
            $prodId = $cobranca['produto_id'] ?? null;

            if ($tipo === 'momentanea') {
                $dataVencimento = $inicio->format('Y-m-d');
                if (!pagamentoJaExisteParaCobranca($contrato_id, $dataVencimento, 'servico', $descricao, $valor)) {
                    $insert = $conn->prepare('INSERT INTO pagamentos (cliente_id, contrato_id, contrato_cobranca_id, produto_id, tipo, descricao, valor, data_vencimento, metodo_pagamento, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                    $insert->execute([$contrato['cliente_id'], $contrato_id, $cobrancaId, $prodId, 'servico', $descricao, $valor, $dataVencimento, $contrato['metodo_pagamento_padrao'] ?? null, 'pendente']);
                }
                continue;
            }

            $dataVencimento = calcularVencimentoCobranca($inicio, $tipo);
            while (!$fim || $dataVencimento <= $fim) {
                $dataFormatada = $dataVencimento->format('Y-m-d');
                    if (!pagamentoJaExisteParaCobranca($contrato_id, $dataFormatada, 'mensalidade', $descricao, $valor)) {
                    $insert = $conn->prepare('INSERT INTO pagamentos (cliente_id, contrato_id, contrato_cobranca_id, produto_id, tipo, descricao, valor, data_vencimento, metodo_pagamento, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                    $insert->execute([$contrato['cliente_id'], $contrato_id, $cobrancaId, $prodId, 'mensalidade', $descricao, $valor, $dataFormatada, $contrato['metodo_pagamento_padrao'] ?? null, 'pendente']);
                }
                $dataVencimento = calcularVencimentoCobranca($dataVencimento, $tipo);
            }
        }

        return true;
    } catch (Exception $e) {
        return false;
    }
}

function obterClientesComPendencias() {
    global $conn;
    try {
        $stmt = $conn->query('SELECT c.id, c.nome, COUNT(p.id) AS total_titulos, COALESCE(SUM(p.valor), 0) AS total_devido FROM clientes c INNER JOIN pagamentos p ON p.cliente_id = c.id WHERE p.status IN ("pendente", "atrasado") GROUP BY c.id, c.nome ORDER BY c.nome');
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function obterDividasCliente($cliente_id) {
    global $conn;
    try {
        $stmt = $conn->prepare('SELECT p.*, ct.numero_contrato FROM pagamentos p LEFT JOIN contratos ct ON ct.id = p.contrato_id WHERE p.cliente_id = ? AND p.status IN ("pendente", "atrasado") ORDER BY p.data_vencimento ASC, p.id ASC');
        $stmt->execute([$cliente_id]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function quitarPagamentos($ids, $metodo = 'caixa', $observacoes = '', $valor_pago = null) {
    global $conn;
    if (empty($ids) || !is_array($ids)) {
        return ['success' => false, 'message' => 'IDs inválidos'];
    }

    $observacoesCompleta = trim((string)$observacoes);
    if ($valor_pago !== null && $valor_pago !== '') {
        $observacoesCompleta = trim($observacoesCompleta . ' | Valor recebido: R$ ' . number_format((float)$valor_pago, 2, ',', '.'));
    }

    try {
        $conn->beginTransaction();
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = 'UPDATE pagamentos SET status = "pago", data_pagamento = CURDATE(), metodo_pagamento = ?, observacoes = CONCAT(COALESCE(observacoes, ""), CASE WHEN observacoes IS NULL OR observacoes = "" THEN "" ELSE " | " END, ?) WHERE id IN (' . $placeholders . ')';
        $stmt = $conn->prepare($sql);
        $params = array_merge([$metodo, $observacoesCompleta], array_map('intval', $ids));
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

function obterUsuarios() {
    global $conn;
    try {
        $stmt = $conn->query('SELECT * FROM usuarios ORDER BY nome');
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

// ============================================================
// DASHBOARD
// ============================================================

function obterResumoDashboard() {
    global $conn;
    try {
        $resumo = [];
        // Total de clientes
        $stmt = $conn->query('SELECT COUNT(*) as total FROM clientes WHERE status = "ativo"');
        $resumo['total_clientes'] = $stmt->fetch()['total'];
        // Pagamentos pendentes
        $stmt = $conn->query('SELECT COALESCE(SUM(valor), 0) as total FROM pagamentos WHERE status IN ("pendente", "atrasado")');
        $resumo['pagamentos_pendentes'] = $stmt->fetch()['total'];
        // Total do mês
        $mes_atual = date('Y-m');
        $stmt = $conn->query("SELECT COALESCE(SUM(valor), 0) as total FROM pagamentos WHERE status = 'pago' AND DATE_FORMAT(data_pagamento, '%Y-%m') = '$mes_atual'");
        $resumo['total_mes'] = $stmt->fetch()['total'];
        // Pagamentos atrasados
        $stmt = $conn->query("SELECT COUNT(*) as total FROM pagamentos WHERE status = 'atrasado' AND data_vencimento < CURDATE()");
        $resumo['atrasados'] = $stmt->fetch()['total'];
        return $resumo;
    } catch (Exception $e) {
        return [
            'total_clientes' => 0,
            'pagamentos_pendentes' => 0,
            'total_mes' => 0,
            'atrasados' => 0
        ];
    }
}

function obterDadosGraficoFaturamento() {
    global $conn;
    try {
        $dados = [];
        for ($i = 5; $i >= 0; $i--) {
            $mes_referencia = date('Y-m', strtotime("-$i months"));
            $label_mes = date('M/Y', strtotime("-$i months"));
            $stmt = $conn->prepare("SELECT COALESCE(SUM(valor), 0) as total FROM pagamentos WHERE status = 'pago' AND DATE_FORMAT(data_pagamento, '%Y-%m') = ?");
            $stmt->execute([$mes_referencia]);
            $valor = $stmt->fetch()['total'];
            $dados[] = ['mes' => $label_mes, 'total' => (float)$valor];
        }
        return $dados;
    } catch (Exception $e) {
        return [];
    }
}

// ============================================================
// PDF E UPLOADS
// ============================================================

function validarPDF($arquivo) {
    if (!isset($arquivo['tmp_name']) || $arquivo['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    $mime = mime_content_type($arquivo['tmp_name']);
    $extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
    if ($mime !== 'application/pdf' && $extensao !== 'pdf') {
        return false;
    }
    if ($arquivo['size'] > (defined('MAX_UPLOAD_SIZE') ? MAX_UPLOAD_SIZE : 5 * 1024 * 1024)) {
        return false;
    }
    return true;
}

function fazerUploadPDF($arquivo, $pasta) {
    if (!validarPDF($arquivo)) {
        return false;
    }
    if (!is_dir($pasta)) mkdir($pasta, 0755, true);
    $nome_arquivo = date('YmdHis') . '_' . basename($arquivo['name']);
    $caminho = rtrim($pasta, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $nome_arquivo;
    if (move_uploaded_file($arquivo['tmp_name'], $caminho)) return $nome_arquivo;
    return false;
}

// ============================================================
// UTILITÁRIOS
// ============================================================

function gerarNumeroContrato() {
    global $conn;
    try {
        $ano = date('Y');
        $stmt = $conn->query("SELECT COUNT(*) as total FROM contratos WHERE YEAR(criado_em) = $ano");
        $numero = $stmt->fetch()['total'] + 1;
        return 'CONT-' . str_pad($numero, 4, '0', STR_PAD_LEFT) . '-' . $ano;
    } catch (Exception $e) {
        return 'CONT-0001-' . date('Y');
    }
}

function redirecionarComMensagem($url, $tipo, $mensagem) {
    $_SESSION['mensagem_tipo'] = $tipo;
    $_SESSION['mensagem'] = $mensagem;
    header('Location: ' . $url);
    exit;
}

function obterMensagem() {
    $tipo = $_SESSION['mensagem_tipo'] ?? null;
    $mensagem = $_SESSION['mensagem'] ?? null;
    unset($_SESSION['mensagem_tipo']);
    unset($_SESSION['mensagem']);
    return [$tipo, $mensagem];
}

function exibirMensagem() {
    list($tipo, $mensagem) = obterMensagem();
    if (empty($mensagem)) return '';
    $tipo_attr = htmlspecialchars($tipo ?? 'info', ENT_QUOTES);
    $mensagem_attr = htmlspecialchars($mensagem, ENT_QUOTES);
    $class = ($tipo === 'sucesso') ? 'success' : (($tipo === 'erro') ? 'danger' : (($tipo === 'aviso') ? 'warning' : 'info'));
    $fallback = '<div class="alert alert-' . $class . '" role="alert">' . $mensagem . '</div>';
    return '<div id="flashMessage" style="display:none" data-tipo="' . $tipo_attr . '" data-mensagem="' . $mensagem_attr . '">' . $fallback . '</div>';
}

?>
