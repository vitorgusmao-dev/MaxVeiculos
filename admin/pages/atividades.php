<?php
/**
 * MaxVeículos - Visualização de Atividades
 */

$titulo_pagina = 'Log de Atividades';
require_once __DIR__ . '/../../includes/config.php';
verificar_autenticacao();

// Paginação
$pagina = $_GET['pagina'] ?? 1;
$itens_por_pagina = 20;
$offset = ($pagina - 1) * $itens_por_pagina;

// Obter total de atividades
$stmt = $pdo->query("SELECT COUNT(*) as total FROM logs_atividades");
$total = $stmt->fetchColumn();
$total_paginas = ceil($total / $itens_por_pagina);

// Obter atividades
$stmt = $pdo->query("
    SELECT la.*, a.username 
    FROM logs_atividades la 
    LEFT JOIN admin a ON la.admin_id = a.id 
    ORDER BY la.created_at DESC 
    LIMIT $offset, $itens_por_pagina
");
$atividades = $stmt->fetchAll();

require_once __DIR__ . '/../../admin/header.php';
?>

<div class="table-responsive">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Usuário</th>
                <th>Ação</th>
                <th>Descrição</th>
                <th>IP Address</th>
                <th>Data/Hora</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($atividades)): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p>Nenhuma atividade registrada</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($atividades as $atividade): ?>
                    <tr>
                        <td><strong><?php echo $atividade['username'] ?? 'Desconhecido'; ?></strong></td>
                        <td>
                            <span class="badge bg-info"><?php echo $atividade['acao']; ?></span>
                        </td>
                        <td><?php echo $atividade['descricao']; ?></td>
                        <td><code><?php echo $atividade['ip_address']; ?></code></td>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($atividade['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Paginação -->
<?php if ($total_paginas > 1): ?>
    <nav aria-label="Paginação">
        <ul class="pagination justify-content-center mt-4">
            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <li class="page-item <?php echo $i === $pagina ? 'active' : ''; ?>">
                    <a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>

<?php
require_once __DIR__ . '/../../admin/footer.php';
?>
