<?php
require_once '../other/seriesFunctions.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$serie = getSerieById($id);
if (!$serie) {
    echo "<p>Série não encontrada.</p>";
    exit;
}

$temporadas = json_decode($serie['temporadas_json'] ?? '[]', true);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Detalhes - <?= htmlspecialchars($serie['titulo']) ?></title>
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
    
    .container-detalhes {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 0 15px;
    }
    
    .serie-header {
      margin-bottom: 2rem;
    }
    
    .btn-voltar {
      background-color: var(--dark-color);
      color: white;
      margin-bottom: 1.5rem;
      transition: all 0.3s;
    }
    
    .btn-voltar:hover {
      background-color: #343a40;
      color: white;
    }
    
    .serie-poster {
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      transition: transform 0.3s;
      height: 100%;
      object-fit: cover;
    }
    
    .serie-poster:hover {
      transform: scale(1.02);
    }
    
    .nav-tabs {
      border-bottom: 2px solid #dee2e6;
    }
    
    .nav-tabs .nav-link {
      color: var(--dark-color);
      font-weight: 500;
      border: none;
      padding: 12px 20px;
      margin-right: 5px;
    }
    
    .nav-tabs .nav-link.active {
      color: var(--primary-color);
      background-color: transparent;
      border-bottom: 3px solid var(--primary-color);
    }
    
    .nav-tabs .nav-link:hover {
      border-color: transparent;
      color: var(--primary-color);
    }
    
    .tab-content {
      background: white;
      padding: 2rem;
      border-radius: 0 0 10px 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    .info-label {
      font-weight: 600;
      color: var(--dark-color);
      min-width: 150px;
      display: inline-block;
    }
    
    .sinopse-text {
      line-height: 1.8;
      text-align: justify;
    }
    
    /* Estilo para as temporadas */
    .temporada-container {
      margin-bottom: 2rem;
      background: var(--secondary-color);
      padding: 1.5rem;
      border-radius: 8px;
    }
    
    .temporada-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
      padding-bottom: 0.5rem;
      border-bottom: 1px solid #dee2e6;
    }
    
    .episodio-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 15px;
      border-left: 3px solid var(--primary-color);
      margin-bottom: 8px;
      background: white;
      border-radius: 5px;
      transition: all 0.2s;
    }
    
    .episodio-item:hover {
      transform: translateX(5px);
      box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }
    
    .episodio-titulo {
      font-weight: 500;
      margin-bottom: 0;
    }
    
    .episodio-duracao {
      background-color: var(--primary-color);
      color: white;
      padding: 3px 8px;
      border-radius: 20px;
      font-size: 0.8rem;
    }
    
    .episodio-data {
      font-size: 0.85rem;
      color: #6c757d;
    }
    
    .avaliacao-estrelas {
      color: gold;
      font-size: 1.2rem;
      letter-spacing: 2px;
    }
    
    @media (max-width: 768px) {
      .serie-poster {
        margin-bottom: 1.5rem;
      }
      
      .info-label {
        min-width: 120px;
      }
    }
  </style>
