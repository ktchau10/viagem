<?php
require_once '../auth_check.php';
require_once '../classes/GestaoViagens.php';

// Verifica se a requisição é DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Método inválido']);
    exit;
}

// Obtém o JSON do corpo da requisição
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Verifica se o JSON é válido
if ($data === null) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'JSON inválido']);
    exit;
}

// Verifica o token CSRF
if (!isset($data['csrf_token']) || !verificarCSRFToken($data['csrf_token'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
    exit;
}

// Verifica se o ID do local foi fornecido
if (!isset($data['placeId']) || !is_numeric($data['placeId'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'ID do local não fornecido']);
    exit;
}

// Obtém o usuário autenticado
$usuario = verificarAutenticacao();

// Instancia o gerenciador de viagens
$gestaoViagens = new GestaoViagens($usuario['usuario_id']);

try {
    // Remove o local
    $gestaoViagens->removerLocal($data['placeId']);

    echo json_encode([
        'success' => true,
        'message' => 'Local removido com sucesso'
    ]);

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
