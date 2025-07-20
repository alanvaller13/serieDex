<?php
// Configurações e funções
function loadJsonData($file) {
    $path = __DIR__ . '/data/' . $file;
    if (!file_exists($path)) {
        return [];
    }

    $content = file_get_contents($path);
    if ($content === false) {
        return [];
    }

    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

// Carrega as séries
$series = loadJsonData('series.json');

// Função para obter cor do status
function getStatusColor($status) {
    $colors = [
        'Não Assistido' => '#666666',
        'Assistindo' => '#2196F3',
        'Concluída' => '#4CAF50',
        'Pausado' => '#FF9800',
        'Abandonado' => '#F44336',
        'Em Dia' => '#FFC107'
    ];
    return $colors[$status] ?? '#666666';
}

// Função para obter cor do status da série
function getSerieStatusColor($status) {
    $colors = [
        'Em Exibição' => '#00ff0a',
        'Finalizada' => '#2196F3',
        'Cancelada' => '#F44336',
        'Renovada' => '#FFC107',
        'Em Hiato' => '#FF9800'
    ];
    return $colors[$status] ?? '#666666';
}

// Definir BASE_URL se não estiver definida
require_once __DIR__ . '/data/base.php';

// Agora você pode usar BASE_URL
echo 'A URL base é: ' . BASE_URL;

// Função para obter ícones de streaming
function getStreamingIcon($ondeVisto) {
    $icons = [
        'Tv' => '1.png',
        'Web' => '2.png',
        'Netflix' => '3.png',
        'PrimeVideo' => '4.png',
        'GloboPlay' => '5.png',
        'Disney+' => '6.png',
        'DisneyPlus' => '6.png',
        'ParamountPlus' => '7.png',
        'Paramount+' => '7.png',
        'HBO MAX' => '8.png',
        'PlutoTV' => '9.png',
        'YouTube' => '10.png'
        'Crunchyroll' => '11.png',
    ];
    
    // Mapeamento de variações para nomes padrão
    $variations = [
        'netflix.png' => 'Netflix',
        'primevideo.png' => 'PrimeVideo',
        'prime-video.png' => 'PrimeVideo',
        'globoplay.png' => 'GloboPlay',
        'disneyplus.png' => 'Disney+',
        'disneymais.png' => 'Disney+',
        'paramountplus.png' => 'Paramount+',
        'paramountmais.png' => 'Paramount+',
        'hbomax.png' => 'HBO MAX',
        'max.png' => 'HBO MAX',
        'hbo.png' => 'HBO MAX',
        'pluto.png' => 'PlutoTV',
        'plutotv.png' => 'PlutoTV',
        'youtube.png' => 'YouTube',
        'tv.png' => 'Tv',
        'web.png' => 'Web'
    ];
    
    // Verifica se o valor é uma variação conhecida
    foreach ($variations as $variation => $standard) {
        if (strtolower($ondeVisto) === strtolower($variation)) {
            $ondeVisto = $standard;
            break;
        }
    }
    
    return $icons[$ondeVisto] ?? 'default.png';
}

// Calcular estatísticas
$totalSeries = count($series);
$favoritas = array_filter($series, fn($s) => !empty($s['favorita']) && $s['favorita']);
$concluidas = array_filter($series, fn($s) => ($s['user_status'] ?? '') === 'Concluída');
$totalEpisodios = array_sum(array_column($series, 'nEpisodios'));

// Gêneros mais assistidos
$generosCount = [];
foreach ($series as $serie) {
    if (!empty($serie['generos']) && is_array($serie['generos'])) {
        foreach ($serie['generos'] as $genero) {
            $generosCount[$genero] = ($generosCount[$genero] ?? 0) + 1;
        }
    }
}
arsort($generosCount);
$topGeneros = array_slice($generosCount, 0, 13);

// Streaming mais utilizado
$streamingData = array_column($series, 'onde_visto');
$streamingData = array_filter($streamingData); // Remove valores vazios
$streamingCount = array_count_values($streamingData);
arsort($streamingCount);
$topStreaming = array_slice($streamingCount, 0, 10);

// Países mais assistidos
$paisesData = array_column($series, 'pais');
$paisesData = array_filter($paisesData); // Remove valores vazios
$paisesCount = array_count_values($paisesData);
arsort($paisesCount);
$topPaises = array_slice($paisesCount, 0, 25);

// Status das séries
$statusData = array_column($series, 'status');
$statusData = array_filter($statusData); // Remove valores vazios
$statusCount = array_count_values($statusData);
$statusLabels = [
    'Em Exibição' => 'Em Exibição',
    'Finalizada' => 'Finalizadas',
    'Cancelada' => 'Canceladas',
    'Renovada' => 'Renovadas',
    'Em Hiato' => 'Em Hiato'
];
$statusSeries = [];
foreach ($statusLabels as $key => $label) {
    $statusSeries[$label] = $statusCount[$key] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Status - Minhas Séries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    /* ============================================= */
    /* ============== GLOBAL STYLES ================ */
    /* ============================================= */
    :root {
      --primary-color: #4CAF50;
      --primary-dark: #2E7D32;
      --secondary-color: #e50914;
      --dark-bg: #2c3e50;
      --darker-bg: #1a1a2e;
      --text-dark: #212529;
      --text-medium: #495057;
      --text-light: #f5f5f1;
      --card-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
      --card-shadow-hover: 0 12px 28px rgba(0, 0, 0, 0.12);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f5f7fa;
      color: var(--text-dark);
      line-height: 1.6;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
      flex: 1;
    }

    /* ============================================= */
    /* ================ ANIMATIONS ================= */
    /* ============================================= */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes fadeInDown {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes scaleIn {
      from { transform: scaleX(0); }
      to { transform: scaleX(1); }
    }

    /* ============================================= */
    /* ================== HEADER =================== */
    /* ============================================= */
    .site-header {
      background: linear-gradient(135deg, var(--dark-bg) 0%, var(--darker-bg) 100%);
      color: white;
      padding: 2rem 0;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      position: relative;
      overflow: hidden;
      margin-bottom: 2rem;
    }

    .site-header::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--secondary-color), #f5f5f1, var(--secondary-color));
    }

    .header-content {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      position: relative;
      z-index: 1;
    }

    .header-title {
      font-size: clamp(1.8rem, 5vw, 3rem);
      font-weight: 800;
      margin: 0;
      padding: 0;
      color: #fff;
      text-transform: uppercase;
      letter-spacing: 2px;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
      animation: fadeInDown 0.8s ease;
    }

    .header-decoration {
      width: 100px;
      height: 4px;
      background: var(--secondary-color);
      margin: 1rem 0;
      border-radius: 2px;
      animation: scaleIn 0.8s ease 0.3s both;
    }
        
    /* Carrossel de favoritas */
    .favoritas-carrossel {
        margin: 30px 0;
        position: relative;
    }
    
    .carrossel-container {
        display: flex;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        gap: 15px;
        padding: 15px 0;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    
    .carrossel-container::-webkit-scrollbar {
        display: none;
    }
    
    .favorita-item {
        flex: 0 0 auto;
        width: 150px;
        scroll-snap-align: start;
        text-align: center;
        transition: transform 0.3s ease;
    }
    
    .favorita-poster {
        width: 100%;
        height: 225px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .favorita-item:hover {
        transform: translateY(-5px);
    }
    
    .favorita-item:hover .favorita-poster {
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }
    
    .favorita-nome {
        margin-top: 10px;
        font-size: 0.9rem;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        padding: 0 5px;
    }
    
    .carrossel-nav {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 15px;
    }
    
    .carrossel-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: #ddd;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .carrossel-dot.active {
        background-color: var(--secondary-color);
        transform: scale(1.2);
    }

    /* Estatísticas */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin: 30px 0;
    }
    
    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: var(--card-shadow);
        transition: all 0.3s ease;
        animation: fadeIn 0.5s ease forwards;
        opacity: 0;
    }
    
    .stat-card:nth-child(1) { animation-delay: 0.1s; }
    .stat-card:nth-child(2) { animation-delay: 0.2s; }
    .stat-card:nth-child(3) { animation-delay: 0.3s; }
    .stat-card:nth-child(4) { animation-delay: 0.4s; }
    .stat-card:nth-child(5) { animation-delay: 0.5s; }
    .stat-card:nth-child(6) { animation-delay: 0.6s; }
    .stat-card:nth-child(7) { animation-delay: 0.7s; }
    .stat-card:nth-child(8) { animation-delay: 0.8s; }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--card-shadow-hover);
    }
    
    .stat-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--dark-bg);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--secondary-color);
        margin: 10px 0;
    }
    
    .stat-desc {
        color: var(--text-medium);
        font-size: 0.9rem;
    }
    
    .stat-list {
        list-style: none;
        padding: 0;
        margin: 15px 0 0;
    }
    
    .stat-list li {
        padding: 10px 0;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .stat-list li:last-child {
        border-bottom: none;
    }
    
    .progress {
        height: 8px;
        margin: 15px 0;
        background-color: #f0f0f0;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .progress-bar {
        background: linear-gradient(90deg, var(--secondary-color), #ff6b6b);
    }
    
    .badge-status {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        color: white;
        min-width: 40px;
        text-align: center;
    }
    
    /* Botões de ação */
    .header-actions {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: var(--card-shadow);
        margin-bottom: 25px;
    }
    
    .action-buttons {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.95rem;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: white;
        box-shadow: 0 4px 8px rgba(76, 175, 80, 0.2);
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #43A047 0%, #1B5E20 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(76, 175, 80, 0.3);
    }
    
    /* Ícones de streaming */
    .streaming-icon {
        width: 24px;
        height: 24px;
        object-fit: contain;
        margin-right: 8px;
    }
    
    .streaming-name {
        display: flex;
        align-items: center;
    }
    
    /* Mensagem vazia */
    .empty-message {
        text-align: center;
        padding: 30px;
        color: var(--text-medium);
        background-color: white;
        border-radius: 10px;
        box-shadow: var(--card-shadow);
    }
    
    /* Rodapé */
    footer {
        text-align: center;
        padding: 20px;
        color: var(--text-medium);
        background-color: white;
        margin-top: 40px;
    }
    
    /* Responsividade */
    @media (max-width: 768px) {
        .stats-container {
            grid-template-columns: 1fr;
        }
        
        .favorita-item {
            width: 120px;
        }
        
        .favorita-poster {
            height: 180px;
        }
        
        .action-buttons {
            flex-direction: column;
            width: 100%;
        }
        
        .btn {
            width: 100%;
        }
    }
    
    @media (max-width: 480px) {
        .favorita-item {
            width: 100px;
        }
        
        .favorita-poster {
            height: 150px;
        }
        
        .stat-title {
            font-size: 1.1rem;
        }
        
        .stat-value {
            font-size: 2rem;
        }
    }
    </style>
</head>
<body>
<header class="site-header">
    <div class="container">
        <div class="header-content">
            <h1 class="header-title">Meu Status</h1>
            <div class="header-decoration"></div>
        </div>
    </div>
</header>

<main class="container">
    <div class="header-actions">
        <div class="action-buttons">
            <a href="index.php" class="btn btn-primary">
                <i class="bi bi-house-door"></i> Início
            </a>
            <a href="todas.php" class="btn btn-primary">
                <i class="bi bi-collection"></i> Todas as Séries
            </a>
        </div>
    </div>

    <?php if (!empty($favoritas)): ?>
    <div class="favoritas-carrossel">
        <h2 class="stat-title"><i class="bi bi-star-fill"></i> Séries Favoritas</h2>
        <div class="carrossel-container" id="favoritasCarrossel">
            <?php foreach ($favoritas as $serie): ?>
            <div class="favorita-item">
                <img src="<?= htmlspecialchars(BASE_URL . '/' . $serie['imagem']) ?>" 
                     alt="<?= htmlspecialchars($serie['titulo']) ?>" 
                     class="favorita-poster" 
                     onerror="this.src='<?= htmlspecialchars(BASE_URL) ?>/assets/default-poster.jpg'">
                <div class="favorita-nome"><?= htmlspecialchars($serie['titulo']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="carrossel-nav">
            <?php 
            $totalItems = count($favoritas);
            if ($totalItems > 0) {
                $itemsPerView = min(4, $totalItems);
                $totalDots = ceil($totalItems / $itemsPerView);
                
                for ($i = 0; $i < min(5, $totalDots); $i++): ?>
                <div class="carrossel-dot <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>"></div>
                <?php endfor;
            }
            ?>
        </div>
    </div>
    <?php else: ?>
    <div class="empty-message">
        <i class="bi bi-emoji-frown" style="font-size: 2rem; margin-bottom: 10px;"></i>
        <p>Nenhuma série favorita ainda</p>
    </div>
    <?php endif; ?>

    <div class="stats-container">
        <!-- Card Total de Séries -->
        <div class="stat-card">
            <div class="stat-title">
                <i class="bi bi-collection-play"></i>
                Total de Séries
            </div>
            <div class="stat-value"><?= htmlspecialchars($totalSeries) ?></div>
            <div class="stat-desc">Séries no seu acervo</div>
        </div>

        <!-- Card Séries Favoritas -->
        <div class="stat-card">
            <div class="stat-title">
                <i class="bi bi-star-fill"></i>
                Séries Favoritas
            </div>
            <div class="stat-value"><?= htmlspecialchars(count($favoritas)) ?></div>
            <div class="stat-desc">Séries marcadas como favoritas</div>
        </div>

        <!-- Card Séries Concluídas -->
        <div class="stat-card">
            <div class="stat-title">
                <i class="bi bi-check-circle"></i>
                Séries Concluídas
            </div>
            <div class="stat-value"><?= htmlspecialchars(count($concluidas)) ?></div>
            <div class="progress">
                <div class="progress-bar" style="width: <?= $totalSeries > 0 ? round(count($concluidas)/$totalSeries*100) : 0 ?>%"></div>
            </div>
            <div class="stat-desc"><?= $totalSeries > 0 ? round(count($concluidas)/$totalSeries*100) : 0 ?>% do seu acervo</div>
        </div>

        <!-- Card Total de Episódios -->
        <div class="stat-card">
            <div class="stat-title">
                <i class="bi bi-film"></i>
                Total de Episódios
            </div>
            <div class="stat-value"><?= htmlspecialchars($totalEpisodios) ?></div>
            <div class="stat-desc">Episódios assistidos</div>
        </div>

        <!-- Card Gêneros Mais Assistidos -->
        <div class="stat-card">
            <div class="stat-title">
                <i class="bi bi-tags"></i>
                Gêneros Favoritos
            </div>
            <ul class="stat-list">
                <?php foreach ($topGeneros as $genero => $count): ?>
                <li>
                    <span><?= htmlspecialchars($genero) ?></span>
                    <span><?= htmlspecialchars($count) ?> série<?= $count > 1 ? 's' : '' ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Card Streaming Mais Utilizado -->
        <div class="stat-card">
            <div class="stat-title">
                <i class="bi bi-tv"></i>
                Plataformas Preferidas
            </div>
            <ul class="stat-list">
                <?php foreach ($topStreaming as $streaming => $count): 
                    $iconFile = getStreamingIcon($streaming);
                    $iconPath = BASE_URL . '/icons/' . $iconFile;
                ?>
                <li>
                    <span class="streaming-name">
                        <img src="<?= htmlspecialchars($iconPath) ?>" 
                             alt="<?= htmlspecialchars($streaming) ?>" 
                             class="streaming-icon" 
                             onerror="this.onerror=null;this.src='<?= htmlspecialchars(BASE_URL) ?>/icons/default.png'">
                        <?= htmlspecialchars($streaming) ?>
                    </span>
                    <span><?= htmlspecialchars($count) ?> série<?= $count > 1 ? 's' : '' ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Card Países Mais Assistidos -->
        <div class="stat-card">
            <div class="stat-title">
                <i class="bi bi-globe"></i>
                Países Mais Assistidos
            </div>
            <ul class="stat-list">
                <?php foreach ($topPaises as $pais => $count): ?>
                <li>
                    <span><?= htmlspecialchars($pais) ?></span>
                    <span><?= htmlspecialchars($count) ?> série<?= $count > 1 ? 's' : '' ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Card Status das Séries -->
        <div class="stat-card">
            <div class="stat-title">
                <i class="bi bi-info-circle"></i>
                Status das Séries
            </div>
            <ul class="stat-list">
                <?php foreach ($statusSeries as $status => $count): ?>
                <li>
                    <span><?= htmlspecialchars($status) ?></span>
                    <span class="badge-status" style="background-color: <?= getSerieStatusColor($status) ?>">
                        <?= htmlspecialchars($count) ?>
                    </span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</main>

<footer>
    <p>&copy; <?= htmlspecialchars(date('Y')) ?> dexSeries</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Carrossel de favoritas
    const carrossel = document.getElementById('favoritasCarrossel');
    const dots = document.querySelectorAll('.carrossel-dot');
    
    if (carrossel && dots.length > 0) {
        const favoritaItems = document.querySelectorAll('.favorita-item');
        if (favoritaItems.length === 0) return;
        
        const itemWidth = favoritaItems[0].offsetWidth + 15; // Largura do item + gap
        const itemsPerView = Math.min(4, Math.floor(carrossel.offsetWidth / itemWidth));
        
        // Atualiza dots ativos conforme scroll
        const updateActiveDot = () => {
            const scrollPos = carrossel.scrollLeft;
            const activeIndex = Math.round(scrollPos / (itemWidth * itemsPerView));
            
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === activeIndex);
            });
        };
        
        carrossel.addEventListener('scroll', updateActiveDot);
        
        // Navegação pelos dots
        dots.forEach(dot => {
            dot.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-index'));
                carrossel.scrollTo({
                    left: index * (itemWidth * itemsPerView),
                    behavior: 'smooth'
                });
            });
        });
        
        // Redimensionamento da janela
        window.addEventListener('resize', function() {
            const newItemsPerView = Math.min(4, Math.floor(carrossel.offsetWidth / itemWidth));
            updateActiveDot();
        });
        
        // Inicializa
        updateActiveDot();
    }
    
    // Animações de entrada
    const statCards = document.querySelectorAll('.stat-card');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
            }
        });
    }, { threshold: 0.1 });
    
    statCards.forEach(card => {
        observer.observe(card);
    });
});
</script>
</body>
</html>