<?php
/**
 * MaxVeículos - Listagem de Veículos (Site Público)
 */

require_once __DIR__ . '/../includes/config.php';

// Obter filtros
$marca = sanitizar($_GET['marca'] ?? '');
$modelo = sanitizar($_GET['modelo'] ?? '');
$ano = sanitizar($_GET['ano'] ?? '');
$preco_min = sanitizar($_GET['preco_min'] ?? '');
$preco_max = sanitizar($_GET['preco_max'] ?? '');
$pagina = (int)($_GET['pagina'] ?? 1);

// Paginação
$itens_por_pagina = 12;
$offset = ($pagina - 1) * $itens_por_pagina;

// Construir query
$where = " WHERE ativo = TRUE";
$params = [];

if (!empty($marca)) {
    $where .= " AND marca LIKE ?";
    $params[] = "%$marca%";
}
if (!empty($modelo)) {
    $where .= " AND modelo LIKE ?";
    $params[] = "%$modelo%";
}
if (!empty($ano)) {
    $where .= " AND ano = ?";
    $params[] = $ano;
}
if (!empty($preco_min)) {
    $where .= " AND preco >= ?";
    $params[] = $preco_min;
}
if (!empty($preco_max)) {
    $where .= " AND preco <= ?";
    $params[] = $preco_max;
}

// Obter total de veículos
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM veiculos $where");
$stmt->execute($params);
$total = $stmt->fetchColumn();
$total_paginas = ceil($total / $itens_por_pagina);

