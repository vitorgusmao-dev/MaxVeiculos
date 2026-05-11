<?php
/**
 * MaxVeículos - Dashboard do Admin
 */

$titulo_pagina = 'Dashboard';
require_once __DIR__ . '/header.php';

// Obter estatísticas
$stats = obter_estatisticas($pdo);

// Obter últimas atividades
$stmt = $pdo->query("
    SELECT la.*, a.username 
    FROM logs_atividades la 
    LEFT JOIN admin a ON la.admin_id = a.id 
    ORDER BY la.created_at DESC 
    LIMIT 10
");
$atividades = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card h-100 card-stats">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total de Veículos</h6>
                        <h2 class="mb-0"><?php echo $stats['total_veiculos']; ?></h2>
                    </div>
                    <i class="fas fa-car fa-3x card-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card h-100 card-stats">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Em Destaque</h6>
                        <h2 class="mb-0"><?php echo $stats['veiculos_destaque']; ?></h2>
                    </div>
                    <i class="fas fa-star fa-3x card-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card h-100 card-stats">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total de Banners</h6>
                        <h2 class="mb-0"><?php echo $stats['total_banners']; ?></h2>
                    </div>
                    <i class="fas fa-image fa-3x card-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card h-100 card-stats">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Último Veículo</h6>
                        <p class="mb-0">
                            <small>
                                <?php 
                                if ($stats['ultimo_veiculo']) {
                                    echo $stats['ultimo_veiculo']['modelo'] . ' ' . $stats['ultimo_veiculo']['ano'];
                                } else {
                                    echo 'Nenhum';
                                }
                                ?>
                            </small>
                        </p>
                    </div>
                    <i class="fas fa-spark fa-3x card-icon"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Ações Rápidas</h5>
            </div>
            <div class="card-body">
                <a href="<?php echo ADMIN_URL; ?>/pages/veiculos.php?action=add" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-plus"></i> Adicionar Novo Veículo
                </a>
                <a href="<?php echo ADMIN_URL; ?>/pages/banners.php?action=add" class="btn btn-outline-primary w-100 mb-2">
                    <i class="fas fa-plus"></i> Adicionar Novo Banner
                </a>
                <a href="<?php echo ADMIN_URL; ?>/pages/veiculos.php" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-list"></i> Ver Todos os Veículos
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Informações do Sistema</h5>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Versão PHP:</strong> <?php echo phpversion(); ?>
                </p>
                <p class="mb-2">
                    <strong>Servidor:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?>
                </p>
                <p class="mb-2">
                    <strong>Seu IP:</strong> <?php echo $_SERVER['REMOTE_ADDR']; ?>
                </p>
                <p class="mb-0">
                    <strong>Último acesso:</strong> 
                    <?php 
                    $stmt = $pdo->prepare("SELECT last_login FROM admin WHERE id = ?");
                    $stmt->execute([$_SESSION['admin_id']]);
                    $data = $stmt->fetch();
                    echo $data['last_login'] ? date('d/m/Y H:i', strtotime($data['last_login'])) : 'Primeiro acesso';
                    ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Últimas Atividades</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Usuário</th>
                            <th>Ação</th>
                            <th>Descrição</th>
                            <th>IP</th>
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
                                    <td>
                                        <strong><?php echo $atividade['username'] ?? 'Desconhecido'; ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $atividade['acao']; ?></span>
                                    </td>
                                    <td><?php echo $atividade['descricao']; ?></td>
                                    <td><code><?php echo $atividade['ip_address']; ?></code></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($atividade['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>