<?php
require_once '../other/config.php';
require_once '../other/seriesFunctions.php';

// Verifica se foi passado um ID válido
$id = $_GET['id'] ?? null;
$serie = $id ? getSerieById($id) : null;

if (!$serie) {
    header('Location: listarTv.php');
    exit;
}

// Carrega os dados dos arquivos JSON
$paises = loadJsonData(PAISES_FILE);
$generos = loadJsonData(GENEROS_FILE);
$idiomas = loadJsonData(IDIOMAS_FILE);

// Processa o formulário de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados = [
        'titulo_original' => $_POST['titulo_original'],
        'titulo' => $_POST['titulo'],
        'ano_lancamento' => (int)$_POST['ano_lancamento'],
        'ano_encerramento' => $_POST['ano_encerramento'] === '' ? null : (int)$_POST['ano_encerramento'],
        'pais' => $_POST['pais'],
        'idioma' => $_POST['idioma'],
        'status' => $_POST['status'],
        'classificacao' => $_POST['classificacao'],
        'sinopse' => $_POST['sinopse'],
        'avaliacao' => (float)$_POST['avaliacao'],
        'generos' => explode(',', $_POST['generos']),
        'emissora' => $_POST['emissora'],
        'onde_visto' => $_POST['onde_visto'],
        'equipe' => []
    ];

    // Processa a equipe criativa
    if (isset($_POST['equipe_nome'])) {
        for ($i = 0; $i < count($_POST['equipe_nome']); $i++) {
            $dados['equipe'][] = [
                'nome' => $_POST['equipe_nome'][$i],
                'funcao' => $_POST['equipe_funcao'][$i]
            ];
        }
    }

    // Atualiza a série
    if (updateSerie($id, $dados, $_FILES['imagem'])) {
        header('Location: verTv.php?id=' . $id . '&sucesso=1');
        exit;
    } else {
        $erro = "Erro ao atualizar a série. Tente novamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Série - <?= htmlspecialchars($serie['titulo']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root {
      --primary-color: #e50914;
      --secondary-color: #f5f5f5;
      --text-color: #333;
      --border-color: #ddd;
    }

    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      background-color: var(--secondary-color);
      color: var(--text-color);
      margin: 0;
      padding: 20px;
      line-height: 1.6;
    }

    .container {
      max-width: 900px;
      margin: 0 auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }

    h1 {
      text-align: center;
      color: var(--primary-color);
      margin-bottom: 30px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-row {
      display: flex;
      gap: 20px;
      margin-bottom: 20px;
    }

    .form-row .form-group {
      flex: 1;
    }

    label {
      display: block;
      font-weight: 600;
      margin-bottom: 8px;
    }

    input, select, textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid var(--border-color);
      border-radius: 5px;
      font-size: 16px;
    }

    textarea {
      resize: vertical;
      min-height: 100px;
    }

    button {
      background-color: var(--primary-color);
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 16px;
      display: block;
      margin: 30px auto 0;
      transition: background-color 0.3s;
    }

    button:hover {
      background-color: #b2070f;
    }

    .rating {
      display: flex;
      gap: 5px;
      margin: 10px 0;
    }

    .rating i {
      font-size: 24px;
      color: #ddd;
      cursor: pointer;
      transition: color 0.2s;
    }

    .rating i.active {
      color: gold;
    }

    .selected-genres {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin: 10px 0;
    }

    .genre-badge {
      background-color: #f0f0f0;
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 14px;
      display: flex;
      align-items: center;
    }

    .genre-badge i {
      margin-left: 5px;
      cursor: pointer;
    }

    .platforms {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-top: 10px;
    }

    .platform {
      cursor: pointer;
      text-align: center;
      transition: transform 0.2s;
    }

    .platform:hover {
      transform: scale(1.05);
    }

    .platform.selected img {
      border: 2px solid var(--primary-color);
    }

    .platform img {
      width: 50px;
      height: 50px;
      object-fit: contain;
      border-radius: 8px;
      border: 2px solid transparent;
    }

    .platform span {
      display: block;
      font-size: 12px;
      margin-top: 5px;
    }

    .equipe-list {
      margin-bottom: 15px;
    }

    .equipe-item {
      display: flex;
      gap: 10px;
      margin-bottom: 10px;
    }

    .equipe-item input {
      flex: 1;
    }

    .add-equipe {
      background-color: #6c757d;
      padding: 8px 15px;
      font-size: 14px;
      margin-top: 5px;
    }

    .add-equipe:hover {
      background-color: #5a6268;
    }

    .image-preview {
      width: 100%;
      height: 200px;
      background-color: #f5f5f5;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 10px;
      overflow: hidden;
    }

    .image-preview img {
      max-width: 100%;
      max-height: 100%;
      object-fit: cover;
    }

    .streaming-options {
      display: grid;
      grid-template-columns: repeat(10, 1fr);
      gap: 10px;
      justify-items: center;
      margin-top: 10px;
      max-width: 100%;
    }

    .streaming-icon {
      cursor: pointer;
    }

    .streaming-icon input[type="radio"] {
      display: none;
    }

    .streaming-icon img {
      width: 25px;
      height: 25px;
      border: 2px solid transparent;
      border-radius: 4px;
      padding: 2px;
      transition: 0.3s;
    }

    .streaming-icon input[type="radio"]:checked + img {
      border-color: #007bff;
      box-shadow: 0 0 4px #007bff;
    }

    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border: 1px solid transparent;
      border-radius: 4px;
    }

    .alert-success {
      color: #3c763d;
      background-color: #dff0d8;
      border-color: #d6e9c6;
    }

    .alert-danger {
      color: #a94442;
      background-color: #f2dede;
      border-color: #ebccd1;
    }
  </style>
