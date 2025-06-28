<?php
// Definir BASE_URL se não estiver definida
if (!defined('BASE_URL')) {
    define('BASE_URL', 'https://dexseries.onrender.com/');
}

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

// Configuração de paginação
$seriesPorPagina = 8;
$paginaAtual = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($paginaAtual - 1) * $seriesPorPagina;
$seriesPaginadas = array_slice($series, $offset, $seriesPorPagina);

// Função para obter cor do status da série
function getSerieStatusColor($status) {
    $status = strtolower($status);
    switch ($status) {
        case 'em exibição': return '#4CAF50';
        case 'finalizada': return '#2196F3';
        case 'cancelada': return '#F44336';
        case 'renovada': return '#FFC107';
        case 'em hiato': return '#FF9800';
        default: return '#666666';
    }
}

// Função para obter cor do status
function getStatusColor($status) {
    $status = strtolower($status);
    switch ($status) {
        case 'não assistido': return '#666666';
        case 'assistindo': return '#2196F3';
        case 'concluída': return '#4CAF50';
        case 'pausado': return '#FF9800';
        case 'abandonado': return '#F44336';
        case 'em dia': return '#FFC107';
        default: return '#666666';
    }
}

// Função para obter ícone do streaming
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
        'PlutoTV' => '9.png',
        'default' => 'default.png'
    ];
    
    return $icons[$ondeVisto] ?? $icons['default'];
}

// Função para renderizar estrelas de avaliação
function renderAvaliacao($nota) {
    $estrelas = '';
    $nota = (float)$nota;
    $estrelasCheias = floor($nota);
    $temMeiaEstrela = ($nota - $estrelasCheias) >= 0.5;
    
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $estrelasCheias) {
            $estrelas .= '<i class="bi bi-star-fill"></i>';
        } elseif ($i == $estrelasCheias + 1 && $temMeiaEstrela) {
            $estrelas .= '<i class="bi bi-star-half"></i>';
        } else {
            $estrelas .= '<i class="bi bi-star"></i>';
        }
    }
    
    return $estrelas;
}

foreach ($seriesPaginadas as $serie): ?>
<div class="serie-card" 
     data-status="<?= strtolower(str_replace(' ', '-', $serie['status'])) ?>"
     data-user-status="<?= strtolower(str_replace(' ', '-', $serie['user_status'] ?? 'nao-assistido')) ?>">
    <div class="serie-image">
        <img src="<?= BASE_URL . '/' . $serie['imagem'] ?>" alt="<?= $serie['titulo'] ?>">
        
        <?php if (!empty($serie['onde_visto'])): ?>
        <span class="card-badge streaming-badge">
            <img src="<?= BASE_URL ?>/assets/icons/<?= getStreamingIcon($serie['onde_visto']) ?>" class="streaming-icon" alt="<?= $serie['onde_visto'] ?>">
            <?= $serie['onde_visto'] ?>
        </span>
        <?php endif; ?>
        
        <?php if (isset($serie['favorita']) && $serie['favorita']): ?>
        <span class="card-badge favorite-badge">
            <i class="bi bi-star-fill"></i> Favorita
        </span>
        <?php endif; ?>
        
        <span class="card-badge status-badge" style="background-color: <?= getSerieStatusColor($serie['status']) ?>">
            <?= $serie['status'] ?>
        </span>
        
        <div class="card-actions">
            <a href="series/editarTv.php?id=<?= $serie['id'] ?>" class="card-action" title="Editar">
                <i class="bi bi-pencil"></i>
            </a>
            <a href="series/excluirTv.php?id=<?= $serie['id'] ?>" class="card-action" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir esta série?')">
                <i class="bi bi-trash"></i>
            </a>
            <a href="series/detalhesTv.php?id=<?= $serie['id'] ?>" class="card-action" title="Detalhes">
                <i class="bi bi-eye-fill"></i>
            </a>
        </div>
    </div>
    <div class="serie-info">
        <h4><?= htmlspecialchars($serie['titulo']) ?></h4>
        
        <div class="serie-meta">
            <span><?= $serie['ano_lancamento'] ?></span> |
            <span><?= $serie['pais'] ?></span>
        </div>
        
        <?php if (!empty($serie['generos'])): ?>
        <div class="generos">
            <?php foreach (array_slice($serie['generos'], 0, 3) as $genero): ?>
            <span><?= htmlspecialchars($genero) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <div class="avaliacao">
            <?= renderAvaliacao($serie['avaliacao']) ?>
            <small><?= number_format($serie['avaliacao'], 1) ?>/5</small>
        </div>
        
        <div class="progress-container">
            <div class="progress-label">
                <span><?= $serie['progresso'] ?? 0 ?>%</span>
                <span><?= $serie['nEpisodios'] ?? 0 ?> eps</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $serie['progresso'] ?? 0 ?>%"></div>
            </div>
        </div>
        
        <div class="user-status" style="background-color: <?= getStatusColor($serie['user_status'] ?? 'Não assistido') ?>">
            <?= $serie['user_status'] ?? 'Não assistido' ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
