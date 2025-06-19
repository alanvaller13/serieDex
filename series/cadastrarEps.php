<?php
$seriesFile = '../data/series.json';
if (!file_exists($seriesFile)) {
    die("Arquivo de séries não encontrado!");
}

$seriesData = file_get_contents($seriesFile);
if ($seriesData === false) {
    die("Erro ao ler o arquivo de séries!");
}

$series = json_decode($seriesData, true);
if ($series === null) {
    die("Erro ao decodificar o arquivo de séries!");
}

$seriesTitulos = array_column($series, 'titulo', 'id');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Episódios</title>
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

    .serie-info {
      display: flex;
      gap: 20px;
      margin-bottom: 20px;
      align-items: center;
    }

    .serie-poster {
      width: 100px;
      height: 150px;
      object-fit: cover;
      border-radius: 5px;
    }

    .serie-details {
      flex: 1;
    }

    .temporada-container {
      margin-bottom: 30px;
      border: 1px solid var(--border-color);
      border-radius: 8px;
      padding: 15px;
    }

    .temporada-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      padding-bottom: 0.5rem;
      border-bottom: 1px solid var(--border-color);
    }

    .episodio-item {
      display: flex;
      gap: 10px;
      margin-bottom: 15px;
      padding: 10px;
      background-color: #f9f9f9;
      border-radius: 5px;
    }

    .episodio-cod {
      width: 80px;
      font-weight: bold;
      display: flex;
      align-items: center;
    }

    .episodio-nome {
      flex: 2;
    }

    .episodio-data {
      width: 120px;
    }

    .add-episodio {
      background-color: #6c757d;
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      margin-top: 5px;
      transition: background-color 0.3s;
    }

    .add-episodio:hover {
      background-color: #5a6268;
    }

    .add-temporada {
      background-color: #28a745;
      color: white;
      border: none;
      padding: 8px 15px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      margin-bottom: 20px;
      transition: background-color 0.3s;
    }

    .add-temporada:hover {
      background-color: #218838;
    }

    .autocomplete {
      position: relative;
    }

    .autocomplete-items {
      position: absolute;
      border: 1px solid var(--border-color);
      border-bottom: none;
      border-top: none;
      z-index: 99;
      top: 100%;
      left: 0;
      right: 0;
      max-height: 200px;
      overflow-y: auto;
    }

    .autocomplete-items div {
      padding: 10px;
      cursor: pointer;
      background-color: #fff;
      border-bottom: 1px solid var(--border-color);
    }

    .autocomplete-items div:hover {
      background-color: #e9e9e9;
    }

    .autocomplete-active {
      background-color: var(--primary-color) !important;
      color: #ffffff;
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

    .error {
      color: var(--primary-color);
      font-size: 14px;
      margin-top: 5px;
    }

    .btn-submit {
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

    .btn-submit:hover {
      background-color: #b2070f;
    }
  </style>
</head>
<body>
<div class="container">
    <h1><i class="bi bi-plus-circle"></i> Cadastrar Episódios</h1>
    
    <form id="episodioForm">
        <input type="hidden" id="serieId" name="serie_id">
        
        <div class="form-group">
            <label>Buscar Série:</label>
            <input type="text" id="serieSearch" placeholder="Digite o nome da série" class="form-control">
        </div>
        
        <div id="serieInfo" style="display:none;">
            <div class="serie-info">
                <img id="seriePoster" src="" class="serie-poster" onerror="this.src='../assets/default_poster.jpg'">
                <div>
                    <h3 id="serieTitulo"></h3>
                    <p id="serieAno"></p>
                </div>
            </div>
            
            <div id="temporadasContainer"></div>
            
            <button type="button" id="addTemporada" class="btn btn-secondary">
                <i class="bi bi-plus"></i> Adicionar Temporada
            </button>
            
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Salvar Episódios
            </button>
        </div>
    </form>
</div>

<script>
// Dados das séries
const series = <?php echo json_encode($series); ?>;

// Selecionar série
document.getElementById('serieSearch').addEventListener('input', function() {
    const search = this.value.toLowerCase();
    const results = series.filter(s => s.titulo.toLowerCase().includes(search));
    
    // Implementar autocomplete simples (pode melhorar)
    if (results.length === 1) {
        const serie = results[0];
        document.getElementById('serieId').value = serie.id;
        document.getElementById('serieTitulo').textContent = serie.titulo;
        document.getElementById('serieAno').textContent = `Ano: ${serie.ano_lancamento}`;
        document.getElementById('seriePoster').src = serie.imagem ? '../' + serie.imagem : '../assets/default_poster.jpg';
        document.getElementById('serieInfo').style.display = 'block';
    }
});

// Adicionar temporada
document.getElementById('addTemporada').addEventListener('click', function() {
    const container = document.getElementById('temporadasContainer');
    const tempNum = container.children.length + 1;
    
    const tempDiv = document.createElement('div');
    tempDiv.className = 'temporada-container';
    tempDiv.innerHTML = `
        <h4>Temporada ${tempNum}</h4>
        <div class="episodios-container" id="episodiosTemp${tempNum}"></div>
        <button type="button" class="btn btn-secondary" onclick="addEpisodio(${tempNum})">
            <i class="bi bi-plus"></i> Adicionar Episódio
        </button>
    `;
    
    container.appendChild(tempDiv);
    addEpisodio(tempNum);
});

// Adicionar episódio
function addEpisodio(tempNum) {
    const container = document.getElementById(`episodiosTemp${tempNum}`);
    const epNum = container.children.length + 1;
    
    const epDiv = document.createElement('div');
    epDiv.className = 'episodio-item';
    epDiv.innerHTML = `
        <span>Episódio ${epNum}</span>
        <input type="hidden" name="temporadas[${tempNum}][episodios][${epNum}][codigo]" value="T${tempNum.toString().padStart(2, '0')}E${epNum.toString().padStart(2, '0')}">
    `;
    
    container.appendChild(epDiv);
}

// Enviar formulário
document.getElementById('episodioForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const serieId = document.getElementById('serieId').value;
    if (!serieId) {
        alert('Selecione uma série válida');
        return;
    }

    const formData = {
        serie_id: serieId,
        temporadas: []
    };

    document.querySelectorAll('.temporada-container').forEach(temp => {
        const tempNum = temp.querySelector('h4').textContent.split(' ')[1];
        const episodios = [];
        
        temp.querySelectorAll('.episodio-item').forEach(ep => {
            episodios.push({
                codigo: ep.querySelector('input').value
            });
        });
        
        formData.temporadas.push({
            numero: parseInt(tempNum),
            episodios: episodios
        });
    });

    try {
        const response = await fetch('../series/salvarEps.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            window.location.href = `../series/detalhesTv.php?id=${serieId}`;
        } else {
            alert('Erro: ' + (result.message || 'Falha ao salvar'));
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao enviar dados');
    }
});
</script>
</body>
</html>