<?php
// Chaves de API - MANTENHA ESTE ARQUIVO FORA DO CONTROLE DE VERSÃO
// Copie este arquivo para api_config.local.php e substitua com suas chaves

// Google Maps & Places API - https://console.cloud.google.com/
define('GOOGLE_MAPS_API_KEY', 'YOUR_GOOGLE_MAPS_API_KEY');
define('GOOGLE_PLACES_API_KEY', 'YOUR_GOOGLE_PLACES_API_KEY');

// Exchange Rate API - https://www.exchangerate-api.com/
define('EXCHANGE_RATE_API_KEY', 'YOUR_EXCHANGE_RATE_API_KEY');

// Configurações do Mapa
define('DEFAULT_MAP_CENTER_LAT', -23.5505); // São Paulo
define('DEFAULT_MAP_CENTER_LNG', -46.6333);
define('DEFAULT_MAP_ZOOM', 13);

// Configurações da busca de lugares
define('PLACES_SEARCH_RADIUS', 50000); // 50km
define('MAX_PLACES_RESULTS', 20);

// Configurações de moeda
define('DEFAULT_CURRENCY', 'BRL');
define('EXCHANGE_RATE_UPDATE_INTERVAL', 3600); // 1 hora em segundos
