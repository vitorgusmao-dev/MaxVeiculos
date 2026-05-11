<?php
/**
 * ===========================================================================
 * MaxVeículos - Página Inicial (Site Público)
 * ===========================================================================
 * Este arquivo é a página principal do site. Ele exibe:
 * - Banners rotativos (slider)
 * - Veículos em destaque (limitado a 6)
 * - Rodapé com informações de contato
 * ===========================================================================
 */

// Inclui o arquivo de configuração (conexão com banco, constantes, funções)
require_once __DIR__ . '/../includes/config.php';

// =========================================================================
// 1. CONSULTAS AO BANCO DE DADOS
// =========================================================================

// Busca todos os banners ativos, ordenados pelo campo 'ordem'
$stmt = $pdo->query("SELECT * FROM banners WHERE ativo = TRUE ORDER BY ordem ASC");
$banners = $stmt->fetchAll();

// Busca os 6 veículos mais recentes que estão com destaque = TRUE e ativo = TRUE
$stmt = $pdo->query("
    SELECT * FROM veiculos 
    WHERE destaque = TRUE AND ativo = TRUE 
    ORDER BY created_at DESC 
    LIMIT 6
");
$veiculos_destaque = $stmt->fetchAll();

// Para cada veículo, busca a primeira foto (ordem ASC) para exibir no card
$fotos_veiculos = [];
foreach ($veiculos_destaque as $veiculo) {
    $stmt = $pdo->prepare("SELECT caminho_foto FROM veiculo_fotos WHERE veiculo_id = ? ORDER BY ordem ASC LIMIT 1");
    $stmt->execute([$veiculo['id']]);
    $foto = $stmt->fetch();
    $fotos_veiculos[$veiculo['id']] = $foto['caminho_foto'] ?? null;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MaxVeículos - Compre seu Carro dos Sonhos</title>
    
    <!-- ==================== CSS EXTERNO ==================== -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- ==================== CABEÇALHO (NAVBAR) ==================== -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                <img src="assets/images/Logo-Fundo-Preto.png" alt="MaxVeículos" class="logo-img">
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
                    <li class="nav-item">
                        <a class="nav-link" href="#contato">Contato</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- ==================== BANNER SLIDER ==================== -->
    <div class="banner-slider">
        <div class="swiper">
            <div class="swiper-wrapper">
                <?php if (empty($banners)): ?>
                    <div class="swiper-slide">
                        <div style="background: linear-gradient(135deg, #0B0B0B 0%, #333 100%); width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                            <div class="banner-content">
                                <h1>Bem-vindo à MaxVeículos</h1>
                                <p>Encontre o seu próximo carro dos sonhos</p>
                                <a href="<?php echo BASE_URL; ?>/public/veiculos.php" class="btn">Ver Catálogo</a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($banners as $banner): ?>
                        <div class="swiper-slide">
                            <?php if ($banner['imagem_path']): ?>
                                <!-- CORREÇÃO AQUI: caminho sem duplicação -->
                                <img src="<?php echo BASE_URL; ?>/public/uploaded_images/<?php echo $banner['imagem_path']; ?>" alt="<?php echo $banner['titulo']; ?>">
                            <?php endif; ?>
                            <div class="banner-content">
                                <h1><?php echo $banner['titulo']; ?></h1>
                                <?php if ($banner['descricao']): ?>
                                    <p><?php echo $banner['descricao']; ?></p>
                                <?php endif; ?>
                                <?php if ($banner['link_destino']): ?>
                                    <a href="<?php echo $banner['link_destino']; ?>" class="btn">Saiba Mais</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>
    </div>

    <!-- ==================== VEÍCULOS EM DESTAQUE ==================== -->
    <section class="destaque-section">
        <div class="container">
            <div class="section-title">
                <h2>Veículos em Destaque</h2>
                <p>Conheça os melhores carros do nosso catálogo</p>
            </div>

            <?php if (empty($veiculos_destaque)): ?>
                <div class="text-center text-muted">
                    <p>Nenhum veículo em destaque no momento.</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($veiculos_destaque as $veiculo): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="veiculo-card">
                                <div class="veiculo-imagem">
                                    <?php if ($fotos_veiculos[$veiculo['id']] ?? null): ?>
                                        <!-- CORREÇÃO AQUI: caminho sem duplicação -->
                                        <img src="<?php echo BASE_URL; ?>/public/uploaded_images/<?php echo $fotos_veiculos[$veiculo['id']]; ?>" alt="<?php echo $veiculo['modelo']; ?>">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/400x250?text=Sem+Foto" alt="Sem foto">
                                    <?php endif; ?>
                                    <span class="badge-destaque"><i class="fas fa-star"></i> Destaque</span>
                                </div>
                                <div class="veiculo-info">
                                    <div class="veiculo-nome"><?php echo $veiculo['marca']; ?> <?php echo $veiculo['modelo']; ?></div>
                                    <div class="veiculo-especificacoes">
                                        <span><i class="fas fa-calendar"></i> <?php echo $veiculo['ano']; ?></span>
                                        <span><i class="fas fa-tachometer-alt"></i> <?php echo number_format($veiculo['quilometragem'], 0, ',', '.'); ?> km</span>
                                    </div>
                                    <div class="veiculo-preco"><?php echo formatar_moeda($veiculo['preco']); ?></div>
                                    <div class="veiculo-acoes">
                                        <a href="<?php echo BASE_URL; ?>/public/detalhes.php?id=<?php echo $veiculo['id']; ?>" class="btn btn-detalhes btn-sm">
                                            <i class="fas fa-eye"></i> Detalhes
                                        </a>
                                        <a href="https://wa.me/?text=Olá, gostaria de saber mais sobre o <?php echo urlencode($veiculo['marca'] . ' ' . $veiculo['modelo'] . ' ' . $veiculo['ano']); ?>" target="_blank" class="btn btn-whatsapp btn-sm">
                                            <i class="fab fa-whatsapp"></i> WhatsApp
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="text-center">
                    <a href="<?php echo BASE_URL; ?>/public/veiculos.php" class="btn btn-mais-veiculos">
                        <i class="fas fa-search"></i> Ver Todos os Veículos
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ==================== RODAPÉ (FOOTER) ==================== -->
    <footer class="footer" id="contato">
        <div class="container">
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="footer-section">
                        <h5><i class="fas fa-car"></i> MaxVeículos</h5>
                        <p>Sua melhor opção em revenda de veículos. Qualidade, segurança e confiança.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="footer-section">
                        <h5>Links Rápidos</h5>
                        <ul>
                            <li><a href="<?php echo BASE_URL; ?>">Home</a></li>
                            <li><a href="<?php echo BASE_URL; ?>/public/veiculos.php">Veículos</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="footer-section">
                        <h5>Contato</h5>
                        <ul>
                            <li><i class="fas fa-phone"></i> (27) 1234-5678</li>
                            <li><i class="fas fa-envelope"></i> contato@maxveiculos.com.br</li>
                            <li><i class="fas fa-map-marker-alt"></i> Cachoeiro de Itapemirim, ES</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="footer-section">
                        <h5>Siga-nos</h5>
                        <div class="redes-sociais">
                            <a href="#" title="Facebook"><i class="fab fa-facebook"></i></a>
                            <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                            <a href="#" title="YouTube"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 MaxVeículos. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        const swiper = new Swiper('.swiper', {
            loop: true,
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });
    </script>
</body>
</html>