</head>
<body>
<div class="container-detalhes">
  <a href="../index.php" class="btn btn-voltar">
    <i class="bi bi-arrow-left"></i> Voltar
  </a>
  
  <h1 class="serie-header"><?= htmlspecialchars($serie['titulo']) ?></h1>

  <ul class="nav nav-tabs" id="detalhesTab" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info-pane" type="button" role="tab" aria-controls="info-pane" aria-selected="true">
        <i class="bi bi-info-circle"></i> Informações
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="temporadas-tab" data-bs-toggle="tab" data-bs-target="#temporadas-pane" type="button" role="tab" aria-controls="temporadas-pane" aria-selected="false">
        <i class="bi bi-collection-play"></i> Temporadas
      </button>
    </li>
  </ul>

  <div class="tab-content" id="detalhesTabContent">
    <!-- Aba de Informações -->
    <div class="tab-pane fade show active" id="info-pane" role="tabpanel" aria-labelledby="info-tab">
      <div class="row">
        <div class="col-lg-4 col-md-5 mb-4 mb-md-0">
          <img src="../<?= htmlspecialchars($serie['imagem']) ?>" alt="<?= htmlspecialchars($serie['titulo']) ?>" class="img-fluid serie-poster">
        </div>
        <div class="col-lg-8 col-md-7">
          <div class="mb-3">
            <span class="info-label">Título Original:</span>
            <?= htmlspecialchars($serie['titulo_original']) ?>
          </div>
          
          <div class="mb-3">
            <span class="info-label">Ano de lançamento:</span>
            <?= htmlspecialchars($serie['ano_lancamento']) ?>
          </div>
          
          <?php if (!empty($serie['ano_encerramento'])): ?>
            <div class="mb-3">
              <span class="info-label">Ano de encerramento:</span>
              <?= htmlspecialchars($serie['ano_encerramento']) ?>
            </div>
          <?php endif ?>
          
          <div class="mb-3">
            <span class="info-label">País:</span>
            <?= htmlspecialchars($serie['pais']) ?>
          </div>
          
          <div class="mb-3">
            <span class="info-label">Idioma:</span>
            <?= htmlspecialchars($serie['idioma']) ?>
          </div>
          
          <div class="mb-3">
            <span class="info-label">Status:</span>
            <?= htmlspecialchars($serie['status']) ?>
          </div>
          
          <div class="mb-3">
            <span class="info-label">Classificação:</span>
            <?= htmlspecialchars($serie['classificacao']) ?>+
          </div>
          
          <div class="mb-3">
            <span class="info-label">Avaliação:</span>
            <span class="avaliacao-estrelas">
              <?= str_repeat('★', round($serie['avaliacao'])) ?><?= str_repeat('☆', 5 - round($serie['avaliacao'])) ?>
            </span>
            (<?= number_format($serie['avaliacao'], 1) ?>/5)
          </div>
          
          <div class="mb-3">
            <span class="info-label">Gêneros:</span>
            <?= implode(', ', array_map('htmlspecialchars', $serie['generos'])) ?>
          </div>

          
          
          <div class="mb-3">
            <span class="info-label">Plataforma:</span>
            <?= !empty($serie['onde_visto']) ? ucfirst(htmlspecialchars($serie['onde_visto'])) : '<em>Não definida</em>' ?>
          </div>
          
          <div class="mt-4">
            <h5 class="mb-2">Sinopse</h5>
            <p class="sinopse-text"><?= nl2br(htmlspecialchars($serie['sinopse'])) ?></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Aba de Temporadas -->
<div class="tab-pane fade" id="temporadas-pane" role="tabpanel" aria-labelledby="temporadas-tab">
    <?php if (empty($serie['temporadas'])): ?>
        <div class="alert alert-info">
            Nenhuma temporada cadastrada para esta série.
        </div>
    <?php else: ?>
        <?php foreach ($serie['temporadas'] as $temporada): ?>
            <div class="temporada-container mb-4">
                <div class="temporada-header d-flex justify-content-between align-items-center mb-3">
                    <h4>Temporada <?= $temporada['numero'] ?></h4>
                </div>
                
                <div class="episodios-list">
                    <?php foreach ($temporada['episodios'] as $episodio): ?>
                        <div class="episodio-item d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded">
                            <div>
                                <h5 class="m-0"><?= $episodio['codigo'] ?></h5>
                                <?php if (!empty($episodio['titulo'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars($episodio['titulo']) ?></small>
                                <?php endif; ?>
                            </div>
                            <div>
                                <span class="badge bg-<?= $episodio['assistido'] ? 'success' : 'secondary' ?>">
                                    <?= $episodio['assistido'] ? 'Assistido' : 'Não assistido' ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>