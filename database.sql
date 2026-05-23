-- ============================================================
-- SISTEMA DE GERENCIAMENTO DE CONTRATOS
-- Arquivo: database.sql
-- Descrição: Schema idempotente para reaproveitar um banco existente
-- ============================================================

-- Use este script dentro do banco já existente `system_contratos`.
-- Ele preserva os dados e não falha se as tabelas já estiverem criadas.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- TABELA: usuarios
-- ============================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(64) NOT NULL,
    perfil ENUM('admin', 'usuario') NOT NULL DEFAULT 'usuario',
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_perfil (perfil)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: clientes
-- ============================================================
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(200) NOT NULL,
    email VARCHAR(150),
    telefone VARCHAR(20),
    cpf_cnpj VARCHAR(20),
    endereco VARCHAR(255),
    cidade VARCHAR(100),
    estado VARCHAR(2),
    cep VARCHAR(10),
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nome (nome),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: produtos
-- ============================================================
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(200) NOT NULL,
    descricao TEXT,
    preco DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    estoque INT DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: contratos
-- ============================================================
CREATE TABLE IF NOT EXISTS contratos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    numero_contrato VARCHAR(50) UNIQUE NOT NULL,
    descricao TEXT,
    valor_total DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    data_inicio DATE NOT NULL,
    data_fim DATE,
    status ENUM('rascunho', 'ativo', 'finalizado', 'cancelado') NOT NULL DEFAULT 'ativo',
    arquivo_pdf VARCHAR(255),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    INDEX idx_numero (numero_contrato),
    INDEX idx_cliente (cliente_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE contratos
    ADD COLUMN IF NOT EXISTS arquivo_pdf VARCHAR(255) NULL AFTER status;

ALTER TABLE contratos
    ADD COLUMN IF NOT EXISTS valor_total DECIMAL(15, 2) NOT NULL DEFAULT 0.00;

ALTER TABLE contratos
    ADD COLUMN IF NOT EXISTS metodo_pagamento_padrao VARCHAR(50) NULL AFTER arquivo_pdf;

-- ============================================================
-- TABELA: contrato_cobrancas
-- Armazena a configuração das cobranças vinculadas ao contrato
-- ============================================================
CREATE TABLE IF NOT EXISTS contrato_cobrancas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    contrato_id INT NOT NULL,
    produto_id INT NULL,
    tipo ENUM('momentanea', 'mensal', 'trimestral', 'anual') NOT NULL,
    descricao VARCHAR(255),
    valor DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    ativa TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (contrato_id) REFERENCES contratos(id) ON DELETE CASCADE,
    INDEX idx_contrato (contrato_id),
    INDEX idx_produto (produto_id),
    INDEX idx_tipo (tipo),
    INDEX idx_ativa (ativa)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE contrato_cobrancas
    ADD COLUMN IF NOT EXISTS produto_id INT NULL AFTER contrato_id;

-- ============================================================
-- TABELA: pagamentos
-- ============================================================
CREATE TABLE IF NOT EXISTS pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    contrato_id INT,
    produto_id INT NULL,
    tipo ENUM('mensalidade', 'servico', 'multa', 'outro') NOT NULL DEFAULT 'servico',
    descricao VARCHAR(255),
    valor DECIMAL(15, 2) NOT NULL,
    data_vencimento DATE NOT NULL,
    data_pagamento DATE,
    status ENUM('pendente', 'pago', 'atrasado', 'cancelado') NOT NULL DEFAULT 'pendente',
    metodo_pagamento VARCHAR(50),
    observacoes TEXT,
    criado_por INT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (contrato_id) REFERENCES contratos(id) ON DELETE SET NULL,
    FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_cliente (cliente_id),
    INDEX idx_contrato (contrato_id),
    INDEX idx_produto (produto_id),
    INDEX idx_status (status),
    INDEX idx_vencimento (data_vencimento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE pagamentos
    ADD COLUMN IF NOT EXISTS contrato_id INT NULL AFTER cliente_id;

ALTER TABLE pagamentos
    ADD COLUMN IF NOT EXISTS produto_id INT NULL AFTER contrato_id;

-- ============================================================
-- TABELA: quitacao_recebimentos
-- Armazena o recibo principal gerado ao quitar pagamentos
-- ============================================================
CREATE TABLE IF NOT EXISTS quitacao_recebimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    valor_total DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    valor_recebido DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    troco DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    observacoes TEXT,
    criado_por INT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (criado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_cliente (cliente_id),
    INDEX idx_data (criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: quitacao_recebimento_formas
-- Detalha as formas de pagamento usadas no recibo
-- ============================================================
CREATE TABLE IF NOT EXISTS quitacao_recebimento_formas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recebimento_id INT NOT NULL,
    metodo VARCHAR(50) NOT NULL,
    cartao_tipo VARCHAR(20),
    valor DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recebimento_id) REFERENCES quitacao_recebimentos(id) ON DELETE CASCADE,
    INDEX idx_recebimento (recebimento_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: quitacao_recebimento_pagamentos
-- Vincula o recibo aos pagamentos quitados
-- ============================================================
CREATE TABLE IF NOT EXISTS quitacao_recebimento_pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recebimento_id INT NOT NULL,
    pagamento_id INT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recebimento_id) REFERENCES quitacao_recebimentos(id) ON DELETE CASCADE,
    FOREIGN KEY (pagamento_id) REFERENCES pagamentos(id) ON DELETE CASCADE,
    INDEX idx_recebimento (recebimento_id),
    INDEX idx_pagamento (pagamento_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABELA: logs
-- ============================================================
CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    tabela VARCHAR(50),
    registro_id INT,
    acao VARCHAR(50),
    descricao TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_tabela (tabela),
    INDEX idx_data (criado_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- FIM DO SCRIPT
-- ============================================================
