<?php
require_once __DIR__ . '/../includes/config.php';

// Hash da senha '123456'
$nova_senha_hash = password_hash('123456', PASSWORD_BCRYPT);

// Atualizar o admin
$stmt = $pdo->prepare("UPDATE admin SET password = ? WHERE username = 'admin'");
if ($stmt->execute([$nova_senha_hash])) {
    echo "✅ Senha do admin atualizada para '123456' com sucesso!";
} else {
    echo "❌ Erro ao atualizar a senha.";
}
?>