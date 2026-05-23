<?php
if (!isset($conn)) require_once __DIR__ . '/../config/database.php';
if (!function_exists('obterClientes')) require_once __DIR__ . '/../includes/functions.php';

$nome = $nome ?? '';
$email = $email ?? '';
$perfil = $perfil ?? 'usuario';
?>
<?php $form_action = $form_action ?? '/SystemContracts/usuarios/adicionar.php'; ?>
<form method="POST" action="<?php echo $form_action; ?>">
    <div class="form-group">
        <label for="nome">Nome *</label>
        <input type="text" class="form-control" id="nome" name="nome" required value="<?php echo htmlspecialchars($nome); ?>">
    </div>
    <div class="form-group">
        <label for="email">Email *</label>
        <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
    </div>
    <?php $require_password = isset($require_password) ? (bool)$require_password : true; ?>
    <div class="form-group">
        <label for="senha">Senha <?php if ($require_password) echo '*'; ?></label>
        <input type="password" class="form-control" id="senha" name="senha" <?php if ($require_password) echo 'required'; ?>>
    </div>
    <div class="form-group">
        <label for="perfil">Perfil *</label>
        <select class="form-control" id="perfil" name="perfil">
            <option value="usuario" <?php echo ($perfil === 'usuario') ? 'selected' : ''; ?>>Usuário</option>
            <option value="admin" <?php echo ($perfil === 'admin') ? 'selected' : ''; ?>>Administrador</option>
        </select>
    </div>
    <?php $show_active = isset($show_active) ? (bool)$show_active : false; ?>
    <?php if ($show_active): ?>
    <div class="form-group">
        <label for="ativo">
            <input type="checkbox" id="ativo" name="ativo" value="1" <?php if (!empty($ativo)) echo 'checked'; ?>>
            Usuário Ativo
        </label>
    </div>
    <?php endif; ?>
</form>
