<?php
// OpenStreetMap e Nominatim não requerem chave de API
define('NOMINATIM_API_URL', 'https://nominatim.openstreetmap.org');
define('NOMINATIM_USER_AGENT', 'TravelPlanner/1.0'); // Identificação necessária para o Nominatim

// OSRM - Open Source Routing Machine (Serviço de rotas gratuito)
define('OSRM_API_URL', 'https://router.project-osrm.org'); // Servidor público gratuito
// Alternativas de servidores OSRM:
// - https://routing.openstreetmap.de/ (Europa)
// - http://map.project-osrm.org/ (Global)
// - Também é possível hospedar seu próprio servidor OSRM

// AwesomeAPI (Cotações de moeda)
define('AWESOME_API_URL', 'https://economia.awesomeapi.com.br/json/last');

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
