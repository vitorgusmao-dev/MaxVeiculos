<?php
/**
 * MaxVeículos - Logout do Admin
 */

require_once __DIR__ . '/../includes/config.php';

// Verifica se o admin está logado (opcional, mas seguro)
verificar_autenticacao();

// Registrar atividade (se a função existir)
if (function_exists('registrar_atividade')) {
    registrar_atividade($pdo, 'LOGOUT', 'Admin realizou logout');
}

// Destruir a sessão e limpar dados
fazer_logout();  // essa função deve existir no config.php

// Redireciona para a tela de login
header('Location: ' . ADMIN_URL . '/login.php');
exit;
?>