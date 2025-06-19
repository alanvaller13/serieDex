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
$seriesExtras = json_decode(file_get_contents('data/OutrasSeries.json'), true) ?? [];

// Configuração de paginação
$seriesPorPagina = 4;
$paginaAtual = isset($_GET['pagina']) ? max(1, (int)$_GET['pagina']) : 1;
$totalSeries = count($seriesExtras);
$totalPaginas = ceil($totalSeries / $seriesPorPagina); // estava $seriesPorPagina
$seriesPaginadas = array_slice($seriesExtras, ($paginaAtual - 1) * $seriesPorPagina, $seriesPorPagina);



// Função para obter cor do status
function getStatusColor($status) {
    switch ($status) {
        case 'Não assistido': return '#666666';
        case 'Assistindo': return '#2196F3';
        case 'Concluída': return '#4CAF50'; // Adicione esta linha
        case 'Pausado': return '#FF9800';
        case 'Abandonado': return '#F44336';
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

// Exibir mensagens de feedback
if (isset($_GET['success'])) {
    $messages = [
        'series_deleted' => 'Série excluída com sucesso!'
    ];
    if (isset($messages[$_GET['success']])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($messages[$_GET['success']]) . '</div>';
    }
}

if (isset($_GET['error'])) {
    $errors = [
        'missing_id' => 'ID da série não especificado.',
        'series_not_found' => 'Série não encontrada.'
    ];
    if (isset($errors[$_GET['error']])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($errors[$_GET['error']]) . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Séries</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Estilos Gerais */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
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

.site-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #e50914, #f5f5f1, #e50914);
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
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    position: relative;
    animation: fadeInDown 0.8s ease;
}

.header-decoration {
    width: 100px;
    height: 4px;
    background: #e50914;
    margin: 1rem 0;
    border-radius: 2px;
    animation: scaleIn 0.8s ease 0.3s both;
}

/* Animações */
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
    from {
        transform: scaleX(0);
    }
    to {
        transform: scaleX(1);
    }
}

/* Responsivo */
@media (max-width: 768px) {
    .header-title {
        font-size: 2.2rem;
    }
}

@media (max-width: 480px) {
    .header-title {
        font-size: 1.8rem;
        letter-spacing: 1px;
    }
}
        /* Filtros de Busca */
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
            color: #495057;
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

        /* Botão de Cadastrar Série */
        .btn.btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 8px rgba(76, 175, 80, 0.2);
        }

        .btn.btn-primary:hover {
            background: linear-gradient(135deg, #43A047 0%, #1B5E20 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(76, 175, 80, 0.3);
        }

        /* Header */
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

        /* Grid de Séries - Versão Aprimorada */
        .series-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 10px;
            padding: 20px 0;
        }

        .serie-card {
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.1);
            position: relative;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .serie-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
        }

        .serie-image {
            position: relative;
            height: 50%;
            padding-bottom: 150%;
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
            background: #e50914;
            color: white;
            transform: scale(1.1);
        }

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
            color: #e50914;
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
    color: gold;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 2px;
}

.avaliacao i {
    font-size: 1.2rem;
    line-height: 1;
}

