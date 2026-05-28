<?php
verificarAutenticacao();
$pagina_atual = basename($_SERVER['PHP_SELF']);
$primeiro_nome_usuario = explode(' ', trim($_SESSION['user_nome'] ?? 'Usuário'))[0];
$body_class = $body_class ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titulo) ? $titulo . ' - ' : ''; ?>Sistema de Gerenciamento de Contratos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/SystemContracts/css/style.css" rel="stylesheet">
</head>
<body class="<?php echo htmlspecialchars(trim($body_class)); ?>">
    <nav class="navbar navbar-expand-lg navbar-dark app-navbar sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand app-brand" href="/SystemContracts/dashboard.php">
                <span class="app-brand-mark">
                    <i class="fas fa-file-contract"></i>
                </span>
                <span class="app-brand-text">
                    <strong>ARISE TECH LTDA</strong>
                    <small>Sistema de Gerenciamento de Contratos</small>
                </span>
            </a>
            <button class="navbar-toggler app-navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Alternar navegação">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse app-navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto app-nav-list">
                    <li class="nav-item app-nav-item <?php echo (strpos($_SERVER['PHP_SELF'], '/clientes/') !== false) ? 'active' : ''; ?>">
                        <a class="nav-link app-nav-link" href="/SystemContracts/clientes/listar.php">
                            <i class="fas fa-users"></i> Clientes
                        </a>
                    </li>
                    <li class="nav-item app-nav-item <?php echo (strpos($_SERVER['PHP_SELF'], '/produtos/') !== false) ? 'active' : ''; ?>">
                        <a class="nav-link app-nav-link" href="/SystemContracts/produtos/listar.php">
                            <i class="fas fa-box"></i> Produtos
                        </a>
                    </li>
                    <li class="nav-item app-nav-item <?php echo (strpos($_SERVER['PHP_SELF'], '/contratos/') !== false) ? 'active' : ''; ?>">
                        <a class="nav-link app-nav-link" href="/SystemContracts/contratos/listar.php">
                            <i class="fas fa-file-alt"></i> Contratos
                        </a>
                    </li>
                    <li class="nav-item app-nav-item <?php echo (strpos($_SERVER['PHP_SELF'], '/pagamentos/') !== false && strpos($_SERVER['PHP_SELF'], '/pagamentos/quitacao.php') === false) ? 'active' : ''; ?>">
                        <a class="nav-link app-nav-link" href="/SystemContracts/pagamentos/listar.php">
                            <i class="fas fa-credit-card"></i> Pagamentos
                        </a>
                    </li>
                    <li class="nav-item app-nav-item <?php echo (strpos($_SERVER['PHP_SELF'], '/pagamentos/quitacao.php') !== false) ? 'active' : ''; ?>">
                        <a class="nav-link app-nav-link" href="/SystemContracts/pagamentos/quitacao.php">
                            <i class="fas fa-receipt"></i> Quitação
                        </a>
                    </li>
                    <?php if ($_SESSION['user_perfil'] === 'admin'): ?>
                    <li class="nav-item app-nav-item <?php echo (strpos($_SERVER['PHP_SELF'], '/usuarios/') !== false) ? 'active' : ''; ?>">
                        <a class="nav-link app-nav-link" href="/SystemContracts/usuarios/listar.php">
                            <i class="fas fa-user-tie"></i> Usuários
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item dropdown ml-lg-3 app-user-nav">
                        <a class="nav-link dropdown-toggle app-user-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="app-user-avatar">
                                <?php echo strtoupper(substr($primeiro_nome_usuario, 0, 1)); ?>
                            </span>
                            <span class="app-user-text">
                                <small>Logado como</small>
                                <strong><?php echo htmlspecialchars($primeiro_nome_usuario); ?></strong>
                            </span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right app-user-menu">
                            <a class="dropdown-item" href="/SystemContracts/perfil.php">Meu Perfil</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="/SystemContracts/logout.php">Sair</a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container-fluid mt-4">
