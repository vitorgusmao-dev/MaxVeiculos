<?php
/**
 * MaxVeículos - Gestão de Veículos (CRUD)
 * Com funcionalidade de definir foto de capa
 */

$titulo_pagina = 'Gestão de Veículos';
require_once __DIR__ . '/../../includes/config.php';

// Processar ações
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Processar definição de foto de capa
if ($action === 'set_capa' && isset($_GET['foto_id']) && $id) {
    $foto_id = (int)$_GET['foto_id'];
    $veiculo_id = (int)$id;

    try {
        $pdo->beginTransaction();

        // Resetar a ordem de todas as fotos do veículo (incrementar)
        $stmt = $pdo->prepare("UPDATE veiculo_fotos SET ordem = ordem + 1 WHERE veiculo_id = ?");
        $stmt->execute([$veiculo_id]);

        // Definir a foto escolhida com ordem 0 (capa)
        $stmt = $pdo->prepare("UPDATE veiculo_fotos SET ordem = 0 WHERE id = ? AND veiculo_id = ?");
        $stmt->execute([$foto_id, $veiculo_id]);

        // Reordenar as demais fotos para ter ordem sequencial (1,2,3...)
        $stmt = $pdo->prepare("SELECT id FROM veiculo_fotos WHERE veiculo_id = ? AND id != ? ORDER BY ordem ASC");
        $stmt->execute([$veiculo_id, $foto_id]);
        $outras_fotos = $stmt->fetchAll();
        $nova_ordem = 1;
        foreach ($outras_fotos as $foto) {
            $stmt2 = $pdo->prepare("UPDATE veiculo_fotos SET ordem = ? WHERE id = ?");
            $stmt2->execute([$nova_ordem, $foto['id']]);
            $nova_ordem++;
        }

        registrar_atividade($pdo, 'SET_CAPA', 'Foto ID ' . $foto_id . ' definida como capa do veículo ID ' . $veiculo_id);
        definir_mensagem('success', 'Foto de capa atualizada com sucesso!');
        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        definir_mensagem('danger', 'Erro ao definir capa: ' . $e->getMessage());
    }
    redirecionar(ADMIN_URL . '/pages/veiculos.php?action=edit&id=' . $veiculo_id);
}

// Processar exclusão de veículo
if ($action === 'delete' && $id) {
    try {
        $pdo->beginTransaction();
        // Obter as fotos do veículo
        $stmt = $pdo->prepare("SELECT caminho_foto FROM veiculo_fotos WHERE veiculo_id = ?");
        $stmt->execute([$id]);
        $fotos = $stmt->fetchAll();

        // Deletar fotos do servidor
        foreach ($fotos as $foto) {
            $caminho = UPLOADS_PATH . $foto['caminho_foto'];
            if (file_exists($caminho)) {
                unlink($caminho);
            }
        }

        // Deletar veículo (as fotos serão deletadas em cascata)
        $stmt = $pdo->prepare("DELETE FROM veiculos WHERE id = ?");
        $stmt->execute([$id]);

        registrar_atividade($pdo, 'DELETE_VEICULO', 'Veículo ID ' . $id . ' deletado');
        definir_mensagem('success', 'Veículo deletado com sucesso!');
        $pdo->commit();
        redirecionar(ADMIN_URL . '/pages/veiculos.php');
    } catch (Exception $e) {
        $pdo->rollBack();
        definir_mensagem('danger', 'Erro ao deletar veículo: ' . $e->getMessage());
        redirecionar(ADMIN_URL . '/pages/veiculos.php');
    }
}

// Processar exclusão de foto individual
if ($action === 'delete_foto' && isset($_GET['foto_id'])) {
    $foto_id = (int)$_GET['foto_id'];
    try {
        $stmt = $pdo->prepare("SELECT veiculo_id, caminho_foto FROM veiculo_fotos WHERE id = ?");
        $stmt->execute([$foto_id]);
        $foto = $stmt->fetch();
        if ($foto) {
            $caminho = UPLOADS_PATH . $foto['caminho_foto'];
            if (file_exists($caminho)) {
                unlink($caminho);
            }
            $stmt = $pdo->prepare("DELETE FROM veiculo_fotos WHERE id = ?");
            $stmt->execute([$foto_id]);
            registrar_atividade($pdo, 'DELETE_FOTO', 'Foto ID ' . $foto_id . ' deletada do veículo ID ' . $foto['veiculo_id']);
            definir_mensagem('success', 'Foto removida com sucesso!');
        }
        redirecionar(ADMIN_URL . '/pages/veiculos.php?action=edit&id=' . $foto['veiculo_id']);
    } catch (Exception $e) {
        definir_mensagem('danger', 'Erro ao deletar foto: ' . $e->getMessage());
        redirecionar(ADMIN_URL . '/pages/veiculos.php');
    }
}

