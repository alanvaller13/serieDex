<?php
require_once '../other/config.php';
require_once '../other/seriesFunctions.php';

$serieId = $_GET['id'] ?? null;
if (!$serieId) {
    header('Location: ../index.php?error=missing_id');
    exit;
}

$serie = getSerieById($serieId);
if (!$serie) {
    header('Location: ../index.php?error=series_not_found');
    exit;
}

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userStatus = $_POST['user_status'] ?? 'Não assistido';
    $isFavorite = isset($_POST['favorita']) ? true : false;
    $completionDate = null;
    
    // Tratar data de conclusão se for status "Concluída"
    if ($userStatus === 'Concluída' && isset($_POST['completion_type'])) {
        if ($_POST['completion_type'] === 'recente') {
            $completionDate = $_POST['completion_date'] ?? date('Y-m');
        }
    }
    
    // Calcular progresso baseado em episódios assistidos
    $progresso = 0;
    $temporadasAtualizadas = $serie['temporadas'] ?? [];
    
    if (isset($_POST['episodios'])) {
        $totalEpisodios = 0;
        $assistidos = 0;
        
        foreach ($temporadasAtualizadas as $temporadaIndex => $temporada) {
            foreach ($temporada['episodios'] as $episodioIndex => $episodio) {
                $totalEpisodios++;
                $epId = "T{$temporada['numero']}E{$episodio['codigo']}";
                
                if (in_array($epId, $_POST['episodios'])) {
                    $temporadasAtualizadas[$temporadaIndex]['episodios'][$episodioIndex]['assistido'] = true;
                    $assistidos++;
                } else {
                    $temporadasAtualizadas[$temporadaIndex]['episodios'][$episodioIndex]['assistido'] = false;
                }
            }
        }
        
        $progresso = $totalEpisodios > 0 ? round(($assistidos / $totalEpisodios) * 100) : 0;
    }
    
    // Preparar dados para atualização
    $updateData = [
        'user_status' => $userStatus,
        'progresso' => $progresso,
        'favorita' => $isFavorite,
        'temporadas' => $temporadasAtualizadas
    ];
    
    if ($completionDate) {
        $updateData['data_conclusao'] = $completionDate;
    }
    
    // Salvar as alterações
    if (updateSerie($serieId, $updateData)) {
        header('Location: ' . $_SERVER['HTTP_REFERER'] . '?success=user_updated');
        exit;
    } else {
        $error = "Erro ao atualizar a série. Tente novamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ação do Usuário - <?= htmlspecialchars($serie['titulo']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #e50914;
            --secondary-color: #f5f5f5;
            --dark-color: #212529;
            --light-color: #f8f9fa;
        }
        
        body {
            background-color: var(--light-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        
        .serie-header {
            display: flex;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .serie-poster {
            width: 100px;
            height: 150px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 20px;
        }
        
        .status-option {
            display: block;
            padding: 10px 15px;
            margin-bottom: 8px;
            border-radius: 5px;
            background: #f8f9fa;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .status-option:hover {
            background: #e9ecef;
        }
        
        .status-option input[type="radio"] {
            margin-right: 10px;
        }
        
        .completion-options {
            margin-left: 25px;
            margin-top: 10px;
            padding: 10px;
            background: #f1f1f1;
            border-radius: 5px;
            display: none;
        }
        
        .episode-list {
            max-height: 300px;
            overflow-y: auto;
            margin: 15px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
        }
        
        .episode-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        
        .episode-item:last-child {
            border-bottom: none;
        }
        
        .progress-container {
            margin: 20px 0;
        }
        
        .progress-bar {
            height: 10px;
            background-color: #e9ecef;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background-color: var(--primary-color);
            width: <?= $serie['progresso'] ?? 0 ?>%;
            transition: width 0.3s ease;
        }
        
        .progress-text {
            text-align: center;
            margin-top: 5px;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .btn-submit {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            background-color: #b2070f;
            transform: translateY(-2px);
        }
        
        .favorite-check {
            display: flex;
            align-items: center;
            margin: 15px 0;
        }
        
        .favorite-check input {
            margin-right: 10px;
        }
        
        .select-all {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="serie-header">
            <img src="../<?= htmlspecialchars($serie['imagem']) ?>" alt="<?= htmlspecialchars($serie['titulo']) ?>" class="serie-poster">
            <h2><?= htmlspecialchars($serie['titulo']) ?></h2>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="serie_id" value="<?= $serieId ?>">
            
            <h4>Status Pessoal</h4>
            <div class="status-options">
                <label class="status-option">
                    <input type="radio" name="user_status" value="Não assistido" <?= ($serie['user_status'] ?? 'Não assistido') === 'Não assistido' ? 'checked' : '' ?>>
                    Não assistido
                </label>
                
                <label class="status-option">
                    <input type="radio" name="user_status" value="Assistindo" <?= ($serie['user_status'] ?? '') === 'Assistindo' ? 'checked' : '' ?>>
                    Assistindo
                </label>
                
                <label class="status-option">
                    <input type="radio" name="user_status" value="Pausada" <?= ($serie['user_status'] ?? '') === 'Pausada' ? 'checked' : '' ?>>
                    Pausada
                </label>
                
                <label class="status-option">
                    <input type="radio" name="user_status" value="Em dia" <?= ($serie['user_status'] ?? '') === 'Em dia' ? 'checked' : '' ?>>
                    Em dia
                </label>
                
                <label class="status-option">
                    <input type="radio" name="user_status" value="Concluída" <?= ($serie['user_status'] ?? '') === 'Concluída' ? 'checked' : '' ?>>
                    Concluída
                </label>
                
                <div id="completionOptions" class="completion-options">
                    <label>
                        <input type="radio" name="completion_type" value="recente" checked> Recente
                        <input type="month" name="completion_date" value="<?= date('Y-m') ?>" max="<?= date('Y-m') ?>">
                    </label>
                    <label style="display: block; margin-top: 8px;">
                        <input type="radio" name="completion_type" value="muito_tempo"> Há muito tempo
                    </label>
                </div>
            </div>
            
            <div class="favorite-check">
                <input type="checkbox" name="favorita" id="favorita" <?= isset($serie['favorita']) && $serie['favorita'] ? 'checked' : '' ?>>
                <label for="favorita">Marcar como favorita</label>
            </div>
            
            <?php if (!empty($serie['temporadas'])): ?>
                <h4>Episódios Assistidos</h4>
                <div class="episode-list">
                    <?php foreach ($serie['temporadas'] as $temporada): ?>
                        <h5>Temporada <?= $temporada['numero'] ?></h5>
                        <?php foreach ($temporada['episodios'] as $episodio): ?>
                            <div class="episode-item">
                                <input type="checkbox" 
                                       name="episodios[]" 
                                       id="ep_<?= $temporada['numero'] ?>_<?= $episodio['codigo'] ?>" 
                                       value="T<?= $temporada['numero'] ?>E<?= $episodio['codigo'] ?>"
                                       <?= $episodio['assistido'] ?? false ? 'checked' : '' ?>>
                                <label for="ep_<?= $temporada['numero'] ?>_<?= $episodio['codigo'] ?>" style="margin-left: 8px;">
                                    Episódio <?= $episodio['codigo'] ?> - <?= htmlspecialchars($episodio['titulo'] ?? '') ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
                
                <div class="select-all">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllEpisodes(true)">Selecionar Todos</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleAllEpisodes(false)">Deselecionar Todos</button>
                </div>
            <?php endif; ?>
            
            <div class="progress-container">
                <h4>Progresso</h4>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <div class="progress-text" id="progressText"><?= $serie['progresso'] ?? 0 ?>% completo</div>
            </div>
            
            <button type="submit" class="btn-submit">Salvar Alterações</button>
        </form>
    </div>
    
    <script>
        // Mostrar/ocultar opções de conclusão
        const completionRadios = document.querySelectorAll('input[name="user_status"]');
        const completionOptions = document.getElementById('completionOptions');
        
        completionRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                if (this.value === 'Concluída') {
                    completionOptions.style.display = 'block';
                } else {
                    completionOptions.style.display = 'none';
                }
            });
            
            // Verificar estado inicial
            if (radio.checked && radio.value === 'Concluída') {
                completionOptions.style.display = 'block';
            }
        });
        
        // Atualizar progresso quando episódios são marcados/desmarcados
        const episodeCheckboxes = document.querySelectorAll('input[name="episodios[]"]');
        const progressFill = document.getElementById('progressFill');
        const progressText = document.getElementById('progressText');
        
        episodeCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateProgress);
        });
        
        function updateProgress() {
            const total = episodeCheckboxes.length;
            const checked = document.querySelectorAll('input[name="episodios[]"]:checked').length;
            const percentage = total > 0 ? Math.round((checked / total) * 100) : 0;
            
            progressFill.style.width = percentage + '%';
            progressText.textContent = percentage + '% completo';
        }
        
        // Selecionar/Deselecionar todos os episódios
        function toggleAllEpisodes(select) {
            episodeCheckboxes.forEach(checkbox => {
                checkbox.checked = select;
            });
            updateProgress();
        }
    </script>
</body>
</html>