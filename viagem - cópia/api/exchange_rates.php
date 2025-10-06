<?php
require_once '../auth_check.php';
require_once '../config.php';

header('Content-Type: application/json');

// API Key
$api_key = EXCHANGE_RATE_API_KEY;

// Base currency is always BRL
$base_currency = 'BRL';
$currency = $_GET['currency'] ?? 'USD';

try {
    // Build API URL
    $url = "https://v6.exchangerate-api.com/v6/{$api_key}/latest/{$base_currency}";
    
    // Make API request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $response = curl_exec($ch);
    
    if(curl_errno($ch)) {
        throw new Exception('Curl error: ' . curl_error($ch));
    }
    
    curl_close($ch);
    
    // Parse response
    $data = json_decode($response, true);
    
    if (!$data || !isset($data['conversion_rates'][$currency])) {
        throw new Exception('Invalid response from exchange rate service');
    }
    
    // Calculate rates
    $direct_rate = $data['conversion_rates'][$currency];
    $inverse_rate = 1 / $direct_rate;
    
    // Format rates for display
    $formatted_direct = number_format($direct_rate, 2, ',', '.');
    $formatted_inverse = number_format($inverse_rate, 2, ',', '.');
    
    // Get currency code info for flag
    $flag_code = strtolower($currency === 'EUR' ? 'eu' : substr($currency, 0, 2));
    
    echo json_encode([
        'success' => true,
        'exchange_rate' => [
            'base' => $base_currency,
            'to' => $currency,
            'rate' => $direct_rate,
            'inverse_rate' => $inverse_rate,
            'flag' => $flag_code,
            'formatted' => [
                'direct' => "1 {$currency} = R$ {$formatted_direct}",
                'inverse' => "R$ 1,00 = {$currency} {$formatted_inverse}"
            ],
            'last_update' => date('Y-m-d H:i:s')
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching exchange rates: ' . $e->getMessage()
    ]);
}