// Obter veículos
$stmt = $pdo->prepare("
    SELECT * FROM veiculos $where
    ORDER BY created_at DESC
    LIMIT $offset, $itens_por_pagina
");
$stmt->execute($params);
$veiculos = $stmt->fetchAll();

// Obter fotos para cada veículo
$fotos_veiculos = [];
foreach ($veiculos as $veiculo) {
    $stmt2 = $pdo->prepare("SELECT caminho_foto FROM veiculo_fotos WHERE veiculo_id = ? ORDER BY ordem ASC LIMIT 1");
    $stmt2->execute([$veiculo['id']]);
    $foto = $stmt2->fetch();
    $fotos_veiculos[$veiculo['id']] = $foto['caminho_foto'] ?? null;
}

// Obter marcas únicas para o filtro
$stmt_marcas = $pdo->query("SELECT DISTINCT marca FROM veiculos WHERE ativo = TRUE ORDER BY marca");
$marcas = $stmt_marcas->fetchAll(PDO::FETCH_COLUMN);

// Obter anos únicos para o filtro
$stmt_anos = $pdo->query("SELECT DISTINCT ano FROM veiculos WHERE ativo = TRUE ORDER BY ano DESC");
$anos = $stmt_anos->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veículos - MaxVeículos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* PALETA DE CORES MAXVEÍCULOS */
        :root {
            --amarelo: #FFD000;
            --preto: #0B0B0B;
            --branco: #FFFFFF;
            --cinza-claro: #f5f5f5;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--cinza-claro);
            color: var(--preto);
        }

        /* HEADER (NAVBAR) - PRETO COM LOGO */
        .navbar {
            background-color: var(--preto) !important;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-img {
            height: 50px;
            width: auto;
        }

        .nav-link {
            color: var(--branco) !important;
            font-weight: 500;
            margin: 0 10px;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: var(--amarelo) !important;
        }

        /* PAGE HEADER (TÍTULO) */
        .page-header {
            background-color: var(--preto);
            color: var(--branco);
            padding: 40px 0;
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        /* FILTROS LATERAL */
        .filtros-section {
            background: var(--branco);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .filtros-section h4 {
            font-weight: bold;
            margin-bottom: 20px;
            color: var(--amarelo);
            border-bottom: 2px solid var(--amarelo);
            padding-bottom: 10px;
        }

        .filter-group {
            margin-bottom: 20px;
        }

        .filter-group label {
            font-weight: 600;
            color: var(--preto);
            margin-bottom: 8px;
            display: block;
        }

        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            border-color: var(--amarelo);
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 208, 0, 0.1);
        }

        .btn-filtrar {
            width: 100%;
            background-color: var(--amarelo);
            color: var(--preto);
            border: none;
            padding: 10px;
            border-radius: 5px;
            font-weight: 600;
            transition: transform 0.2s;
            margin-bottom: 10px;
        }

        .btn-filtrar:hover {
            background-color: #e6bc00;
            transform: translateY(-2px);
        }

        .btn-limpar {
            width: 100%;
            background: #f0f0f0;
            color: var(--preto);
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            font-weight: 600;
            text-align: center;
            display: inline-block;
            text-decoration: none;
        }

        .btn-limpar:hover {
            background: #e0e0e0;
        }

        /* GRID DE VEÍCULOS */
        .veiculos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .veiculo-card {
            background: var(--branco);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .veiculo-card:hover {
            transform: translateY(-10px);
        }

        .veiculo-imagem {
            height: 200px;
            overflow: hidden;
        }

        .veiculo-imagem img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .veiculo-card:hover .veiculo-imagem img {
            transform: scale(1.05);
        }

        .veiculo-info {
            padding: 15px;
            display: flex;
            flex-direction: column;
        }

        .veiculo-nome {
            font-weight: bold;
            font-size: 18px;
            color: var(--preto);
            margin-bottom: 8px;
        }

        .veiculo-especificacoes {
            font-size: 13px;
            color: #666;
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .veiculo-preco {
            font-size: 22px;
            font-weight: bold;
            color: var(--amarelo);
            margin-bottom: 15px;
        }

        .veiculo-acoes {
            display: flex;
            gap: 8px;
        }

        .btn-detalhes {
            background-color: var(--amarelo);
            color: var(--preto);
            text-align: center;
            padding: 8px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            flex: 1;
        }

        .btn-detalhes:hover {
            background-color: #e6bc00;
            color: var(--preto);
        }

        .btn-whatsapp {
            background-color: #25D366;
            color: white;
            text-align: center;
            padding: 8px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            flex: 1;
        }

        .btn-whatsapp:hover {
            background-color: #20ba5b;
        }

        /* PAGINAÇÃO */
        .pagination {
            justify-content: center;
        }
        .pagination .page-link {
            color: var(--preto);
        }
        .pagination .page-item.active .page-link {
            background-color: var(--amarelo);
            border-color: var(--amarelo);
            color: var(--preto);
        }

        /* RESULTADOS */
        .results-info {
            background: var(--branco);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .results-info strong {
            color: var(--amarelo);
        }
    </style>
</head>
<body>

    <!-- CABEÇALHO COM LOGO -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <img src="<?php echo BASE_URL; ?>/public/assets/images/Logo-Fundo-Preto.png" alt="MaxVeículos" class="logo-img">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/public/veiculos.php">Veículos</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/#contato">Contato</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- TÍTULO DA PÁGINA -->
    <div class="page-header">
        <div class="container">
            <h1>Nossos Veículos</h1>
            <p>Explore nossa grande variedade de carros de qualidade</p>
        </div>
    </div>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="container" style="margin-bottom: 60px;">
        <div class="row">
            <!-- FILTROS LATERAL -->
            <div class="col-md-3 mb-4">
                <div class="filtros-section">
                    <h4><i class="fas fa-filter"></i> Filtros</h4>
                    <form method="GET" action="">
                        <div class="filter-group">
                            <label>Marca</label>
                            <select name="marca">
                                <option value="">Todas</option>
                                <?php foreach ($marcas as $marc): ?>
                                    <option value="<?php echo $marc; ?>" <?php echo $marca === $marc ? 'selected' : ''; ?>><?php echo $marc; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Modelo</label>
                            <input type="text" name="modelo" value="<?php echo $modelo; ?>" placeholder="Digite o modelo">
                        </div>
                        <div class="filter-group">
                            <label>Ano</label>
                            <select name="ano">
                                <option value="">Todos</option>
                                <?php foreach ($anos as $an): ?>
                                    <option value="<?php echo $an; ?>" <?php echo $ano == $an ? 'selected' : ''; ?>><?php echo $an; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Preço Mínimo</label>
                            <input type="number" name="preco_min" value="<?php echo $preco_min; ?>" placeholder="R$ 0">
                        </div>
                        <div class="filter-group">
                            <label>Preço Máximo</label>
                            <input type="number" name="preco_max" value="<?php echo $preco_max; ?>" placeholder="R$ 999.999">
                        </div>
                        <button type="submit" class="btn-filtrar"><i class="fas fa-search"></i> Filtrar</button>
                        <a href="<?php echo BASE_URL; ?>/public/veiculos.php" class="btn-limpar"><i class="fas fa-redo"></i> Limpar</a>
                    </form>
                </div>
            </div>

            <!-- LISTAGEM DE VEÍCULOS -->
            <div class="col-md-9">
                <?php if ($total > 0): ?>
                    <div class="results-info">
                        Encontrados <strong><?php echo $total; ?></strong> veículo(s)
                        <?php if ($marca): ?> da marca <strong><?php echo $marca; ?></strong><?php endif; ?>
                    </div>
                    <div class="veiculos-grid">
                        <?php foreach ($veiculos as $veiculo): ?>
                            <div class="veiculo-card">
                                <div class="veiculo-imagem">
                                    <?php if (!empty($fotos_veiculos[$veiculo['id']])): ?>
                                        <img src="<?php echo BASE_URL; ?>/public/uploaded_images/<?php echo $fotos_veiculos[$veiculo['id']]; ?>" alt="<?php echo $veiculo['modelo']; ?>">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/400x250?text=Sem+Foto" alt="Sem foto">
                                    <?php endif; ?>
                                </div>
                                <div class="veiculo-info">
                                    <div class="veiculo-nome"><?php echo $veiculo['marca']; ?> <?php echo $veiculo['modelo']; ?></div>
                                    <div class="veiculo-especificacoes">
                                        <span><i class="fas fa-calendar"></i> <?php echo $veiculo['ano']; ?></span>
                                        <span><i class="fas fa-tachometer-alt"></i> <?php echo number_format($veiculo['quilometragem'], 0, ',', '.'); ?> km</span>
                                    </div>
                                    <div class="veiculo-preco">R$ <?php echo number_format($veiculo['preco'], 2, ',', '.'); ?></div>
                                    <div class="veiculo-acoes">
                                        <a href="<?php echo BASE_URL; ?>/public/detalhes.php?id=<?php echo $veiculo['id']; ?>" class="btn-detalhes"><i class="fas fa-eye"></i> Detalhes</a>
                                        <a href="https://wa.me/?text=Olá, gostaria de saber mais sobre o <?php echo $veiculo['marca'] . ' ' . $veiculo['modelo'] . ' ' . $veiculo['ano']; ?>" target="_blank" class="btn-whatsapp"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($total_paginas > 1): ?>
                        <nav>
                            <ul class="pagination">
                                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                    <li class="page-item <?php echo $i === $pagina ? 'active' : ''; ?>">
                                        <a class="page-link" href="?marca=<?php echo urlencode($marca); ?>&modelo=<?php echo urlencode($modelo); ?>&ano=<?php echo $ano; ?>&preco_min=<?php echo $preco_min; ?>&preco_max=<?php echo $preco_max; ?>&pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="alert alert-info text-center">Nenhum veículo encontrado. Tente outros filtros.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>