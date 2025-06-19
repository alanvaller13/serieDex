<?php
// Configurações básicas do sistema
define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/dexSeries');
define('DATA_PATH', BASE_PATH . '/data');
define('CAPAS_PATH', BASE_PATH . '/uploads');

// Configurações de arquivos
define('SERIES_FILE', DATA_PATH . '/series.json');
define('PAISES_FILE', DATA_PATH . '/paises.json');
define('GENEROS_FILE', DATA_PATH . '/generos.json');
define('IDIOMAS_FILE', DATA_PATH . '/idiomas.json');

// Cria as pastas necessárias se não existirem
$requiredFolders = [DATA_PATH, CAPAS_PATH];
foreach ($requiredFolders as $folder) {
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
    }
}

// Verifica se os arquivos JSON essenciais existem, senão cria
$essentialFiles = [
    SERIES_FILE => [],
    PAISES_FILE => ["Brasil", "Estados Unidos", "Reino Unido", "Japão", "Coreia do Sul"],
    GENEROS_FILE => ["Ação", "Aventura", "Comédia", "Drama", "Ficção Científica"],
    IDIOMAS_FILE => ["Português", "Inglês", "Espanhol", "Japonês", "Coreano"]
];

foreach ($essentialFiles as $file => $defaultContent) {
    if (!file_exists($file)) {
        file_put_contents($file, json_encode($defaultContent, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}

/**
 * Carrega dados de um arquivo JSON
 * @param string $file Caminho do arquivo JSON
 * @return array Dados decodificados ou array vazio se falhar
 */
function loadJsonData($file) {
    if (!file_exists($file)) {
        return [];
    }
    
    $content = file_get_contents($file);
    $data = json_decode($content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Erro ao decodificar JSON em $file: " . json_last_error_msg());
        return [];
    }
    
    return is_array($data) ? $data : [];
}

/**
 * Salva todas as séries no arquivo JSON
 * @param array $series Array de séries
 * @return bool True se salvou com sucesso
 */
function saveAllSeries($series) {
    try {
        $json = json_encode($series, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new Exception("Falha ao codificar JSON: " . json_last_error_msg());
        }
        
        $result = file_put_contents(SERIES_FILE, $json);
        if ($result === false) {
            throw new Exception("Falha ao escrever no arquivo " . SERIES_FILE);
        }
        
        return true;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return false;
    }
}

/**
 * Obtém o próximo ID disponível para uma nova série
 * @param array $series Array de séries existentes
 * @return string Novo ID único
 */
function getNextSerieId($series) {
    $maxId = 0;
    foreach ($series as $serie) {
        if (isset($serie['id'])) {
            $numId = (int) str_replace('serie_', '', $serie['id']);
            if ($numId > $maxId) {
                $maxId = $numId;
            }
        }
    }
    return 'serie_' . ($maxId + 1);
}

/**
 * Valida e processa um upload de imagem
 * @param array $file Array $_FILES['nome_do_campo']
 * @param string $targetDir Diretório de destino
 * @param string $prefix Prefixo para o nome do arquivo
 * @return array ['success'=>bool, 'message'=>string, 'filename'=>string]
 */
function processImageUpload($file, $targetDir, $prefix) {
    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];
    
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Erro no upload: ' . $file['error']];
    }
    
    if (!array_key_exists($file['type'], $allowedTypes)) {
        return ['success' => false, 'message' => 'Tipo de arquivo não permitido'];
    }
    
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'Arquivo muito grande (máx. 5MB)'];
    }
    
    $extension = $allowedTypes[$file['type']];
    $filename = $prefix . '_' . uniqid() . '.' . $extension;
    $targetPath = rtrim($targetDir, '/') . '/' . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => false, 'message' => 'Falha ao mover arquivo'];
    }
    
    return ['success' => true, 'filename' => $filename];
}

// Habilita erros para desenvolvimento (remover em produção)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone padrão
date_default_timezone_set('America/Sao_Paulo');
?>