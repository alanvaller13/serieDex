<?php
require_once '../other/seriesFunctions.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php?error=missing_id');
    exit;
}

$serie = getSerieById($id);
if (!$serie) {
    header('Location: index.php?error=series_not_found');
    exit;
}

// Carregar episódios do arquivo JSON
$episodesFile = "../episodio/{$id}.json";
$temporadas = [];

if (file_exists($episodesFile)) {
    $episodesData = json_decode(file_get_contents($episodesFile), true);
    if ($episodesData) {
        $temporadas = $episodesData;
    }
}

// Configurações básicas
$hasTemporadas = count($temporadas) > 0;
$nTemporadas = $hasTemporadas ? count($temporadas) : 0;
$totalEpisodios = $serie['nEpisodios'] ?? 0;

// Calcular progresso
$progressoGeral = $serie['progresso'] ?? 0;
$totalAssistidos = 0;

if ($hasTemporadas) {
    foreach ($temporadas as $temporada) {
        if (isset($temporada['episodios'])) {
            $episodiosAssistidos = array_filter($temporada['episodios'], function($ep) { 
                return isset($ep['assistido']) && $ep['assistido']; 
            });
            $totalAssistidos += count($episodiosAssistidos);
        }
    }
    if (!$totalEpisodios) {
        $totalEpisodios = $totalAssistidos; // Fallback se nEpisodios não estiver definido
    }
    if (!$progressoGeral && $totalEpisodios > 0) {
        $progressoGeral = round(($totalAssistidos / $totalEpisodios) * 100);
    }
}

// Mapeamento de plataformas para ícones
$plataformaIcons = [
        'Tv' => '1.png', 'tv.png',
        'Web' => '2.png', 'web.png',
        'Netflix' => '3.png','netflix.png',
        'PrimeVideo' => '4.png','primevideo.png', 'prime-video.png',
        'GloboPlay' => '5.png', 'globoplay.png',
        'Disney+' => '6.png', 'disneyplus.png', 'disneymais.png',
        'DisneyPlus' => '6.png', 'disneyplus.png', 'disneymais.png',
        'ParamountPlus' => '7.png', 'paramountplus.png', 'paramountmais.png',
        'Paramount+' => '7.png', 'paramountplus.png', 'paramountmais.png',
        'HBO MAX' => '8.png', 'hbomax.png', 'max.png', 'hbo.png',
        'PlutoTV' => '9.png', 'pluto.png', 'plutotv.png',
        'YouTube' => '10.png', 'youtube.png',
];

$plataforma = $serie['onde_visto'] ?? '';
$plataformaIcon = isset($plataformaIcons[$plataforma]) ? 
    "../assets/icons/" . $plataformaIcons[$plataforma] : 
    "../assets/icons/" . $plataformaIcons['default'];

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

