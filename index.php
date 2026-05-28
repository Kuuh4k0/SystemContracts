<?php
// ============================================================
// LOGIN - PÁGINA DE AUTENTICAÇÃO
// ============================================================

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Se já está logado, redireciona
if (isset($_SESSION['user_id'])) {
    header('Location: /SystemContracts/dashboard.php');
    exit;
}

$erro = '';

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    
    if (empty($email) || empty($senha)) {
        $erro = 'Email e senha são obrigatórios!';
    } elseif (fazerLogin($email, $senha)) {
        header('Location: /SystemContracts/dashboard.php');
        exit;
    } else {
        $erro = 'Email ou senha inválidos!';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Contratos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/SystemContracts/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-hero">
            <div class="login-hero-badge">
                <i class="fas fa-shield-alt"></i>
                Gestão segura
            </div>
            <h1>Controle contratos, clientes e pagamentos com uma interface mais clara.</h1>
            <p>
                Acesse o painel administrativo com um fluxo rápido, visual limpo e foco nas tarefas do dia a dia.
            </p>

            <div class="login-feature-list">
                <div class="login-feature-item">
                    <i class="fas fa-file-signature"></i>
                    Contratos organizados em poucos cliques
                </div>
                <div class="login-feature-item">
                    <i class="fas fa-users"></i>
                    Cadastro centralizado de clientes e usuários
                </div>
                <div class="login-feature-item">
                    <i class="fas fa-receipt"></i>
                    Pagamentos e quitações acompanhados de perto
                </div>
            </div>
        </section>

        <section class="login-card">
            <div class="login-card-header">
                <div class="login-card-icon">
                    <i class="fas fa-file-contract"></i>
                </div>
                <div>
                    <h2>Contratos</h2>
                    <p>Entre para continuar sua operação</p>
                </div>
            </div>

            <?php if (!empty($erro)): ?>
            <div class="alert alert-danger login-alert" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo $erro; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        class="form-control"
                        id="email"
                        name="email"
                        placeholder="seu@email.com"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input
                        type="password"
                        class="form-control"
                        id="senha"
                        name="senha"
                        placeholder="Digite sua senha"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary btn-block login-submit">
                    <i class="fas fa-arrow-right-to-bracket"></i> Entrar
                </button>
            </form>

            <div class="login-note">
                <i class="fas fa-lock"></i>
                Acesso protegido por autenticação SHA-256.
            </div>
        </section>
    </main>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
