<?php
/**
 * MaxVeículos - Tela de Login do Admin
 */

require_once __DIR__ . '/../includes/config.php';

// Se já está autenticado, redireciona para dashboard
verificar_desautenticacao();

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizar($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $erro = 'Usuário e senha são obrigatórios.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                // Autenticação bem-sucedida
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_nome'] = $admin['nome_completo'];

                // Atualizar último login
                $stmt = $pdo->prepare("UPDATE admin SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$admin['id']]);

                // Registrar atividade
                registrar_atividade($pdo, 'LOGIN', 'Admin realizou login');

                // Redirecionar para dashboard
                header('Location: ' . ADMIN_URL . '/dashboard.php');
                exit;
            } else {
                $erro = 'Usuário ou senha inválidos.';
            }
        } catch (PDOException $e) {
            $erro = 'Erro ao processar o login. Tente novamente.';
            if (DEBUG_MODE) {
                error_log("Erro login: " . $e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MaxVeículos Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* ============================================
           Paleta de cores - MaxVeículos
           Amarelo: #FFD000
           Preto: #0B0B0B
           Branco: #FFFFFF
        ============================================ */
        :root {
            --amarelo: #FFD000;
            --preto: #0B0B0B;
            --branco: #FFFFFF;
        }

        body {
            background: linear-gradient(135deg, var(--preto) 0%, #2c2c2c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            background: var(--branco);
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 420px;
            width: 100%;
            padding: 40px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        /* Logo imagem */
        .logo-img {
            max-width: 180px;
            height: auto;
            margin-bottom: 15px;
        }

        .login-header h1 {
            font-size: 24px;
            color: var(--preto);
            margin-bottom: 5px;
            font-weight: bold;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
        }

        .form-control {
            height: 45px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 14px;
            padding: 10px 15px;
        }

        .form-control:focus {
            border-color: var(--amarelo);
            box-shadow: 0 0 0 0.2rem rgba(255, 208, 0, 0.25);
        }

        .btn-login {
            width: 100%;
            height: 45px;
            background-color: var(--amarelo);
            border: none;
            color: var(--preto);
            font-weight: bold;
            border-radius: 5px;
            font-size: 16px;
            margin-top: 10px;
            transition: transform 0.3s, background-color 0.3s;
        }

        .btn-login:hover {
            background-color: #e6bc00;
            transform: translateY(-2px);
            color: var(--preto);
        }

        .alert {
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <!-- Logo da MaxVeículos - ajuste o caminho conforme a estrutura -->
            <img src="<?php echo BASE_URL; ?>/public/assets/images/Logo-Fundo-Preto.png" alt="MaxVeículos" class="logo-img">
            <h1>MaxVeículos</h1>
            <p>Painel de Administração</p>
        </div>

        <?php if ($erro): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Erro!</strong> <?php echo $erro; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Usuário</label>
                <input type="text" class="form-control" id="username" name="username" required autofocus>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <button type="submit" class="btn btn-login">Entrar</button>
        </form>

        <div class="login-footer">
            <p>Credenciais padrão: admin / admin123</p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>