// Função para estrelas de avaliação
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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($serie['titulo']) ?> | DexSeries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
    :root {
        --primary-gradient: linear-gradient(135deg, #43A047 0%, #1B5E20 100%);
        --primary-color: #43A047;
        --primary-dark: #1B5E20;
        --secondary-color: #e50914;
        --bg-color: rgb(96, 105, 114);
        --card-bg: rgba(255, 255, 255, 0.95);
        --text-dark: #2c3e50;
        --text-medium: #495057;
        --text-light: #f8f9fa;
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.12);
        --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.16);
        --shadow-lg: 0 10px 20px rgba(0, 0, 0, 0.19);
        --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }

    body {
        font-family: 'Segoe UI', 'Roboto', sans-serif;
        background-color: var(--bg-color);
        color: var(--text-dark);
        line-height: 1.6;
        min-height: 100vh;
        padding-top: 2rem;
    }

    .glass-card {
        background: var(--card-bg);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-radius: 16px;
        box-shadow: var(--shadow-lg);
        overflow: hidden;
        transition: var(--transition);
    }

    .serie-header {
        position: relative;
        overflow: hidden;
        border-radius: 16px 16px 0 0;
        height: 300px;
    }

    .serie-backdrop {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        opacity: 0.2;
        z-index: 0;
    }

    .serie-poster-container {
        position: relative;
        z-index: 1;
        height: 100%;
        display: flex;
        align-items: flex-end;
        padding: 2rem;
        background: linear-gradient(to top, var(--bg-color) 10%, transparent 90%);
    }

    .serie-poster {
        width: 200px;
        height: 300px;
        border-radius: 8px;
        box-shadow: var(--shadow-lg);
        object-fit: cover;
        border: 3px solid white;
        transition: var(--transition);
    }

    .serie-title {
        flex: 1;
        padding-left: 2rem;
        color: white;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    }

    .serie-title h1 {
        font-weight: 800;
        margin-bottom: 0.5rem;
    }

    .serie-meta {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .meta-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        backdrop-filter: blur(5px);
    }

    .btn-voltar {
        position: absolute;
        top: 2rem;
        left: 2rem;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        z-index: 2;
        transition: var(--transition);
    }

    .btn-voltar:hover {
        background: var(--secondary-color);
        transform: scale(1.1);
    }

    .serie-content {
        padding: 2rem;
    }

    .nav-tabs {
        border-bottom: 2px solid rgba(0, 0, 0, 0.1);
    }

    .nav-tabs .nav-link {
        color: var(--text-medium);
        font-weight: 600;
        border: none;
        padding: 1rem 1.5rem;
        transition: var(--transition);
    }

    .nav-tabs .nav-link.active {
        color: var(--primary-color);
        background: transparent;
        border-bottom: 3px solid var(--primary-color);
    }

    .nav-tabs .nav-link:hover {
        color: var(--primary-color);
        background: rgba(67, 160, 71, 0.1);
    }

    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .info-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
    }

    .info-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
    }

    .info-label {
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--text-medium);
        margin-bottom: 0.5rem;
        font-weight: 600;
    }

    .info-value {
        font-size: 1.1rem;
        font-weight: 500;
        color: var(--text-dark);
    }

    .status-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        color: white;
    }

    .progress-container {
        margin-top: 1rem;
    }

    .progress-label {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .progress-bar-custom {
        height: 8px;
        background: #f0f0f0;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: var(--primary-gradient);
        border-radius: 4px;
        transition: width 0.6s ease;
    }

    .avaliacao {
        color: gold;
        font-size: 1.5rem;
        letter-spacing: 2px;
    }

    .temporada-container {
        margin-bottom: 2rem;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
    }

    .temporada-container:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }

    .temporada-header {
        padding: 1.5rem;
        background: var(--primary-gradient);
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .temporada-title {
        font-weight: 700;
        margin: 0;
    }

    .temporada-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.9rem;
    }

    .episodio-item {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: var(--transition);
    }

    .episodio-item:last-child {
        border-bottom: none;
    }

    .episodio-item:hover {
        background: rgba(67, 160, 71, 0.05);
    }

    .episodio-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .episodio-numero {
        font-weight: 700;
        color: var(--primary-color);
        min-width: 40px;
    }

    .episodio-status {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .plataforma-icon {
        width: 24px;
        height: 24px;
        object-fit: contain;
        margin-right: 0.5rem;
    }

    .sinopse {
        font-size: 1.1rem;
        line-height: 1.8;
        color: var(--text-dark);
    }

    .favorite-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: gold;
        color: var(--text-dark);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        box-shadow: var(--shadow-sm);
        z-index: 2;
    }

    @media (max-width: 768px) {
        .serie-header {
            height: auto;
            flex-direction: column;
        }
        
        .serie-poster-container {
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 1rem;
        }
        
        .serie-poster {
            width: 150px;
            height: 225px;
            margin-bottom: 1rem;
        }
        
        .serie-title {
            padding-left: 0;
            text-align: center;
        }
        
        .serie-meta {
            justify-content: center;
        }
        
        .info-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
</head>
<body>
<div class="container">
    <div class="glass-card animate__animated animate__fadeIn">
        <!-- Cabeçalho com poster e título -->
        <div class="serie-header" style="background-color: <?= getSerieStatusColor($serie['status']) ?>">
            <div class="serie-backdrop" style="background-image: url('../<?= htmlspecialchars($serie['imagem']) ?>')"></div>
            <a href="../index.php" class="btn-voltar">
                <i class="bi bi-arrow-left"></i>
            </a>
            
            <?php if (isset($serie['favorita']) && $serie['favorita']): ?>
            <div class="favorite-badge">
                <i class="bi bi-star-fill"></i> Favorita
            </div>
            <?php endif; ?>
            
            <div class="serie-poster-container">
                <img src="../<?= htmlspecialchars($serie['imagem']) ?>" alt="<?= htmlspecialchars($serie['titulo']) ?>" class="serie-poster">
                <div class="serie-title">
                    <h1><?= htmlspecialchars($serie['titulo']) ?></h1>
                    <div class="serie-meta">
                        <span class="meta-badge"><?= $serie['ano_lancamento'] ?></span>
                        <span class="meta-badge"><?= $serie['pais'] ?></span>
                        <span class="meta-badge">
                            <?php if (!empty($serie['onde_visto'])): ?>
                            <img src="<?= $plataformaIcon ?>" class="plataforma-icon" alt="<?= $serie['onde_visto'] ?>">
                            <?= $serie['onde_visto'] ?>
                            <?php endif; ?>
                        </span>
                        <span class="meta-badge">
                            <span class="avaliacao"><?= renderAvaliacao($serie['avaliacao']) ?></span>
                            <?= number_format($serie['avaliacao'], 1) ?>/5
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conteúdo principal -->
        <div class="serie-content">
            <ul class="nav nav-tabs" id="serieTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab" aria-controls="info" aria-selected="true">
                        <i class="bi bi-info-circle"></i> Informações
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="temporadas-tab" data-bs-toggle="tab" data-bs-target="#temporadas" type="button" role="tab" aria-controls="temporadas" aria-selected="false">
                        <i class="bi bi-collection-play"></i> Temporadas (<?= $nTemporadas ?>)
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="serieTabsContent">
                <!-- Aba de Informações -->
                <div class="tab-pane fade show active" id="info" role="tabpanel" aria-labelledby="info-tab">
                    <div class="info-grid">
                        <div class="info-card">
                            <div class="info-label">Título Original</div>
                            <div class="info-value"><?= htmlspecialchars($serie['titulo_original']) ?></div>
                        </div>
                        
                        <div class="info-card">
                            <div class="info-label">Status da Série</div>
                            <div class="info-value">
                                <span class="status-badge" style="background-color: <?= getSerieStatusColor($serie['status']) ?>">
                                    <?= htmlspecialchars($serie['status']) ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <div class="info-label">Meu Status</div>
                            <div class="info-value">
                                <span class="status-badge" style="background-color: <?= getStatusColor($serie['user_status'] ?? 'Não assistido') ?>">
                                    <?= htmlspecialchars($serie['user_status'] ?? 'Não assistido') ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <div class="info-label">Progresso</div>
                            <div class="progress-container">
                                <div class="progress-label">
                                    <span><?= $progressoGeral ?>% completo</span>
                                    <span><?= $totalAssistidos ?> de <?= $totalEpisodios ?> episódios</span>
                                </div>
                                <div class="progress-bar-custom">
                                    <div class="progress-fill" style="width: <?= $progressoGeral ?>%"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-card">
                            <div class="info-label">Classificação</div>
                            <div class="info-value"><?= htmlspecialchars($serie['classificacao']) ?>+</div>
                        </div>
                        
                        <div class="info-card">
                            <div class="info-label">Idioma</div>
                            <div class="info-value"><?= htmlspecialchars($serie['idioma']) ?></div>
                        </div>
                        
                        <?php if (!empty($serie['emissora'])): ?>
                        <div class="info-card">
                            <div class="info-label">Emissora</div>
                            <div class="info-value"><?= htmlspecialchars($serie['emissora']) ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="info-card">
                            <div class="info-label">Gêneros</div>
                            <div class="info-value">
                                <?php foreach ($serie['generos'] as $genero): ?>
                                <span class="badge bg-secondary me-1 mb-1"><?= htmlspecialchars($genero) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h5 class="fw-bold mb-3">Sinopse</h5>
                        <p class="sinopse"><?= nl2br(htmlspecialchars($serie['sinopse'])) ?></p>
                    </div>
                </div>

                <!-- Aba de Temporadas -->
                <div class="tab-pane fade" id="temporadas" role="tabpanel" aria-labelledby="temporadas-tab">
                    <?php if ($hasTemporadas): ?>
                        <?php foreach ($temporadas as $temporada): 
                            $episodiosTemporada = $temporada['episodios'] ?? [];
                            $episodiosAssistidos = array_filter($episodiosTemporada, function($ep) { 
                                return isset($ep['assistido']) && $ep['assistido']; 
                            });
                            $progressoTemporada = count($episodiosTemporada) > 0 ? 
                                round((count($episodiosAssistidos) / count($episodiosTemporada) * 100)) : 0;
                        ?>
                            <div class="temporada-container mt-3">
                                <div class="temporada-header">
                                    <h3 class="temporada-title">Temporada <?= $temporada['temporada'] ?? 1 ?></h3>
                                    <?php if (!empty($temporada['codinome']) && $temporada['codinome'] != '-'): ?>
                                        <small><?= htmlspecialchars($temporada['codinome']) ?></small>
                                    <?php endif; ?>
                                    <div class="temporada-meta">
                                        <span><?= count($episodiosTemporada) ?> Episódios</span>
                                      -  <span><?= count($episodiosAssistidos) ?> Assistidos</span> -
                                        <span><?= $progressoTemporada ?>%</span>
                                    </div>
                                </div>
                                
                                <?php if (count($episodiosTemporada) > 0): ?>
                                    <?php foreach ($episodiosTemporada as $episodio): ?>
                                        <div class="episodio-item">
                                            <div class="episodio-info">
                                                <span class="episodio-numero"><?= $episodio['codigo'] ?? 'E1' ?></span>
                                                <div>
                                                    <h6 class="mb-0"><?= htmlspecialchars($episodio['nome'] ?? 'Episódio sem título') ?></h6>
                                                    <?php if (!empty($episodio['data_lancamento']) && $episodio['data_lancamento'] != '00/00/0000'): ?>
                                                        <small class="text-muted"><?= $episodio['data_lancamento'] ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div>
                                                <span class="episodio-status" style="background-color: <?= isset($episodio['assistido']) && $episodio['assistido'] ? getStatusColor('Concluída') : '#6c757d' ?>">
                                                    <?= isset($episodio['assistido']) && $episodio['assistido'] ? 'Assistido' : 'Não assistido' ?>
                                                </span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="p-3 text-center text-muted">
                                        Nenhum episódio cadastrado para esta temporada
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info mt-3">
                            Nenhuma temporada cadastrada para esta série.
                        </div>
                    <?php endif; ?>
                    
                    <!-- Resumo geral -->
                    <div class="glass-card mt-4 p-3" style="background: var(--primary-gradient); color: white;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0"><i class="bi bi-trophy-fill"></i> Progresso Geral</h5>
                            </div>
                            <div class="text-end">
                                <h4 class="mb-0"><?= $progressoGeral ?>%</h4>
                                <small><?= $totalAssistidos ?> de <?= $totalEpisodios ?> episódios</small>
                            </div>
                        </div>
                        <div class="progress mt-2" style="height: 8px; background: rgba(255,255,255,0.3);">
                            <div class="progress-bar" style="width: <?= $progressoGeral ?>%; background: white;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Inicializa as tabs do Bootstrap
    var tabs = new bootstrap.Tab(document.querySelector('#info-tab'));
    tabs.show();
</script>
</body>
</html>
