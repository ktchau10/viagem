<?php
require_once '../auth_check.php';
require_once '../classes/GestaoViagens.php';

// Verifica se o ID do dia foi fornecido
if (!isset($_GET['dayId']) || !is_numeric($_GET['dayId'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'ID do dia não fornecido']);
    exit;
}

$dia_id = (int)$_GET['dayId'];

// Obtém o usuário autenticado
$usuario = verificarAutenticacao();

// Instancia o gerenciador de viagens
$gestaoViagens = new GestaoViagens($usuario['usuario_id']);

try {
    // Busca os locais do dia
    $locais = $gestaoViagens->buscarLocaisDoDia($dia_id);

    echo json_encode([
        'success' => true,
        'places' => $locais
    ]);

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
