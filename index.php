<?php
// Configurações e funções
function loadJsonData($file) {
    $path = __DIR__ . '/series/' . $file;
    if (!file_exists($path)) {
        return [];
    }

    $content = file_get_contents($path);
    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

// Carrega todas as séries organizadas por ano
$seriesByYear = [
    'antes2005' => loadJsonData('antes2005.json'),
    'antes2010' => loadJsonData('antes2010.json'),
    'antes2015' => loadJsonData('antes2015.json'),
    '2016' => loadJsonData('2016.json'),
    '2017' => loadJsonData('2017.json'),
    '2018' => loadJsonData('2018.json'),
    '2019' => loadJsonData('2019.json'),
    '2020' => loadJsonData('2020.json'),
    '2021' => loadJsonData('2021.json'),
    '2022' => loadJsonData('2022.json'),
    '2023' => loadJsonData('2023.json'),
    '2024' => loadJsonData('2024.json')
];

// Carrega séries por status
$seriesByStatus = [
    'pausadas' => loadJsonData('pausadas.json'),
    'abandonadas' => loadJsonData('abandonadas.json'),
    'em-exibicao' => loadJsonData('em-exibicao.json'),
    'nao-iniciadas' => loadJsonData('nao-iniciadas.json'),
    'pokemon' => loadJsonData('pokemon.json'),
];

// Função para contar o total de séries
function countTotalSeries($seriesData) {
    $total = 0;
    foreach ($seriesData as $year => $series) {
        $total += count($series);
    }
    return $total;
}

// Total de séries finalizadas
$totalFinalizadas = countTotalSeries($seriesByYear);

// Total de séries por status
$totalPausadas = count($seriesByStatus['pausadas']);
$totalAbandonadas = count($seriesByStatus['abandonadas']);
$totalEmExibicao = count($seriesByStatus['em-exibicao']);
$totalNaoIniciadas = count($seriesByStatus['nao-iniciadas']);

//Total de séries do Pokémon
$totalPokemon = count($seriesByStatus['pokemon']);

// Total geral
$totalSeries = $totalFinalizadas + $totalPausadas + $totalAbandonadas + $totalEmExibicao + $totalNaoIniciadas + $totalPokemon;

// Função para obter cor do status
function getStatusColor($status) {
    switch ($status) {
        case 'Não Assistido': return '#666666';
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
        case 'Em Exibição': return '#00ff0a';
        case 'Finalizada': return '#2196F3';
        case 'Cancelada': return '#F44336';
        case 'Renovada': return '#FFC107';
        case 'Em Hiato': return '#FF9800';
        default: return '#666666';
    }
}

// Função para obter ícone do streaming
function getStreamingIcon($ondeVisto) {
    $icons = [
        'Tv' => '1.png', 'tv.png',
        'Web' => '2.png', 'web.png',
        'Netflix' => '3.png','netflix.png',
        'PrimeVideo' => '4.png','primevideo.png', 'prime-video.png',
        'GloboPlay' => '5.png', 'globoplay.png',
        'Disney+' => '6.png', 'disneyplus.png', 'disneymais.png',
        'DisneyPlus' => '6.png', 'disneyplus.png', 'disneymais.png',
        'ParamountPlus' => '7.png', 'paramountplus.png', 'paramountmais.png',
        'Paramount+' => '7.png', 'paramountplus.png', 'paramountmais.png',
        'HBO MAX' => 'max.png', 'hbo.png',
        'PlutoTV' => '9.png', 'pluto.png', 'plutotv.png',
        'YouTube' => '10.png', 'youtube.png',
    ];
    
    return $icons[$ondeVisto] ?? 'default.png';
}

// Função para truncar texto
function truncarTexto($texto, $limite = 25) {
    if (strlen($texto) > $limite) {
        return substr($texto, 0, $limite) . "...";
    }
    return $texto;
}

// Definir BASE_URL se não estiver definida
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/dexSeries');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Série DEX</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    /* ============================================= */
    /* ============== GLOBAL STYLES ================ */
    /* ============================================= */
    :root {
      --primary-gradient: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
      --primary-color: #6a11cb;
      --primary-dark: #2E7D32;
      --secondary-color: #2575fc;
      --dark-bg:rgb(108, 107, 107);
      --darker-bg: #1a1a2e;
      --text-dark: #212529;
      --text-medium: #495057;
      --text-light: #f5f5f1;
      --card-shadow: 0 5px 20px rgba(0, 0, 0, 0.61);
      --card-shadow-hover: 0 12px 28px rgba(0, 0, 0, 0.12);
    }

    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
      background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
      color: var(--text-light);
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
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
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

    /* ============================================= */
    /* ============= SEARCH & FILTERS ============== */
    /* ============================================= */
    .search-filters {
      display: flex;
      gap: 20px;
      margin-bottom: 30px;
      flex-wrap: wrap;
      background: #ffffff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    }

    .search-filters > div {
      flex: 1;
      min-width: 220px;
    }

    .search-filters label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: var(--text-medium);
      font-size: 0.9rem;
    }

    .search-filters input,
    .search-filters select {
      width: 100%;
      padding: 10px 15px;
      border: 1px solid #e0e0e0;
      border-radius: 6px;
      font-size: 0.95rem;
      transition: all 0.3s ease;
      background-color: #f8f9fa;
    }

    .search-filters select {
      appearance: none;
      background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
      background-repeat: no-repeat;
      background-position: right 10px center;
      background-size: 15px;
    }

    /* ============================================= */
    /* ============== ACTION BUTTONS =============== */
    /* ============================================= */
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
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      padding: 12px 24px;
      border: none;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.95rem;
      text-decoration: none;
      cursor: pointer;
      transition: all 0.3s;
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

    /* ============================================= */
    /* ============== SERIES GRID ================== */
    /* ============================================= */
.series-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr); /* Força 4 colunas */
  gap: 15px;
  padding: 20px;
}

    .serie-card {
      background: #ffffff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: var(--card-shadow);
      transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.1);
      position: relative;
      display: flex;
      flex-direction: column;
      height: 100%;
      animation: fadeIn 0.5s ease forwards;
      opacity: 0;
    }

    .serie-card:nth-child(1) { animation-delay: 0.1s; }
    .serie-card:nth-child(2) { animation-delay: 0.2s; }
    .serie-card:nth-child(3) { animation-delay: 0.3s; }
    .serie-card:nth-child(4) { animation-delay: 0.4s; }

    .serie-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--card-shadow-hover);
    }

       /* Card Actions */
    .card-actions {
      position: absolute;
      top: 16px;
      right: 16px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      z-index: 2;
      transform: translateX(20px);
      opacity: 0;
      transition: all 0.3s ease;
    }

    .serie-card:hover .card-actions {
      transform: translateX(0);
      opacity: 1;
    }

    .card-action {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 36px;
      height: 36px;
      background: rgba(255, 255, 255, 0.9);
      border-radius: 50%;
      color: #333;
      text-decoration: none;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      transition: all 0.2s ease;
    }

    .card-action:hover {
      background: var(--secondary-color);
      color: white;
      transform: scale(1.1);
    }

    /* Card Image Section */
    .serie-image {
      position: relative;
      height: 50%;
      padding-bottom: 130%;
      overflow: hidden;
    }

    .serie-image img {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.6s ease;
    }

    .serie-card:hover .serie-image img {
      transform: scale(1.05);
    }

    .serie-card:hover .serie-image::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(to top, rgba(0,0,0,0.5) 0%, transparent 30%);
      z-index: 1;
    }

    /* Card Icons */
    .streaming-icon {
      position: absolute;
      top: 16px;
      left: 16px;
      width: 48px;
      height: 48px;
      background: rgba(0, 0, 0, 0.7);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 2;
      transition: all 0.3s ease;
      overflow: hidden;
    }

    .streaming-icon img {
      width: 48px;
      height: 48px;
      object-fit: contain;
    }

    .serie-card:hover .streaming-icon {
      background: rgba(0, 0, 0, 0.9);
      transform: scale(1.1);
    }

    .favorite-icon {
      position: absolute;
      top: 75px;
      left: 16px;
      width: 48px;
      height: 48px;
      color: #003d9e;
      font-size: 2rem;
      background-color: rgba(255, 255, 255, 0.7);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 3;
      transition: all 0.3s ease;
    }

    .serie-card:hover .favorite-icon {
      background: rgba(196, 185, 185, 0.9);
      transform: scale(1.1);
    }

    .favorite-icon i {
      font-size: 1.2rem;
    }

    /* Card Info Section */
    .serie-info {
      padding: 18px;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .serie-info h4 {
      margin: 0 0 8px 0;
      font-size: 1.2rem;
      font-weight: 700;
      color: #222;
      line-height: 1.3;
      transition: color 0.3s ease;
    }

    .serie-card:hover .serie-info h4 {
      color: var(--secondary-color);
    }

    .meta {
      font-size: 0.85rem;
      color: #666;
      margin: 8px 0;
      display: flex;
      gap: 8px;
      align-items: center;
    }

    .generos {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin: 10px 0;
    }

    .generos span {
      background: #f0f0f0;
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 0.75rem;
      color: #555;
    }

    .avaliacao {
      color: blue;
      font-size: 1rem;
      display: flex;
      align-items: center;
      gap: 2px;
    }

    .avaliacao i {
      font-size: 1.2rem;
      line-height: 1;
    }

    .avaliacao .half-star {
      position: relative;
      display: inline-block;
      width: 1em;
    }

    .avaliacao .half-star:before {
      content: '★';
      position: absolute;
      left: 0;
      width: 50%;
      overflow: hidden;
      color: gold;
    }

    /* Barra de Progresso */
    .progress-container {
        margin: 12px 0;
    }

    .progress-label {
        display: flex;
        justify-content: space-between;
        font-size: 0.8rem;
        color: var(--text-medium);
        margin-bottom: 4px;
    }

    .progress-bar {
        height: 6px;
        background: #f0f0f0;
        border-radius: 3px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: var(--primary-gradient);
        border-radius: 3px;
        transition: width 0.6s ease;
    }

    .status-badge {
      margin-top: auto;
      padding: 8px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      color: white;
      text-align: center;
      transition: all 0.3s ease;
    }

    .serie-status {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 0.75rem;
      font-weight: 600;
      color: white;
      margin-bottom: 10px;
    }

    /* Empty State */
    .empty-message {
      grid-column: 1 / -1;
      text-align: center;
      padding: 60px 20px;
      background: white;
      border-radius: 12px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    }

    /* Stats Section */
    .stats-container {
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 1rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      background: white;
      border-radius: 10px;
      padding: 1.5rem;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      text-align: center;
      min-width: 200px;
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .stat-value {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 0.5rem;
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .stat-label {
      font-size: 1rem;
      color: #666;
    }

    /* Year Section */
    .year-section {
      margin-bottom: 3rem;
      background: white;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    .year-header {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
      color: white;
      padding: 1rem 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .year-title {
      font-size: 1.5rem;
      font-weight: 600;
      margin: 0;
    }

    .year-count {
      background: rgba(255, 255, 255, 0.2);
      border-radius: 20px;
      padding: 0.25rem 0.75rem;
      font-size: 1rem;
    }

    /* Section Titles */
    .section-title {
      text-align: center;
      margin-bottom: 2rem;
      color: white;
      font-size: 2rem;
      font-weight: 700;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    /* ============================================= */
    /* ============= RESPONSIVE STYLES ============= */
    /* ============================================= */
    @media (max-width: 768px) {
      .header-title {
        font-size: 2.2rem;
      }
      
      .series-grid {
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 20px;
      }
      
      .serie-info {
        padding: 15px;
      }
      
      .search-filters {
        gap: 15px;
        padding: 15px;
      }
      
      .search-filters > div {
        min-width: 100%;
      }
      
      .action-buttons {
        flex-direction: column;
        width: 100%;
      }

      .stat-card {
        min-width: 150px;
        padding: 1rem;
      }
      
      .stat-value {
        font-size: 2rem;
      }
    }

    @media (max-width: 480px) {
      .header-title {
        font-size: 1.8rem;
        letter-spacing: 1px;
      }
    }
    </style>
</head>
<body>
<header class="site-header">
    <div class="container">
        <div class="header-content">
            <h1 class="header-title">Minha Coleção de Séries</h1>
            <div class="header-decoration"></div>
        </div>
    </div>
</header>

<main class="container">
    <!-- Stats Section -->
    <div class="stats-container">
        <div class="stat-card">
            <div class="stat-value"><?= $totalSeries ?></div>
            <div class="stat-label">Total de Séries</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $totalFinalizadas ?></div>
            <div class="stat-label">Séries Finalizadas</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $totalEmExibicao ?></div>
            <div class="stat-label">Em Exibição</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $totalPausadas + $totalAbandonadas ?></div>
            <div class="stat-label">Pausadas/Abandonadas</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?= $totalNaoIniciadas ?></div>
            <div class="stat-label">Não Iniciadas</div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="header-actions">
        <div class="action-buttons">
            <a href="index.php" class="btn btn-primary">
                <i class="bi bi-house-door"></i> Início
            </a>
            <a href="todas.php" class="btn btn-primary">
                <i class="bi bi-collection"></i> Todas as Séries
            </a>
            <a href="meuStatus.php" class="btn btn-primary">
                <i class="bi bi-star-circle-fill"></i> Meu Status
            </a>
        </div>
    </div>

    <!-- Séries Finalizadas por Ano -->
    <h2 class="section-title">Séries Finalizadas</h2>
    
    <?php foreach ($seriesByYear as $year => $series): 
        if (empty($series)) continue;
        $yearLabel = str_replace('antes', 'Antes de ', $year);
    ?>
    <div class="year-section">
        <div class="year-header">
            <h3 class="year-title"><?= $yearLabel ?></h3>
            <span class="year-count"><?= count($series) ?> séries</span>
        </div>
        
        <div class="series-grid">
            <?php foreach ($series as $serie): ?>
            <div class="serie-card">
                <div class="serie-image">
                    <img src="<?= BASE_URL . '/' . $serie['imagem'] ?>" alt="<?= $serie['titulo'] ?>">
                    <?php if (!empty($serie['onde_visto'])): ?>
                    <div class="streaming-icon" title="<?= $serie['onde_visto'] ?>">
                        <img src="<?= BASE_URL ?>/icons/<?= getStreamingIcon($serie['onde_visto']) ?>" alt="<?= $serie['onde_visto'] ?>">
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($serie['favorita']) && $serie['favorita']): ?>
                    <div class="favorite-icon" title="Série favorita">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <?php endif; ?>

                <div class="card-actions">
                <a href="add/detalhesTv.php?id=<?= $serie['id'] ?>" class="card-action" title="Detalhes">
                    <i class="bi bi-plus"></i>
                </a>
                </div>

                </div>
                <div class="serie-info">
                    <h4><?= truncarTexto($serie['titulo'], 20) ?></h4>
                    <div>
                        <span class="serie-status" style="background-color: <?= getSerieStatusColor($serie['status']) ?>">
                            <?= $serie['status'] ?>
                        </span>
                    </div>
                    <div class="meta">
                        <span><?= $serie['ano_lancamento'] ?></span> |
                        <span><?= $serie['pais'] ?></span>
                    </div>
                    <div class="generos">
                        <?php foreach (array_slice($serie['generos'], 0, 3) as $genero): ?>
                        <span><?= $genero ?></span>
                        <?php endforeach; ?>
                    </div>
                    <!-- Barra de Progresso Adicionada -->
                    <div class="progress-container">
                        <div class="progress-label">
                            <span><?= $serie['progresso'] ?? 0 ?>%</span>
                            <span><?= $serie['nEpisodios'] ?? 0 ?> eps</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $serie['progresso'] ?? 0 ?>%"></div>
                        </div>
                    </div>
                    <div class="status-badge" style="background-color: <?= getStatusColor('Concluída') ?>">
                        Concluída
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <!-- Seção Pokémon -->
<div class="year-section">
    <div class="year-header" style="background: linear-gradient(135deg, #FF0000 0%, #CC0000 100%);">
        <h3 class="year-title">Pokémon</h3>
        <?php
        $pokemonSeries = loadJsonData('pokemon.json');
        $totalPokemon = count($pokemonSeries);
        ?>
        <span class="year-count"><?= $totalPokemon ?> Séries</span>
    </div>
    
    <div class="series-grid">
        <?php if (!empty($pokemonSeries)): ?>
            <?php foreach ($pokemonSeries as $serie): ?>
            <div class="serie-card">
                <div class="serie-image">
                    <img src="<?= BASE_URL . '/' . $serie['imagem'] ?>" alt="<?= $serie['titulo'] ?>">
                    <?php if (!empty($serie['onde_visto'])): ?>
                    <div class="streaming-icon" title="<?= $serie['onde_visto'] ?>">
                        <img src="<?= BASE_URL ?>/icons/<?= getStreamingIcon($serie['onde_visto']) ?>" alt="<?= $serie['onde_visto'] ?>">
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($serie['favorita']) && $serie['favorita']): ?>
                    <div class="favorite-icon" title="Série favorita">
                        <i class="bi bi-star-fill"></i>
                    </div>
                    <?php endif; ?>

                    <div class="card-actions">
                        <a href="add/detalhesTv.php?id=<?= $serie['id'] ?>" class="card-action" title="Detalhes">
                            <i class="bi bi-plus"></i>
                        </a>
                    </div>
                </div>
                <div class="serie-info">
                    <h4><?= truncarTexto($serie['titulo'], 20) ?></h4>
                    <div>
                        <span class="serie-status" style="background-color: <?= getSerieStatusColor($serie['status']) ?>">
                            <?= $serie['status'] ?>
                        </span>
                    </div>
                    <div class="meta">
                        <span><?= $serie['ano_lancamento'] ?></span> |
                        <span><?= $serie['pais'] ?></span>
                    </div>
                    <div class="generos">
                        <?php foreach (array_slice($serie['generos'], 0, 3) as $genero): ?>
                        <span><?= $genero ?></span>
                        <?php endforeach; ?>
                    </div>
                    <!-- Barra de Progresso Adicionada -->
                    <div class="progress-container">
                        <div class="progress-label">
                            <span><?= $serie['progresso'] ?? 0 ?>%</span>
                            <span><?= $serie['nEpisodios'] ?? 0 ?> eps</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?= $serie['progresso'] ?? 0 ?>%"></div>
                        </div>
                    </div>
                    <div class="status-badge" style="background-color: <?= getStatusColor($serie['user_status'] ?? 'Em Dia') ?>">
                        <?= $serie['user_status'] ?? 'Em Dia' ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-message">Nenhuma temporada de Pokémon cadastrada</div>
        <?php endif; ?>
    </div>
</div>

    <!-- Séries por Status -->
    <h2 class="section-title">Séries por Status</h2>
    
    <!-- Em Exibição/Em Dia -->
    <div class="year-section">
        <div class="year-header" style="background: linear-gradient(135deg, #00c853 0%, #64dd17 100%);">
            <h3 class="year-title">Em Exibição / Em Dia</h3>
            <span class="year-count"><?= $totalEmExibicao ?> séries</span>
        </div>
        
        <div class="series-grid">
            <?php if (!empty($seriesByStatus['em-exibicao'])): ?>
                <?php foreach ($seriesByStatus['em-exibicao'] as $serie): ?>
                <div class="serie-card">
                    <div class="serie-image">
                        <img src="<?= BASE_URL . '/' . $serie['imagem'] ?>" alt="<?= $serie['titulo'] ?>">
                        <?php if (!empty($serie['onde_visto'])): ?>
                        <div class="streaming-icon" title="<?= $serie['onde_visto'] ?>">
                            <img src="<?= BASE_URL ?>/icons/<?= getStreamingIcon($serie['onde_visto']) ?>" alt="<?= $serie['onde_visto'] ?>">
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($serie['favorita']) && $serie['favorita']): ?>
                        <div class="favorite-icon" title="Série favorita">
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <?php endif; ?>

                    <div class="card-actions">
                <a href="add/detalhesTv.php?id=<?= $serie['id'] ?>" class="card-action" title="Detalhes">
                    <i class="bi bi-plus"></i>
                </a>
                        </div>

                    </div>
                    <div class="serie-info">
                        <h4><?= truncarTexto($serie['titulo'], 20) ?></h4>
                        <div>
                            <span class="serie-status" style="background-color: <?= getSerieStatusColor($serie['status']) ?>">
                                <?= $serie['status'] ?>
                            </span>
                        </div>
                        <div class="meta">
                            <span><?= $serie['ano_lancamento'] ?></span> |
                            <span><?= $serie['pais'] ?></span>
                        </div>
                        <div class="generos">
                            <?php foreach (array_slice($serie['generos'], 0, 3) as $genero): ?>
                            <span><?= $genero ?></span>
                            <?php endforeach; ?>
                        </div>
                        <!-- Barra de Progresso Adicionada -->
                        <div class="progress-container">
                            <div class="progress-label">
                                <span><?= $serie['progresso'] ?? 0 ?>%</span>
                                <span><?= $serie['nEpisodios'] ?? 0 ?> eps</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $serie['progresso'] ?? 0 ?>%"></div>
                            </div>
                        </div>
                        <div class="status-badge" style="background-color: <?= getStatusColor($serie['user_status'] ?? 'Em Dia') ?>">
                            <?= $serie['user_status'] ?? 'Em Dia' ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-message">Nenhuma série em exibição no momento</div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Pausadas -->
    <div class="year-section">
        <div class="year-header" style="background: linear-gradient(135deg, #FF9800 0%, #FFC107 100%);">
            <h3 class="year-title">Séries Pausadas</h3>
            <span class="year-count"><?= $totalPausadas ?> séries</span>
        </div>
        
        <div class="series-grid">
            <?php if (!empty($seriesByStatus['pausadas'])): ?>
                <?php foreach ($seriesByStatus['pausadas'] as $serie): ?>
                <div class="serie-card">
                    <div class="serie-image">
                        <img src="<?= BASE_URL . '/' . $serie['imagem'] ?>" alt="<?= $serie['titulo'] ?>">
                        <?php if (!empty($serie['onde_visto'])): ?>
                        <div class="streaming-icon" title="<?= $serie['onde_visto'] ?>">
                            <img src="<?= BASE_URL ?>/icons/<?= getStreamingIcon($serie['onde_visto']) ?>" alt="<?= $serie['onde_visto'] ?>">
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($serie['favorita']) && $serie['favorita']): ?>
                        <div class="favorite-icon" title="Série favorita">
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <?php endif; ?>

                <div class="card-actions">
                <a href="add/detalhesTv.php?id=<?= $serie['id'] ?>" class="card-action" title="Detalhes">
                    <i class="bi bi-plus"></i>
                </a>
                </div>

                    </div>
                    <div class="serie-info">
                        <h4><?= truncarTexto($serie['titulo'], 20) ?></h4>
                        <div>
                            <span class="serie-status" style="background-color: <?= getSerieStatusColor($serie['status']) ?>">
                                <?= $serie['status'] ?>
                            </span>
                        </div>
                        <div class="meta">
                            <span><?= $serie['ano_lancamento'] ?></span> |
                            <span><?= $serie['pais'] ?></span>
                        </div>
                        <div class="generos">
                            <?php foreach (array_slice($serie['generos'], 0, 3) as $genero): ?>
                            <span><?= $genero ?></span>
                            <?php endforeach; ?>
                        </div>
                        <!-- Barra de Progresso Adicionada -->
                        <div class="progress-container">
                            <div class="progress-label">
                                <span><?= $serie['progresso'] ?? 0 ?>%</span>
                                <span><?= $serie['nEpisodios'] ?? 0 ?> eps</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $serie['progresso'] ?? 0 ?>%"></div>
                            </div>
                        </div>
                        <div class="status-badge" style="background-color: <?= getStatusColor('Pausado') ?>">
                            Pausada
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-message">Nenhuma série pausada</div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Abandonadas -->
    <div class="year-section">
        <div class="year-header" style="background: linear-gradient(135deg, #f44336 0%, #e53935 100%);">
            <h3 class="year-title">Séries Abandonadas</h3>
            <span class="year-count"><?= $totalAbandonadas ?> séries</span>
        </div>
        
        <div class="series-grid">
            <?php if (!empty($seriesByStatus['abandonadas'])): ?>
                <?php foreach ($seriesByStatus['abandonadas'] as $serie): ?>
                <div class="serie-card">
                    <div class="serie-image">
                        <img src="<?= BASE_URL . '/' . $serie['imagem'] ?>" alt="<?= $serie['titulo'] ?>">
                        <?php if (!empty($serie['onde_visto'])): ?>
                        <div class="streaming-icon" title="<?= $serie['onde_visto'] ?>">
                            <img src="<?= BASE_URL ?>/icons/<?= getStreamingIcon($serie['onde_visto']) ?>" alt="<?= $serie['onde_visto'] ?>">
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($serie['favorita']) && $serie['favorita']): ?>
                        <div class="favorite-icon" title="Série favorita">
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <?php endif; ?>

                                        <div class="card-actions">
                <a href="add/detalhesTv.php?id=<?= $serie['id'] ?>" class="card-action" title="Detalhes">
                    <i class="bi bi-plus"></i>
                </a>
                </div>
                    </div>
                    <div class="serie-info">
                        <h4><?= truncarTexto($serie['titulo'], 20) ?></h4>
                        <div>
                            <span class="serie-status" style="background-color: <?= getSerieStatusColor($serie['status']) ?>">
                                <?= $serie['status'] ?>
                            </span>
                        </div>
                        <div class="meta">
                            <span><?= $serie['ano_lancamento'] ?></span> |
                            <span><?= $serie['pais'] ?></span>
                        </div>
                        <div class="generos">
                            <?php foreach (array_slice($serie['generos'], 0, 3) as $genero): ?>
                            <span><?= $genero ?></span>
                            <?php endforeach; ?>
                        </div>
                        <!-- Barra de Progresso Adicionada -->
                        <div class="progress-container">
                            <div class="progress-label">
                                <span><?= $serie['progresso'] ?? 0 ?>%</span>
                                <span><?= $serie['nEpisodios'] ?? 0 ?> eps</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $serie['progresso'] ?? 0 ?>%"></div>
                            </div>
                        </div>
                        <div class="status-badge" style="background-color: <?= getStatusColor('Abandonado') ?>">
                            Abandonada
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-message">Nenhuma série abandonada</div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Não Iniciadas -->
    <div class="year-section">
        <div class="year-header" style="background: linear-gradient(135deg, #607d8b 0%, #455a64 100%);">
            <h3 class="year-title">Séries Não Iniciadas</h3>
            <span class="year-count"><?= $totalNaoIniciadas ?> séries</span>
        </div>
        
        <div class="series-grid">
            <?php if (!empty($seriesByStatus['nao-iniciadas'])): ?>
                <?php foreach ($seriesByStatus['nao-iniciadas'] as $serie): ?>
                <div class="serie-card">
                    <div class="serie-image">
                        <img src="<?= BASE_URL . '/' . $serie['imagem'] ?>" alt="<?= $serie['titulo'] ?>">
                        <?php if (!empty($serie['onde_visto'])): ?>
                        <div class="streaming-icon" title="<?= $serie['onde_visto'] ?>">
                            <img src="<?= BASE_URL ?>/icons/<?= getStreamingIcon($serie['onde_visto']) ?>" alt="<?= $serie['onde_visto'] ?>">
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($serie['favorita']) && $serie['favorita']): ?>
                        <div class="favorite-icon" title="Série favorita">
                            <i class="bi bi-star-fill"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="serie-info">
                        <h4><?= truncarTexto($serie['titulo'], 20) ?></h4>
                        <div>
                            <span class="serie-status" style="background-color: <?= getSerieStatusColor($serie['status']) ?>">
                                <?= $serie['status'] ?>
                            </span>
                        </div>
                        <div class="meta">
                            <span><?= $serie['ano_lancamento'] ?></span> |
                            <span><?= $serie['pais'] ?></span>
                        </div>
                        <div class="generos">
                            <?php foreach (array_slice($serie['generos'], 0, 3) as $genero): ?>
                            <span><?= $genero ?></span>
                            <?php endforeach; ?>
                        </div>
                        <!-- Barra de Progresso Adicionada -->
                        <div class="progress-container">
                            <div class="progress-label">
                                <span><?= $serie['progresso'] ?? 0 ?>%</span>
                                <span><?= $serie['nEpisodios'] ?? 0 ?> eps</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?= $serie['progresso'] ?? 0 ?>%"></div>
                            </div>
                        </div>
                        <div class="status-badge" style="background-color: <?= getStatusColor('Não Assistido') ?>">
                            Não Iniciada
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-message">Nenhuma série não iniciada</div>
            <?php endif; ?>
        </div>
    </div>
</main>

<footer class="container">
    <p>&copy; <?= date('Y') ?> Minha Coleção de Séries</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script> 
// Adicione este código no seu arquivo index.php, dentro da tag <script> existente

document.addEventListener('DOMContentLoaded', function() {
    // Adiciona event listeners para todos os links de detalhes
    document.querySelectorAll('.card-action[href*="detalhesTv.php"]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault(); // Previne o comportamento padrão do link
            
            // Obtém o ID da série do href
            const url = new URL(this.href);
            const id = url.searchParams.get('id');
            
            if (id) {
                // Redireciona para a página de detalhes
                window.location.href = `add/detalhesTv.php?id=${id}`;
            }
        });
    });
});
</script>
</body>
</html>