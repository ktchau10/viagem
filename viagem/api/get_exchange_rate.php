<?php
require_once '../auth_check.php';
require_once '../config/api_config.php';

// Verifica se a moeda de destino foi fornecida
if (!isset($_GET['currency'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode([
        'success' => false,
        'message' => 'Moeda de destino não fornecida'
    ]);
    exit;
}

$currency = strtoupper($_GET['currency']);

// Lista de moedas suportadas
$supportedCurrencies = [
    'USD' => ['symbol' => '$', 'flag' => 'us'],
    'EUR' => ['symbol' => '€', 'flag' => 'eu'],
    'GBP' => ['symbol' => '£', 'flag' => 'gb'],
    'JPY' => ['symbol' => '¥', 'flag' => 'jp'],
    'AUD' => ['symbol' => 'A$', 'flag' => 'au'],
    'CAD' => ['symbol' => 'C$', 'flag' => 'ca'],
    'CHF' => ['symbol' => 'CHF', 'flag' => 'ch'],
    'CNY' => ['symbol' => '¥', 'flag' => 'cn'],
    'ARS' => ['symbol' => '$', 'flag' => 'ar'],
    'CLP' => ['symbol' => '$', 'flag' => 'cl'],
    'COP' => ['symbol' => '$', 'flag' => 'co'],
    'PEN' => ['symbol' => 'S/', 'flag' => 'pe'],
    'UYU' => ['symbol' => '$', 'flag' => 'uy'],
    'MXN' => ['symbol' => '$', 'flag' => 'mx']
];

if (!isset($supportedCurrencies[$currency])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode([
        'success' => false,
        'message' => 'Moeda não suportada'
    ]);
    exit;
}

try {
    // Usar a API de câmbio do Exchange Rates Data API
    $url = "https://api.exchangerate-api.com/v4/latest/BRL";
    
    // Fazer a requisição à API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        throw new Exception('Erro na requisição de câmbio: ' . curl_error($ch));
    }
    
    curl_close($ch);
    
    // Decodificar a resposta
    $data = json_decode($response, true);
    
    if (!$data || !isset($data['rates'][$currency])) {
        throw new Exception('Erro ao obter taxa de câmbio');
    }
    
    // Calcular as taxas
    $rate = $data['rates'][$currency];
    $inverse_rate = 1 / $rate;
    
    // Formatar as taxas
    $formatted_direct = number_format($rate, 2, ',', '.');
    $formatted_inverse = number_format($inverse_rate, 2, ',', '.');
    
    echo json_encode([
        'success' => true,
        'exchange_rate' => [
            'from' => 'BRL',
            'to' => $currency,
            'rate' => $rate,
            'inverse_rate' => $inverse_rate,
            'formatted' => [
                'direct' => "R$ 1,00 = {$supportedCurrencies[$currency]['symbol']} $formatted_direct",
                'inverse' => "{$supportedCurrencies[$currency]['symbol']} 1,00 = R$ $formatted_inverse"
            ],
            'symbol' => $supportedCurrencies[$currency]['symbol'],
            'flag' => $supportedCurrencies[$currency]['flag'],
            'last_update' => date('Y-m-d H:i:s', strtotime($data['time_last_updated']))
        ]
    ]);

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
