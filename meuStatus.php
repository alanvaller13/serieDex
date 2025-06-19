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
$topGeneros = array_slice($generosCount, 0, 25);

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
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #212529;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .site-header {
            background: linear-gradient(135deg, #2c3e50 0%, #1a1a2e 100%);
            color: white;
            padding: 2rem 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            margin-bottom: 2rem;
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
                <?php foreach ($topStreaming as $streaming => $count): ?>
                <li>
                    <span><?= $streaming ?></span>
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
</body>
</html>