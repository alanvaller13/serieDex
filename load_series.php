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

// Função para obter ícone do streaming
function getStreamingIcon($ondeVisto) {
    $icons = [
        "Tv" => "1.png",
        'Web' => "2.png",
        'Netflix' => 'netflix.png',
        'Prime Video' => 'prime-video.png',
        'Disney+' => 'disneymais.png',
        'HBO Max' => 'hbo.png',
        'Apple TV+' => 'apple.png',
        'GloboPlay' => 'globoplay.png',
        'Star+' => 'star.png',
        'Paramount+' => 'paramountmais.png',
        'Crunchyroll' => 'crunchyroll.png'
    ];
    
    return $icons[$ondeVisto] ?? 'default.png';
}

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

$series = loadJsonData('series.json');
$seriesPorPagina = 4;
$paginaAtual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($paginaAtual - 1) * $seriesPorPagina;
$seriesPaginadas = array_slice($series, $offset, $seriesPorPagina);

foreach ($seriesPaginadas as $serie): ?>
<div class="serie-card" 
     data-status="<?= strtolower(str_replace(' ', '-', $serie['status'])) ?>"
     data-user-status="<?= strtolower(str_replace(' ', '-', $serie['user_status'] ?? 'nao-assistido')) ?>">
    <div class="serie-image">
        <img src="<?= BASE_URL . '/' . $serie['imagem'] ?>" alt="<?= $serie['titulo'] ?>">
        <?php if (!empty($serie['onde_visto'])): ?>
        <div class="streaming-icon" title="<?= $serie['onde_visto'] ?>">
            <img src="<?= BASE_URL ?>/assets/icons/<?= getStreamingIcon($serie['onde_visto']) ?>" alt="<?= $serie['onde_visto'] ?>">
        </div>
        <?php endif; ?>
        
        <?php if (isset($serie['favorita']) && $serie['favorita']): ?>
        <div class="favorite-icon" title="Série favorita">
            <i class="bi bi-star-fill"></i>
        </div>
        <?php endif; ?>
        
        <div class="card-actions">
            <a href="series/editarTv.php?id=<?= $serie['id'] ?>" class="card-action" title="Editar">
                <i class="bi bi-pencil"></i>
            </a>
            <a href="series/excluirTv.php?id=<?= $serie['id'] ?>" class="card-action" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir esta série?')">
                <i class="bi bi-trash"></i>
            </a>
            <a href="series/detalhesTv.php?id=<?= $serie['id'] ?>" class="card-action" title="Detalhes">
                <i class="bi bi-plus"></i>
            </a>
            <a href="other/userAction.php?id=<?= $serie['id'] ?>" class="card-action user-action" title="Ação do Usuário" onclick="handleUserAction(event, <?= $serie['id'] ?>)">
                <i class="bi bi-person"></i>
            </a>
        </div>
    </div>
    <div class="serie-info">
        <h4><?= $serie['titulo'] ?></h4>
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
        <div class="avaliacao">
            <?php
            $avaliacao = $serie['avaliacao'];
            $estrelasCheias = floor($avaliacao);
            $temMeiaEstrela = ($avaliacao - $estrelasCheias) >= 0.5;
            $estrelasVazias = 5 - $estrelasCheias - ($temMeiaEstrela ? 1 : 0);
            
            echo str_repeat('<i class="bi bi-star-fill"></i>', $estrelasCheias);
            
            if ($temMeiaEstrela) {
                echo '<i class="bi bi-star-half"></i>';
            }
            
            echo str_repeat('<i class="bi bi-star"></i>', $estrelasVazias);
            ?>
        </div>
        <div class="progresso">
            <div class="barra" style="width: <?= $serie['progresso'] ?? 0 ?>%"></div>
        </div>
        <div class="status-badge" style="background-color: <?= getStatusColor($serie['user_status'] ?? 'Não assistido') ?>">
            <?= $serie['user_status'] ?? 'Não assistido' ?>
        </div>
    </div>
</div>
<?php endforeach;