/* Se estiver usando o símbolo ½ para meia estrela */
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

        .progresso {
            height: 6px;
            background: #f0f0f0;
            border-radius: 3px;
            margin: 12px 0 16px;
            overflow: hidden;
        }

        .barra {
            height: 100%;
            background: linear-gradient(90deg, #e50914, #ff6b6b);
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

        /* Mensagem Vazia */
        .empty-message {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        /* Paginação */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 5px;
        }

        .pagination a, .pagination span {
            padding: 8px 16px;
            text-decoration: none;
            border: 1px solid #ddd;
            color: #333;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .pagination a:hover {
            background-color: #4CAF50;
            color: white;
            border-color: #4CAF50;
        }

        .pagination .active {
            background-color: #4CAF50;
            color: white;
            border-color: #4CAF50;
        }

        /* Responsividade */
        @media (max-width: 768px) {
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
        }

        /* Animações */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .serie-card {
            animation: fadeIn 0.5s ease forwards;
            opacity: 0;
        }

        .serie-card:nth-child(1) { animation-delay: 0.1s; }
        .serie-card:nth-child(2) { animation-delay: 0.2s; }
        .serie-card:nth-child(3) { animation-delay: 0.3s; }
        .serie-card:nth-child(4) { animation-delay: 0.4s; }
    </style>
</head>
<body>
<header class="site-header">
    <div class="container">
        <div class="header-content">
            <h1 class="header-title">Bora?</h1>
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
                <a href="series/cadastrarTv.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle-fill"></i> Adicionar Série
                </a>
                <a href="series/cadastrarEps.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle-fill"></i> Cadastrar Episódio
                </a>
            </div>
        </div>

        <div class="search-filters">
            <div>
                <label for="search">Pesquisar</label>
                <input type="text" id="search" placeholder="Digite o título da série..." onkeyup="filtrarSeries()">
            </div>
        </div>
        <h3>Outras Séries</h3>
<div class="series-grid" id="outras-series-container">
<?php if (count($seriesPaginadas) > 0): ?>
    <?php foreach ($seriesPaginadas as $serie): ?>
                <div class="serie-card" 
                     data-status="<?= strtolower(str_replace(' ', '-', $serie['status'])) ?>"
                     data-user-status="<?= strtolower(str_replace(' ', '-', $serie['user_status'] ?? 'nao-assistido')) ?>">
                    <div class="serie-image">
                        <img src="<?= BASE_URL . '/' . $serie['imagem'] ?>" alt="<?= $serie['titulo'] ?>">
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
    
    // Estrelas cheias
    echo str_repeat('<i class="bi bi-star-fill"></i>', $estrelasCheias);
    
    // Meia estrela
    if ($temMeiaEstrela) {
        echo '<i class="bi bi-star-half"></i>';
    }
    
    // Estrelas vazias
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
            
        <?php endforeach; ?>
    <?php else: ?>
        <p>Nenhuma outra série cadastrada.</p>
    <?php endif; ?>
</div>
        <?php if ($totalPaginas > 1): ?>
        <div class="pagination">
            <?php if ($paginaAtual > 1): ?>
                <a href="?pagina=1">&laquo;</a>
                <a href="?pagina=<?= $paginaAtual - 1 ?>">&lsaquo;</a>
            <?php else: ?>
                <span class="disabled">&laquo;</span>
                <span class="disabled">&lsaquo;</span>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <?php if ($i == $paginaAtual): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="?pagina=<?= $i ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($paginaAtual < $totalPaginas): ?>
                <a href="?pagina=<?= $paginaAtual + 1 ?>">&rsaquo;</a>
                <a href="?pagina=<?= $totalPaginas ?>">&raquo;</a>
            <?php else: ?>
                <span class="disabled">&rsaquo;</span>
                <span class="disabled">&raquo;</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <script>
            function filtrarSeries() {
                const searchTerm = document.getElementById('search').value.toLowerCase();
                const filterStatus = document.getElementById('filter-status').value;
                const filterUserStatus = document.getElementById('filter-user-status').value;
                const series = document.querySelectorAll('.serie-card');
                
                series.forEach(serie => {
                    const title = serie.querySelector('h4').textContent.toLowerCase();
                    const status = serie.getAttribute('data-status');
                    const userStatus = serie.getAttribute('data-user-status');
                    
                    const matchesSearch = title.includes(searchTerm);
                    const matchesStatus = filterStatus === 'all' || status === filterStatus;
                    const matchesUserStatus = filterUserStatus === 'all' || userStatus === filterUserStatus;
                    
                    if (matchesSearch && matchesStatus && matchesUserStatus) {
                        serie.style.display = 'block';
                    } else {
                        serie.style.display = 'none';
                    }
                });
            }

function handleUserAction(event, serieId) {
    event.preventDefault();
    window.location.href = `series/userAction.php?id=${serieId}`;
}
        </script>
    </main>

    <footer class="container">
        <p>&copy; <?= date('Y') ?> Meu Projeto de Séries</p>
    </footer>
</body>
</html>