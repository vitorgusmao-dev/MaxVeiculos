<?php
/**
 * MaxVeículos - Detalhes do Veículo (Site Público)
 */

require_once __DIR__ . '/../includes/config.php';

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header('Location: ' . BASE_URL . '/public/veiculos.php');
    exit;
}

// Obter veículo
$stmt = $pdo->prepare("SELECT * FROM veiculos WHERE id = ? AND ativo = TRUE");
$stmt->execute([$id]);
$veiculo = $stmt->fetch();

if (!$veiculo) {
    header('Location: ' . BASE_URL . '/public/veiculos.php');
    exit;
}

// Obter fotos do veículo
$stmt = $pdo->prepare("SELECT * FROM veiculo_fotos WHERE veiculo_id = ? ORDER BY ordem ASC");
$stmt->execute([$id]);
$fotos = $stmt->fetchAll();

// Obter veículos relacionados
$stmt = $pdo->prepare("
    SELECT * FROM veiculos 
    WHERE marca = ? AND id != ? AND ativo = TRUE
    ORDER BY created_at DESC
    LIMIT 3
");
$stmt->execute([$veiculo['marca'], $id]);
$veiculos_relacionados = $stmt->fetchAll();

// Fotos dos relacionados
$fotos_relacionados = [];
foreach ($veiculos_relacionados as $relacionado) {
    $stmt2 = $pdo->prepare("SELECT caminho_foto FROM veiculo_fotos WHERE veiculo_id = ? ORDER BY ordem ASC LIMIT 1");
    $stmt2->execute([$relacionado['id']]);
    $foto = $stmt2->fetch();
    $fotos_relacionados[$relacionado['id']] = $foto['caminho_foto'] ?? null;
}

$mensagem_whatsapp = "Olá, gostaria de saber mais sobre o " . $veiculo['marca'] . " " . $veiculo['modelo'] . " " . $veiculo['ano'] . " (" . formatar_moeda($veiculo['preco']) . ").";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $veiculo['marca']; ?> <?php echo $veiculo['modelo']; ?> - MaxVeículos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/css/lightgallery.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/css/lg-zoom.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/css/lg-thumbnail.min.css">
    <style>
        /* ============================================
           MaxVeículos - Detalhes do Veículo
           Paleta: Amarelo #FFD000, Preto #0B0B0B, Branco #FFFFFF
        ============================================ */
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

        /* HEADER (NAVBAR) - PRETO COM LOGO IMAGEM */
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
            height: 60px;
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

        /* Breadcrumb */
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 20px 0;
        }
        .breadcrumb-item a {
            color: var(--amarelo);
            text-decoration: none;
        }
        .breadcrumb-item.active {
            color: var(--preto);
        }

        /* Galeria - imagem principal com altura fixa */
        .gallery-container {
            background: var(--branco);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
            cursor: pointer;
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
        }

        .gallery-thumb {
            cursor: pointer;
            border-radius: 5px;
            overflow: hidden;
            aspect-ratio: 1;
        }

        .gallery-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s;
        }

        .gallery-thumb:hover img {
            transform: scale(1.05);
        }

        /* Info Sections */
        .info-section {
            background: var(--branco);
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
        }

        .info-section h3 {
            font-size: 24px;
            font-weight: bold;
            color: var(--amarelo);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid var(--amarelo);
        }

        .spec-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .spec-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: var(--cinza-claro);
            border-radius: 8px;
        }

        .spec-icon {
            font-size: 24px;
            color: var(--amarelo);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .spec-info h6 {
            font-weight: 600;
            color: #999;
            font-size: 12px;
            margin-bottom: 3px;
        }

        .spec-info p {
            color: var(--preto);
            font-weight: bold;
            font-size: 16px;
            margin: 0;
        }

        .preco-grande {
            font-size: 32px;
            font-weight: bold;
            color: var(--amarelo);
            margin-bottom: 20px;
            text-align: center;
            padding: 20px;
            background: var(--cinza-claro);
            border-radius: 8px;
        }

        /* Botões */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .btn-custom {
            padding: 12px 25px;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            border-radius: 8px;
            border: none;
        }

        .btn-whatsapp {
            background: #25d366;
            color: white;
        }

        .btn-whatsapp:hover {
            background: #20ba5b;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(37, 211, 102, 0.3);
            color: white;
        }

        .descricao-text {
            line-height: 1.8;
            color: #666;
            font-size: 16px;
        }

        /* Veículos Relacionados */
        .relacionados-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .relacionado-card {
            background: var(--branco);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }

        .relacionado-card:hover {
            transform: translateY(-5px);
        }

        .relacionado-imagem {
            width: 100%;
            height: 180px;
            overflow: hidden;
        }

        .relacionado-imagem img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .relacionado-info {
            padding: 15px;
        }

        .relacionado-nome {
            font-weight: bold;
            color: var(--preto);
            margin-bottom: 8px;
        }

        .relacionado-preco {
            color: var(--amarelo);
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
        }

        .relacionado-link {
            display: block;
            text-align: center;
            background: var(--cinza-claro);
            padding: 10px;
            border-radius: 5px;
            color: var(--amarelo);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .relacionado-link:hover {
            background-color: var(--amarelo);
            color: var(--preto);
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .logo-img {
                height: 45px;
            }
            .main-image {
                height: 250px;
            }
            .preco-grande {
                font-size: 24px;
            }
        }

        /* Forçar esconder botão de download no lightGallery (fallback) */
        .lg-download {
            display: none !important;
        }
    </style>
</head>
<body>

    <!-- CABEÇALHO COM LOGO IMAGEM -->
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
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/public/veiculos.php">Veículos</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="container" style="padding: 40px 0;">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/public/veiculos.php">Veículos</a></li>
                <li class="breadcrumb-item active"><?php echo $veiculo['marca']; ?> <?php echo $veiculo['modelo']; ?></li>
            </ol>
        </nav>

        <div class="row">
            <!-- Coluna Principal -->
            <div class="col-lg-8">
                <!-- Galeria -->
                <div class="gallery-container">
                    <?php if (!empty($fotos)): ?>
                        <div id="lightgallery">
                            <?php foreach ($fotos as $index => $foto): ?>
                                <a href="<?php echo BASE_URL; ?>/public/uploaded_images/<?php echo $foto['caminho_foto']; ?>" data-lg-size="1200-675" data-sub-html="<h4><?php echo $veiculo['marca']; ?> <?php echo $veiculo['modelo']; ?></h4>">
                                    <?php if ($index === 0): ?>
                                        <img src="<?php echo BASE_URL; ?>/public/uploaded_images/<?php echo $foto['caminho_foto']; ?>" alt="<?php echo $veiculo['modelo']; ?>" class="main-image">
                                    <?php else: ?>
                                        <!-- As thumbnails serão geradas dinamicamente pelo lightGallery, mas podemos manter os links -->
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                            <!-- Thumbnails das outras fotos (visíveis na galeria) -->
                            <div class="gallery">
                                <?php foreach ($fotos as $foto): ?>
                                    <a href="<?php echo BASE_URL; ?>/public/uploaded_images/<?php echo $foto['caminho_foto']; ?>" data-lg-size="1200-675" class="gallery-thumb">
                                        <img src="<?php echo BASE_URL; ?>/public/uploaded_images/<?php echo $foto['caminho_foto']; ?>" alt="<?php echo $veiculo['modelo']; ?>">
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <img src="https://via.placeholder.com/800x600?text=Sem+Fotos+Disponíveis" alt="Sem foto" class="main-image">
                    <?php endif; ?>
                </div>

                <!-- Descrição -->
                <div class="info-section">
                    <h3><i class="fas fa-info-circle"></i> Descrição</h3>
                    <p class="descricao-text">
                        <?php echo nl2br(htmlspecialchars($veiculo['descricao'] ?? 'Sem descrição disponível')); ?>
                    </p>
                </div>

                <!-- Especificações -->
                <div class="info-section">
                    <h3><i class="fas fa-cogs"></i> Especificações</h3>
                    <div class="spec-grid">
                        <div class="spec-item">
                            <div class="spec-icon"><i class="fas fa-calendar-alt"></i></div>
                            <div class="spec-info"><h6>Ano</h6><p><?php echo $veiculo['ano']; ?></p></div>
                        </div>
                        <div class="spec-item">
                            <div class="spec-icon"><i class="fas fa-tachometer-alt"></i></div>
                            <div class="spec-info"><h6>Quilometragem</h6><p><?php echo number_format($veiculo['quilometragem'], 0, ',', '.'); ?> km</p></div>
                        </div>
                        <div class="spec-item">
                            <div class="spec-icon"><i class="fas fa-palette"></i></div>
                            <div class="spec-info"><h6>Cor</h6><p><?php echo $veiculo['cor'] ?? 'Não informada'; ?></p></div>
                        </div>
                        <div class="spec-item">
                            <div class="spec-icon"><i class="fas fa-exchange-alt"></i></div>
                            <div class="spec-info"><h6>Câmbio</h6><p><?php echo $veiculo['cambio'] ?? 'Não informado'; ?></p></div>
                        </div>
                        <div class="spec-item">
                            <div class="spec-icon"><i class="fas fa-gas-pump"></i></div>
                            <div class="spec-info"><h6>Combustível</h6><p><?php echo $veiculo['combustivel'] ?? 'Não informado'; ?></p></div>
                        </div>
                        <div class="spec-item">
                            <div class="spec-icon"><i class="fas fa-barcode"></i></div>
                            <div class="spec-info"><h6>ID do Veículo</h6><p>#<?php echo $veiculo['id']; ?></p></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="info-section">
                    <div class="preco-grande"><?php echo formatar_moeda($veiculo['preco']); ?></div>
                    <h4 style="text-align: center; margin-bottom: 15px;"><?php echo $veiculo['marca']; ?> <?php echo $veiculo['modelo']; ?> <?php echo $veiculo['ano']; ?></h4>
                    <div class="action-buttons">
                        <a href="https://wa.me/?text=<?php echo urlencode($mensagem_whatsapp); ?>" target="_blank" class="btn-custom btn-whatsapp" style="width: 100%;">
                            <i class="fab fa-whatsapp"></i> Solicitar via WhatsApp
                        </a>
                    </div>
                    <hr>
                    <h5 style="margin-bottom: 15px; color: var(--amarelo);">Informações de Contato</h5>
                    <p><i class="fas fa-phone" style="color: var(--amarelo); width: 20px;"></i> <strong>(27) 1234-5678</strong></p>
                    <p><i class="fas fa-envelope" style="color: var(--amarelo); width: 20px;"></i> <strong>contato@maxveiculos.com.br</strong></p>
                    <p><i class="fas fa-map-marker-alt" style="color: var(--amarelo); width: 20px;"></i> <strong>Cachoeiro de Itapemirim, ES</strong></p>
                </div>

                <div class="info-section" style="background: rgba(255, 208, 0, 0.1); border: 1px solid var(--amarelo);">
                    <h5 style="color: var(--amarelo);"><i class="fas fa-question-circle"></i> Tem dúvidas?</h5>
                    <p>Entre em contato via WhatsApp e tire suas dúvidas.</p>
                    <a href="https://wa.me/?text=<?php echo urlencode($mensagem_whatsapp); ?>" target="_blank" class="btn-custom btn-whatsapp" style="width: 100%; justify-content: center;">
                        <i class="fab fa-whatsapp"></i> Conversar
                    </a>
                </div>
            </div>
        </div>

        <!-- Veículos Relacionados -->
        <?php if (!empty($veiculos_relacionados)): ?>
            <hr style="margin: 50px 0;">
            <h3 style="margin-bottom: 30px; color: var(--amarelo);"><i class="fas fa-car"></i> Outros Veículos da Marca</h3>
            <div class="relacionados-grid">
                <?php foreach ($veiculos_relacionados as $relacionado): ?>
                    <div class="relacionado-card">
                        <div class="relacionado-imagem">
                            <?php if ($fotos_relacionados[$relacionado['id']] ?? null): ?>
                                <img src="<?php echo BASE_URL; ?>/public/uploaded_images/<?php echo $fotos_relacionados[$relacionado['id']]; ?>" alt="<?php echo $relacionado['modelo']; ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/400x250?text=Sem+Foto" alt="Sem foto">
                            <?php endif; ?>
                        </div>
                        <div class="relacionado-info">
                            <div class="relacionado-nome"><?php echo $relacionado['marca']; ?> <?php echo $relacionado['modelo']; ?></div>
                            <div class="relacionado-preco"><?php echo formatar_moeda($relacionado['preco']); ?></div>
                            <a href="<?php echo BASE_URL; ?>/public/detalhes.php?id=<?php echo $relacionado['id']; ?>" class="relacionado-link">Ver Detalhes</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/lightgallery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/plugins/zoom/lg-zoom.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightgallery@2.7.2/plugins/thumbnail/lg-thumbnail.min.js"></script>
    <script>
        lightGallery(document.getElementById('lightgallery'), {
            selector: 'a',
            plugins: [lgZoom, lgThumbnail],
            licenseKey: 'D4E2E2D9F4D414D4EDE2E2D9F4D414',
            thumbnail: true,
            zoom: true,
            speed: 500,
            download: false,
            counter: true,
            mousewheel: true,
            closable: true,
            // Forçar esconder botão de download também via plugin
            hideDownload: true,
            // Ajustar tamanho da imagem para não estourar
            width: '100%',
            height: '100%',
            maxWidth: '90vw',
            maxHeight: '90vh'
        });
    </script>
</body>
</html>