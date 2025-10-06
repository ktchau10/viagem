<?php
require_once '../auth_check.php';
require_once '../classes/GestaoViagens.php';

// Verifica se a requisição é POST e JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || 
    !isset($_SERVER['CONTENT_TYPE']) || 
    strpos($_SERVER['CONTENT_TYPE'], 'application/json') === false) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Requisição inválida']);
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

// Verifica se os dados necessários foram fornecidos
if (!isset($data['dayId']) || !isset($data['order']) || !is_array($data['order'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
    exit;
}

// Obtém o usuário autenticado
$usuario = verificarAutenticacao();

// Instancia o gerenciador de viagens
$gestaoViagens = new GestaoViagens($usuario['usuario_id']);

try {
    // Atualiza a ordem dos locais
    $gestaoViagens->atualizarOrdemLocais($data['dayId'], $data['order']);

    // Busca os locais atualizados
    $locais = $gestaoViagens->buscarLocaisDoDia($data['dayId']);

    echo json_encode([
        'success' => true,
        'message' => 'Ordem atualizada com sucesso',
        'places' => $locais
    ]);

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
