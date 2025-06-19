<?php
require_once '../other/config.php';
require_once '../other/seriesFunctions.php';

$id = $_GET['id'] ?? '';
if (!$id) {
    echo "<p>ID não informado.</p>";
    exit;
}

$serie = getSerieById($id);
if (!$serie) {
    echo "<p>Série não encontrada.</p>";
    exit;
}

// Excluir imagem, se existir e for local
if (!empty($serie['imagem']) && file_exists('../' . $serie['imagem'])) {
    unlink('../' . $serie['imagem']);
}

// Excluir do JSON de séries
excluirSerie($id);

// Redirecionar
header('Location: ../index.php');
exit;
?>