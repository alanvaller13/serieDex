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

// Configuração de paginação
$seriesPorPagina = 4;
$paginaAtual = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($paginaAtual - 1) * $seriesPorPagina;
$seriesPaginadas = array_slice($series, $offset, $seriesPorPagina);

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

// Função para obter ícone do streaming
function getStreamingIcon($ondeVisto) {
    $icons = [
        'Tv' => '1.png',
        'Tv' => 'tv.png'
        'Web' => '2.png',
        'Web' => 'web.png',
        'Netflix' => '3.png',
        'Netflix' => 'netflix.png',
        'PrimeVideo' => '4.png',
        'PrimeVideo' => 'primevideo.png',
        'PrimeVideo' => 'prime-video.png',
        'GloboPlay' => '5.png',
        'GloboPlay' => 'globoplay.png',
        'Disney+' => '6.png',
        'Disney+' => 'disneyplus.png',
        'Disney+' => 'disneymais.png',
        'DisneyPlus' => '6.png',
        'DisneyPlus' => 'disneyplus.png',
        'DisneyPlus' => 'disneymais.png',
        'ParamountPlus' => '7.png',
        'ParamountPlus' => 'paramountplus.png',
        'ParamountPlus' => 'paramountmais.png',
        'Paramount+' => '7.png',
        'Paramount+' => 'paramountplus.png',
        'Paramount+' => 'paramountmais.png',
        'HBO MAX' => '8.png',
        'HBO MAX' => 'hbomax.png',
        'HBO MAX' => 'max.png',
        'PlutoTV' => '9.png',
        'PlutoTV' => 'pluto.png',
        'PlutoTV' => 'plutotv.png'
    ];
    return $icons[$ondeVisto] ?? 'default.png';
}

// Definir BASE_URL se não estiver definida
if (!defined('BASE_URL')) {
    define('BASE_URL', 'https://dexseries.onrender.com');
}

// Função para truncar texto
function truncarTexto($texto, $limite = 20) {
    if (strlen($texto) > $limite) {
        return substr($texto, 0, $limite) . "...";
    }
    return $texto;
}

// Verifica se há séries para exibir
if (empty($seriesPaginadas)) {
    exit; // Encerra o script se não houver séries
}

// Inicia o buffer de saída
ob_start();
?>

<?php foreach ($seriesPaginadas as $serie): ?>
<div class="serie-card" 
     data-status="<?= strtolower(str_replace(' ', '-', $serie['status'])) ?>"
     data-user-status="<?= strtolower(str_replace(' ', '-', $serie['user_status'] ?? 'nao-assistido')) ?>">
    <div class="serie-image">
        <img src="<?= BASE_URL . '/' . $serie['imagem'] ?>" alt="<?= htmlspecialchars($serie['titulo']) ?>" onerror="this.src='<?= BASE_URL ?>/assets/default-poster.jpg'">
        
        <?php if (!empty($serie['onde_visto'])): ?>
        <div class="streaming-icon" title="<?= htmlspecialchars($serie['onde_visto']) ?>">
            <img src="<?= BASE_URL ?>/assets/icons/<?= getStreamingIcon($serie['onde_visto']) ?>" alt="<?= htmlspecialchars($serie['onde_visto']) ?>" onerror="this.src='<?= BASE_URL ?>/assets/icons/default.png'">
        </div>
        <?php endif; ?>
        
        <?php if (isset($serie['favorita']) && $serie['favorita']): ?>
        <div class="favorite-icon" title="Série favorita">
            <i class="bi bi-star-fill"></i>
        </div>
        <?php endif; ?>
        
        <div class="card-actions"> 
            <a href="series/detalhesTv.php?id=<?= $serie['id'] ?>" class="card-action" title="Detalhes">
                <i class="bi bi-plus"></i>
            </a>
            
            <!-- </a><a href="series/editarTv.php?id=<?= $serie['id'] ?>" class="card-action" title="Editar">
                <i class="bi bi-pencil"></i>
            </a>
            <a href="series/excluirTv.php?id=<?= $serie['id'] ?>" class="card-action" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir esta série?')">
                <i class="bi bi-trash"></i>
            </a>
            <a href="other/userAction.php?id=<?= $serie['id'] ?>" class="card-action user-action" title="Ação do Usuário" onclick="handleUserAction(event, <?= $serie['id'] ?>)">
                <i class="bi bi-person"></i>
            </a>
             -->
        </div>
    </div>
    <div class="serie-info">
        <h4><?= truncarTexto(htmlspecialchars($serie['titulo']), 20) ?></h4>
        
        <div>
            <span class="serie-status" style="background-color: <?= getSerieStatusColor($serie['status']) ?>">
                <?= htmlspecialchars($serie['status']) ?>
            </span>
        </div>
        
        <div class="meta">
            <span><?= htmlspecialchars($serie['ano_lancamento']) ?></span> |
            <span><?= htmlspecialchars($serie['pais']) ?></span>
        </div>
        
        <div class="generos">
            <?php foreach (array_slice($serie['generos'], 0, 3) as $genero): ?>
            <span><?= htmlspecialchars($genero) ?></span>
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
        
        <div class="progress-container">
            <div class="progress-label">
                <span><?= $serie['progresso'] ?? 0 ?>%</span>
                <span><?= $serie['nEpisodios'] ?? 0 ?> eps</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= $serie['progresso'] ?? 0 ?>%"></div>
            </div>
        </div>
        
        <div class="status-badge" style="background-color: <?= getStatusColor($serie['user_status'] ?? 'Não assistido') ?>">
            <?= htmlspecialchars($serie['user_status'] ?? 'Não assistido') ?>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php
// Limpa o buffer e envia a saída
ob_end_flush();
?>
