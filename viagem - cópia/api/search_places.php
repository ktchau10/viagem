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
    // Configurar a requisição para o Nominatim
    $url = NOMINATIM_API_URL . '/search?' . http_build_query([
        'q' => $query,
        'format' => 'json',
        'addressdetails' => 1,
        'limit' => MAX_PLACES_RESULTS,
        'countrycodes' => 'br', // Limitar para Brasil
        'accept-language' => 'pt-BR'
    ]);

    // Adicionar User-Agent conforme requisitado pelo Nominatim
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => 'User-Agent: ' . NOMINATIM_USER_AGENT
        ]
    ];

    $response = file_get_contents($url);

    if ($response === false) {
        throw new Exception('Erro ao consultar o serviço de busca');
    }

    $places = json_decode($response, true);
    
    if ($places === false || !is_array($places)) {
        throw new Exception('Erro ao decodificar resposta do serviço de busca');
    }
    
    // Formatar os resultados
    $formatted_places = array_map(function($place) {
        // Determinar o tipo do local
        $type = 'outro';
        if (isset($place['type'])) {
            switch ($place['type']) {
                case 'restaurant':
                case 'cafe':
                case 'bar':
                case 'fast_food':
                    $type = 'restaurante';
                    break;
                case 'hotel':
                case 'hostel':
                case 'guest_house':
                    $type = 'hotel';
                    break;
                case 'tourism':
                case 'museum':
                case 'attraction':
                case 'artwork':
                case 'gallery':
                    $type = 'atracao';
                    break;
                case 'bus_station':
                case 'train_station':
                case 'subway_station':
                case 'airport':
                    $type = 'transporte';
                    break;
            }
        }

        // Construir o endereço formatado
        $address_parts = [];
        if (isset($place['address'])) {
            $addr = $place['address'];
            if (isset($addr['road'])) $address_parts[] = $addr['road'];
            if (isset($addr['suburb'])) $address_parts[] = $addr['suburb'];
            if (isset($addr['city'])) $address_parts[] = $addr['city'];
            if (isset($addr['state'])) $address_parts[] = $addr['state'];
        }

        return [
            'name' => $place['display_name'],
            'type' => $type,
            'latitude' => (float)$place['lat'],
            'longitude' => (float)$place['lon'],
            'address' => implode(', ', $address_parts),
            'osm_id' => $place['osm_id'],
            'importance' => $place['importance'] ?? null
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
