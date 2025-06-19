<?php
require_once __DIR__ . '/config.php';

/**
 * Obtém todas as séries cadastradas com opção de filtro e paginação
 * @param array $filters Filtros opcionais
 * @param int $page Página atual para paginação
 * @param int $perPage Itens por página
 * @return array ['series' => [], 'total' => 0, 'pages' => 1]
 */
function getAllSeries($filters = [], $page = 1, $perPage = 12) {
    $series = loadJsonData(SERIES_FILE);
    
    // Aplica filtros
    if (!empty($filters)) {
        $series = filterSeries($series, $filters);
    }
    
    // Ordena por data de cadastro (mais recentes primeiro)
    usort($series, function($a, $b) {
        return strtotime($b['data_cadastro']) - strtotime($a['data_cadastro']);
    });
    
    // Paginação
    $total = count($series);
    $totalPages = ceil($total / $perPage);
    $offset = ($page - 1) * $perPage;
    $paginated = array_slice($series, $offset, $perPage);
    
    return [
        'series' => $paginated,
        'total' => $total,
        'pages' => $totalPages
    ];
}

/**
 * Busca uma série pelo ID
 * @param string $id ID da série
 * @return array|null Dados da série ou null se não encontrada
 */
function getSerieById($id) {
    $series = loadJsonData(SERIES_FILE);
    
    foreach ($series as $serie) {
        if ($serie['id'] === $id) {
            return $serie;
        }
    }
    
    return null;
}

/**
 * Adiciona uma nova série ao sistema
 * @param array $data Dados da série
 * @param array $image Dados da imagem (opcional)
 * @return array ['success' => bool, 'message' => string, 'id' => string]
 */
