<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação do Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f5f7fa; padding: 20px; }
        .container { max-width: 900px; margin-top: 30px; }
        .check-item { padding: 15px; margin-bottom: 10px; border-left: 4px solid #ddd; background: white; }
        .check-ok { border-left-color: #28a745; }
        .check-error { border-left-color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4"><i class="fas fa-stethoscope"></i> Verificação do Sistema</h1>
        
        <?php
        $problemas = 0;
        
        // 1. PDO MySQL
        echo '<h3>1. PDO e MySQL</h3>';
        try {
            $pdo = new PDO('mysql:host=localhost', 'root', '');
            echo '<div class="check-item check-ok">✅ Conexão com MySQL OK</div>';
        } catch (PDOException $e) {
            echo '<div class="check-item check-error">❌ Erro: ' . $e->getMessage() . '</div>';
            $problemas++;
        }
        
        // 2. config.php
        echo '<h3>2. Arquivos de Configuração</h3>';
        if (file_exists(__DIR__ . '/config/config.php')) {
            echo '<div class="check-item check-ok">✅ config/config.php existe</div>';
        } else {
            echo '<div class="check-item check-error">❌ config/config.php NÃO ENCONTRADO</div>';
            $problemas++;
        }
        
        if (file_exists(__DIR__ . '/config/database.php')) {
            echo '<div class="check-item check-ok">✅ config/database.php existe</div>';
        } else {
            echo '<div class="check-item check-error">❌ config/database.php NÃO ENCONTRADO</div>';
            $problemas++;
        }
        
        // 3. Funções
        echo '<h3>3. Arquivos de Sistema</h3>';
        if (file_exists(__DIR__ . '/includes/functions.php')) {
            echo '<div class="check-item check-ok">✅ includes/functions.php existe</div>';
        } else {
            echo '<div class="check-item check-error">❌ includes/functions.php NÃO ENCONTRADO</div>';
            $problemas++;
        }
        
        // 4. CSS e JS
        echo '<h3>4. Arquivos Estáticos</h3>';
        if (file_exists(__DIR__ . '/css/style.css')) {
            echo '<div class="check-item check-ok">✅ css/style.css existe</div>';
        } else {
            echo '<div class="check-item check-error">❌ css/style.css NÃO ENCONTRADO</div>';
            $problemas++;
        }
        
        if (file_exists(__DIR__ . '/js/script.js')) {
            echo '<div class="check-item check-ok">✅ js/script.js existe</div>';
        } else {
            echo '<div class="check-item check-error">❌ js/script.js NÃO ENCONTRADO</div>';
            $problemas++;
        }
        
        // 5. database.sql
        echo '<h3>5. Schema do Banco de Dados</h3>';
        if (file_exists(__DIR__ . '/database.sql')) {
            echo '<div class="check-item check-ok">✅ database.sql existe</div>';
        } else {
            echo '<div class="check-item check-error">❌ database.sql NÃO ENCONTRADO</div>';
            $problemas++;
        }
        
        // 6. Módulos
        echo '<h3>6. Módulos do Sistema</h3>';
        $modulos = ['clientes', 'contratos', 'pagamentos', 'usuarios'];
        foreach ($modulos as $modulo) {
            if (is_dir(__DIR__ . '/' . $modulo)) {
                echo '<div class="check-item check-ok">✅ ' . $modulo . '/ existe</div>';
            } else {
                echo '<div class="check-item check-error">❌ ' . $modulo . '/ NÃO ENCONTRADO</div>';
                $problemas++;
            }
        }
        
        // 7. Banco de dados
        echo '<h3>7. Banco de Dados</h3>';
        try {
            require_once __DIR__ . '/config/database.php';
            echo '<div class="check-item check-ok">✅ Banco de dados conectado</div>';
            
            $stmt = $conn->query("SHOW TABLES FROM system_contratos");
            $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo '<div class="check-item check-ok">✅ ' . count($tabelas) . ' tabelas encontradas: ' . implode(', ', $tabelas) . '</div>';
            
            if (count($tabelas) < 5) {
                echo '<div class="check-item check-error">⚠️ AVISO: Menos de 5 tabelas! Importe o database.sql!</div>';
                $problemas++;
            }
        } catch (Exception $e) {
            echo '<div class="check-item check-error">❌ Erro ao conectar: ' . $e->getMessage() . '</div>';
            $problemas++;
        }
        
        // Resumo
        echo '<hr>';
        if ($problemas === 0) {
            echo '<div class="alert alert-success"><h4>✅ Sistema pronto!</h4> Todos os arquivos estão ok. Você pode acessar: <a href="/SystemContracts/index.php">Login</a></div>';
        } else {
            echo '<div class="alert alert-danger"><h4>⚠️ ' . $problemas . ' problema(s) encontrado(s)</h4></div>';
        }
        ?>
    </div>
</body>
</html>