</head>
<body>
<div class="container">
  <h1><i class="bi bi-pencil-square"></i> Editar Série: <?= htmlspecialchars($serie['titulo']) ?></h1>
  
  <?php if (isset($erro)): ?>
    <div class="alert alert-danger"><?= $erro ?></div>
  <?php endif; ?>

  <form method="POST" action="editarTv.php?id=<?= $id ?>" enctype="multipart/form-data">
    <!-- Imagem -->
    <div class="form-group">
      <label>Imagem da Série:</label>
      <div class="image-preview" id="imagePreview">
        <?php if (!empty($serie['imagem'])): ?>
          <img src="../data/capas/<?= htmlspecialchars($serie['imagem']) ?>" alt="Capa da série">
        <?php else: ?>
          <i class="bi bi-image" style="font-size: 3rem; color: #ccc;"></i>
        <?php endif; ?>
      </div>
      <input type="file" name="imagem" id="imagem" accept="image/*">
      <small>Deixe em branco para manter a imagem atual</small>
    </div>

    <!-- Títulos -->
    <div class="form-row">
      <div class="form-group">
        <label>Título Original:</label>
        <input type="text" name="titulo_original" value="<?= htmlspecialchars($serie['titulo_original']) ?>" required>
      </div>
      <div class="form-group">
        <label>Título (no seu idioma):</label>
        <input type="text" name="titulo" value="<?= htmlspecialchars($serie['titulo']) ?>" required>
      </div>
    </div>

    <!-- Anos -->
    <div class="form-row">
      <div class="form-group">
        <label>Ano de Lançamento:</label>
        <select name="ano_lancamento" required>
          <?php for ($ano = 1900; $ano <= 2025; $ano++): ?>
            <option value="<?= $ano ?>" <?= $ano == $serie['ano_lancamento'] ? 'selected' : '' ?>><?= $ano ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Ano de Encerramento:</label>
        <select name="ano_encerramento">
          <option value="">Continuando</option>
          <?php for ($ano = 1900; $ano <= 2025; $ano++): ?>
            <option value="<?= $ano ?>" <?= $serie['ano_encerramento'] !== null && $ano == $serie['ano_encerramento'] ? 'selected' : '' ?>><?= $ano ?></option>
          <?php endfor; ?>
        </select>
      </div>
    </div>

    <!-- País e Idioma -->
    <div class="form-row">
      <div class="form-group">
        <label>País:</label>
        <select name="pais" required>
          <option value="">Selecione um país</option>
          <?php foreach ($paises as $pais): ?>
            <option value="<?= htmlspecialchars($pais) ?>" <?= $pais == $serie['pais'] ? 'selected' : '' ?>><?= htmlspecialchars($pais) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Idioma Original:</label>
        <select name="idioma" required>
          <option value="">Selecione um idioma</option>
          <?php foreach ($idiomas as $idioma): ?>
            <option value="<?= htmlspecialchars($idioma) ?>" <?= $idioma == $serie['idioma'] ? 'selected' : '' ?>><?= htmlspecialchars($idioma) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- Status e Classificação -->
    <div class="form-row">
      <div class="form-group">
        <label>Status:</label>
        <select name="status" required>
          <option value="Em Exibição" <?= $serie['status'] == 'Em Exibição' ? 'selected' : '' ?>>Em Exibição</option>
          <option value="Finalizada" <?= $serie['status'] == 'Finalizada' ? 'selected' : '' ?>>Finalizada</option>
          <option value="Cancelada" <?= $serie['status'] == 'Cancelada' ? 'selected' : '' ?>>Cancelada</option>
          <option value="Renovada" <?= $serie['status'] == 'Renovada' ? 'selected' : '' ?>>Renovada</option>
          <option value="Em Hiato" <?= $serie['status'] == 'Em Hiato' ? 'selected' : '' ?>>Em Hiato</option>
        </select>
      </div>
      <div class="form-group">
        <label>Classificação:</label>
        <select name="classificacao" required>
          <option value="L" <?= $serie['classificacao'] == 'L' ? 'selected' : '' ?>>Livre</option>
          <option value="10" <?= $serie['classificacao'] == '10' ? 'selected' : '' ?>>10+</option>
          <option value="12" <?= $serie['classificacao'] == '12' ? 'selected' : '' ?>>12+</option>
          <option value="14" <?= $serie['classificacao'] == '14' ? 'selected' : '' ?>>14+</option>
          <option value="16" <?= $serie['classificacao'] == '16' ? 'selected' : '' ?>>16+</option>
          <option value="18" <?= $serie['classificacao'] == '18' ? 'selected' : '' ?>>18+</option>
        </select>
      </div>
    </div>

    <!-- Sinopse -->
    <div class="form-group">
      <label>Sinopse:</label>
      <textarea name="sinopse" required><?= htmlspecialchars($serie['sinopse']) ?></textarea>
    </div>

    <!-- Avaliação -->
    <div class="form-group">
      <label>Avaliação:</label>
      <div class="rating" id="ratingStars">
        <?php for ($i = 1; $i <= 10; $i++): ?>
          <i class="bi <?= $i % 2 === 0 ? 'bi-star-fill' : 'bi-star-half' ?> 
            <?= $serie['avaliacao'] >= ($i / 2) ? 'active' : '' ?>" 
            data-value="<?= $i / 2 ?>"></i>
        <?php endfor; ?>
      </div>
      <input type="hidden" name="avaliacao" id="avaliacaoInput" value="<?= $serie['avaliacao'] ?>">
    </div>

    <!-- Gêneros -->
    <div class="form-group">
      <label>Gêneros (Selecione 3):</label>
      <select id="generoSelect">
        <option value="">Selecione um gênero</option>
        <?php foreach ($generos as $genero): ?>
          <option value="<?= htmlspecialchars($genero) ?>"><?= htmlspecialchars($genero) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="selected-genres" id="selectedGenres">
        <?php foreach ($serie['generos'] as $genero): ?>
          <div class="genre-badge">
            <?= htmlspecialchars($genero) ?> 
            <i class="bi bi-x" onclick="removeGenre('<?= htmlspecialchars($genero) ?>')"></i>
          </div>
        <?php endforeach; ?>
      </div>
      <input type="hidden" name="generos" id="generosInput" value="<?= implode(',', $serie['generos']) ?>">
    </div>

    <!-- Equipe -->
    <div class="form-group">
      <label>Equipe Criativa:</label>
      <div id="equipe-container" class="equipe-list">
        <?php foreach ($serie['equipe'] as $membro): ?>
          <div class="equipe-item">
            <input type="text" name="equipe_nome[]" placeholder="Nome" value="<?= htmlspecialchars($membro['nome']) ?>" required>
            <input type="text" name="equipe_funcao[]" placeholder="Função" value="<?= htmlspecialchars($membro['funcao']) ?>" required>
          </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="add-equipe" onclick="adicionarCampoEquipe()">
        <i class="bi bi-plus"></i> Adicionar Membro
      </button>
    </div>

    <!-- Emissora -->
    <div class="form-group">
      <label>Emissora Original:</label>
      <input type="text" name="emissora" value="<?= htmlspecialchars($serie['emissora']) ?>" required>
    </div>

    <!-- Plataformas -->
    <div class="streaming-container">
      <p><strong>Plataformas Disponíveis</strong> *</p>
      <div class="streaming-options">
        <?php
        $streamings = [
          "internet", "tv", "appletv", "bandplay", "crunchyroll", "disneymais",
          "globoplay", "looke", "maissbt", "max", "mubi", "Netflix",
          "netmovies", "paramountmais", "plutotv", "prime-video",
          "telecine", "viki", "vix", "youtube", "outro"];
        
        foreach ($streamings as $streaming): ?>
          <label class="streaming-icon">
            <input type="radio" name="onde_visto" value="<?= $streaming ?>" 
              <?= $serie['onde_visto'] == $streaming ? 'checked' : '' ?> />
            <img src="../assets/icons/<?= $streaming ?>.png" alt="<?= ucfirst($streaming) ?>">
          </label>
        <?php endforeach; ?>
      </div>
    </div>

    <button type="submit"><i class="bi bi-save"></i> Salvar Alterações</button>
  </form>
