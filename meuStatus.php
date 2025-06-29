<?php
// Configurações e funções
function loadJsonData($file) {
    $path = __DIR__ . '/data/' . $file;
    if (!file_exists($path)) {
        return [];
    }

    $content = file_get_contents($path);
    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

// Carrega as séries
$series = loadJsonData('series.json');

// Função para obter cor do status
function getStatusColor($status) {
    switch ($status) {
        case 'Não assistido': return '#666666';
        case 'Assistindo': return '#2196F3';
        case 'Concluída': return '#4CAF50';
        case 'Pausado': return '#FF9800';
        case 'Abandonado': return '#F44336';
        case 'Em Dia': return '#FFC107';
        default: return '#666666';
    }
}

// Função para obter cor do status da série
function getSerieStatusColor($status) {
    switch ($status) {
        case 'Em Exibição': return '#4CAF50';
        case 'Finalizada': return '#2196F3';
        case 'Cancelada': return '#F44336';
        case 'Renovada': return '#FFC107';
        case 'Em Hiato': return '#FF9800';
        default: return '#666666';
    }
}

// Definir BASE_URL se não estiver definida
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/dexSeries');
}

// Calcular estatísticas
$totalSeries = count($series);
$favoritas = array_filter($series, fn($s) => !empty($s['favorita']) && $s['favorita']);
$concluidas = array_filter($series, fn($s) => ($s['user_status'] ?? '') === 'Concluída');
$totalEpisodios = array_sum(array_column($series, 'nEpisodios'));

// Gêneros mais assistidos
$generosCount = [];
foreach ($series as $serie) {
    foreach ($serie['generos'] as $genero) {
        $generosCount[$genero] = ($generosCount[$genero] ?? 0) + 1;
    }
}
arsort($generosCount);
$topGeneros = array_slice($generosCount, 0, 13);

// Streaming mais utilizado
$streamingCount = array_count_values(array_column($series, 'onde_visto'));
arsort($streamingCount);
$topStreaming = array_slice($streamingCount, 0, 10);

// Países mais assistidos
$paisesCount = array_count_values(array_column($series, 'pais'));
arsort($paisesCount);
$topPaises = array_slice($paisesCount, 0, 25);

// Status das séries
$statusCount = array_count_values(array_column($series, 'status'));
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

