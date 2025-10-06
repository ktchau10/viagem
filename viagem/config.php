<?php
// Habilitar exibição de erros durante o desenvolvimento
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configurações de banco de dados
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'plataforma_viagens');

// Incluir configurações de API
require_once __DIR__ . '/config/api_config.php';

// Incluir conexão com o banco de dados
require_once __DIR__ . '/db_connect.php';
?>