// Se for adicionar ou editar, mostrar formulário
if ($action === 'add' || $action === 'edit') {
    $veiculo = null;
    $fotos = [];

    if ($action === 'edit' && $id) {
        $stmt = $pdo->prepare("SELECT * FROM veiculos WHERE id = ?");
        $stmt->execute([$id]);
        $veiculo = $stmt->fetch();

        if (!$veiculo) {
            definir_mensagem('danger', 'Veículo não encontrado!');
            redirecionar(ADMIN_URL . '/pages/veiculos.php');
        }

        // Obter fotos ordenadas por ordem (capa será ordem 0)
        $stmt = $pdo->prepare("SELECT * FROM veiculo_fotos WHERE veiculo_id = ? ORDER BY ordem ASC");
        $stmt->execute([$id]);
        $fotos = $stmt->fetchAll();
    }

    // Processar formulário (salvar veículo)
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $marca = sanitizar($_POST['marca'] ?? '');
        $modelo = sanitizar($_POST['modelo'] ?? '');
        $ano = sanitizar($_POST['ano'] ?? '');
        $preco = sanitizar($_POST['preco'] ?? '');
        $quilometragem = sanitizar($_POST['quilometragem'] ?? '');
        $cor = sanitizar($_POST['cor'] ?? '');
        $cambio = sanitizar($_POST['cambio'] ?? '');
        $combustivel = sanitizar($_POST['combustivel'] ?? '');
        $descricao = $_POST['descricao'] ?? '';
        $destaque = isset($_POST['destaque']) ? 1 : 0;

        if (empty($marca) || empty($modelo) || empty($ano) || empty($preco)) {
            definir_mensagem('danger', 'Preencha todos os campos obrigatórios!');
        } else {
            try {
                if ($action === 'add') {
                    $stmt = $pdo->prepare("
                        INSERT INTO veiculos (marca, modelo, ano, preco, quilometragem, cor, cambio, combustivel, descricao, destaque)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $marca, $modelo, $ano, $preco, $quilometragem,
                        $cor, $cambio, $combustivel, $descricao, $destaque
                    ]);
                    $veiculo_id = $pdo->lastInsertId();
                    $mensagem_sucesso = 'Veículo adicionado com sucesso!';
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE veiculos 
                        SET marca = ?, modelo = ?, ano = ?, preco = ?, quilometragem = ?,
                            cor = ?, cambio = ?, combustivel = ?, descricao = ?, destaque = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $marca, $modelo, $ano, $preco, $quilometragem,
                        $cor, $cambio, $combustivel, $descricao, $destaque, $id
                    ]);
                    $veiculo_id = $id;
                    $mensagem_sucesso = 'Veículo atualizado com sucesso!';
                }

                // Processar upload de novas fotos
                if (isset($_FILES['fotos']) && !empty($_FILES['fotos']['name'][0])) {
                    // Obter a maior ordem atual
                    $stmt = $pdo->prepare("SELECT MAX(ordem) as max_ordem FROM veiculo_fotos WHERE veiculo_id = ?");
                    $stmt->execute([$veiculo_id]);
                    $max_ordem = (int)$stmt->fetchColumn();
                    $nova_ordem = $max_ordem + 1;

                    for ($i = 0; $i < count($_FILES['fotos']['name']); $i++) {
                        if ($_FILES['fotos']['error'][$i] === 0) {
                            $arquivo = [
                                'name' => $_FILES['fotos']['name'][$i],
                                'tmp_name' => $_FILES['fotos']['tmp_name'][$i]
                            ];
                            $nome_arquivo = fazer_upload_arquivo($arquivo, UPLOADS_PATH);
                            if ($nome_arquivo) {
                                $stmt = $pdo->prepare("
                                    INSERT INTO veiculo_fotos (veiculo_id, caminho_foto, ordem)
                                    VALUES (?, ?, ?)
                                ");
                                $stmt->execute([$veiculo_id, $nome_arquivo, $nova_ordem++]);
                            }
                        }
                    }
                }

                registrar_atividade($pdo, $action === 'add' ? 'ADD_VEICULO' : 'EDIT_VEICULO', 'Veículo ID ' . $veiculo_id . ' - ' . $modelo);
                definir_mensagem('success', $mensagem_sucesso);
                redirecionar(ADMIN_URL . '/pages/veiculos.php?action=edit&id=' . $veiculo_id);
            } catch (Exception $e) {
                definir_mensagem('danger', 'Erro ao salvar veículo: ' . $e->getMessage());
            }
        }
    }

    require_once __DIR__ . '/../../admin/header.php';
    ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="marca" class="form-label">Marca *</label>
                    <input type="text" class="form-control" id="marca" name="marca" value="<?php echo $veiculo['marca'] ?? ''; ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="modelo" class="form-label">Modelo *</label>
                    <input type="text" class="form-control" id="modelo" name="modelo" value="<?php echo $veiculo['modelo'] ?? ''; ?>" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="ano" class="form-label">Ano *</label>
                    <input type="number" class="form-control" id="ano" name="ano" value="<?php echo $veiculo['ano'] ?? ''; ?>" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="preco" class="form-label">Preço *</label>
                    <input type="number" step="0.01" class="form-control" id="preco" name="preco" value="<?php echo $veiculo['preco'] ?? ''; ?>" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="quilometragem" class="form-label">Quilometragem</label>
                    <input type="number" class="form-control" id="quilometragem" name="quilometragem" value="<?php echo $veiculo['quilometragem'] ?? ''; ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="cor" class="form-label">Cor</label>
                    <input type="text" class="form-control" id="cor" name="cor" value="<?php echo $veiculo['cor'] ?? ''; ?>">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="cambio" class="form-label">Câmbio</label>
                    <select class="form-control" id="cambio" name="cambio">
                        <option value="">Selecione...</option>
                        <option value="Manual" <?php echo ($veiculo['cambio'] ?? '') === 'Manual' ? 'selected' : ''; ?>>Manual</option>
                        <option value="Automático" <?php echo ($veiculo['cambio'] ?? '') === 'Automático' ? 'selected' : ''; ?>>Automático</option>
                        <option value="CVT" <?php echo ($veiculo['cambio'] ?? '') === 'CVT' ? 'selected' : ''; ?>>CVT</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="combustivel" class="form-label">Combustível</label>
                    <select class="form-control" id="combustivel" name="combustivel">
                        <option value="">Selecione...</option>
                        <option value="Gasolina" <?php echo ($veiculo['combustivel'] ?? '') === 'Gasolina' ? 'selected' : ''; ?>>Gasolina</option>
                        <option value="Diesel" <?php echo ($veiculo['combustivel'] ?? '') === 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
                        <option value="Álcool" <?php echo ($veiculo['combustivel'] ?? '') === 'Álcool' ? 'selected' : ''; ?>>Álcool</option>
                        <option value="Flex" <?php echo ($veiculo['combustivel'] ?? '') === 'Flex' ? 'selected' : ''; ?>>Flex</option>
                        <option value="Elétrico" <?php echo ($veiculo['combustivel'] ?? '') === 'Elétrico' ? 'selected' : ''; ?>>Elétrico</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="destaque" name="destaque" <?php echo ($veiculo['destaque'] ?? 0) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="destaque">Marcar como destaque</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="descricao" class="form-label">Descrição</label>
            <textarea class="form-control" id="descricao" name="descricao" rows="4"><?php echo $veiculo['descricao'] ?? ''; ?></textarea>
        </div>

        <div class="mb-3">
            <label for="fotos" class="form-label">Adicionar Fotos</label>
            <input type="file" class="form-control" id="fotos" name="fotos[]" multiple accept="image/*">
            <small class="text-muted">Selecione múltiplas imagens (JPG, PNG, GIF).</small>
        </div>

        <!-- Listagem de fotos com opção de definir capa e excluir -->
        <?php if (!empty($fotos)): ?>
            <div class="mb-3">
                <label class="form-label">Fotos do Veículo</label>
                <div class="row">
                    <?php foreach ($fotos as $foto): ?>
                        <div class="col-md-3 mb-3">
                            <div class="card position-relative">
                                <img src="<?php echo BASE_URL; ?>/public/uploaded_images/<?php echo $foto['caminho_foto']; ?>" class="card-img-top" alt="Foto do veículo" style="height: 150px; object-fit: cover;">
                                <div class="card-body p-2 text-center">
                                    <?php if ($foto['ordem'] == 0): ?>
                                        <span class="badge bg-warning text-dark mb-1"><i class="fas fa-star"></i> Capa</span>
                                    <?php else: ?>
                                        <a href="?action=set_capa&id=<?php echo $veiculo['id']; ?>&foto_id=<?php echo $foto['id']; ?>" class="btn btn-sm btn-outline-warning mb-1" onclick="return confirm('Definir esta foto como capa?')">
                                            <i class="fas fa-crown"></i> Definir como Capa
                                        </a>
                                    <?php endif; ?>
                                    <a href="?action=delete_foto&foto_id=<?php echo $foto['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remover esta foto?')">
                                        <i class="fas fa-trash"></i> Remover
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <small class="text-muted">A foto marcada como "Capa" será exibida na página inicial e na listagem de veículos.</small>
            </div>
        <?php endif; ?>

        <div class="mb-3">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar Veículo</button>
            <a href="<?php echo ADMIN_URL; ?>/pages/veiculos.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Voltar</a>
        </div>
    </form>

    <?php
    require_once __DIR__ . '/../../admin/footer.php';
} else {
    // Listagem de veículos (com paginação)
    $pagina = $_GET['pagina'] ?? 1;
    $itens_por_pagina = 10;
    $offset = ($pagina - 1) * $itens_por_pagina;

    $stmt = $pdo->query("SELECT COUNT(*) as total FROM veiculos");
    $total = $stmt->fetchColumn();
    $total_paginas = ceil($total / $itens_por_pagina);

    $stmt = $pdo->query("SELECT * FROM veiculos ORDER BY created_at DESC LIMIT $offset, $itens_por_pagina");
    $veiculos = $stmt->fetchAll();

    require_once __DIR__ . '/../../admin/header.php';
    ?>

    <div class="mb-3">
        <a href="<?php echo ADMIN_URL; ?>/pages/veiculos.php?action=add" class="btn btn-primary"><i class="fas fa-plus"></i> Adicionar Novo Veículo</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr><th>ID</th><th>Marca/Modelo</th><th>Ano</th><th>Preço</th><th>Destaque</th><th>Ações</th></tr>
            </thead>
            <tbody>
                <?php if (empty($veiculos)): ?>
                    <tr><td colspan="6" class="text-center">Nenhum veículo cadastrado</td></tr>
                <?php else: ?>
                    <?php foreach ($veiculos as $veiculo): ?>
                        <tr>
                            <td><?php echo $veiculo['id']; ?></td>
                            <td><?php echo $veiculo['marca']; ?> <?php echo $veiculo['modelo']; ?></td>
                            <td><?php echo $veiculo['ano']; ?></td>
                            <td><?php echo formatar_moeda($veiculo['preco']); ?></td>
                            <td><?php echo $veiculo['destaque'] ? '<span class="badge bg-success">Sim</span>' : '<span class="badge bg-secondary">Não</span>'; ?></td>
                            <td>
                                <a href="?action=edit&id=<?php echo $veiculo['id']; ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Editar</a>
                                <button onclick="confirmarExclusaoVeiculo(<?php echo $veiculo['id']; ?>)" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i> Deletar</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_paginas > 1): ?>
        <nav><ul class="pagination justify-content-center"><?php for($i=1;$i<=$total_paginas;$i++): ?><li class="page-item <?php echo $i==$pagina?'active':''; ?>"><a class="page-link" href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a></li><?php endfor; ?></ul></nav>
    <?php endif; ?>

    <script>
        function confirmarExclusaoVeiculo(id) {
            Swal.fire({
                title: 'Tem certeza?',
                text: "Este veículo e todas as suas fotos serão removidos!",
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