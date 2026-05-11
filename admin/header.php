<?php
require_once __DIR__ . '/../includes/config.php';
verificar_autenticacao();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina ?? 'MaxVeículos - Admin'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* ============================================
           MaxVeículos - Admin com as cores oficiais
           Amarelo: #FFD000
           Preto: #0B0B0B
           Branco: #FFFFFF
        ============================================ */
        :root {
            --amarelo: #FFD000;
            --preto: #0B0B0B;
            --branco: #FFFFFF;
            --cinza-escuro: #1a1a1a;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
        }

        /* Sidebar - fundo preto */
        .sidebar {
            background-color: var(--preto);
            color: var(--branco);
            min-height: 100vh;
            position: fixed;
            width: 250px;
            left: 0;
            top: 0;
            padding-top: 20px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
            overflow-y: auto;
            transition: all 0.3s;
        }

        /* Logo na sidebar */
        .sidebar-brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-brand .logo-img {
            max-width: 150px;
            height: auto;
            margin-bottom: 10px;
        }

        .sidebar-brand h3 {
            font-size: 18px;
            margin: 0;
            color: var(--amarelo);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* Menu da sidebar */
        .sidebar-menu {
            list-style: none;
            padding: 0;
        }

        .sidebar-menu li {
            margin: 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            gap: 10px;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 208, 0, 0.1);
            color: var(--amarelo);
            border-left-color: var(--amarelo);
        }

        .sidebar-menu i {
            width: 22px;
            text-align: center;
            font-size: 1.1rem;
        }

        /* Conteúdo principal */
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        /* Top navbar (barra superior) */
        .top-navbar {
            background: var(--branco);
            padding: 12px 25px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .top-navbar h1 {
            margin: 0;
            font-size: 24px;
            color: var(--preto);
            font-weight: 600;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-menu .dropdown-toggle {
            background: transparent;
            border: 1px solid #ddd;
            color: var(--preto);
            padding: 8px 15px;
            border-radius: 30px;
            transition: all 0.2s;
        }

        .user-menu .dropdown-toggle:hover {
            border-color: var(--amarelo);
            color: var(--amarelo);
        }

        .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .dropdown-item:hover {
            background-color: var(--amarelo);
            color: var(--preto);
        }

        /* Wrapper do conteúdo (cards, formulários) */
        .content-wrapper {
            background: var(--branco);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        /* Botões personalizados */
        .btn-primary {
            background-color: var(--amarelo);
            border-color: var(--amarelo);
            color: var(--preto);
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background-color: #e6bc00;
            border-color: #e6bc00;
            color: var(--preto);
            transform: translateY(-1px);
        }

        .btn-outline-primary {
            border-color: var(--amarelo);
            color: var(--amarelo);
        }
        .btn-outline-primary:hover {
            background-color: var(--amarelo);
            color: var(--preto);
        }

        /* Badges */
        .badge.bg-info {
            background-color: var(--amarelo) !important;
            color: var(--preto);
        }

        /* Tabelas */
        .table th {
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
            font-weight: 600;
            color: var(--preto);
        }

        /* Formulários */
        .form-label {
            font-weight: 600;
            color: var(--preto);
            margin-bottom: 8px;
        }

        .form-control:focus {
            border-color: var(--amarelo);
            box-shadow: 0 0 0 0.2rem rgba(255, 208, 0, 0.25);
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: static;
                min-height: auto;
            }
            .main-content {
                margin-left: 0;
                padding: 10px;
            }
            .top-navbar {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            .sidebar-menu a {
                display: inline-block;
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-brand">
            <!-- Logo imagem (ajuste o caminho conforme sua estrutura) -->
            <img src="<?php echo BASE_URL; ?>/public/assets/images/Logo-Fundo-Preto.png" alt="MaxVeículos" class="logo-img">
            <h3>
                <i class="fas fa-car"></i> MaxVeículos
            </h3>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="<?php echo ADMIN_URL; ?>/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="<?php echo ADMIN_URL; ?>/pages/veiculos.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'veiculos.php' ? 'active' : ''; ?>">
                    <i class="fas fa-car"></i> Veículos
                </a>
            </li>
            <li>
                <a href="<?php echo ADMIN_URL; ?>/pages/banners.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'banners.php' ? 'active' : ''; ?>">
                    <i class="fas fa-image"></i> Banners
                </a>
            </li>
            <li>
                <a href="<?php echo ADMIN_URL; ?>/pages/atividades.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'atividades.php' ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i> Atividades
                </a>
            </li>
            <li>
                <hr style="border-color: rgba(255, 255, 255, 0.2); margin: 10px 0;">
            </li>
            <li>
                <a href="<?php echo ADMIN_URL; ?>/pages/perfil.php" class="<?php echo basename($_SERVER['PHP_SELF']) === 'perfil.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user"></i> Meu Perfil
                </a>
            </li>
            <li>
                <a href="<?php echo ADMIN_URL; ?>/logout.php">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="top-navbar">
            <h1><?php echo $titulo_pagina ?? 'Dashboard'; ?></h1>
            <div class="user-info">
                <div class="user-menu">
                    <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i> <?php echo $_SESSION['admin_nome'] ?? 'Admin'; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo ADMIN_URL; ?>/pages/perfil.php">Meu Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo ADMIN_URL; ?>/logout.php">Sair</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="content-wrapper">
            <?php exibir_mensagem(); ?>
            <!-- O conteúdo específico de cada página (dashboard, veículos, etc.) virá depois -->