</div>

<script>
  // Imagem Preview
  document.getElementById('imagem').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      const reader = new FileReader();
      reader.onload = function(event) {
        document.getElementById('imagePreview').innerHTML = `<img src="${event.target.result}" alt="Preview">`;
      };
      reader.readAsDataURL(file);
    }
  });

  // Avaliação por estrelas
  const stars = document.querySelectorAll('#ratingStars i');
  const ratingInput = document.getElementById('avaliacaoInput');
  stars.forEach(star => {
    star.addEventListener('click', function() {
      const value = parseFloat(this.dataset.value);
      ratingInput.value = value;
      stars.forEach(s => {
        s.classList.toggle('active', parseFloat(s.dataset.value) <= value);
      });
    });
  });

  // Seleção de gêneros
  const generoSelect = document.getElementById('generoSelect');
  const selectedGenres = <?= json_encode($serie['generos']) ?>;
  
  function updateSelectedGenres() {
    const container = document.getElementById('selectedGenres');
    container.innerHTML = '';
    selectedGenres.forEach(genre => {
      const badge = document.createElement('div');
      badge.className = 'genre-badge';
      badge.innerHTML = `${genre} <i class="bi bi-x" onclick="removeGenre('${genre}')"></i>`;
      container.appendChild(badge);
    });
    document.getElementById('generosInput').value = selectedGenres.join(',');
    generoSelect.disabled = selectedGenres.length >= 3;
  }

  generoSelect.addEventListener('change', function() {
    if (this.value && !selectedGenres.includes(this.value)) {
      if (selectedGenres.length < 3) {
        selectedGenres.push(this.value);
        updateSelectedGenres();
      }
      this.value = '';
    }
  });

  function removeGenre(genre) {
    const index = selectedGenres.indexOf(genre);
    if (index > -1) {
      selectedGenres.splice(index, 1);
      updateSelectedGenres();
    }
  }

  // Equipe criativa
  function adicionarCampoEquipe() {
    const container = document.getElementById('equipe-container');
    const item = document.createElement('div');
    item.className = 'equipe-item';
    item.innerHTML = `
      <input type="text" name="equipe_nome[]" placeholder="Nome" required>
      <input type="text" name="equipe_funcao[]" placeholder="Função" required>
    `;
    container.appendChild(item);
  }
</script>
</body>
</html>