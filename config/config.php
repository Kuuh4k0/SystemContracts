<?php
/**
 * CONFIGURAÇÕES PRINCIPAIS DO SISTEMA
 */

// Ambiente
define('AMBIENTE', 'development');
define('DEBUG_MODE', true);

// Banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'system_contratos');

// Aplicação
define('APP_NAME', 'Sistema de Gerenciamento de Contratos');
define('APP_VERSION', '1.0.0');
define('TIMEZONE', 'America/Sao_Paulo');

// Segurança
define('SESSION_TIMEOUT', 3600);
define('MAX_UPLOAD_SIZE', 10485760); // 10MB

// Moeda
define('MOEDA_SIMBOLO', 'R$');
define('MOEDA_DECIMAIS', 2);

// Pasta de uploads
define('UPLOAD_DIR', __DIR__ . '/../documentos/');
?>