function addSerie($data, $image = null) {
    $series = loadJsonData(SERIES_FILE);
    
    // Validação básica
    if (empty($data['titulo'])) {
        return ['success' => false, 'message' => 'Título é obrigatório'];
    }
    
    // Prepara os dados da série (CORREÇÃO APLICADA AQUI)
    $newSerie = [
        'id' => getNextSerieId($series),
        'titulo' => htmlspecialchars(trim($data['titulo'])),
        'titulo_original' => htmlspecialchars(trim($data['titulo_original'] ?? $data['titulo'])),
        'ano_lancamento' => (int)($data['ano_lancamento'] ?? date('Y')),
        'ano_encerramento' => !empty($data['ano_encerramento']) ? (int)$data['ano_encerramento'] : null,
        'pais' => htmlspecialchars(trim($data['pais'] ?? 'Desconhecido')),
        'idioma' => htmlspecialchars(trim($data['idioma'] ?? 'Desconhecido')),
        'status' => htmlspecialchars(trim($data['status'] ?? 'Em Exibição')), // LINHA CORRIGIDA
        'classificacao' => htmlspecialchars(trim($data['classificacao'] ?? 'L')),
        'sinopse' => htmlspecialchars(trim($data['sinopse'] ?? '')),
        'avaliacao' => min(max((float)($data['avaliacao'] ?? 0), 10)), // Garante entre 0 e 10
        'generos' => array_map('trim', $data['generos'] ?? []),
        'emissora' => htmlspecialchars(trim($data['emissora'] ?? '')),
        'onde_visto' => htmlspecialchars(trim($data['onde_visto'] ?? '')),
        'data_cadastro' => date('Y-m-d H:i:s'),
        'data_atualizacao' => date('Y-m-d H:i:s'),
        'user_status' => 'Não assistido',
        'progresso' => 0,
        'equipe' => $data['equipe'] ?? []
    ];
    
    // Processa a imagem
    if ($image && $image['error'] === UPLOAD_ERR_OK) {
        $upload = processImageUpload($image, CAPAS_PATH, 'serie_' . $newSerie['id']);
        if (!$upload['success']) {
            return ['success' => false, 'message' => $upload['message']];
        }
        $newSerie['imagem'] = 'https://dexseries.onrender.com/uploads/' . $upload['filename'];
    }
    
    // Adiciona a nova série
    $series[] = $newSerie;
    
    // Salva no arquivo
    if (saveAllSeries($series)) {
        return ['success' => true, 'message' => 'Série adicionada com sucesso', 'id' => $newSerie['id']];
    }
    
    // Se falhar ao salvar, remove a imagem se foi enviada
    if (isset($newSerie['imagem'])) {
        $imagePath = CAPAS_PATH . '/' . basename($newSerie['imagem']);
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    return ['success' => false, 'message' => 'Falha ao salvar série'];
}

/**
 * Atualiza uma série existente
 * @param string $id ID da série
 * @param array $data Dados atualizados
 * @param array $image Nova imagem (opcional)
 * @return array ['success' => bool, 'message' => string]
 */
function updateSerie($id, $data, $image = null) {
    $series = loadJsonData(SERIES_FILE);
    $updated = false;
    $oldImage = null;
    
    foreach ($series as &$serie) {
        if ($serie['id'] === $id) {
            // Mantém dados imutáveis
            $data['id'] = $id;
            $data['data_cadastro'] = $serie['data_cadastro'];
            
            // Atualiza campos permitidos
            $serie['titulo'] = htmlspecialchars(trim($data['titulo'] ?? $serie['titulo']));
            $serie['titulo_original'] = htmlspecialchars(trim($data['titulo_original'] ?? $serie['titulo_original']));
            $serie['ano_lancamento'] = (int)($data['ano_lancamento'] ?? $serie['ano_lancamento']);
            $serie['ano_encerramento'] = isset($data['ano_encerramento']) ? (int)$data['ano_encerramento'] : $serie['ano_encerramento'];
            $serie['pais'] = htmlspecialchars(trim($data['pais'] ?? $serie['pais']));
            $serie['idioma'] = htmlspecialchars(trim($data['idioma'] ?? $serie['idioma']));
            $serie['status'] = htmlspecialchars(trim($data['status'] ?? $serie['status']));
            $serie['classificacao'] = htmlspecialchars(trim($data['classificacao'] ?? $serie['classificacao']));
            $serie['sinopse'] = htmlspecialchars(trim($data['sinopse'] ?? $serie['sinopse']));
            $serie['avaliacao'] = min(10, max(0, (float)($data['avaliacao'] ?? $serie['avaliacao'])));
            $serie['generos'] = !empty($data['generos']) ? array_map('trim', $data['generos']) : $serie['generos'];
            $serie['emissora'] = htmlspecialchars(trim($data['emissora'] ?? $serie['emissora']));
            $serie['onde_visto'] = htmlspecialchars(trim($data['onde_visto'] ?? $serie['onde_visto']));
            $serie['data_atualizacao'] = date('Y-m-d H:i:s');
            $serie['equipe'] = $data['equipe'] ?? $serie['equipe'];
            
            // Campos específicos da ação do usuário
            $serie['user_status'] = $data['user_status'] ?? $serie['user_status'] ?? 'Não assistido';
            $serie['progresso'] = $data['progresso'] ?? $serie['progresso'] ?? 0;
            $serie['favorita'] = $data['favorita'] ?? $serie['favorita'] ?? false;
            
            if (isset($data['data_conclusao'])) {
                $serie['data_conclusao'] = $data['data_conclusao'];
            }
            
            // Atualiza temporadas se fornecidas
            if (isset($data['temporadas'])) {
                $serie['temporadas'] = $data['temporadas'];
            }
            
            // Processa nova imagem
            if ($image && $image['error'] === UPLOAD_ERR_OK) {
                $oldImage = $serie['imagem'] ?? null;
                $upload = processImageUpload($image, CAPAS_PATH, 'serie_' . $id);
                if (!$upload['success']) {
                    return ['success' => false, 'message' => $upload['message']];
                }
                $serie['imagem'] = 'uploads/' . $upload['filename'];
            }
            
            $updated = true;
            break;
        }
    }
    
    if ($updated) {
        if (saveAllSeries($series)) {
            // Remove a imagem antiga se foi substituída
            if ($oldImage && file_exists(CAPAS_PATH . '/' . basename($oldImage))) {
                unlink(CAPAS_PATH . '/' . basename($oldImage));
            }
            return ['success' => true, 'message' => 'Série atualizada com sucesso'];
        }
        
        // Se falhar ao salvar, remove a nova imagem se foi enviada
        if (isset($serie['imagem']) && $serie['imagem'] !== $oldImage) {
            $newImagePath = CAPAS_PATH . '/' . basename($serie['imagem']);
            if (file_exists($newImagePath)) {
                unlink($newImagePath);
            }
        }
    }
    
    return ['success' => false, 'message' => 'Série não encontrada ou falha ao atualizar'];
}

/**
 * Remove uma série do sistema
 * @param string $id ID da série
 * @return array ['success' => bool, 'message' => string]
 */
function deleteSerie($id) {
    $series = loadJsonData(SERIES_FILE);
    $deleted = false;
    $imageToDelete = null;
    
    foreach ($series as $key => $serie) {
        if ($serie['id'] === $id) {
            $imageToDelete = $serie['imagem'] ?? null;
            unset($series[$key]);
            $deleted = true;
            break;
        }
    }
    
    if ($deleted) {
        // Reindexa o array
        $series = array_values($series);
        
        if (saveAllSeries($series)) {
            // Remove a imagem associada
            if ($imageToDelete && file_exists(CAPAS_PATH . '/' . basename($imageToDelete))) {
                unlink(CAPAS_PATH . '/' . basename($imageToDelete));
            }
            return ['success' => true, 'message' => 'Série excluída com sucesso'];
        }
        return ['success' => false, 'message' => 'Falha ao salvar alterações'];
    }
    
    return ['success' => false, 'message' => 'Série não encontrada'];
}

/**
 * Filtra séries com base em critérios
 * @param array $series Lista de séries
 * @param array $criteria Critérios de filtro
 * @return array Séries filtradas
 */
function filterSeries($series, $criteria) {
    return array_filter($series, function($serie) use ($criteria) {
        foreach ($criteria as $key => $value) {
            if (empty($value)) continue;
            
            switch ($key) {
                case 'search':
                    $search = strtolower($value);
                    $fields = ['titulo', 'titulo_original', 'sinopse'];
                    $match = false;
                    foreach ($fields as $field) {
                        if (isset($serie[$field]) && stripos(strtolower($serie[$field]), $search) !== false) {
                            $match = true;
                            break;
                        }
                    }
                    if (!$match) return false;
                    break;
                    
                case 'generos':
                    if (!array_intersect((array)$value, $serie['generos'] ?? [])) {
                        return false;
                    }
                    break;
                    
                case 'status':
                    if (strtolower($serie['status'] ?? '') !== strtolower($value)) {
                        return false;
                    }
                    break;
                    
                case 'user_status':
                    if (strtolower($serie['user_status'] ?? '') !== strtolower($value)) {
                        return false;
                    }
                    break;
                    
                default:
                    if (isset($serie[$key]) && $serie[$key] != $value) {
                        return false;
                    }
            }
        }
        return true;
    });
}

/**
 * Obtém valores únicos de um campo para filtros
 * @param string $field Campo para obter valores
 * @return array Valores únicos
 */
function getUniqueFieldValues($field) {
    $series = loadJsonData(SERIES_FILE);
    $values = [];
    
    foreach ($series as $serie) {
        if (isset($serie[$field])) {
            if (is_array($serie[$field])) {
                $values = array_merge($values, $serie[$field]);
            } else {
                $values[] = $serie[$field];
            }
        }
    }
    
    return array_unique($values);
}

/**
 * Atualiza o status do usuário para uma série
 * @param string $serieId ID da série
 * @param string $status Novo status
 * @param int $progresso Progresso (opcional)
 * @return bool Sucesso da operação
 */
function updateUserStatus($serieId, $status, $progresso = null) {
    $series = loadJsonData(SERIES_FILE);
    $updated = false;
    
    foreach ($series as &$serie) {
        if ($serie['id'] === $serieId) {
            $serie['user_status'] = $status;
            if ($progresso !== null) {
                $serie['progresso'] = min(max((int)$progresso, 0), 100);
            }
            $serie['data_atualizacao'] = date('Y-m-d H:i:s');
            $updated = true;
            break;
        }
    }
    
    if ($updated) {
        return saveAllSeries($series);
    }
    
    return false;
}
