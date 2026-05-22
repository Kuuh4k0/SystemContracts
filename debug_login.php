<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';

echo "<h1>🔍 DEBUG DE LOGIN</h1>";

// Testar hash
$senha_teste = '123123';
$hash_teste = hash('sha256', $senha_teste);
echo "<h3>1. Hash da senha '123123':</h3>";
echo "<code>" . $hash_teste . "</code><br>";

// Testar conexão
echo "<h3>2. Conexão com banco:</h3>";
try {
    $stmt = $conn->query("SELECT VERSION()");
    $version = $stmt->fetch();
    echo "✅ Banco conectado! MySQL: " . $version[0] . "<br>";
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
    exit;
}

// Verificar tabela usuarios
echo "<h3>3. Tabela usuarios:</h3>";
try {
    $stmt = $conn->query("SELECT * FROM usuarios");
    $usuarios = $stmt->fetchAll();
    echo "Total de usuários: " . count($usuarios) . "<br>";
    
    foreach ($usuarios as $user) {
        echo "<strong>" . $user['email'] . "</strong><br>";
        echo "Senha no BD: <code>" . $user['senha'] . "</code><br>";
        echo "Hash 123123: <code>" . hash('sha256', '123123') . "</code><br>";
        echo "Senha igual? " . ($user['senha'] === hash('sha256', '123123') ? "✅ SIM" : "❌ NÃO") . "<br>";
        echo "Ativo? " . ($user['ativo'] ? "✅ SIM" : "❌ NÃO") . "<br>";
        echo "<hr>";
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "<br>";
}

// Testar login
echo "<h3>4. Teste de Login:</h3>";
session_start();
require_once __DIR__ . '/includes/functions.php';

$resultado = fazerLogin('admin@contratos.com', '123123');
if ($resultado) {
    echo "✅ Login bem-sucedido!<br>";
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "Nome: " . $_SESSION['user_nome'] . "<br>";
} else {
    echo "❌ Falha no login<br>";
}
?>
