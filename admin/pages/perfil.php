<?php
/**
 * MaxVeículos - Perfil do Admin
 */

$titulo_pagina = 'Meu Perfil';
require_once __DIR__ . '/../../includes/config.php';
verificar_autenticacao();

// Obter informações do admin
$stmt = $pdo->prepare("SELECT * FROM admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();

$erro = '';
$sucesso = '';

// Processar mudança de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'mudar_senha') {
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_senha)) {
        $erro = 'Todos os campos são obrigatórios.';
    } elseif (!password_verify($senha_atual, $admin['password'])) {
        $erro = 'Senha atual incorreta.';
    } elseif ($nova_senha !== $confirmar_senha) {
        $erro = 'As senhas não correspondem.';
    } elseif (strlen($nova_senha) < 6) {
        $erro = 'A nova senha deve ter no mínimo 6 caracteres.';
    } else {
        try {
            $nova_senha_hash = password_hash($nova_senha, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE admin SET password = ? WHERE id = ?");
            $stmt->execute([$nova_senha_hash, $_SESSION['admin_id']]);

            registrar_atividade($pdo, 'CHANGE_PASSWORD', 'Admin alterou sua senha');
            $sucesso = 'Senha alterada com sucesso!';
        } catch (Exception $e) {
            $erro = 'Erro ao alterar senha: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/../../admin/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Informações do Perfil</h5>
            </div>
            <div class="card-body">
                <?php if ($erro): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $erro; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($sucesso): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $sucesso; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label class="form-label">Usuário</label>
                    <input type="text" class="form-control" value="<?php echo $admin['username']; ?>" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nome Completo</label>
                    <input type="text" class="form-control" value="<?php echo $admin['nome_completo']; ?>" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" value="<?php echo $admin['email']; ?>" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Último Login</label>
                    <input type="text" class="form-control" value="<?php echo $admin['last_login'] ? date('d/m/Y H:i', strtotime($admin['last_login'])) : 'Nunca'; ?>" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label">Data de Cadastro</label>
                    <input type="text" class="form-control" value="<?php echo date('d/m/Y H:i', strtotime($admin['created_at'])); ?>" disabled>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Alterar Senha</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="acao" value="mudar_senha">

                    <div class="mb-3">
                        <label for="senha_atual" class="form-label">Senha Atual *</label>
                        <input type="password" class="form-control" id="senha_atual" name="senha_atual" required>
                    </div>

                    <div class="mb-3">
                        <label for="nova_senha" class="form-label">Nova Senha *</label>
                        <input type="password" class="form-control" id="nova_senha" name="nova_senha" required>
                        <small class="text-muted">Mínimo 6 caracteres</small>
                    </div>

                    <div class="mb-3">
                        <label for="confirmar_senha" class="form-label">Confirmar Nova Senha *</label>
                        <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Alterar Senha
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Ajuda Rápida</h5>
            </div>
            <div class="card-body">
                <h6>Recursos do Sistema:</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> Gerenciar Veículos</li>
                    <li><i class="fas fa-check text-success"></i> Gerenciar Banners</li>
                    <li><i class="fas fa-check text-success"></i> Upload de Imagens</li>
                    <li><i class="fas fa-check text-success"></i> Log de Atividades</li>
                    <li><i class="fas fa-check text-success"></i> Dashboard Dinâmico</li>
                </ul>

                <hr>

                <h6>Dicas de Segurança:</h6>
                <ul class="list-unstyled small">
                    <li>✓ Use uma senha forte</li>
                    <li>✓ Altere sua senha regularmente</li>
                    <li>✓ Nunca compartilhe suas credenciais</li>
                    <li>✓ Faça logout quando terminar</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../admin/footer.php';
?>
