<?php
header('Content-Type: application/json');

require_once '../other/config.php';
require_once '../other/seriesFunctions.php';

// Verifica se é POST e tem dados JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'JSON inválido']);
    exit;
}

if (empty($data['serie_id']) || !isset($data['temporadas'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

try {
    $series = loadJsonData(SERIES_FILE);
    $serieIndex = null;
    
    // Encontra a série
    foreach ($series as $index => $serie) {
        if ($serie['id'] === $data['serie_id']) {
            $serieIndex = $index;
            break;
        }
    }
    
    if ($serieIndex === null) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Série não encontrada']);
        exit;
    }
    
    // Processa temporadas
    $temporadasFormatadas = [];
    foreach ($data['temporadas'] as $temporada) {
        $episodios = [];
        
        foreach ($temporada['episodios'] as $episodio) {
            $episodios[] = [
                'codigo' => $episodio['codigo'],
                'titulo' => 'Episódio ' . substr($episodio['codigo'], -2),
                'assistido' => false
            ];
        }
        
        $temporadasFormatadas[] = [
            'numero' => $temporada['numero'],
            'episodios' => $episodios
        ];
    }
    
    // Atualiza a série
    $series[$serieIndex]['temporadas'] = $temporadasFormatadas;
    
    // Salva no arquivo
    if (saveAllSeries($series)) {
        echo json_encode([
            'success' => true,
            'message' => 'Episódios salvos com sucesso'
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Falha ao salvar arquivo']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}
?>