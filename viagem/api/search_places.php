<?php
require_once '../auth_check.php';
require_once '../config.php';

header('Content-Type: application/json');

// Obter parâmetro de busca
$query = filter_input(INPUT_GET, 'query', FILTER_SANITIZE_STRING);

if (empty($query)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Termo de busca não fornecido'
    ]);
    exit;
}

try {
    // Configurar a requisição para o Google Places API
    $url = 'https://maps.googleapis.com/maps/api/place/textsearch/json?' . http_build_query([
        'query' => $query,
        'key' => GOOGLE_MAPS_API_KEY,
        'language' => 'pt-BR',
        'region' => 'br'
    ]);

    $response = file_get_contents($url);

    if ($response === false) {
        throw new Exception('Erro ao consultar o serviço de busca');
    }

    $result = json_decode($response, true);
    
    if ($result['status'] !== 'OK' && $result['status'] !== 'ZERO_RESULTS') {
        throw new Exception('Erro no serviço de busca: ' . $result['status']);
    }

    $places = $result['results'] ?? [];
    
    // Formatar os resultados
    $formatted_places = array_map(function($place) {
        // Determinar o tipo do local
        $type = 'outro';
        if (isset($place['types'])) {
            if (in_array('restaurant', $place['types']) || 
                in_array('cafe', $place['types']) || 
                in_array('food', $place['types'])) {
                $type = 'restaurante';
            } elseif (in_array('lodging', $place['types'])) {
                $type = 'hotel';
            } elseif (in_array('tourist_attraction', $place['types']) || 
                     in_array('museum', $place['types']) || 
                     in_array('point_of_interest', $place['types'])) {
                $type = 'atracao';
            } elseif (in_array('transit_station', $place['types']) || 
                     in_array('airport', $place['types'])) {
                $type = 'transporte';
            }
        }

        return [
            'name' => $place['name'],
            'type' => $type,
            'latitude' => $place['geometry']['location']['lat'],
            'longitude' => $place['geometry']['location']['lng'],
            'address' => $place['formatted_address'],
            'place_id' => $place['place_id'],
            'rating' => $place['rating'] ?? null,
            'photo_reference' => isset($place['photos'][0]['photo_reference']) ? 
                                $place['photos'][0]['photo_reference'] : null
        ];
    }, $places);

    echo json_encode([
        'success' => true,
        'places' => $formatted_places
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao buscar lugares: ' . $e->getMessage()
    ]);
}
