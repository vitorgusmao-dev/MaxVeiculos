<?php
/**
 * MaxVeículos - Gestão de Banners (CRUD)
 */

$titulo_pagina = 'Gestão de Banners';
require_once __DIR__ . '/../../includes/config.php';

// Processar ações
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Processar exclusão
if ($action === 'delete' && $id) {
    try {
        $pdo->beginTransaction();

        // Obter informações do banner
        $stmt = $pdo->prepare("SELECT imagem_path FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        $banner = $stmt->fetch();

        if ($banner) {
            // Deletar imagem do servidor
            $caminho = UPLOADS_PATH . $banner['imagem_path'];
            if (file_exists($caminho)) {
                unlink($caminho);
            }

            // Deletar banner do banco de dados
            $stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
            $stmt->execute([$id]);

            registrar_atividade($pdo, 'DELETE_BANNER', 'Banner ID ' . $id . ' deletado');
            definir_mensagem('success', 'Banner deletado com sucesso!');
        }

        $pdo->commit();
        redirecionar(ADMIN_URL . '/pages/banners.php');
    } catch (Exception $e) {
        $pdo->rollBack();
        definir_mensagem('danger', 'Erro ao deletar banner: ' . $e->getMessage());
        redirecionar(ADMIN_URL . '/pages/banners.php');
    }
}

// Se for adicionar ou editar
if ($action === 'add' || $action === 'edit') {
    $banner = null;

    if ($action === 'edit' && $id) {
        $stmt = $pdo->prepare("SELECT * FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        $banner = $stmt->fetch();

        if (!$banner) {
            definir_mensagem('danger', 'Banner não encontrado!');
            redirecionar(ADMIN_URL . '/pages/banners.php');
        }
    }

    // Processar formulário
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $titulo = sanitizar($_POST['titulo'] ?? '');
        $descricao = sanitizar($_POST['descricao'] ?? '');
        $link_destino = sanitizar($_POST['link_destino'] ?? '');
        $ativo = isset($_POST['ativo']) ? 1 : 0;
        $ordem = (int)($_POST['ordem'] ?? 0);

        if (empty($titulo)) {
            definir_mensagem('danger', 'Título é obrigatório!');
        } else {
            try {
                $imagem_path = $banner['imagem_path'] ?? null;

                // Processar upload de imagem
                if (isset($_FILES['imagem']) && $_FILES['imagem']['size'] > 0) {
                    // Deletar imagem anterior se existir
                    if ($banner && $banner['imagem_path']) {
                        $caminho_antigo = UPLOADS_PATH . $banner['imagem_path'];
                        if (file_exists($caminho_antigo)) {
                            unlink($caminho_antigo);
                        }
                    }

                    $imagem_path = fazer_upload_arquivo($_FILES['imagem'], UPLOADS_PATH);
                    if (!$imagem_path) {
                        definir_mensagem('danger', 'Erro ao fazer upload da imagem!');
                        $imagem_path = $banner['imagem_path'] ?? null;
                    }
                }

                if ($action === 'add') {
                    if (!$imagem_path) {
                        definir_mensagem('danger', 'Imagem é obrigatória para um novo banner!');
                    } else {
                        $stmt = $pdo->prepare("
                            INSERT INTO banners (titulo, descricao, imagem_path, link_destino, ativo, ordem)
                            VALUES (?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $titulo, $descricao, $imagem_path, $link_destino, $ativo, $ordem
                        ]);

                        registrar_atividade($pdo, 'ADD_BANNER', 'Banner "' . $titulo . '" adicionado');
                        definir_mensagem('success', 'Banner adicionado com sucesso!');
                        redirecionar(ADMIN_URL . '/pages/banners.php');
                    }
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE banners 
                        SET titulo = ?, descricao = ?, imagem_path = ?, link_destino = ?, ativo = ?, ordem = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $titulo, $descricao, $imagem_path, $link_destino, $ativo, $ordem, $id
                    ]);

                    registrar_atividade($pdo, 'EDIT_BANNER', 'Banner ID ' . $id . ' atualizado');
                    definir_mensagem('success', 'Banner atualizado com sucesso!');
                    redirecionar(ADMIN_URL . '/pages/banners.php');
                }
            } catch (Exception $e) {
                definir_mensagem('danger', 'Erro ao salvar banner: ' . $e->getMessage());
            }
        }
    }

    require_once __DIR__ . '/../../admin/header.php';
    ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="titulo" class="form-label">Título *</label>
            <input type="text" class="form-control" id="titulo" name="titulo" value="<?php echo $banner['titulo'] ?? ''; ?>" required>
        </div>

        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea class="form-control" id="descricao" name="descricao" rows="3"><?php echo $banner['descricao'] ?? ''; ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="link_destino" class="form-label">Link de Destino</label>
                    <input type="text" class="form-control" id="link_destino" name="link_destino" value="<?php echo $banner['link_destino'] ?? ''; ?>" placeholder="/veiculos ou https://...">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="ordem" class="form-label">Ordem de Exibição</label>
                    <input type="number" class="form-control" id="ordem" name="ordem" value="<?php echo $banner['ordem'] ?? 0; ?>">
                </div>
            </div>
        </div>

        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="ativo" name="ativo" <?php echo ($banner['ativo'] ?? 1) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="ativo">Banner Ativo</label>
            </div>
        </div>

        <div class="mb-3">
            <label for="imagem" class="form-label">Imagem do Banner <?php echo !$banner ? '*' : ''; ?></label>
            <input type="file" class="form-control" id="imagem" name="imagem" accept="image/*" <?php echo !$banner ? 'required' : ''; ?>>
            <small class="text-muted">Formatos aceitos: JPG, PNG, GIF. Tamanho máximo: 5MB</small>
        </div>

        <?php if ($banner && $banner['imagem_path']): ?>
            <div class="mb-3">
                <label class="form-label">Imagem Atual</label>
                <div>
                    <img src="<?php echo BASE_URL; ?>/public/uploaded_images/<?php echo $banner['imagem_path']; ?>" alt="Banner" style="max-width: 300px; max-height: 200px; border-radius: 5px;">
                </div>
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Banner</button>
            <a href="<?php echo ADMIN_URL; ?>/pages/banners.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>
    </form>

    <?php
    require_once __DIR__ . '/../../admin/footer.php';
} else {
    // Listagem de banners
    $stmt = $pdo->query("SELECT * FROM banners ORDER BY ordem ASC, created_at DESC");
    $banners = $stmt->fetchAll();

    require_once __DIR__ . '/../../admin/header.php';
    ?>

    <div class="mb-3">
        <a href="<?php echo ADMIN_URL; ?>/pages/banners.php?action=add" class="btn btn-primary"><i class="fas fa-plus"></i> Adicionar Novo Banner</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Imagem</th>
                    <th>Status</th>
                    <th>Ordem</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($banners)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>Nenhum banner cadastrado</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($banners as $banner): ?>
                        <tr>
                            <td><strong><?php echo $banner['id']; ?></strong></td>
                            <td><?php echo $banner['titulo']; ?></td>
                            <td>
                                <img src="<?php echo BASE_URL; ?>/public/uploaded_images/<?php echo $banner['imagem_path']; ?>" alt="Banner" style="max-width: 100px; max-height: 60px; border-radius: 3px;">
                            </td>
                            <td>
                                <?php if ($banner['ativo']): ?>
                                    <span class="badge bg-success">Ativo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $banner['ordem']; ?></td>
                            <td>
                                <a href="?action=edit&id=<?php echo $banner['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Editar</a>
                                <button onclick="confirmarExclusaoBanner(<?php echo $banner['id']; ?>)" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Deletar</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        function confirmarExclusaoBanner(id) {
            Swal.fire({
                title: 'Tem certeza?',
                text: "Esta ação não pode ser desfeita!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#FFD000',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, deletar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?action=delete&id=' + id;
                }
            });
        }
    </script>

    <?php
    require_once __DIR__ . '/../../admin/footer.php';
}
?>