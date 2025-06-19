<?php
// Inclui o arquivo de configuração
require_once '../other/config.php';

// Verifica se o método de requisição é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cadastrarTv.php?error=invalid_method');
    exit;
}

// Verifica se todos os campos obrigatórios foram enviados
$requiredFields = [
    'titulo_original', 'titulo', 'ano_lancamento', 'pais', 
    'idioma', 'status', 'classificacao', 'sinopse', 'generos'
];

foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        header('Location: cadastrarTv.php?error=missing_field&field=' . $field);
        exit;
    }
}

// Verifica se foi enviado um arquivo de imagem
if (!isset($_FILES['imagem']) || $_FILES['imagem']['error'] !== UPLOAD_ERR_OK) {
    header('Location: cadastrarTv.php?error=no_image');
    exit;
}

// Valida o tipo da imagem
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$fileType = $_FILES['imagem']['type'];
if (!in_array($fileType, $allowedTypes)) {
    header('Location: cadastrarTv.php?error=invalid_image_type');
    exit;
}

// Valida o tamanho da imagem (máximo 5MB)
$maxSize = 5 * 1024 * 1024; // 5MB
if ($_FILES['imagem']['size'] > $maxSize) {
    header('Location: cadastrarTv.php?error=image_too_large');
    exit;
}

// Carrega as séries existentes
$series = loadJsonData(SERIES_FILE);

// Prepara os dados da nova série
$novaSerie = [
    'id' => uniqid(), // Gera um ID único
    'titulo_original' => htmlspecialchars(trim($_POST['titulo_original'])),
    'titulo' => htmlspecialchars(trim($_POST['titulo'])),
    'ano_lancamento' => (int)$_POST['ano_lancamento'],
    'ano_encerramento' => !empty($_POST['ano_encerramento']) ? (int)$_POST['ano_encerramento'] : null,
    'pais' => htmlspecialchars(trim($_POST['pais'])),
    'idioma' => htmlspecialchars(trim($_POST['idioma'])),
    'status' => htmlspecialchars(trim($_POST['status'])),
    'classificacao' => htmlspecialchars(trim($_POST['classificacao'])),
    'sinopse' => htmlspecialchars(trim($_POST['sinopse'])),
    'avaliacao' => isset($_POST['avaliacao']) ? (float)$_POST['avaliacao'] : 0,
    'generos' => array_map('trim', explode(',', $_POST['generos'])),
    'emissora' => htmlspecialchars(trim($_POST['emissora'])),
    'onde_visto' => htmlspecialchars(trim($_POST['onde_visto'])),
    'data_cadastro' => date('Y-m-d H:i:s'),
    'user_status' => 'Não assistido',
    'progresso' => 0,
    'equipe' => []
];

// Processa a equipe criativa
if (isset($_POST['equipe_nome']) && isset($_POST['equipe_funcao'])) {
    $nomes = $_POST['equipe_nome'];
    $funcoes = $_POST['equipe_funcao'];
    
    for ($i = 0; $i < count($nomes); $i++) {
        if (!empty($nomes[$i]) && !empty($funcoes[$i])) {
            $novaSerie['equipe'][] = [
                'nome' => htmlspecialchars(trim($nomes[$i])),
                'funcao' => htmlspecialchars(trim($funcoes[$i]))
            ];
        }
    }
}

// Processa o upload da imagem
$extensao = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
$nomeImagem = 'serie_' . $novaSerie['id'] . '.' . $extensao;
$caminhoImagem = CAPAS_PATH . '/' . $nomeImagem;

if (move_uploaded_file($_FILES['imagem']['tmp_name'], $caminhoImagem)) {
    $novaSerie['imagem'] = 'uploads/' . $nomeImagem; // Caminho relativo para exibição
} else {
    header('Location: cadastrarTv.php?error=upload_failed');
    exit;
}

// Adiciona a nova série ao array
$series[] = $novaSerie;

// Tenta salvar no arquivo JSON
if (!saveAllSeries($series)) {
    // Se falhar ao salvar, remove a imagem que foi enviada
    if (file_exists($caminhoImagem)) {
        unlink($caminhoImagem);
    }
    header('Location: cadastrarTv.php?error=save_failed');
    exit;
}

// Redireciona com mensagem de sucesso
header('Location: ../index.php?success=series_added');
exit;
?>