// Função para obter ícones de streaming
function getStreamingIcon($ondeVisto) {
    $icons = [
        'Tv' => '1.png',
        'Web' => '2.png',
        'Netflix' => '3.png',
        'PrimeVideo' => '4.png',
        'GloboPlay' => '5.png',
        'DisneyPlus' => '6.png',
        'Disney+' => '6.png',
        'ParamountPlus' => '7.png',
        'Paramount+' => '7.png',
        'HBO MAX' => '8.png',
        'PlutoTV' => '9.png'
    ];
    return $icons[$ondeVisto] ?? 'default.png';

    $ondeVisto = strtolower(str_replace(['+', ' '], '', $ondeVisto));

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
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
      background-color:rgb(96, 105, 114);
      color: var(--text-dark);
      line-height: 1.6;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
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
      font-size: 3rem;
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
        
    /* Novos estilos para o carrossel */
    .favoritas-carrossel {
        margin-top: 15px;
        position: relative;
    }
    
    .carrossel-container {
        display: flex;
        overflow-x: auto;
        scroll-snap-type: x mandatory;
        gap: 15px;
        padding-bottom: 15px;
        scrollbar-width: none; /* Firefox */
    }
    
    .carrossel-container::-webkit-scrollbar {
        display: none; /* Chrome/Safari */
    }
    
    .favorita-item {
        flex: 0 0 auto;
        width: 120px;
        scroll-snap-align: start;
        text-align: center;
    }
    
    .favorita-poster {
        width: 100%;
        height: 180px;
        object-fit: cover;
        border-radius: 8px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        transition: transform 0.3s ease;
    }
    
    .favorita-item:hover .favorita-poster {
        transform: scale(1.05);
    }
    
    .favorita-nome {
        margin-top: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .carrossel-nav {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin-top: 10px;
    }
    
    .carrossel-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #ddd;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    
    .carrossel-dot.active {
        background-color: #e50914;
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
        margin-top: 30px;
    }
    
    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition: transform 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }
    
    .stat-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: #e50914;
        margin: 10px 0;
    }
    
    .stat-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .stat-list li {
        padding: 8px 0;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .stat-list li:last-child {
        border-bottom: none;
    }
    
    .progress {
        height: 10px;
        margin-top: 10px;
    }
    
    .progress-bar {
        background-color: #e50914;
    }
    
    .badge-status {
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .header-actions {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        margin-bottom: 25px;
        display: flex;
        justify-content: center;
    }
    
    .action-buttons {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
        border: none;
        border-radius: 8px;
        font-weight: 600;
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #43A047 0%, #1B5E20 100%);
        transform: translateY(-2px);
    }
    
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
    
    @media (max-width: 768px) {
        .stats-container {
            grid-template-columns: 1fr;
        }
        
        .header-title {
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
        </div>
    </div>
</header>

<main class="container">
    <div class="header-actions">
        <div class="action-buttons">
            <a href="index.php" class="btn btn-primary">
                <i class="bi bi-house-door"></i> Início
            </a>
        </div>
    </div>

    <?php if (!empty($favoritas)): ?>
    <div class="favoritas-carrossel">
        <div class="carrossel-container" id="favoritasCarrossel">
            <?php foreach ($favoritas as $serie): ?>
            <div class="favorita-item">
                <img src="<?= BASE_URL . '/' . $serie['imagem'] ?>" alt="<?= htmlspecialchars($serie['titulo']) ?>" class="favorita-poster" onerror="this.src='<?= BASE_URL ?>/assets/default-poster.jpg'">
                <div class="favorita-nome"><?= htmlspecialchars($serie['titulo']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="carrossel-nav">
            <?php 
            // Calcula quantos dots são necessários baseado no número de itens e largura
            $itemsPerView = 4; // Quantos itens são visíveis por vez
            $totalItems = count($favoritas);
            $totalDots = ceil($totalItems / $itemsPerView);
            ?>
            <?php for ($i = 0; $i < min(5, $totalDots); $i++): ?>
            <div class="carrossel-dot <?= $i === 0 ? 'active' : '' ?>" data-index="<?= $i ?>"></div>
            <?php endfor; ?>
        </div>
    </div>
    <?php else: ?>
    <p style="margin-top: 15px; color: #666; text-align: center;">Nenhuma série favorita ainda</p>
    <?php endif; ?>

    <div class="stats-container">
        
        <!-- Card Total de Séries -->
        <div class="stat-card">
            <div class="stat-title">
                <i class="bi bi-collection-play"></i>
                Total de Séries
            </div>
            <div class="stat-value"><?= $totalSeries ?></div>
            <div class="stat-desc">Séries no seu acervo</div>
        </div>

        <!-- Card Séries Favoritas -->
        <div class="stat-card">
            <div class="stat-title">
                <i class="bi bi-star-fill"></i>
                Séries Favoritas
            </div>
            <div class="stat-value"><?= count($favoritas) ?></div>
            <div class="stat-desc">Séries marcadas como favoritas</div>
        </div>

        <!-- Card Séries Concluídas -->
        <div class="stat-card">
            <div class="stat-title">
                <i class="bi bi-check-circle"></i>
                Séries Concluídas
            </div>
            <div class="stat-value"><?= count($concluidas) ?></div>
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
            <div class="stat-value"><?= $totalEpisodios ?></div>
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
                    <span><?= $genero ?></span>
                    <span><?= $count ?> série<?= $count > 1 ? 's' : '' ?></span>
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
                    $iconPath = BASE_URL . '/assets/icons/' . $iconFile;
                ?>
                <li>
                    <span class="streaming-name">
                        <img src="<?= $iconPath ?>" alt="<?= htmlspecialchars($streaming) ?>" class="streaming-icon" onerror="this.onerror=null;this.src='<?= BASE_URL ?>/assets/icons/default.png'">
                        <?= $streaming ?>
                    </span>
                    <span><?= $count ?> série<?= $count > 1 ? 's' : '' ?></span>
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
                    <span><?= $pais ?></span>
                    <span><?= $count ?> série<?= $count > 1 ? 's' : '' ?></span>
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
                    <span><?= $status ?></span>
                    <span class="badge-status" style="background-color: <?= getSerieStatusColor($status) ?>">
                        <?= $count ?>
                    </span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</main>

<footer class="container text-center mt-5 mb-3">
    <p>&copy; <?= date('Y') ?> dexSeries</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const carrossel = document.getElementById('favoritasCarrossel');
    const dots = document.querySelectorAll('.carrossel-dot');
    const favoritaItems = document.querySelectorAll('.favorita-item');
    
    if (carrossel && dots.length > 0 && favoritaItems.length > 0) {
        const itemWidth = favoritaItems[0].offsetWidth + 15; // Largura do item + gap
        const itemsPerView = Math.floor(carrossel.offsetWidth / itemWidth);
        
        // Atualiza dots ativos conforme scroll
        carrossel.addEventListener('scroll', function() {
            const scrollPos = carrossel.scrollLeft;
            const activeIndex = Math.round(scrollPos / (itemWidth * itemsPerView));
            
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === activeIndex);
            });
        });
        
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
            const newItemsPerView = Math.floor(carrossel.offsetWidth / itemWidth);
            if (newItemsPerView !== itemsPerView) {
                // Atualiza a posição do scroll para manter o item visível
                const activeDot = document.querySelector('.carrossel-dot.active');
                if (activeDot) {
                    const index = parseInt(activeDot.getAttribute('data-index'));
                    carrossel.scrollTo({
                        left: index * (itemWidth * newItemsPerView),
                        behavior: 'auto'
                    });
                }
            }
        });
    }
});
</script>
</body>
</html>
